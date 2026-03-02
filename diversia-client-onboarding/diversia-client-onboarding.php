<?php
/**
 * Plugin Name: Diversia Client Onboarding
 * Plugin URI: https://diversiahealth.com
 * Description: Multi-step client registration with AI qualification (OpenAI GPT-4o) and Stripe payment gating. Only qualified clients gain access to payment and are provisioned as active clients.
 * Version: 1.3.0
 * Author: Jimmy
 * Author URI: mailto:Jimmy@alternaagancy.com
 * License: GPL v2 or later
 * Text Domain: diversia-client-onboarding
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('DCO_VERSION',     '1.3.0');
define('DCO_PLUGIN_DIR',  plugin_dir_path(__FILE__));
define('DCO_PLUGIN_URL',  plugin_dir_url(__FILE__));
define('DCO_TEXT_DOMAIN', 'diversia-client-onboarding');

// Load all class files
require_once DCO_PLUGIN_DIR . 'includes/class-database.php';
require_once DCO_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once DCO_PLUGIN_DIR . 'includes/class-applications-admin.php';
require_once DCO_PLUGIN_DIR . 'includes/class-ai-parameters.php';
require_once DCO_PLUGIN_DIR . 'includes/class-rate-limiter.php';
require_once DCO_PLUGIN_DIR . 'includes/class-qualification-token.php';
require_once DCO_PLUGIN_DIR . 'includes/class-openai-client.php';
require_once DCO_PLUGIN_DIR . 'includes/class-meta-client.php';
require_once DCO_PLUGIN_DIR . 'includes/class-stripe-client.php';
require_once DCO_PLUGIN_DIR . 'includes/class-stripe-webhook.php';
require_once DCO_PLUGIN_DIR . 'includes/class-client-provisioner.php';
require_once DCO_PLUGIN_DIR . 'includes/class-ajax-handlers.php';
require_once DCO_PLUGIN_DIR . 'includes/class-shortcodes.php';

class Diversia_Client_Onboarding {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init',               array($this, 'init'));
        add_action('admin_init',         array('DCO_Database',          'check_version'));
        add_action('admin_init',         array('DCO_Admin_Settings',    'init'));
        add_action('admin_init',         array('DCO_Applications_Admin','init'));
        add_action('admin_init',         array('DCO_AI_Parameters',     'init'));
        add_action('rest_api_init',      array('DCO_Stripe_Webhook',    'register_route'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Register pending_client role on init so it exists before any user creation
        add_action('init', array($this, 'register_pending_client_role'));

        // Redirect provisioned clients to the trial dashboard after login
        add_filter('login_redirect', array($this, 'redirect_client_after_login'), 10, 3);
    }

    public function init() {
        DCO_Shortcodes::register();
        DCO_Ajax_Handlers::register();
    }

    /**
     * Redirects provisioned clients to /trial-dashboard/ after login.
     * Admins and other roles follow the default WP redirect.
     */
    public function redirect_client_after_login(string $redirect_to, string $requested_redirect_to, $user): string {
        if ($user instanceof WP_User && in_array('client', (array) $user->roles, true)) {
            return home_url('/trial-dashboard/');
        }
        return $redirect_to;
    }

    public function register_pending_client_role() {
        if (!get_role('pending_client')) {
            add_role('pending_client', 'Pending Client', array(
                'read' => true,
            ));
        }
    }

    public function enqueue_frontend_assets() {
        // Only enqueue on pages that use our shortcodes
        global $post;
        if (!is_a($post, 'WP_Post')) {
            return;
        }

        $has_shortcode = has_shortcode($post->post_content, 'diversia_client_registration')
                      || has_shortcode($post->post_content, 'diversia_payment_gate');

        if (!$has_shortcode) {
            return;
        }

        wp_enqueue_style(
            'dco-frontend',
            DCO_PLUGIN_URL . 'assets/css/dco-frontend.css',
            array(),
            DCO_VERSION
        );

        wp_enqueue_script(
            'dco-frontend',
            DCO_PLUGIN_URL . 'assets/js/dco-frontend.js',
            array('jquery'),
            DCO_VERSION,
            true
        );

        wp_localize_script('dco-frontend', 'dcoData', array(
            'ajaxurl'        => admin_url('admin-ajax.php'),
            'nonce_step1'    => wp_create_nonce('dco_register_step1'),
            'nonce_step2'    => wp_create_nonce('dco_register_step2'),
            'nonce_status'   => wp_create_nonce('dco_check_ai_status'),
            'nonce_stripe'   => wp_create_nonce('dco_create_stripe_session'),
            'is_logged_in'   => is_user_logged_in() ? 1 : 0,
        ));
    }
}

// Activation / deactivation hooks
register_activation_hook(__FILE__,   array('DCO_Database', 'activate'));
register_deactivation_hook(__FILE__, array('DCO_Database', 'deactivate'));

// Boot the plugin
add_action('plugins_loaded', array('Diversia_Client_Onboarding', 'get_instance'));
