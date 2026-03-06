<?php if (!defined('ABSPATH')) exit; ?>

<div class="dco-panel__header">
    <h2 class="dco-panel__title" data-i18n data-en="Trial Requirements" data-es="Necesidades del Ensayo">Trial Requirements</h2>
    <p class="dco-panel__subtitle" data-i18n data-en="Tell us about your clinical trial so we can evaluate your application." data-es="Cuéntenos sobre su ensayo clínico para que podamos evaluar su solicitud.">Tell us about your clinical trial so we can evaluate your application.</p>
</div>

<form class="dco-form" id="dco-form-step2" novalidate>
    <input type="hidden" name="action"         value="dco_register_step2">
    <input type="hidden" name="nonce"          id="dco-nonce-step2">
    <input type="hidden" name="application_id" id="dco-app-id-step2">
    <input type="hidden" name="trial_type"     id="dco-trial-type-value">
    <input type="hidden" name="timeline_unit"  id="dco-timeline-unit" value="months">

    <!-- ── Trial Type — custom disease dropdown ─────────────────────────── -->
    <div class="dco-field">
        <label class="dco-label">
            <span data-i18n data-en="Trial Type" data-es="Tipo de Ensayo">Trial Type</span>
            <span class="dco-required">*</span>
        </label>
        <div class="dco-custom-select" id="dco-trial-type-wrap">
            <button type="button" class="dco-custom-select__btn" id="dco-trial-type-btn"
                    onclick="dcoToggleDropdown('dco-trial-type-wrap')">
                <span id="dco-trial-type-display"
                      data-i18n data-en="— Select disease area —" data-es="— Seleccione área de enfermedad —">— Select disease area —</span>
                <svg class="dco-custom-select__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <ul class="dco-custom-select__list" id="dco-trial-type-list">

                <li class="dco-custom-select__sep" style="border-top:none;margin-top:0">Metabolic &amp; Weight</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Obesity / Overweight','Obesidad / Sobrepeso')"><span>Obesity / Overweight</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Prediabetes','Prediabetes')"><span>Prediabetes</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Type 2 Diabetes','Diabetes Tipo 2')"><span>Type 2 Diabetes</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Metabolic Syndrome','Síndrome Metabólico')"><span>Metabolic Syndrome</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'High Cholesterol','Colesterol Alto')"><span>High Cholesterol</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Thyroid Disease / Hypothyroidism','Enfermedad Tiroidea / Hipotiroidismo')"><span>Thyroid Disease / Hypothyroidism</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'PCOS (Polycystic Ovary Syndrome)','SOP (Síndrome de Ovario Poliquístico)')"><span>PCOS (Polycystic Ovary Syndrome)</span></li>

                <li class="dco-custom-select__sep">Cardiovascular</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Cardiovascular Disease (CVD)','Enfermedad Cardiovascular (CVD)')"><span>Cardiovascular Disease (CVD)</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Hypertension','Hipertensión')"><span>Hypertension</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Heart Failure','Insuficiencia Cardíaca')"><span>Heart Failure</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Atrial Fibrillation','Fibrilación Auricular')"><span>Atrial Fibrillation</span></li>

                <li class="dco-custom-select__sep">Respiratory</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Asthma / COPD','Asma / EPOC')"><span>Asthma / COPD</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Sleep Apnea','Apnea del Sueño')"><span>Sleep Apnea</span></li>

                <li class="dco-custom-select__sep">Mental Health</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Anxiety','Ansiedad')"><span>Anxiety</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Depression','Depresión')"><span>Depression</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'ADHD','TDAH')"><span>ADHD</span></li>

                <li class="dco-custom-select__sep">Neurological</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Alzheimer\'s / Dementia','Alzheimer / Demencia')"><span>Alzheimer's / Dementia</span><span class="dco-rare-badge">RARE</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Parkinson\'s Disease','Enfermedad de Parkinson')"><span>Parkinson's Disease</span><span class="dco-rare-badge">RARE</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Migraine','Migraña')"><span>Migraine</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Cognitive Impairment (non-Alzheimer\'s)','Deterioro Cognitivo (no Alzheimer)')"><span>Cognitive Impairment (non-Alzheimer's)</span></li>

                <li class="dco-custom-select__sep">Autoimmune &amp; Musculoskeletal</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Lupus / SLE','Lupus / LES')"><span>Lupus / SLE</span><span class="dco-rare-badge">RARE</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Arthritis','Artritis')"><span>Arthritis</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Autoimmune / Rheumatology (Other)','Autoinmune / Reumatología (Otro)')"><span>Autoimmune / Rheumatology (Other)</span></li>

                <li class="dco-custom-select__sep">Oncology</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Oncology / Cancer','Oncología / Cáncer')"><span>Oncology / Cancer</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Rare Cancer / Hematology','Cáncer Raro / Hematología')"><span>Rare Cancer / Hematology</span><span class="dco-rare-badge">RARE</span></li>

                <li class="dco-custom-select__sep">Chronic Conditions</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Chronic Kidney Disease (CKD)','Enfermedad Crónica Renal (ERC)')"><span>Chronic Kidney Disease (CKD)</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Liver Disease','Enfermedad Hepática')"><span>Liver Disease</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Chronic Pain','Dolor Crónico')"><span>Chronic Pain</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'GERD / Acid Reflux','ERGE / Reflujo')"><span>GERD / Acid Reflux</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Osteoporosis','Osteoporosis')"><span>Osteoporosis</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Diabetic Neuropathy','Neuropatía Diabética')"><span>Diabetic Neuropathy</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Glaucoma / Cataracts','Glaucoma / Cataratas')"><span>Glaucoma / Cataracts</span></li>

                <li class="dco-custom-select__sep">Infectious Disease</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'HIV / AIDS','VIH / SIDA')"><span>HIV / AIDS</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Infectious Disease (Other)','Enfermedad Infecciosa (Otro)')"><span>Infectious Disease (Other)</span></li>

                <li class="dco-custom-select__sep">Rare &amp; Genetic</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Rare Genetic Disorders','Trastornos Genéticos Raros')"><span>Rare Genetic Disorders</span><span class="dco-rare-badge">RARE</span></li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Rare Pediatric Conditions','Condiciones Pediátricas Raras')"><span>Rare Pediatric Conditions</span><span class="dco-rare-badge">RARE</span></li>

                <li class="dco-custom-select__sep">Other</li>
                <li class="dco-custom-select__item" onclick="dcoSelectTrialType(this,'Other','Otro',true)"><span>Other — specify below</span></li>

            </ul>
        </div>
        <input id="dco-trial-type-other" class="dco-input" type="text"
               placeholder="Describe your trial disease area..."
               style="display:none;margin-top:8px">
    </div>

    <!-- ── Target Population ─────────────────────────────────────────────── -->
    <div class="dco-field">
        <label class="dco-label">
            <span data-i18n data-en="Target Population" data-es="Población Objetivo">Target Population</span>
            <span class="dco-required">*</span>
        </label>
        <p class="dco-hint" data-i18n data-en="Select all that apply." data-es="Seleccione todos los que apliquen.">Select all that apply.</p>
        <div class="dco-checkboxes">
            <label class="dco-checkbox-label">
                <input type="checkbox" name="target_population[]" value="Latino/Hispanic (General)">
                <span data-i18n data-en="Latino/Hispanic (General)" data-es="Latino/Hispano (General)">Latino/Hispanic (General)</span>
            </label>
            <label class="dco-checkbox-label">
                <input type="checkbox" name="target_population[]" value="Other">
                <span data-i18n data-en="Other" data-es="Otro">Other</span>
            </label>
        </div>
    </div>

    <!-- ── Campaign Location ────────────────────────────────────────────── -->
    <div class="dco-field">
        <label class="dco-label">
            <span data-i18n data-en="Campaign Location" data-es="Ubicación de la Campaña">Campaign Location</span>
            <span class="dco-required">*</span>
        </label>
        <p class="dco-hint" data-i18n
           data-en="Select the country where you plan to recruit trial participants."
           data-es="Seleccione el país donde planea reclutar participantes del ensayo.">Select the country where you plan to recruit trial participants.</p>

        <select class="dco-input dco-select" id="dco-campaign-country" name="campaign_country"
                required onchange="dcoUpdateCampaignRegions(this.value)">
            <option value="" data-i18n data-en="— Select country —" data-es="— Seleccione país —">— Select country —</option>
            <option value="US">United States / Estados Unidos</option>
            <option value="PR">Puerto Rico</option>
        </select>

        <!-- Primary US state for viability analysis (shown when US is selected) -->
        <div id="dco-location-wrap" style="display:none;margin-top:10px;">
            <label class="dco-label" for="dco-location" style="font-size:13px;margin-bottom:4px;">
                <span data-i18n data-en="Primary State (optional — improves analysis)" data-es="Estado Principal (opcional — mejora el análisis)">Primary State (optional — improves analysis)</span>
            </label>
            <select class="dco-input dco-select" id="dco-location" name="location_code">
                <option value="" data-i18n data-en="— All US States —" data-es="— Todos los Estados —">— All US States —</option>
                <optgroup label="Northeast">
                    <option value="CT">Connecticut</option>
                    <option value="ME">Maine</option>
                    <option value="MA">Massachusetts</option>
                    <option value="NH">New Hampshire</option>
                    <option value="NJ">New Jersey</option>
                    <option value="NY">New York</option>
                    <option value="PA">Pennsylvania</option>
                    <option value="RI">Rhode Island</option>
                    <option value="VT">Vermont</option>
                </optgroup>
                <optgroup label="Southeast">
                    <option value="AL">Alabama</option>
                    <option value="AR">Arkansas</option>
                    <option value="FL">Florida</option>
                    <option value="GA">Georgia</option>
                    <option value="KY">Kentucky</option>
                    <option value="LA">Louisiana</option>
                    <option value="MS">Mississippi</option>
                    <option value="NC">North Carolina</option>
                    <option value="SC">South Carolina</option>
                    <option value="TN">Tennessee</option>
                    <option value="VA">Virginia</option>
                    <option value="WV">West Virginia</option>
                </optgroup>
                <optgroup label="Midwest">
                    <option value="IL">Illinois</option>
                    <option value="IN">Indiana</option>
                    <option value="IA">Iowa</option>
                    <option value="KS">Kansas</option>
                    <option value="MI">Michigan</option>
                    <option value="MN">Minnesota</option>
                    <option value="MO">Missouri</option>
                    <option value="NE">Nebraska</option>
                    <option value="ND">North Dakota</option>
                    <option value="OH">Ohio</option>
                    <option value="SD">South Dakota</option>
                    <option value="WI">Wisconsin</option>
                </optgroup>
                <optgroup label="Southwest">
                    <option value="AZ">Arizona</option>
                    <option value="NM">New Mexico</option>
                    <option value="OK">Oklahoma</option>
                    <option value="TX">Texas</option>
                </optgroup>
                <optgroup label="West">
                    <option value="AK">Alaska</option>
                    <option value="CA">California</option>
                    <option value="CO">Colorado</option>
                    <option value="HI">Hawaii</option>
                    <option value="ID">Idaho</option>
                    <option value="MT">Montana</option>
                    <option value="NV">Nevada</option>
                    <option value="OR">Oregon</option>
                    <option value="UT">Utah</option>
                    <option value="WA">Washington</option>
                    <option value="WY">Wyoming</option>
                </optgroup>
                <optgroup label="Mid-Atlantic / Other">
                    <option value="DC">District of Columbia</option>
                    <option value="MD">Maryland</option>
                    <option value="DE">Delaware</option>
                    <option value="PR">Puerto Rico</option>
                </optgroup>
            </select>
        </div>

        <!-- Other country text input -->
        <input id="dco-campaign-country-other" class="dco-input" type="text"
               name="campaign_country_other"
               data-placeholder-en="Specify country or countries..."
               data-placeholder-es="Especifique el país o países..."
               placeholder="Specify country or countries..."
               style="display:none;margin-top:8px;">
    </div>

    <!-- ── Organization Type — select ───────────────────────────────────── -->
    <div class="dco-field">
        <label class="dco-label" for="dco-org-type">
            <span data-i18n data-en="Organization" data-es="Organización">Organization</span>
            <span class="dco-required">*</span>
        </label>
        <select class="dco-input dco-select" id="dco-org-type" name="organization_type"
                required onchange="dcoToggleOrgOther(this)">
            <option value="" data-i18n data-en="— Select —" data-es="— Seleccione —">— Select —</option>
            <option value="Researcher"     data-i18n data-en="Researcher"     data-es="Investigador">Researcher</option>
            <option value="CRO"            data-i18n data-en="CRO"            data-es="ORC">CRO</option>
            <option value="University"     data-i18n data-en="University"     data-es="Universidad">University</option>
            <option value="Pharmaceutical" data-i18n data-en="Pharmaceutical" data-es="Farmacéutica">Pharmaceutical</option>
            <option value="Other"          data-i18n data-en="Other"          data-es="Otro">Other</option>
        </select>
        <input id="dco-org-other" class="dco-input" type="text"
               placeholder="Describe your organization..."
               style="display:none;margin-top:8px">
    </div>

    <!-- ── Budget — single field ─────────────────────────────────────────── -->
    <div class="dco-field">
        <label class="dco-label" for="dco-budget">
            <span data-i18n data-en="Budget (USD)" data-es="Presupuesto (USD)">Budget (USD)</span>
            <span class="dco-required">*</span>
        </label>
        <div class="dco-input-prefix-wrap">
            <span class="dco-input-prefix">$</span>
            <input class="dco-input dco-input--prefixed" type="number" id="dco-budget" name="budget_min"
                   data-placeholder-en="e.g. 10,000" data-placeholder-es="ej. 10,000"
                   placeholder="e.g. 10,000" min="500" step="500" required>
        </div>
        <span class="dco-hint" style="margin-top:5px;display:block">
            <span data-i18n data-en="Minimum $500" data-es="Mínimo $500">Minimum $500</span>
        </span>
    </div>

    <!-- ── Enrollment Goal + Timeline ───────────────────────────────────── -->
    <div class="dco-form__row dco-form__row--2col">
        <div class="dco-field">
            <label class="dco-label" for="dco-enrollment">
                <span data-i18n data-en="Enrollment Goal" data-es="Meta de Participantes">Enrollment Goal</span>
                <span class="dco-required">*</span>
            </label>
            <input class="dco-input" type="number" id="dco-enrollment-goal" name="enrollment_goal"
                   data-placeholder-en="e.g. 150" data-placeholder-es="ej. 150"
                   placeholder="e.g. 150" min="1" max="100000" required>
            <p id="dco-enrollment-hint" class="dco-hint dco-hint--suggestion" style="margin-top:4px;display:none;"></p>
        </div>
        <div class="dco-field">
            <label class="dco-label" for="dco-timeline">
                <span data-i18n data-en="Timeline" data-es="Duración">Timeline</span>
                <span class="dco-required">*</span>
            </label>
            <div class="dco-input-unit-wrap">
                <input class="dco-input dco-input--unit" type="number" id="dco-timeline" name="timeline_value"
                       data-placeholder-en="e.g. 18" data-placeholder-es="ej. 18"
                       placeholder="e.g. 18" min="1" max="9999" required
                       oninput="dcoTimelineAutoConvert(this)">
                <div class="dco-unit-toggle">
                    <button type="button" class="dco-unit-btn dco-unit-btn--active" id="dco-unit-months"
                            onclick="dcoSetTimelineUnit('months')">
                        <span data-i18n data-en="Months" data-es="Meses">Months</span>
                    </button>
                    <button type="button" class="dco-unit-btn" id="dco-unit-weeks"
                            onclick="dcoSetTimelineUnit('weeks')">
                        <span data-i18n data-en="Weeks" data-es="Semanas">Weeks</span>
                    </button>
                </div>
            </div>
            <p id="dco-timeline-hint" class="dco-hint dco-hint--suggestion" style="margin-top:4px;display:none;"></p>
        </div>
    </div>

    <!-- ── Organization Website ──────────────────────────────────────────── -->
    <div class="dco-field">
        <label class="dco-label" for="dco-website">
            <span data-i18n data-en="Organization Website" data-es="Sitio Web de la Organización">Organization Website</span>
        </label>
        <input class="dco-input" type="url" id="dco-website" name="organization_website"
               data-placeholder-en="https://www.organization.com" data-placeholder-es="https://www.organización.com"
               placeholder="https://www.organization.com" autocomplete="url">
    </div>

    <!-- ── Additional Notes ──────────────────────────────────────────────── -->
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

    <!-- ── Promotional Content ───────────────────────────────────────────── -->
    <div class="dco-field">
        <label class="dco-label">
            <span data-i18n data-en="Promotional Content" data-es="Contenido Promocional">Promotional Content</span>
            <span class="dco-promo-optional" data-i18n data-en="(optional)" data-es="(opcional)">(optional)</span>
        </label>
        <p class="dco-hint" style="margin-bottom:12px;display:block"
           data-i18n
           data-en="Upload images or add video links to help attract participants to your trial."
           data-es="Sube imágenes o agrega enlaces de video para atraer participantes a su ensayo.">
            Upload images or add video links to help attract participants to your trial.
        </p>

        <div class="dco-media-tabs">
            <button type="button" class="dco-media-tab dco-media-tab--active" onclick="dcoSwitchMediaTab('images',this)">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span data-i18n data-en="Images" data-es="Imágenes">Images</span>
            </button>
            <button type="button" class="dco-media-tab" onclick="dcoSwitchMediaTab('videos',this)">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                <span data-i18n data-en="Videos" data-es="Videos">Videos</span>
            </button>
        </div>

        <!-- Images pane -->
        <div class="dco-media-pane dco-media-pane--active" id="dco-pane-images">
            <label class="dco-media-drop" for="dco-img-input">
                <span class="dco-media-drop__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </span>
                <span class="dco-media-drop__label">
                    <span data-i18n data-en="Drop images here or" data-es="Arrastra imágenes aquí o">Drop images here or</span>
                    <u class="dco-browse-link"><span data-i18n data-en="browse files" data-es="selecciona archivos">browse files</span></u>
                </span>
                <span class="dco-media-drop__sub"
                      data-i18n data-en="JPG, PNG, GIF · Max 5 MB each · Up to 10 images"
                      data-es="JPG, PNG, GIF · Máx 5 MB cada una · Hasta 10 imágenes">JPG, PNG, GIF · Max 5 MB each · Up to 10 images</span>
            </label>
            <input type="file" id="dco-img-input" name="promo_images[]" accept="image/*" multiple style="display:none" onchange="dcoPreviewImages(this)">
            <div class="dco-media-thumbs" id="dco-img-thumbs"></div>
        </div>

        <!-- Videos pane -->
        <div class="dco-media-pane" id="dco-pane-videos">
            <p class="dco-hint" style="font-weight:600;color:var(--dco-navy);margin-bottom:8px">
                <span data-i18n data-en="Paste a video link" data-es="Pegar enlace de video">Paste a video link</span>
            </p>
            <div class="dco-video-list" id="dco-video-url-list">
                <div class="dco-video-row">
                    <input class="dco-input" type="url" name="promo_video_urls[]"
                           placeholder="https://youtube.com/watch?v=... or https://vimeo.com/...">
                    <button type="button" class="dco-video-remove" onclick="dcoRemoveVideoRow(this)" title="Remove">✕</button>
                </div>
            </div>
            <button type="button" class="dco-add-video-btn" onclick="dcoAddVideoRow()">
                + <span data-i18n data-en="Add another video link" data-es="Agregar otro enlace de video">Add another video link</span>
            </button>
            <div class="dco-or-divider"><span data-i18n data-en="or" data-es="o">or</span></div>
            <p class="dco-hint" style="font-weight:600;color:var(--dco-navy);margin-bottom:8px">
                <span data-i18n data-en="Upload video files" data-es="Subir archivos de video">Upload video files</span>
            </p>
            <label class="dco-media-drop" for="dco-video-input">
                <span class="dco-media-drop__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                </span>
                <span class="dco-media-drop__label">
                    <span data-i18n data-en="Drop videos here or" data-es="Arrastra videos aquí o">Drop videos here or</span>
                    <u class="dco-browse-link"><span data-i18n data-en="browse files" data-es="selecciona archivos">browse files</span></u>
                </span>
                <span class="dco-media-drop__sub"
                      data-i18n data-en="MP4, MOV, AVI, WebM · Max 500 MB each"
                      data-es="MP4, MOV, AVI, WebM · Máx 500 MB cada uno">MP4, MOV, AVI, WebM · Max 500 MB each</span>
            </label>
            <input type="file" id="dco-video-input" name="promo_videos[]" accept="video/*" multiple style="display:none" onchange="dcoPreviewVideos(this)">
            <div class="dco-video-file-list" id="dco-video-file-list"></div>
        </div>
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
