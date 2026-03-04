<?php
if (!defined('ABSPATH')) exit;

class DCO_Ajax_Handlers {

    /**
     * Registers all wp_ajax_* actions.
     */
    public static function register(): void {
        // Step 1: non-logged-in AND logged-in (user may already be logged in)
        add_action('wp_ajax_nopriv_dco_register_step1', array(__CLASS__, 'handle_step1'));
        add_action('wp_ajax_dco_register_step1',        array(__CLASS__, 'handle_step1'));

        // Step 2: must be logged in (we log the user in at end of step 1)
        add_action('wp_ajax_dco_register_step2',        array(__CLASS__, 'handle_step2'));

        // AI status poll (logged in)
        add_action('wp_ajax_dco_check_ai_status',       array(__CLASS__, 'handle_check_ai_status'));

        // Create Stripe session (logged in, requires valid token)
        add_action('wp_ajax_dco_create_stripe_session', array(__CLASS__, 'handle_create_stripe_session'));

        // Start new campaign (existing provisioned client only)
        add_action('wp_ajax_dco_start_new_campaign', array(__CLASS__, 'handle_start_new_campaign'));
    }

    // =========================================================================
    // Step 1 — Create WP account + application row
    // =========================================================================

    public static function handle_step1(): void {
        self::verify_nonce('dco_register_step1');

        // Rate limit by IP
        $identifier = DCO_Rate_Limiter::get_identifier();
        if (!DCO_Rate_Limiter::check_and_increment($identifier, 'step1')) {
            wp_send_json_error(array('message' => 'Too many requests. Please try again later. / Demasiados intentos. Por favor intente más tarde.', 'code' => 429));
            return;
        }

        // Sanitize inputs
        $first_name   = sanitize_text_field($_POST['first_name'] ?? '');
        $last_name    = sanitize_text_field($_POST['last_name'] ?? '');
        $email        = sanitize_email($_POST['email'] ?? '');
        $password     = $_POST['password'] ?? '';
        $company_name = sanitize_text_field($_POST['company_name'] ?? '');

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($company_name)) {
            wp_send_json_error(array('message' => 'All fields are required. / Todos los campos son obligatorios.'));
            return;
        }

        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Please enter a valid email address. / Por favor ingrese un correo electrónico válido.'));
            return;
        }

        if (strlen($password) < 8) {
            wp_send_json_error(array('message' => 'Password must be at least 8 characters. / La contraseña debe tener al menos 8 caracteres.'));
            return;
        }

        if (email_exists($email)) {
            wp_send_json_error(array('message' => 'This email is already registered. / Este correo ya está registrado.'));
            return;
        }

        global $wpdb;

        // Check if an application already exists for this email
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM " . DCO_Database::table() . " WHERE email = %s",
            $email
        ));
        if ($existing) {
            wp_send_json_error(array('message' => 'An application for this email already exists. / Ya existe una solicitud para este correo.'));
            return;
        }

        // Create the WordPress user
        $user_id = wp_create_user($email, $password, $email);
        if (is_wp_error($user_id)) {
            wp_send_json_error(array('message' => $user_id->get_error_message()));
            return;
        }

        // Set display name and role
        wp_update_user(array(
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $first_name . ' ' . $last_name,
            'role'         => 'pending_client',
        ));

        // Insert application row
        $wpdb->insert(
            DCO_Database::table(),
            array(
                'first_name'   => $first_name,
                'last_name'    => $last_name,
                'email'        => $email,
                'company_name' => $company_name,
                'wp_user_id'   => $user_id,
                'status'       => 'step1_complete',
                'ip_address'   => DCO_Rate_Limiter::get_identifier(),
                'user_agent'   => sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)),
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );

        $application_id = (int) $wpdb->insert_id;

        if (!$application_id) {
            // Roll back user creation
            wp_delete_user($user_id);
            wp_send_json_error(array('message' => 'Could not create application. Please try again. / No se pudo crear la solicitud.'));
            return;
        }

        // Log the user in automatically so Step 2 AJAX works
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, false);

        wp_send_json_success(array(
            'application_id' => $application_id,
            'nonce_step2'    => wp_create_nonce('dco_register_step2'),
        ));
    }

    // =========================================================================
    // Step 2 — Collect trial needs + run AI evaluation
    // =========================================================================

    public static function handle_step2(): void {
        self::verify_nonce('dco_register_step2');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in. / Debe iniciar sesión.'));
            return;
        }

        $application_id = (int) ($_POST['application_id'] ?? 0);
        $app = self::get_application_for_current_user($application_id);
        if (!$app) {
            wp_send_json_error(array('message' => 'Application not found. / Solicitud no encontrada.'));
            return;
        }

        // Sanitize step 2 fields
        $trial_type           = sanitize_text_field($_POST['trial_type'] ?? '');
        $target_population    = array_map('sanitize_text_field', (array) ($_POST['target_population'] ?? array()));
        $budget_min           = intval($_POST['budget_min'] ?? 0);
        $budget_max           = intval($_POST['budget_max'] ?? 0);
        $enrollment_goal      = intval($_POST['enrollment_goal'] ?? 0);
        $timeline_value       = intval($_POST['timeline_value'] ?? 0);
        $timeline_unit        = ($_POST['timeline_unit'] ?? 'months') === 'days' ? 'days' : 'months';
        $timeline_label       = $timeline_value > 0 ? $timeline_value . ' ' . $timeline_unit : '';
        $timeline_months      = ($timeline_unit === 'days') ? (int) ceil($timeline_value / 30) : $timeline_value;
        $organization_type    = sanitize_text_field($_POST['organization_type'] ?? '');
        $organization_website = esc_url_raw($_POST['organization_website'] ?? '');
        $additional_notes     = sanitize_textarea_field($_POST['additional_notes'] ?? '');

        // Campaign location
        $campaign_country_code  = sanitize_text_field($_POST['campaign_country'] ?? '');
        $campaign_country_other = sanitize_text_field($_POST['campaign_country_other'] ?? '');
        $campaign_regions_raw   = array_map('sanitize_text_field', (array) ($_POST['campaign_regions'] ?? array()));
        $campaign_regions       = array_values(array_filter($campaign_regions_raw));
        $location_code          = sanitize_text_field($_POST['location_code'] ?? '');

        $country_names = array(
            'US' => 'United States', 'MX' => 'Mexico', 'CO' => 'Colombia',
            'PR' => 'Puerto Rico',   'DO' => 'Dominican Republic',
            'AR' => 'Argentina',     'ES' => 'Spain',
        );
        $campaign_country_name = ($campaign_country_code === 'OTHER')
            ? ($campaign_country_other ?: 'Other')
            : ($country_names[$campaign_country_code] ?? $campaign_country_code);

        $campaign_location = wp_json_encode(array(
            'country_code' => $campaign_country_code,
            'country'      => $campaign_country_name,
            'regions'      => $campaign_regions,
            'state'        => $location_code,
        ));

        // Compose a human-readable budget range string for storage and AI context
        if ($budget_min > 0 && $budget_max > 0) {
            $estimated_budget = '$' . number_format($budget_min) . ' – $' . number_format($budget_max);
        } elseif ($budget_min > 0) {
            $estimated_budget = 'From $' . number_format($budget_min);
        } elseif ($budget_max > 0) {
            $estimated_budget = 'Up to $' . number_format($budget_max);
        } else {
            $estimated_budget = '';
        }

        if (empty($trial_type) || empty($estimated_budget) || empty($organization_type) || empty($campaign_country_code)) {
            wp_send_json_error(array('message' => 'Required fields are missing. / Faltan campos obligatorios.'));
            return;
        }

        global $wpdb;

        // Update the application row with step 2 data
        $wpdb->update(
            DCO_Database::table(),
            array(
                'trial_type'           => $trial_type,
                'target_population'    => wp_json_encode($target_population),
                'estimated_budget'     => $estimated_budget,
                'enrollment_goal'      => $enrollment_goal ?: null,
                'timeline_months'      => $timeline_months ?: null,
                'organization_type'    => $organization_type,
                'organization_website' => $organization_website,
                'additional_notes'     => $additional_notes,
                'campaign_location'    => $campaign_location,
                'status'               => 'pending_ai',
            ),
            array('id' => $application_id),
            array('%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );

        // Call OpenAI synchronously
        $ai_client = new DCO_OpenAI_Client();
        $result = $ai_client->evaluate_application(array(
            'company_name'         => $app->company_name,
            'organization_type'    => $organization_type,
            'organization_website' => $organization_website,
            'trial_type'           => $trial_type,
            'target_population'    => $target_population,
            'enrollment_goal'      => $enrollment_goal,
            'timeline_months'      => $timeline_months,
            'timeline_label'       => $timeline_label,
            'estimated_budget'     => $estimated_budget,
            'additional_notes'     => $additional_notes,
            'campaign_country'     => $campaign_country_name,
            'campaign_regions'     => $campaign_regions,
        ));

        $qualified = (bool) $result['qualified'];
        $status    = $qualified ? 'ai_qualified' : 'ai_rejected';

        $update_data = array(
            'ai_qualified'     => $qualified ? 1 : 0,
            'ai_score'         => $result['score'],
            'ai_reasoning_es'  => $result['reasoning_es'],
            'ai_reasoning_en'  => $result['reasoning_en'],
            'ai_raw_response'  => $result['raw'],
            'ai_evaluated_at'  => current_time('mysql'),
            'status'           => $status,
        );

        $token = null;
        if ($qualified) {
            $token = DCO_Qualification_Token::generate($application_id);
            $update_data['qualification_token'] = $token;
        }

        $wpdb->update(
            DCO_Database::table(),
            $update_data,
            array('id' => $application_id),
            null,
            array('%d')
        );

        $response = array(
            'status'       => $qualified ? 'qualified' : 'rejected',
            'reasoning_es' => $result['reasoning_es'],
            'reasoning_en' => $result['reasoning_en'],
            'score'        => $result['score'],
        );

        if ($qualified && $token) {
            $response['token']          = $token;
            $response['application_id'] = $application_id;
            $response['nonce_stripe']   = wp_create_nonce('dco_create_stripe_session');
        } else {
            // Include actionable suggestions so the client can revise and resubmit
            $response['suggestions']    = $result['suggestions'] ?? array();
            $response['nonce_step2']    = wp_create_nonce('dco_register_step2');
        }

        wp_send_json_success($response);
    }

    // =========================================================================
    // AI Status poll (fallback for slow connections)
    // =========================================================================

    public static function handle_check_ai_status(): void {
        self::verify_nonce('dco_check_ai_status');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Not logged in.'));
            return;
        }

        $application_id = (int) ($_POST['application_id'] ?? 0);
        $app = self::get_application_for_current_user($application_id);

        if (!$app) {
            wp_send_json_error(array('message' => 'Not found.'));
            return;
        }

        if ($app->status === 'pending_ai') {
            wp_send_json_success(array('status' => 'pending'));
            return;
        }

        $response = array(
            'status'       => in_array($app->status, array('ai_qualified', 'payment_pending', 'payment_complete'), true) ? 'qualified' : 'rejected',
            'reasoning_es' => $app->ai_reasoning_es,
            'reasoning_en' => $app->ai_reasoning_en,
            'score'        => $app->ai_score,
        );

        if ($response['status'] === 'qualified' && !empty($app->qualification_token)) {
            $response['token']          = $app->qualification_token;
            $response['application_id'] = $application_id;
            $response['nonce_stripe']   = wp_create_nonce('dco_create_stripe_session');
        }

        wp_send_json_success($response);
    }

    // =========================================================================
    // Create Stripe Checkout Session
    // =========================================================================

    public static function handle_create_stripe_session(): void {
        self::verify_nonce('dco_create_stripe_session');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Not logged in. / No ha iniciado sesión.'));
            return;
        }

        $application_id = (int) ($_POST['application_id'] ?? 0);
        $token          = sanitize_text_field($_POST['token'] ?? '');
        $payment_method = sanitize_text_field($_POST['payment_method'] ?? 'card');
        $package_tier   = sanitize_text_field($_POST['package_tier']   ?? 'basic');

        // Whitelist payment method
        if (!in_array($payment_method, array('card', 'us_bank_account'), true)) {
            $payment_method = 'card';
        }

        // Whitelist package tier
        if (!in_array($package_tier, array('basic', 'standard', 'pro'), true)) {
            $package_tier = 'basic';
        }

        // Validate token
        if (!DCO_Qualification_Token::validate($token, $application_id)) {
            wp_send_json_error(array('message' => 'Your qualification token is invalid or has expired. Please contact us. / Su token de calificación no es válido o ha expirado.'));
            return;
        }

        $app = self::get_application_for_current_user($application_id);
        if (!$app || $app->ai_qualified != 1) {
            wp_send_json_error(array('message' => 'This application has not been qualified.'));
            return;
        }

        // Don't create a duplicate session if one already exists
        if (!empty($app->stripe_session_id) && $app->status === 'payment_pending') {
            wp_send_json_success(array('checkout_url' => $app->stripe_session_url));
            return;
        }

        try {
            $stripe  = new DCO_Stripe_Client();
            $session = $stripe->create_checkout_session(array(
                'application_id' => $application_id,
                'user_email'     => $app->email,
                'company_name'   => $app->company_name,
                'payment_method' => $payment_method,
                'package_tier'   => $package_tier,
            ));

            wp_send_json_success(array('checkout_url' => $session['url']));

        } catch (Exception $e) {
            error_log('[DCO] Stripe session creation failed: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Could not initiate payment. Please try again or contact us. / No se pudo iniciar el pago.'));
        }
    }

    // =========================================================================
    // New Campaign — provisioned client starts a fresh application
    // =========================================================================

    public static function handle_start_new_campaign(): void {
        self::verify_nonce('dco_start_new_campaign');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in. / Debe iniciar sesión.'));
            return;
        }

        $user = wp_get_current_user();
        if (!in_array('client', (array) $user->roles, true)) {
            wp_send_json_error(array('message' => 'Access denied. / Acceso denegado.'));
            return;
        }

        // Pull account details from the most recent application row
        global $wpdb;
        $last_app = $wpdb->get_row($wpdb->prepare(
            "SELECT first_name, last_name, email, company_name FROM " . DCO_Database::table() . " WHERE wp_user_id = %d ORDER BY id DESC LIMIT 1",
            (int) $user->ID
        ));

        $first_name   = sanitize_text_field($last_app->first_name ?? $user->first_name);
        $last_name    = sanitize_text_field($last_app->last_name  ?? $user->last_name);
        $email        = sanitize_email($user->user_email);
        $company_name = sanitize_text_field($last_app->company_name ?? '');

        $wpdb->insert(
            DCO_Database::table(),
            array(
                'first_name'   => $first_name,
                'last_name'    => $last_name,
                'email'        => $email,
                'company_name' => $company_name,
                'wp_user_id'   => (int) $user->ID,
                'status'       => 'step1_complete',
                'ip_address'   => DCO_Rate_Limiter::get_identifier(),
                'user_agent'   => sanitize_text_field(substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)),
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );

        $application_id = (int) $wpdb->insert_id;

        if (!$application_id) {
            wp_send_json_error(array('message' => 'Could not create campaign. Please try again. / No se pudo crear la campaña.'));
            return;
        }

        wp_send_json_success(array(
            'application_id' => $application_id,
            'nonce_step2'    => wp_create_nonce('dco_register_step2'),
        ));
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private static function verify_nonce(string $action): void {
        if (!check_ajax_referer($action, 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed. / Verificación de seguridad fallida.', 'code' => 403));
            wp_die();
        }
    }

    /**
     * Returns the application row only if it belongs to the current logged-in user.
     * Prevents IDOR attacks.
     */
    private static function get_application_for_current_user(int $application_id): ?object {
        if (!$application_id || !is_user_logged_in()) {
            return null;
        }

        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . DCO_Database::table() . " WHERE id = %d AND wp_user_id = %d",
            $application_id,
            get_current_user_id()
        ));
    }
}
