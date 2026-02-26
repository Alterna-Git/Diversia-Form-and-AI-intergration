<?php
if (!defined('ABSPATH')) exit;

class DCO_OpenAI_Client {

    private string $api_key;
    private string $model;
    private string $api_url = 'https://api.openai.com/v1/chat/completions';

    public function __construct() {
        $this->api_key = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_OPENAI_API_KEY, '');
        $this->model   = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_OPENAI_MODEL, 'gpt-4o');
    }

    /**
     * Evaluates a client application using OpenAI GPT-4o.
     *
     * @param array $application_data  Normalized application fields.
     * @return array {
     *   'qualified'      => bool,
     *   'score'          => float,
     *   'reasoning_es'   => string,
     *   'reasoning_en'   => string,
     *   'raw'            => string,
     * }
     */
    public function evaluate_application(array $application_data): array {
        if (empty($this->api_key)) {
            error_log('[DCO] OpenAI API key not configured.');
            return $this->fallback_rejection('OpenAI API key not configured.');
        }

        $messages = array(
            array('role' => 'system', 'content' => $this->build_system_prompt()),
            array('role' => 'user',   'content' => $this->build_user_message($application_data)),
        );

        try {
            $raw_body = $this->call_api($messages);
            return $this->parse_ai_response($raw_body);
        } catch (Exception $e) {
            error_log('[DCO] OpenAI error: ' . $e->getMessage());
            return $this->fallback_rejection('AI evaluation unavailable: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------------------

    private function build_system_prompt(): string {
        // Budget: stored as integer, format for display
        $min_budget_raw   = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_MIN_BUDGET_THRESHOLD, 10000);
        $min_budget       = is_numeric($min_budget_raw)
            ? '$' . number_format((int) $min_budget_raw)
            : sanitize_text_field($min_budget_raw);

        $custom_criteria  = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_QUALIFICATION_CRITERIA, '');
        $allowed_org_raw  = DCO_Admin_Settings::get_option(DCO_Admin_Settings::OPT_ALLOWED_ORG_TYPES, '[]');
        $allowed_org_list = json_decode($allowed_org_raw, true);
        $org_text         = (!empty($allowed_org_list) && is_array($allowed_org_list))
            ? implode(', ', $allowed_org_list)
            : 'Pharma, CRO, Academic Institution, Hospital/Healthcare System, Biotech, Government Agency, Other';

        // Build timeline guidance from the enrollment-timeline setting
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

EVALUATION CRITERIA (total: 100 points):

1. ORGANIZATIONAL LEGITIMACY (0–25 points)
   - Allowed organization types: {$org_text}
   - Is the organization type plausible for running clinical trials?
   - Is the contact information complete and professional?
   - Score 0 and DISQUALIFY if organization type is Personal or Individual.

2. TRIAL VIABILITY (0–25 points)
   - Is the trial type described coherently?
   - Is the enrollment goal realistic for the budget and timeline?
   - Score 0 and DISQUALIFY if no coherent trial type is stated.
   {$timeline_text}

3. LATINO COMMUNITY RELEVANCE (0–25 points)
   - Does the target population include or specifically address Latino/Hispanic communities?
   - Is there awareness of the cultural and linguistic needs of this community?
   - Custom criteria from administrator: {$custom_criteria}

4. BUDGET SERIOUSNESS (0–25 points)
   - Minimum acceptable budget: {$min_budget}
   - Does the stated budget match the scope of enrollment goal and timeline?
   - Score 0 and DISQUALIFY if budget is below the minimum threshold.

DISQUALIFICATION CONDITIONS — any single condition triggers an automatic rejection:
   - Budget below minimum threshold ({$min_budget})
   - Organization type is Personal or Individual
   - No coherent trial type stated
   - Application contains spam, gibberish, or clearly fictitious information
   - Total score below 40 out of 100

OUTPUT RULES:
- Respond ONLY with a valid JSON object. Do not include any text outside the JSON.
- reasoning_es and reasoning_en should be 2–4 sentences, professional and respectful.
- If rejecting, the reasoning should be constructive without disclosing internal scoring details.
- If rejecting, populate "suggestions" with 1–3 specific, actionable items the applicant can change
  to improve their chances. Each suggestion must reference a real field from the application.
  Use these field names: estimated_budget, trial_type, target_population, organization_type,
  enrollment_goal, timeline_months, additional_notes, organization_website.
- If qualifying, set "suggestions" to an empty array [].

JSON FORMAT:
{
  "qualified": true,
  "score": 78.5,
  "reasoning_es": "Su organización presenta un perfil sólido...",
  "reasoning_en": "Your organization presents a strong profile...",
  "disqualification_reason": null,
  "suggestions": [],
  "criteria_scores": {
    "organizational_legitimacy": 22,
    "trial_viability": 20,
    "latino_community_relevance": 18,
    "budget_seriousness": 18.5
  }
}

REJECTION EXAMPLE (suggestions populated):
{
  "qualified": false,
  "score": 32,
  "reasoning_es": "Su solicitud no cumple con los requisitos mínimos en este momento...",
  "reasoning_en": "Your application does not meet our minimum requirements at this time...",
  "disqualification_reason": "budget_below_threshold",
  "suggestions": [
    {
      "field": "estimated_budget",
      "issue_en": "Your stated budget falls below our minimum threshold for viable trial recruitment.",
      "issue_es": "Su presupuesto declarado está por debajo de nuestro umbral mínimo para reclutamiento viable.",
      "suggestion_en": "Consider revising your budget to at least $50,000–$200,000 to meet our service requirements.",
      "suggestion_es": "Considere revisar su presupuesto a al menos $50,000–$200,000 para cumplir con nuestros requisitos."
    },
    {
      "field": "target_population",
      "issue_en": "Your target population does not include Latino or Hispanic communities.",
      "issue_es": "Su población objetivo no incluye comunidades latinas o hispanas.",
      "suggestion_en": "Specify Latino or Hispanic communities as part of your target enrollment population to align with Diversia Health's mission.",
      "suggestion_es": "Especifique comunidades latinas o hispanas como parte de su población objetivo de inscripción."
    }
  ],
  "criteria_scores": {
    "organizational_legitimacy": 18,
    "trial_viability": 14,
    "latino_community_relevance": 0,
    "budget_seriousness": 0
  }
}
PROMPT;
    }

    private function build_user_message(array $data): string {
        $population = is_array($data['target_population'])
            ? implode(', ', array_map('sanitize_text_field', $data['target_population']))
            : sanitize_textarea_field($data['target_population'] ?? '');

        return "APPLICANT INFORMATION:\n\n"
            . "Company/Organization: " . wp_strip_all_tags($data['company_name'] ?? '') . "\n"
            . "Organization Type: "    . wp_strip_all_tags($data['organization_type'] ?? '') . "\n"
            . "Website: "              . wp_strip_all_tags($data['organization_website'] ?? 'Not provided') . "\n\n"
            . "TRIAL DETAILS:\n"
            . "Trial Type: "           . wp_strip_all_tags($data['trial_type'] ?? '') . "\n"
            . "Target Population: "    . wp_strip_all_tags($population) . "\n"
            . "Enrollment Goal: "      . intval($data['enrollment_goal'] ?? 0) . " participants\n"
            . "Estimated Timeline: "   . wp_strip_all_tags(!empty($data['timeline_label']) ? $data['timeline_label'] : intval($data['timeline_months'] ?? 0) . ' months') . "\n"
            . "Estimated Budget: "     . wp_strip_all_tags($data['estimated_budget'] ?? '') . "\n\n"
            . "ADDITIONAL NOTES:\n"
            . wp_strip_all_tags(sanitize_textarea_field($data['additional_notes'] ?? 'None provided')) . "\n\n"
            . "Please evaluate this application and respond with a JSON object only.";
    }

    private function call_api(array $messages): string {
        $response = wp_remote_post($this->api_url, array(
            'timeout' => 45,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type'  => 'application/json',
            ),
            'body' => wp_json_encode(array(
                'model'       => $this->model,
                'messages'    => $messages,
                'max_tokens'  => 1400,
                'temperature' => 0.2,
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
            throw new Exception('OpenAI API error: ' . $msg);
        }

        $parsed = json_decode($body, true);
        if (!isset($parsed['choices'][0]['message']['content'])) {
            throw new Exception('Unexpected OpenAI response structure.');
        }

        return $parsed['choices'][0]['message']['content'];
    }

    private function parse_ai_response(string $content): array {
        // Try direct decode first
        $data = json_decode($content, true);

        // If direct decode fails, try extracting JSON from the string
        if (null === $data) {
            if (preg_match('/\{[\s\S]*\}/U', $content, $matches)) {
                $data = json_decode($matches[0], true);
            }
        }

        if (null === $data) {
            error_log('[DCO] Failed to parse OpenAI JSON response: ' . $content);
            return $this->fallback_rejection('Could not parse AI response.');
        }

        // Sanitize each suggestion item
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
