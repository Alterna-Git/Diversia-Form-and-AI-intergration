<?php if (!defined('ABSPATH')) exit; ?>
<?php
// Variables available: $app (application row object), $application_id (int),
//                      $token (string), $nonce_stripe (string)

// ── Pre-select package tier based on AI score ────────────────────────────────
$ai_score = (float) $app->ai_score;
if ($ai_score >= 92) {
    $default_pkg = 'pro';
} elseif ($ai_score >= 83) {
    $default_pkg = 'standard';
} else {
    $default_pkg = 'basic';
}
?>

<div class="dco-wrapper dco-payment-gate" data-lang="en">

    <!-- Language toggle -->
    <div class="dco-lang-bar">
        <button class="dco-lang-toggle" id="dco-lang-btn" type="button" aria-label="Switch language">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
            <span data-i18n data-en="Ver en Español" data-es="View in English">Ver en Español</span>
        </button>
    </div>

    <div class="dco-panel">

        <!-- Approval Header -->
        <div class="dco-panel__header">
            <div class="dco-result__icon" style="color: var(--dco-success);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h2 class="dco-panel__title" data-i18n
                data-en="You Have Been Approved!"
                data-es="¡Ha sido aprobado(a)!">
                You Have Been Approved!
            </h2>
            <p class="dco-panel__subtitle" data-i18n
               data-en="Select a service package and complete your payment to activate your Diversia Health client account."
               data-es="Seleccione un paquete de servicio y complete su pago para activar su cuenta de cliente Diversia Health.">
                Select a service package and complete your payment to activate your Diversia Health client account.
            </p>
        </div>

        <!-- Application Summary -->
        <div class="dco-summary">
            <h3 class="dco-summary__title" data-i18n data-en="Your Application Summary" data-es="Resumen de su Solicitud">
                Your Application Summary
            </h3>
            <table class="dco-summary__table">
                <tr>
                    <th data-i18n data-en="Organization" data-es="Organización">Organization</th>
                    <td><?php echo esc_html($app->company_name); ?></td>
                </tr>
                <tr>
                    <th data-i18n data-en="Trial Type" data-es="Tipo de Ensayo">Trial Type</th>
                    <td><?php echo esc_html($app->trial_type); ?></td>
                </tr>
                <tr>
                    <th data-i18n data-en="Budget" data-es="Presupuesto">Budget</th>
                    <td><?php echo esc_html($app->estimated_budget); ?></td>
                </tr>
                <tr>
                    <th data-i18n data-en="Organization Type" data-es="Tipo de Organización">Organization Type</th>
                    <td><?php echo esc_html($app->organization_type); ?></td>
                </tr>
                <tr>
                    <th data-i18n data-en="AI Score" data-es="Calificación AI">AI Score</th>
                    <td><strong><?php echo esc_html(number_format((float)$app->ai_score, 1)); ?>/100</strong></td>
                </tr>
            </table>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             PRICING PACKAGES
        ═══════════════════════════════════════════════════════════════ -->
        <div class="dco-packages">
            <h3 class="dco-packages__title" data-i18n
                data-en="Choose Your Service Package"
                data-es="Elija su Paquete de Servicio">
                Choose Your Service Package
            </h3>
            <p class="dco-packages__subtitle" data-i18n
               data-en="Each tier is engineered for a distinct operational profile. Your AI qualification score pre-selected the package that best fits your trial's scope and complexity."
               data-es="Cada nivel está diseñado para un perfil operativo distinto. Su puntuación de calificación IA pre-seleccionó el paquete más adecuado para su ensayo.">
                Each tier is engineered for a distinct operational profile. Your AI qualification score pre-selected the package that best fits your trial's scope and complexity.
            </p>

            <!-- Hidden input — updated by JS on package selection -->
            <input type="hidden" id="dco-selected-package" value="<?php echo esc_attr($default_pkg); ?>">

            <div class="dco-packages__grid">

                <!-- ── BASIC: Foundation ─────────────────────────────── -->
                <div class="dco-pkg-card <?php echo ($default_pkg === 'basic') ? 'dco-pkg-card--selected' : ''; ?>"
                     data-package="basic" tabindex="0" role="button" aria-pressed="<?php echo ($default_pkg === 'basic') ? 'true' : 'false'; ?>">

                    <div class="dco-pkg-card__selected-check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                             fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>

                    <div class="dco-pkg-card__tier" data-i18n data-en="Basic" data-es="Básico">Basic</div>
                    <div class="dco-pkg-card__name" data-i18n data-en="Foundation" data-es="Foundation">Foundation</div>
                    <div class="dco-pkg-card__price">$4,500 <span data-i18n data-en="/ engagement" data-es="/ proyecto">/ engagement</span></div>
                    <div class="dco-pkg-card__billing" data-i18n
                         data-en="One-time onboarding + campaign activation fee"
                         data-es="Cargo único de incorporación y activación de campaña">
                        One-time onboarding + campaign activation fee
                    </div>

                    <div class="dco-pkg-card__ideal" data-i18n
                         data-en="Ideal for: CROs launching a first digital Latino recruitment channel for a single Phase II/III trial with a structured 6–12 month timeline."
                         data-es="Ideal para: CROs que lanzan su primer canal digital de reclutamiento Latino para un ensayo Phase II/III con cronograma de 6–12 meses.">
                        <strong data-i18n data-en="Ideal for:" data-es="Ideal para:">Ideal for:</strong>
                        CROs launching a first digital Latino recruitment channel for a single Phase II/III trial with a structured 6–12 month timeline.
                    </div>

                    <hr class="dco-pkg-card__divider">

                    <ul class="dco-pkg-card__features">
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="AI-assisted patient matching from Diversia's Latino participant network" data-es="Concordancia de pacientes por IA desde la red de participantes Latinos de Diversia">AI-assisted patient matching from Diversia's Latino participant network</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Meta Ads campaign — 1 audience segment, 1 creative variant" data-es="Campaña Meta Ads — 1 segmento de audiencia, 1 variante creativa">Meta Ads campaign — 1 audience segment, 1 creative variant</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Bilingual HIPAA-compliant pre-screening landing page (EN/ES)" data-es="Página de pre-selección bilingüe compatible con HIPAA (EN/ES)">Bilingual HIPAA-compliant pre-screening landing page (EN/ES)</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Protocol-informed audience targeting (ICD-10 condition mapping)" data-es="Segmentación de audiencia basada en protocolo (mapeo ICD-10)">Protocol-informed audience targeting (ICD-10 condition mapping)</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="IRB/IEC compliance review for all advertising assets" data-es="Revisión de cumplimiento IRB/IEC para todos los materiales publicitarios">IRB/IEC compliance review for all advertising assets</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Bi-weekly recruitment performance reports + Clinical Trial Dashboard" data-es="Informes de rendimiento quincenales + Panel de Ensayos Clínicos">Bi-weekly recruitment performance reports + Clinical Trial Dashboard</span>
                        </li>
                    </ul>

                    <div class="dco-pkg-card__value" data-i18n
                         data-en="Reduces recruitment lag 30–45% vs. site-only enrollment."
                         data-es="Reduce el retraso de reclutamiento 30–45% frente a la inscripción solo en sitio.">
                        Reduces recruitment lag 30–45% vs. site-only enrollment.
                    </div>

                    <button type="button" class="dco-pkg-select-btn" data-package="basic">
                        <span data-i18n data-en="Select Foundation" data-es="Seleccionar Foundation">Select Foundation</span>
                    </button>
                </div>

                <!-- ── STANDARD: Accelerate ──────────────────────────── -->
                <div class="dco-pkg-card dco-pkg-card--featured <?php echo ($default_pkg === 'standard') ? 'dco-pkg-card--selected' : ''; ?>"
                     data-package="standard" tabindex="0" role="button" aria-pressed="<?php echo ($default_pkg === 'standard') ? 'true' : 'false'; ?>">

                    <div class="dco-pkg-card__popular-ribbon" data-i18n data-en="Most Selected" data-es="Más Elegido">Most Selected</div>

                    <div class="dco-pkg-card__selected-check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                             fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>

                    <div class="dco-pkg-card__tier" data-i18n data-en="Standard" data-es="Estándar">Standard</div>
                    <div class="dco-pkg-card__name" data-i18n data-en="Accelerate" data-es="Accelerate">Accelerate</div>
                    <div class="dco-pkg-card__price">$11,500 <span data-i18n data-en="/ engagement" data-es="/ proyecto">/ engagement</span></div>
                    <div class="dco-pkg-card__billing" data-i18n
                         data-en="Comprehensive multi-site campaign infrastructure"
                         data-es="Infraestructura completa para campaña multi-sitio">
                        Comprehensive multi-site campaign infrastructure
                    </div>

                    <div class="dco-pkg-card__ideal" data-i18n
                         data-en="Ideal for: CROs managing multi-site Phase II/III trials requiring coordinated outreach across 3–5 investigator sites with competing patient pools."
                         data-es="Ideal para: CROs que gestionan ensayos multi-sitio Phase II/III con reclutamiento coordinado en 3–5 sitios de investigación.">
                        <strong data-i18n data-en="Ideal for:" data-es="Ideal para:">Ideal for:</strong>
                        CROs managing multi-site Phase II/III trials requiring coordinated outreach across 3–5 investigator sites with competing patient pools.
                    </div>

                    <hr class="dco-pkg-card__divider">

                    <ul class="dco-pkg-card__features">
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Everything in Foundation, plus:" data-es="Todo lo de Foundation, más:"><strong>Everything in Foundation, plus:</strong></span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Multi-audience targeting — up to 4 segments, 3 creative variants per condition" data-es="Segmentación múltiple — hasta 4 segmentos, 3 variantes creativas por condición">Multi-audience targeting — up to 4 segments, 3 creative variants per condition</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Referral tracking integration (UTM + CRM webhook delivery)" data-es="Integración de rastreo de referidos (UTM + entrega vía webhook CRM)">Referral tracking integration (UTM + CRM webhook delivery)</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="CTMS-compatible structured data export + real-time eligibility pre-screening" data-es="Exportación de datos compatible con CTMS + pre-selección de elegibilidad en tiempo real">CTMS-compatible structured data export + real-time eligibility pre-screening</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Enrollment velocity dashboard with 30/60/90-day projections" data-es="Panel de velocidad de inscripción con proyecciones a 30/60/90 días">Enrollment velocity dashboard with 30/60/90-day projections</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Lookalike modeling + site-level lead attribution and geo-distribution reporting" data-es="Modelado lookalike + atribución de leads por sitio y reporte de distribución geográfica">Lookalike modeling + site-level lead attribution and geo-distribution reporting</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Monthly strategy calls with dedicated campaign strategist + A/B optimization cadence" data-es="Llamadas estratégicas mensuales con estratega dedicado + cadencia de optimización A/B">Monthly strategy calls with dedicated campaign strategist + A/B optimization cadence</span>
                        </li>
                    </ul>

                    <div class="dco-pkg-card__value" data-i18n
                         data-en="Delivers pre-qualified candidate profiles to site coordinators before ICA — reducing screen failure rate at source."
                         data-es="Entrega perfiles de candidatos pre-calificados a coordinadores de sitio antes de la ICA — reduciendo la tasa de falla de cribado en la fuente.">
                        Delivers pre-qualified candidate profiles to site coordinators before ICA — reducing screen failure rate at source.
                    </div>

                    <button type="button" class="dco-pkg-select-btn" data-package="standard">
                        <span data-i18n data-en="Select Accelerate" data-es="Seleccionar Accelerate">Select Accelerate</span>
                    </button>
                </div>

                <!-- ── PRO: Command ──────────────────────────────────── -->
                <div class="dco-pkg-card <?php echo ($default_pkg === 'pro') ? 'dco-pkg-card--selected' : ''; ?>"
                     data-package="pro" tabindex="0" role="button" aria-pressed="<?php echo ($default_pkg === 'pro') ? 'true' : 'false'; ?>">

                    <div class="dco-pkg-card__selected-check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                             fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>

                    <div class="dco-pkg-card__tier" data-i18n data-en="Pro" data-es="Pro">Pro</div>
                    <div class="dco-pkg-card__name" data-i18n data-en="Command" data-es="Command">Command</div>
                    <div class="dco-pkg-card__price" data-i18n data-en="Custom" data-es="A Medida">Custom</div>
                    <div class="dco-pkg-card__billing" data-i18n
                         data-en="Enterprise scope — contact us for custom pricing"
                         data-es="Alcance empresarial — contáctenos para precios personalizados">
                        Enterprise scope — contact us for custom pricing
                    </div>

                    <div class="dco-pkg-card__ideal" data-i18n
                         data-en="Ideal for: Enterprise CROs and sponsors running multi-regional, multi-indication programs across 10+ investigator sites requiring clinical-grade data infrastructure."
                         data-es="Ideal para: CROs empresariales y patrocinadores con programas multi-regionales y multi-indicación en 10+ sitios que requieren infraestructura de datos de grado clínico.">
                        <strong data-i18n data-en="Ideal for:" data-es="Ideal para:">Ideal for:</strong>
                        Enterprise CROs and sponsors running multi-regional, multi-indication programs across 10+ investigator sites requiring clinical-grade data infrastructure.
                    </div>

                    <hr class="dco-pkg-card__divider">

                    <ul class="dco-pkg-card__features">
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Everything in Accelerate, plus:" data-es="Todo lo de Accelerate, más:"><strong>Everything in Accelerate, plus:</strong></span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Multi-platform activation: Meta, Google, Programmatic Display + community partnerships" data-es="Activación multi-plataforma: Meta, Google, Display Programático + alianzas comunitarias">Multi-platform activation: Meta, Google, Programmatic Display + community partnerships</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="CTMS/eTMF API integration (REST/FHIR-compatible participant data output)" data-es="Integración API CTMS/eTMF (salida de datos de participantes compatible con REST/FHIR)">CTMS/eTMF API integration (REST/FHIR-compatible participant data output)</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="21 CFR Part 11 aligned audit trail for all participant touchpoints" data-es="Registro de auditoría alineado a 21 CFR Parte 11 para todos los puntos de contacto con participantes">21 CFR Part 11 aligned audit trail for all participant touchpoints</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Predictive enrollment modeling with adaptive budget reallocation" data-es="Modelado predictivo de inscripción con reasignación adaptativa de presupuesto">Predictive enrollment modeling with adaptive budget reallocation</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="IRB-approved bilingual pre-screening chatbot (EN/ES) + 4-hour support SLA" data-es="Chatbot bilingüe de pre-selección aprobado por IRB (EN/ES) + SLA de soporte de 4 horas">IRB-approved bilingual pre-screening chatbot (EN/ES) + 4-hour support SLA</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Enrollment guarantee SLA + dedicated CRO partnership manager with weekly KPI briefings" data-es="SLA de garantía de inscripción + gerente de alianza CRO dedicado con informes semanales de KPIs">Enrollment guarantee SLA + dedicated CRO partnership manager with weekly KPI briefings</span>
                        </li>
                        <li>
                            <span class="dco-pkg-card__check" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <span data-i18n data-en="Annual recruitment intelligence report (market benchmarks + condition-specific outreach data)" data-es="Informe anual de inteligencia de reclutamiento (benchmarks de mercado + datos de alcance por condición)">Annual recruitment intelligence report (market benchmarks + condition-specific outreach data)</span>
                        </li>
                    </ul>

                    <div class="dco-pkg-card__value" data-i18n
                         data-en="Built for CROs where enrollment delays cost $600K–$8M per day. The infrastructure layer for zero-failure, multi-indication programs."
                         data-es="Diseñado para CROs donde los retrasos en inscripción cuestan $600K–$8M por día. Infraestructura para programas multi-indicación sin margen de error.">
                        Built for CROs where enrollment delays cost $600K–$8M per day. The infrastructure layer for zero-failure, multi-indication programs.
                    </div>

                    <button type="button" class="dco-pkg-select-btn" data-package="pro">
                        <span data-i18n data-en="Select Command" data-es="Seleccionar Command">Select Command</span>
                    </button>
                </div>

            </div><!-- /.dco-packages__grid -->

            <!-- Selected package summary (updated by JS) -->
            <div class="dco-pkg-summary" id="dco-pkg-summary" style="display:none;"></div>

        </div><!-- /.dco-packages -->

        <!-- Payment Method Selection -->
        <div class="dco-pay-method-section">
            <h3 class="dco-pay-method-section__title" data-i18n data-en="Select Payment Method" data-es="Seleccione Método de Pago">
                Select Payment Method
            </h3>
            <div class="dco-pay-method-grid" id="dco-pay-method-grid">

                <!-- Card option -->
                <button class="dco-pay-method-card" type="button" data-method="card" aria-pressed="false">
                    <span class="dco-pay-method-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </span>
                    <span class="dco-pay-method-card__title" data-i18n data-en="Credit / Debit Card" data-es="Tarjeta de Crédito / Débito">
                        Credit / Debit Card
                    </span>
                    <span class="dco-pay-method-card__desc" data-i18n data-en="Visa, Mastercard, Amex" data-es="Visa, Mastercard, Amex">
                        Visa, Mastercard, Amex
                    </span>
                    <span class="dco-pay-method-card__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                </button>

                <!-- eCheck / ACH option -->
                <button class="dco-pay-method-card" type="button" data-method="us_bank_account" aria-pressed="false">
                    <span class="dco-pay-method-card__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="15" rx="2"/>
                            <polyline points="16 2 12 6 8 2"/>
                            <line x1="12" y1="12" x2="12" y2="17"/>
                            <line x1="9" y1="14.5" x2="15" y2="14.5"/>
                        </svg>
                    </span>
                    <span class="dco-pay-method-card__title" data-i18n data-en="eCheck / ACH Transfer" data-es="Cheque Electrónico / Transferencia ACH">
                        eCheck / ACH Transfer
                    </span>
                    <span class="dco-pay-method-card__desc" data-i18n data-en="US bank account — no card fees" data-es="Cuenta bancaria EE.UU. — sin cargos de tarjeta">
                        US bank account — no card fees
                    </span>
                    <span class="dco-pay-method-card__check" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </span>
                </button>

            </div>
            <p class="dco-pay-method-section__hint" id="dco-pay-method-hint" style="display:none;"
               data-i18n data-en="Please select a payment method to continue." data-es="Seleccione un método de pago para continuar.">
                Please select a payment method to continue.
            </p>
        </div>

        <!-- eCheck / ACH info panel -->
        <div class="dco-ach-info" id="dco-ach-info" style="display:none;">
            <div class="dco-ach-info__header">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="15" rx="2"/>
                    <polyline points="16 2 12 6 8 2"/>
                    <line x1="12" y1="12" x2="12" y2="17"/>
                    <line x1="9" y1="14.5" x2="15" y2="14.5"/>
                </svg>
                <span data-i18n data-en="How eCheck / ACH works" data-es="Cómo funciona el Cheque Electrónico / ACH">
                    How eCheck / ACH works
                </span>
            </div>
            <ol class="dco-ach-info__steps">
                <li>
                    <span data-i18n
                          data-en="You will be redirected to Stripe's secure page to enter your US bank account details (routing number &amp; account number)."
                          data-es="Será redirigido a la página segura de Stripe para ingresar los datos de su cuenta bancaria (número de ruta y número de cuenta).">
                        You will be redirected to Stripe's secure page to enter your US bank account details (routing number &amp; account number).
                    </span>
                </li>
                <li>
                    <span data-i18n
                          data-en="Stripe will instantly verify your bank, or send micro-deposits (1–2 business days) if instant verification is unavailable."
                          data-es="Stripe verificará su banco de forma instantánea o enviará micro-depósitos (1–2 días hábiles) si la verificación instantánea no está disponible.">
                        Stripe will instantly verify your bank, or send micro-deposits (1–2 business days) if instant verification is unavailable.
                    </span>
                </li>
                <li>
                    <span data-i18n
                          data-en="Once verified, your payment will be debited and your client account activated within 2–5 business days."
                          data-es="Una vez verificado, su pago será debitado y su cuenta de cliente activada en 2–5 días hábiles.">
                        Once verified, your payment will be debited and your client account activated within 2–5 business days.
                    </span>
                </li>
            </ol>
        </div>

        <!-- Error banner -->
        <div class="dco-error-banner" id="dco-gate-error" style="display:none;"></div>

        <!-- Pay button -->
        <div class="dco-result__cta">
            <button class="dco-btn dco-btn--primary dco-btn--large" id="dco-gate-pay-btn"
                    data-application-id="<?php echo esc_attr($application_id); ?>"
                    data-token="<?php echo esc_attr($token); ?>"
                    data-nonce="<?php echo esc_attr($nonce_stripe); ?>"
                    disabled>
                <span class="dco-btn__text">
                    <span id="dco-gate-pay-label"
                          data-i18n
                          data-en="Complete Payment"
                          data-es="Completar Pago">
                        Complete Payment
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </span>
                <span class="dco-btn__spinner" style="display:none;"></span>
            </button>
            <p class="dco-hint dco-hint--center" id="dco-gate-pay-hint" style="margin-top: 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                     fill="none" stroke="#888" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <span id="dco-gate-pay-hint-text"
                      data-i18n
                      data-en="Secure payment via Stripe"
                      data-es="Pago seguro via Stripe">
                    Secure payment via Stripe
                </span>
            </p>
        </div>

    </div><!-- .dco-panel -->
</div><!-- .dco-wrapper -->

<script>
jQuery(document).ready(function ($) {
    'use strict';

    var selectedMethod  = null;
    var selectedPackage = '<?php echo esc_js($default_pkg); ?>';

    var $payBtn    = $('#dco-gate-pay-btn');
    var $hint      = $('#dco-pay-method-hint');
    var $achInfo   = $('#dco-ach-info');
    var $btnLabel  = $('#dco-gate-pay-label');
    var $hintText  = $('#dco-gate-pay-hint-text');
    var $pkgInput  = $('#dco-selected-package');
    var $pkgSummary = $('#dco-pkg-summary');

    // Package meta — names/prices for the summary bar
    var pkgMeta = {
        basic:    { en: 'Foundation',  es: 'Foundation',  price: '$4,500'  },
        standard: { en: 'Accelerate',  es: 'Accelerate',  price: '$11,500' },
        pro:      { en: 'Command',     es: 'Command',     price: 'Custom'  }
    };

    function getLang() {
        return ($('.dco-wrapper').data('lang') || 'en');
    }

    // ── Package selection ─────────────────────────────────────────────
    function selectPackage(pkg) {
        selectedPackage = pkg;
        $pkgInput.val(pkg);

        // Update card states
        $('.dco-pkg-card').removeClass('dco-pkg-card--selected').attr('aria-pressed', 'false');
        $('.dco-pkg-card[data-package="' + pkg + '"]').addClass('dco-pkg-card--selected').attr('aria-pressed', 'true');

        // Render summary bar
        renderPkgSummary(pkg);
    }

    function renderPkgSummary(pkg) {
        var lang = getLang();
        var meta = pkgMeta[pkg];
        if (!meta) return;

        var name  = meta[lang] || meta.en;
        var price = meta.price;

        var summaryHtml = '<div class="dco-pkg-summary__inner">'
            + '<span class="dco-pkg-summary__label" data-i18n data-en="Selected Package:" data-es="Paquete Seleccionado:">'
            + (lang === 'es' ? 'Paquete Seleccionado:' : 'Selected Package:')
            + '</span>'
            + '<strong class="dco-pkg-summary__name">' + name + '</strong>'
            + '<span class="dco-pkg-summary__price">' + price + '</span>'
            + '</div>';

        $pkgSummary.html(summaryHtml).show();
    }

    // Clicks on card body
    $('.dco-pkg-card').on('click', function () {
        selectPackage($(this).data('package'));
    });

    // Clicks on select button (stop propagation so card click doesn't double-fire)
    $('.dco-pkg-select-btn').on('click', function (e) {
        e.stopPropagation();
        selectPackage($(this).data('package'));
    });

    // Keyboard support for card selection
    $('.dco-pkg-card').on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            selectPackage($(this).data('package'));
        }
    });

    // Init default selection
    selectPackage(selectedPackage);

    // ── Payment method labels ─────────────────────────────────────────
    var btnLabels = {
        card:            { en: 'Proceed to Secure Payment',      es: 'Proceder al Pago Seguro'         },
        us_bank_account: { en: 'Proceed to Bank Verification',   es: 'Proceder a Verificación Bancaria' }
    };
    var hintLabels = {
        card:            { en: 'Secure payment via Stripe',                              es: 'Pago seguro via Stripe'                                  },
        us_bank_account: { en: "You'll enter your bank details on Stripe's secure page", es: 'Ingresará sus datos bancarios en la página segura de Stripe' }
    };

    function applyMethodLabels(method) {
        var lang   = getLang();
        var bLabel = btnLabels[method]  || btnLabels.card;
        var hLabel = hintLabels[method] || hintLabels.card;

        $btnLabel.attr('data-en', bLabel.en).attr('data-es', bLabel.es).text(bLabel[lang]);
        $hintText.attr('data-en', hLabel.en).attr('data-es', hLabel.es).text(hLabel[lang]);
    }

    // ── Payment method card selection ─────────────────────────────────
    $('#dco-pay-method-grid .dco-pay-method-card').on('click', function () {
        var $card = $(this);
        selectedMethod = $card.data('method');

        $('#dco-pay-method-grid .dco-pay-method-card').removeClass('dco-pay-method-card--selected').attr('aria-pressed', 'false');
        $card.addClass('dco-pay-method-card--selected').attr('aria-pressed', 'true');

        if (selectedMethod === 'us_bank_account') {
            $achInfo.slideDown(200);
        } else {
            $achInfo.slideUp(150);
        }

        applyMethodLabels(selectedMethod);
        $payBtn.prop('disabled', false);
        $hint.hide();
    });

    // ── Pay button click ──────────────────────────────────────────────
    $payBtn.on('click', function () {
        if (!selectedMethod) {
            $hint.show();
            return;
        }

        var $btn = $(this);
        $btn.find('.dco-btn__text').hide();
        $btn.find('.dco-btn__spinner').show();
        $btn.prop('disabled', true);
        $('#dco-gate-error').hide().text('');

        $.ajax({
            url:  dcoData.ajaxurl,
            type: 'POST',
            data: {
                action:          'dco_create_stripe_session',
                nonce:           $btn.data('nonce'),
                application_id:  $btn.data('application-id'),
                token:           $btn.data('token'),
                payment_method:  selectedMethod,
                package_tier:    selectedPackage,
            },
            success: function (res) {
                if (res.success && res.data.checkout_url) {
                    window.location.href = res.data.checkout_url;
                } else {
                    var msg = (res.data && res.data.message) ? res.data.message : 'An error occurred. Please try again.';
                    $('#dco-gate-error').text(msg).show();
                    $btn.find('.dco-btn__text').show();
                    $btn.find('.dco-btn__spinner').hide();
                    $btn.prop('disabled', false);
                }
            },
            error: function () {
                $('#dco-gate-error').text('Connection error. Please try again.').show();
                $btn.find('.dco-btn__text').show();
                $btn.find('.dco-btn__spinner').hide();
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
