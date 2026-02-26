<?php if (!defined('ABSPATH')) exit; ?>

<div class="dco-result dco-result--qualified">
    <div class="dco-result__icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
    </div>

    <h2 class="dco-result__title" data-i18n
        data-en="Congratulations — you have been qualified!"
        data-es="¡Felicitaciones, ha sido calificado(a)!">
        Congratulations — you have been qualified!
    </h2>
    <p class="dco-result__subtitle" data-i18n
       data-en="You are approved to join Diversia Health as a client."
       data-es="Ha sido aprobado(a) para unirse a Diversia Health como cliente.">
        You are approved to join Diversia Health as a client.
    </p>

    <div class="dco-result__reasoning" id="dco-reasoning-qualified">
        <!-- Populated by JS: shows reasoning_en or reasoning_es based on current language -->
    </div>

    <div class="dco-result__cta">
        <p class="dco-result__cta-text" data-i18n
           data-en="The next step is to complete your payment to activate your client account."
           data-es="El siguiente paso es completar su pago para activar su cuenta de cliente.">
            The next step is to complete your payment to activate your client account.
        </p>
        <button class="dco-btn dco-btn--primary dco-btn--large" id="dco-btn-pay">
            <span class="dco-btn__text">
                <span data-i18n data-en="Proceed to Payment" data-es="Proceder al Pago">Proceed to Payment</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </span>
            <span class="dco-btn__spinner" style="display:none;"></span>
        </button>
        <p class="dco-hint dco-hint--center" style="margin-top:12px;" data-i18n
           data-en="You will be redirected to Stripe, a secure payment platform."
           data-es="Será redirigido(a) a Stripe, una plataforma de pagos segura.">
            You will be redirected to Stripe, a secure payment platform.
        </p>
    </div>

    <div class="dco-result__security">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
             fill="none" stroke="#666" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        <span data-i18n data-en="Payments securely processed by Stripe" data-es="Pagos procesados de forma segura por Stripe">Payments securely processed by Stripe</span>
    </div>
</div>
