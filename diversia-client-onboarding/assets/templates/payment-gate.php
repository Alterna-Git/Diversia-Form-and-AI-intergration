<?php if (!defined('ABSPATH')) exit; ?>
<?php
// Variables available: $app (application row object), $application_id (int),
//                      $token (string), $nonce_stripe (string)
?>

<div class="dco-wrapper dco-payment-gate" data-lang="en">

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

    <div class="dco-panel">

        <!-- Approval Header -->
        <div class="dco-panel__header">
            <div class="dco-result__icon" style="color: var(--dco-success);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h2 class="dco-panel__title" data-i18n
                data-en="You Have Been Approved!"
                data-es="¡Ha sido aprobado(a)!">
                You Have Been Approved!
            </h2>
            <p class="dco-panel__subtitle" data-i18n
               data-en="Complete your payment to activate your Diversia Health client account."
               data-es="Complete su pago para activar su cuenta de cliente Diversia Health.">
                Complete your payment to activate your Diversia Health client account.
            </p>
        </div>

        <!-- Application Summary -->
        <div class="dco-summary">
            <h3 class="dco-summary__title" data-i18n data-en="Your Application Summary" data-es="Resumen de su Solicitud">
                Your Application Summary
            </h3>
            <table class="dco-summary__table">
                <tr>
                    <th data-i18n data-en="Organization" data-es="Organización">Organization</th>
                    <td><?php echo esc_html($app->company_name); ?></td>
                </tr>
                <tr>
                    <th data-i18n data-en="Trial Type" data-es="Tipo de Ensayo">Trial Type</th>
                    <td><?php echo esc_html($app->trial_type); ?></td>
                </tr>
                <tr>
                    <th data-i18n data-en="Budget" data-es="Presupuesto">Budget</th>
                    <td><?php echo esc_html($app->estimated_budget); ?></td>
                </tr>
                <tr>
                    <th data-i18n data-en="Organization Type" data-es="Tipo de Organización">Organization Type</th>
                    <td><?php echo esc_html($app->organization_type); ?></td>
                </tr>
                <tr>
                    <th data-i18n data-en="AI Score" data-es="Calificación AI">AI Score</th>
                    <td><strong><?php echo esc_html(number_format((float)$app->ai_score, 1)); ?>/100</strong></td>
                </tr>
            </table>
        </div>

        <!-- What's Included -->
        <div class="dco-includes">
            <h3 class="dco-includes__title" data-i18n data-en="What's Included" data-es="Qué Está Incluido">
                What's Included
            </h3>
            <ul class="dco-includes__list">
                <li class="dco-includes__item">
                    <span class="dco-includes__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <span data-i18n
                          data-en="Full access to the Clinical Trial Dashboard"
                          data-es="Acceso completo al Panel de Ensayos Clínicos">
                        Full access to the Clinical Trial Dashboard
                    </span>
                </li>
                <li class="dco-includes__item">
                    <span class="dco-includes__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <span data-i18n
                          data-en="Access to Diversia's Latino participant recruitment network"
                          data-es="Acceso a la red de reclutamiento de participantes latinos de Diversia">
                        Access to Diversia's Latino participant recruitment network
                    </span>
                </li>
                <li class="dco-includes__item">
                    <span class="dco-includes__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <span data-i18n
                          data-en="AI-powered patient matching and eligibility screening"
                          data-es="Concordancia de pacientes impulsada por IA y evaluación de elegibilidad">
                        AI-powered patient matching and eligibility screening
                    </span>
                </li>
                <li class="dco-includes__item">
                    <span class="dco-includes__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <span data-i18n
                          data-en="Bilingual patient outreach and community engagement (English & Spanish)"
                          data-es="Difusión bilingüe a pacientes y participación comunitaria (inglés y español)">
                        Bilingual patient outreach and community engagement (English &amp; Spanish)
                    </span>
                </li>
                <li class="dco-includes__item">
                    <span class="dco-includes__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <span data-i18n
                          data-en="Real-time enrollment tracking and trial progress reports"
                          data-es="Seguimiento de inscripción en tiempo real e informes de progreso del ensayo">
                        Real-time enrollment tracking and trial progress reports
                    </span>
                </li>
                <li class="dco-includes__item">
                    <span class="dco-includes__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                    <span data-i18n
                          data-en="Dedicated account manager for your trial"
                          data-es="Gerente de cuenta dedicado para su ensayo">
                        Dedicated account manager for your trial
                    </span>
                </li>
            </ul>
        </div>

        <!-- Payment Method Selection -->
        <div class="dco-pay-method-section">
            <h3 class="dco-pay-method-section__title" data-i18n data-en="Select Payment Method" data-es="Seleccione Método de Pago">
                Select Payment Method
            </h3>
            <div class="dco-pay-method-grid" id="dco-pay-method-grid">

                <!-- Card option -->
                <button class="dco-pay-method-card" type="button" data-method="card" aria-pressed="false">
                    <span class="dco-pay-method-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </span>
                    <span class="dco-pay-method-card__title" data-i18n data-en="Credit / Debit Card" data-es="Tarjeta de Crédito / Débito">
                        Credit / Debit Card
                    </span>
                    <span class="dco-pay-method-card__desc" data-i18n data-en="Visa, Mastercard, Amex" data-es="Visa, Mastercard, Amex">
                        Visa, Mastercard, Amex
                    </span>
                    <span class="dco-pay-method-card__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                </button>

                <!-- eCheck / ACH option -->
                <button class="dco-pay-method-card" type="button" data-method="us_bank_account" aria-pressed="false">
                    <span class="dco-pay-method-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="15" rx="2"/>
                            <polyline points="16 2 12 6 8 2"/>
                            <line x1="12" y1="12" x2="12" y2="17"/>
                            <line x1="9" y1="14.5" x2="15" y2="14.5"/>
                        </svg>
                    </span>
                    <span class="dco-pay-method-card__title" data-i18n data-en="eCheck / ACH Transfer" data-es="Cheque Electrónico / Transferencia ACH">
                        eCheck / ACH Transfer
                    </span>
                    <span class="dco-pay-method-card__desc" data-i18n data-en="US bank account — no card fees" data-es="Cuenta bancaria EE.UU. — sin cargos de tarjeta">
                        US bank account — no card fees
                    </span>
                    <span class="dco-pay-method-card__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                </button>

            </div>
            <p class="dco-pay-method-section__hint" id="dco-pay-method-hint" style="display:none;"
               data-i18n data-en="Please select a payment method to continue." data-es="Seleccione un método de pago para continuar.">
                Please select a payment method to continue.
            </p>
        </div>

        <!-- eCheck / ACH info panel (shown only when eCheck is selected) -->
        <div class="dco-ach-info" id="dco-ach-info" style="display:none;">
            <div class="dco-ach-info__header">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="15" rx="2"/>
                    <polyline points="16 2 12 6 8 2"/>
                    <line x1="12" y1="12" x2="12" y2="17"/>
                    <line x1="9" y1="14.5" x2="15" y2="14.5"/>
                </svg>
                <span data-i18n
                      data-en="How eCheck / ACH works"
                      data-es="Cómo funciona el Cheque Electrónico / ACH">
                    How eCheck / ACH works
                </span>
            </div>
            <ol class="dco-ach-info__steps">
                <li>
                    <span data-i18n
                          data-en="You will be redirected to Stripe's secure page to enter your US bank account details (routing number &amp; account number)."
                          data-es="Será redirigido a la página segura de Stripe para ingresar los datos de su cuenta bancaria (número de ruta y número de cuenta).">
                        You will be redirected to Stripe's secure page to enter your US bank account details (routing number &amp; account number).
                    </span>
                </li>
                <li>
                    <span data-i18n
                          data-en="Stripe will instantly verify your bank, or send micro-deposits (1–2 business days) if instant verification is unavailable."
                          data-es="Stripe verificará su banco de forma instantánea o enviará micro-depósitos (1–2 días hábiles) si la verificación instantánea no está disponible.">
                        Stripe will instantly verify your bank, or send micro-deposits (1–2 business days) if instant verification is unavailable.
                    </span>
                </li>
                <li>
                    <span data-i18n
                          data-en="Once verified, your payment will be debited and your client account activated within 2–5 business days."
                          data-es="Una vez verificado, su pago será debitado y su cuenta de cliente activada en 2–5 días hábiles.">
                        Once verified, your payment will be debited and your client account activated within 2–5 business days.
                    </span>
                </li>
            </ol>
        </div>

        <!-- Error banner -->
        <div class="dco-error-banner" id="dco-gate-error" style="display:none;"></div>

        <!-- Pay button -->
        <div class="dco-result__cta">
            <button class="dco-btn dco-btn--primary dco-btn--large" id="dco-gate-pay-btn"
                    data-application-id="<?php echo esc_attr($application_id); ?>"
                    data-token="<?php echo esc_attr($token); ?>"
                    data-nonce="<?php echo esc_attr($nonce_stripe); ?>"
                    disabled>
                <span class="dco-btn__text">
                    <span id="dco-gate-pay-label"
                          data-i18n
                          data-en="Complete Payment"
                          data-es="Completar Pago">
                        Complete Payment
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </span>
                <span class="dco-btn__spinner" style="display:none;"></span>
            </button>
            <p class="dco-hint dco-hint--center" id="dco-gate-pay-hint" style="margin-top: 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                     fill="none" stroke="#888" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <span id="dco-gate-pay-hint-text"
                      data-i18n
                      data-en="Secure payment via Stripe"
                      data-es="Pago seguro via Stripe">
                    Secure payment via Stripe
                </span>
            </p>
        </div>

    </div><!-- .dco-panel -->
</div><!-- .dco-wrapper -->

<script>
jQuery(document).ready(function ($) {
    var selectedMethod = null;
    var $payBtn    = $('#dco-gate-pay-btn');
    var $hint      = $('#dco-pay-method-hint');
    var $achInfo   = $('#dco-ach-info');
    var $btnLabel  = $('#dco-gate-pay-label');
    var $hintText  = $('#dco-gate-pay-hint-text');

    // Per-method button labels (en / es)
    var btnLabels = {
        card: {
            en: 'Proceed to Secure Payment',
            es: 'Proceder al Pago Seguro'
        },
        us_bank_account: {
            en: 'Proceed to Bank Verification',
            es: 'Proceder a Verificación Bancaria'
        }
    };
    var hintLabels = {
        card: {
            en: 'Secure payment via Stripe',
            es: 'Pago seguro via Stripe'
        },
        us_bank_account: {
            en: 'You\'ll enter your bank details on Stripe\'s secure page',
            es: 'Ingresará sus datos bancarios en la página segura de Stripe'
        }
    };

    function getLang() {
        return ($('.dco-wrapper').data('lang') || 'en');
    }

    function applyMethodLabels(method) {
        var lang = getLang();
        var bLabel = btnLabels[method] || btnLabels.card;
        var hLabel = hintLabels[method] || hintLabels.card;

        // Update text and data-en/es so language toggle keeps them in sync
        $btnLabel
            .attr('data-en', bLabel.en)
            .attr('data-es', bLabel.es)
            .text(bLabel[lang]);

        $hintText
            .attr('data-en', hLabel.en)
            .attr('data-es', hLabel.es)
            .text(hLabel[lang]);
    }

    // Payment method card selection
    $('#dco-pay-method-grid .dco-pay-method-card').on('click', function () {
        var $card = $(this);
        selectedMethod = $card.data('method');

        // Update card states
        $('#dco-pay-method-grid .dco-pay-method-card').removeClass('dco-pay-method-card--selected').attr('aria-pressed', 'false');
        $card.addClass('dco-pay-method-card--selected').attr('aria-pressed', 'true');

        // Show / hide eCheck info panel
        if (selectedMethod === 'us_bank_account') {
            $achInfo.slideDown(200);
        } else {
            $achInfo.slideUp(150);
        }

        // Update button label and hint
        applyMethodLabels(selectedMethod);

        // Enable pay button, hide validation hint
        $payBtn.prop('disabled', false);
        $hint.hide();
    });

    // Pay button click
    $payBtn.on('click', function () {
        if (!selectedMethod) {
            $hint.show();
            return;
        }

        var $btn = $(this);
        $btn.find('.dco-btn__text').hide();
        $btn.find('.dco-btn__spinner').show();
        $btn.prop('disabled', true);
        $('#dco-gate-error').hide().text('');

        $.ajax({
            url:  dcoData.ajaxurl,
            type: 'POST',
            data: {
                action:          'dco_create_stripe_session',
                nonce:           $btn.data('nonce'),
                application_id:  $btn.data('application-id'),
                token:           $btn.data('token'),
                payment_method:  selectedMethod,
            },
            success: function (res) {
                if (res.success && res.data.checkout_url) {
                    window.location.href = res.data.checkout_url;
                } else {
                    var msg = (res.data && res.data.message) ? res.data.message : 'An error occurred. Please try again.';
                    $('#dco-gate-error').text(msg).show();
                    $btn.find('.dco-btn__text').show();
                    $btn.find('.dco-btn__spinner').hide();
                    $btn.prop('disabled', false);
                }
            },
            error: function () {
                $('#dco-gate-error').text('Connection error. Please try again.').show();
                $btn.find('.dco-btn__text').show();
                $btn.find('.dco-btn__spinner').hide();
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
