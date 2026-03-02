<?php
if (!defined('ABSPATH')) exit;

class DCO_Rate_Limiter {

    const TRANSIENT_PREFIX = 'dco_rate_';

    /**
     * Checks whether the identifier is under the rate limit for the given action,
     * and increments the counter if so.
     *
     * @return bool  true if under the limit (request should proceed), false if exceeded.
     */
    public static function check_and_increment(string $identifier, string $action): bool {
        // Admins are never rate-limited (useful for testing)
        if (current_user_can('manage_options')) {
            return true;
        }

        $max = (int) DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_RATE_LIMIT_MAX, 5);
        $key = self::build_key($identifier, $action);

        $current = (int) get_transient($key);

        if ($current >= $max) {
            return false;
        }

        // Increment: if transient doesn't exist, set_transient creates it at 1
        if ($current === 0) {
            set_transient($key, 1, HOUR_IN_SECONDS);
        } else {
            set_transient($key, $current + 1, HOUR_IN_SECONDS);
        }

        return true;
    }

    /**
     * Returns a rate-limit identifier: user ID for logged-in users, IP for others.
     */
    public static function get_identifier(): string {
        if (is_user_logged_in()) {
            return 'user_' . get_current_user_id();
        }
        return 'ip_' . self::get_ip();
    }

    /**
     * Resets the counter for an identifier + action (e.g. after successful completion).
     */
    public static function reset(string $identifier, string $action): void {
        delete_transient(self::build_key($identifier, $action));
    }

    private static function build_key(string $identifier, string $action): string {
        return self::TRANSIENT_PREFIX . $action . '_' . md5($identifier);
    }

    private static function get_ip(): string {
        $ip = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Take the first IP in the chain
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip    = trim($parts[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
    }
}
