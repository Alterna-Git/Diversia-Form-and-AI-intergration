<?php
if (!defined('ABSPATH')) exit;

/**
 * DCO_Lead_Engine
 *
 * PHP port of the LeadEngine recruitment viability calculator (dco-estimator.js).
 * Given a trial type, location(s), budget, enrollment goal, and timeline, produces
 * a pre-calculated analysis text ready to be appended to the Anthropic AI user message.
 *
 * All data tables (location data, CPL/funnel rates) are identical to the JS version
 * so the numbers Claude receives are consistent with what the Estimator tool shows.
 */
class DCO_Lead_Engine {

    // ── Location data (52 entries: 50 US states + DC + PR) ───────────────────

    private static $EL = array(
        'PR' => array('n'=>'Puerto Rico',       'pop'=>2430000,  'reach'=>1822500,  'hm'=>1.15,'cm'=>1.00),
        'CA' => array('n'=>'California',        'pop'=>11820000, 'reach'=>8865000,  'hm'=>1.05,'cm'=>1.40),
        'TX' => array('n'=>'Texas',             'pop'=>9100000,  'reach'=>6825000,  'hm'=>1.10,'cm'=>1.25),
        'FL' => array('n'=>'Florida',           'pop'=>4650000,  'reach'=>3487500,  'hm'=>1.04,'cm'=>1.25),
        'NY' => array('n'=>'New York',          'pop'=>2850000,  'reach'=>2137500,  'hm'=>1.00,'cm'=>1.40),
        'AZ' => array('n'=>'Arizona',           'pop'=>1750000,  'reach'=>1312500,  'hm'=>1.12,'cm'=>1.25),
        'IL' => array('n'=>'Illinois',          'pop'=>1650000,  'reach'=>1237500,  'hm'=>1.06,'cm'=>1.25),
        'NJ' => array('n'=>'New Jersey',        'pop'=>1500000,  'reach'=>1125000,  'hm'=>1.02,'cm'=>1.40),
        'GA' => array('n'=>'Georgia',           'pop'=>825000,   'reach'=>618750,   'hm'=>1.05,'cm'=>1.15),
        'NC' => array('n'=>'North Carolina',    'pop'=>790000,   'reach'=>592500,   'hm'=>1.03,'cm'=>1.10),
        'CO' => array('n'=>'Colorado',          'pop'=>935000,   'reach'=>701250,   'hm'=>0.95,'cm'=>1.25),
        'WA' => array('n'=>'Washington',        'pop'=>685000,   'reach'=>513750,   'hm'=>0.98,'cm'=>1.25),
        'PA' => array('n'=>'Pennsylvania',      'pop'=>685000,   'reach'=>513750,   'hm'=>1.00,'cm'=>1.20),
        'MA' => array('n'=>'Massachusetts',     'pop'=>650000,   'reach'=>487500,   'hm'=>1.08,'cm'=>1.35),
        'NV' => array('n'=>'Nevada',            'pop'=>650000,   'reach'=>487500,   'hm'=>1.10,'cm'=>1.25),
        'VA' => array('n'=>'Virginia',          'pop'=>570000,   'reach'=>427500,   'hm'=>1.00,'cm'=>1.25),
        'MD' => array('n'=>'Maryland',          'pop'=>540000,   'reach'=>405000,   'hm'=>1.02,'cm'=>1.30),
        'CT' => array('n'=>'Connecticut',       'pop'=>465000,   'reach'=>348750,   'hm'=>1.05,'cm'=>1.30),
        'OR' => array('n'=>'Oregon',            'pop'=>450000,   'reach'=>337500,   'hm'=>0.97,'cm'=>1.20),
        'UT' => array('n'=>'Utah',              'pop'=>334000,   'reach'=>250500,   'hm'=>0.96,'cm'=>1.10),
        'MI' => array('n'=>'Michigan',          'pop'=>428000,   'reach'=>321000,   'hm'=>1.01,'cm'=>1.15),
        'OH' => array('n'=>'Ohio',              'pop'=>371000,   'reach'=>278250,   'hm'=>1.01,'cm'=>1.10),
        'TN' => array('n'=>'Tennessee',         'pop'=>367000,   'reach'=>275250,   'hm'=>1.04,'cm'=>1.05),
        'IN' => array('n'=>'Indiana',           'pop'=>356000,   'reach'=>267000,   'hm'=>1.03,'cm'=>1.05),
        'NM' => array('n'=>'New Mexico',        'pop'=>760000,   'reach'=>570000,   'hm'=>1.12,'cm'=>1.10),
        'MN' => array('n'=>'Minnesota',         'pop'=>311000,   'reach'=>233250,   'hm'=>0.97,'cm'=>1.15),
        'WI' => array('n'=>'Wisconsin',         'pop'=>311000,   'reach'=>233250,   'hm'=>1.00,'cm'=>1.10),
        'OK' => array('n'=>'Oklahoma',          'pop'=>292000,   'reach'=>219000,   'hm'=>1.08,'cm'=>1.00),
        'KS' => array('n'=>'Kansas',            'pop'=>259000,   'reach'=>194250,   'hm'=>1.05,'cm'=>1.00),
        'SC' => array('n'=>'South Carolina',    'pop'=>240000,   'reach'=>180000,   'hm'=>1.03,'cm'=>1.05),
        'ID' => array('n'=>'Idaho',             'pop'=>221000,   'reach'=>165750,   'hm'=>1.00,'cm'=>1.00),
        'MO' => array('n'=>'Missouri',          'pop'=>210000,   'reach'=>157500,   'hm'=>1.02,'cm'=>1.05),
        'RI' => array('n'=>'Rhode Island',      'pop'=>154000,   'reach'=>115500,   'hm'=>1.10,'cm'=>1.25),
        'AR' => array('n'=>'Arkansas',          'pop'=>195000,   'reach'=>146250,   'hm'=>1.07,'cm'=>0.95),
        'AL' => array('n'=>'Alabama',           'pop'=>180000,   'reach'=>135000,   'hm'=>1.05,'cm'=>0.95),
        'NE' => array('n'=>'Nebraska',          'pop'=>176000,   'reach'=>132000,   'hm'=>1.04,'cm'=>1.00),
        'KY' => array('n'=>'Kentucky',          'pop'=>169000,   'reach'=>126750,   'hm'=>1.03,'cm'=>0.95),
        'IA' => array('n'=>'Iowa',              'pop'=>172000,   'reach'=>129000,   'hm'=>1.03,'cm'=>0.95),
        'LA' => array('n'=>'Louisiana',         'pop'=>206000,   'reach'=>154500,   'hm'=>1.06,'cm'=>1.00),
        'MS' => array('n'=>'Mississippi',       'pop'=>82000,    'reach'=>61500,    'hm'=>1.07,'cm'=>0.90),
        'DE' => array('n'=>'Delaware',          'pop'=>79000,    'reach'=>59250,    'hm'=>1.02,'cm'=>1.20),
        'DC' => array('n'=>'Washington, D.C.',  'pop'=>86000,    'reach'=>64500,    'hm'=>1.05,'cm'=>1.45),
        'NH' => array('n'=>'New Hampshire',     'pop'=>45000,    'reach'=>33750,    'hm'=>0.98,'cm'=>1.15),
        'HI' => array('n'=>'Hawaii',            'pop'=>45000,    'reach'=>33750,    'hm'=>0.96,'cm'=>1.25),
        'AK' => array('n'=>'Alaska',            'pop'=>41000,    'reach'=>30750,    'hm'=>0.95,'cm'=>1.10),
        'MT' => array('n'=>'Montana',           'pop'=>41000,    'reach'=>30750,    'hm'=>0.97,'cm'=>0.90),
        'WV' => array('n'=>'West Virginia',     'pop'=>30000,    'reach'=>22500,    'hm'=>1.02,'cm'=>0.85),
        'ND' => array('n'=>'North Dakota',      'pop'=>26000,    'reach'=>19500,    'hm'=>1.00,'cm'=>0.90),
        'ME' => array('n'=>'Maine',             'pop'=>22000,    'reach'=>16500,    'hm'=>0.96,'cm'=>1.00),
        'VT' => array('n'=>'Vermont',           'pop'=>15000,    'reach'=>11250,    'hm'=>0.95,'cm'=>1.00),
        'WY' => array('n'=>'Wyoming',           'pop'=>52000,    'reach'=>39000,    'hm'=>0.98,'cm'=>0.90),
        'SD' => array('n'=>'South Dakota',      'pop'=>37000,    'reach'=>27750,    'hm'=>1.00,'cm'=>0.90),
    );

    // ── trial_type value → condition key ─────────────────────────────────────

    private static $KEY_MAP = array(
        'obesity'            => 'OB',
        'prediabetes'        => 'PRE',
        'diabetes'           => 'DT2',
        'metabolic'          => 'SM',
        'cholesterol'        => 'COL',
        'thyroid'            => 'TIR',
        'pcos'               => 'SOP',
        'cardiovascular'     => 'CVD',
        'hypertension'       => 'HTA',
        'heart-failure'      => 'IC',
        'afib'               => 'FA',
        'asthma'             => 'ASM',
        'sleep-apnea'        => 'APN',
        'anxiety'            => 'ANX',
        'depression'         => 'DEP',
        'adhd'               => 'TDAH',
        'alzheimers'         => 'ALZ',
        'parkinsons'         => 'PK',
        'migraine'           => 'MIG',
        'cognitive'          => 'DCG',
        'lupus'              => 'LUP',
        'arthritis'          => 'ART',
        'autoimmune'         => 'AUR',
        'oncology'           => 'ONC',
        'rare-cancer'        => 'CRH',
        'ckd'                => 'ERC',
        'liver'              => 'HEP',
        'chronic-pain'       => 'DOL',
        'gerd'               => 'ERG',
        'osteoporosis'       => 'OST',
        'diabetic-neuropathy'=> 'NDB',
        'glaucoma'           => 'GLC',
        'hiv'                => 'VIH',
        'infectious'         => 'INF',
        'rare-genetic'       => 'TGR',
        'rare-pediatric'     => 'CPR',
    );

    // ── Condition display names ───────────────────────────────────────────────

    private static $EC_NAMES = array(
        'OB'   => 'Obesity/Overweight',
        'PRE'  => 'Prediabetes',
        'DT2'  => 'Type 2 Diabetes',
        'SM'   => 'Metabolic Syndrome',
        'COL'  => 'High Cholesterol',
        'TIR'  => 'Thyroid Disease',
        'SOP'  => 'PCOS',
        'CVD'  => 'Cardiovascular Disease',
        'HTA'  => 'Hypertension',
        'IC'   => 'Heart Failure',
        'FA'   => 'Atrial Fibrillation',
        'ASM'  => 'Asthma/COPD',
        'APN'  => 'Sleep Apnea',
        'ANX'  => 'Anxiety',
        'DEP'  => 'Depression',
        'TDAH' => 'ADHD',
        'ALZ'  => "Alzheimer's/Dementia",
        'PK'   => "Parkinson's",
        'MIG'  => 'Migraine',
        'DCG'  => 'Cognitive Decline',
        'LUP'  => 'Lupus/SLE',
        'ART'  => 'Arthritis',
        'AUR'  => 'Autoimmune (Other)',
        'ONC'  => 'Oncology/Cancer',
        'CRH'  => 'Rare Cancer',
        'ERC'  => 'Chronic Kidney Disease',
        'HEP'  => 'Liver Disease',
        'DOL'  => 'Chronic Pain',
        'ERG'  => 'GERD/Reflux',
        'OST'  => 'Osteoporosis',
        'NDB'  => 'Diabetic Neuropathy',
        'GLC'  => 'Glaucoma/Cataracts',
        'VIH'  => 'HIV/AIDS',
        'INF'  => 'Infectious Disease',
        'TGR'  => 'Rare Genetic Disorders',
        'CPR'  => 'Rare Pediatric Conditions',
    );

    // ── Condition data: CPL (cL=optimistic, cM=realistic, cH=conservative) ──
    //    prev = US population prevalence | diff = recruitment difficulty

    private static $LE_D = array(
        'OB'   => array('cL'=>18, 'cM'=>28,  'cH'=>42,  'prev'=>0.366, 'diff'=>'low'),
        'PRE'  => array('cL'=>22, 'cM'=>35,  'cH'=>50,  'prev'=>0.38,  'diff'=>'medium'),
        'DT2'  => array('cL'=>25, 'cM'=>38,  'cH'=>55,  'prev'=>0.18,  'diff'=>'medium'),
        'SM'   => array('cL'=>30, 'cM'=>48,  'cH'=>70,  'prev'=>0.35,  'diff'=>'medium'),
        'COL'  => array('cL'=>22, 'cM'=>32,  'cH'=>48,  'prev'=>0.38,  'diff'=>'low'),
        'TIR'  => array('cL'=>28, 'cM'=>42,  'cH'=>62,  'prev'=>0.08,  'diff'=>'medium'),
        'SOP'  => array('cL'=>25, 'cM'=>38,  'cH'=>55,  'prev'=>0.10,  'diff'=>'medium'),
        'CVD'  => array('cL'=>35, 'cM'=>55,  'cH'=>80,  'prev'=>0.065, 'diff'=>'high'),
        'HTA'  => array('cL'=>20, 'cM'=>32,  'cH'=>48,  'prev'=>0.30,  'diff'=>'low'),
        'IC'   => array('cL'=>45, 'cM'=>68,  'cH'=>95,  'prev'=>0.018, 'diff'=>'high'),
        'FA'   => array('cL'=>42, 'cM'=>65,  'cH'=>90,  'prev'=>0.02,  'diff'=>'high'),
        'ASM'  => array('cL'=>22, 'cM'=>35,  'cH'=>52,  'prev'=>0.165, 'diff'=>'medium'),
        'APN'  => array('cL'=>25, 'cM'=>40,  'cH'=>58,  'prev'=>0.07,  'diff'=>'medium'),
        'ANX'  => array('cL'=>15, 'cM'=>25,  'cH'=>38,  'prev'=>0.22,  'diff'=>'low'),
        'DEP'  => array('cL'=>15, 'cM'=>25,  'cH'=>38,  'prev'=>0.18,  'diff'=>'low'),
        'TDAH' => array('cL'=>20, 'cM'=>32,  'cH'=>48,  'prev'=>0.05,  'diff'=>'medium'),
        'ALZ'  => array('cL'=>55, 'cM'=>85,  'cH'=>120, 'prev'=>0.013, 'diff'=>'very_high'),
        'PK'   => array('cL'=>60, 'cM'=>90,  'cH'=>130, 'prev'=>0.006, 'diff'=>'very_high'),
        'MIG'  => array('cL'=>18, 'cM'=>28,  'cH'=>42,  'prev'=>0.16,  'diff'=>'low'),
        'DCG'  => array('cL'=>40, 'cM'=>60,  'cH'=>85,  'prev'=>0.04,  'diff'=>'high'),
        'LUP'  => array('cL'=>65, 'cM'=>95,  'cH'=>140, 'prev'=>0.007, 'diff'=>'very_high'),
        'ART'  => array('cL'=>20, 'cM'=>32,  'cH'=>48,  'prev'=>0.20,  'diff'=>'low'),
        'AUR'  => array('cL'=>35, 'cM'=>55,  'cH'=>78,  'prev'=>0.05,  'diff'=>'high'),
        'ONC'  => array('cL'=>45, 'cM'=>70,  'cH'=>100, 'prev'=>0.03,  'diff'=>'high'),
        'CRH'  => array('cL'=>80, 'cM'=>120, 'cH'=>175, 'prev'=>0.004, 'diff'=>'very_high'),
        'ERC'  => array('cL'=>35, 'cM'=>52,  'cH'=>75,  'prev'=>0.14,  'diff'=>'high'),
        'HEP'  => array('cL'=>38, 'cM'=>58,  'cH'=>82,  'prev'=>0.06,  'diff'=>'high'),
        'DOL'  => array('cL'=>18, 'cM'=>28,  'cH'=>42,  'prev'=>0.22,  'diff'=>'low'),
        'ERG'  => array('cL'=>18, 'cM'=>28,  'cH'=>42,  'prev'=>0.20,  'diff'=>'low'),
        'OST'  => array('cL'=>25, 'cM'=>38,  'cH'=>55,  'prev'=>0.08,  'diff'=>'medium'),
        'NDB'  => array('cL'=>35, 'cM'=>52,  'cH'=>75,  'prev'=>0.07,  'diff'=>'high'),
        'GLC'  => array('cL'=>28, 'cM'=>42,  'cH'=>62,  'prev'=>0.065, 'diff'=>'medium'),
        'VIH'  => array('cL'=>35, 'cM'=>55,  'cH'=>80,  'prev'=>0.015, 'diff'=>'high'),
        'INF'  => array('cL'=>30, 'cM'=>48,  'cH'=>68,  'prev'=>0.025, 'diff'=>'medium'),
        'TGR'  => array('cL'=>90, 'cM'=>140, 'cH'=>200, 'prev'=>0.002, 'diff'=>'very_high'),
        'CPR'  => array('cL'=>95, 'cM'=>150, 'cH'=>220, 'prev'=>0.001, 'diff'=>'very_high'),
    );

    // ── Funnel rates for Latino populations ───────────────────────────────────
    //    ps=pre-screen pass, bk=book appt, at=attend, en=enroll, sf=screen-fail

    private static $LE_F = array(
        'OB'   => array('ps'=>0.80,'bk'=>0.62,'at'=>0.75,'en'=>0.60,'sf'=>0.18),
        'PRE'  => array('ps'=>0.65,'bk'=>0.55,'at'=>0.68,'en'=>0.50,'sf'=>0.30),
        'DT2'  => array('ps'=>0.75,'bk'=>0.60,'at'=>0.72,'en'=>0.52,'sf'=>0.28),
        'SM'   => array('ps'=>0.60,'bk'=>0.50,'at'=>0.65,'en'=>0.45,'sf'=>0.35),
        'COL'  => array('ps'=>0.78,'bk'=>0.58,'at'=>0.72,'en'=>0.58,'sf'=>0.22),
        'TIR'  => array('ps'=>0.72,'bk'=>0.55,'at'=>0.68,'en'=>0.55,'sf'=>0.22),
        'SOP'  => array('ps'=>0.75,'bk'=>0.62,'at'=>0.75,'en'=>0.55,'sf'=>0.20),
        'CVD'  => array('ps'=>0.60,'bk'=>0.50,'at'=>0.65,'en'=>0.42,'sf'=>0.38),
        'HTA'  => array('ps'=>0.78,'bk'=>0.60,'at'=>0.72,'en'=>0.58,'sf'=>0.22),
        'IC'   => array('ps'=>0.52,'bk'=>0.45,'at'=>0.58,'en'=>0.40,'sf'=>0.40),
        'FA'   => array('ps'=>0.55,'bk'=>0.48,'at'=>0.60,'en'=>0.45,'sf'=>0.35),
        'ASM'  => array('ps'=>0.75,'bk'=>0.58,'at'=>0.70,'en'=>0.58,'sf'=>0.20),
        'APN'  => array('ps'=>0.65,'bk'=>0.52,'at'=>0.65,'en'=>0.52,'sf'=>0.25),
        'ANX'  => array('ps'=>0.78,'bk'=>0.65,'at'=>0.72,'en'=>0.62,'sf'=>0.15),
        'DEP'  => array('ps'=>0.75,'bk'=>0.62,'at'=>0.68,'en'=>0.58,'sf'=>0.18),
        'TDAH' => array('ps'=>0.70,'bk'=>0.58,'at'=>0.68,'en'=>0.55,'sf'=>0.22),
        'ALZ'  => array('ps'=>0.42,'bk'=>0.38,'at'=>0.52,'en'=>0.32,'sf'=>0.50),
        'PK'   => array('ps'=>0.48,'bk'=>0.42,'at'=>0.55,'en'=>0.38,'sf'=>0.42),
        'MIG'  => array('ps'=>0.78,'bk'=>0.60,'at'=>0.72,'en'=>0.58,'sf'=>0.18),
        'DCG'  => array('ps'=>0.48,'bk'=>0.42,'at'=>0.55,'en'=>0.40,'sf'=>0.42),
        'LUP'  => array('ps'=>0.52,'bk'=>0.48,'at'=>0.60,'en'=>0.40,'sf'=>0.38),
        'ART'  => array('ps'=>0.78,'bk'=>0.58,'at'=>0.70,'en'=>0.55,'sf'=>0.22),
        'AUR'  => array('ps'=>0.58,'bk'=>0.50,'at'=>0.62,'en'=>0.45,'sf'=>0.32),
        'ONC'  => array('ps'=>0.55,'bk'=>0.52,'at'=>0.65,'en'=>0.38,'sf'=>0.42),
        'CRH'  => array('ps'=>0.45,'bk'=>0.42,'at'=>0.55,'en'=>0.32,'sf'=>0.48),
        'ERC'  => array('ps'=>0.65,'bk'=>0.52,'at'=>0.65,'en'=>0.48,'sf'=>0.32),
        'HEP'  => array('ps'=>0.62,'bk'=>0.50,'at'=>0.62,'en'=>0.48,'sf'=>0.32),
        'DOL'  => array('ps'=>0.78,'bk'=>0.62,'at'=>0.70,'en'=>0.58,'sf'=>0.15),
        'ERG'  => array('ps'=>0.75,'bk'=>0.58,'at'=>0.68,'en'=>0.58,'sf'=>0.18),
        'OST'  => array('ps'=>0.68,'bk'=>0.52,'at'=>0.65,'en'=>0.52,'sf'=>0.25),
        'NDB'  => array('ps'=>0.65,'bk'=>0.50,'at'=>0.62,'en'=>0.48,'sf'=>0.28),
        'GLC'  => array('ps'=>0.70,'bk'=>0.52,'at'=>0.65,'en'=>0.52,'sf'=>0.25),
        'VIH'  => array('ps'=>0.68,'bk'=>0.55,'at'=>0.65,'en'=>0.50,'sf'=>0.25),
        'INF'  => array('ps'=>0.65,'bk'=>0.52,'at'=>0.62,'en'=>0.50,'sf'=>0.25),
        'TGR'  => array('ps'=>0.38,'bk'=>0.35,'at'=>0.52,'en'=>0.30,'sf'=>0.52),
        'CPR'  => array('ps'=>0.35,'bk'=>0.32,'at'=>0.50,'en'=>0.28,'sf'=>0.55),
    );

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Resolves a form trial_type string to a LeadEngine condition key.
     * Returns null when the condition is not in the database (e.g., "Other").
     */
    public static function resolve_condition_key(string $trial_type): ?string {
        $lower = strtolower(trim($trial_type));
        return self::$KEY_MAP[$lower] ?? null;
    }

    /**
     * Runs the LeadEngine viability analysis and returns a pre-formatted text
     * block ready to be appended to the Anthropic AI user message.
     *
     * @param string $trial_type       Disease string from the form (e.g. "obesity")
     * @param array  $locations        State/territory codes (e.g. ["CA"] or ["PR"])
     * @param int    $budget           Campaign budget in USD (use upper bound of range)
     * @param int    $target_enrolled  Number of patients the applicant wants to enroll
     * @param int    $timeline_months  Campaign duration already converted to months
     * @return string|null             Analysis text, or null if data is insufficient
     */
    public static function format_for_ai(
        string $trial_type,
        array  $locations,
        int    $budget,
        int    $target_enrolled,
        int    $timeline_months
    ): ?string {

        $condition_key = self::resolve_condition_key($trial_type);
        if (
            !$condition_key
            || !isset(self::$LE_D[$condition_key])
            || !isset(self::$LE_F[$condition_key])
            || empty($locations)
            || $budget           <= 0
            || $target_enrolled  <= 0
            || $timeline_months  <= 0
        ) {
            return null;
        }

        $cond = self::$LE_D[$condition_key];
        $fn   = self::$LE_F[$condition_key];

        // Aggregate location data
        $total_pop   = 0;
        $total_reach = 0;
        $sum_hm      = 0.0;
        $sum_cm      = 0.0;
        $loc_names   = array();

        foreach ($locations as $loc) {
            if (!isset(self::$EL[$loc])) continue;
            $st           = self::$EL[$loc];
            $total_pop   += $st['pop'];
            $total_reach += $st['reach'];
            $sum_hm      += $st['hm'];
            $sum_cm      += $st['cm'];
            $loc_names[]  = $st['n'];
        }

        if (empty($loc_names)) {
            return null;
        }

        $n      = count($loc_names);
        $avg_hm = $sum_hm / $n;
        $avg_cm = $sum_cm / $n;

        // Adjusted prevalence & eligible pool
        $adj_prev = min($cond['prev'] * ($avg_hm / 1.15), 0.95);
        $pool     = (int) round($total_reach * $adj_prev);

        // Funnel net conversion and leads needed
        $nc          = $fn['ps'] * $fn['bk'] * $fn['at'] * $fn['en'];
        $lpe         = (int) ceil(1 / $nc);
        $total_leads = $lpe * $target_enrolled;
        $time_weeks  = (int) round($timeline_months * 4.33);
        $lpw         = $time_weeks > 0 ? (int) ceil($total_leads / $time_weeks) : $total_leads;

        // Location-adjusted CPL for each scenario
        $cpl_o = (int) round($cond['cL'] * $avg_cm);
        $cpl_m = (int) round($cond['cM'] * $avg_cm);
        $cpl_c = (int) round($cond['cH'] * $avg_cm);

        // Total cost per scenario to hit goal
        $cost_o = $total_leads * $cpl_o;
        $cost_m = $total_leads * $cpl_m;
        $cost_c = $total_leads * $cpl_c;

        // How many leads / enrolled patients the given budget actually buys
        $leads_o = $cpl_o > 0 ? (int) floor($budget / $cpl_o) : 0;
        $leads_m = $cpl_m > 0 ? (int) floor($budget / $cpl_m) : 0;
        $leads_c = $cpl_c > 0 ? (int) floor($budget / $cpl_c) : 0;
        $enr_o   = (int) floor($leads_o * $nc);
        $enr_m   = (int) floor($leads_m * $nc);
        $enr_c   = (int) floor($leads_c * $nc);

        // Viability checks (mirrors JS _leAnalyze)
        $budget_ok = $budget >= $cost_m;
        $pool_ok   = $pool   >= $total_leads * 3;
        $time_ok   = $time_weeks >= (int) ceil($total_leads / max($lpw, 1));

        $pass_count = ($budget_ok ? 1 : 0) + ($pool_ok ? 1 : 0) + ($time_ok ? 1 : 0);

        if ($pass_count === 3) {
            $verdict = 'APPROVED';
            $score   = min(100, 85 + (int) round($nc * 100));
        } elseif ($pass_count >= 1) {
            $verdict = 'VIABLE WITH ADJUSTMENTS';
            $score   = 40 + $pass_count * 15;
        } else {
            $verdict = 'NOT VIABLE';
            $score   = max(10, (int) round($nc * 200));
        }

        // >30% over budget → force NOT VIABLE (mirrors JS)
        if ($cost_m > $budget * 1.30) {
            $verdict = 'NOT VIABLE';
            $score   = min($score, 30);
        }

        // ── Build the text block ─────────────────────────────────────────────

        $cond_name = self::$EC_NAMES[$condition_key] ?? $condition_key;
        $loc_str   = implode(', ', $loc_names);
        $nc_pct    = number_format($nc * 100, 1);
        $sf_pct    = number_format($fn['sf'] * 100, 0);
        $prev_pct  = number_format($adj_prev * 100, 1);
        $avg_cm_f  = number_format($avg_cm, 2);

        $pool_chk = ($pool_ok ? '✓' : '✗')
            . ' Latino Pool: ' . number_format($pool) . ' reachable '
            . ($pool_ok
                ? '(sufficient for ' . number_format($total_leads) . ' leads needed)'
                : '(INSUFFICIENT — need ~' . number_format($total_leads * 3) . ' to avoid saturation)');

        $budget_chk = ($budget_ok ? '✓' : '✗')
            . ' Budget: $' . number_format($budget) . ' vs realistic cost $' . number_format($cost_m)
            . ($budget_ok
                ? ' (covers it)'
                : ' (DEFICIT $' . number_format($cost_m - $budget) . ')');

        $time_chk = (($time_ok && $nc >= 0.05) ? '✓' : '✗')
            . ' Timeline: ' . $timeline_months . ' months (' . $time_weeks . ' weeks), '
            . $lpw . ' leads/week needed '
            . (($time_ok && $nc >= 0.05) ? '(feasible)' : '(INSUFFICIENT)');

        $out  = "=== LEADENGINE PRE-CALCULATION (Alterna Agency Recruitment Viability) ===\n";
        $out .= "Condition   : {$cond_name} [{$condition_key}] | Difficulty: {$cond['diff']}\n";
        $out .= "Location(s) : {$loc_str}\n";
        $out .= "Latino reach: " . number_format($total_reach) . " | Adj. prevalence {$prev_pct}% | Eligible pool: " . number_format($pool) . "\n\n";

        $out .= "Funnel (Latino Meta Ads — Alterna Agency benchmarks):\n";
        $out .= "  Pre-screen {$fn['ps']} → Book {$fn['bk']} → Attend {$fn['at']} → Enroll {$fn['en']}\n";
        $out .= "  Net conversion: {$nc_pct}% | Screen-fail: {$sf_pct}%\n";
        $out .= "  Leads per enrolled: {$lpe} | Total leads needed for {$target_enrolled} enrolled: " . number_format($total_leads) . "\n\n";

        $out .= "Budget scenarios (CPL × location cost index {$avg_cm_f}):\n";
        $out .= "  Optimistic   CPL \${$cpl_o}: need \$" . number_format($cost_o) . " → budget buys ~{$enr_o} enrolled\n";
        $out .= "  Realistic    CPL \${$cpl_m}: need \$" . number_format($cost_m) . " → budget buys ~{$enr_m} enrolled\n";
        $out .= "  Conservative CPL \${$cpl_c}: need \$" . number_format($cost_c) . " → budget buys ~{$enr_c} enrolled\n\n";

        $out .= "Viability verdict: {$verdict} ({$pass_count}/3 checks passed, score {$score}/100)\n";
        $out .= "  {$pool_chk}\n";
        $out .= "  {$budget_chk}\n";
        $out .= "  {$time_chk}\n";

        if (!$budget_ok) {
            $out .= "\n[!] At realistic CPL the budget projects ~{$enr_m} enrolled patients vs. the goal of {$target_enrolled}. ";
            $out .= "Full budget needed: \$" . number_format($cost_m) . ".\n";
        }
        if (!$pool_ok) {
            $out .= "\n[!] The Latino pool may be too small for this volume without audience saturation. ";
            $out .= "Expanding to additional high-Hispanic states (CA, TX, FL) is recommended.\n";
        }
        if ($fn['sf'] > 0.30) {
            $out .= "\n[!] High screen-fail rate ({$sf_pct}%) — a digital pre-screening questionnaire is strongly advised.\n";
        }
        $out .= "=== END LEADENGINE ===";

        return $out;
    }
}
