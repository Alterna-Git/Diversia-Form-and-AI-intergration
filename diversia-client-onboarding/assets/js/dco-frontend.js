/**
 * Diversia Client Onboarding — Frontend JavaScript
 * Handles multi-step form transitions, AJAX calls, and UI state management.
 */
(function ($) {
    'use strict';

    // ---------------------------------------------------------------------------
    // State
    // ---------------------------------------------------------------------------
    var state = {
        applicationId: null,
        token: null,
        nonceStep2: null,
        nonceStripe: null,
        lang: 'en',
        aiReasoningEs: '',
        aiReasoningEn: '',
        aiSuggestions: [],
    };

    // ---------------------------------------------------------------------------
    // Init
    // ---------------------------------------------------------------------------
    $(document).ready(function () {
        initLangToggle();
        initStep1();
        initStep2();
        initReviseButton();
        initPayButton();
        initPasswordStrength();
        initCharCounter();
    });

    // ---------------------------------------------------------------------------
    // Language Toggle
    // ---------------------------------------------------------------------------
    function initLangToggle() {
        $('#dco-lang-btn').on('click', function () {
            state.lang = (state.lang === 'en') ? 'es' : 'en';
            applyLanguage(state.lang);
        });
    }

    /**
     * Swaps all [data-i18n] elements to the chosen language.
     * Also updates placeholders and select option text.
     * Also refreshes the AI reasoning block if already populated.
     */
    function applyLanguage(lang) {
        // Targets both the registration form and the payment gate (both share .dco-wrapper)
        var $wrapper = $('.dco-wrapper');
        var $btn     = $('#dco-lang-btn');

        // Toggle button visual state and label
        if (lang === 'es') {
            $btn.addClass('dco-lang-toggle--active');
        } else {
            $btn.removeClass('dco-lang-toggle--active');
        }

        // Swap all text nodes that have data-i18n
        $wrapper.find('[data-i18n]').each(function () {
            var $el  = $(this);
            var text = $el.data(lang);
            if (text !== undefined) {
                // Preserve child elements (e.g. SVG inside buttons) — only swap if element
                // is a leaf node (no child elements, only text)
                if ($el.children().length === 0) {
                    $el.text(text);
                } else {
                    // For elements with children (like buttons with icons), update only the
                    // first text node — handled by targeting specific child spans instead
                    $el.contents().filter(function () {
                        return this.nodeType === 3; // Text node
                    }).first().replaceWith(text);
                }
            }
        });

        // Swap placeholders
        $wrapper.find('[data-placeholder-' + lang + ']').each(function () {
            var placeholder = $(this).data('placeholder-' + lang);
            if (placeholder) {
                $(this).attr('placeholder', placeholder);
            }
        });

        // Swap select option text
        $wrapper.find('select option[data-i18n]').each(function () {
            var text = $(this).data(lang);
            if (text !== undefined) {
                $(this).text(text);
            }
        });

        // Refresh AI reasoning block with the correct language
        refreshReasoningBlock(lang);

        // Re-render suggestions in the active language
        if (state.aiSuggestions && state.aiSuggestions.length) {
            renderSuggestions(state.aiSuggestions, lang);
        }
    }

    /**
     * Updates the reasoning block (qualified or rejected) after language switch.
     */
    function refreshReasoningBlock(lang) {
        if (state.aiReasoningEs || state.aiReasoningEn) {
            var text = (lang === 'es') ? state.aiReasoningEs : state.aiReasoningEn;
            var $block = $('#dco-reasoning-qualified, #dco-reasoning-rejected').filter(':visible');
            if ($block.length && text) {
                $block.html('<p>' + escHtml(text) + '</p>');
            }
        }
    }

    // ---------------------------------------------------------------------------
    // Step 1 — Account Info
    // ---------------------------------------------------------------------------
    function initStep1() {
        $('#dco-form-step1').on('submit', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $btn  = $('#dco-btn-step1');

            // Inject nonce
            $('#dco-nonce-step1').val(dcoData.nonce_step1);

            // Client-side validation
            var errors = validateStep1($form);
            if (errors.length) {
                showError(errors.join('<br>'));
                return;
            }

            hideError();
            setBtnLoading($btn, true);

            $.ajax({
                url:  dcoData.ajaxurl,
                type: 'POST',
                data: $form.serialize(),
                success: function (res) {
                    if (res.success) {
                        state.applicationId  = res.data.application_id;
                        state.nonceStep2     = res.data.nonce_step2;

                        // Populate hidden fields for step 2
                        $('#dco-app-id-step2').val(state.applicationId);
                        $('#dco-nonce-step2').val(state.nonceStep2);
                        $('#dco-application-id').val(state.applicationId);

                        goToStep(2);
                    } else {
                        var msg = (res.data && res.data.message) ? res.data.message : 'An error occurred. / Ocurrió un error.';
                        if (res.data && res.data.code === 429) {
                            showError('⏱ ' + msg);
                        } else {
                            showError(msg);
                        }
                        setBtnLoading($btn, false);
                    }
                },
                error: function () {
                    showError('Connection error. Please try again. / Error de conexión. Por favor intente de nuevo.');
                    setBtnLoading($btn, false);
                }
            });
        });

        // Back button on step 2
        $('#dco-btn-back').on('click', function () {
            hideError();
            goToStep(1);
        });
    }

    function validateStep1($form) {
        var errors = [];
        var email   = $form.find('#dco-email').val().trim();
        var pass    = $form.find('#dco-password').val();
        var confirm = $form.find('#dco-password-confirm').val();

        if (!$form.find('#dco-first-name').val().trim())    errors.push('First name / Nombre es requerido.');
        if (!$form.find('#dco-last-name').val().trim())     errors.push('Last name / Apellido es requerido.');
        if (!email)                                          errors.push('Email es requerido.');
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Invalid email / Correo electrónico inválido.');
        if (!$form.find('#dco-company').val().trim())       errors.push('Organization name / Nombre de organización es requerido.');
        if (pass.length < 8)                                 errors.push('Password must be at least 8 characters / La contraseña debe tener al menos 8 caracteres.');
        if (pass !== confirm)                                errors.push('Passwords do not match / Las contraseñas no coinciden.');

        return errors;
    }

    // ---------------------------------------------------------------------------
    // Step 2 — Trial Needs
    // ---------------------------------------------------------------------------
    function initStep2() {
        $('#dco-form-step2').on('submit', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $btn  = $('#dco-btn-step2');

            var errors = validateStep2($form);
            if (errors.length) {
                showError(errors.join('<br>'));
                return;
            }

            hideError();
            setBtnLoading($btn, true);

            // Show loading screen immediately for better UX
            goToStep(3);

            $.ajax({
                url:     dcoData.ajaxurl,
                type:    'POST',
                data:    $form.serialize(),
                timeout: 60000, // 60s timeout for AI call
                success: function (res) {
                    if (res.success) {
                        handleAiResult(res.data);
                    } else {
                        var msg = (res.data && res.data.message) ? res.data.message : 'Evaluation failed. / La evaluación falló.';
                        goToStep(2);
                        showError(msg);
                        setBtnLoading($btn, false);
                    }
                },
                error: function (xhr, status) {
                    goToStep(2);
                    if (status === 'timeout') {
                        showError('The evaluation timed out. Please try again. / La evaluación tardó demasiado.');
                    } else {
                        showError('Connection error. / Error de conexión.');
                    }
                    setBtnLoading($btn, false);
                }
            });
        });
    }

    function validateStep2($form) {
        var errors = [];
        var budgetMin = parseInt($form.find('#dco-budget-min').val(), 10) || 0;
        var budgetMax = parseInt($form.find('#dco-budget-max').val(), 10) || 0;

        if (!$form.find('#dco-trial-type').val())   errors.push('Trial type / Tipo de ensayo es requerido.');
        if (!$form.find('#dco-org-type').val().trim()) errors.push('Organization type / Tipo de organización es requerido.');
        if (!budgetMin && !budgetMax)               errors.push('Estimated budget / Presupuesto estimado es requerido.');
        else if (budgetMax > 0 && budgetMax < budgetMin) errors.push('Maximum budget must be greater than the minimum. / El presupuesto máximo debe ser mayor al mínimo.');
        return errors;
    }

    // ---------------------------------------------------------------------------
    // AI Result Handler
    // ---------------------------------------------------------------------------
    function handleAiResult(data) {
        var qualified = (data.status === 'qualified');

        // Store both language versions so the toggle can refresh them
        state.aiReasoningEs = data.reasoning_es || '';
        state.aiReasoningEn = data.reasoning_en || '';

        // Show reasoning in the currently active language
        var reasoning = '<p>' + escHtml(state.lang === 'es' ? state.aiReasoningEs : state.aiReasoningEn) + '</p>';

        if (qualified) {
            state.token       = data.token;
            state.nonceStripe = data.nonce_stripe;

            $('#dco-token').val(state.token);
            $('#dco-reasoning-qualified').html(reasoning);
            goToStep('4a');
        } else {
            // Store suggestions and refresh nonce for potential resubmission
            state.aiSuggestions = data.suggestions || [];
            if (data.nonce_step2) {
                state.nonceStep2 = data.nonce_step2;
                $('#dco-nonce-step2').val(state.nonceStep2);
            }

            $('#dco-reasoning-rejected').html(reasoning);
            goToStep('4b');

            // Render AI suggestions and show revise CTA
            if (state.aiSuggestions.length) {
                renderSuggestions(state.aiSuggestions, state.lang);
                $('#dco-suggestions-rejected').show();
                $('#dco-revise-cta').show();
            }
        }
    }

    /**
     * Builds the suggestions list HTML and injects it into #dco-suggestions-list.
     * Called on first render and whenever language is toggled.
     */
    function renderSuggestions(suggestions, lang) {
        var $list = $('#dco-suggestions-list');
        if (!$list.length || !suggestions.length) return;

        var html = '';
        suggestions.forEach(function (s) {
            var issue = escHtml(lang === 'es' ? (s.issue_es || s.issue_en) : s.issue_en);
            var fix   = escHtml(lang === 'es' ? (s.suggestion_es || s.suggestion_en) : s.suggestion_en);
            html += '<li class="dco-suggestions__item">';
            html += '<p class="dco-suggestions__item-issue">' + issue + '</p>';
            html += '<p class="dco-suggestions__item-fix">' + fix + '</p>';
            html += '</li>';
        });

        $list.html(html);
    }

    // ---------------------------------------------------------------------------
    // Revise Button — Rejected screen
    // ---------------------------------------------------------------------------
    function initReviseButton() {
        $('#dco-btn-revise').on('click', function () {
            // Reset step 2 submit button in case it was left in loading state
            setBtnLoading($('#dco-btn-step2'), false);
            hideError();
            goToStep(2);
        });
    }

    // ---------------------------------------------------------------------------
    // Pay Button — Qualified screen
    // ---------------------------------------------------------------------------
    function initPayButton() {
        $('#dco-btn-pay').on('click', function () {
            var $btn = $(this);
            setBtnLoading($btn, true);
            hideError();

            $.ajax({
                url:  dcoData.ajaxurl,
                type: 'POST',
                data: {
                    action:         'dco_create_stripe_session',
                    nonce:          state.nonceStripe || dcoData.nonce_stripe,
                    application_id: state.applicationId,
                    token:          state.token,
                },
                success: function (res) {
                    if (res.success && res.data.checkout_url) {
                        window.location.href = res.data.checkout_url;
                    } else {
                        var msg = (res.data && res.data.message) ? res.data.message : 'Could not initiate payment. / No se pudo iniciar el pago.';
                        showError(msg);
                        setBtnLoading($btn, false);
                    }
                },
                error: function () {
                    showError('Connection error. Please try again. / Error de conexión.');
                    setBtnLoading($btn, false);
                }
            });
        });
    }

    // ---------------------------------------------------------------------------
    // Password Strength Indicator
    // ---------------------------------------------------------------------------
    function initPasswordStrength() {
        $('#dco-password').on('input', function () {
            var pass     = $(this).val();
            var strength = 0;
            var $fill    = $('#dco-strength-fill');
            var $text    = $('#dco-strength-text');

            if (pass.length >= 8)              strength++;
            if (/[A-Z]/.test(pass))            strength++;
            if (/[0-9]/.test(pass))            strength++;
            if (/[^A-Za-z0-9]/.test(pass))    strength++;

            var colors = ['', '#e74c3c', '#f39c12', '#2ecc71', '#27ae60'];
            var labels = ['', 'Débil / Weak', 'Regular / Fair', 'Buena / Good', 'Fuerte / Strong'];

            $fill.css({ width: (strength * 25) + '%', background: colors[strength] || '' });
            $text.text(pass.length ? labels[strength] : '');
        });
    }

    // ---------------------------------------------------------------------------
    // Character Counter
    // ---------------------------------------------------------------------------
    function initCharCounter() {
        $('#dco-notes').on('input', function () {
            var len = $(this).val().length;
            $(this).siblings('.dco-char-count').text(len + ' / 1000');
        });
    }

    // ---------------------------------------------------------------------------
    // Step Navigation
    // ---------------------------------------------------------------------------
    function goToStep(step) {
        // Hide all panels
        $('.dco-panel').hide();

        // Update step indicators
        $('.dco-step').removeClass('dco-step--active dco-step--done');

        if (step === 1) {
            $('#dco-step-1').show();
            setStepState(1);
        } else if (step === 2) {
            $('#dco-step-2').show();
            setStepState(2);
            $('[data-step="1"]').addClass('dco-step--done');
        } else if (step === 3) {
            $('#dco-step-3').show();
            $('[data-step="1"], [data-step="2"]').addClass('dco-step--done');
            $('[data-step="3"]').addClass('dco-step--active');
        } else if (step === '4a') {
            $('#dco-step-4a').show();
            $('.dco-step').addClass('dco-step--done');
        } else if (step === '4b') {
            $('#dco-step-4b').show();
            $('.dco-step').addClass('dco-step--done');
        }

        // Scroll to top of wrapper
        var $wrapper = $('#dco-registration');
        if ($wrapper.length) {
            $('html, body').animate({ scrollTop: $wrapper.offset().top - 40 }, 300);
        }
    }

    function setStepState(active) {
        for (var i = 1; i <= 3; i++) {
            var $s = $('[data-step="' + i + '"]');
            if (i < active) {
                $s.addClass('dco-step--done').removeClass('dco-step--active');
            } else if (i === active) {
                $s.addClass('dco-step--active').removeClass('dco-step--done');
            }
        }
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------
    function showError(msg) {
        var $err = $('#dco-error');
        $err.html(msg).show();
        $('html, body').animate({ scrollTop: $err.offset().top - 20 }, 200);
    }

    function hideError() {
        $('#dco-error').hide().html('');
    }

    function setBtnLoading($btn, loading) {
        if (loading) {
            $btn.find('.dco-btn__text').hide();
            $btn.find('.dco-btn__spinner').show();
            $btn.prop('disabled', true);
        } else {
            $btn.find('.dco-btn__text').show();
            $btn.find('.dco-btn__spinner').hide();
            $btn.prop('disabled', false);
        }
    }

    function escHtml(str) {
        if (!str) return '';
        return $('<div>').text(str).html();
    }

})(jQuery);
