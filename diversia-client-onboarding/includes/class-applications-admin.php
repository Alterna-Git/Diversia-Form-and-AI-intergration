<?php
if (!defined('ABSPATH')) exit;

class DCO_Applications_Admin {

    const PAGE_SLUG        = 'dco-applications';
    const ROW_ACTION       = 'dco_meta_row_action';
    const SYNC_ALL_ACTION  = 'dco_sync_all_meta_leads';
    const DELETE_ACTION    = 'dco_delete_application';
    const NOTICE_ARG       = 'dco_apps_notice';
    const NOTICE_MSG_ARG   = 'dco_apps_msg';

    /** Statuses that are eligible for permanent deletion. */
    const DELETABLE_STATUSES = array('closed', 'expired');

    /** Statuses an admin can manually assign via the row form. */
    const ADMIN_SETTABLE_STATUSES = array('closed', 'expired');

    public static function init(): void {
        add_action('admin_menu',                              array(__CLASS__, 'add_submenu_page'));
        add_action('admin_enqueue_scripts',                   array(__CLASS__, 'enqueue_admin_assets'));
        add_action('admin_post_' . self::ROW_ACTION,         array(__CLASS__, 'handle_meta_row_action'));
        add_action('admin_post_' . self::SYNC_ALL_ACTION,    array(__CLASS__, 'handle_sync_all_meta_leads'));
        add_action('admin_post_' . self::DELETE_ACTION,      array(__CLASS__, 'handle_delete_application'));
    }

    public static function add_submenu_page(): void {
        add_submenu_page(
            'options-general.php',
            'Client Applications',
            'Client Applications',
            'manage_options',
            self::PAGE_SLUG,
            array(__CLASS__, 'render_page')
        );
    }

    public static function enqueue_admin_assets(string $hook): void {
        if ($hook !== 'settings_page_' . self::PAGE_SLUG) {
            return;
        }
        wp_enqueue_style(
            'dco-admin',
            DCO_PLUGIN_URL . 'assets/css/dco-admin.css',
            array(),
            DCO_VERSION
        );
    }

    // ---------------------------------------------------------------------------
    // Page render
    // ---------------------------------------------------------------------------

    public static function render_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;

        $applications = $wpdb->get_results(
            "SELECT id, created_at, first_name, last_name, email, company_name, status,
                    ai_score, enrollment_goal, meta_leads, meta_lead_form_id, meta_leads_synced_at
             FROM " . DCO_Database::table() . "
             ORDER BY created_at DESC"
        );

        $notice     = sanitize_key($_GET[self::NOTICE_ARG]    ?? '');
        $notice_msg = sanitize_text_field(wp_unslash($_GET[self::NOTICE_MSG_ARG] ?? ''));
        $summary    = self::build_summary($applications);

        $meta_token_set = (bool) DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_META_ACCESS_TOKEN, '');
        ?>
        <div class="wrap dco-apps-wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                Client Applications
                <?php if ($meta_token_set): ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                        <?php wp_nonce_field(self::SYNC_ALL_ACTION); ?>
                        <input type="hidden" name="action" value="<?php echo esc_attr(self::SYNC_ALL_ACTION); ?>">
                        <button type="submit" class="button button-secondary" style="font-size:13px;">
                            ↻ Sync All from Meta
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=dco-settings#dco_meta')); ?>"
                       class="button button-secondary" style="font-size:13px;">
                        ⚙ Configure Meta API →
                    </a>
                <?php endif; ?>
            </h1>

            <?php self::render_notice($notice, $notice_msg); ?>

            <?php if (!$meta_token_set): ?>
                <div class="notice notice-warning" style="margin-bottom:16px;">
                    <p>
                        <strong>Meta API not configured.</strong>
                        To enable automatic lead syncing, add your Meta access token in
                        <a href="<?php echo esc_url(admin_url('options-general.php?page=dco-settings')); ?>">
                            Settings → Client Onboarding → Meta Integration
                        </a>.
                    </p>
                </div>
            <?php endif; ?>

            <p class="description" style="max-width:900px;">
                Compare each application's AI enrollment target with leads collected via Meta ads.
                Enter the <strong>Meta Lead Form ID</strong> for each client, then click
                <strong>Sync from Meta</strong> to automatically fetch the real lead count.
            </p>

            <div class="dco-apps-summary">
                <div class="dco-apps-card">
                    <span class="dco-apps-card__label">Applications</span>
                    <strong class="dco-apps-card__value"><?php echo esc_html(number_format_i18n($summary['application_count'])); ?></strong>
                </div>
                <div class="dco-apps-card">
                    <span class="dco-apps-card__label">Linked to Meta</span>
                    <strong class="dco-apps-card__value"><?php echo esc_html(number_format_i18n($summary['linked_count'])); ?></strong>
                </div>
                <div class="dco-apps-card">
                    <span class="dco-apps-card__label">Synced from API</span>
                    <strong class="dco-apps-card__value"><?php echo esc_html(number_format_i18n($summary['synced_count'])); ?></strong>
                </div>
                <div class="dco-apps-card">
                    <span class="dco-apps-card__label">Total AI Target</span>
                    <strong class="dco-apps-card__value"><?php echo esc_html(number_format_i18n($summary['ai_total'])); ?></strong>
                </div>
                <div class="dco-apps-card">
                    <span class="dco-apps-card__label">Total Meta Leads</span>
                    <strong class="dco-apps-card__value"><?php echo esc_html(number_format_i18n($summary['meta_total'])); ?></strong>
                </div>
                <div class="dco-apps-card">
                    <span class="dco-apps-card__label">Overall Match</span>
                    <strong class="dco-apps-card__value"><?php echo esc_html($summary['overall_match']); ?></strong>
                </div>
            </div>

            <div class="dco-apps-table-wrap">
                <table class="widefat striped dco-apps-table">
                    <thead>
                        <tr>
                            <th>Created</th>
                            <th>Client</th>
                            <th>Organization</th>
                            <th>Status</th>
                            <th>AI Score</th>
                            <th>AI Target</th>
                            <th>Meta Form ID</th>
                            <th>Meta Leads</th>
                            <th>Last Synced</th>
                            <th>Difference</th>
                            <th>Match %</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="12">No applications found yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <?php
                                $ai_target      = (int) ($app->enrollment_goal ?? 0);
                                $meta_leads     = isset($app->meta_leads) && $app->meta_leads !== '' ? (int) $app->meta_leads : null;
                                $form_id        = sanitize_text_field($app->meta_lead_form_id ?? '');
                                $synced_at      = $app->meta_leads_synced_at ?? null;
                                $difference     = ($meta_leads !== null && $ai_target > 0) ? ($meta_leads - $ai_target) : null;
                                $match          = ($meta_leads !== null && $ai_target > 0) ? round(($meta_leads / $ai_target) * 100, 1) : null;
                                $client_name    = trim($app->first_name . ' ' . $app->last_name);
                                ?>
                                <tr>
                                    <td><?php echo esc_html(mysql2date(get_option('date_format'), $app->created_at)); ?></td>
                                    <td>
                                        <strong><?php echo esc_html($client_name ?: 'Unknown'); ?></strong><br>
                                        <span class="description"><?php echo esc_html($app->email); ?></span>
                                    </td>
                                    <td><?php echo esc_html($app->company_name); ?></td>
                                    <td><span class="dco-apps-status"><?php echo esc_html(self::format_status($app->status)); ?></span></td>
                                    <td><?php echo $app->ai_score !== null ? esc_html(number_format_i18n((float) $app->ai_score, 1)) . '/100' : '&mdash;'; ?></td>
                                    <td><?php echo $ai_target > 0 ? esc_html(number_format_i18n($ai_target)) : '&mdash;'; ?></td>

                                    <!-- Meta Form ID + Sync controls (single form, two submit buttons) -->
                                    <td colspan="3">
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="dco-apps-meta-form">
                                            <?php wp_nonce_field('dco_meta_row_' . $app->id); ?>
                                            <input type="hidden" name="action" value="<?php echo esc_attr(self::ROW_ACTION); ?>">
                                            <input type="hidden" name="application_id" value="<?php echo esc_attr((string) $app->id); ?>">

                                            <div class="dco-apps-meta-inputs">
                                                <div>
                                                    <label class="dco-apps-meta-label">Form ID</label>
                                                    <input
                                                        type="text"
                                                        name="meta_lead_form_id"
                                                        value="<?php echo esc_attr($form_id); ?>"
                                                        placeholder="e.g. 1234567890"
                                                        class="dco-apps-form-id-input"
                                                        pattern="[0-9]*"
                                                        title="Numeric Meta Lead Gen Form ID"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="dco-apps-meta-label">Manual override</label>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        name="meta_leads"
                                                        value="<?php echo $meta_leads !== null ? esc_attr((string) $meta_leads) : ''; ?>"
                                                        class="small-text"
                                                        placeholder="0"
                                                        title="Manual lead count (used if no Form ID or Sync)"
                                                    >
                                                </div>
                                                <div>
                                                    <label class="dco-apps-meta-label">Set status</label>
                                                    <select name="app_status" class="dco-apps-status-select">
                                                        <option value="">— no change —</option>
                                                        <option value="closed">Closed</option>
                                                        <option value="expired">Expired</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="dco-apps-meta-actions">
                                                <button type="submit" name="dco_action" value="save"
                                                        class="button button-secondary button-small">
                                                    Save
                                                </button>
                                                <?php if ($meta_token_set): ?>
                                                    <button type="submit" name="dco_action" value="sync"
                                                            class="button button-primary button-small dco-apps-sync-btn"
                                                            <?php echo empty($form_id) ? 'disabled title="Enter a Form ID first"' : ''; ?>>
                                                        ↻ Sync
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($synced_at): ?>
                                                <div class="dco-apps-sync-badge">
                                                    API synced: <?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $synced_at)); ?>
                                                </div>
                                            <?php elseif ($form_id): ?>
                                                <div class="dco-apps-sync-badge dco-apps-sync-badge--pending">
                                                    Form ID saved — not yet synced
                                                </div>
                                            <?php endif; ?>
                                        </form>
                                    </td>

                                    <td class="<?php echo $difference !== null ? ($difference >= 0 ? 'dco-apps-delta dco-apps-delta--positive' : 'dco-apps-delta dco-apps-delta--negative') : ''; ?>">
                                        <?php
                                        if ($difference === null) {
                                            echo '&mdash;';
                                        } else {
                                            echo esc_html(($difference > 0 ? '+' : '') . number_format_i18n($difference));
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $match !== null ? esc_html(number_format_i18n($match, 1)) . '%' : '&mdash;'; ?>
                                    </td>

                                    <!-- Actions: Delete (only for closed / expired) -->
                                    <td class="dco-apps-actions-cell">
                                        <?php if (in_array($app->status, self::DELETABLE_STATUSES, true)): ?>
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                                  onsubmit="return confirm('Permanently delete this application record for <?php echo esc_js($client_name ?: $app->email); ?>?\n\nThis action cannot be undone.');">
                                                <?php wp_nonce_field('dco_delete_' . $app->id); ?>
                                                <input type="hidden" name="action" value="<?php echo esc_attr(self::DELETE_ACTION); ?>">
                                                <input type="hidden" name="application_id" value="<?php echo esc_attr((string) $app->id); ?>">
                                                <button type="submit" class="button button-small dco-apps-delete-btn">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="dco-apps-delete-locked"
                                                  title="Only Closed or Expired campaigns can be deleted. Use the 'Set status' dropdown to close this campaign first.">
                                                &mdash;
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ---------------------------------------------------------------------------
    // Admin-post handlers
    // ---------------------------------------------------------------------------

    /**
     * Handles per-row save or sync.
     * Submit button name="dco_action" value="save"|"sync" determines path.
     */
    public static function handle_meta_row_action(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $application_id = isset($_POST['application_id']) ? (int) $_POST['application_id'] : 0;
        if (!$application_id) {
            wp_die('Invalid application ID.');
        }

        check_admin_referer('dco_meta_row_' . $application_id);

        $dco_action = sanitize_key($_POST['dco_action'] ?? 'save');
        $form_id    = sanitize_text_field(trim(wp_unslash($_POST['meta_lead_form_id'] ?? '')));

        // Validate form_id if provided: must be numeric
        if ($form_id !== '' && !ctype_digit($form_id)) {
            self::redirect_with_notice('meta_error', 'Meta Lead Form ID must be a numeric string.');
            return;
        }

        $raw_manual  = trim(wp_unslash($_POST['meta_leads'] ?? ''));
        $meta_leads  = $raw_manual === '' ? null : max(0, (int) $raw_manual);

        global $wpdb;
        $table = DCO_Database::table();

        if ($dco_action === 'sync') {
            if (empty($form_id)) {
                self::redirect_with_notice('meta_error', 'Please enter a Meta Lead Form ID before syncing.');
                return;
            }

            $client = new DCO_Meta_Client();
            $result = $client->fetch_lead_count($form_id);

            if (is_wp_error($result)) {
                error_log('[DCO Meta] Sync error app ' . $application_id . ': ' . $result->get_error_message());
                self::redirect_with_notice('meta_error', $result->get_error_message());
                return;
            }

            $wpdb->update(
                $table,
                array(
                    'meta_lead_form_id'    => $form_id,
                    'meta_leads'           => $result,
                    'meta_leads_synced_at' => current_time('mysql'),
                ),
                array('id' => $application_id),
                array('%s', '%d', '%s'),
                array('%d')
            );

            self::redirect_with_notice('meta_synced', number_format_i18n($result) . ' leads synced from Meta for this application.');

        } else {
            // Save: store form_id + manual override + optional status change
            $update_data   = array(
                'meta_lead_form_id' => $form_id !== '' ? $form_id : null,
                'meta_leads'        => $meta_leads,
            );
            $update_format = array('%s', $meta_leads === null ? '%s' : '%d');

            $new_status = sanitize_key($_POST['app_status'] ?? '');
            if (!empty($new_status) && in_array($new_status, self::ADMIN_SETTABLE_STATUSES, true)) {
                $update_data['status'] = $new_status;
                $update_format[]       = '%s';
            }

            $wpdb->update($table, $update_data, array('id' => $application_id), $update_format, array('%d'));

            self::redirect_with_notice('updated', 'Application updated.');
        }
    }

    /**
     * Syncs all applications that have a meta_lead_form_id set.
     */
    public static function handle_sync_all_meta_leads(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer(self::SYNC_ALL_ACTION);

        $token = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_META_ACCESS_TOKEN, '');
        if (empty($token)) {
            self::redirect_with_notice('meta_error', 'Meta access token is not configured. Please save it in Settings → Client Onboarding.');
            return;
        }

        global $wpdb;
        $table = DCO_Database::table();

        $apps = $wpdb->get_results(
            "SELECT id, meta_lead_form_id FROM {$table}
             WHERE meta_lead_form_id IS NOT NULL AND meta_lead_form_id != ''
             ORDER BY id ASC"
        );

        if (empty($apps)) {
            self::redirect_with_notice('updated', 'No applications have a Meta Lead Form ID set.');
            return;
        }

        $client   = new DCO_Meta_Client();
        $synced   = 0;
        $errors   = 0;
        $now      = current_time('mysql');

        foreach ($apps as $app) {
            $form_id = sanitize_text_field($app->meta_lead_form_id);
            if (empty($form_id) || !ctype_digit($form_id)) {
                continue;
            }

            $result = $client->fetch_lead_count($form_id);

            if (is_wp_error($result)) {
                error_log('[DCO Meta] Sync All error app ' . $app->id . ': ' . $result->get_error_message());
                $errors++;
                continue;
            }

            $wpdb->update(
                $table,
                array(
                    'meta_leads'           => $result,
                    'meta_leads_synced_at' => $now,
                ),
                array('id' => (int) $app->id),
                array('%d', '%s'),
                array('%d')
            );

            $synced++;
        }

        $msg = number_format_i18n($synced) . ' application(s) synced from Meta.';
        if ($errors > 0) {
            $msg .= ' ' . number_format_i18n($errors) . ' error(s) — check error logs for details.';
        }

        self::redirect_with_notice($errors > 0 ? 'meta_partial' : 'meta_synced', $msg);
    }

    /**
     * Permanently deletes an application record.
     * Only succeeds when the application's status is 'closed' or 'expired'.
     * The WP user and any wp_ctd_clients row are intentionally preserved.
     */
    public static function handle_delete_application(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $application_id = isset($_POST['application_id']) ? (int) $_POST['application_id'] : 0;
        if (!$application_id) {
            wp_die('Invalid application ID.');
        }

        check_admin_referer('dco_delete_' . $application_id);

        global $wpdb;
        $table = DCO_Database::table();

        // Server-side status enforcement — must be closed or expired
        $current_status = $wpdb->get_var($wpdb->prepare(
            "SELECT status FROM {$table} WHERE id = %d",
            $application_id
        ));

        if ($current_status === null) {
            self::redirect_with_notice('delete_error', 'Application not found.');
            return;
        }

        if (!in_array($current_status, self::DELETABLE_STATUSES, true)) {
            self::redirect_with_notice(
                'delete_error',
                'Only Closed or Expired campaigns can be deleted. This application has status: ' . self::format_status($current_status)
            );
            return;
        }

        $deleted = $wpdb->delete($table, array('id' => $application_id), array('%d'));

        if ($deleted) {
            self::redirect_with_notice('deleted', 'Application #' . $application_id . ' has been permanently deleted.');
        } else {
            self::redirect_with_notice('delete_error', 'Database error — application could not be deleted. Please try again.');
        }
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    private static function redirect_with_notice(string $notice, string $msg = ''): void {
        wp_safe_redirect(add_query_arg(
            array(
                'page'           => self::PAGE_SLUG,
                self::NOTICE_ARG => $notice,
                self::NOTICE_MSG_ARG => urlencode($msg),
            ),
            admin_url('options-general.php')
        ));
        exit;
    }

    private static function render_notice(string $notice, string $msg): void {
        if (!$notice) return;

        $type = 'success';
        $default_msg = '';

        switch ($notice) {
            case 'updated':
                $type = 'success';
                $default_msg = 'Saved.';
                break;
            case 'meta_synced':
                $type = 'success';
                $default_msg = 'Meta leads synced.';
                break;
            case 'meta_partial':
                $type = 'warning';
                $default_msg = 'Partial sync — some rows had errors.';
                break;
            case 'meta_error':
                $type = 'error';
                $default_msg = 'Meta sync failed.';
                break;
            case 'deleted':
                $type = 'success';
                $default_msg = 'Application deleted.';
                break;
            case 'delete_error':
                $type = 'error';
                $default_msg = 'Application could not be deleted.';
                break;
        }

        $display_msg = $msg ?: $default_msg;
        echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>' . esc_html($display_msg) . '</p></div>';
    }

    private static function build_summary(array $applications): array {
        $summary = array(
            'application_count' => count($applications),
            'linked_count'      => 0,
            'synced_count'      => 0,
            'ai_total'          => 0,
            'meta_total'        => 0,
            'overall_match'     => '—',
        );

        foreach ($applications as $app) {
            $ai_target = max(0, (int) ($app->enrollment_goal ?? 0));
            $summary['ai_total'] += $ai_target;

            if (!empty($app->meta_lead_form_id)) {
                $summary['linked_count']++;
            }

            if (!empty($app->meta_leads_synced_at)) {
                $summary['synced_count']++;
            }

            if ($app->meta_leads !== null && $app->meta_leads !== '') {
                $summary['meta_total'] += max(0, (int) $app->meta_leads);
            }
        }

        if ($summary['ai_total'] > 0 && $summary['meta_total'] > 0) {
            $summary['overall_match'] = number_format_i18n(
                ($summary['meta_total'] / $summary['ai_total']) * 100, 1
            ) . '%';
        }

        return $summary;
    }

    private static function format_status(string $status): string {
        return ucwords(str_replace('_', ' ', $status));
    }
}
