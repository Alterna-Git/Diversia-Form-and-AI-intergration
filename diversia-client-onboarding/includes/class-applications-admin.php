<?php
if (!defined('ABSPATH')) exit;

class DCO_Applications_Admin {

    const PAGE_SLUG           = 'dco-applications';
    const UPDATE_ACTION       = 'dco_update_meta_leads';
    const NOTICE_QUERY_ARG    = 'dco_apps_notice';

    public static function init(): void {
        add_action('admin_menu', array(__CLASS__, 'add_submenu_page'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
        add_action('admin_post_' . self::UPDATE_ACTION, array(__CLASS__, 'handle_update_meta_leads'));
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

    public static function render_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;

        $applications = $wpdb->get_results(
            "SELECT id, created_at, first_name, last_name, email, company_name, status, ai_score, enrollment_goal, meta_leads
             FROM " . DCO_Database::table() . "
             ORDER BY created_at DESC"
        );

        $notice = sanitize_key($_GET[self::NOTICE_QUERY_ARG] ?? '');
        $summary = self::build_summary($applications);
        ?>
        <div class="wrap dco-apps-wrap">
            <h1>Client Applications</h1>
            <?php if ($notice === 'updated'): ?>
                <div class="notice notice-success is-dismissible">
                    <p>Meta leads updated.</p>
                </div>
            <?php endif; ?>

            <p class="description" style="max-width:900px;">
                Compare each application's AI enrollment target with the number of leads reported from Meta.
                Use the <strong>Meta Leads</strong> field to track actual lead volume and review the variance.
            </p>

            <div class="dco-apps-summary">
                <div class="dco-apps-card">
                    <span class="dco-apps-card__label">Applications</span>
                    <strong class="dco-apps-card__value"><?php echo esc_html(number_format_i18n($summary['application_count'])); ?></strong>
                </div>
                <div class="dco-apps-card">
                    <span class="dco-apps-card__label">Tracked in Meta</span>
                    <strong class="dco-apps-card__value"><?php echo esc_html(number_format_i18n($summary['tracked_count'])); ?></strong>
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
                            <th>Meta Leads</th>
                            <th>Difference</th>
                            <th>Match %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="9">No applications found yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <?php
                                $ai_target   = (int) ($app->enrollment_goal ?? 0);
                                $meta_leads  = isset($app->meta_leads) ? (int) $app->meta_leads : null;
                                $difference  = ($meta_leads !== null && $ai_target > 0) ? ($meta_leads - $ai_target) : null;
                                $match       = ($meta_leads !== null && $ai_target > 0) ? round(($meta_leads / $ai_target) * 100, 1) : null;
                                $client_name = trim($app->first_name . ' ' . $app->last_name);
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
                                    <td>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="dco-apps-meta-form">
                                            <?php wp_nonce_field(self::UPDATE_ACTION . '_' . $app->id); ?>
                                            <input type="hidden" name="action" value="<?php echo esc_attr(self::UPDATE_ACTION); ?>">
                                            <input type="hidden" name="application_id" value="<?php echo esc_attr((string) $app->id); ?>">
                                            <input
                                                type="number"
                                                min="0"
                                                name="meta_leads"
                                                value="<?php echo $meta_leads !== null ? esc_attr((string) $meta_leads) : ''; ?>"
                                                class="small-text"
                                                placeholder="0"
                                            >
                                            <?php submit_button('Save', 'secondary small', 'submit', false); ?>
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
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    public static function handle_update_meta_leads(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $application_id = isset($_POST['application_id']) ? (int) $_POST['application_id'] : 0;
        if (!$application_id) {
            wp_die('Invalid application ID.');
        }

        check_admin_referer(self::UPDATE_ACTION . '_' . $application_id);

        $raw_meta_leads = isset($_POST['meta_leads']) ? trim(wp_unslash($_POST['meta_leads'])) : '';
        $meta_leads     = $raw_meta_leads === '' ? null : max(0, (int) $raw_meta_leads);

        global $wpdb;
        if ($meta_leads === null) {
            $wpdb->update(
                DCO_Database::table(),
                array('meta_leads' => null),
                array('id' => $application_id),
                array('%s'),
                array('%d')
            );
        } else {
            $wpdb->update(
                DCO_Database::table(),
                array('meta_leads' => $meta_leads),
                array('id' => $application_id),
                array('%d'),
                array('%d')
            );
        }

        wp_safe_redirect(add_query_arg(
            array(
                'page' => self::PAGE_SLUG,
                self::NOTICE_QUERY_ARG => 'updated',
            ),
            admin_url('options-general.php')
        ));
        exit;
    }

    private static function build_summary(array $applications): array {
        $summary = array(
            'application_count' => count($applications),
            'tracked_count'     => 0,
            'ai_total'          => 0,
            'meta_total'        => 0,
            'overall_match'     => '—',
        );

        foreach ($applications as $app) {
            $ai_target = max(0, (int) ($app->enrollment_goal ?? 0));
            $summary['ai_total'] += $ai_target;

            if ($app->meta_leads !== null && $app->meta_leads !== '') {
                $summary['tracked_count']++;
                $summary['meta_total'] += max(0, (int) $app->meta_leads);
            }
        }

        if ($summary['ai_total'] > 0 && $summary['meta_total'] > 0) {
            $summary['overall_match'] = number_format_i18n(($summary['meta_total'] / $summary['ai_total']) * 100, 1) . '%';
        }

        return $summary;
    }

    private static function format_status(string $status): string {
        return ucwords(str_replace('_', ' ', $status));
    }
}
