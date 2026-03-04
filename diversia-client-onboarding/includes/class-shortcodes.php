<?php
if (!defined('ABSPATH')) exit;

class DCO_Shortcodes {

    public static function register(): void {
        add_shortcode('diversia_client_registration', array(__CLASS__, 'render_registration'));
        add_shortcode('diversia_payment_gate',         array(__CLASS__, 'render_payment_gate'));
        add_shortcode('diversia_client_profile',       array(__CLASS__, 'render_client_profile'));
    }

    /**
     * [diversia_client_registration]
     * Multi-step registration form for potential clients.
     */
    public static function render_registration(array $atts = array()): string {
        $dco_is_returning_client = false;
        $dco_returning_user      = null;

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if (in_array('client', (array) $current_user->roles, true)) {
                // Active client — let them start a new campaign instead of blocking
                $dco_is_returning_client = true;
                $dco_returning_user      = $current_user;
            }
        }

        ob_start();
        include DCO_PLUGIN_DIR . 'templates/registration-form.php';
        return ob_get_clean();
    }

    /**
     * [diversia_payment_gate]
     * Validates a qualification token, then lets the qualified client proceed to Stripe.
     * Accepts URL params: ?application_id=N&token=XXX
     */
    public static function render_payment_gate(array $atts = array()): string {
        $atts = shortcode_atts(array(
            'application_id' => '',
            'token'          => '',
        ), $atts);

        // Prefer URL params (set by the redirect from the registration form)
        $application_id = (int) ($_GET['application_id'] ?? $atts['application_id']);
        $token          = sanitize_text_field($_GET['token'] ?? $atts['token']);

        if (!is_user_logged_in()) {
            return '<div class="dco-notice dco-notice--warning">'
                 . '<p>Please <a href="' . esc_url(wp_login_url(get_permalink())) . '">log in</a> to continue.</p>'
                 . '</div>';
        }

        if (!$application_id || !$token) {
            return '<div class="dco-notice dco-notice--error">'
                 . '<p><strong>Este enlace no es válido.</strong> / <strong>This link is not valid.</strong></p>'
                 . '<p>Please complete the <a href="' . esc_url(home_url('/client-registration/')) . '">registration form</a>.</p>'
                 . '</div>';
        }

        if (!DCO_Qualification_Token::validate($token, $application_id)) {
            return '<div class="dco-notice dco-notice--error">'
                 . '<p><strong>Este enlace ha expirado o no es válido.</strong></p>'
                 . '<p>This qualification link has expired or is invalid. Please contact us or <a href="' . esc_url(home_url('/client-registration/')) . '">restart the application</a>.</p>'
                 . '</div>';
        }

        // Verify the application belongs to the current user
        global $wpdb;
        $app = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . DCO_Database::table() . " WHERE id = %d AND wp_user_id = %d",
            $application_id,
            get_current_user_id()
        ));

        if (!$app || $app->ai_qualified != 1) {
            return '<div class="dco-notice dco-notice--error">'
                 . '<p>Application not found or not qualified. / Solicitud no encontrada o no calificada.</p>'
                 . '</div>';
        }

        ob_start();
        $nonce_stripe = wp_create_nonce('dco_create_stripe_session');
        include DCO_PLUGIN_DIR . 'templates/payment-gate.php';
        return ob_get_clean();
    }

    /**
     * [diversia_client_profile]
     * Displays the provisioned client's trial overview and recruitment progress.
     * Drop this shortcode onto the /trial-dashboard/ page.
     *
     * States:
     * - Not logged in         → login prompt
     * - Logged in, not client → "application under review" message
     * - client role           → trial overview card + Meta recruitment progress card
     */
    public static function render_client_profile(array $atts = array()): string {
        if (!is_user_logged_in()) {
            return '<div class="dco-notice dco-notice--warning">'
                 . '<p>Please <a href="' . esc_url(wp_login_url(get_permalink())) . '">log in</a> to view your dashboard. / '
                 . 'Por favor <a href="' . esc_url(wp_login_url(get_permalink())) . '">inicie sesión</a> para ver su panel.</p>'
                 . '</div>';
        }

        $user = wp_get_current_user();

        if (!in_array('client', (array) $user->roles, true)) {
            return '<div class="dco-notice dco-notice--info">'
                 . '<p><strong>Your application is being reviewed.</strong> / <strong>Su solicitud está siendo revisada.</strong></p>'
                 . '<p>You will receive an email once your account is fully activated. If you have questions, contact us at '
                 . '<a href="mailto:info@diversiahealth.com">info@diversiahealth.com</a>.</p>'
                 . '</div>';
        }

        $user_id         = (int) $user->ID;
        $app_id          = (int) get_user_meta($user_id, 'dco_application_id',    true);
        $trial_type      = (string) get_user_meta($user_id, 'dco_trial_type',         true);
        $enrollment_goal = (int) get_user_meta($user_id, 'dco_enrollment_goal',   true);
        $budget          = (string) get_user_meta($user_id, 'dco_estimated_budget',   true);
        $org_type        = (string) get_user_meta($user_id, 'dco_organization_type',  true);
        $ai_score        = (float) get_user_meta($user_id, 'dco_ai_score',           true);
        $ctd_client_id   = (int) get_user_meta($user_id, 'ctd_client_id',          true);

        // Fetch live Meta lead count from the applications table
        $meta_leads = 0;
        if ($app_id > 0) {
            global $wpdb;
            $meta_leads = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT meta_leads FROM " . DCO_Database::table() . " WHERE id = %d",
                $app_id
            ));
        }

        // Detect active language (WPML-compatible; falls back to WP locale)
        $lang = 'en';
        if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'es') {
            $lang = 'es';
        } elseif (strpos(get_locale(), 'es') === 0) {
            $lang = 'es';
        }

        $registration_url = home_url('/client-registration/');

        ob_start();
        include DCO_PLUGIN_DIR . 'templates/client-profile.php';
        return ob_get_clean();
    }
}
