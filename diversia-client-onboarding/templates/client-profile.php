<?php
if (!defined('ABSPATH')) exit;
/**
 * Client profile template — rendered by [diversia_client_profile] shortcode.
 *
 * Available variables (all sanitized by the shortcode handler):
 *   $user            WP_User
 *   $lang            'en' | 'es'
 *   $app_id          int
 *   $trial_type      string
 *   $enrollment_goal int
 *   $budget          string
 *   $org_type        string
 *   $ai_score        float
 *   $meta_leads      int
 *   $ctd_client_id   int
 */

// ── Progress calculation ──────────────────────────────────────────────────────
$progress_pct   = ($enrollment_goal > 0 && $meta_leads >= 0)
                    ? min(round(($meta_leads / $enrollment_goal) * 100, 1), 100)
                    : 0;

if ($enrollment_goal > 0 && $meta_leads >= $enrollment_goal) {
    $status_key = 'reached';
} elseif ($progress_pct >= 80) {
    $status_key = 'on_track';
} elseif ($progress_pct >= 20) {
    $status_key = 'in_progress';
} else {
    $status_key = 'getting_started';
}

// ── i18n strings ─────────────────────────────────────────────────────────────
$t = array(
    'en' => array(
        'greeting'         => 'Welcome back,',
        'overview_title'   => 'Your Trial Overview',
        'trial_type'       => 'Trial Type',
        'organization'     => 'Organization Type',
        'budget'           => 'Estimated Budget',
        'ai_score'         => 'AI Qualification Score',
        'progress_title'   => 'Recruitment Progress',
        'goal_label'       => 'Enrollment Goal',
        'leads_label'      => 'Leads from Meta Ads',
        'progress_label'   => 'Progress',
        'status_reached'   => 'Goal Reached',
        'status_on_track'  => 'On Track',
        'status_in_prog'   => 'In Progress',
        'status_starting'  => 'Getting Started',
        'no_trial'         => 'No trial data yet.',
        'contact'          => 'Questions? Contact us at',
        'participants'     => 'participants',
    ),
    'es' => array(
        'greeting'         => 'Bienvenido(a) de nuevo,',
        'overview_title'   => 'Resumen de su Ensayo',
        'trial_type'       => 'Tipo de Ensayo',
        'organization'     => 'Tipo de Organización',
        'budget'           => 'Presupuesto Estimado',
        'ai_score'         => 'Puntuación de Calificación IA',
        'progress_title'   => 'Progreso de Reclutamiento',
        'goal_label'       => 'Meta de Inscripción',
        'leads_label'      => 'Contactos de Meta Ads',
        'progress_label'   => 'Progreso',
        'status_reached'   => 'Meta Alcanzada',
        'status_on_track'  => 'En Buen Camino',
        'status_in_prog'   => 'En Progreso',
        'status_starting'  => 'Comenzando',
        'no_trial'         => 'Aún no hay datos del ensayo.',
        'contact'          => 'Preguntas? Contáctenos en',
        'participants'     => 'participantes',
    ),
);

$s = $t[$lang] ?? $t['en'];

// Status color + label
$status_map = array(
    'reached'         => array('label' => $s['status_reached'],  'color' => '#1e8449', 'bg' => '#eafbf0'),
    'on_track'        => array('label' => $s['status_on_track'],  'color' => '#1e6b8a', 'bg' => '#e3f4fb'),
    'in_progress'     => array('label' => $s['status_in_prog'],   'color' => '#856404', 'bg' => '#fff8e1'),
    'getting_started' => array('label' => $s['status_starting'],  'color' => '#6c757d', 'bg' => '#f4f6f8'),
);
$status = $status_map[$status_key];

// Progress bar color
$bar_color = ($status_key === 'reached') ? '#1e8449'
           : (($status_key === 'on_track') ? '#2e86ab'
           : (($status_key === 'in_progress') ? '#f0a500' : '#adb5bd'));
?>

<div class="dco-profile-wrap" style="font-family:Arial,Helvetica,sans-serif;max-width:820px;margin:0 auto;color:#1a2e40;">

    <!-- Greeting ──────────────────────────────────────────────────── -->
    <div style="margin-bottom:24px;">
        <h2 style="margin:0 0 4px;font-size:22px;font-weight:700;color:#1a2e40;">
            <?php echo esc_html($s['greeting']); ?> <?php echo esc_html($user->display_name); ?>
        </h2>
        <?php if ($trial_type): ?>
            <p style="margin:0;color:#64748b;font-size:14px;">
                <?php echo esc_html($trial_type); ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if (empty($trial_type)): ?>
        <p style="color:#64748b;"><?php echo esc_html($s['no_trial']); ?></p>
    <?php else: ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <!-- Card 1: Trial Overview ──────────────────────────────────── -->
        <div style="background:#fff;border:1px solid #dde4ec;border-radius:10px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;color:#1a2e40;text-transform:uppercase;letter-spacing:.5px;border-bottom:2px solid #F47920;padding-bottom:8px;">
                <?php echo esc_html($s['overview_title']); ?>
            </h3>
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="padding:7px 0;font-size:13px;color:#64748b;width:50%;"><?php echo esc_html($s['trial_type']); ?></td>
                    <td style="padding:7px 0;font-size:13px;font-weight:600;color:#1a2e40;"><?php echo esc_html($trial_type); ?></td>
                </tr>
                <tr style="border-top:1px solid #f0f2f5;">
                    <td style="padding:7px 0;font-size:13px;color:#64748b;"><?php echo esc_html($s['organization']); ?></td>
                    <td style="padding:7px 0;font-size:13px;font-weight:600;color:#1a2e40;"><?php echo esc_html($org_type ?: '—'); ?></td>
                </tr>
                <tr style="border-top:1px solid #f0f2f5;">
                    <td style="padding:7px 0;font-size:13px;color:#64748b;"><?php echo esc_html($s['budget']); ?></td>
                    <td style="padding:7px 0;font-size:13px;font-weight:600;color:#1a2e40;"><?php echo esc_html($budget ?: '—'); ?></td>
                </tr>
                <tr style="border-top:1px solid #f0f2f5;">
                    <td style="padding:7px 0;font-size:13px;color:#64748b;"><?php echo esc_html($s['ai_score']); ?></td>
                    <td style="padding:7px 0;font-size:13px;">
                        <span style="font-weight:700;color:#F47920;font-size:15px;"><?php echo esc_html(number_format_i18n($ai_score, 1)); ?></span>
                        <span style="color:#64748b;font-size:12px;">/100</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Card 2: Recruitment Progress ───────────────────────────── -->
        <div style="background:#fff;border:1px solid #dde4ec;border-radius:10px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;color:#1a2e40;text-transform:uppercase;letter-spacing:.5px;border-bottom:2px solid #F47920;padding-bottom:8px;">
                <?php echo esc_html($s['progress_title']); ?>
            </h3>

            <!-- Status badge -->
            <div style="margin-bottom:16px;">
                <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;letter-spacing:.3px;background:<?php echo esc_attr($status['bg']); ?>;color:<?php echo esc_attr($status['color']); ?>;">
                    <?php echo esc_html($status['label']); ?>
                </span>
            </div>

            <!-- Stats row -->
            <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                <div style="text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#1a2e40;"><?php echo esc_html(number_format_i18n($meta_leads)); ?></div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px;"><?php echo esc_html($s['leads_label']); ?></div>
                </div>
                <div style="display:flex;align-items:center;color:#dde4ec;font-size:20px;">→</div>
                <div style="text-align:center;">
                    <div style="font-size:22px;font-weight:800;color:#1a2e40;"><?php echo esc_html(number_format_i18n($enrollment_goal)); ?></div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px;"><?php echo esc_html($s['goal_label']); ?></div>
                </div>
            </div>

            <!-- Progress bar -->
            <div style="background:#f0f2f5;border-radius:6px;height:10px;overflow:hidden;margin-bottom:6px;">
                <div style="height:100%;width:<?php echo esc_attr($progress_pct); ?>%;background:<?php echo esc_attr($bar_color); ?>;border-radius:6px;transition:width .4s ease;"></div>
            </div>
            <div style="font-size:12px;color:#64748b;text-align:right;">
                <?php echo esc_html($s['progress_label']); ?>: <strong><?php echo esc_html(number_format_i18n($progress_pct, 1)); ?>%</strong>
            </div>

            <?php if ($enrollment_goal > 0 && $meta_leads < $enrollment_goal): ?>
                <p style="margin:12px 0 0;font-size:12px;color:#64748b;">
                    <?php echo esc_html(number_format_i18n($enrollment_goal - $meta_leads) . ' ' . $s['participants']); ?>
                    <?php echo ($lang === 'es') ? ' restantes para alcanzar la meta.' : ' more to reach the goal.'; ?>
                </p>
            <?php endif; ?>
        </div>

    </div><!-- /grid -->

    <?php endif; ?>

    <!-- Footer contact line ─────────────────────────────────────── -->
    <p style="margin:20px 0 0;font-size:12px;color:#adb5bd;text-align:center;">
        <?php echo esc_html($s['contact']); ?>
        <a href="mailto:info@diversiahealth.com" style="color:#F47920;text-decoration:none;">info@diversiahealth.com</a>
    </p>

</div><!-- /dco-profile-wrap -->
