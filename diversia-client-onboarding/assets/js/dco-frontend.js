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

        // Resolve org type — if "Other" selected, use the text input value
        var orgType = $form.find('#dco-org-type').val();
        if (orgType === 'Other') {
            var orgOther = $form.find('#dco-org-other').val().trim();
            if (orgOther) {
                $form.find('#dco-org-type').val(orgOther);
                orgType = orgOther;
            }
        }

        // Resolve trial type — if "Other", use the text input
        var trialType = $form.find('#dco-trial-type-value').val();
        if (trialType === 'Other') {
            var trialOther = $form.find('#dco-trial-type-other').val().trim();
            if (trialOther) {
                $form.find('#dco-trial-type-value').val(trialOther);
                trialType = trialOther;
            }
        }

        var budget = parseInt($form.find('#dco-budget').val(), 10) || 0;

        if (!trialType)      errors.push('Trial type / Tipo de ensayo es requerido.');
        if (!orgType.trim()) errors.push('Organization / Organización es requerido.');
        if (!budget)         errors.push('Budget / Presupuesto es requerido.');
        else if (budget < 500) errors.push('Minimum budget is $500. / El presupuesto mínimo es $500.');

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
    // Password Strength Indicator + Criteria Checklist
    // ---------------------------------------------------------------------------
    function initPasswordStrength() {
        $('#dco-password').on('input', function () {
            var pass     = $(this).val();
            var strength = 0;
            var $fill    = $('#dco-strength-fill');
            var $text    = $('#dco-strength-text');

            var hasLength  = pass.length >= 8;
            var hasUpper   = /[A-Z]/.test(pass);
            var hasNumber  = /[0-9]/.test(pass);
            var hasSpecial = /[^A-Za-z0-9]/.test(pass);

            if (hasLength)  strength++;
            if (hasUpper)   strength++;
            if (hasNumber)  strength++;
            if (hasSpecial) strength++;

            // Update live criteria indicators
            setCriterion('dco-crit-length',  hasLength);
            setCriterion('dco-crit-upper',   hasUpper);
            setCriterion('dco-crit-number',  hasNumber);
            setCriterion('dco-crit-special', hasSpecial);

            var colors = ['', '#e74c3c', '#f39c12', '#2ecc71', '#27ae60'];
            var labels = ['', 'Débil / Weak', 'Regular / Fair', 'Buena / Good', 'Fuerte / Strong'];

            $fill.css({ width: (strength * 25) + '%', background: colors[strength] || '' });
            $text.text(pass.length ? labels[strength] : '');
        });
    }

    function setCriterion(id, met) {
        var el = document.getElementById(id);
        if (!el) return;
        if (met) {
            el.classList.add('dco-criterion--met');
        } else {
            el.classList.remove('dco-criterion--met');
        }
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

/* ==========================================================================
   Step 2 — Disease dropdown, org toggle, timeline unit, media upload
   All assigned to window to guarantee global scope regardless of how WP loads the script
   ========================================================================== */

window.dcoToggleDropdown = function(wrapId) {
    var wrap = document.getElementById(wrapId);
    if (!wrap) return;
    var isOpen = wrap.classList.contains('open');
    document.querySelectorAll('.dco-custom-select.open').forEach(function(el) {
        el.classList.remove('open');
    });
    if (!isOpen) wrap.classList.add('open');
};

window.dcoSelectTrialType = function(el, labelEn, labelEs, isOther) {
    var display = document.getElementById('dco-trial-type-display');
    var hidden  = document.getElementById('dco-trial-type-value');
    var btn     = document.getElementById('dco-trial-type-btn');
    var other   = document.getElementById('dco-trial-type-other');
    var wrap    = document.getElementById('dco-trial-type-wrap');
    var langBtn = document.getElementById('dco-lang-btn');
    var lang    = (langBtn && langBtn.classList.contains('dco-lang-toggle--active')) ? 'es' : 'en';
    var label   = (lang === 'es' && labelEs) ? labelEs : labelEn;
    if (display) display.textContent = label;
    if (hidden)  hidden.value = labelEn;
    if (btn)     btn.classList.add('dco-custom-select__btn--selected');
    if (wrap)    wrap.classList.remove('open');
    if (other)   other.style.display = isOther ? 'block' : 'none';
    if (!isOther && other) other.value = '';
};

window.dcoToggleOrgOther = function(sel) {
    var other = document.getElementById('dco-org-other');
    if (!other) return;
    other.style.display = (sel.value === 'Other') ? 'block' : 'none';
    if (sel.value !== 'Other') other.value = '';
};

window.dcoSetTimelineUnit = function(unit) {
    var hidden = document.getElementById('dco-timeline-unit');
    var btnM   = document.getElementById('dco-unit-months');
    var btnD   = document.getElementById('dco-unit-days');
    if (hidden) hidden.value = unit;
    if (btnM)   btnM.classList.toggle('dco-unit-btn--active', unit === 'months');
    if (btnD)   btnD.classList.toggle('dco-unit-btn--active', unit === 'days');
};

window.dcoSwitchMediaTab = function(tab, btn) {
    document.querySelectorAll('.dco-media-tab').forEach(function(t) {
        t.classList.remove('dco-media-tab--active');
    });
    if (btn) btn.classList.add('dco-media-tab--active');
    document.querySelectorAll('.dco-media-pane').forEach(function(p) {
        p.classList.remove('dco-media-pane--active');
        p.style.display = 'none';
    });
    var pane = document.getElementById('dco-pane-' + tab);
    if (pane) {
        pane.classList.add('dco-media-pane--active');
        pane.style.display = 'block';
    }
};

window.dcoPreviewImages = function(input) {
    var container = document.getElementById('dco-img-thumbs');
    if (!container) return;
    container.innerHTML = '';
    Array.from(input.files).slice(0, 10).forEach(function(file) {
        if (!file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            var wrap = document.createElement('div');
            wrap.className = 'dco-media-thumb-wrap';
            var img = document.createElement('img');
            img.className = 'dco-media-thumb';
            img.src = e.target.result;
            var rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'dco-media-thumb-rm';
            rm.textContent = '×';
            rm.onclick = function() { wrap.remove(); };
            wrap.appendChild(img);
            wrap.appendChild(rm);
            container.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
};

window.dcoAddVideoRow = function() {
    var list = document.getElementById('dco-video-url-list');
    if (!list) return;
    var row = document.createElement('div');
    row.className = 'dco-video-row';
    row.innerHTML = '<input class="dco-input" type="url" name="promo_video_urls[]"'
        + ' placeholder="https://youtube.com/watch?v=... or https://vimeo.com/...">'
        + '<button type="button" class="dco-video-remove" onclick="window.dcoRemoveVideoRow(this)" title="Remove">✕</button>';
    list.appendChild(row);
};

window.dcoRemoveVideoRow = function(btn) {
    var row = btn.closest('.dco-video-row');
    var list = document.getElementById('dco-video-url-list');
    if (list && list.querySelectorAll('.dco-video-row').length > 1) {
        row.remove();
    } else {
        row.querySelector('input').value = '';
    }
};

window.dcoPreviewVideos = function(input) {
    var container = document.getElementById('dco-video-file-list');
    if (!container) return;
    container.innerHTML = '';
    Array.from(input.files).forEach(function(file) {
        var size = file.size > 1048576
            ? (file.size / 1048576).toFixed(1) + ' MB'
            : (file.size / 1024).toFixed(0) + ' KB';
        var item = document.createElement('div');
        item.className = 'dco-video-file-item';
        item.innerHTML = '<span class="dco-video-file-item__icon">'
            + '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>'
            + '</span>'
            + '<div class="dco-video-file-item__info">'
            +   '<div class="dco-video-file-item__name">' + file.name + '</div>'
            +   '<div class="dco-video-file-item__size">' + size + '</div>'
            + '</div>'
            + '<button type="button" class="dco-video-file-item__rm" onclick="this.closest(\'.dco-video-file-item\').remove()">×</button>';
        container.appendChild(item);
    });
};

// Password peek toggle
window.dcoPeekToggle = function(inputId, btnId) {
    var input = document.getElementById(inputId);
    var btn   = document.getElementById(btnId);
    if (!input || !btn) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    var iconShow = btn.querySelector('.dco-peek-icon--show');
    var iconHide = btn.querySelector('.dco-peek-icon--hide');
    if (iconShow) iconShow.style.display = show ? 'none' : '';
    if (iconHide) iconHide.style.display = show ? ''     : 'none';
};

// Close dropdown on outside click — runs after DOM ready
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dco-custom-select')) {
            document.querySelectorAll('.dco-custom-select.open').forEach(function(el) {
                el.classList.remove('open');
            });
        }
    });

    // Force-hide inactive media panes on load (belt-and-suspenders vs theme overrides)
    document.querySelectorAll('.dco-media-pane:not(.dco-media-pane--active)').forEach(function(p) {
        p.style.display = 'none';
    });
});
