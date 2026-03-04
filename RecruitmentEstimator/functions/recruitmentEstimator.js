/**
 * Firebase Cloud Function — recruitmentEstimator
 *
 * POST https://us-central1-{project-id}.cloudfunctions.net/recruitmentEstimator
 * Body: { conditionKey, locations, budget, targetEnrolled, timeWeeks }
 * Returns: full analysis result + aiInterpretation (Spanish)
 *
 * ── Setup ─────────────────────────────────────────────────────────────────
 * 1. Set the Anthropic API key:
 *      firebase functions:secrets:set ANTHROPIC_API_KEY
 *    OR (legacy config):
 *      firebase functions:config:set anthropic.key="sk-ant-..."
 *
 * 2. Add to functions/index.js:
 *      const { recruitmentEstimator } = require('./recruitmentEstimator');
 *      exports.recruitmentEstimator = recruitmentEstimator;
 *
 * 3. Deploy:
 *      firebase deploy --only functions:recruitmentEstimator
 * ──────────────────────────────────────────────────────────────────────────
 */

"use strict";

const functions  = require("firebase-functions");
const Anthropic  = require("@anthropic-ai/sdk");
const corsLib    = require("cors");

const cors = corsLib({ origin: true });

// ─────────────────────────────────────────────────────────────────────────────
// DATA  (HCHS/SOL · CDC/OMH 2025 · Census 2023 · Cross-Cancino 2021)
// ─────────────────────────────────────────────────────────────────────────────

const CONDITIONS = {
  OB:  { n:"Obesidad/Sobrepeso",       cat:"Metabólica",     prev:0.366, cL:18,  cM:28,  cH:42  },
  PRE: { n:"Prediabetes",              cat:"Metabólica",     prev:0.38,  cL:22,  cM:35,  cH:50  },
  DT2: { n:"Diabetes Tipo 2",          cat:"Metabólica",     prev:0.18,  cL:25,  cM:38,  cH:55  },
  SM:  { n:"Síndrome Metabólico",      cat:"Metabólica",     prev:0.35,  cL:30,  cM:48,  cH:70  },
  COL: { n:"Colesterol Alto",          cat:"Metabólica",     prev:0.38,  cL:22,  cM:32,  cH:48  },
  TIR: { n:"Enfermedad Tiroidea",      cat:"Metabólica",     prev:0.08,  cL:28,  cM:42,  cH:62  },
  SOP: { n:"SOP",                      cat:"Metabólica",     prev:0.10,  cL:25,  cM:38,  cH:55  },
  CVD: { n:"Enf. Cardiovascular",      cat:"Cardiovascular", prev:0.065, cL:35,  cM:55,  cH:80  },
  HTA: { n:"Hipertensión",             cat:"Cardiovascular", prev:0.30,  cL:20,  cM:32,  cH:48  },
  IC:  { n:"Insuf. Cardíaca",          cat:"Cardiovascular", prev:0.018, cL:45,  cM:68,  cH:95  },
  FA:  { n:"Fibrilación Auricular",    cat:"Cardiovascular", prev:0.02,  cL:42,  cM:65,  cH:90  },
  ASM: { n:"Asma/EPOC",               cat:"Respiratoria",   prev:0.165, cL:22,  cM:35,  cH:52  },
  APN: { n:"Apnea del Sueño",          cat:"Respiratoria",   prev:0.07,  cL:25,  cM:40,  cH:58  },
  ANX: { n:"Ansiedad",                 cat:"Salud Mental",   prev:0.22,  cL:15,  cM:25,  cH:38  },
  DEP: { n:"Depresión",                cat:"Salud Mental",   prev:0.18,  cL:15,  cM:25,  cH:38  },
  TDAH:{ n:"TDAH",                     cat:"Salud Mental",   prev:0.05,  cL:20,  cM:32,  cH:48  },
  ALZ: { n:"Alzheimer/Demencia",       cat:"Neurológica",    prev:0.013, cL:55,  cM:85,  cH:120 },
  PK:  { n:"Parkinson",                cat:"Neurológica",    prev:0.006, cL:60,  cM:90,  cH:130 },
  MIG: { n:"Migraña",                  cat:"Neurológica",    prev:0.16,  cL:18,  cM:28,  cH:42  },
  DCG: { n:"Deterioro Cognitivo",      cat:"Neurológica",    prev:0.04,  cL:40,  cM:60,  cH:85  },
  LUP: { n:"Lupus/LES",               cat:"Autoinmune",     prev:0.007, cL:65,  cM:95,  cH:140 },
  ART: { n:"Artritis",                 cat:"Autoinmune",     prev:0.20,  cL:20,  cM:32,  cH:48  },
  AUR: { n:"Autoinmune (Otro)",        cat:"Autoinmune",     prev:0.05,  cL:35,  cM:55,  cH:78  },
  ONC: { n:"Oncología/Cáncer",         cat:"Oncología",      prev:0.03,  cL:45,  cM:70,  cH:100 },
  CRH: { n:"Cáncer Raro",             cat:"Oncología",      prev:0.004, cL:80,  cM:120, cH:175 },
  ERC: { n:"Enf. Renal Crónica",      cat:"Crónicas",       prev:0.14,  cL:35,  cM:52,  cH:75  },
  HEP: { n:"Enf. Hepática",           cat:"Crónicas",       prev:0.06,  cL:38,  cM:58,  cH:82  },
  DOL: { n:"Dolor Crónico",            cat:"Crónicas",       prev:0.22,  cL:18,  cM:28,  cH:42  },
  ERG: { n:"ERGE/Reflujo",             cat:"Crónicas",       prev:0.20,  cL:18,  cM:28,  cH:42  },
  OST: { n:"Osteoporosis",             cat:"Crónicas",       prev:0.08,  cL:25,  cM:38,  cH:55  },
  NDB: { n:"Neuropatía Diabética",     cat:"Crónicas",       prev:0.07,  cL:35,  cM:52,  cH:75  },
  GLC: { n:"Glaucoma/Cataratas",       cat:"Crónicas",       prev:0.065, cL:28,  cM:42,  cH:62  },
  VIH: { n:"VIH/SIDA",                cat:"Infecciosas",    prev:0.015, cL:35,  cM:55,  cH:80  },
  INF: { n:"Enf. Infecciosa",          cat:"Infecciosas",    prev:0.025, cL:30,  cM:48,  cH:68  },
  TGR: { n:"Trast. Genéticos Raros",  cat:"Raras",          prev:0.002, cL:90,  cM:140, cH:200 },
  CPR: { n:"Cond. Pediátricas Raras", cat:"Raras",          prev:0.001, cL:95,  cM:150, cH:220 },
};

const FUNNEL = {
  OB:  { ps:0.80, bk:0.62, at:0.75, en:0.60, sf:0.18 },
  PRE: { ps:0.65, bk:0.55, at:0.68, en:0.50, sf:0.30 },
  DT2: { ps:0.75, bk:0.60, at:0.72, en:0.52, sf:0.28 },
  SM:  { ps:0.60, bk:0.50, at:0.65, en:0.45, sf:0.35 },
  COL: { ps:0.78, bk:0.58, at:0.72, en:0.58, sf:0.22 },
  TIR: { ps:0.72, bk:0.55, at:0.68, en:0.55, sf:0.22 },
  SOP: { ps:0.75, bk:0.62, at:0.75, en:0.55, sf:0.20 },
  CVD: { ps:0.60, bk:0.50, at:0.65, en:0.42, sf:0.38 },
  HTA: { ps:0.78, bk:0.60, at:0.72, en:0.58, sf:0.22 },
  IC:  { ps:0.52, bk:0.45, at:0.58, en:0.40, sf:0.40 },
  FA:  { ps:0.55, bk:0.48, at:0.60, en:0.45, sf:0.35 },
  ASM: { ps:0.75, bk:0.58, at:0.70, en:0.58, sf:0.20 },
  APN: { ps:0.65, bk:0.52, at:0.65, en:0.52, sf:0.25 },
  ANX: { ps:0.78, bk:0.65, at:0.72, en:0.62, sf:0.15 },
  DEP: { ps:0.75, bk:0.62, at:0.68, en:0.58, sf:0.18 },
  TDAH:{ ps:0.70, bk:0.58, at:0.68, en:0.55, sf:0.22 },
  ALZ: { ps:0.42, bk:0.38, at:0.52, en:0.32, sf:0.50 },
  PK:  { ps:0.48, bk:0.42, at:0.55, en:0.38, sf:0.42 },
  MIG: { ps:0.78, bk:0.60, at:0.72, en:0.58, sf:0.18 },
  DCG: { ps:0.48, bk:0.42, at:0.55, en:0.40, sf:0.42 },
  LUP: { ps:0.52, bk:0.48, at:0.60, en:0.40, sf:0.38 },
  ART: { ps:0.78, bk:0.58, at:0.70, en:0.55, sf:0.22 },
  AUR: { ps:0.58, bk:0.50, at:0.62, en:0.45, sf:0.32 },
  ONC: { ps:0.55, bk:0.52, at:0.65, en:0.38, sf:0.42 },
  CRH: { ps:0.45, bk:0.42, at:0.55, en:0.32, sf:0.48 },
  ERC: { ps:0.65, bk:0.52, at:0.65, en:0.48, sf:0.32 },
  HEP: { ps:0.62, bk:0.50, at:0.62, en:0.48, sf:0.32 },
  DOL: { ps:0.78, bk:0.62, at:0.70, en:0.58, sf:0.15 },
  ERG: { ps:0.75, bk:0.58, at:0.68, en:0.58, sf:0.18 },
  OST: { ps:0.68, bk:0.52, at:0.65, en:0.52, sf:0.25 },
  NDB: { ps:0.65, bk:0.50, at:0.62, en:0.48, sf:0.28 },
  GLC: { ps:0.70, bk:0.52, at:0.65, en:0.52, sf:0.25 },
  VIH: { ps:0.68, bk:0.55, at:0.65, en:0.50, sf:0.25 },
  INF: { ps:0.65, bk:0.52, at:0.62, en:0.50, sf:0.25 },
  TGR: { ps:0.38, bk:0.35, at:0.52, en:0.30, sf:0.52 },
  CPR: { ps:0.35, bk:0.32, at:0.50, en:0.28, sf:0.55 },
};

const LOCATIONS = {
  PR: { n:"Puerto Rico",  pop:2430000,  reach:1822500, hm:1.15, cm:1.00 },
  CA: { n:"California",  pop:11820000, reach:8865000,  hm:1.05, cm:1.40 },
  TX: { n:"Texas",       pop:9100000,  reach:6825000,  hm:1.10, cm:1.25 },
  FL: { n:"Florida",     pop:4650000,  reach:3487500,  hm:1.04, cm:1.25 },
  NY: { n:"New York",    pop:2850000,  reach:2137500,  hm:1.00, cm:1.40 },
  AZ: { n:"Arizona",     pop:1750000,  reach:1312500,  hm:1.12, cm:1.25 },
  IL: { n:"Illinois",    pop:1650000,  reach:1237500,  hm:1.06, cm:1.25 },
  NJ: { n:"New Jersey",  pop:1500000,  reach:1125000,  hm:1.02, cm:1.40 },
  CO: { n:"Colorado",    pop:935000,   reach:701250,   hm:0.95, cm:1.25 },
  NM: { n:"New Mexico",  pop:760000,   reach:570000,   hm:1.12, cm:1.10 },
  NV: { n:"Nevada",      pop:650000,   reach:487500,   hm:1.10, cm:1.25 },
};

// ─────────────────────────────────────────────────────────────────────────────
// ANALYSIS ENGINE
// ─────────────────────────────────────────────────────────────────────────────

function analyzeRecruitment({ conditionKey, locations, budget, targetEnrolled, timeWeeks }) {
  const cond = CONDITIONS[conditionKey];
  const fn   = FUNNEL[conditionKey];
  if (!cond || !fn) throw new Error(`Unknown condition key: ${conditionKey}`);

  // Aggregate location data
  let totalPop = 0, totalReach = 0, sumHm = 0, sumCm = 0;
  const locs = [];
  for (const loc of locations) {
    const st = LOCATIONS[loc];
    if (!st) continue;
    totalPop   += st.pop;
    totalReach += st.reach;
    sumHm      += st.hm;
    sumCm      += st.cm;
    locs.push({ code: loc, ...st });
  }
  if (!locs.length) throw new Error("No valid locations provided.");

  const avgHm = sumHm / locs.length;
  const avgCm = sumCm / locs.length;

  // Adjusted prevalence and reachable pool
  const adjPrev = Math.min(cond.prev * (avgHm / 1.15), 0.95);
  const pool    = Math.round(totalReach * adjPrev);

  // Funnel math
  const nc   = fn.ps * fn.bk * fn.at * fn.en;  // net conversion rate
  const lpe  = Math.ceil(1 / nc);               // leads per enrolled patient
  const totalLeads = lpe * targetEnrolled;

  // CPL scenarios adjusted by location cost multiplier
  const cplO = Math.round(cond.cL * avgCm);
  const cplM = Math.round(cond.cM * avgCm);
  const cplC = Math.round(cond.cH * avgCm);

  // Cost scenarios for target
  const costO = totalLeads * cplO;
  const costM = totalLeads * cplM;
  const costC = totalLeads * cplC;

  // What budget actually achieves
  const leadsO = Math.floor(budget / cplO);
  const leadsM = Math.floor(budget / cplM);
  const leadsC = Math.floor(budget / cplC);
  const enrO   = Math.floor(leadsO * nc);
  const enrM   = Math.floor(leadsM * nc);
  const enrC   = Math.floor(leadsC * nc);

  // Timeline
  const lpw = Math.ceil(totalLeads / timeWeeks);

  // ── Triple verification ──────────────────────────────────────────────────
  const budgetOk = budget >= costM;
  const poolOk   = pool >= totalLeads * 3;
  const timeOk   = timeWeeks >= Math.ceil(totalLeads / Math.max(lpw, 1));

  const checks = [
    {
      name:   "Pool Latino",
      passed: poolOk,
      detail: poolOk
        ? `Pool de ${pool.toLocaleString()} Latinos alcanzables es suficiente para ${totalLeads.toLocaleString()} leads.`
        : `Pool de ${pool.toLocaleString()} insuficiente. Se necesitan ~${(totalLeads * 3).toLocaleString()} para evitar saturación.`,
      metric: `${pool.toLocaleString()} alcanzables`,
    },
    {
      name:   "Presupuesto",
      passed: budgetOk,
      detail: budgetOk
        ? `$${budget.toLocaleString()} cubre costo realista de $${costM.toLocaleString()}. CPL: $${cplM}/lead.`
        : `$${budget.toLocaleString()} insuficiente. Realista requiere $${costM.toLocaleString()}. Déficit: $${(costM - budget).toLocaleString()}.`,
      metric: `$${cplM} CPL realista`,
    },
    {
      name:   "Funnel & Timeline",
      passed: timeOk && nc >= 0.05,
      detail: (timeOk && nc >= 0.05)
        ? `Conversión ${(nc * 100).toFixed(1)}% con ${timeWeeks} semanas viable. ~${lpw} leads/semana.`
        : `Conversión ${(nc * 100).toFixed(1)}% con screen-fail ${(fn.sf * 100).toFixed(0)}% requiere más tiempo o volumen.`,
      metric: `${(nc * 100).toFixed(1)}% conversión neta`,
    },
  ];

  // ── Verdict ──────────────────────────────────────────────────────────────
  const passedCount = checks.filter(c => c.passed).length;
  let verdict, score;
  if (passedCount === 3) {
    verdict = "APROBADO";
    score   = Math.min(100, 85 + Math.round(nc * 100));
  } else if (passedCount >= 1) {
    verdict = "VIABLE CON AJUSTES";
    score   = 40 + passedCount * 15;
  } else {
    verdict = "NO VIABLE";
    score   = Math.max(10, Math.round(nc * 200));
  }

  // ── Hints ────────────────────────────────────────────────────────────────
  const hints = [];
  if (!budgetOk) {
    hints.push({
      title: "Aumentar presupuesto",
      desc:  `Necesitas ~$${costM.toLocaleString()} para ${targetEnrolled} enrolled. Budget actual alcanza ~${enrM} pacientes.`,
      priority: "alta",
    });
    hints.push({
      title: "Reducir meta de pacientes",
      desc:  `Con $${budget.toLocaleString()} puedes enrollar ~${enrM} pacientes. Ajusta target de ${targetEnrolled} a ${enrM}.`,
      priority: "media",
    });
  }
  if (!poolOk) {
    const suggested = Object.entries(LOCATIONS)
      .filter(([k]) => !locations.includes(k))
      .sort((a, b) => b[1].pop - a[1].pop)
      .slice(0, 3);
    hints.push({
      title: "Agregar ubicaciones",
      desc:  `Incluye: ${suggested.map(([, v]) => `${v.n} (${(v.pop / 1e6).toFixed(1)}M)`).join(", ")}`,
      priority: "alta",
    });
  }
  if (fn.sf > 0.30) {
    hints.push({
      title: "Pre-screening digital",
      desc:  `Screen-fail ${(fn.sf * 100).toFixed(0)}% — implementar pre-screen para filtrar antes de cita en sitio.`,
      priority: "alta",
    });
  }
  if (!timeOk) {
    hints.push({
      title: "Extender timeline",
      desc:  `Necesitas ~${Math.ceil(totalLeads / lpw) + 2} semanas vs ${timeWeeks} actuales.`,
      priority: "media",
    });
  }
  hints.push({
    title: "Familismo en creativos",
    desc:  "75% Latinos aceptan cuando invitados directamente (Cross-Cancino 2021). Incluir testimonios familiares y 'Sin costo para ti'.",
    priority: "baja",
  });
  hints.push({
    title: "Materiales bilingües",
    desc:  "Solo 48% Latinos saben qué es un ensayo clínico (Wallington 2012). Creativos deben educar + reclutar.",
    priority: "baja",
  });

  return {
    condition: cond,
    conditionKey,
    locations: locs,
    population: { total: totalPop, reach: totalReach, adjustedPrev: adjPrev, pool },
    funnel: { ...fn, netConversion: nc, leadsPerEnrolled: lpe },
    totalLeads,
    scenarios: {
      optimista:   { cpl: cplO, cost: costO, leads: leadsO, enrolled: enrO, costPerEnrolled: cplO * lpe },
      realista:    { cpl: cplM, cost: costM, leads: leadsM, enrolled: enrM, costPerEnrolled: cplM * lpe },
      conservador: { cpl: cplC, cost: costC, leads: leadsC, enrolled: enrC, costPerEnrolled: cplC * lpe },
    },
    leadsPerWeek: lpw,
    checks,
    passedCount,
    verdict,
    score,
    hints,
    funnelStages: [
      { label: "Leads generados",      count: totalLeads,                                            pct: 100 },
      { label: "Pasan pre-screening",  count: Math.round(totalLeads * fn.ps),                        pct: Math.round(fn.ps * 100) },
      { label: "Agendan cita",         count: Math.round(totalLeads * fn.ps * fn.bk),                pct: Math.round(fn.ps * fn.bk * 100) },
      { label: "Asisten a cita",       count: Math.round(totalLeads * fn.ps * fn.bk * fn.at),        pct: Math.round(fn.ps * fn.bk * fn.at * 100) },
      { label: "Enrolled",             count: targetEnrolled,                                         pct: Math.round(nc * 100) },
    ],
    dataSources: "HCHS/SOL · CDC/OMH 2025 · NHANES Hispanic · Census 2023 · Cross-Cancino 2021 · Tufts CSDD 2019",
  };
}

// ─────────────────────────────────────────────────────────────────────────────
// CLAUDE INTERPRETATION
// ─────────────────────────────────────────────────────────────────────────────

function buildPrompt(result) {
  const hiPriHints = result.hints
    .filter(h => h.priority === "alta")
    .map(h => `- ${h.title}: ${h.desc}`)
    .join("\n");

  return `Analiza este resultado de viabilidad de reclutamiento Latino y da tu interpretación profesional en 4-6 oraciones. Sé directo, práctico, y menciona implicaciones culturales.

DATOS:
- Condición: ${result.condition.n} (prevalencia Latino: ${(result.condition.prev * 100).toFixed(1)}%)
- Veredicto: ${result.verdict} (score: ${result.score})
- Ubicaciones: ${result.locations.map(l => l.n).join(", ")}
- Pool targeteable: ${result.population.pool.toLocaleString()} Latinos
- Conversión neta: ${(result.funnel.netConversion * 100).toFixed(1)}%
- Screen-fail: ${(result.funnel.sf * 100).toFixed(0)}%
- Budget: $${result.form.budget.toLocaleString()} | Costo realista: $${result.scenarios.realista.cost.toLocaleString()}
- Enrolled estimados: ${result.scenarios.realista.enrolled} de ${result.form.targetEnrolled} target
- Checks: Pool ${result.checks[0].passed ? "✓" : "✗"}, Budget ${result.checks[1].passed ? "✓" : "✗"}, Funnel ${result.checks[2].passed ? "✓" : "✗"}
${hiPriHints}

Responde SOLO en español. No uses markdown. Sé conciso y accionable.`;
}

async function getAIInterpretation(result, apiKey) {
  const anthropic = new Anthropic({ apiKey });
  const response  = await anthropic.messages.create({
    model:      "claude-sonnet-4-6",
    max_tokens: 500,
    messages:   [{ role: "user", content: buildPrompt(result) }],
  });
  return response.content[0]?.text || "Análisis no disponible.";
}

// ─────────────────────────────────────────────────────────────────────────────
// FIREBASE HTTP HANDLER
// ─────────────────────────────────────────────────────────────────────────────

exports.recruitmentEstimator = functions.https.onRequest((req, res) => {
  cors(req, res, async () => {
    if (req.method !== "POST") {
      return res.status(405).json({ error: "Method not allowed" });
    }

    const { conditionKey, locations, budget, targetEnrolled, timeWeeks } = req.body;

    if (!conditionKey || !locations?.length || !budget || !targetEnrolled || !timeWeeks) {
      return res.status(400).json({ error: "Missing required fields: conditionKey, locations, budget, targetEnrolled, timeWeeks" });
    }

    // Resolve API key: Secret Manager > env var > legacy config
    const apiKey =
      process.env.ANTHROPIC_API_KEY ||
      (typeof functions.config === "function" ? functions.config()?.anthropic?.key : null);

    if (!apiKey) {
      return res.status(500).json({ error: "ANTHROPIC_API_KEY not configured on this function." });
    }

    try {
      const result = analyzeRecruitment({
        conditionKey,
        locations:      Array.isArray(locations) ? locations : [locations],
        budget:         Number(budget),
        targetEnrolled: Number(targetEnrolled),
        timeWeeks:      Number(timeWeeks),
      });

      result.form = {
        budget:         Number(budget),
        targetEnrolled: Number(targetEnrolled),
        timeWeeks:      Number(timeWeeks),
      };

      const aiInterpretation = await getAIInterpretation(result, apiKey);

      return res.json({ ...result, aiInterpretation });
    } catch (err) {
      console.error("[recruitmentEstimator]", err);
      return res.status(500).json({ error: err.message });
    }
  });
});
