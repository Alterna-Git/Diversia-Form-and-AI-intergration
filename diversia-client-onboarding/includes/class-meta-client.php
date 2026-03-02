<?php
if (!defined('ABSPATH')) exit;

/**
 * Wraps the Meta Graph API for fetching Lead Gen Form lead counts.
 * Mirrors the pattern used by DCO_OpenAI_Client.
 */
class DCO_Meta_Client {

    const API_BASE    = 'https://graph.facebook.com/v19.0';
    const CACHE_TTL   = HOUR_IN_SECONDS;
    const CACHE_GROUP = 'dco_meta_leads';

    private string $access_token;

    public function __construct() {
        $this->access_token = DCO_Admin_Settings::get_option(
            DCO_Admin_Settings::OPT_META_ACCESS_TOKEN, ''
        );
    }

    /**
     * Fetches the total lead count for a Meta Lead Gen Form.
     *
     * Caches the result for 1 hour (keyed by form_id hash).
     *
     * @param  string         $form_id  Numeric Lead Gen Form ID from Meta.
     * @return int|WP_Error             Lead count on success, WP_Error on failure.
     */
    public function fetch_lead_count(string $form_id): int|WP_Error {
        if (empty($this->access_token)) {
            return new WP_Error(
                'dco_meta_no_token',
                'Meta access token is not configured. Please add it in Settings → Client Onboarding.'
            );
        }

        $form_id = sanitize_text_field($form_id);
        if (empty($form_id) || !ctype_digit($form_id)) {
            return new WP_Error('dco_meta_invalid_form_id', 'Invalid Meta Lead Form ID — it must be a numeric string.');
        }

        // Return cached count if available
        $cache_key = 'leads_' . md5($form_id);
        $cached    = get_transient(self::CACHE_GROUP . '_' . $cache_key);
        if ($cached !== false) {
            return (int) $cached;
        }

        $url = add_query_arg(
            array(
                'fields'       => 'id',
                'summary'      => 'true',
                'limit'        => '1',
                'access_token' => $this->access_token,
            ),
            self::API_BASE . '/' . rawurlencode($form_id) . '/leads'
        );

        $response = wp_remote_get($url, array('timeout' => 15));

        if (is_wp_error($response)) {
            return new WP_Error(
                'dco_meta_http_error',
                'HTTP error contacting Meta API: ' . $response->get_error_message()
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200) {
            $api_msg = isset($body['error']['message'])
                ? $body['error']['message']
                : 'HTTP ' . $code;
            error_log('[DCO Meta] fetch_lead_count error for form ' . $form_id . ': ' . $api_msg);
            return new WP_Error('dco_meta_api_error', 'Meta API error: ' . $api_msg);
        }

        $count = isset($body['summary']['total_count'])
            ? (int) $body['summary']['total_count']
            : 0;

        set_transient(self::CACHE_GROUP . '_' . $cache_key, $count, self::CACHE_TTL);

        return $count;
    }

    /**
     * Tests the access token by calling /me?fields=id,name.
     *
     * @return array{success:bool, message:string}
     */
    public function test_connection(): array {
        if (empty($this->access_token)) {
            return array(
                'success' => false,
                'message' => 'Meta access token is not configured.',
            );
        }

        $url = add_query_arg(
            array(
                'fields'       => 'id,name',
                'access_token' => $this->access_token,
            ),
            self::API_BASE . '/me'
        );

        $response = wp_remote_get($url, array('timeout' => 15));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'HTTP error: ' . $response->get_error_message(),
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200) {
            $api_msg = isset($body['error']['message'])
                ? $body['error']['message']
                : 'HTTP ' . $code;
            return array(
                'success' => false,
                'message' => 'Meta connection failed: ' . $api_msg,
            );
        }

        $account_name = isset($body['name']) ? $body['name'] : 'Unknown';
        $account_id   = isset($body['id'])   ? $body['id']   : '—';

        return array(
            'success' => true,
            'message' => 'Meta connection successful. Connected as: ' . $account_name . ' (ID: ' . $account_id . ')',
        );
    }

    /**
     * Clears the cached lead count for a given form ID.
     *
     * @param string $form_id
     */
    public function clear_cache(string $form_id): void {
        $cache_key = 'leads_' . md5($form_id);
        delete_transient(self::CACHE_GROUP . '_' . $cache_key);
    }
}
