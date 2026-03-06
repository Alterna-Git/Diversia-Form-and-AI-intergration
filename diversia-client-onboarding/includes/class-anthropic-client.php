<?php
if (!defined('ABSPATH')) exit;

class DCO_Anthropic_Client {

    private string $api_key;
    private string $model;
    private string $api_url = 'https://api.anthropic.com/v1/messages';

    public function __construct() {
        $this->api_key = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_ANTHROPIC_API_KEY, '');
        $this->model   = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_ANTHROPIC_MODEL, 'claude-sonnet-4-6');
    }

    /**
     * Evaluates a client application using 3 independent Claude passes.
     * Returns majority-vote qualified/rejected with an averaged score and
     * deduplicated suggestions from all three passes.
     */
    public function evaluate_application(array $application_data): array {
        if (empty($this->api_key)) {
            error_log('[DCO] Anthropic API key not configured.');
            return $this->fallback_rejection('Anthropic API key not configured.');
        }

        $results = array();
        for ($pass = 1; $pass <= 1; $pass++) { // single pass — keeps total time well under server max_execution_time
            try {
                $result    = $this->evaluate_single($application_data);
                $results[] = $result;
                error_log(sprintf(
                    '[DCO] Evaluation pass %d/3 complete. score=%.1f qualified=%s',
                    $pass,
                    $result['score'],
                    $result['qualified'] ? 'yes' : 'no'
                ));
            } catch (Exception $e) {
                error_log("[DCO] Anthropic error on pass {$pass}/3: " . $e->getMessage());
            }
        }

        if (empty($results)) {
            return $this->fallback_rejection('All 3 evaluation passes failed.');
        }

        return $this->aggregate_results($results);
    }

    /**
     * Sends a minimal request to confirm the configured key and model respond.
     */
    public function test_connection(): array {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => 'Anthropic API key is not configured.',
            );
        }

        try {
            $this->call_api(
                'You are a connection test. Respond with a short JSON object: {"status":"ok"}',
                'Return exactly {"status":"ok"}',
                20
            );

            return array(
                'success' => true,
                'message' => 'Anthropic connection successful. The saved API key and model responded correctly.',
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Anthropic test failed: ' . $e->getMessage(),
            );
        }
    }

    // ---------------------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------------------

    private function evaluate_single(array $application_data): array {
        $raw_body = $this->call_api(
            $this->build_system_prompt(),
            $this->build_user_message($application_data),
            2000
        );
        return $this->parse_ai_response($raw_body);
    }

    private function aggregate_results(array $results): array {
        $qualified_count = 0;
        $total_score     = 0.0;
        $all_suggestions = array();

        foreach ($results as $r) {
            if (!empty($r['qualified'])) {
                $qualified_count++;
            }
            $total_score += (float) $r['score'];
            $all_suggestions = array_merge($all_suggestions, $r['suggestions'] ?? array());
        }

        $count     = count($results);
        $qualified = $qualified_count >= (int) ceil($count / 2);
        $avg_score = round($total_score / $count, 1);

        usort($results, function($a, $b) use ($avg_score) {
            return abs($a['score'] - $avg_score) <=> abs($b['score'] - $avg_score);
        });
        $best = $results[0];

        $seen        = array();
        $suggestions = array();
        foreach ($all_suggestions as $s) {
            $field = $s['field'] ?? 'additional_notes';
            if (!isset($seen[$field]) && count($suggestions) < 3) {
                $seen[$field]  = true;
                $suggestions[] = $s;
            }
        }

        $scores_str = implode(' / ', array_map(
            function($r) { return (string) round($r['score']); },
            $results
        ));

        return array(
            'qualified'    => $qualified,
            'score'        => $avg_score,
            'reasoning_es' => $best['reasoning_es'],
            'reasoning_en' => $best['reasoning_en'],
            'suggestions'  => $suggestions,
            'raw'          => 'Consensus of ' . $count . ' passes (' . $scores_str . '). ' . $best['raw'],
        );
    }

    private function build_system_prompt(): string {
        $min_budget = '$500';

        $custom_criteria  = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_QUALIFICATION_CRITERIA, '');
        $allowed_org_raw  = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_ALLOWED_ORG_TYPES, '[]');
        $allowed_org_list = json_decode($allowed_org_raw, true);
        $org_text         = (!empty($allowed_org_list) && is_array($allowed_org_list))
            ? implode(', ', $allowed_org_list)
            : 'Pharma, CRO, Academic Institution, Hospital/Healthcare System, Biotech, Government Agency, Other';

        $timeline_raw    = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_ENROLLMENT_TIMELINE, '[]');
        $timeline_ranges = json_decode($timeline_raw, true);
        $timeline_lines  = array();
        if (!empty($timeline_ranges) && is_array($timeline_ranges)) {
            foreach ($timeline_ranges as $r) {
                $min    = intval($r['min'] ?? 0);
                $max    = intval($r['max'] ?? 0);
                $months = intval($r['months'] ?? 0);
                if ($months < 1) continue;
                $range = ($max === 0)
                    ? number_format($min) . '+ participants'
                    : number_format($min) . '–' . number_format($max) . ' participants';
                $timeline_lines[] = "   - {$range}: minimum ~{$months} months";
            }
        }
        $timeline_text = !empty($timeline_lines)
            ? "EXPECTED TIMELINES BY ENROLLMENT GOAL:\n" . implode("\n", $timeline_lines) . "\n   Flag if the applicant's stated timeline is significantly shorter than expected."
            : '';

        return <<<PROMPT
You are a qualification specialist for Diversia Health, a clinical trial recruitment platform
specializing in connecting Latino communities — especially Puerto Rican populations — with
clinical research opportunities.

Your task is to evaluate whether an organization is a legitimate and viable client for our
recruitment services based on the onboarding application they have submitted.

LEADENGINE CROSS-REFERENCE (Alterna Agency benchmarks for Latino Meta Ads recruitment):
Use these data points to verify whether the applicant's budget is realistically aligned with
their enrollment goal. Extract the budget as a number from the application.

Formula: leads = floor(budget / CPL_realistic); net_rate = ps×bk×at×en; projected = round(leads × net_rate)
budget_needed = ceil(enrollment_goal / net_rate) × CPL

CPL (Cost-Per-Lead, realistic/mid scenario, Latino Meta Ads):
- Obesity $28 | Prediabetes $35 | Type 2 Diabetes $38 | Metabolic Syndrome $48 | Cholesterol $32
- Hypertension $32 | CVD $55 | Heart Failure $68 | Afib $65
- Asthma/COPD $35 | Sleep Apnea $40
- Anxiety $25 | Depression $25 | ADHD $32
- Alzheimer's $85 | Parkinson's $90 | Migraine $28 | Cognitive Impairment $60
- Lupus $95 | Arthritis $32 | Autoimmune(other) $55 | Oncology $70 | Rare Cancer $120
- CKD $52 | Liver Disease $58 | Chronic Pain $28 | GERD $28 | Osteoporosis $38
- Diabetic Neuropathy $52 | Glaucoma $42 | HIV/AIDS $55 | Rare Genetic $140 | Rare Pediatric $150
- Default for unlisted conditions: $45

NET ENROLLMENT RATES (ps×bk×at×en) for Latino populations:
- Obesity 22% | Prediabetes 12% | T2D 17% | MetSyn 9% | Cholesterol 19%
- Hypertension 19% | CVD 8% | Heart Failure 5% | Afib 7%
- Asthma 17% | Sleep Apnea 11% | Anxiety 22% | Depression 18% | ADHD 13%
- Alzheimer's 4% | Parkinson's 5% | Migraine 19% | Cognitive Imp 4%
- Lupus 6% | Arthritis 15% | Autoimmune 8% | Oncology 7% | Rare Cancer 3%
- CKD 11% | Liver 9% | Chronic Pain 19% | GERD 17% | Osteoporosis 13%
- Diabetic Neuropathy 10% | Glaucoma 12% | HIV 10% | Rare Genetic 2% | Rare Pediatric 1%
- Default: 15%

RARE DISEASES (do NOT penalize lower enrollment goals 10–75):
Alzheimer's, Parkinson's, Lupus, Rare Cancer, Rare Genetic Disorders, Rare Pediatric Conditions.

HIGH SCREEN-FAILURE (flag in suggestions if applicable):
Alzheimer's 50% | Rare Cancer 48% | Rare Genetic 52% | Rare Pediatric 55% | Heart Failure 40% | Oncology 42%

EVALUATION CRITERIA (total: 100 points):

1. ORGANIZATIONAL LEGITIMACY (0–25 points)
   - Allowed organization types: {$org_text}
   - Is the organization type plausible for running clinical trials?
   - Is the contact information complete and professional?
   - Score 0 and DISQUALIFY if organization type is Personal or Individual.

2. TRIAL VIABILITY (0–25 points)
   - Is the trial type described coherently?
   - Use LeadEngine to verify: does the budget project enough enrolled patients to meet the enrollment goal?
   - Score 0 and DISQUALIFY if no coherent trial type is stated.
   {$timeline_text}

3. LATINO COMMUNITY RELEVANCE (0–25 points)
   - Does the target population include or specifically address Latino/Hispanic communities?
   - Is there awareness of the cultural and linguistic needs of this community?
   - Custom criteria from administrator: {$custom_criteria}

4. BUDGET SERIOUSNESS (0–25 points)
   - Minimum acceptable budget: {$min_budget}
   - Use LeadEngine CPL to calculate projected leads and enrolled patients.
   - Score 0 and DISQUALIFY if budget is below minimum or insufficient to generate even 20% of enrollment goal.
   - Deduct points proportionally if projected enrollment is below goal.

DISQUALIFICATION CONDITIONS — any single condition triggers an automatic rejection:
   - Budget below minimum threshold ({$min_budget})
   - Organization type is Personal or Individual
   - No coherent trial type stated
   - Application contains spam, gibberish, or clearly fictitious information
   - Total score below 40 out of 100

OUTPUT RULES:
- Respond ONLY with a valid JSON object. Do not include any text outside the JSON.
- reasoning_es and reasoning_en should be 2–4 sentences, professional and respectful.
- If rejecting, include SPECIFIC NUMBERS in reasoning: budget provided, CPL used, leads projected, enrolled projected, budget needed.
- If rejecting, populate "suggestions" with 1–3 specific, actionable items with real numbers.
  Each suggestion MUST have exactly these 5 fields:
    "field"         — one of: estimated_budget, trial_type, target_population, organization_type,
                      enrollment_goal, timeline_months, additional_notes, organization_website
    "issue_en"      — short phrase describing the problem (English)
    "issue_es"      — short phrase describing the problem (Spanish)
    "suggestion_en" — specific, actionable fix with real numbers (English)
    "suggestion_es" — specific, actionable fix with real numbers (Spanish)
- If qualifying, set "suggestions" to an empty array [].

JSON FORMAT (rejection example):
{
  "qualified": false,
  "score": 42.0,
  "reasoning_es": "Su presupuesto de $2,000 proyecta solo 57 leads a un CPL de $35, resultando en 7 participantes inscritos — muy por debajo de su meta de 150.",
  "reasoning_en": "Your $2,000 budget projects only 57 leads at a $35 CPL, yielding approximately 7 enrolled participants — well below your goal of 150.",
  "disqualification_reason": "Budget insufficient to meet enrollment goal",
  "suggestions": [
    {
      "field": "estimated_budget",
      "issue_en": "Budget too low to meet enrollment goal",
      "issue_es": "Presupuesto insuficiente para la meta de participantes",
      "suggestion_en": "Increase budget to at least $18,750 to project 150 enrolled participants at $35 CPL with a 22% net enrollment rate.",
      "suggestion_es": "Aumente el presupuesto a al menos $18,750 para proyectar 150 participantes inscritos con un CPL de $35 y tasa neta del 22%."
    }
  ],
  "criteria_scores": {
    "organizational_legitimacy": 20,
    "trial_viability": 8,
    "latino_community_relevance": 14,
    "budget_seriousness": 0
  }
}

JSON FORMAT (qualified example):
{
  "qualified": true,
  "score": 78.5,
  "reasoning_es": "Su organización presenta un perfil sólido con un presupuesto adecuado y un enfoque claro en la comunidad latina.",
  "reasoning_en": "Your organization presents a strong profile with an adequate budget and clear focus on the Latino community.",
  "disqualification_reason": null,
  "suggestions": [],
  "criteria_scores": {
    "organizational_legitimacy": 22,
    "trial_viability": 20,
    "latino_community_relevance": 18,
    "budget_seriousness": 18.5
  }
}
PROMPT;
    }

    private function build_user_message(array $data): string {
        $population = is_array($data['target_population'])
            ? implode(', ', array_map('sanitize_text_field', $data['target_population']))
            : sanitize_textarea_field($data['target_population'] ?? '');

        $country = wp_strip_all_tags($data['campaign_country'] ?? '');
        $regions = $data['campaign_regions'] ?? array();
        $location_line = $country
            ? ($country . ((!empty($regions)) ? ' — ' . implode(', ', array_map('wp_strip_all_tags', (array) $regions)) : ' (all regions)'))
            : 'Not specified';

        $msg = "APPLICANT INFORMATION:\n\n"
            . "Company/Organization: " . wp_strip_all_tags($data['company_name'] ?? '') . "\n"
            . "Organization Type: "    . wp_strip_all_tags($data['organization_type'] ?? '') . "\n"
            . "Website: "              . wp_strip_all_tags($data['organization_website'] ?? 'Not provided') . "\n\n"
            . "TRIAL DETAILS:\n"
            . "Trial Type: "           . wp_strip_all_tags($data['trial_type'] ?? '') . "\n"
            . "Target Population: "    . wp_strip_all_tags($population) . "\n"
            . "Campaign Location: "    . $location_line . "\n"
            . "Enrollment Goal: "      . intval($data['enrollment_goal'] ?? 0) . " participants\n"
            . "Estimated Timeline: "   . wp_strip_all_tags(!empty($data['timeline_label']) ? $data['timeline_label'] : intval($data['timeline_months'] ?? 0) . ' months') . "\n"
            . "Estimated Budget: "     . wp_strip_all_tags($data['estimated_budget'] ?? '') . "\n\n"
            . "ADDITIONAL NOTES:\n"
            . wp_strip_all_tags(sanitize_textarea_field($data['additional_notes'] ?? 'None provided'));

        // Append pre-calculated LeadEngine analysis when available.
        // These numbers are authoritative — use them instead of re-calculating from CPL tables.
        $le = $data['lead_engine_analysis'] ?? null;
        if (!empty($le) && is_string($le)) {
            $msg .= "\n\n" . $le;
        }

        $msg .= "\n\nPlease evaluate this application and respond with a JSON object only.";

        return $msg;
    }

    private function call_api(string $system_prompt, string $user_message, int $max_tokens = 2000): string {
        $response = wp_remote_post($this->api_url, array(
            'timeout' => 55, // keep under 60s in case max_execution_time hasn't been increased yet
            'headers' => array(
                'x-api-key'         => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'model'      => $this->model,
                'max_tokens' => $max_tokens,
                'system'     => $system_prompt,
                'messages'   => array(
                    array('role' => 'user', 'content' => $user_message),
                ),
            )),
        ));

        if (is_wp_error($response)) {
            throw new Exception('HTTP error: ' . $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            $err = json_decode($body, true);
            $msg = isset($err['error']['message']) ? $err['error']['message'] : "HTTP {$code}";
            throw new Exception('Anthropic API error: ' . $msg);
        }

        $parsed = json_decode($body, true);
        if (!isset($parsed['content'][0]['text'])) {
            throw new Exception('Unexpected Anthropic response structure.');
        }

        return $parsed['content'][0]['text'];
    }

    private function parse_ai_response(string $content): array {
        $data = json_decode($content, true);

        if (null === $data) {
            if (preg_match('/\{[\s\S]*\}/U', $content, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (null === $data) {
            error_log('[DCO] Failed to parse Anthropic JSON response: ' . $content);
            return $this->fallback_rejection('Could not parse AI response.');
        }

        $raw_suggestions = is_array($data['suggestions'] ?? null) ? $data['suggestions'] : array();
        $suggestions = array();
        $allowed_fields = array(
            'estimated_budget', 'trial_type', 'target_population', 'organization_type',
            'enrollment_goal', 'timeline_months', 'additional_notes', 'organization_website',
        );
        foreach (array_slice($raw_suggestions, 0, 3) as $s) {
            if (!is_array($s)) continue;
            $field = sanitize_key($s['field'] ?? '');
            if (!in_array($field, $allowed_fields, true)) {
                $field = 'additional_notes';
            }
            $suggestions[] = array(
                'field'         => $field,
                'issue_en'      => sanitize_text_field($s['issue_en'] ?? ''),
                'issue_es'      => sanitize_text_field($s['issue_es'] ?? ''),
                'suggestion_en' => sanitize_textarea_field($s['suggestion_en'] ?? ''),
                'suggestion_es' => sanitize_textarea_field($s['suggestion_es'] ?? ''),
            );
        }

        return array(
            'qualified'    => (bool) ($data['qualified'] ?? false),
            'score'        => (float) ($data['score'] ?? 0),
            'reasoning_es' => sanitize_textarea_field($data['reasoning_es'] ?? 'Evaluación no disponible.'),
            'reasoning_en' => sanitize_textarea_field($data['reasoning_en'] ?? 'Evaluation not available.'),
            'suggestions'  => $suggestions,
            'raw'          => $content,
        );
    }

    private function fallback_rejection(string $reason): array {
        return array(
            'qualified'    => false,
            'score'        => 0.0,
            'reasoning_es' => 'No pudimos completar la evaluación en este momento. Por favor contáctenos.',
            'reasoning_en' => 'We could not complete the evaluation at this time. Please contact us.',
            'suggestions'  => array(),
            'raw'          => $reason,
        );
    }
}
