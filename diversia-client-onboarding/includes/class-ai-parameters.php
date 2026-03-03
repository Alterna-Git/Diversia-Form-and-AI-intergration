<?php
if (!defined('ABSPATH')) exit;

/**
 * DCO_AI_Parameters
 *
 * Renders the "AI Parameters Questionnaire" admin page.
 * Saves answers to wp_options under the key `dco_ai_questionnaire` and
 * auto-generates the `dco_qualification_criteria` text that is injected
 * into every OpenAI evaluation prompt.
 */
class DCO_AI_Parameters {

    const OPT_QUESTIONNAIRE = 'dco_ai_questionnaire';
    const NONCE_ACTION      = 'dco_save_ai_parameters';

    // Defaults used when no questionnaire has been saved yet
    private static function defaults(): array {
        return array(
            // Section 1 — Mission
            'mission_statement'           => '',
            'community_focus'             => array('Latino/Hispanic'),
            'value_proposition'           => '',

            // Section 2 — Trial Types
            'accepted_trial_phases'       => array('Phase I', 'Phase II', 'Phase III', 'Phase IV', 'Observational Study'),
            'rejected_trial_notes'        => '',

            // Section 3 — Population Requirements
            'latino_requirement'          => 'preferred',
            'latino_min_percent'          => 0,

            // Section 4 — Organization Eligibility
            'eligible_org_types'          => array(),
            'disqualify_individuals'      => true,
            'min_years_experience'        => 0,
            'require_prior_trials'        => 'preferred',

            // Section 5 — Budget (fixed at $500)
            'min_budget_usd'              => 500,
            'budget_action_below_min'     => 'disqualify',

            // Section 6 — Scoring Weights
            'weight_org_legitimacy'       => 25,
            'weight_trial_viability'      => 25,
            'weight_community_relevance'  => 25,
            'weight_budget'               => 25,
            'min_qualifying_score'        => 40,

            // Section 7 — Disqualification
            'auto_disqualify_below_budget'  => true,
            'auto_disqualify_individuals'   => true,
            'auto_disqualify_no_latino'     => false,
            'custom_disqualification_rules' => '',

            // Section 8 — AI Tone & Instructions
            'ai_tone'                     => 'empathetic',
            'custom_ai_instructions'      => '',
            'company_context'             => '',
        );
    }

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    public static function init(): void {
        add_action('admin_menu',             array(__CLASS__, 'add_submenu_page'));
        add_action('admin_post_' . self::NONCE_ACTION, array(__CLASS__, 'handle_save'));
        add_action('admin_enqueue_scripts',  array(__CLASS__, 'enqueue_admin_assets'));
    }

    public static function add_submenu_page(): void {
        add_submenu_page(
            'options-general.php',
            'AI Qualification Parameters',
            'AI Parameters',
            'manage_options',
            'dco-ai-parameters',
            array(__CLASS__, 'render_page')
        );
    }

    public static function enqueue_admin_assets(string $hook): void {
        if ($hook !== 'settings_page_dco-ai-parameters') {
            return;
        }
        wp_enqueue_style(
            'dco-admin',
            DCO_PLUGIN_URL . 'assets/css/dco-admin.css',
            array(),
            DCO_VERSION
        );
    }

    // -------------------------------------------------------------------------
    // Save Handler
    // -------------------------------------------------------------------------

    public static function handle_save(): void {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        check_admin_referer(self::NONCE_ACTION);

        $p = $_POST;

        $data = array(
            // Section 1
            'mission_statement'           => sanitize_textarea_field($p['mission_statement']          ?? ''),
            'community_focus'             => array_map('sanitize_text_field', (array) ($p['community_focus']           ?? array())),
            'value_proposition'           => sanitize_textarea_field($p['value_proposition']          ?? ''),

            // Section 2
            'accepted_trial_phases'       => array_map('sanitize_text_field', (array) ($p['accepted_trial_phases']    ?? array())),
            'rejected_trial_notes'        => sanitize_textarea_field($p['rejected_trial_notes']       ?? ''),

            // Section 3
            'latino_requirement'          => self::whitelist($p['latino_requirement'] ?? 'preferred', array('required', 'preferred', 'optional'), 'preferred'),
            'latino_min_percent'          => max(0, min(100, intval($p['latino_min_percent'] ?? 0))),

            // Section 4
            'eligible_org_types'          => array_map('sanitize_text_field', (array) ($p['eligible_org_types']       ?? array())),
            'disqualify_individuals'      => !empty($p['disqualify_individuals']),
            'min_years_experience'        => max(0, intval($p['min_years_experience']        ?? 0)),
            'require_prior_trials'        => self::whitelist($p['require_prior_trials'] ?? 'preferred', array('required', 'preferred', 'none'), 'preferred'),

            // Section 5 — min_budget_usd is fixed at $500, ignore submitted value
            'min_budget_usd'              => 500,
            'budget_action_below_min'     => self::whitelist($p['budget_action_below_min'] ?? 'disqualify', array('disqualify', 'reduce_score'), 'disqualify'),

            // Section 6
            'weight_org_legitimacy'       => max(0, min(100, intval($p['weight_org_legitimacy']      ?? 25))),
            'weight_trial_viability'      => max(0, min(100, intval($p['weight_trial_viability']     ?? 25))),
            'weight_community_relevance'  => max(0, min(100, intval($p['weight_community_relevance'] ?? 25))),
            'weight_budget'               => max(0, min(100, intval($p['weight_budget']              ?? 25))),
            'min_qualifying_score'        => max(0, min(100, intval($p['min_qualifying_score']       ?? 40))),

            // Section 7
            'auto_disqualify_below_budget'  => !empty($p['auto_disqualify_below_budget']),
            'auto_disqualify_individuals'   => !empty($p['auto_disqualify_individuals']),
            'auto_disqualify_no_latino'     => !empty($p['auto_disqualify_no_latino']),
            'custom_disqualification_rules' => sanitize_textarea_field($p['custom_disqualification_rules'] ?? ''),

            // Section 8
            'ai_tone'                     => self::whitelist($p['ai_tone'] ?? 'empathetic', array('professional', 'empathetic', 'direct', 'encouraging'), 'empathetic'),
            'custom_ai_instructions'      => sanitize_textarea_field($p['custom_ai_instructions'] ?? ''),
            'company_context'             => sanitize_textarea_field($p['company_context']        ?? ''),
        );

        update_option(self::OPT_QUESTIONNAIRE, $data);

        // Sync generated criteria into the main settings option used by OpenAI client
        $criteria = self::generate_criteria_text($data);
        update_option(DCO_Admin_Settings::OPT_QUALIFICATION_CRITERIA, $criteria);

        // Min budget is fixed at $500
        update_option(DCO_Admin_Settings::OPT_MIN_BUDGET_THRESHOLD, 500);

        wp_redirect(add_query_arg(array('page' => 'dco-ai-parameters', 'saved' => '1'), admin_url('options-general.php')));
        exit;
    }

    // -------------------------------------------------------------------------
    // Criteria Text Generator
    // -------------------------------------------------------------------------

    /**
     * Converts questionnaire answers into a structured plain-text block that is
     * injected verbatim into the OpenAI system prompt.
     */
    public static function generate_criteria_text(array $d): string {
        $lines = array();

        $lines[] = '=== DIVERSIA HEALTH — AI EVALUATION PARAMETERS ===';
        $lines[] = '';

        // Mission
        if (!empty($d['mission_statement'])) {
            $lines[] = 'COMPANY MISSION:';
            $lines[] = $d['mission_statement'];
            $lines[] = '';
        }
        if (!empty($d['value_proposition'])) {
            $lines[] = 'VALUE PROPOSITION TO CLIENTS:';
            $lines[] = $d['value_proposition'];
            $lines[] = '';
        }
        if (!empty($d['company_context'])) {
            $lines[] = 'ADDITIONAL COMPANY CONTEXT:';
            $lines[] = $d['company_context'];
            $lines[] = '';
        }

        // Community focus
        if (!empty($d['community_focus'])) {
            $lines[] = 'PRIMARY COMMUNITIES SERVED: ' . implode(', ', $d['community_focus']);
            $lines[] = '';
        }

        // Population requirement
        $latino_map = array('required' => 'REQUIRED', 'preferred' => 'STRONGLY PREFERRED', 'optional' => 'OPTIONAL');
        $latino_label = $latino_map[$d['latino_requirement']] ?? 'PREFERRED';
        $lines[] = 'LATINO/HISPANIC POPULATION FOCUS: ' . $latino_label;
        if (!empty($d['latino_min_percent'])) {
            $lines[] = 'Minimum Latino/Hispanic participant target: ' . $d['latino_min_percent'] . '%';
        }
        if ($d['auto_disqualify_no_latino'] ?? false) {
            $lines[] = 'DISQUALIFY if applicant does not plan to enroll Latino/Hispanic participants.';
        }
        $lines[] = '';

        // Trial phases
        if (!empty($d['accepted_trial_phases'])) {
            $lines[] = 'ACCEPTED TRIAL PHASES/TYPES: ' . implode(', ', $d['accepted_trial_phases']);
        }
        if (!empty($d['rejected_trial_notes'])) {
            $lines[] = 'TRIAL TYPES TO REJECT: ' . $d['rejected_trial_notes'];
        }
        $lines[] = '';

        // Organization eligibility
        if (!empty($d['eligible_org_types'])) {
            $lines[] = 'ELIGIBLE ORGANIZATION TYPES: ' . implode(', ', $d['eligible_org_types']);
        } else {
            $lines[] = 'ELIGIBLE ORGANIZATION TYPES: All legitimate research organizations';
        }
        if (!empty($d['disqualify_individuals'])) {
            $lines[] = 'DISQUALIFY individual researchers / sole practitioners without organizational backing.';
        }
        if (!empty($d['min_years_experience'])) {
            $lines[] = 'Minimum years in clinical research: ' . $d['min_years_experience'];
        }
        $prior_map = array('required' => 'REQUIRED', 'preferred' => 'strongly preferred', 'none' => 'not required');
        $lines[] = 'Prior clinical trial experience: ' . ($prior_map[$d['require_prior_trials']] ?? 'preferred');
        $lines[] = '';

        // Budget
        $lines[] = 'MINIMUM ACCEPTABLE BUDGET: $500';
        $budget_action = ($d['budget_action_below_min'] === 'disqualify')
            ? 'AUTOMATICALLY DISQUALIFY if budget is below minimum.'
            : 'Heavily penalize (score 0 on budget criterion) if budget is below minimum.';
        $lines[] = $budget_action;
        if (!empty($d['auto_disqualify_below_budget'])) {
            $lines[] = 'DISQUALIFY outright if applicant states budget below the minimum threshold.';
        }
        $lines[] = '';

        // Scoring
        $lines[] = 'SCORING WEIGHTS (must total 100):';
        $lines[] = '  - Organizational Legitimacy: ' . $d['weight_org_legitimacy'] . ' points';
        $lines[] = '  - Trial Viability: '           . $d['weight_trial_viability'] . ' points';
        $lines[] = '  - Community Relevance: '       . $d['weight_community_relevance'] . ' points';
        $lines[] = '  - Budget Seriousness: '        . $d['weight_budget'] . ' points';
        $lines[] = 'MINIMUM SCORE TO QUALIFY: ' . $d['min_qualifying_score'] . ' / 100';
        $lines[] = '';

        // Custom disqualification
        if (!empty($d['custom_disqualification_rules'])) {
            $lines[] = 'ADDITIONAL DISQUALIFICATION RULES:';
            $lines[] = $d['custom_disqualification_rules'];
            $lines[] = '';
        }

        // Tone
        $tone_map = array(
            'professional'  => 'Use a professional and objective tone.',
            'empathetic'    => 'Use a warm, empathetic tone that acknowledges the applicant\'s effort.',
            'direct'        => 'Be direct and concise in your reasoning.',
            'encouraging'   => 'Be encouraging and constructive, highlighting paths to improvement.',
        );
        $lines[] = 'COMMUNICATION TONE: ' . ($tone_map[$d['ai_tone']] ?? $tone_map['empathetic']);
        $lines[] = '';

        // Custom instructions
        if (!empty($d['custom_ai_instructions'])) {
            $lines[] = 'CUSTOM EVALUATION INSTRUCTIONS:';
            $lines[] = $d['custom_ai_instructions'];
            $lines[] = '';
        }

        $lines[] = '=== END OF DIVERSIA PARAMETERS ===';

        return implode("\n", $lines);
    }

    // -------------------------------------------------------------------------
    // Render Page
    // -------------------------------------------------------------------------

    public static function render_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        $saved   = !empty($_GET['saved']);
        $data    = array_merge(self::defaults(), (array) get_option(self::OPT_QUESTIONNAIRE, array()));
        $preview = self::generate_criteria_text($data);

        ?>
        <div class="wrap dco-aq-wrap">

            <div class="dco-aq-header">
                <div class="dco-aq-header__inner">
                    <div class="dco-aq-header__brand">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2e86ab" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        <div>
                            <h1>AI Qualification Parameters</h1>
                            <p>Define the criteria Diversia uses to train and guide the AI when evaluating client applications.</p>
                        </div>
                    </div>
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=dco-settings')); ?>" class="dco-aq-back-link">
                        ← Back to Settings
                    </a>
                </div>
            </div>

            <?php if ($saved): ?>
            <div class="notice notice-success dco-aq-notice">
                <p><strong>Parameters saved.</strong> The AI evaluation prompt has been updated and will apply to all new applications immediately.</p>
            </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="dco-aq-form">
                <input type="hidden" name="action" value="<?php echo esc_attr(self::NONCE_ACTION); ?>">
                <?php wp_nonce_field(self::NONCE_ACTION); ?>

                <div class="dco-aq-layout">

                    <!-- ====================================================
                         LEFT COLUMN — Questionnaire
                    ===================================================== -->
                    <div class="dco-aq-questions">

                        <!-- ─── SECTION 1: Mission ─── -->
                        <div class="dco-aq-section" id="section-mission">
                            <div class="dco-aq-section__head">
                                <span class="dco-aq-section__num">1</span>
                                <div>
                                    <h2>Company Mission & Focus</h2>
                                    <p>Help the AI understand who Diversia is and what it stands for so it can evaluate alignment with your mission.</p>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label for="aq-mission">What is Diversia Health's primary mission?</label>
                                <textarea id="aq-mission" name="mission_statement" rows="3" placeholder="e.g. Diversia Health connects clinical trial sponsors with underrepresented Latino/Hispanic communities to increase diversity in medical research..."><?php echo esc_textarea($data['mission_statement']); ?></textarea>
                                <span class="dco-aq-hint">The AI will reference this to assess applicant mission alignment.</span>
                            </div>

                            <div class="dco-aq-field">
                                <label>Primary communities Diversia serves</label>
                                <div class="dco-aq-checkboxes">
                                    <?php
                                    $communities = array('Latino/Hispanic', 'African American', 'Asian American', 'Indigenous/Native American', 'LGBTQ+', 'Elderly (65+)', 'Pediatric', 'Rural Communities', 'Uninsured/Underinsured');
                                    foreach ($communities as $c):
                                    ?>
                                    <label class="dco-aq-check">
                                        <input type="checkbox" name="community_focus[]" value="<?php echo esc_attr($c); ?>"
                                               <?php checked(in_array($c, (array) $data['community_focus'], true)); ?>>
                                        <?php echo esc_html($c); ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label for="aq-value-prop">What is Diversia's value proposition to clinical trial sponsors?</label>
                                <textarea id="aq-value-prop" name="value_proposition" rows="3" placeholder="e.g. We provide access to a verified network of 50,000+ Latino participants across 15 US states, with bilingual coordinators and culturally adapted trial protocols..."><?php echo esc_textarea($data['value_proposition']); ?></textarea>
                                <span class="dco-aq-hint">This helps the AI judge whether a sponsor's trial is a genuine fit for Diversia's network.</span>
                            </div>

                            <div class="dco-aq-field">
                                <label for="aq-company-context">Any additional context about Diversia's partnerships, capabilities, or restrictions?</label>
                                <textarea id="aq-company-context" name="company_context" rows="2" placeholder="e.g. We currently operate in FL, TX, CA, NY and PR. We do not work with tobacco or cannabis trials..."><?php echo esc_textarea($data['company_context']); ?></textarea>
                            </div>
                        </div>

                        <!-- ─── SECTION 2: Trial Types ─── -->
                        <div class="dco-aq-section" id="section-trials">
                            <div class="dco-aq-section__head">
                                <span class="dco-aq-section__num">2</span>
                                <div>
                                    <h2>Accepted Trial Types</h2>
                                    <p>Which categories of clinical trials is Diversia equipped to support?</p>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label>Which trial phases/types do you accept?</label>
                                <div class="dco-aq-checkboxes">
                                    <?php
                                    $phases = array('Phase I — Safety', 'Phase II — Efficacy', 'Phase III — Comparative', 'Phase IV — Post-market', 'Observational Study', 'Expanded Access', 'Pragmatic Trial');
                                    $phase_vals = array('Phase I', 'Phase II', 'Phase III', 'Phase IV', 'Observational Study', 'Expanded Access', 'Pragmatic Trial');
                                    foreach ($phases as $i => $label):
                                    $val = $phase_vals[$i];
                                    ?>
                                    <label class="dco-aq-check">
                                        <input type="checkbox" name="accepted_trial_phases[]" value="<?php echo esc_attr($val); ?>"
                                               <?php checked(in_array($val, (array) $data['accepted_trial_phases'], true)); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label for="aq-rejected-trial-notes">Any trial types Diversia will never accept?</label>
                                <textarea id="aq-rejected-trial-notes" name="rejected_trial_notes" rows="2" placeholder="e.g. No tobacco-related trials, no first-in-human trials without IRB approval letter, no cannabis studies..."><?php echo esc_textarea($data['rejected_trial_notes']); ?></textarea>
                            </div>
                        </div>

                        <!-- ─── SECTION 3: Population Requirements ─── -->
                        <div class="dco-aq-section" id="section-population">
                            <div class="dco-aq-section__head">
                                <span class="dco-aq-section__num">3</span>
                                <div>
                                    <h2>Target Population Requirements</h2>
                                    <p>How important is it that trials explicitly target Latino/Hispanic participants?</p>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label>Latino/Hispanic population inclusion</label>
                                <div class="dco-aq-radio-group">
                                    <?php
                                    $latino_opts = array(
                                        'required'  => array('label' => 'Required', 'desc' => 'Reject any trial that does not explicitly target Latino/Hispanic participants.'),
                                        'preferred' => array('label' => 'Strongly Preferred', 'desc' => 'Penalize but don\'t reject trials without Latino focus. Reward those that include it.'),
                                        'optional'  => array('label' => 'Optional', 'desc' => 'Any population is acceptable. Award bonus points for Latino inclusion.'),
                                    );
                                    foreach ($latino_opts as $val => $opt):
                                    ?>
                                    <label class="dco-aq-radio <?php echo $data['latino_requirement'] === $val ? 'dco-aq-radio--selected' : ''; ?>">
                                        <input type="radio" name="latino_requirement" value="<?php echo esc_attr($val); ?>"
                                               <?php checked($data['latino_requirement'], $val); ?>>
                                        <div>
                                            <strong><?php echo esc_html($opt['label']); ?></strong>
                                            <span><?php echo esc_html($opt['desc']); ?></span>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label for="aq-latino-min-percent">Minimum percentage of Latino/Hispanic participants applicant must target (0 = no minimum)</label>
                                <div class="dco-aq-number-wrap">
                                    <input type="number" id="aq-latino-min-percent" name="latino_min_percent"
                                           value="<?php echo esc_attr($data['latino_min_percent']); ?>"
                                           min="0" max="100" step="5">
                                    <span class="dco-aq-unit">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- ─── SECTION 4: Organization Eligibility ─── -->
                        <div class="dco-aq-section" id="section-org">
                            <div class="dco-aq-section__head">
                                <span class="dco-aq-section__num">4</span>
                                <div>
                                    <h2>Organization Eligibility</h2>
                                    <p>What types of organizations should the AI consider legitimate applicants?</p>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label>Eligible organization types <span class="dco-aq-optional">(leave all unchecked to allow any type)</span></label>
                                <div class="dco-aq-checkboxes">
                                    <?php
                                    $org_types = array('Pharmaceutical Company', 'Contract Research Organization (CRO)', 'Academic Institution', 'Hospital / Healthcare System', 'Biotechnology Company', 'Government Agency', 'Non-Profit / Foundation', 'Medical Device Company', 'Other');
                                    $org_vals  = array('Pharma', 'CRO', 'Academic Institution', 'Hospital/Healthcare System', 'Biotech', 'Government Agency', 'Non-Profit', 'Medical Device', 'Other');
                                    foreach ($org_types as $i => $label):
                                    $val = $org_vals[$i];
                                    ?>
                                    <label class="dco-aq-check">
                                        <input type="checkbox" name="eligible_org_types[]" value="<?php echo esc_attr($val); ?>"
                                               <?php checked(in_array($val, (array) $data['eligible_org_types'], true)); ?>>
                                        <?php echo esc_html($label); ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label class="dco-aq-toggle-label">
                                    <input type="checkbox" name="disqualify_individuals" value="1"
                                           <?php checked(!empty($data['disqualify_individuals'])); ?>>
                                    <span>Automatically disqualify individual researchers / sole practitioners without organizational backing</span>
                                </label>
                            </div>

                            <div class="dco-aq-field dco-aq-field--row">
                                <div>
                                    <label for="aq-min-years">Minimum years in clinical research (0 = no minimum)</label>
                                    <div class="dco-aq-number-wrap">
                                        <input type="number" id="aq-min-years" name="min_years_experience"
                                               value="<?php echo esc_attr($data['min_years_experience']); ?>"
                                               min="0" max="50">
                                        <span class="dco-aq-unit">years</span>
                                    </div>
                                </div>
                                <div>
                                    <label>Prior clinical trial experience</label>
                                    <select name="require_prior_trials" class="dco-aq-select">
                                        <option value="required"  <?php selected($data['require_prior_trials'], 'required');  ?>>Required</option>
                                        <option value="preferred" <?php selected($data['require_prior_trials'], 'preferred'); ?>>Strongly Preferred</option>
                                        <option value="none"      <?php selected($data['require_prior_trials'], 'none');      ?>>Not Required</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ─── SECTION 5: Budget Parameters ─── -->
                        <div class="dco-aq-section" id="section-budget">
                            <div class="dco-aq-section__head">
                                <span class="dco-aq-section__num">5</span>
                                <div>
                                    <h2>Budget Parameters</h2>
                                    <p>Set the minimum acceptable budget and how the AI should respond to low-budget applications.</p>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label>Minimum budget to be considered a serious applicant (USD)</label>
                                <div class="dco-aq-number-wrap">
                                    <span class="dco-aq-prefix">$</span>
                                    <strong style="font-size:16px;line-height:1;">500</strong>
                                    <span style="margin-left:8px;color:#888;font-size:13px;">(fixed — not editable)</span>
                                </div>
                                <input type="hidden" name="min_budget_usd" value="500">
                            </div>

                            <div class="dco-aq-field">
                                <label>When an applicant's budget is below the minimum:</label>
                                <div class="dco-aq-radio-group">
                                    <label class="dco-aq-radio <?php echo $data['budget_action_below_min'] === 'disqualify' ? 'dco-aq-radio--selected' : ''; ?>">
                                        <input type="radio" name="budget_action_below_min" value="disqualify"
                                               <?php checked($data['budget_action_below_min'], 'disqualify'); ?>>
                                        <div>
                                            <strong>Automatically Disqualify</strong>
                                            <span>Reject the application regardless of other scores.</span>
                                        </div>
                                    </label>
                                    <label class="dco-aq-radio <?php echo $data['budget_action_below_min'] === 'reduce_score' ? 'dco-aq-radio--selected' : ''; ?>">
                                        <input type="radio" name="budget_action_below_min" value="reduce_score"
                                               <?php checked($data['budget_action_below_min'], 'reduce_score'); ?>>
                                        <div>
                                            <strong>Score 0 on Budget Criterion</strong>
                                            <span>Award 0 points for budget but allow other criteria to potentially qualify the application.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- ─── SECTION 6: Scoring Weights ─── -->
                        <div class="dco-aq-section" id="section-scoring">
                            <div class="dco-aq-section__head">
                                <span class="dco-aq-section__num">6</span>
                                <div>
                                    <h2>Scoring Weights</h2>
                                    <p>The four criteria must total 100 points. Drag to emphasize what matters most to Diversia.</p>
                                </div>
                            </div>

                            <?php
                            $weights = array(
                                'weight_org_legitimacy'      => array('label' => 'Organizational Legitimacy', 'desc' => 'Is the applying organization credible, established, and professionally run?'),
                                'weight_trial_viability'     => array('label' => 'Trial Viability',           'desc' => 'Is the trial type realistic, coherent, and achievable given the stated resources?'),
                                'weight_community_relevance' => array('label' => 'Community Relevance',       'desc' => 'Does the trial align with Diversia\'s mission to serve underrepresented populations?'),
                                'weight_budget'              => array('label' => 'Budget Seriousness',        'desc' => 'Does the stated budget reflect genuine commitment and ability to execute the trial?'),
                            );
                            $total = array_sum(array_map(fn($k) => intval($data[$k] ?? 0), array_keys($weights)));
                            ?>

                            <div class="dco-aq-weights" id="dco-aq-weights">
                                <?php foreach ($weights as $key => $info): ?>
                                <div class="dco-aq-weight-row">
                                    <div class="dco-aq-weight-label">
                                        <strong><?php echo esc_html($info['label']); ?></strong>
                                        <span><?php echo esc_html($info['desc']); ?></span>
                                    </div>
                                    <div class="dco-aq-weight-control">
                                        <input type="range" name="<?php echo esc_attr($key); ?>"
                                               id="<?php echo esc_attr($key); ?>"
                                               value="<?php echo esc_attr($data[$key] ?? 25); ?>"
                                               min="0" max="100" step="5"
                                               class="dco-aq-range" data-weight>
                                        <span class="dco-aq-range-val" id="val-<?php echo esc_attr($key); ?>"><?php echo esc_html($data[$key] ?? 25); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <div class="dco-aq-weight-total">
                                    Total: <span id="dco-weight-total" class="<?php echo $total !== 100 ? 'dco-weight-total--warn' : ''; ?>"><?php echo esc_html($total); ?></span> / 100
                                    <span class="dco-weight-total__note" id="dco-weight-note"><?php echo $total !== 100 ? '⚠ Weights should total 100' : '✓ Balanced'; ?></span>
                                </div>
                            </div>

                            <div class="dco-aq-field" style="margin-top:20px;">
                                <label for="aq-min-score">Minimum total score to qualify (out of 100)</label>
                                <div class="dco-aq-number-wrap">
                                    <input type="number" id="aq-min-score" name="min_qualifying_score"
                                           value="<?php echo esc_attr($data['min_qualifying_score']); ?>"
                                           min="0" max="100" step="5">
                                    <span class="dco-aq-unit">/ 100</span>
                                </div>
                                <span class="dco-aq-hint">Applicants scoring below this threshold will be rejected. Recommended: 40–60.</span>
                            </div>
                        </div>

                        <!-- ─── SECTION 7: Disqualification Rules ─── -->
                        <div class="dco-aq-section" id="section-disqualify">
                            <div class="dco-aq-section__head">
                                <span class="dco-aq-section__num">7</span>
                                <div>
                                    <h2>Automatic Disqualification Rules</h2>
                                    <p>These conditions trigger an immediate rejection regardless of score.</p>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <div class="dco-aq-disqualify-list">
                                    <label class="dco-aq-toggle-label">
                                        <input type="checkbox" name="auto_disqualify_below_budget" value="1"
                                               <?php checked(!empty($data['auto_disqualify_below_budget'])); ?>>
                                        <span>Reject if stated budget is below the minimum threshold</span>
                                    </label>
                                    <label class="dco-aq-toggle-label">
                                        <input type="checkbox" name="auto_disqualify_individuals" value="1"
                                               <?php checked(!empty($data['auto_disqualify_individuals'])); ?>>
                                        <span>Reject individual researchers without organizational backing</span>
                                    </label>
                                    <label class="dco-aq-toggle-label">
                                        <input type="checkbox" name="auto_disqualify_no_latino" value="1"
                                               <?php checked(!empty($data['auto_disqualify_no_latino'])); ?>>
                                        <span>Reject if applicant has no plan to enroll Latino/Hispanic participants</span>
                                    </label>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label for="aq-custom-disqualify">Additional disqualification conditions</label>
                                <textarea id="aq-custom-disqualify" name="custom_disqualification_rules" rows="3"
                                          placeholder="e.g. Reject if trial involves tobacco or cannabis products. Reject if applicant has unresolved FDA warning letters. Reject if the trial has no IRB approval or pending review..."><?php echo esc_textarea($data['custom_disqualification_rules']); ?></textarea>
                                <span class="dco-aq-hint">Write in plain English. The AI will apply these as absolute rejection criteria.</span>
                            </div>
                        </div>

                        <!-- ─── SECTION 8: AI Tone & Instructions ─── -->
                        <div class="dco-aq-section" id="section-ai">
                            <div class="dco-aq-section__head">
                                <span class="dco-aq-section__num">8</span>
                                <div>
                                    <h2>AI Tone &amp; Custom Instructions</h2>
                                    <p>How should the AI communicate its decision to applicants?</p>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label>Communication tone for evaluation explanations</label>
                                <div class="dco-aq-tone-grid">
                                    <?php
                                    $tones = array(
                                        'professional'  => array('icon' => '🏢', 'label' => 'Professional',   'desc' => 'Formal, objective, business-like'),
                                        'empathetic'    => array('icon' => '🤝', 'label' => 'Empathetic',     'desc' => 'Warm, acknowledges effort, constructive'),
                                        'direct'        => array('icon' => '⚡', 'label' => 'Direct',         'desc' => 'Concise, clear, no fluff'),
                                        'encouraging'   => array('icon' => '🌱', 'label' => 'Encouraging',    'desc' => 'Positive framing, paths to improvement'),
                                    );
                                    foreach ($tones as $val => $t):
                                    ?>
                                    <label class="dco-aq-tone-card <?php echo $data['ai_tone'] === $val ? 'dco-aq-tone-card--selected' : ''; ?>">
                                        <input type="radio" name="ai_tone" value="<?php echo esc_attr($val); ?>"
                                               <?php checked($data['ai_tone'], $val); ?>>
                                        <span class="dco-aq-tone-icon"><?php echo $t['icon']; ?></span>
                                        <strong><?php echo esc_html($t['label']); ?></strong>
                                        <span><?php echo esc_html($t['desc']); ?></span>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="dco-aq-field">
                                <label for="aq-custom-instructions">Custom evaluation instructions for the AI</label>
                                <textarea id="aq-custom-instructions" name="custom_ai_instructions" rows="4"
                                          placeholder="e.g. Always mention Diversia's bilingual support team as a resource for non-English-speaking applicants. When rejecting due to budget, suggest that applicants consider partnering with an academic institution to pool resources. If the trial targets pediatric populations, award 5 bonus community relevance points..."><?php echo esc_textarea($data['custom_ai_instructions']); ?></textarea>
                                <span class="dco-aq-hint">Specific instructions the AI must follow during every evaluation. These are applied verbatim.</span>
                            </div>
                        </div>

                        <div class="dco-aq-submit-bar">
                            <button type="submit" class="button button-primary dco-aq-save-btn">
                                Save Parameters &amp; Update AI Prompt
                            </button>
                            <span class="dco-aq-save-note">Changes apply immediately to all new applications.</span>
                        </div>

                    </div><!-- /.dco-aq-questions -->

                    <!-- ====================================================
                         RIGHT COLUMN — Live Preview
                    ===================================================== -->
                    <div class="dco-aq-sidebar">
                        <div class="dco-aq-preview">
                            <div class="dco-aq-preview__header">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                Generated AI Instructions Preview
                            </div>
                            <div class="dco-aq-preview__body">
                                <pre id="dco-aq-preview-text"><?php echo esc_html($preview); ?></pre>
                            </div>
                            <div class="dco-aq-preview__footer">
                                This text is injected verbatim into every AI evaluation prompt. Save the form to update it.
                            </div>
                        </div>

                        <div class="dco-aq-nav">
                            <p><strong>Jump to section</strong></p>
                            <a href="#section-mission">1. Mission &amp; Focus</a>
                            <a href="#section-trials">2. Trial Types</a>
                            <a href="#section-population">3. Population</a>
                            <a href="#section-org">4. Organization</a>
                            <a href="#section-budget">5. Budget</a>
                            <a href="#section-scoring">6. Scoring</a>
                            <a href="#section-disqualify">7. Disqualification</a>
                            <a href="#section-ai">8. AI Tone</a>
                        </div>
                    </div>

                </div><!-- /.dco-aq-layout -->
            </form>

        </div><!-- /.wrap -->

        <script>
        (function() {
            // Radio card selection highlight
            document.querySelectorAll('.dco-aq-radio input, .dco-aq-tone-card input').forEach(function(input) {
                input.addEventListener('change', function() {
                    var group = this.closest('.dco-aq-radio-group, .dco-aq-tone-grid');
                    if (group) {
                        group.querySelectorAll('.dco-aq-radio, .dco-aq-tone-card').forEach(function(el) {
                            el.classList.remove('dco-aq-radio--selected', 'dco-aq-tone-card--selected');
                        });
                        this.closest('.dco-aq-radio, .dco-aq-tone-card').classList.add(
                            this.closest('.dco-aq-radio') ? 'dco-aq-radio--selected' : 'dco-aq-tone-card--selected'
                        );
                    }
                });
            });

            // Weight sliders — live total counter
            var rangeInputs = document.querySelectorAll('input[data-weight]');
            function updateTotal() {
                var total = 0;
                rangeInputs.forEach(function(r) {
                    total += parseInt(r.value, 10) || 0;
                    var valEl = document.getElementById('val-' + r.name);
                    if (valEl) valEl.textContent = r.value;
                });
                var totalEl = document.getElementById('dco-weight-total');
                var noteEl  = document.getElementById('dco-weight-note');
                if (totalEl) {
                    totalEl.textContent = total;
                    if (total === 100) {
                        totalEl.className = '';
                        if (noteEl) noteEl.textContent = '✓ Balanced';
                    } else {
                        totalEl.className = 'dco-weight-total--warn';
                        if (noteEl) noteEl.textContent = '⚠ Weights should total 100';
                    }
                }
            }
            rangeInputs.forEach(function(r) { r.addEventListener('input', updateTotal); });
            updateTotal();
        })();
        </script>
        <?php
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private static function whitelist(string $value, array $allowed, string $default): string {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
