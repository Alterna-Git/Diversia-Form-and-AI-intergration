<?php
if (!defined('ABSPATH')) exit;

/**
 * Handles the Zapier → Meta leads webhook.
 *
 * Endpoint: POST /wp-json/dco/v1/meta-lead
 *
 * Zapier setup:
 *   Action: Webhooks by Zapier → POST
 *   URL:    <site>/wp-json/dco/v1/meta-lead
 *   Header: Authorization: Bearer <secret configured in Settings>
 *   Body:   JSON — map "email", "first_name", "last_name", "phone", "form_id" from FB Lead Ads fields
 */
class DCO_Meta_Webhook {

    public static function register_route(): void {
        register_rest_route('dco/v1', '/meta-lead', array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array(__CLASS__, 'handle'),
            'permission_callback' => '__return_true', // auth handled inside handle()
        ));
    }

    public static function handle(WP_REST_Request $request): WP_REST_Response {
        // ── 1. Authenticate ──────────────────────────────────────────────────
        $configured_secret = (string) DCO_Admin_Settings::get_option(
            DCO_Admin_Settings::OPT_ZAPIER_WEBHOOK_SECRET, ''
        );

        if ($configured_secret === '') {
            return self::error(403, 'webhook_not_configured', 'Zapier webhook secret is not configured.');
        }

        // Accept secret from Authorization header (preferred) or body field (fallback)
        $provided_secret = '';
        $auth_header = $request->get_header('authorization');
        if ($auth_header && strncasecmp($auth_header, 'Bearer ', 7) === 0) {
            $provided_secret = trim(substr($auth_header, 7));
        }
        if ($provided_secret === '') {
            $provided_secret = (string) ($request->get_param('secret') ?? '');
        }

        if (!hash_equals($configured_secret, $provided_secret)) {
            error_log('[DCO Meta Webhook] 401 — invalid secret from ' . self::mask_ip());
            return self::error(401, 'unauthorized', 'Invalid webhook secret.');
        }

        // ── 2. Validate email ────────────────────────────────────────────────
        $email = sanitize_email((string) ($request->get_param('email') ?? ''));
        if (!$email || !is_email($email)) {
            return self::error(400, 'missing_email', 'A valid "email" field is required.');
        }

        // ── 3. Log the incoming lead (masked for privacy) ────────────────────
        $form_id    = sanitize_text_field((string) ($request->get_param('form_id')    ?? ''));
        $lead_id    = sanitize_text_field((string) ($request->get_param('lead_id')    ?? ''));
        error_log(sprintf(
            '[DCO Meta Webhook] Incoming lead — email: %s | form_id: %s | lead_id: %s',
            self::mask_email($email),
            $form_id ?: '(none)',
            $lead_id ?: '(none)'
        ));

        // ── 4. Look up application by email ──────────────────────────────────
        global $wpdb;
        $table = DCO_Database::table();

        $app = $wpdb->get_row($wpdb->prepare(
            "SELECT id, meta_leads FROM {$table} WHERE email = %s ORDER BY id DESC LIMIT 1",
            $email
        ));

        if (!$app) {
            error_log('[DCO Meta Webhook] No application found for email: ' . self::mask_email($email));
            // Return 200 (not 404) so Zapier does not treat it as an error and retry
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'No matching application for this email.',
            ), 200);
        }

        // ── 5. Increment meta_leads ───────────────────────────────────────────
        $update_data   = array(
            'meta_leads'           => (int) $app->meta_leads + 1,
            'meta_leads_synced_at' => current_time('mysql'),
        );
        $update_format = array('%d', '%s');

        if ($form_id !== '') {
            $update_data['meta_lead_form_id'] = $form_id;
            $update_format[]                  = '%s';
        }

        $wpdb->update(
            $table,
            $update_data,
            array('id' => (int) $app->id),
            $update_format,
            array('%d')
        );

        $new_count = (int) $update_data['meta_leads'];

        error_log(sprintf(
            '[DCO Meta Webhook] Lead counted — application_id: %d | new meta_leads total: %d',
            (int) $app->id,
            $new_count
        ));

        return new WP_REST_Response(array(
            'success'        => true,
            'application_id' => (int) $app->id,
            'meta_leads'     => $new_count,
        ), 200);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private static function error(int $status, string $code, string $message): WP_REST_Response {
        return new WP_REST_Response(array(
            'success' => false,
            'code'    => $code,
            'message' => $message,
        ), $status);
    }

    /** Mask an email for safe logging: ana***@example.com */
    private static function mask_email(string $email): string {
        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) return '***';
        return substr($parts[0], 0, 3) . '***@' . $parts[1];
    }

    private static function mask_ip(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
    }
}
