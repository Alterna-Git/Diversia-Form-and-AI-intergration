<?php
// Only run if WordPress called this uninstall script
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Remove the plugin table
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}dco_client_applications");

// Remove all plugin options
$options = array(
    'dco_db_version',
    'dco_openai_api_key',
    'dco_openai_model',
    'dco_stripe_secret_key',
    'dco_stripe_publishable_key',
    'dco_stripe_webhook_secret',
    'dco_stripe_price_id',
    'dco_stripe_success_url',
    'dco_stripe_cancel_url',
    'dco_qualification_criteria',
    'dco_min_budget_threshold',
    'dco_allowed_org_types',
    'dco_token_ttl_hours',
    'dco_rate_limit_max',
    'dco_admin_notification_email',
);

foreach ($options as $option) {
    delete_option($option);
}

// Remove the pending_client role
remove_role('pending_client');
