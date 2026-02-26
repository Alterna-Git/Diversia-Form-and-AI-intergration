<?php
if (!defined('ABSPATH')) exit;

class DCO_Qualification_Token {

    const TRANSIENT_PREFIX = 'dco_qual_token_';

    /**
     * Generates a secure token for a qualified application and stores it in a transient.
     *
     * @param int $application_id
     * @return string  The 64-character hex token.
     */
    public static function generate(int $application_id): string {
        $token   = bin2hex(random_bytes(32)); // 64 hex chars
        $ttl     = (int) DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_TOKEN_TTL_HOURS, 24);
        $expires = $ttl * HOUR_IN_SECONDS;

        // Store in transient: keyed by token, contains application + user context
        set_transient(self::TRANSIENT_PREFIX . $token, array(
            'application_id' => $application_id,
            'user_id'        => get_current_user_id(),
        ), $expires);

        // Persist token and expiry in the DB row for auditing
        global $wpdb;
        $wpdb->update(
            DCO_Database::table(),
            array(
                'qualification_token' => $token,
                'token_expires_at'    => gmdate('Y-m-d H:i:s', time() + $expires),
            ),
            array('id' => $application_id),
            array('%s', '%s'),
            array('%d')
        );

        return $token;
    }

    /**
     * Validates that a token is genuine, unexpired, and belongs to the current user's application.
     *
     * @param string $token
     * @param int    $application_id
     * @return bool
     */
    public static function validate(string $token, int $application_id): bool {
        if (empty($token) || strlen($token) !== 64) {
            return false;
        }

        $data = get_transient(self::TRANSIENT_PREFIX . $token);

        if (!$data || !is_array($data)) {
            return false;
        }

        if ((int) $data['application_id'] !== $application_id) {
            return false;
        }

        if (is_user_logged_in() && (int) $data['user_id'] !== get_current_user_id()) {
            return false;
        }

        return true;
    }

    /**
     * Revokes (deletes) the transient for a token. Called after provisioning.
     */
    public static function revoke(string $token): void {
        delete_transient(self::TRANSIENT_PREFIX . $token);
    }

    /**
     * Returns the stored token for an application (for audit display).
     */
    public static function get_token_for_application(int $application_id): ?string {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT qualification_token FROM " . DCO_Database::table() . " WHERE id = %d",
            $application_id
        ));
        return $row ? $row->qualification_token : null;
    }
}
