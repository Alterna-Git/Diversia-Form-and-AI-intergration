<?php if (!defined('ABSPATH')) exit; ?>

<div class="dco-panel__header">
    <h2 class="dco-panel__title" data-i18n data-en="Trial Requirements" data-es="Necesidades del Ensayo">Trial Requirements</h2>
    <p class="dco-panel__subtitle" data-i18n data-en="Tell us about your clinical trial so we can evaluate your application." data-es="Cuéntenos sobre su ensayo clínico para que podamos evaluar su solicitud.">Tell us about your clinical trial so we can evaluate your application.</p>
</div>

<form class="dco-form" id="dco-form-step2" novalidate>
    <input type="hidden" name="action" value="dco_register_step2">
    <input type="hidden" name="nonce" id="dco-nonce-step2">
    <input type="hidden" name="application_id" id="dco-app-id-step2">

    <!-- Trial Type -->
    <div class="dco-field">
        <label class="dco-label" for="dco-trial-type">
            <span data-i18n data-en="Trial Type" data-es="Tipo de Ensayo">Trial Type</span>
            <span class="dco-required">*</span>
        </label>
        <select class="dco-input dco-select" id="dco-trial-type" name="trial_type" required>
            <option value="" data-i18n data-en="— Select —" data-es="— Seleccione —">— Select —</option>
            <option value="Phase I"           data-i18n data-en="Phase I — Safety"       data-es="Fase I — Seguridad">Phase I — Safety</option>
            <option value="Phase II"          data-i18n data-en="Phase II — Efficacy"     data-es="Fase II — Eficacia">Phase II — Efficacy</option>
            <option value="Phase III"         data-i18n data-en="Phase III — Comparative" data-es="Fase III — Comparativo">Phase III — Comparative</option>
            <option value="Phase IV"          data-i18n data-en="Phase IV — Post-market"  data-es="Fase IV — Post-mercado">Phase IV — Post-market</option>
            <option value="Observational Study" data-i18n data-en="Observational Study"   data-es="Estudio Observacional">Observational Study</option>
            <option value="Other"             data-i18n data-en="Other"                   data-es="Otro">Other</option>
        </select>
    </div>

    <!-- Target Population -->
    <div class="dco-field">
        <label class="dco-label">
            <span data-i18n data-en="Target Population" data-es="Población Objetivo">Target Population</span>
            <span class="dco-required">*</span>
        </label>
        <p class="dco-hint" data-i18n data-en="Select all that apply." data-es="Seleccione todos los que apliquen.">Select all that apply.</p>
        <div class="dco-checkboxes">
            <?php
            $populations = array(
                'Latino/Hispanic (General)' => array('en' => 'Latino/Hispanic (General)',     'es' => 'Latino/Hispano (General)'),
                'Other'                     => array('en' => 'Other',                         'es' => 'Otro'),
            );
            foreach ($populations as $val => $labels):
            ?>
            <label class="dco-checkbox-label">
                <input type="checkbox" name="target_population[]" value="<?php echo esc_attr($val); ?>">
                <span data-i18n data-en="<?php echo esc_attr($labels['en']); ?>" data-es="<?php echo esc_attr($labels['es']); ?>"><?php echo esc_html($labels['en']); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Organization Type -->
    <div class="dco-field">
        <label class="dco-label" for="dco-org-type">
            <span data-i18n data-en="Organization Type" data-es="Tipo de Organización">Organization Type</span>
            <span class="dco-required">*</span>
        </label>
        <input class="dco-input" type="text" id="dco-org-type" name="organization_type"
               data-placeholder-en="e.g. Pharmaceutical Company, CRO, Academic Institution..."
               data-placeholder-es="ej. Empresa Farmacéutica, CRO, Institución Académica..."
               placeholder="e.g. Pharmaceutical Company, CRO, Academic Institution..."
               maxlength="150" required autocomplete="organization">
    </div>

    <!-- Estimated Budget -->
    <div class="dco-field">
        <label class="dco-label">
            <span data-i18n data-en="Estimated Budget (USD)" data-es="Presupuesto Estimado (USD)">Estimated Budget (USD)</span>
            <span class="dco-required">*</span>
        </label>
        <div class="dco-form__row dco-form__row--2col">
            <div class="dco-field dco-field--no-margin">
                <label class="dco-label dco-label--sub" for="dco-budget-min">
                    <span data-i18n data-en="Minimum" data-es="Mínimo">Minimum</span>
                </label>
                <div class="dco-input-prefix-wrap">
                    <span class="dco-input-prefix">$</span>
                    <input class="dco-input dco-input--prefixed" type="number" id="dco-budget-min" name="budget_min"
                           data-placeholder-en="e.g. 50000" data-placeholder-es="ej. 50000"
                           placeholder="e.g. 50000" min="0" step="1000">
                </div>
            </div>
            <div class="dco-field dco-field--no-margin">
                <label class="dco-label dco-label--sub" for="dco-budget-max">
                    <span data-i18n data-en="Maximum" data-es="Máximo">Maximum</span>
                </label>
                <div class="dco-input-prefix-wrap">
                    <span class="dco-input-prefix">$</span>
                    <input class="dco-input dco-input--prefixed" type="number" id="dco-budget-max" name="budget_max"
                           data-placeholder-en="e.g. 200000" data-placeholder-es="ej. 200000"
                           placeholder="e.g. 200000" min="0" step="1000">
                </div>
            </div>
        </div>
    </div>

    <div class="dco-form__row dco-form__row--2col">
        <div class="dco-field">
            <label class="dco-label" for="dco-enrollment">
                <span data-i18n data-en="Enrollment Goal" data-es="Meta de Participantes">Enrollment Goal</span>
            </label>
            <input class="dco-input" type="number" id="dco-enrollment" name="enrollment_goal"
                   data-placeholder-en="e.g. 150" data-placeholder-es="ej. 150"
                   placeholder="e.g. 150" min="1" max="100000">
        </div>
        <div class="dco-field">
            <label class="dco-label" for="dco-timeline">
                <span data-i18n data-en="Timeline" data-es="Duración">Timeline</span>
            </label>
            <div class="dco-input-unit-wrap">
                <input class="dco-input dco-input--unit" type="number" id="dco-timeline" name="timeline_value"
                       data-placeholder-en="e.g. 18" data-placeholder-es="ej. 18"
                       placeholder="e.g. 18" min="1" max="9999">
                <select class="dco-unit-select" name="timeline_unit" id="dco-timeline-unit">
                    <option value="months" data-i18n data-en="Months" data-es="Meses">Months</option>
                    <option value="days"   data-i18n data-en="Days"   data-es="Días">Days</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Organization Website -->
    <div class="dco-field">
        <label class="dco-label" for="dco-website">
            <span data-i18n data-en="Organization Website" data-es="Sitio Web de la Organización">Organization Website</span>
        </label>
        <input class="dco-input" type="url" id="dco-website" name="organization_website"
               data-placeholder-en="https://www.organization.com" data-placeholder-es="https://www.organización.com"
               placeholder="https://www.organization.com" autocomplete="url">
    </div>

    <!-- Additional Notes -->
    <div class="dco-field">
        <label class="dco-label" for="dco-notes">
            <span data-i18n data-en="Additional Notes" data-es="Notas Adicionales">Additional Notes</span>
        </label>
        <textarea class="dco-input dco-textarea" id="dco-notes" name="additional_notes"
                  rows="4" maxlength="1000"
                  data-placeholder-en="Describe any additional details about your trial or special requirements..."
                  data-placeholder-es="Describa cualquier detalle adicional sobre su ensayo o requisitos especiales..."
                  placeholder="Describe any additional details about your trial or special requirements..."></textarea>
        <span class="dco-char-count">0 / 1000</span>
    </div>

    <div class="dco-form__actions dco-form__actions--spaced">
        <button type="button" class="dco-btn dco-btn--secondary" id="dco-btn-back">
            &larr; <span data-i18n data-en="Back" data-es="Atrás">Back</span>
        </button>
        <button type="submit" class="dco-btn dco-btn--primary" id="dco-btn-step2">
            <span class="dco-btn__text">
                <span data-i18n data-en="Submit Application" data-es="Enviar Solicitud">Submit Application</span>
            </span>
            <span class="dco-btn__spinner" style="display:none;"></span>
        </button>
    </div>
</form>
