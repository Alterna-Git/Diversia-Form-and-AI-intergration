<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<!-- Recruitment Estimator Tool -->
<div class="le-root" id="le-root">

  <!-- PHASE 1: FORM -->
  <div id="le-phase-form">
    <div class="le-header">
      <span class="le-badge">VALIDATED LATINO DATA · Meta Ads</span>
      <h2 class="le-title">🔬 Recruitment Estimator</h2>
      <p class="le-sub">Verify recruitment viability for clinical trials in Latino communities via Meta Ads</p>
    </div>
    <div class="le-card">
      <form onsubmit="leSubmit(event)" novalidate>
        <div class="le-field">
          <label class="le-label">Health Condition</label>
          <select id="le-condition" class="le-select">
            <optgroup label="Metabolic">
              <option value="OB">Obesity / Overweight — prev. 36.6% Latino</option>
              <option value="PRE">Prediabetes — prev. 38.0% Latino</option>
              <option value="DT2">Type 2 Diabetes — prev. 18.0% Latino</option>
              <option value="SM">Metabolic Syndrome — prev. 35.0% Latino</option>
              <option value="COL">High Cholesterol — prev. 38.0% Latino</option>
              <option value="TIR">Thyroid Disease — prev. 8.0% Latino</option>
              <option value="SOP">PCOS — prev. 10.0% Latino</option>
            </optgroup>
            <optgroup label="Cardiovascular">
              <option value="CVD">Cardiovascular Disease — prev. 6.5% Latino</option>
              <option value="HTA">Hypertension — prev. 30.0% Latino</option>
              <option value="IC">Heart Failure — prev. 1.8% Latino</option>
              <option value="FA">Atrial Fibrillation — prev. 2.0% Latino</option>
            </optgroup>
            <optgroup label="Respiratory">
              <option value="ASM">Asthma / COPD — prev. 16.5% Latino</option>
              <option value="APN">Sleep Apnea — prev. 7.0% Latino</option>
            </optgroup>
            <optgroup label="Mental Health">
              <option value="ANX">Anxiety — prev. 22.0% Latino</option>
              <option value="DEP">Depression — prev. 18.0% Latino</option>
              <option value="TDAH">ADHD — prev. 5.0% Latino</option>
            </optgroup>
            <optgroup label="Neurological">
              <option value="ALZ">Alzheimer's / Dementia — prev. 1.3% Latino</option>
              <option value="PK">Parkinson's Disease — prev. 0.6% Latino</option>
              <option value="MIG">Migraine — prev. 16.0% Latino</option>
              <option value="DCG">Cognitive Impairment — prev. 4.0% Latino</option>
            </optgroup>
            <optgroup label="Autoimmune">
              <option value="LUP">Lupus / SLE — prev. 0.7% Latino</option>
              <option value="ART">Arthritis — prev. 20.0% Latino</option>
              <option value="AUR">Autoimmune / Other — prev. 5.0% Latino</option>
            </optgroup>
            <optgroup label="Oncology">
              <option value="ONC">Oncology / Cancer — prev. 3.0% Latino</option>
              <option value="CRH">Rare Cancer / Hematology — prev. 0.4% Latino</option>
            </optgroup>
            <optgroup label="Chronic Conditions">
              <option value="ERC">Chronic Kidney Disease — prev. 14.0% Latino</option>
              <option value="HEP">Liver Disease — prev. 6.0% Latino</option>
              <option value="DOL">Chronic Pain — prev. 22.0% Latino</option>
              <option value="ERG">GERD / Acid Reflux — prev. 20.0% Latino</option>
              <option value="OST">Osteoporosis — prev. 8.0% Latino</option>
              <option value="NDB">Diabetic Neuropathy — prev. 7.0% Latino</option>
              <option value="GLC">Glaucoma / Cataracts — prev. 6.5% Latino</option>
            </optgroup>
            <optgroup label="Infectious Disease">
              <option value="VIH">HIV / AIDS — prev. 1.5% Latino</option>
              <option value="INF">Infectious Disease (Other) — prev. 2.5% Latino</option>
            </optgroup>
            <optgroup label="Rare &amp; Genetic">
              <option value="TGR">Rare Genetic Disorders — prev. 0.2% Latino</option>
              <option value="CPR">Rare Pediatric Conditions — prev. 0.1% Latino</option>
            </optgroup>
          </select>
        </div>

        <div class="le-field" style="margin-top:18px">
          <label class="le-label">Locations <small style="font-weight:400;text-transform:none;font-size:11px;color:#64748b">— one or more</small></label>
          <div class="le-chips" id="le-loc-chips">
            <button type="button" class="le-chip le-chip--active" data-loc="PR" onclick="leToggleLoc(this)"><span class="le-chip-code">PR</span>Puerto Rico<span class="le-chip-pop">2.4M</span></button>
            <button type="button" class="le-chip" data-loc="CA" onclick="leToggleLoc(this)"><span class="le-chip-code">CA</span>California<span class="le-chip-pop">11.8M</span></button>
            <button type="button" class="le-chip" data-loc="TX" onclick="leToggleLoc(this)"><span class="le-chip-code">TX</span>Texas<span class="le-chip-pop">9.1M</span></button>
            <button type="button" class="le-chip" data-loc="FL" onclick="leToggleLoc(this)"><span class="le-chip-code">FL</span>Florida<span class="le-chip-pop">4.7M</span></button>
            <button type="button" class="le-chip" data-loc="NY" onclick="leToggleLoc(this)"><span class="le-chip-code">NY</span>New York<span class="le-chip-pop">2.9M</span></button>
            <button type="button" class="le-chip" data-loc="AZ" onclick="leToggleLoc(this)"><span class="le-chip-code">AZ</span>Arizona<span class="le-chip-pop">1.8M</span></button>
            <button type="button" class="le-chip" data-loc="IL" onclick="leToggleLoc(this)"><span class="le-chip-code">IL</span>Illinois<span class="le-chip-pop">1.7M</span></button>
            <button type="button" class="le-chip" data-loc="NJ" onclick="leToggleLoc(this)"><span class="le-chip-code">NJ</span>New Jersey<span class="le-chip-pop">1.5M</span></button>
            <button type="button" class="le-chip" data-loc="CO" onclick="leToggleLoc(this)"><span class="le-chip-code">CO</span>Colorado<span class="le-chip-pop">0.9M</span></button>
            <button type="button" class="le-chip" data-loc="NM" onclick="leToggleLoc(this)"><span class="le-chip-code">NM</span>New Mexico<span class="le-chip-pop">0.8M</span></button>
            <button type="button" class="le-chip" data-loc="NV" onclick="leToggleLoc(this)"><span class="le-chip-code">NV</span>Nevada<span class="le-chip-pop">0.7M</span></button>
          </div>
        </div>

        <div class="le-field-row" style="margin-top:18px">
          <div class="le-field">
            <label class="le-label">Budget (USD)</label>
            <div style="position:relative">
              <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#64748b;pointer-events:none">$</span>
              <input id="le-budget" class="le-input" style="padding-left:24px" type="number" min="500" step="100" placeholder="5,000">
            </div>
          </div>
          <div class="le-field">
            <label class="le-label">Patients to Enroll</label>
            <input id="le-target" class="le-input" type="number" min="1" placeholder="50">
          </div>
          <div class="le-field">
            <label class="le-label">Duration (weeks)</label>
            <input id="le-weeks" class="le-input" type="number" min="1" placeholder="12">
          </div>
        </div>

        <p id="le-form-error" style="color:#ef4444;font-size:12px;margin:10px 0 0;min-height:16px"></p>
        <button type="submit" class="le-btn-submit" style="margin-top:16px">🔍 Verify Viability with AI Agent</button>
      </form>
      <p style="font-size:11px;color:#475569;text-align:center;margin:14px 0 0">Sources: HCHS/SOL · CDC/OMH 2025 · Census 2023 · Cross-Cancino 2021 · Tufts CSDD 2019</p>
    </div>
  </div><!-- /le-phase-form -->

  <!-- PHASE 2: VERIFYING -->
  <div id="le-phase-verifying" style="display:none">
    <div class="le-header">
      <span class="le-badge">VERIFICATION IN PROGRESS</span>
      <h2 class="le-title le-title--sm">Analyzing viability…</h2>
      <p class="le-sub" id="le-verify-sub">Processing data…</p>
    </div>
    <div class="le-card">
      <div class="le-checks">
        <div class="le-check-row le-check-row--pending" id="le-chk-0">
          <div class="le-check-icon">👥</div>
          <div class="le-check-body">
            <div class="le-check-hd"><span class="le-check-name">Latino Pool</span><span class="le-check-badge le-check-badge--pending">—</span></div>
            <p class="le-check-detail" style="display:none"></p>
            <span class="le-check-metric" style="display:none"></span>
          </div>
        </div>
        <div class="le-check-row le-check-row--pending" id="le-chk-1">
          <div class="le-check-icon">💰</div>
          <div class="le-check-body">
            <div class="le-check-hd"><span class="le-check-name">Budget</span><span class="le-check-badge le-check-badge--pending">—</span></div>
            <p class="le-check-detail" style="display:none"></p>
            <span class="le-check-metric" style="display:none"></span>
          </div>
        </div>
        <div class="le-check-row le-check-row--pending" id="le-chk-2">
          <div class="le-check-icon">🔬</div>
          <div class="le-check-body">
            <div class="le-check-hd"><span class="le-check-name">Funnel &amp; Timeline</span><span class="le-check-badge le-check-badge--pending">—</span></div>
            <p class="le-check-detail" style="display:none"></p>
            <span class="le-check-metric" style="display:none"></span>
          </div>
        </div>
      </div>
    </div>
  </div><!-- /le-phase-verifying -->

  <!-- PHASE 3: RESULTS -->
  <div id="le-phase-results" style="display:none">

    <div class="le-verdict" id="le-verdict">
      <div class="le-verdict-inner">
        <span class="le-verdict-emoji" id="le-verdict-emoji">✅</span>
        <div>
          <div style="font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;opacity:.6;margin-bottom:4px">Verdict</div>
          <div class="le-verdict-text" id="le-verdict-text">APROBADO</div>
        </div>
        <div style="margin-left:auto;text-align:center">
          <div style="font-family:monospace;font-size:44px;font-weight:600;line-height:1" id="le-verdict-score">—</div>
          <div style="font-size:12px;opacity:.5;font-family:monospace">/100</div>
        </div>
      </div>
    </div>

    <div class="le-card le-ai-card" style="margin-bottom:14px">
      <div class="le-card-hd">🤖 <strong>AI Interpretation</strong><span id="le-ai-prov" style="font-size:11px;color:#64748b;margin-left:6px"></span></div>
      <div id="le-ai-loading" style="display:flex;align-items:center;gap:10px;color:#94a3b8;font-size:14px;padding:4px 0"><span class="le-spinner"></span>Generating analysis…</div>
      <p id="le-ai-text" style="display:none;font-size:14px;line-height:1.75;color:#94a3b8;margin:0"></p>
    </div>

    <div class="le-metrics" style="margin-bottom:14px">
      <div class="le-metric-card"><div class="le-metric-val" id="le-m-pool">—</div><div class="le-metric-lbl">Latino Pool</div><div class="le-metric-sub" id="le-m-pool-sub"></div></div>
      <div class="le-metric-card"><div class="le-metric-val" id="le-m-leads">—</div><div class="le-metric-lbl">Leads Needed</div><div class="le-metric-sub" id="le-m-leads-sub"></div></div>
      <div class="le-metric-card"><div class="le-metric-val" id="le-m-cost">—</div><div class="le-metric-lbl">Realistic Cost</div><div class="le-metric-sub" id="le-m-cost-sub"></div></div>
      <div class="le-metric-card"><div class="le-metric-val" id="le-m-enrolled">—</div><div class="le-metric-lbl">Est. Enrolled</div><div class="le-metric-sub" id="le-m-enrolled-sub"></div></div>
    </div>

    <div class="le-card" style="margin-bottom:14px">
      <div class="le-card-hd">🔻 <strong>Latino Recruitment Funnel</strong><span id="le-sf-note" style="margin-left:auto;font-size:11px;font-family:monospace;color:#64748b"></span></div>
      <div id="le-funnel" class="le-funnel-wrap"></div>
    </div>

    <div class="le-card" style="margin-bottom:14px">
      <div class="le-card-hd">📊 <strong>Cost Scenarios</strong></div>
      <div class="le-scene-grid" id="le-scenarios"></div>
    </div>

    <div class="le-card" style="margin-bottom:14px">
      <div class="le-card-hd">✅ <strong>Triple Verification</strong></div>
      <div class="le-checks" id="le-res-checks"></div>
    </div>

    <div class="le-card" style="margin-bottom:24px">
      <div class="le-card-hd"><span id="le-hints-icon">🛠</span> <strong id="le-hints-title">How to Improve for Approval</strong></div>
      <div id="le-hints" class="le-hint-list"></div>
    </div>

    <div style="text-align:center;padding-bottom:40px">
      <p style="font-size:11px;color:#334155;font-family:monospace;margin-bottom:14px">HCHS/SOL · CDC/OMH 2025 · Census 2023 · Cross-Cancino 2021 · Tufts CSDD 2019</p>
      <button class="le-btn-reset" onclick="leReset()">← New Estimate</button>
    </div>

  </div><!-- /le-phase-results -->

</div><!-- /le-root -->
