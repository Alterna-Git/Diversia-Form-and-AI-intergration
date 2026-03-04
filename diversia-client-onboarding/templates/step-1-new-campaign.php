<?php if (!defined('ABSPATH')) exit;
/**
 * Step 1 — Returning client "Start New Campaign" entry card.
 *
 * Rendered by registration-form.php when $dco_is_returning_client === true.
 * Available vars: $dco_returning_user (WP_User)
 */
$_rc_name    = $dco_returning_user ? esc_html($dco_returning_user->display_name) : 'there';
$_rc_email   = $dco_returning_user ? esc_html($dco_returning_user->user_email)   : '';
?>

<div class="dco-panel__header">
    <h2 class="dco-panel__title">Welcome back, <?php echo $_rc_name; ?></h2>
    <p class="dco-panel__subtitle">
        <span data-i18n data-en="You're signed in as an active Diversia Health client. Click below to submit a new trial campaign application."
              data-es="Has iniciado sesión como cliente activo de Diversia Health. Haga clic para enviar una nueva solicitud de campaña.">
            You're signed in as an active Diversia Health client. Click below to submit a new trial campaign application.
        </span>
    </p>
</div>

<div class="dco-new-campaign-info" style="background:rgba(244,121,32,0.07);border:1px solid rgba(244,121,32,0.25);border-radius:12px;padding:16px 20px;margin-bottom:28px;">
    <p style="margin:0;font-size:13px;color:#7a4a1a;">
        <strong>Logged in as:</strong> <?php echo $_rc_email; ?>
    </p>
    <p style="margin:6px 0 0;font-size:13px;color:#7a4a1a;">
        Your account details will carry over automatically. Just fill in the new campaign details on the next step.
        / <em>Sus datos de cuenta se transferirán automáticamente. Solo complete los detalles de la nueva campaña.</em>
    </p>
</div>

<div style="text-align:center;">
    <button type="button" id="dco-btn-new-campaign" class="dco-btn dco-btn--primary" style="min-width:220px;">
        <span class="dco-btn__text">
            <span data-i18n data-en="Start New Campaign →" data-es="Nueva Campaña →">Start New Campaign →</span>
        </span>
        <span class="dco-btn__spinner" style="display:none;"></span>
    </button>
</div>
