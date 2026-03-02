<?php if (!defined('ABSPATH')) exit; ?>

<div class="dco-panel__header">
    <h2 class="dco-panel__title" data-i18n data-en="Account Information" data-es="Información de Cuenta">Account Information</h2>
    <p class="dco-panel__subtitle" data-i18n data-en="Create your account to begin the application process." data-es="Crea tu cuenta para comenzar el proceso de solicitud.">Create your account to begin the application process.</p>
</div>

<form class="dco-form" id="dco-form-step1" novalidate>
    <input type="hidden" name="action" value="dco_register_step1">
    <input type="hidden" name="nonce" id="dco-nonce-step1">

    <div class="dco-form__row dco-form__row--2col">
        <div class="dco-field">
            <label class="dco-label" for="dco-first-name">
                <span data-i18n data-en="First Name" data-es="Nombre">First Name</span>
                <span class="dco-required">*</span>
            </label>
            <input class="dco-input" type="text" id="dco-first-name" name="first_name"
                   data-placeholder-en="María" data-placeholder-es="María"
                   placeholder="María" autocomplete="given-name" required>
        </div>
        <div class="dco-field">
            <label class="dco-label" for="dco-last-name">
                <span data-i18n data-en="Last Name" data-es="Apellido">Last Name</span>
                <span class="dco-required">*</span>
            </label>
            <input class="dco-input" type="text" id="dco-last-name" name="last_name"
                   data-placeholder-en="García" data-placeholder-es="García"
                   placeholder="García" autocomplete="family-name" required>
        </div>
    </div>

    <div class="dco-field">
        <label class="dco-label" for="dco-email">
            <span data-i18n data-en="Email Address" data-es="Correo Electrónico">Email Address</span>
            <span class="dco-required">*</span>
        </label>
        <input class="dco-input" type="email" id="dco-email" name="email"
               data-placeholder-en="maria@organization.com" data-placeholder-es="maria@organización.com"
               placeholder="maria@organization.com" autocomplete="email" required>
    </div>

    <div class="dco-field">
        <label class="dco-label" for="dco-company">
            <span data-i18n data-en="Organization Name" data-es="Nombre de la Organización">Organization Name</span>
            <span class="dco-required">*</span>
        </label>
        <input class="dco-input" type="text" id="dco-company" name="company_name"
               data-placeholder-en="MedResearch Inc." data-placeholder-es="MedResearch Inc."
               placeholder="MedResearch Inc." autocomplete="organization" required>
    </div>

    <div class="dco-form__row dco-form__row--2col">
        <div class="dco-field">
            <label class="dco-label" for="dco-password">
                <span data-i18n data-en="Password" data-es="Contraseña">Password</span>
                <span class="dco-required">*</span>
            </label>
            <div class="dco-input-wrap">
                <input class="dco-input" type="password" id="dco-password" name="password"
                       data-placeholder-en="Minimum 8 characters" data-placeholder-es="Mínimo 8 caracteres"
                       placeholder="Minimum 8 characters" autocomplete="new-password" minlength="8" required>
                <button type="button" class="dco-peek-btn" id="dco-peek-password"
                        onclick="dcoPeekToggle('dco-password','dco-peek-password')"
                        title="Show/hide password" aria-label="Toggle password visibility">
                    <svg class="dco-peek-icon dco-peek-icon--show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="dco-peek-icon dco-peek-icon--hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            <div class="dco-password-strength">
                <div class="dco-password-strength__fill" id="dco-strength-fill"></div>
            </div>
            <span class="dco-hint" id="dco-strength-text"></span>
            <ul class="dco-criteria">
                <li class="dco-criterion" id="dco-crit-length">
                    <svg class="dco-criterion__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span data-i18n data-en="At least 8 characters" data-es="Mínimo 8 caracteres">At least 8 characters</span>
                </li>
                <li class="dco-criterion" id="dco-crit-upper">
                    <svg class="dco-criterion__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span data-i18n data-en="One uppercase letter" data-es="Una mayúscula">One uppercase letter</span>
                </li>
                <li class="dco-criterion" id="dco-crit-number">
                    <svg class="dco-criterion__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span data-i18n data-en="One number" data-es="Un número">One number</span>
                </li>
                <li class="dco-criterion" id="dco-crit-special">
                    <svg class="dco-criterion__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span data-i18n data-en="One special character" data-es="Un carácter especial">One special character</span>
                </li>
            </ul>
        </div>
        <div class="dco-field">
            <label class="dco-label" for="dco-password-confirm">
                <span data-i18n data-en="Confirm Password" data-es="Confirmar Contraseña">Confirm Password</span>
                <span class="dco-required">*</span>
            </label>
            <div class="dco-input-wrap">
                <input class="dco-input" type="password" id="dco-password-confirm" name="password_confirm"
                       data-placeholder-en="Repeat your password" data-placeholder-es="Repita su contraseña"
                       placeholder="Repeat your password" autocomplete="new-password" required>
                <button type="button" class="dco-peek-btn" id="dco-peek-confirm"
                        onclick="dcoPeekToggle('dco-password-confirm','dco-peek-confirm')"
                        title="Show/hide password" aria-label="Toggle password visibility">
                    <svg class="dco-peek-icon dco-peek-icon--show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="dco-peek-icon dco-peek-icon--hide" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div class="dco-form__actions">
        <button type="submit" class="dco-btn dco-btn--primary" id="dco-btn-step1">
            <span class="dco-btn__text">
                <span data-i18n data-en="Continue" data-es="Continuar">Continue</span> &rarr;
            </span>
            <span class="dco-btn__spinner" style="display:none;"></span>
        </button>
    </div>

    <p class="dco-hint dco-hint--center">
        <span data-i18n data-en="By continuing, you agree to our terms of service." data-es="Al continuar, acepta nuestros términos de servicio.">By continuing, you agree to our terms of service.</span>
    </p>
</form>
