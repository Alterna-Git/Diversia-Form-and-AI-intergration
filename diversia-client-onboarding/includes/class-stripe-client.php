<?php
if (!defined('ABSPATH')) exit;

class DCO_Stripe_Client {

    private string $secret_key;
    private string $api_base = 'https://api.stripe.com/v1';

    public function __construct() {
        $this->secret_key = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_STRIPE_SECRET_KEY, '');
    }

    /**
     * Creates a Stripe Checkout Session and persists the session ID/URL to the application row.
     *
     * @param array $params {
     *   'application_id'  => int,
     *   'user_email'      => string,
     *   'company_name'    => string,
     *   'payment_method'  => string  'card' | 'us_bank_account'  (optional, defaults to 'card')
     * }
     * @return array { 'id' => string, 'url' => string }
     * @throws Exception on Stripe API error or misconfiguration.
     */
    public function create_checkout_session(array $params): array {
        if (empty($this->secret_key)) {
            throw new Exception('Stripe secret key is not configured.');
        }

        $price_id    = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_STRIPE_PRICE_ID, '');
        $success_url = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_STRIPE_SUCCESS_URL, home_url('/client-welcome/'));
        $cancel_url  = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_STRIPE_CANCEL_URL,  home_url('/client-registration/'));

        if (empty($price_id)) {
            throw new Exception('Stripe Price ID is not configured.');
        }

        // Stripe replaces {CHECKOUT_SESSION_ID} in success_url with the actual session ID
        if (strpos($success_url, '{CHECKOUT_SESSION_ID}') === false) {
            $success_url = add_query_arg('session_id', '{CHECKOUT_SESSION_ID}', $success_url);
        }

        // Whitelist the payment method type; default to card
        $allowed_methods = array('card', 'us_bank_account');
        $payment_method  = in_array($params['payment_method'] ?? '', $allowed_methods, true)
            ? $params['payment_method']
            : 'card';

        $body = array(
            'mode'                                  => 'payment',
            'customer_email'                        => sanitize_email($params['user_email']),
            'line_items[0][price]'                  => $price_id,
            'line_items[0][quantity]'               => '1',
            'metadata[application_id]'              => (string) $params['application_id'],
            'metadata[company_name]'                => sanitize_text_field($params['company_name']),
            'metadata[package_tier]'                => sanitize_text_field($params['package_tier'] ?? 'basic'),
            'success_url'                           => $success_url,
            'cancel_url'                            => $cancel_url,
            'billing_address_collection'            => 'required',
            'allow_promotion_codes'                 => 'false',
            'payment_method_types[0]'               => $payment_method,
        );

        // For ACH/eCheck: tell Stripe to show the bank account entry form.
        // 'automatic' tries instant bank linking (Plaid) first, falls back to
        // manual routing + account number entry with micro-deposit verification.
        if ($payment_method === 'us_bank_account') {
            $body['payment_method_options[us_bank_account][verification_method]'] = 'automatic';
        }

        $session = $this->api_request('POST', '/checkout/sessions', $body);

        // Persist to DB
        global $wpdb;
        $wpdb->update(
            DCO_Database::table(),
            array(
                'stripe_session_id'  => $session['id'],
                'stripe_session_url' => $session['url'],
                'status'             => 'payment_pending',
            ),
            array('id' => (int) $params['application_id']),
            array('%s', '%s', '%s'),
            array('%d')
        );

        return array(
            'id'  => $session['id'],
            'url' => $session['url'],
        );
    }

    /**
     * Retrieves a Checkout Session from Stripe (used during webhook to verify).
     */
    public function retrieve_session(string $session_id): array {
        return $this->api_request('GET', '/checkout/sessions/' . urlencode($session_id) . '?expand[]=payment_intent');
    }

    // ---------------------------------------------------------------------------
    // Private
    // ---------------------------------------------------------------------------

    private function api_request(string $method, string $endpoint, array $params = []): array {
        $url = $this->api_base . $endpoint;

        $args = array(
            'method'  => $method,
            'timeout' => 30,
            'headers' => array(
                // Stripe uses HTTP Basic Auth with secret key as username, empty password
                'Authorization' => 'Basic ' . base64_encode($this->secret_key . ':'),
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Stripe-Version' => '2023-10-16',
            ),
        );

        if ($method === 'POST' && !empty($params)) {
            $args['body'] = http_build_query($params);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new Exception('Stripe HTTP error: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            throw new Exception('Stripe error: ' . ($data['error']['message'] ?? 'Unknown Stripe error'));
        }

        if ($code < 200 || $code >= 300) {
            throw new Exception("Stripe returned HTTP {$code}");
        }

        return $data;
    }
}
