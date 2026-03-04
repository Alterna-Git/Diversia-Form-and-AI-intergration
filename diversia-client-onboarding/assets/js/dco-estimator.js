/* Diversia Health — Recruitment Estimator (standalone, no jQuery dependency) */

(function() {
  'use strict';

  // ── Location data (52 entries: 50 states + DC + PR) ──────────────────────────

  var _EL = {
    // ── U.S. Territory ──────────────────────────────────────────────────────
    PR:{ n:'Puerto Rico',       pop:2430000,  reach:1822500,  hm:1.15, cm:1.00 },
    // ── High-volume states ──────────────────────────────────────────────────
    CA:{ n:'California',        pop:11820000, reach:8865000,  hm:1.05, cm:1.40 },
    TX:{ n:'Texas',             pop:9100000,  reach:6825000,  hm:1.10, cm:1.25 },
    FL:{ n:'Florida',           pop:4650000,  reach:3487500,  hm:1.04, cm:1.25 },
    NY:{ n:'New York',          pop:2850000,  reach:2137500,  hm:1.00, cm:1.40 },
    AZ:{ n:'Arizona',           pop:1750000,  reach:1312500,  hm:1.12, cm:1.25 },
    IL:{ n:'Illinois',          pop:1650000,  reach:1237500,  hm:1.06, cm:1.25 },
    NJ:{ n:'New Jersey',        pop:1500000,  reach:1125000,  hm:1.02, cm:1.40 },
    // ── Mid-volume states ───────────────────────────────────────────────────
    GA:{ n:'Georgia',           pop:825000,   reach:618750,   hm:1.05, cm:1.15 },
    NC:{ n:'North Carolina',    pop:790000,   reach:592500,   hm:1.03, cm:1.10 },
    CO:{ n:'Colorado',          pop:935000,   reach:701250,   hm:0.95, cm:1.25 },
    WA:{ n:'Washington',        pop:685000,   reach:513750,   hm:0.98, cm:1.25 },
    PA:{ n:'Pennsylvania',      pop:685000,   reach:513750,   hm:1.00, cm:1.20 },
    MA:{ n:'Massachusetts',     pop:650000,   reach:487500,   hm:1.08, cm:1.35 },
    NV:{ n:'Nevada',            pop:650000,   reach:487500,   hm:1.10, cm:1.25 },
    VA:{ n:'Virginia',          pop:570000,   reach:427500,   hm:1.00, cm:1.25 },
    MD:{ n:'Maryland',          pop:540000,   reach:405000,   hm:1.02, cm:1.30 },
    CT:{ n:'Connecticut',       pop:465000,   reach:348750,   hm:1.05, cm:1.30 },
    OR:{ n:'Oregon',            pop:450000,   reach:337500,   hm:0.97, cm:1.20 },
    UT:{ n:'Utah',              pop:334000,   reach:250500,   hm:0.96, cm:1.10 },
    MI:{ n:'Michigan',          pop:428000,   reach:321000,   hm:1.01, cm:1.15 },
    OH:{ n:'Ohio',              pop:371000,   reach:278250,   hm:1.01, cm:1.10 },
    TN:{ n:'Tennessee',         pop:367000,   reach:275250,   hm:1.04, cm:1.05 },
    IN:{ n:'Indiana',           pop:356000,   reach:267000,   hm:1.03, cm:1.05 },
    NM:{ n:'New Mexico',        pop:760000,   reach:570000,   hm:1.12, cm:1.10 },
    MN:{ n:'Minnesota',         pop:311000,   reach:233250,   hm:0.97, cm:1.15 },
    WI:{ n:'Wisconsin',         pop:311000,   reach:233250,   hm:1.00, cm:1.10 },
    OK:{ n:'Oklahoma',          pop:292000,   reach:219000,   hm:1.08, cm:1.00 },
    KS:{ n:'Kansas',            pop:259000,   reach:194250,   hm:1.05, cm:1.00 },
    SC:{ n:'South Carolina',    pop:240000,   reach:180000,   hm:1.03, cm:1.05 },
    ID:{ n:'Idaho',             pop:221000,   reach:165750,   hm:1.00, cm:1.00 },
    MO:{ n:'Missouri',          pop:210000,   reach:157500,   hm:1.02, cm:1.05 },
    RI:{ n:'Rhode Island',      pop:154000,   reach:115500,   hm:1.10, cm:1.25 },
    // ── Lower-volume states ─────────────────────────────────────────────────
    AR:{ n:'Arkansas',          pop:195000,   reach:146250,   hm:1.07, cm:0.95 },
    AL:{ n:'Alabama',           pop:180000,   reach:135000,   hm:1.05, cm:0.95 },
    NE:{ n:'Nebraska',          pop:176000,   reach:132000,   hm:1.04, cm:1.00 },
    KY:{ n:'Kentucky',          pop:169000,   reach:126750,   hm:1.03, cm:0.95 },
    IA:{ n:'Iowa',              pop:172000,   reach:129000,   hm:1.03, cm:0.95 },
    LA:{ n:'Louisiana',         pop:206000,   reach:154500,   hm:1.06, cm:1.00 },
    MS:{ n:'Mississippi',       pop:82000,    reach:61500,    hm:1.07, cm:0.90 },
    DE:{ n:'Delaware',          pop:79000,    reach:59250,    hm:1.02, cm:1.20 },
    DC:{ n:'Washington, D.C.',  pop:86000,    reach:64500,    hm:1.05, cm:1.45 },
    NH:{ n:'New Hampshire',     pop:45000,    reach:33750,    hm:0.98, cm:1.15 },
    HI:{ n:'Hawaii',            pop:45000,    reach:33750,    hm:0.96, cm:1.25 },
    AK:{ n:'Alaska',            pop:41000,    reach:30750,    hm:0.95, cm:1.10 },
    MT:{ n:'Montana',           pop:41000,    reach:30750,    hm:0.97, cm:0.90 },
    WV:{ n:'West Virginia',     pop:30000,    reach:22500,    hm:1.02, cm:0.85 },
    ND:{ n:'North Dakota',      pop:26000,    reach:19500,    hm:1.00, cm:0.90 },
    ME:{ n:'Maine',             pop:22000,    reach:16500,    hm:0.96, cm:1.00 },
    VT:{ n:'Vermont',           pop:15000,    reach:11250,    hm:0.95, cm:1.00 },
    WY:{ n:'Wyoming',           pop:52000,    reach:39000,    hm:0.98, cm:0.90 },
    SD:{ n:'South Dakota',      pop:37000,    reach:27750,    hm:1.00, cm:0.90 },
  };

  // Condition display names (codes match _LE.D keys)
  var _EC_NAMES = {
    OB:'Obesidad/Sobrepeso',   PRE:'Prediabetes',         DT2:'Diabetes Tipo 2',
    SM:'Síndrome Metabólico',  COL:'Colesterol Alto',     TIR:'Enfermedad Tiroidea',
    SOP:'SOP',                 CVD:'Enf. Cardiovascular', HTA:'Hipertensión',
    IC:'Insuf. Cardíaca',      FA:'Fibrilación Auricular', ASM:'Asma/EPOC',
    APN:'Apnea del Sueño',     ANX:'Ansiedad',            DEP:'Depresión',
    TDAH:'TDAH',               ALZ:'Alzheimer/Demencia',  PK:'Parkinson',
    MIG:'Migraña',             DCG:'Deterioro Cognitivo', LUP:'Lupus/LES',
    ART:'Artritis',            AUR:'Autoinmune (Otro)',   ONC:'Oncología/Cáncer',
    CRH:'Cáncer Raro',         ERC:'Enf. Renal Crónica', HEP:'Enf. Hepática',
    DOL:'Dolor Crónico',       ERG:'ERGE/Reflujo',        OST:'Osteoporosis',
    NDB:'Neuropatía Diabética',GLC:'Glaucoma/Cataratas',  VIH:'VIH/SIDA',
    INF:'Enf. Infecciosa',     TGR:'Trast. Genéticos Raros', CPR:'Cond. Pediátricas Raras',
  };

  // ── LeadEngine condition + funnel data ────────────────────────────────────────

  var _LE = {
    // Maps preview.html disease value → LeadEngine condition key
    keyMap: {
      'obesity':'OB','prediabetes':'PRE','diabetes':'DT2','metabolic':'SM',
      'cholesterol':'COL','thyroid':'TIR','pcos':'SOP',
      'cardiovascular':'CVD','hypertension':'HTA','heart-failure':'IC','afib':'FA',
      'asthma':'ASM','sleep-apnea':'APN',
      'anxiety':'ANX','depression':'DEP','adhd':'TDAH',
      'alzheimers':'ALZ','parkinsons':'PK','migraine':'MIG','cognitive':'DCG',
      'lupus':'LUP','arthritis':'ART','autoimmune':'AUR',
      'oncology':'ONC','rare-cancer':'CRH',
      'ckd':'ERC','liver':'HEP','chronic-pain':'DOL','gerd':'ERG',
      'osteoporosis':'OST','diabetic-neuropathy':'NDB','glaucoma':'GLC',
      'hiv':'VIH','infectious':'INF',
      'rare-genetic':'TGR','rare-pediatric':'CPR'
    },
    // Condition data: cL=optimistic CPL, cM=realistic CPL, cH=conservative CPL, prev=prevalence, diff=difficulty
    D: {
      'OB' :{cL:18,cM:28,cH:42,prev:0.366,diff:'low'},
      'PRE':{cL:22,cM:35,cH:50,prev:0.38, diff:'medium'},
      'DT2':{cL:25,cM:38,cH:55,prev:0.18, diff:'medium'},
      'SM' :{cL:30,cM:48,cH:70,prev:0.35, diff:'medium'},
      'COL':{cL:22,cM:32,cH:48,prev:0.38, diff:'low'},
      'TIR':{cL:28,cM:42,cH:62,prev:0.08, diff:'medium'},
      'SOP':{cL:25,cM:38,cH:55,prev:0.10, diff:'medium'},
      'CVD':{cL:35,cM:55,cH:80,prev:0.065,diff:'high'},
      'HTA':{cL:20,cM:32,cH:48,prev:0.30, diff:'low'},
      'IC' :{cL:45,cM:68,cH:95,prev:0.018,diff:'high'},
      'FA' :{cL:42,cM:65,cH:90,prev:0.02, diff:'high'},
      'ASM':{cL:22,cM:35,cH:52,prev:0.165,diff:'medium'},
      'APN':{cL:25,cM:40,cH:58,prev:0.07, diff:'medium'},
      'ANX':{cL:15,cM:25,cH:38,prev:0.22, diff:'low'},
      'DEP':{cL:15,cM:25,cH:38,prev:0.18, diff:'low'},
      'TDAH':{cL:20,cM:32,cH:48,prev:0.05,diff:'medium'},
      'ALZ':{cL:55,cM:85,cH:120,prev:0.013,diff:'very_high'},
      'PK' :{cL:60,cM:90,cH:130,prev:0.006,diff:'very_high'},
      'MIG':{cL:18,cM:28,cH:42,prev:0.16, diff:'low'},
      'DCG':{cL:40,cM:60,cH:85,prev:0.04, diff:'high'},
      'LUP':{cL:65,cM:95,cH:140,prev:0.007,diff:'very_high'},
      'ART':{cL:20,cM:32,cH:48,prev:0.20, diff:'low'},
      'AUR':{cL:35,cM:55,cH:78,prev:0.05, diff:'high'},
      'ONC':{cL:45,cM:70,cH:100,prev:0.03, diff:'high'},
      'CRH':{cL:80,cM:120,cH:175,prev:0.004,diff:'very_high'},
      'ERC':{cL:35,cM:52,cH:75,prev:0.14, diff:'high'},
      'HEP':{cL:38,cM:58,cH:82,prev:0.06, diff:'high'},
      'DOL':{cL:18,cM:28,cH:42,prev:0.22, diff:'low'},
      'ERG':{cL:18,cM:28,cH:42,prev:0.20, diff:'low'},
      'OST':{cL:25,cM:38,cH:55,prev:0.08, diff:'medium'},
      'NDB':{cL:35,cM:52,cH:75,prev:0.07, diff:'high'},
      'GLC':{cL:28,cM:42,cH:62,prev:0.065,diff:'medium'},
      'VIH':{cL:35,cM:55,cH:80,prev:0.015,diff:'high'},
      'INF':{cL:30,cM:48,cH:68,prev:0.025,diff:'medium'},
      'TGR':{cL:90,cM:140,cH:200,prev:0.002,diff:'very_high'},
      'CPR':{cL:95,cM:150,cH:220,prev:0.001,diff:'very_high'}
    },
    // Funnel rates for Latino populations (from LeadEngine research data)
    // ps=pre-screen pass, bk=book appt, at=attend, en=enroll/randomize, sf=screen-fail
    F: {
      'OB' :{ps:0.80,bk:0.62,at:0.75,en:0.60,sf:0.18},
      'PRE':{ps:0.65,bk:0.55,at:0.68,en:0.50,sf:0.30},
      'DT2':{ps:0.75,bk:0.60,at:0.72,en:0.52,sf:0.28},
      'SM' :{ps:0.60,bk:0.50,at:0.65,en:0.45,sf:0.35},
      'COL':{ps:0.78,bk:0.58,at:0.72,en:0.58,sf:0.22},
      'TIR':{ps:0.72,bk:0.55,at:0.68,en:0.55,sf:0.22},
      'SOP':{ps:0.75,bk:0.62,at:0.75,en:0.55,sf:0.20},
      'CVD':{ps:0.60,bk:0.50,at:0.65,en:0.42,sf:0.38},
      'HTA':{ps:0.78,bk:0.60,at:0.72,en:0.58,sf:0.22},
      'IC' :{ps:0.52,bk:0.45,at:0.58,en:0.40,sf:0.40},
      'FA' :{ps:0.55,bk:0.48,at:0.60,en:0.45,sf:0.35},
      'ASM':{ps:0.75,bk:0.58,at:0.70,en:0.58,sf:0.20},
      'APN':{ps:0.65,bk:0.52,at:0.65,en:0.52,sf:0.25},
      'ANX':{ps:0.78,bk:0.65,at:0.72,en:0.62,sf:0.15},
      'DEP':{ps:0.75,bk:0.62,at:0.68,en:0.58,sf:0.18},
      'TDAH':{ps:0.70,bk:0.58,at:0.68,en:0.55,sf:0.22},
      'ALZ':{ps:0.42,bk:0.38,at:0.52,en:0.32,sf:0.50},
      'PK' :{ps:0.48,bk:0.42,at:0.55,en:0.38,sf:0.42},
      'MIG':{ps:0.78,bk:0.60,at:0.72,en:0.58,sf:0.18},
      'DCG':{ps:0.48,bk:0.42,at:0.55,en:0.40,sf:0.42},
      'LUP':{ps:0.52,bk:0.48,at:0.60,en:0.40,sf:0.38},
      'ART':{ps:0.78,bk:0.58,at:0.70,en:0.55,sf:0.22},
      'AUR':{ps:0.58,bk:0.50,at:0.62,en:0.45,sf:0.32},
      'ONC':{ps:0.55,bk:0.52,at:0.65,en:0.38,sf:0.42},
      'CRH':{ps:0.45,bk:0.42,at:0.55,en:0.32,sf:0.48},
      'ERC':{ps:0.65,bk:0.52,at:0.65,en:0.48,sf:0.32},
      'HEP':{ps:0.62,bk:0.50,at:0.62,en:0.48,sf:0.32},
      'DOL':{ps:0.78,bk:0.62,at:0.70,en:0.58,sf:0.15},
      'ERG':{ps:0.75,bk:0.58,at:0.68,en:0.58,sf:0.18},
      'OST':{ps:0.68,bk:0.52,at:0.65,en:0.52,sf:0.25},
      'NDB':{ps:0.65,bk:0.50,at:0.62,en:0.48,sf:0.28},
      'GLC':{ps:0.70,bk:0.52,at:0.65,en:0.52,sf:0.25},
      'VIH':{ps:0.68,bk:0.55,at:0.65,en:0.50,sf:0.25},
      'INF':{ps:0.65,bk:0.52,at:0.62,en:0.50,sf:0.25},
      'TGR':{ps:0.38,bk:0.35,at:0.52,en:0.30,sf:0.52},
      'CPR':{ps:0.35,bk:0.32,at:0.50,en:0.28,sf:0.55}
    }
  };

  // ── HTML escape helper ────────────────────────────────────────────────────────

  function _esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }

  // ── Analysis engine ──────────────────────────────────────────────────────────

  function _leAnalyze(params) {
    var conditionKey   = params.conditionKey;
    var locations      = params.locations;
    var budget         = params.budget;
    var targetEnrolled = params.targetEnrolled;
    var timeWeeks      = params.timeWeeks;

    var cond = _LE.D[conditionKey];
    var fn   = _LE.F[conditionKey];
    if (!cond || !fn) throw new Error('Unknown condition: ' + conditionKey);

    var totalPop = 0, totalReach = 0, sumHm = 0, sumCm = 0;
    var locs = [];
    locations.forEach(function(loc) {
      var st = _EL[loc];
      if (!st) return;
      totalPop   += st.pop;
      totalReach += st.reach;
      sumHm      += st.hm;
      sumCm      += st.cm;
      locs.push(Object.assign({ code: loc }, st));
    });
    if (!locs.length) throw new Error('No valid locations.');

    var avgHm = sumHm / locs.length;
    var avgCm = sumCm / locs.length;

    var adjPrev  = Math.min(cond.prev * (avgHm / 1.15), 0.95);
    var pool     = Math.round(totalReach * adjPrev);
    var nc       = fn.ps * fn.bk * fn.at * fn.en;
    var lpe      = Math.ceil(1 / nc);
    var totalLeads = lpe * targetEnrolled;

    var cplO = Math.round(cond.cL * avgCm);
    var cplM = Math.round(cond.cM * avgCm);
    var cplC = Math.round(cond.cH * avgCm);

    var costO = totalLeads * cplO, costM = totalLeads * cplM, costC = totalLeads * cplC;
    var leadsO = Math.floor(budget / cplO), leadsM = Math.floor(budget / cplM), leadsC = Math.floor(budget / cplC);
    var enrO = Math.floor(leadsO * nc), enrM = Math.floor(leadsM * nc), enrC = Math.floor(leadsC * nc);
    var lpw = Math.ceil(totalLeads / timeWeeks);

    var budgetOk = budget >= costM;
    var poolOk   = pool >= totalLeads * 3;
    var timeOk   = timeWeeks >= Math.ceil(totalLeads / Math.max(lpw, 1));

    var checks = [
      {
        name: 'Latino Pool', passed: poolOk,
        detail: poolOk
          ? 'Pool of ' + pool.toLocaleString() + ' reachable Latinos is sufficient for ' + totalLeads.toLocaleString() + ' leads.'
          : 'Pool of ' + pool.toLocaleString() + ' insufficient. Need ~' + (totalLeads * 3).toLocaleString() + ' to avoid saturation.',
        metric: pool.toLocaleString() + ' reachable',
      },
      {
        name: 'Budget', passed: budgetOk,
        detail: budgetOk
          ? '$' + budget.toLocaleString() + ' covers realistic cost of $' + costM.toLocaleString() + '. CPL: $' + cplM + '/lead.'
          : '$' + budget.toLocaleString() + ' insufficient. Realistic requires $' + costM.toLocaleString() + '. Deficit: $' + (costM - budget).toLocaleString() + '.',
        metric: '$' + cplM + ' realistic CPL',
      },
      {
        name: 'Funnel & Timeline', passed: timeOk && nc >= 0.05,
        detail: (timeOk && nc >= 0.05)
          ? 'Conversion ' + (nc * 100).toFixed(1) + '% with ' + timeWeeks + ' weeks viable. ~' + lpw + ' leads/week.'
          : 'Conversion ' + (nc * 100).toFixed(1) + '% with screen-fail ' + (fn.sf * 100).toFixed(0) + '% requires more time or volume.',
        metric: (nc * 100).toFixed(1) + '% net conversion',
      },
    ];

    var passedCount = checks.filter(function(c) { return c.passed; }).length;
    var verdict, score;
    if (passedCount === 3) { verdict = 'APROBADO'; score = Math.min(100, 85 + Math.round(nc * 100)); }
    else if (passedCount >= 1) { verdict = 'VIABLE CON AJUSTES'; score = 40 + passedCount * 15; }
    else { verdict = 'NO VIABLE'; score = Math.max(10, Math.round(nc * 200)); }

    // >30% overbudget → force NOT VIABLE
    if (costM > budget * 1.30) {
      verdict = 'NO VIABLE';
      score = Math.min(score, 30);
    }

    var hints = [];
    if (!budgetOk) {
      hints.push({ title:'Increase budget', desc:'You need ~$' + costM.toLocaleString() + ' for ' + targetEnrolled + ' enrolled. Current budget can reach ~' + enrM + ' patients.', priority:'alta' });
      hints.push({ title:'Reduce patient target', desc:'With $' + budget.toLocaleString() + ' you can enroll ~' + enrM + ' patients. Adjust target from ' + targetEnrolled + ' to ' + enrM + '.', priority:'media' });
    }
    if (!poolOk) {
      var suggested = Object.keys(_EL).filter(function(k) { return locations.indexOf(k) === -1; })
        .sort(function(a, b) { return _EL[b].pop - _EL[a].pop; }).slice(0, 3)
        .map(function(k) { return _EL[k].n + ' (' + (_EL[k].pop / 1e6).toFixed(1) + 'M)'; }).join(', ');
      hints.push({ title:'Add locations', desc:'Include: ' + suggested, priority:'alta' });
    }
    if (fn.sf > 0.30) hints.push({ title:'Digital pre-screening', desc:'Screen-fail ' + (fn.sf * 100).toFixed(0) + '% — implement pre-screen to filter before in-person visit.', priority:'alta' });
    if (!timeOk) hints.push({ title:'Extend timeline', desc:'You need ~' + (Math.ceil(totalLeads / lpw) + 2) + ' weeks vs current ' + timeWeeks + '.', priority:'media' });
    hints.push({ title:'Familismo in creatives', desc:'75% of Latinos agree when invited directly (Cross-Cancino 2021). Include family testimonials and "No cost to you".', priority:'baja' });
    hints.push({ title:'Bilingual materials', desc:'Only 48% of Latinos know what a clinical trial is (Wallington 2012). Creatives must educate + recruit.', priority:'baja' });

    var condName = _EC_NAMES[conditionKey] || conditionKey;
    return {
      condition:  { key: conditionKey, n: condName, prev: cond.prev },
      locations:  locs,
      population: { total: totalPop, reach: totalReach, adjustedPrev: adjPrev, pool: pool },
      funnel:     Object.assign({}, fn, { netConversion: nc, leadsPerEnrolled: lpe }),
      totalLeads: totalLeads, leadsPerWeek: lpw,
      scenarios: {
        optimista:   { cpl: cplO, cost: costO, leads: leadsO, enrolled: enrO, costPerEnrolled: cplO * lpe },
        realista:    { cpl: cplM, cost: costM, leads: leadsM, enrolled: enrM, costPerEnrolled: cplM * lpe },
        conservador: { cpl: cplC, cost: costC, leads: leadsC, enrolled: enrC, costPerEnrolled: cplC * lpe },
      },
      checks: checks, passedCount: passedCount, verdict: verdict, score: score, hints: hints,
      funnelStages: [
        { label:'Leads generated',       count: totalLeads,                                           pct: 100 },
        { label:'Pass pre-screening',   count: Math.round(totalLeads * fn.ps),                       pct: Math.round(fn.ps * 100) },
        { label:'Schedule visit',        count: Math.round(totalLeads * fn.ps * fn.bk),               pct: Math.round(fn.ps * fn.bk * 100) },
        { label:'Attend visit',          count: Math.round(totalLeads * fn.ps * fn.bk * fn.at),       pct: Math.round(fn.ps * fn.bk * fn.at * 100) },
        { label:'Enrolled',             count: targetEnrolled,                                        pct: Math.round(nc * 100) },
      ],
      form: { budget: budget, targetEnrolled: targetEnrolled, timeWeeks: timeWeeks },
    };
  }

  // ── Phase switching ──────────────────────────────────────────────────────────

  function lePhase(name) {
    ['form', 'verifying', 'results'].forEach(function(p) {
      var el = document.getElementById('le-phase-' + p);
      if (el) el.style.display = (p === name) ? '' : 'none';
    });
  }

  // ── Location chip toggle ─────────────────────────────────────────────────────

  function leToggleLoc(btn) {
    var activeCount = document.querySelectorAll('#le-loc-chips .le-chip--active').length;
    if (btn.classList.contains('le-chip--active') && activeCount <= 1) return;
    btn.classList.toggle('le-chip--active');
  }

  // ── Check row animation ──────────────────────────────────────────────────────

  function _leSetCheck(i, status, detail, metric) {
    var row = document.getElementById('le-chk-' + i);
    if (!row) return;
    row.className = 'le-check-row le-check-row--' + status;
    var badge = row.querySelector('.le-check-badge');
    if (badge) {
      badge.className = 'le-check-badge le-check-badge--' + status;
      badge.innerHTML = status === 'pending' ? '—'
        : status === 'running' ? '<span class="le-spinner"></span>'
        : status === 'pass' ? '✓ PASS' : '✗ FAIL';
    }
    var detEl = row.querySelector('.le-check-detail');
    var metEl = row.querySelector('.le-check-metric');
    var show  = (status === 'pass' || status === 'fail');
    if (detEl) { detEl.textContent = detail || ''; detEl.style.display = show ? '' : 'none'; }
    if (metEl) { metEl.textContent = metric || ''; metEl.style.display = show ? '' : 'none'; }
  }

  function _leAnimateChecks(result, onDone) {
    for (var k = 0; k < 3; k++) _leSetCheck(k, 'pending', '', '');
    function runCheck(i) {
      if (i >= 3) { setTimeout(onDone, 500); return; }
      setTimeout(function() {
        _leSetCheck(i, 'running', '', '');
        setTimeout(function() {
          var c = result.checks[i];
          _leSetCheck(i, c.passed ? 'pass' : 'fail', c.detail, c.metric);
          runCheck(i + 1);
        }, 1300);
      }, 250);
    }
    runCheck(0);
  }

  // ── Render results ───────────────────────────────────────────────────────────

  function _leSetTxt(id, text) { var el = document.getElementById(id); if (el) el.textContent = text; }

  function _leRenderResults(r) {
    var approved = r.verdict === 'APROBADO';
    var partial  = r.verdict === 'VIABLE CON AJUSTES';
    var vEl = document.getElementById('le-verdict');
    if (vEl) vEl.className = 'le-verdict le-verdict--' + (approved ? 'aprobado' : partial ? 'viable' : 'noviable');
    _leSetTxt('le-verdict-emoji',  approved ? '✅' : partial ? '⚠️' : '❌');
    var _VD = { 'APROBADO':'APPROVED', 'VIABLE CON AJUSTES':'VIABLE WITH ADJUSTMENTS', 'NO VIABLE':'NOT VIABLE' };
    _leSetTxt('le-verdict-text', _VD[r.verdict] || r.verdict);
    _leSetTxt('le-verdict-score',  r.score);

    _leSetTxt('le-m-pool',         r.population.pool.toLocaleString());
    _leSetTxt('le-m-pool-sub',     (r.population.adjustedPrev * 100).toFixed(1) + '% adjusted prevalence');
    _leSetTxt('le-m-leads',        r.totalLeads.toLocaleString());
    _leSetTxt('le-m-leads-sub',    r.funnel.leadsPerEnrolled + ' leads per enrolled');
    _leSetTxt('le-m-cost',         '$' + r.scenarios.realista.cost.toLocaleString());
    _leSetTxt('le-m-cost-sub',     '$' + r.scenarios.realista.cpl + ' realistic CPL');
    _leSetTxt('le-m-enrolled',     r.scenarios.realista.enrolled.toLocaleString());
    _leSetTxt('le-m-enrolled-sub', 'of ' + r.form.targetEnrolled + ' target');
    _leSetTxt('le-sf-note',        'screen-fail: ' + (r.funnel.sf * 100).toFixed(0) + '%');

    var funnelEl = document.getElementById('le-funnel');
    if (funnelEl) funnelEl.innerHTML = r.funnelStages.map(function(s, i) {
      var final = (i === r.funnelStages.length - 1);
      return '<div class="le-funnel-row">'
        + '<div class="le-funnel-meta"><span class="le-funnel-lbl">' + s.label + '</span><span class="le-funnel-cnt">' + s.count.toLocaleString() + '</span></div>'
        + '<div class="le-funnel-track"><div class="le-funnel-bar' + (final ? ' le-funnel-bar--final' : '') + '" style="width:' + s.pct + '%"></div></div>'
        + '<span class="le-funnel-pct">' + s.pct + '%</span></div>';
    }).join('');

    var scenEl = document.getElementById('le-scenarios');
    if (scenEl) {
      var SK = ['optimista','realista','conservador'], SL = ['Optimistic','Realistic','Conservative'];
      scenEl.innerHTML = SK.map(function(key, i) {
        var s = r.scenarios[key], hi = (key === 'realista');
        return '<div class="le-scene-card' + (hi ? ' le-scene-card--hi' : '') + '">'
          + (hi ? '<div class="le-scene-ribbon">Realistic</div>' : '')
          + '<div class="le-scene-lbl">' + SL[i] + '</div>'
          + '<div class="le-scen-row"><span>CPL</span><strong>$' + s.cpl + '</strong></div>'
          + '<div class="le-scen-row"><span>Leads</span><strong>' + s.leads.toLocaleString() + '</strong></div>'
          + '<div class="le-scen-row"><span>Enrolled</span><strong>' + s.enrolled.toLocaleString() + '</strong></div>'
          + '<div class="le-scen-row"><span>Total</span><strong>$' + s.cost.toLocaleString() + '</strong></div>'
          + '<div class="le-scen-row le-scen-cpe"><span>$/enrolled</span><strong>$' + s.costPerEnrolled.toLocaleString() + '</strong></div>'
          + '</div>';
      }).join('');
    }

    var rcEl = document.getElementById('le-res-checks');
    if (rcEl) {
      var ICONS = ['👥','💰','🔬'];
      rcEl.innerHTML = r.checks.map(function(c, i) {
        return '<div class="le-check-row le-check-row--' + (c.passed ? 'pass' : 'fail') + '">'
          + '<div class="le-check-icon">' + ICONS[i] + '</div>'
          + '<div class="le-check-body">'
          + '<div class="le-check-hd"><span class="le-check-name">' + _esc(c.name) + '</span>'
          + '<span class="le-check-badge le-check-badge--' + (c.passed ? 'pass' : 'fail') + '">' + (c.passed ? '✓ PASS' : '✗ FAIL') + '</span></div>'
          + '<p class="le-check-detail">' + _esc(c.detail) + '</p>'
          + '<span class="le-check-metric">' + _esc(c.metric) + '</span>'
          + '</div></div>';
      }).join('');
    }

    var approved2 = r.verdict === 'APROBADO';
    _leSetTxt('le-hints-icon',  approved2 ? '🚀' : '🛠');
    _leSetTxt('le-hints-title', approved2 ? 'Optimization Tips' : 'How to Improve for Approval');
    var hintsEl = document.getElementById('le-hints');
    if (hintsEl) {
      var PICO = { alta:'🚨', media:'⚠️', baja:'💡' };
      hintsEl.innerHTML = r.hints.map(function(h) {
        return '<div class="le-hint le-hint--' + h.priority + '">'
          + '<div class="le-hint-hd"><span>' + PICO[h.priority] + '</span>'
          + '<span class="le-hint-title">' + _esc(h.title) + '</span>'
          + '<span class="le-hint-badge le-hint-badge--' + h.priority + '">' + h.priority.toUpperCase() + '</span></div>'
          + '<p class="le-hint-desc">' + _esc(h.desc) + '</p></div>';
      }).join('');
    }
  }

  // ── Form submit / reset ──────────────────────────────────────────────────────

  function leSubmit(e) {
    if (e) e.preventDefault();

    var condKey = (document.getElementById('le-condition') || {}).value || 'OB';
    var budget  = parseFloat((document.getElementById('le-budget') || {}).value) || 0;
    var target  = parseInt((document.getElementById('le-target')  || {}).value) || 0;
    var weeks   = parseInt((document.getElementById('le-weeks')   || {}).value) || 0;

    var locs = [];
    document.querySelectorAll('#le-loc-chips .le-chip--active').forEach(function(c) { locs.push(c.dataset.loc); });
    if (!locs.length) locs = ['PR'];

    var errEl = document.getElementById('le-form-error');
    var errs  = [];
    if (!budget || budget < 500)  errs.push('Minimum budget: $500.');
    if (!target || target < 1)   errs.push('Enter a valid patient target.');
    if (!weeks  || weeks < 1)    errs.push('Enter a valid duration (weeks).');
    if (errEl) errEl.textContent = errs.join(' · ');
    if (errs.length) return;

    var result;
    try {
      result = _leAnalyze({ conditionKey: condKey, locations: locs, budget: budget, targetEnrolled: target, timeWeeks: weeks });
    } catch (err) {
      if (errEl) errEl.textContent = 'Analysis error: ' + err.message;
      return;
    }

    // Switch to verifying phase
    lePhase('verifying');
    var vSub = document.getElementById('le-verify-sub');
    if (vSub) vSub.textContent = 'Reviewing ' + result.condition.n + ' in ' + result.locations.map(function(l) { return l.n; }).join(', ');

    // Animate checks, then transition to results
    _leAnimateChecks(result, function() {
      lePhase('results');
      _leRenderResults(result);

      // Reset AI section
      var aiLoad = document.getElementById('le-ai-loading');
      var aiText = document.getElementById('le-ai-text');
      if (aiLoad) aiLoad.style.display = 'flex';
      if (aiText) { aiText.style.display = 'none'; aiText.textContent = ''; }

      // Fetch AI interpretation (calls _leCallAI if available in host page scope)
      if (typeof _leCallAI === 'function') _leCallAI(result);
    });
  }

  function leReset() {
    lePhase('form');
  }

  // ── Expose public API on window (called via onclick in HTML) ─────────────────

  window.leSubmit    = leSubmit;
  window.leReset     = leReset;
  window.lePhase     = lePhase;
  window.leToggleLoc = leToggleLoc;

  // Also expose data objects so other scripts (e.g. dco-frontend.js) can read them
  window._LE      = _LE;
  window._EL      = _EL;
  window._EC_NAMES = _EC_NAMES;
  window._leAnalyze = _leAnalyze;

})();
