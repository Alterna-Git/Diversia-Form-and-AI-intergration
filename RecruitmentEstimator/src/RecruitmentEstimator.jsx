/**
 * RecruitmentEstimator.jsx
 *
 * Usage:
 *   import RecruitmentEstimator from './RecruitmentEstimator';
 *   <RecruitmentEstimator functionUrl="https://us-central1-YOUR-PROJECT.cloudfunctions.net/recruitmentEstimator" />
 *
 * Or set REACT_APP_ESTIMATOR_URL in your .env file.
 */

import React, { useState, useEffect, useCallback } from "react";
import "./RecruitmentEstimator.css";

// ─────────────────────────────────────────────────────────────────────────────
// DATA  (mirror of backend — used for instant client-side analysis)
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
// CLIENT-SIDE ANALYSIS ENGINE  (instant — used for the animated verification)
// ─────────────────────────────────────────────────────────────────────────────

function analyzeRecruitment({ conditionKey, locations, budget, targetEnrolled, timeWeeks }) {
  const cond = CONDITIONS[conditionKey];
  const fn   = FUNNEL[conditionKey];

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
  const avgHm = sumHm / locs.length;
  const avgCm = sumCm / locs.length;

  const adjPrev = Math.min(cond.prev * (avgHm / 1.15), 0.95);
  const pool    = Math.round(totalReach * adjPrev);

  const nc          = fn.ps * fn.bk * fn.at * fn.en;
  const lpe         = Math.ceil(1 / nc);
  const totalLeads  = lpe * targetEnrolled;

  const cplO = Math.round(cond.cL * avgCm);
  const cplM = Math.round(cond.cM * avgCm);
  const cplC = Math.round(cond.cH * avgCm);

  const costO = totalLeads * cplO;
  const costM = totalLeads * cplM;
  const costC = totalLeads * cplC;

  const leadsO = Math.floor(budget / cplO);
  const leadsM = Math.floor(budget / cplM);
  const leadsC = Math.floor(budget / cplC);
  const enrO   = Math.floor(leadsO * nc);
  const enrM   = Math.floor(leadsM * nc);
  const enrC   = Math.floor(leadsC * nc);

  const lpw = Math.ceil(totalLeads / timeWeeks);

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

  const passedCount = checks.filter(c => c.passed).length;
  let verdict, score;
  if (passedCount === 3) { verdict = "APROBADO"; score = Math.min(100, 85 + Math.round(nc * 100)); }
  else if (passedCount >= 1) { verdict = "VIABLE CON AJUSTES"; score = 40 + passedCount * 15; }
  else { verdict = "NO VIABLE"; score = Math.max(10, Math.round(nc * 200)); }

  const hints = [];
  if (!budgetOk) {
    hints.push({ title:"Aumentar presupuesto", desc:`Necesitas ~$${costM.toLocaleString()} para ${targetEnrolled} enrolled. Budget actual alcanza ~${enrM} pacientes.`, priority:"alta" });
    hints.push({ title:"Reducir meta de pacientes", desc:`Con $${budget.toLocaleString()} puedes enrollar ~${enrM} pacientes. Ajusta target de ${targetEnrolled} a ${enrM}.`, priority:"media" });
  }
  if (!poolOk) {
    const suggested = Object.entries(LOCATIONS)
      .filter(([k]) => !locations.includes(k))
      .sort((a, b) => b[1].pop - a[1].pop)
      .slice(0, 3);
    hints.push({ title:"Agregar ubicaciones", desc:`Incluye: ${suggested.map(([, v]) => `${v.n} (${(v.pop / 1e6).toFixed(1)}M)`).join(", ")}`, priority:"alta" });
  }
  if (fn.sf > 0.30) hints.push({ title:"Pre-screening digital", desc:`Screen-fail ${(fn.sf*100).toFixed(0)}% — implementar pre-screen para filtrar antes de cita en sitio.`, priority:"alta" });
  if (!timeOk) hints.push({ title:"Extender timeline", desc:`Necesitas ~${Math.ceil(totalLeads/lpw)+2} semanas vs ${timeWeeks} actuales.`, priority:"media" });
  hints.push({ title:"Familismo en creativos", desc:"75% Latinos aceptan cuando invitados directamente (Cross-Cancino 2021). Incluir testimonios familiares y 'Sin costo para ti'.", priority:"baja" });
  hints.push({ title:"Materiales bilingües", desc:"Solo 48% Latinos saben qué es un ensayo clínico (Wallington 2012). Creativos deben educar + reclutar.", priority:"baja" });

  return {
    condition: cond, conditionKey, locations: locs,
    population: { total: totalPop, reach: totalReach, adjustedPrev: adjPrev, pool },
    funnel: { ...fn, netConversion: nc, leadsPerEnrolled: lpe },
    totalLeads,
    scenarios: {
      optimista:   { cpl: cplO, cost: costO, leads: leadsO, enrolled: enrO, costPerEnrolled: cplO * lpe },
      realista:    { cpl: cplM, cost: costM, leads: leadsM, enrolled: enrM, costPerEnrolled: cplM * lpe },
      conservador: { cpl: cplC, cost: costC, leads: leadsC, enrolled: enrC, costPerEnrolled: cplC * lpe },
    },
    leadsPerWeek: lpw,
    checks, passedCount, verdict, score, hints,
    funnelStages: [
      { label:"Leads generados",     count: totalLeads,                                       pct: 100 },
      { label:"Pasan pre-screening", count: Math.round(totalLeads * fn.ps),                   pct: Math.round(fn.ps * 100) },
      { label:"Agendan cita",        count: Math.round(totalLeads * fn.ps * fn.bk),           pct: Math.round(fn.ps * fn.bk * 100) },
      { label:"Asisten a cita",      count: Math.round(totalLeads * fn.ps * fn.bk * fn.at),   pct: Math.round(fn.ps * fn.bk * fn.at * 100) },
      { label:"Enrolled",            count: targetEnrolled,                                    pct: Math.round(nc * 100) },
    ],
    form: { budget, targetEnrolled, timeWeeks },
    dataSources: "HCHS/SOL · CDC/OMH 2025 · NHANES Hispanic · Census 2023 · Cross-Cancino 2021 · Tufts CSDD 2019",
  };
}

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────────────────────

const sleep = ms => new Promise(r => setTimeout(r, ms));

const fmt  = n => Number(n).toLocaleString("en-US");
const fmtD = n => `$${fmt(n)}`;

// Group conditions by category for the dropdown
const CONDITION_GROUPS = Object.entries(CONDITIONS).reduce((acc, [key, c]) => {
  if (!acc[c.cat]) acc[c.cat] = [];
  acc[c.cat].push({ key, ...c });
  return acc;
}, {});

const CHECK_ICONS = ["👥", "💰", "🔬"];

// ─────────────────────────────────────────────────────────────────────────────
// SUB-COMPONENTS
// ─────────────────────────────────────────────────────────────────────────────

function CheckRow({ icon, name, status, detail, metric }) {
  return (
    <div className={`re-check-row re-check-row--${status}`}>
      <div className="re-check-icon">{icon}</div>
      <div className="re-check-body">
        <div className="re-check-header">
          <span className="re-check-name">{name}</span>
          <span className={`re-check-badge re-check-badge--${status}`}>
            {status === "pending" && "—"}
            {status === "running" && <span className="re-spinner" />}
            {status === "pass"    && "✓ PASS"}
            {status === "fail"    && "✗ FAIL"}
          </span>
        </div>
        {(status === "pass" || status === "fail") && (
          <>
            <p className="re-check-detail">{detail}</p>
            <span className="re-check-metric">{metric}</span>
          </>
        )}
      </div>
    </div>
  );
}

function MetricCard({ label, value, sub }) {
  return (
    <div className="re-metric-card">
      <div className="re-metric-value">{value}</div>
      <div className="re-metric-label">{label}</div>
      {sub && <div className="re-metric-sub">{sub}</div>}
    </div>
  );
}

function ScenarioCard({ label, data, highlighted }) {
  const cls = `re-scenario-card${highlighted ? " re-scenario-card--highlighted" : ""}`;
  return (
    <div className={cls}>
      {highlighted && <div className="re-scenario-ribbon">Realista</div>}
      <div className="re-scenario-label">{label}</div>
      <div className="re-scenario-row"><span>CPL</span><strong>{fmtD(data.cpl)}</strong></div>
      <div className="re-scenario-row"><span>Leads</span><strong>{fmt(data.leads)}</strong></div>
      <div className="re-scenario-row"><span>Enrolled</span><strong>{fmt(data.enrolled)}</strong></div>
      <div className="re-scenario-row"><span>Costo total</span><strong>{fmtD(data.cost)}</strong></div>
      <div className="re-scenario-row re-scenario-row--cpe"><span>$/enrolled</span><strong>{fmtD(data.costPerEnrolled)}</strong></div>
    </div>
  );
}

function HintCard({ hint }) {
  const icons = { alta: "🚨", media: "⚠️", baja: "💡" };
  return (
    <div className={`re-hint re-hint--${hint.priority}`}>
      <div className="re-hint-header">
        <span className="re-hint-icon">{icons[hint.priority]}</span>
        <span className="re-hint-title">{hint.title}</span>
        <span className={`re-hint-badge re-hint-badge--${hint.priority}`}>{hint.priority.toUpperCase()}</span>
      </div>
      <p className="re-hint-desc">{hint.desc}</p>
    </div>
  );
}

// ─────────────────────────────────────────────────────────────────────────────
// MAIN COMPONENT
// ─────────────────────────────────────────────────────────────────────────────

export default function RecruitmentEstimator({
  functionUrl = process.env.REACT_APP_ESTIMATOR_URL || "",
}) {
  // ── State ──────────────────────────────────────────────────────────────────
  const [phase, setPhase]               = useState("form"); // form | verifying | results
  const [form, setForm]                 = useState({
    conditionKey:   "OB",
    locations:      ["PR"],
    budget:         "",
    targetEnrolled: "",
    timeWeeks:      "",
  });
  const [errors, setErrors]             = useState({});
  const [result, setResult]             = useState(null);
  const [checkStatuses, setCheckStatuses] = useState(["pending", "pending", "pending"]);
  const [aiText, setAiText]             = useState("");
  const [aiLoading, setAiLoading]       = useState(false);

  // ── Form helpers ───────────────────────────────────────────────────────────
  const setField = (key, val) => setForm(f => ({ ...f, [key]: val }));

  const toggleLocation = loc => {
    setForm(f => {
      const locs = f.locations.includes(loc)
        ? f.locations.filter(l => l !== loc)
        : [...f.locations, loc];
      return { ...f, locations: locs.length ? locs : f.locations }; // keep at least one
    });
  };

  const validate = () => {
    const e = {};
    if (!form.conditionKey) e.conditionKey = "Selecciona una condición.";
    if (!form.locations.length) e.locations = "Selecciona al menos una ubicación.";
    if (!form.budget || Number(form.budget) < 1000) e.budget = "Presupuesto mínimo: $1,000.";
    if (!form.targetEnrolled || Number(form.targetEnrolled) < 1) e.targetEnrolled = "Ingresa un target válido.";
    if (!form.timeWeeks || Number(form.timeWeeks) < 1) e.timeWeeks = "Ingresa una duración válida.";
    return e;
  };

  // ── Submit → Verification → Results ───────────────────────────────────────
  const handleSubmit = useCallback(async e => {
    e.preventDefault();
    const errs = validate();
    if (Object.keys(errs).length) { setErrors(errs); return; }
    setErrors({});

    const params = {
      conditionKey:   form.conditionKey,
      locations:      form.locations,
      budget:         Number(form.budget),
      targetEnrolled: Number(form.targetEnrolled),
      timeWeeks:      Number(form.timeWeeks),
    };

    // Instant client-side analysis (for animation)
    const localResult = analyzeRecruitment(params);
    setResult(localResult);
    setAiText("");
    setCheckStatuses(["pending", "pending", "pending"]);
    setPhase("verifying");

    // Animate 3 checks with real pass/fail values
    for (let i = 0; i < 3; i++) {
      await sleep(250);
      setCheckStatuses(prev => prev.map((s, idx) => idx === i ? "running" : s));
      await sleep(1250);
      setCheckStatuses(prev =>
        prev.map((s, idx) => idx === i ? (localResult.checks[i].passed ? "pass" : "fail") : s)
      );
    }
    await sleep(600);
    setPhase("results");

    // Fetch Claude interpretation from Firebase Function
    if (!functionUrl) {
      setAiText("Configura REACT_APP_ESTIMATOR_URL para obtener interpretación de IA.");
      return;
    }
    setAiLoading(true);
    try {
      const resp = await fetch(functionUrl, {
        method:  "POST",
        headers: { "Content-Type": "application/json" },
        body:    JSON.stringify(params),
      });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const data = await resp.json();
      setAiText(data.aiInterpretation || "Interpretación no disponible.");
    } catch (err) {
      console.error("[RecruitmentEstimator]", err);
      setAiText("No se pudo obtener la interpretación de IA. Verifica la conexión.");
    } finally {
      setAiLoading(false);
    }
  }, [form, functionUrl]);

  const handleReset = () => {
    setPhase("form");
    setResult(null);
    setAiText("");
    setCheckStatuses(["pending", "pending", "pending"]);
  };

  // ── PHASE 1: FORM ──────────────────────────────────────────────────────────
  if (phase === "form") {
    return (
      <div className="re-root">
        <div className="re-header">
          <span className="re-badge">LATINO-FOCUSED DATA</span>
          <h1 className="re-title">Recruitment Estimator</h1>
          <p className="re-subtitle">
            Verifica la viabilidad de reclutamiento para ensayos clínicos en comunidades Latinas vía Meta Ads
          </p>
        </div>

        <form className="re-form" onSubmit={handleSubmit} noValidate>
          {/* Condition */}
          <div className="re-field">
            <label className="re-label">Condición de Salud</label>
            <select
              className="re-select"
              value={form.conditionKey}
              onChange={e => setField("conditionKey", e.target.value)}
            >
              {Object.entries(CONDITION_GROUPS).map(([cat, items]) => (
                <optgroup key={cat} label={cat}>
                  {items.map(c => (
                    <option key={c.key} value={c.key}>
                      {c.n} — prev. {(c.prev * 100).toFixed(1)}% Latino
                    </option>
                  ))}
                </optgroup>
              ))}
            </select>
            {errors.conditionKey && <span className="re-error">{errors.conditionKey}</span>}
          </div>

          {/* Locations */}
          <div className="re-field">
            <label className="re-label">Ubicaciones <span className="re-label-hint">(multi-selección)</span></label>
            <div className="re-chips">
              {Object.entries(LOCATIONS).map(([code, loc]) => (
                <button
                  key={code}
                  type="button"
                  className={`re-chip${form.locations.includes(code) ? " re-chip--active" : ""}`}
                  onClick={() => toggleLocation(code)}
                >
                  <span className="re-chip-code">{code}</span>
                  <span className="re-chip-name">{loc.n}</span>
                  <span className="re-chip-pop">{(loc.pop / 1e6).toFixed(1)}M</span>
                </button>
              ))}
            </div>
            {errors.locations && <span className="re-error">{errors.locations}</span>}
          </div>

          {/* Numeric inputs */}
          <div className="re-field-row">
            <div className="re-field">
              <label className="re-label">Presupuesto (USD)</label>
              <div className="re-input-wrap">
                <span className="re-input-prefix">$</span>
                <input
                  className="re-input re-input--prefixed"
                  type="number"
                  min="1000"
                  step="500"
                  placeholder="15,000"
                  value={form.budget}
                  onChange={e => setField("budget", e.target.value)}
                />
              </div>
              {errors.budget && <span className="re-error">{errors.budget}</span>}
            </div>

            <div className="re-field">
              <label className="re-label">Pacientes a enrollar</label>
              <input
                className="re-input"
                type="number"
                min="1"
                placeholder="50"
                value={form.targetEnrolled}
                onChange={e => setField("targetEnrolled", e.target.value)}
              />
              {errors.targetEnrolled && <span className="re-error">{errors.targetEnrolled}</span>}
            </div>

            <div className="re-field">
              <label className="re-label">Duración (semanas)</label>
              <input
                className="re-input"
                type="number"
                min="1"
                placeholder="12"
                value={form.timeWeeks}
                onChange={e => setField("timeWeeks", e.target.value)}
              />
              {errors.timeWeeks && <span className="re-error">{errors.timeWeeks}</span>}
            </div>
          </div>

          <button type="submit" className="re-submit-btn">
            🔍 Verificar Viabilidad con AI Agent
          </button>

          <p className="re-disclaimer">
            Datos: HCHS/SOL · CDC/OMH 2025 · Census 2023 · Cross-Cancino 2021 · Tufts CSDD 2019
          </p>
        </form>
      </div>
    );
  }

  // ── PHASE 2: VERIFYING ─────────────────────────────────────────────────────
  if (phase === "verifying") {
    return (
      <div className="re-root">
        <div className="re-header">
          <span className="re-badge">VERIFICACIÓN EN PROGRESO</span>
          <h2 className="re-title re-title--sm">Analizando viabilidad…</h2>
          <p className="re-subtitle">
            Revisando {result?.condition?.n} en {result?.locations?.map(l => l.n).join(", ")}
          </p>
        </div>

        <div className="re-checks">
          {CHECK_ICONS.map((icon, i) => (
            <CheckRow
              key={i}
              icon={icon}
              name={result?.checks[i]?.name || ""}
              status={checkStatuses[i]}
              detail={result?.checks[i]?.detail || ""}
              metric={result?.checks[i]?.metric || ""}
            />
          ))}
        </div>
      </div>
    );
  }

  // ── PHASE 3: RESULTS ───────────────────────────────────────────────────────
  const r = result;
  const approved = r?.verdict === "APROBADO";
  const partial  = r?.verdict === "VIABLE CON AJUSTES";

  return (
    <div className="re-root">
      {/* Verdict banner */}
      <div className={`re-verdict re-verdict--${approved ? "approved" : partial ? "partial" : "denied"}`}>
        <div className="re-verdict-inner">
          <span className="re-verdict-emoji">
            {approved ? "✅" : partial ? "⚠️" : "❌"}
          </span>
          <div>
            <div className="re-verdict-label">Veredicto</div>
            <div className="re-verdict-text">{r.verdict}</div>
          </div>
          <div className="re-verdict-score">
            <div className="re-verdict-score-num">{r.score}</div>
            <div className="re-verdict-score-label">/ 100</div>
          </div>
        </div>
      </div>

      {/* AI Interpretation */}
      <div className="re-card re-ai-card">
        <div className="re-card-header">
          <span className="re-card-icon">🤖</span>
          <span className="re-card-title">Interpretación IA — Claude Sonnet</span>
        </div>
        {aiLoading ? (
          <div className="re-ai-loading">
            <span className="re-spinner re-spinner--lg" />
            <span>Generando análisis…</span>
          </div>
        ) : (
          <p className="re-ai-text">{aiText || "—"}</p>
        )}
      </div>

      {/* Metric cards */}
      <div className="re-metrics">
        <MetricCard
          label="Pool Latino alcanzable"
          value={fmt(r.population.pool)}
          sub={`${(r.population.adjustedPrev * 100).toFixed(1)}% prevalencia ajustada`}
        />
        <MetricCard
          label="Leads necesarios"
          value={fmt(r.totalLeads)}
          sub={`${r.funnel.leadsPerEnrolled} leads por enrolled`}
        />
        <MetricCard
          label="Costo realista"
          value={fmtD(r.scenarios.realista.cost)}
          sub={`$${r.scenarios.realista.cpl} CPL`}
        />
        <MetricCard
          label="Enrolled estimados"
          value={fmt(r.scenarios.realista.enrolled)}
          sub={`de ${fmt(r.form.targetEnrolled)} objetivo`}
        />
      </div>

      {/* Funnel */}
      <div className="re-card">
        <div className="re-card-header">
          <span className="re-card-icon">🔻</span>
          <span className="re-card-title">Funnel de Reclutamiento Latino</span>
          <span className="re-card-sub">screen-fail: {(r.funnel.sf * 100).toFixed(0)}%</span>
        </div>
        <div className="re-funnel">
          {r.funnelStages.map((stage, i) => (
            <div key={i} className="re-funnel-stage">
              <div className="re-funnel-meta">
                <span className="re-funnel-label">{stage.label}</span>
                <span className="re-funnel-count">{fmt(stage.count)}</span>
              </div>
              <div className="re-funnel-bar-wrap">
                <div
                  className={`re-funnel-bar${i === r.funnelStages.length - 1 ? " re-funnel-bar--final" : ""}`}
                  style={{ width: `${stage.pct}%` }}
                />
              </div>
              <span className="re-funnel-pct">{stage.pct}%</span>
            </div>
          ))}
        </div>
      </div>

      {/* Scenarios */}
      <div className="re-card">
        <div className="re-card-header">
          <span className="re-card-icon">📊</span>
          <span className="re-card-title">Escenarios de Costo</span>
        </div>
        <div className="re-scenarios">
          <ScenarioCard label="Optimista"   data={r.scenarios.optimista}   highlighted={false} />
          <ScenarioCard label="Realista"    data={r.scenarios.realista}    highlighted={true}  />
          <ScenarioCard label="Conservador" data={r.scenarios.conservador} highlighted={false} />
        </div>
      </div>

      {/* Checks summary */}
      <div className="re-card">
        <div className="re-card-header">
          <span className="re-card-icon">✔️</span>
          <span className="re-card-title">Triple Verificación</span>
        </div>
        <div className="re-checks re-checks--compact">
          {CHECK_ICONS.map((icon, i) => (
            <CheckRow
              key={i}
              icon={icon}
              name={r.checks[i].name}
              status={r.checks[i].passed ? "pass" : "fail"}
              detail={r.checks[i].detail}
              metric={r.checks[i].metric}
            />
          ))}
        </div>
      </div>

      {/* Hints */}
      <div className="re-card">
        <div className="re-card-header">
          <span className="re-card-icon">{approved ? "🚀" : "🛠"}</span>
          <span className="re-card-title">
            {approved ? "Recomendaciones para optimizar" : "Cómo mejorar para ser aprobado"}
          </span>
        </div>
        <div className="re-hints">
          {r.hints.map((h, i) => <HintCard key={i} hint={h} />)}
        </div>
      </div>

      {/* Footer */}
      <div className="re-footer">
        <p className="re-sources">{r.dataSources}</p>
        <button className="re-reset-btn" onClick={handleReset}>
          ← Nueva estimación
        </button>
      </div>
    </div>
  );
}
