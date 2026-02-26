<?php if (!defined('ABSPATH')) exit; ?>

<div class="dco-wrapper" id="dco-registration" data-lang="en">

    <!-- Language toggle -->
    <div class="dco-lang-bar">
        <button class="dco-lang-toggle" id="dco-lang-btn" type="button" aria-label="Switch language">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <span data-i18n data-en="Ver en Español" data-es="View in English">Ver en Español</span>
        </button>
    </div>

    <!-- Step indicators -->
    <div class="dco-steps">
        <div class="dco-step dco-step--active" data-step="1">
            <span class="dco-step__num">1</span>
            <span class="dco-step__label" data-i18n data-en="Account" data-es="Cuenta">Account</span>
        </div>
        <div class="dco-step" data-step="2">
            <span class="dco-step__num">2</span>
            <span class="dco-step__label" data-i18n data-en="Requirements" data-es="Necesidades">Requirements</span>
        </div>
        <div class="dco-step" data-step="3">
            <span class="dco-step__num">3</span>
            <span class="dco-step__label" data-i18n data-en="Evaluation" data-es="Evaluación">Evaluation</span>
        </div>
    </div>

    <!-- Error banner -->
    <div class="dco-error-banner" id="dco-error" style="display:none;"></div>

    <!-- ===== STEP 1 ===== -->
    <div class="dco-panel" id="dco-step-1">
        <?php include DCO_PLUGIN_DIR . 'templates/step-1-account.php'; ?>
    </div>

    <!-- ===== STEP 2 ===== -->
    <div class="dco-panel" id="dco-step-2" style="display:none;">
        <?php include DCO_PLUGIN_DIR . 'templates/step-2-trial-needs.php'; ?>
    </div>

    <!-- ===== STEP 3: Loading ===== -->
    <div class="dco-panel" id="dco-step-3" style="display:none;">
        <?php include DCO_PLUGIN_DIR . 'templates/step-3-loading.php'; ?>
    </div>

    <!-- ===== STEP 4a: Qualified ===== -->
    <div class="dco-panel" id="dco-step-4a" style="display:none;">
        <?php include DCO_PLUGIN_DIR . 'templates/step-4a-qualified.php'; ?>
    </div>

    <!-- ===== STEP 4b: Rejected ===== -->
    <div class="dco-panel" id="dco-step-4b" style="display:none;">
        <?php include DCO_PLUGIN_DIR . 'templates/step-4b-rejected.php'; ?>
    </div>

    <!-- Hidden state fields (populated by JS) -->
    <input type="hidden" id="dco-application-id" value="">
    <input type="hidden" id="dco-token" value="">

</div><!-- .dco-wrapper -->
