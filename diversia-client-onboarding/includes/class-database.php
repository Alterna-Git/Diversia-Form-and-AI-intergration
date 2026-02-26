<?php
if (!defined('ABSPATH')) exit;

class DCO_Database {

    const DB_VERSION        = '1.0.0';
    const DB_VERSION_OPTION = 'dco_db_version';

    /**
     * Returns the full table name for wp_dco_client_applications.
     */
    public static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'dco_client_applications';
    }

    /**
     * Runs on plugin activation.
     */
    public static function activate(): void {
        self::create_tables();
        update_option(self::DB_VERSION_OPTION, self::DB_VERSION);
    }

    /**
     * Runs on plugin deactivation. Tables are intentionally kept.
     */
    public static function deactivate(): void {
        // No-op — preserve data across deactivations
    }

    /**
     * Hooked to admin_init. Upgrades the table if the DB version is behind.
     */
    public static function check_version(): void {
        if (get_option(self::DB_VERSION_OPTION) !== self::DB_VERSION) {
            self::activate();
        }
    }

    /**
     * Creates/updates the wp_dco_client_applications table using dbDelta.
     */
    private static function create_tables(): void {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $table           = self::table();

        $sql = "CREATE TABLE {$table} (
            id                      BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,

            -- Step 1: Account info
            first_name              VARCHAR(100) NOT NULL DEFAULT '',
            last_name               VARCHAR(100) NOT NULL DEFAULT '',
            email                   VARCHAR(255) NOT NULL DEFAULT '',
            company_name            VARCHAR(255) NOT NULL DEFAULT '',
            wp_user_id              BIGINT(20) UNSIGNED DEFAULT NULL,

            -- Step 2: Trial needs
            trial_type              VARCHAR(100) NOT NULL DEFAULT '',
            target_population       LONGTEXT DEFAULT NULL,
            estimated_budget        VARCHAR(100) NOT NULL DEFAULT '',
            enrollment_goal         INT(11) DEFAULT NULL,
            timeline_months         INT(11) DEFAULT NULL,
            organization_type       VARCHAR(100) NOT NULL DEFAULT '',
            organization_website    VARCHAR(500) DEFAULT NULL,
            additional_notes        TEXT DEFAULT NULL,

            -- AI evaluation
            ai_qualified            TINYINT(1) DEFAULT NULL,
            ai_score                DECIMAL(5,2) DEFAULT NULL,
            ai_reasoning_es         TEXT DEFAULT NULL,
            ai_reasoning_en         TEXT DEFAULT NULL,
            ai_raw_response         LONGTEXT DEFAULT NULL,
            ai_evaluated_at         DATETIME DEFAULT NULL,

            -- Qualification token
            qualification_token     VARCHAR(64) DEFAULT NULL,
            token_expires_at        DATETIME DEFAULT NULL,

            -- Stripe payment
            stripe_session_id       VARCHAR(255) DEFAULT NULL,
            stripe_session_url      VARCHAR(1000) DEFAULT NULL,
            stripe_payment_intent   VARCHAR(255) DEFAULT NULL,
            stripe_customer_id      VARCHAR(255) DEFAULT NULL,
            stripe_amount_paid      INT(11) DEFAULT NULL,
            stripe_currency         VARCHAR(10) NOT NULL DEFAULT 'usd',
            payment_completed_at    DATETIME DEFAULT NULL,

            -- Fulfillment
            ctd_client_id           BIGINT(20) UNSIGNED DEFAULT NULL,
            welcome_email_sent      TINYINT(1) NOT NULL DEFAULT 0,

            -- Application status
            status                  VARCHAR(30) NOT NULL DEFAULT 'step1_complete',

            -- Audit
            ip_address              VARCHAR(45) DEFAULT NULL,
            user_agent              VARCHAR(500) DEFAULT NULL,
            created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

            PRIMARY KEY  (id),
            KEY wp_user_id (wp_user_id),
            KEY status (status),
            KEY stripe_session_id (stripe_session_id),
            KEY ctd_client_id (ctd_client_id),
            KEY ai_qualified (ai_qualified),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql);
    }
}
