<?php
if (!defined('ABSPATH')) exit;

class DCO_Admin_Settings {

    // Option keys
    const OPT_OPENAI_API_KEY           = 'dco_openai_api_key';
    const OPT_OPENAI_MODEL             = 'dco_openai_model';
    const OPT_STRIPE_SECRET_KEY        = 'dco_stripe_secret_key';
    const OPT_STRIPE_PUBLISHABLE_KEY   = 'dco_stripe_publishable_key';
    const OPT_STRIPE_WEBHOOK_SECRET    = 'dco_stripe_webhook_secret';
    const OPT_STRIPE_PRICE_ID          = 'dco_stripe_price_id';
    const OPT_STRIPE_SUCCESS_URL       = 'dco_stripe_success_url';
    const OPT_STRIPE_CANCEL_URL        = 'dco_stripe_cancel_url';
    const OPT_QUALIFICATION_CRITERIA   = 'dco_qualification_criteria';
    const OPT_MIN_BUDGET_THRESHOLD     = 'dco_min_budget_threshold';
    const OPT_ALLOWED_ORG_TYPES        = 'dco_allowed_org_types';
    const OPT_ENROLLMENT_TIMELINE      = 'dco_enrollment_timeline';
    const OPT_TOKEN_TTL_HOURS          = 'dco_token_ttl_hours';
    const OPT_RATE_LIMIT_MAX           = 'dco_rate_limit_max';
    const OPT_ADMIN_NOTIFICATION_EMAIL = 'dco_admin_notification_email';
    const OPT_META_ACCESS_TOKEN        = 'dco_meta_access_token';
    const OPT_META_APP_ID              = 'dco_meta_app_id';

    public static function init(): void {
        add_action('admin_menu',  array(__CLASS__, 'add_settings_page'));
        add_action('admin_init',  array(__CLASS__, 'register_settings'));
        add_action('admin_post_dco_test_openai_connection', array(__CLASS__, 'handle_test_openai_connection'));
        add_action('admin_post_dco_test_meta_connection',   array(__CLASS__, 'handle_test_meta_connection'));
    }

    public static function add_settings_page(): void {
        add_submenu_page(
            'options-general.php',
            'Client Onboarding',
            'Client Onboarding',
            'manage_options',
            'dco-settings',
            array(__CLASS__, 'render_settings_page')
        );
    }

    public static function register_settings(): void {
        // Section: API Keys
        add_settings_section('dco_api_keys', 'API Keys', array(__CLASS__, 'render_section_api_keys'), 'dco-settings');
        register_setting('dco_settings_group', self::OPT_OPENAI_API_KEY,         array('sanitize_callback' => array(__CLASS__, 'sanitize_api_key')));
        register_setting('dco_settings_group', self::OPT_OPENAI_MODEL,            array('sanitize_callback' => 'sanitize_text_field'));
        add_settings_field(self::OPT_OPENAI_API_KEY,        'OpenAI API Key',  array(__CLASS__, 'render_field_openai_api_key'),  'dco-settings', 'dco_api_keys');
        add_settings_field(self::OPT_OPENAI_MODEL,           'OpenAI Model',    array(__CLASS__, 'render_field_openai_model'),    'dco-settings', 'dco_api_keys');

        // Section: Stripe
        add_settings_section('dco_stripe', 'Stripe Configuration', array(__CLASS__, 'render_section_stripe'), 'dco-settings');
        register_setting('dco_settings_group', self::OPT_STRIPE_SECRET_KEY,      array('sanitize_callback' => array(__CLASS__, 'sanitize_api_key')));
        register_setting('dco_settings_group', self::OPT_STRIPE_PUBLISHABLE_KEY, array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('dco_settings_group', self::OPT_STRIPE_WEBHOOK_SECRET,  array('sanitize_callback' => array(__CLASS__, 'sanitize_api_key')));
        register_setting('dco_settings_group', self::OPT_STRIPE_PRICE_ID,        array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('dco_settings_group', self::OPT_STRIPE_SUCCESS_URL,     array('sanitize_callback' => 'esc_url_raw'));
        register_setting('dco_settings_group', self::OPT_STRIPE_CANCEL_URL,      array('sanitize_callback' => 'esc_url_raw'));
        add_settings_field(self::OPT_STRIPE_SECRET_KEY,      'Secret Key',        array(__CLASS__, 'render_field_stripe_secret_key'),      'dco-settings', 'dco_stripe');
        add_settings_field(self::OPT_STRIPE_PUBLISHABLE_KEY, 'Publishable Key',   array(__CLASS__, 'render_field_stripe_publishable_key'), 'dco-settings', 'dco_stripe');
        add_settings_field(self::OPT_STRIPE_WEBHOOK_SECRET,  'Webhook Secret',    array(__CLASS__, 'render_field_stripe_webhook_secret'),  'dco-settings', 'dco_stripe');
        add_settings_field(self::OPT_STRIPE_PRICE_ID,        'Price ID',          array(__CLASS__, 'render_field_stripe_price_id'),        'dco-settings', 'dco_stripe');
        add_settings_field(self::OPT_STRIPE_SUCCESS_URL,     'Success URL',       array(__CLASS__, 'render_field_stripe_success_url'),     'dco-settings', 'dco_stripe');
        add_settings_field(self::OPT_STRIPE_CANCEL_URL,      'Cancel URL',        array(__CLASS__, 'render_field_stripe_cancel_url'),      'dco-settings', 'dco_stripe');

        // Section: Qualification
        add_settings_section('dco_qualification', 'Qualification Criteria', array(__CLASS__, 'render_section_qualification'), 'dco-settings');
        register_setting('dco_settings_group', self::OPT_QUALIFICATION_CRITERIA, array('sanitize_callback' => 'sanitize_textarea_field'));
        register_setting('dco_settings_group', self::OPT_MIN_BUDGET_THRESHOLD,   array('sanitize_callback' => 'intval'));
        register_setting('dco_settings_group', self::OPT_ALLOWED_ORG_TYPES,      array('sanitize_callback' => array(__CLASS__, 'sanitize_json_field')));
        register_setting('dco_settings_group', self::OPT_ENROLLMENT_TIMELINE,    array('sanitize_callback' => array(__CLASS__, 'sanitize_json_field')));
        add_settings_field(self::OPT_MIN_BUDGET_THRESHOLD,   'Minimum Budget Threshold',    array(__CLASS__, 'render_field_min_budget_threshold'),    'dco-settings', 'dco_qualification');
        add_settings_field(self::OPT_ENROLLMENT_TIMELINE,    'Timeline by Enrollment Goal', array(__CLASS__, 'render_field_enrollment_timeline'),     'dco-settings', 'dco_qualification');
        add_settings_field(self::OPT_QUALIFICATION_CRITERIA, 'Additional AI Instructions',  array(__CLASS__, 'render_field_qualification_criteria'), 'dco-settings', 'dco_qualification');
        add_settings_field(self::OPT_ALLOWED_ORG_TYPES,      'Allowed Organization Types',  array(__CLASS__, 'render_field_allowed_org_types'),       'dco-settings', 'dco_qualification');

        // Section: Meta Integration
        add_settings_section('dco_meta', 'Meta Integration', array(__CLASS__, 'render_section_meta'), 'dco-settings');
        register_setting('dco_settings_group', self::OPT_META_ACCESS_TOKEN, array('sanitize_callback' => array(__CLASS__, 'sanitize_api_key')));
        register_setting('dco_settings_group', self::OPT_META_APP_ID,       array('sanitize_callback' => 'sanitize_text_field'));
        add_settings_field(self::OPT_META_ACCESS_TOKEN, 'Access Token',  array(__CLASS__, 'render_field_meta_access_token'), 'dco-settings', 'dco_meta');
        add_settings_field(self::OPT_META_APP_ID,       'App ID',        array(__CLASS__, 'render_field_meta_app_id'),       'dco-settings', 'dco_meta');

        // Section: Security & Notifications
        add_settings_section('dco_security', 'Security & Notifications', array(__CLASS__, 'render_section_security'), 'dco-settings');
        register_setting('dco_settings_group', self::OPT_TOKEN_TTL_HOURS,          array('sanitize_callback' => 'intval'));
        register_setting('dco_settings_group', self::OPT_RATE_LIMIT_MAX,           array('sanitize_callback' => 'intval'));
        register_setting('dco_settings_group', self::OPT_ADMIN_NOTIFICATION_EMAIL, array('sanitize_callback' => 'sanitize_email'));
        add_settings_field(self::OPT_TOKEN_TTL_HOURS,          'Token TTL (hours)',      array(__CLASS__, 'render_field_token_ttl_hours'),          'dco-settings', 'dco_security');
        add_settings_field(self::OPT_RATE_LIMIT_MAX,           'Rate Limit (per hour)',  array(__CLASS__, 'render_field_rate_limit_max'),           'dco-settings', 'dco_security');
        add_settings_field(self::OPT_ADMIN_NOTIFICATION_EMAIL, 'Info / Notification Email', array(__CLASS__, 'render_field_admin_notification_email'), 'dco-settings', 'dco_security');
    }

    public static function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        $test_status      = sanitize_key($_GET['dco_openai_test'] ?? '');
        $test_msg         = sanitize_text_field(wp_unslash($_GET['dco_openai_msg'] ?? ''));
        $meta_test_status = sanitize_key($_GET['dco_meta_test'] ?? '');
        $meta_test_msg    = sanitize_text_field(wp_unslash($_GET['dco_meta_msg'] ?? ''));
        ?>
        <div class="wrap">
            <h1>Client Onboarding Settings</h1>
            <?php if ($test_status && $test_msg): ?>
                <div class="notice notice-<?php echo $test_status === 'success' ? 'success' : 'error'; ?> is-dismissible">
                    <p><?php echo esc_html($test_msg); ?></p>
                </div>
            <?php endif; ?>
            <?php if ($meta_test_status && $meta_test_msg): ?>
                <div class="notice notice-<?php echo $meta_test_status === 'success' ? 'success' : 'error'; ?> is-dismissible">
                    <p><?php echo esc_html($meta_test_msg); ?></p>
                </div>
            <?php endif; ?>
            <p>
                <a href="<?php echo esc_url(admin_url('options-general.php?page=dco-ai-parameters')); ?>"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#2e86ab;color:#fff;border-radius:5px;text-decoration:none;font-weight:600;font-size:13px;">
                    ⚙ Configure AI Qualification Parameters →
                </a>
                <a href="<?php echo esc_url(admin_url('options-general.php?page=dco-applications')); ?>"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#1b3a5c;color:#fff;border-radius:5px;text-decoration:none;font-weight:600;font-size:13px;margin-left:8px;">
                    📊 View Client Applications →
                </a>
                <span style="margin-left:10px;color:#666;font-size:13px;">Set the criteria the AI uses to evaluate all client applications.</span>
            </p>
            <hr style="margin:16px 0 24px;">
            <form method="post" action="options.php">
                <?php
                settings_fields('dco_settings_group');
                do_settings_sections('dco-settings');
                submit_button('Save Settings');
                ?>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;display:inline-block;margin-right:16px;">
                <?php wp_nonce_field('dco_test_openai_connection'); ?>
                <input type="hidden" name="action" value="dco_test_openai_connection">
                <?php submit_button('Test OpenAI Connection', 'secondary', 'submit', false); ?>
                <span class="description" style="margin-left:8px;">Tests the currently saved OpenAI API key and model.</span>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;display:inline-block;">
                <?php wp_nonce_field('dco_test_meta_connection'); ?>
                <input type="hidden" name="action" value="dco_test_meta_connection">
                <?php submit_button('Test Meta Connection', 'secondary', 'submit', false); ?>
                <span class="description" style="margin-left:8px;">Tests the currently saved Meta access token.</span>
            </form>
            <hr>
            <h2>Webhook Endpoint</h2>
            <p>Configure this URL in your Stripe Dashboard under <strong>Developers → Webhooks</strong>:</p>
            <code><?php echo esc_url(rest_url('dco/v1/stripe-webhook')); ?></code>
            <p>Event to listen for: <strong>checkout.session.completed</strong></p>
        </div>
        <?php
    }

    public static function handle_test_openai_connection(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('dco_test_openai_connection');

        $client = new DCO_OpenAI_Client();
        $result = $client->test_connection();

        wp_safe_redirect(add_query_arg(array(
            'page'            => 'dco-settings',
            'dco_openai_test' => $result['success'] ? 'success' : 'error',
            'dco_openai_msg'  => $result['message'],
        ), admin_url('options-general.php')));
        exit;
    }

    public static function handle_test_meta_connection(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer('dco_test_meta_connection');

        $client = new DCO_Meta_Client();
        $result = $client->test_connection();

        wp_safe_redirect(add_query_arg(array(
            'page'           => 'dco-settings',
            'dco_meta_test'  => $result['success'] ? 'success' : 'error',
            'dco_meta_msg'   => $result['message'],
        ), admin_url('options-general.php')));
        exit;
    }

    // --- Section descriptions ---

    public static function render_section_api_keys(): void {
        echo '<p>Enter your OpenAI API credentials for AI qualification.</p>';
    }

    public static function render_section_stripe(): void {
        echo '<p>Enter your Stripe credentials. Use test keys during development.</p>';
    }

    public static function render_section_qualification(): void {
        echo '<p>Configure the criteria the AI uses to evaluate client applications.</p>';
    }

    public static function render_section_security(): void {
        echo '<p>Security settings and admin notification preferences.</p>';
    }

    public static function render_section_meta(): void {
        echo '<p>Configure your Meta (Facebook) access token to automatically sync lead counts from Meta Lead Gen Forms into the <a href="' . esc_url(admin_url('options-general.php?page=dco-applications')) . '">Client Applications</a> dashboard.</p>';
        echo '<p class="description">You need a <strong>long-lived System User token</strong> or <strong>Page Access Token</strong> with <code>ads_read</code> and <code>leads_retrieval</code> permissions. Obtain this from your <a href="https://business.facebook.com/settings/system-users" target="_blank" rel="noopener">Meta Business Settings → System Users</a>.</p>';
    }

    // --- Field renderers ---

    public static function render_field_openai_api_key(): void {
        $val = self::get_option(self::OPT_OPENAI_API_KEY);
        $display = $val ? str_repeat('*', 20) . substr($val, -4) : '';
        echo '<input type="password" name="' . esc_attr(self::OPT_OPENAI_API_KEY) . '" value="' . esc_attr($val) . '" class="regular-text" autocomplete="off" placeholder="sk-proj-...">';
        echo '<p class="description">Your OpenAI API key from platform.openai.com</p>';
    }

    public static function render_field_openai_model(): void {
        $val = self::get_option(self::OPT_OPENAI_MODEL, 'gpt-4o');
        $models = array('gpt-4o' => 'GPT-4o (Recommended)', 'gpt-4o-mini' => 'GPT-4o Mini (Faster, lower cost)');
        echo '<select name="' . esc_attr(self::OPT_OPENAI_MODEL) . '">';
        foreach ($models as $model_id => $label) {
            echo '<option value="' . esc_attr($model_id) . '"' . selected($val, $model_id, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public static function render_field_stripe_secret_key(): void {
        $val = self::get_option(self::OPT_STRIPE_SECRET_KEY);
        echo '<input type="password" name="' . esc_attr(self::OPT_STRIPE_SECRET_KEY) . '" value="' . esc_attr($val) . '" class="regular-text" autocomplete="off" placeholder="sk_live_... or sk_test_...">';
    }

    public static function render_field_stripe_publishable_key(): void {
        $val = self::get_option(self::OPT_STRIPE_PUBLISHABLE_KEY);
        echo '<input type="text" name="' . esc_attr(self::OPT_STRIPE_PUBLISHABLE_KEY) . '" value="' . esc_attr($val) . '" class="regular-text" placeholder="pk_live_... or pk_test_...">';
    }

    public static function render_field_stripe_webhook_secret(): void {
        $val = self::get_option(self::OPT_STRIPE_WEBHOOK_SECRET);
        echo '<input type="password" name="' . esc_attr(self::OPT_STRIPE_WEBHOOK_SECRET) . '" value="' . esc_attr($val) . '" class="regular-text" autocomplete="off" placeholder="whsec_...">';
    }

    public static function render_field_stripe_price_id(): void {
        $val = self::get_option(self::OPT_STRIPE_PRICE_ID);
        echo '<input type="text" name="' . esc_attr(self::OPT_STRIPE_PRICE_ID) . '" value="' . esc_attr($val) . '" class="regular-text" placeholder="price_...">';
        echo '<p class="description">The Stripe Price ID for the client onboarding fee.</p>';
    }

    public static function render_field_stripe_success_url(): void {
        $val = self::get_option(self::OPT_STRIPE_SUCCESS_URL, home_url('/trial-dashboard/'));
        echo '<input type="url" name="' . esc_attr(self::OPT_STRIPE_SUCCESS_URL) . '" value="' . esc_attr($val) . '" class="regular-text">';
        echo '<p class="description">Where to redirect after successful payment (must be a page on your site).</p>';
    }

    public static function render_field_stripe_cancel_url(): void {
        $val = self::get_option(self::OPT_STRIPE_CANCEL_URL, home_url('/client-registration/'));
        echo '<input type="url" name="' . esc_attr(self::OPT_STRIPE_CANCEL_URL) . '" value="' . esc_attr($val) . '" class="regular-text">';
        echo '<p class="description">Where to redirect if the client cancels checkout.</p>';
    }

    public static function render_field_qualification_criteria(): void {
        $val = self::get_option(self::OPT_QUALIFICATION_CRITERIA, '');
        echo '<textarea name="' . esc_attr(self::OPT_QUALIFICATION_CRITERIA) . '" rows="6" cols="60" class="large-text" placeholder="e.g. Require that applicants describe their experience working with bilingual staff. Give extra consideration to trials targeting underserved rural Latino communities. Reject applications that mention tobacco or cannabis...">' . esc_textarea($val) . '</textarea>';
        echo '<p class="description">Write any additional evaluation instructions in plain English. The AI will follow these for every application. No special formatting required.</p>';
    }

    public static function render_field_min_budget_threshold(): void {
        $val = intval(self::get_option(self::OPT_MIN_BUDGET_THRESHOLD, 10000));
        echo '<div style="display:flex;align-items:center;gap:6px;">';
        echo '<span style="font-size:15px;font-weight:600;color:#444;">$</span>';
        echo '<input type="number" name="' . esc_attr(self::OPT_MIN_BUDGET_THRESHOLD) . '" value="' . esc_attr($val) . '" min="0" step="1000" class="regular-text">';
        echo '</div>';
        echo '<p class="description">Applications stating a total budget below this amount are automatically disqualified. Enter a whole number (e.g. <code>50000</code> for $50,000).</p>';
    }

    public static function render_field_enrollment_timeline(): void {
        $default_ranges = array(
            array('min' => 1,    'max' => 50,   'months' => 3),
            array('min' => 51,   'max' => 150,  'months' => 6),
            array('min' => 151,  'max' => 500,  'months' => 12),
            array('min' => 501,  'max' => 1000, 'months' => 18),
            array('min' => 1001, 'max' => 0,    'months' => 24),
        );
        $saved  = json_decode(self::get_option(self::OPT_ENROLLMENT_TIMELINE, '[]'), true);
        $ranges = (!empty($saved) && is_array($saved)) ? $saved : $default_ranges;

        echo '<p class="description" style="margin-bottom:10px;">Set the expected minimum timeline (in months) for each participant count range. The AI uses this to flag unrealistic timelines.</p>';
        echo '<table class="widefat fixed striped" style="max-width:520px;">';
        echo '<thead><tr><th>Participants</th><th>Expected Minimum (months)</th></tr></thead>';
        echo '<tbody>';

        foreach ($ranges as $i => $row):
            $min    = intval($row['min']);
            $max    = intval($row['max']);
            $months = intval($row['months']);
            $range_label = ($max === 0)
                ? esc_html(number_format($min)) . '+ participants'
                : esc_html(number_format($min)) . ' – ' . esc_html(number_format($max)) . ' participants';

            echo '<tr>';
            echo '<td style="vertical-align:middle;">';
            echo '<strong>' . $range_label . '</strong>';
            echo '<input type="hidden" name="' . esc_attr(self::OPT_ENROLLMENT_TIMELINE) . '[' . $i . '][min]" value="' . esc_attr($min) . '">';
            echo '<input type="hidden" name="' . esc_attr(self::OPT_ENROLLMENT_TIMELINE) . '[' . $i . '][max]" value="' . esc_attr($max) . '">';
            echo '</td>';
            echo '<td>';
            echo '<input type="number" name="' . esc_attr(self::OPT_ENROLLMENT_TIMELINE) . '[' . $i . '][months]" value="' . esc_attr($months) . '" min="1" max="240" style="width:80px;"> months';
            echo '</td>';
            echo '</tr>';
        endforeach;

        echo '</tbody></table>';
    }

    public static function render_field_allowed_org_types(): void {
        $saved   = json_decode(self::get_option(self::OPT_ALLOWED_ORG_TYPES, '[]'), true) ?: array();
        $options = array('Pharma', 'CRO', 'Academic Institution', 'Hospital/Healthcare System', 'Biotech', 'Government Agency', 'Other');
        echo '<fieldset>';
        foreach ($options as $opt) {
            $checked = in_array($opt, $saved, true) ? 'checked' : '';
            echo '<label style="display:block;margin-bottom:4px;">';
            echo '<input type="checkbox" name="' . esc_attr(self::OPT_ALLOWED_ORG_TYPES) . '[]" value="' . esc_attr($opt) . '" ' . $checked . '> ';
            echo esc_html($opt);
            echo '</label>';
        }
        echo '</fieldset>';
        echo '<p class="description">Organization types that are eligible to apply. If none selected, all types are allowed.</p>';
    }

    public static function render_field_token_ttl_hours(): void {
        $val = (int) self::get_option(self::OPT_TOKEN_TTL_HOURS, 24);
        echo '<input type="number" name="' . esc_attr(self::OPT_TOKEN_TTL_HOURS) . '" value="' . esc_attr($val) . '" min="1" max="168" class="small-text"> hours';
        echo '<p class="description">How long a qualification token remains valid (1–168 hours).</p>';
    }

    public static function render_field_rate_limit_max(): void {
        $val = (int) self::get_option(self::OPT_RATE_LIMIT_MAX, 5);
        echo '<input type="number" name="' . esc_attr(self::OPT_RATE_LIMIT_MAX) . '" value="' . esc_attr($val) . '" min="1" max="100" class="small-text"> attempts per hour';
    }

    public static function render_field_admin_notification_email(): void {
        $val = self::get_option(self::OPT_ADMIN_NOTIFICATION_EMAIL, 'info@diversiahealth.com');
        echo '<input type="email" name="' . esc_attr(self::OPT_ADMIN_NOTIFICATION_EMAIL) . '" value="' . esc_attr($val) . '" class="regular-text">';
        echo '<p class="description">New application notifications and alerts will be sent to this address.</p>';
    }

    public static function render_field_meta_access_token(): void {
        $val = self::get_option(self::OPT_META_ACCESS_TOKEN, '');
        echo '<input type="password" name="' . esc_attr(self::OPT_META_ACCESS_TOKEN) . '" value="' . esc_attr($val) . '" class="regular-text" autocomplete="off" placeholder="EAAxxxxxxx...">';
        echo '<p class="description">Long-lived Meta access token with <code>ads_read</code> and <code>leads_retrieval</code> permissions. Generate from Meta Business Settings → System Users.</p>';
    }

    public static function render_field_meta_app_id(): void {
        $val = self::get_option(self::OPT_META_APP_ID, '');
        echo '<input type="text" name="' . esc_attr(self::OPT_META_APP_ID) . '" value="' . esc_attr($val) . '" class="regular-text" placeholder="1234567890123456">';
        echo '<p class="description">Your Meta App ID (optional — for reference and future webhook setup).</p>';
    }

    // --- Sanitization ---

    public static function sanitize_api_key(string $value): string {
        return trim(sanitize_text_field($value));
    }

    public static function sanitize_json_field($value): string {
        if (is_array($value)) {
            return wp_json_encode($value);
        }
        $decoded = json_decode($value);
        if ($decoded === null && $value !== 'null' && $value !== '') {
            add_settings_error('dco_settings_group', 'invalid_json', 'Custom criteria must be valid JSON.');
            return '{}';
        }
        return sanitize_textarea_field($value);
    }

    // --- Getter ---

    public static function get_option(string $key, $default = '') {
        $val = get_option($key, $default);
        // Allow overriding with wp-config.php constants, e.g. DCO_OPENAI_API_KEY
        $const = 'DCO_' . strtoupper(str_replace('dco_', '', $key));
        if (defined($const)) {
            $val = constant($const);
        }
        return $val;
    }
}
