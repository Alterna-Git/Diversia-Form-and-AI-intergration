<?php
/**
 * Recruitment Estimator — WordPress admin page
 *
 * Adds "Recruitment Estimator" under WP Admin → Settings.
 * All analysis logic runs client-side (pure JS/CSS, no AJAX needed).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class DCO_Recruitment_Estimator {

    public static function init(): void {
        add_action( 'admin_menu',            array( __CLASS__, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    public static function add_menu_page(): void {
        add_submenu_page(
            'options-general.php',
            __( 'Recruitment Estimator', 'diversia-client-onboarding' ),
            __( 'Recruitment Estimator', 'diversia-client-onboarding' ),
            'manage_options',
            'dco-estimator',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function enqueue_assets( string $hook ): void {
        if ( $hook !== 'settings_page_dco-estimator' ) return;
        wp_enqueue_style(
            'dco-estimator',
            DCO_PLUGIN_URL . 'assets/css/dco-estimator.css',
            array(),
            DCO_VERSION
        );
        wp_enqueue_script(
            'dco-estimator',
            DCO_PLUGIN_URL . 'assets/js/dco-estimator.js',
            array(),
            DCO_VERSION,
            true
        );
    }

    public static function render_page(): void {
        ?>
        <div class="wrap">
            <div class="dco-estimator-wrap">
                <?php include DCO_PLUGIN_DIR . 'templates/estimator-tool.php'; ?>
            </div>
        </div>
        <?php
    }
}
