<?php
if (!defined('ABSPATH')) exit;

class DCO_Stripe_Webhook {

    const REST_NAMESPACE = 'dco/v1';
    const REST_ROUTE     = '/stripe-webhook';

    /**
     * Registers the REST route. Hooked to rest_api_init.
     */
    public static function register_route(): void {
        register_rest_route(self::REST_NAMESPACE, self::REST_ROUTE, array(
            'methods'             => 'POST',
            'callback'            => array(__CLASS__, 'handle_webhook'),
            'permission_callback' => '__return_true', // Security is via HMAC signature below
        ));
    }

    /**
     * Main webhook handler.
     */
    public static function handle_webhook(WP_REST_Request $request): WP_REST_Response {
        $raw_body  = $request->get_body();
        $sig_header = $request->get_header('stripe_signature');

        if (!self::verify_signature($raw_body, $sig_header)) {
            error_log('[DCO] Stripe webhook signature verification failed.');
            return new WP_REST_Response(array('error' => 'Invalid signature'), 400);
        }

        $event = json_decode($raw_body, true);

        if (!isset($event['type'])) {
            return new WP_REST_Response(array('error' => 'Invalid event'), 400);
        }

        switch ($event['type']) {
            case 'checkout.session.completed':
                self::handle_checkout_session_completed($event);
                break;

            case 'payment_intent.payment_failed':
                self::handle_payment_intent_failed($event);
                break;

            default:
                // Unhandled event — return 200 so Stripe stops retrying
                break;
        }

        return new WP_REST_Response(array('received' => true), 200);
    }

    // ---------------------------------------------------------------------------
    // Private
    // ---------------------------------------------------------------------------

    /**
     * Verifies the Stripe-Signature header using HMAC-SHA256.
     * Follows Stripe's official verification specification.
     */
    private static function verify_signature(string $payload, ?string $sig_header): bool {
        if (empty($sig_header)) {
            return false;
        }

        $webhook_secret = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_STRIPE_WEBHOOK_SECRET, '');
        if (empty($webhook_secret)) {
            error_log('[DCO] Stripe webhook secret not configured.');
            return false;
        }

        // Parse t= and v1= from the header
        $timestamp  = null;
        $v1_sig     = null;
        $parts      = explode(',', $sig_header);

        foreach ($parts as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) !== 2) continue;
            if ($kv[0] === 't')  $timestamp = $kv[1];
            if ($kv[0] === 'v1') $v1_sig    = $kv[1];
        }

        if (!$timestamp || !$v1_sig) {
            return false;
        }

        // Replay protection: reject events older than 5 minutes
        if (abs(time() - (int) $timestamp) > 300) {
            error_log('[DCO] Stripe webhook timestamp too old.');
            return false;
        }

        $signed_payload = $timestamp . '.' . $payload;
        $expected       = hash_hmac('sha256', $signed_payload, $webhook_secret);

        return hash_equals($expected, $v1_sig);
    }

    private static function handle_checkout_session_completed(array $event): void {
        $session        = $event['data']['object'] ?? array();
        $application_id = (int) ($session['metadata']['application_id'] ?? 0);

        if (!$application_id) {
            error_log('[DCO] checkout.session.completed: missing application_id in metadata.');
            return;
        }

        global $wpdb;
        $app = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . DCO_Database::table() . " WHERE id = %d",
            $application_id
        ));

        if (!$app) {
            error_log("[DCO] checkout.session.completed: application {$application_id} not found.");
            return;
        }

        // Idempotency: skip if already provisioned
        if ($app->status === 'payment_complete') {
            return;
        }

        // Update payment fields
        $wpdb->update(
            DCO_Database::table(),
            array(
                'status'                => 'payment_complete',
                'stripe_payment_intent' => $session['payment_intent'] ?? null,
                'stripe_customer_id'    => $session['customer'] ?? null,
                'stripe_amount_paid'    => $session['amount_total'] ?? null,
                'stripe_currency'       => $session['currency'] ?? 'usd',
                'payment_completed_at'  => current_time('mysql'),
            ),
            array('id' => $application_id),
            array('%s', '%s', '%s', '%d', '%s', '%s'),
            array('%d')
        );

        DCO_Client_Provisioner::provision($application_id);

        self::log_event('checkout.session.completed', (string) $application_id, true);
    }

    private static function handle_payment_intent_failed(array $event): void {
        $pi             = $event['data']['object'] ?? array();
        $application_id = (int) ($pi['metadata']['application_id'] ?? 0);

        error_log("[DCO] Payment failed for application_id={$application_id}: " . ($pi['last_payment_error']['message'] ?? 'unknown'));
        self::log_event('payment_intent.payment_failed', (string) $application_id, false);
    }

    private static function log_event(string $event_type, string $application_id, bool $success): void {
        $status = $success ? 'SUCCESS' : 'FAILURE';
        error_log("[DCO Webhook] {$status} | event={$event_type} | application_id={$application_id}");
    }
}
