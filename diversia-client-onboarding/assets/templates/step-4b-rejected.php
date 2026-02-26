<?php if (!defined('ABSPATH')) exit; ?>

<div class="dco-result dco-result--rejected">

    <!-- Icon + headline -->
    <div class="dco-result__icon dco-result__icon--neutral">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
    </div>

    <h2 class="dco-result__title" data-i18n
        data-en="Thank You for Your Interest"
        data-es="Gracias por su Interés">
        Thank You for Your Interest
    </h2>
    <p class="dco-result__subtitle" data-i18n
       data-en="We have carefully reviewed your application."
       data-es="Hemos revisado su solicitud cuidadosamente.">
        We have carefully reviewed your application.
    </p>

    <!-- AI reasoning -->
    <div class="dco-result__reasoning" id="dco-reasoning-rejected">
        <!-- Populated by JS -->
    </div>

    <!-- ===== AI Analysis & Suggestions ===== -->
    <div class="dco-suggestions" id="dco-suggestions-rejected" style="display:none;">
        <div class="dco-suggestions__header">
            <div class="dco-suggestions__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div>
                <h3 class="dco-suggestions__title" data-i18n
                    data-en="What Our AI Flagged"
                    data-es="Lo Que Señaló Nuestra IA">
                    What Our AI Flagged
                </h3>
                <p class="dco-suggestions__subtitle" data-i18n
                   data-en="Addressing these specific points may help your application qualify on resubmission."
                   data-es="Abordar estos puntos específicos puede ayudar a que su solicitud califique al volver a presentarla.">
                    Addressing these specific points may help your application qualify on resubmission.
                </p>
            </div>
        </div>
        <ul class="dco-suggestions__list" id="dco-suggestions-list">
            <!-- Populated by JS -->
        </ul>
    </div>

    <!-- ===== Revise & Resubmit CTA ===== -->
    <div class="dco-revise-cta" id="dco-revise-cta" style="display:none;">
        <button class="dco-btn dco-btn--primary dco-btn--revise" id="dco-btn-revise" type="button">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            <span data-i18n data-en="Revise My Application" data-es="Revisar Mi Solicitud">
                Revise My Application
            </span>
        </button>
        <p class="dco-hint dco-hint--center" style="margin-top: 10px;" data-i18n
           data-en="Update the flagged information in Step 2 and resubmit for a new evaluation."
           data-es="Actualice la información señalada en el Paso 2 y vuelva a enviar para una nueva evaluación.">
            Update the flagged information in Step 2 and resubmit for a new evaluation.
        </p>
    </div>

    <!-- Contact fallback -->
    <div class="dco-result__contact">
        <p data-i18n
           data-en="If you believe there has been an error or have additional questions, please don't hesitate to contact us."
           data-es="Si cree que ha ocurrido un error o tiene preguntas adicionales, no dude en contactarnos.">
            If you believe there has been an error or have additional questions, please don't hesitate to contact us.
        </p>
        <a href="mailto:info@diversiahealth.com" class="dco-btn dco-btn--secondary" style="margin-top:16px;display:inline-flex;">
            <span data-i18n data-en="Contact Us" data-es="Contáctenos">Contact Us</span>
        </a>
    </div>

</div>
