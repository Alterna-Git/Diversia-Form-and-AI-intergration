<?php
if (!defined('ABSPATH')) exit;

class DCO_Client_Provisioner {

    /**
     * Main provisioner — called after Stripe webhook confirms payment.
     * Upgrades the WP user role, creates the wp_ctd_clients record, and sends welcome email.
     *
     * @param int $application_id
     * @return bool
     */
    public static function provision(int $application_id): bool {
        global $wpdb;

        $app = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . DCO_Database::table() . " WHERE id = %d",
            $application_id
        ));

        if (!$app) {
            error_log("[DCO Provisioner] Application {$application_id} not found.");
            return false;
        }

        if (empty($app->wp_user_id)) {
            error_log("[DCO Provisioner] Application {$application_id} has no wp_user_id.");
            return false;
        }

        $user_id = (int) $app->wp_user_id;

        // 1. Upgrade role
        self::upgrade_user_role($user_id);

        // 2. Create wp_ctd_clients record
        $ctd_client_id = self::create_ctd_client_record($app, $user_id);
        if (!$ctd_client_id) {
            error_log("[DCO Provisioner] Failed to create ctd_clients row for application {$application_id}.");
            return false;
        }

        // 3. Update application row with ctd_client_id
        $wpdb->update(
            DCO_Database::table(),
            array('ctd_client_id' => $ctd_client_id),
            array('id' => $application_id),
            array('%d'),
            array('%d')
        );

        // 4. Revoke qualification token
        if (!empty($app->qualification_token)) {
            DCO_Qualification_Token::revoke($app->qualification_token);
        }

        // 5. Send bilingual welcome email
        self::send_client_welcome_email($app, $ctd_client_id);

        // 6. Notify admin
        self::notify_admin($app);

        // 7. Fire action hook for extensibility
        do_action('dco_client_provisioned', $application_id, $ctd_client_id, $user_id);

        error_log("[DCO Provisioner] SUCCESS: application {$application_id} → ctd_client_id {$ctd_client_id}");

        return true;
    }

    // ---------------------------------------------------------------------------
    // Private
    // ---------------------------------------------------------------------------

    /**
     * Upgrades WP user from pending_client → client role.
     */
    private static function upgrade_user_role(int $user_id): void {
        $user = new WP_User($user_id);
        $user->remove_role('pending_client');
        $user->add_role('client');
    }

    /**
     * Inserts a row into wp_ctd_clients (owned by clinical-trial-dashboard-enhanced).
     * Also sets the ctd_client_id user meta that grants dashboard access.
     *
     * @return int  The new client ID, or 0 on failure.
     */
    private static function create_ctd_client_record(object $app, int $user_id): int {
        global $wpdb;

        $ctd_clients_table = $wpdb->prefix . 'ctd_clients';

        // Ensure the table exists before writing
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$ctd_clients_table}'");
        if (!$table_exists) {
            error_log("[DCO Provisioner] wp_ctd_clients table does not exist.");
            return 0;
        }

        // Build a unique slug
        $base_slug = sanitize_title($app->company_name);
        $slug      = $base_slug;
        $suffix    = 1;
        while ($wpdb->get_var($wpdb->prepare("SELECT id FROM {$ctd_clients_table} WHERE slug = %s", $slug))) {
            $slug = $base_slug . '-' . $suffix;
            $suffix++;
        }

        $result = $wpdb->insert(
            $ctd_clients_table,
            array(
                'name'          => sanitize_text_field($app->company_name),
                'slug'          => $slug,
                'contact_name'  => sanitize_text_field($app->first_name . ' ' . $app->last_name),
                'contact_email' => sanitize_email($app->email),
                'status'        => 'active',
                'created_at'    => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );

        if (!$result) {
            return 0;
        }

        $ctd_client_id = (int) $wpdb->insert_id;

        // Set the user meta that the CTD plugin checks for dashboard access
        update_user_meta($user_id, 'ctd_client_id', $ctd_client_id);

        return $ctd_client_id;
    }

    /**
     * Sends a bilingual HTML welcome email matching the existing Diversia branding.
     */
    private static function send_client_welcome_email(object $app, int $ctd_client_id): void {
        $to       = sanitize_email($app->email);
        $subject  = '¡Bienvenido(a) a Diversia Health! / Welcome to Diversia Health!';
        $headers  = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Diversia Health <no-reply@' . parse_url(home_url(), PHP_URL_HOST) . '>',
        );

        ob_start();
        $first_name    = esc_html($app->first_name);
        $company_name  = esc_html($app->company_name);
        $login_url     = esc_url(wp_login_url());
        $email         = esc_html($app->email);
        $dashboard_url = esc_url(home_url('/trial-dashboard/')); // Admin should set the actual page
        include DCO_PLUGIN_DIR . 'templates/email-client-welcome.php';
        $message = ob_get_clean();

        $sent = wp_mail($to, $subject, $message, $headers);

        global $wpdb;
        if ($sent) {
            $wpdb->update(
                DCO_Database::table(),
                array('welcome_email_sent' => 1),
                array('id' => (int) $app->id),
                array('%d'),
                array('%d')
            );
        }
    }

    /**
     * Notifies the admin that a new client has been provisioned.
     */
    private static function notify_admin(object $app): void {
        $admin_email = DCO_Admin_Settings::get_option(
            DCO_Admin_Settings::OPT_ADMIN_NOTIFICATION_EMAIL,
            get_option('admin_email')
        );

        if (empty($admin_email)) return;

        $subject = '[Diversia] New client onboarded: ' . $app->company_name;
        $body    = "A new client has completed onboarding and payment.\n\n"
                 . "Company: {$app->company_name}\n"
                 . "Contact: {$app->first_name} {$app->last_name}\n"
                 . "Email: {$app->email}\n"
                 . "Trial Type: {$app->trial_type}\n"
                 . "Organization: {$app->organization_type}\n"
                 . "Budget: {$app->estimated_budget}\n"
                 . "AI Score: {$app->ai_score}/100\n\n"
                 . "View in WP Admin: " . admin_url('options-general.php?page=dco-settings') . "\n";

        wp_mail($admin_email, $subject, $body);
    }
}
