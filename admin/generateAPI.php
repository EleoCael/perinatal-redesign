
<?php
header('Content-Type: application/json');
ob_start();
//generateAPI for admin
// Correct path: from /admin/ go up one level to root, then into /module/
$configFile = '../module/db.config.php';

if (!file_exists($configFile)) {
    die(json_encode([
        'success' => false, 
        'error' => 'Database config not found at: ' . realpath($configFile) . '. Looking in: ' . $configFile
    ]));
}

require_once $configFile;

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . mysqli_connect_error()]));
}

session_start();

$action = $_GET['action'] ?? '';
$period = $_GET['period'] ?? 'monthly';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$quarter = $_GET['quarter'] ?? 1;
$barangays = isset($_GET['barangays']) ? explode(',', $_GET['barangays']) : [];

try {
    if ($action === 'getBarangays') {
        getBarangays($conn);
    } elseif ($action === 'maternal') {
        getPrenatalReport($conn, $barangays, $period, $month, $year, $quarter);
    } elseif ($action === 'child') {
        getChildReport($conn, $barangays, $period, $month, $year, $quarter);
    } elseif ($action === 'nutrition') {
        getNutritionReport($conn, $barangays, $period, $month, $year, $quarter);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getBarangays($conn)
{
    $sql = "SELECT health_center_id, barangay_name, municipality, province 
            FROM health_center 
            ORDER BY barangay_name";

    $result = $conn->query($sql);
    $barangays = [];

    while ($row = $result->fetch_assoc()) {
        $barangays[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $barangays]);
}

function getPrenatalReport($conn, $barangays, $period, $month, $year, $quarter)
{
    $title = generateTitle('Prenatal Care Report', $period, $month, $year, $quarter, $conn, $barangays);
    $reportData = [];

    $indicators = [
        ['key' => 'checkups_4plus', 'label' => '1. No. of pregnant women who gave birth with at least 4 prenatal check-ups - Total'],
        ['key' => 'nutrition_assessed', 'label' => '2. No. of pregnant women assessed of their nutritional status during the 1st trimester - Total'],
        ['key' => 'normal_bmi', 'label' => '&nbsp;&nbsp;a. Pregnant women seen in the first trimester who have normal BMI - Total', 'indent' => true],
        ['key' => 'low_bmi', 'label' => '&nbsp;&nbsp;b. Pregnant women seen in the first trimester who have low BMI - Total', 'indent' => true],
        ['key' => 'high_bmi', 'label' => '&nbsp;&nbsp;c. Pregnant women seen in the first trimester who have high BMI - Total', 'indent' => true],
        ['key' => 'td_2doses', 'label' => '3. No. of pregnant women for the first time given 2 doses of Td vaccination - Total'],
        ['key' => 'td_3plus_doses', 'label' => '4. No. of pregnant women for the 2nd or more times given at least 3 doses of Td vaccination - Total'],
        ['key' => 'iron_folic', 'label' => '5. No. of pregnant women who completed the dose of iron with folic acid supplementation - Total'],
        ['key' => 'calcium', 'label' => '6. No. of pregnant women who completed dose of calcium carbonate supplementation - Total'],
        ['key' => 'iodine', 'label' => '7. No. of pregnant women given iodine capsules - Total'],
        ['key' => 'deworming', 'label' => '8. No. of pregnant women given one dose of deworming tablet - Total'],
        ['key' => 'syphilis_screened', 'label' => '9. No. of pregnant women screened for syphilis - Total'],
        ['key' => 'syphilis_positive', 'label' => '10. No. of pregnant women tested positive for syphilis - Total'],
        ['key' => 'hepb_screened', 'label' => '11. No. of pregnant women screened for Hepatitis B - Total'],
        ['key' => 'hepb_positive', 'label' => '12. No. of pregnant women tested positive for Hepatitis B - Total'],
        ['key' => 'hiv_screened', 'label' => '13. No. of pregnant women screened for HIV - Total'],
        ['key' => 'cbc_tested', 'label' => '14. No. of pregnant women tested for CBC or Hgb & Hct count - Total'],
        ['key' => 'anemia_diagnosed', 'label' => '15. No. of pregnant women diagnosed with anemia - Total'],
        ['key' => 'diabetes_screened', 'label' => '16. No. of pregnant women screened for gestational diabetes - Total'],
        ['key' => 'diabetes_positive', 'label' => '17. No. of pregnant women tested positive for gestational diabetes - Total'],
    ];

    foreach ($indicators as $indicator) {
        $data = getPrenatalIndicatorData($conn, $barangays, $indicator['key'], $period, $year, $month, $quarter);

       // In your admin generateAPI.php - REPLACE the prenatal output section:
            $reportData[] = [
                'indicator' => $indicator['label'],
                'indent' => $indicator['indent'] ?? false,
                'age_10_14' => $data['10-14'] ?? 0,
                'age_15_19' => $data['15-19'] ?? 0,
                'age_20_49' => $data['20-49'] ?? 0,
                'grand_total' => ($data['10-14'] ?? 0) + ($data['15-19'] ?? 0) + ($data['20-49'] ?? 0)
            ];
    }

    echo json_encode(['success' => true, 'data' => $reportData, 'title' => $title]);
}

function getChildReport($conn, $barangays, $period, $month, $year, $quarter)
{
    $title = generateTitle('Infant Care & Immunization Report', $period, $month, $year, $quarter, $conn, $barangays);
    $reportData = [];

    $indicators = [
        ['key' => 'cpab', 'label' => '1. CPAB - Total'],
        ['key' => 'bcg', 'label' => '2. BCG - Total'],
        ['key' => 'hepb_24h', 'label' => '3. Hepatitis B within 24 hours - Total'],
        ['key' => 'hepb_after_24h', 'label' => '4. Hepatitis B after 24 hours - Total'],
        ['key' => 'pentavalent_1', 'label' => '5. DPT-HIB-HepB 1 - Total'],
        ['key' => 'pentavalent_2', 'label' => '6. DPT-HIB-HepB 2 - Total'],
        ['key' => 'pentavalent_3', 'label' => '7. DPT-HIB-HepB 3 - Total'],
        ['key' => 'opv_1', 'label' => '8. OPV 1 - Total'],
        ['key' => 'opv_2', 'label' => '9. OPV 2 - Total'],
        ['key' => 'opv_3', 'label' => '10. OPV 3 - Total'],
        ['key' => 'ipv_1', 'label' => '11. IPV 1 (routine) - Total'],
        ['key' => 'pcv_1', 'label' => '12. PCV 1 - Total'],
        ['key' => 'pcv_2', 'label' => '13. PCV 2 - Total'],
        ['key' => 'pcv_3', 'label' => '14. PCV 3 - Total'],
        ['key' => 'mcv_1', 'label' => '15. MCV 1 (AMV) - Total'],
        ['key' => 'mcv_2', 'label' => '16. MCV 2 (MMR) - Total'],
        ['key' => 'fic', 'label' => '17. FIC - Fully Immunized Child - Total'],
    ];

    foreach ($indicators as $indicator) {
        $data = getChildIndicatorData($conn, $barangays, $indicator['key'], $period, $year, $month, $quarter);

        $reportData[] = [
            'indicator' => $indicator['label'],
            'male' => $data['male'] ?? 0,
            'female' => $data['female'] ?? 0,
            'total' => ($data['male'] ?? 0) + ($data['female'] ?? 0)
        ];
    }

    echo json_encode(['success' => true, 'data' => $reportData, 'title' => $title]);
}

function getNutritionReport($conn, $barangays, $period, $month, $year, $quarter)
{
    $title = generateTitle('Nutrition Services Report', $period, $month, $year, $quarter, $conn, $barangays);
    $reportData = [];

    $indicators = [
        ['key' => 'bf_initiated', 'label' => '1. Newborns initiated on breastfeeding within 90 minutes - Total'],
        ['key' => 'lbw_iron', 'label' => '2. Preterm/LBW infants given iron supplementation - Total'],
        ['key' => 'ebf_6month', 'label' => '3. Infants exclusively breastfed until 6 months and 29 days- Total'],
        ['key' => 'compl_feeding_6month', 'label' => '4. Infants 6 months old initiated to complementary feeding with continued BF - Total'],
        ['key' => 'compl_no_bf', 'label' => '5. Infants 6 months initiated complementary feeding but no longer or never been breastfed - Total'],
        ['key' => 'vit_a_6_11m', 'label' => '5. Infants 6–11 months  given 1 dose of Vitamin A (100,000 IU) - Total'],
        ['key' => 'mnp_6_11m', 'label' => '6. Infants 6–11 months who completed MNP supplementation - Total'],
        ['key' => 'deworming', 'label' => '8. Infants given deworming - Total'],
    ];

    foreach ($indicators as $indicator) {
        $data = getNutritionIndicatorData($conn, $barangays, $indicator['key'], $period, $year, $month, $quarter);

        $reportData[] = [
            'indicator' => $indicator['label'],
            'male' => $data['male'] ?? 0,
            'female' => $data['female'] ?? 0,
            'total' => ($data['male'] ?? 0) + ($data['female'] ?? 0)
        ];
    }

    echo json_encode(['success' => true, 'data' => $reportData, 'title' => $title]);
}

function getPrenatalIndicatorData($conn, $barangays, $indicator, $period, $year, $month, $quarter)
{
    $data = [
        '10-14' => 0,
        '15-19' => 0,
        '20-49' => 0
    ];

    // Ensure barangays is an array
    if (is_string($barangays)) {
        $barangays = [$barangays];
    }
    
    $barangayIds = array_filter(array_map('intval', $barangays));
    
    // If no barangays selected, include ALL barangays (don't filter)
    if (empty($barangayIds)) {
        $barangayFilter = "";  // No filter = all barangays
    } else {
        $barangayIdsStr = implode(',', $barangayIds);
        $barangayFilter = "AND hc.health_center_id IN ($barangayIdsStr)";
    }

    $sql = "";

    switch ($indicator) {
        case 'checkups_4plus':
            // FIX: Match midwife logic exactly
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.patient_id) as count
                    FROM pregnancy p
                    INNER JOIN prenatal_checkup pc ON p.pregnancy_id = pc.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE pc.checkup_date IS NOT NULL 
                    AND pc.checkup_date != '0000-00-00'
                    " . getDateCondition($period, 'pc.checkup_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY pt.age_bracket, p.patient_id
                    HAVING COUNT(pc.checkup_id) >= 4";
            
            // Now count by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'nutrition_assessed':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN prenatal_checkup pc ON p.pregnancy_id = pc.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE pc.trimester = '1st' 
                    AND pc.bmi_class IS NOT NULL
                    " . getDateCondition($period, 'pc.checkup_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY pt.age_bracket";
            break;

        case 'normal_bmi':
        case 'low_bmi':
        case 'high_bmi':
            // FIX: Use exact case matching from database
            $bmi_map = [
                'normal_bmi' => 'NORMAL',
                'low_bmi' => 'LOW',
                'high_bmi' => 'HIGH'
            ];
            $bmi_class = $bmi_map[$indicator];
            
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.patient_id) as count
                    FROM pregnancy p
                    INNER JOIN prenatal_checkup pc ON p.pregnancy_id = pc.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE pc.trimester = '1st' 
                    AND pc.bmi_class = '$bmi_class'
                    " . getDateCondition($period, 'pc.checkup_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY pt.age_bracket";
            break;

        case 'td_2doses':
            // FIX: Use subquery approach like midwife
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_immunization mi ON p.pregnancy_id = mi.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE hc.health_center_id IN ($barangayIdsStr)
                    AND mi.immunization_date IS NOT NULL 
                    AND mi.immunization_date != '0000-00-00'
                    " . getDateCondition($period, 'mi.immunization_date', $year, $month, $quarter) . "
                    GROUP BY p.pregnancy_id, pt.age_bracket
                    HAVING COUNT(DISTINCT mi.immunization_type) = 2
                    AND SUM(CASE WHEN mi.immunization_type IN ('Td1/TT1', 'Td2/TT2') THEN 1 ELSE 0 END) = 2";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'td_3plus_doses':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_immunization mi ON p.pregnancy_id = mi.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mi.immunization_date IS NOT NULL 
                    AND mi.immunization_date != '0000-00-00'
                    " . getDateCondition($period, 'mi.immunization_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket
                    HAVING COUNT(DISTINCT mi.immunization_type) >= 3";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'iron_folic':
            // FIX: Count per pregnancy, then aggregate by age bracket
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_supplements ms ON p.pregnancy_id = ms.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE ms.supplement_type = 'Iron Sulfate w/Folic Acid'
                    AND ms.date_supp IS NOT NULL 
                    AND ms.date_supp != '0000-00-00'
                    " . getDateCondition($period, 'ms.date_supp', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket
                    HAVING COUNT(ms.maternal_supplement_id) = 4";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'calcium':
            // FIX: Count per pregnancy, then aggregate by age bracket
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_supplements ms ON p.pregnancy_id = ms.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE ms.supplement_type = 'Calcium Carbonate'
                    AND ms.date_supp IS NOT NULL 
                    AND ms.date_supp != '0000-00-00'
                    " . getDateCondition($period, 'ms.date_supp', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket
                    HAVING COUNT(ms.maternal_supplement_id) = 3";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'iodine':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN iodine_supplement iod ON p.pregnancy_id = iod.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE iod.iodine_capsule_given = 1
                    AND iod.date_iodine IS NOT NULL 
                    AND iod.date_iodine != '0000-00-00'
                    " . getDateCondition($period, 'iod.date_iodine', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'deworming':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN prenatal_checkup pc ON p.pregnancy_id = pc.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE pc.deworming_date_given IS NOT NULL 
                    AND pc.deworming_date_given != '0000-00-00'
                    " . getDateCondition($period, 'pc.deworming_date_given', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'syphilis_screened':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.syphilis_screening IS NOT NULL
                    AND mds.syphilis_date IS NOT NULL 
                    AND mds.syphilis_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.syphilis_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'syphilis_positive':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.syphilis_screening = 'positive'
                    AND mds.syphilis_date IS NOT NULL 
                    AND mds.syphilis_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.syphilis_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'hepb_screened':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.hepatitis_b_screening IS NOT NULL
                    AND mds.hepatitisB_date IS NOT NULL 
                    AND mds.hepatitisB_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.hepatitisB_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'hepb_positive':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.hepatitis_b_screening = 'positive'
                    AND mds.hepatitisB_date IS NOT NULL 
                    AND mds.hepatitisB_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.hepatitisB_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'hiv_screened':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.hiv_screening IS NOT NULL
                    AND mds.hiv_date IS NOT NULL 
                    AND mds.hiv_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.hiv_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'cbc_tested':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.cbc_hgb_hct_count IS NOT NULL
                    AND mds.cbc_hgb_hct_date IS NOT NULL 
                    AND mds.cbc_hgb_hct_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.cbc_hgb_hct_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'anemia_diagnosed':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.anemia_status = 'with anemia'
                    AND mds.cbc_hgb_hct_date IS NOT NULL 
                    AND mds.cbc_hgb_hct_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.cbc_hgb_hct_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'diabetes_screened':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.gestational_diabetes_screening IS NOT NULL
                    AND mds.gestational_diabetes_date IS NOT NULL 
                    AND mds.gestational_diabetes_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.gestational_diabetes_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        case 'diabetes_positive':
            $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.pregnancy_id) as count
                    FROM pregnancy p
                    INNER JOIN maternal_disease_screening mds ON p.pregnancy_id = mds.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                    WHERE mds.gestational_diabetes_screening = 'positive'
                    AND mds.gestational_diabetes_date IS NOT NULL 
                    AND mds.gestational_diabetes_date != '0000-00-00'
                    " . getDateCondition($period, 'mds.gestational_diabetes_date', $year, $month, $quarter) . "
                    $barangayFilter
                    GROUP BY p.pregnancy_id, pt.age_bracket";
            
            // Count results by age bracket
            $result = $conn->query($sql);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($data[$row['age_bracket']])) {
                        $data[$row['age_bracket']]++;
                    }
                }
            }
            return $data;

        default:
            return $data;
    }

    // For cases that don't return early, execute the query
    $result = $conn->query($sql);

    if ($result === false) {
        error_log("Database error: " . $conn->error . " SQL: " . $sql);
        return $data;
    }

    while ($row = $result->fetch_assoc()) {
        if (isset($data[$row['age_bracket']])) {
            $data[$row['age_bracket']] = (int)$row['count'];
        }
    }

    return $data;
}

function getChildIndicatorData($conn, $barangays, $indicator, $period, $year, $month, $quarter)
{
    $data = ['male' => 0, 'female' => 0];

    $barangayFilter = !empty($barangays) ? "AND pt.health_center_id IN (" . implode(',', array_map('intval', $barangays)) . ")" : "";

    switch ($indicator) {
        case 'cpab':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT i.patient_id) as count
                    FROM infant i
                    INNER JOIN patient pt ON i.patient_id = pt.patient_id
                    WHERE i.cpab_tt_status IS NOT NULL
                    AND i.cpab_tt_date IS NOT NULL 
                    AND i.cpab_tt_date != '0000-00-00'
                    " . getDateCondition($period, 'i.cpab_tt_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'bcg':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT b.patient_id) as count
                    FROM bcg b
                    INNER JOIN infant i ON b.patient_id = i.patient_id
                    INNER JOIN patient pt ON b.patient_id = pt.patient_id
                    WHERE b.bcg_check = 1 
                    AND b.bcg_date IS NOT NULL 
                    AND b.bcg_date != '0000-00-00'
                    " . getDateCondition($period, 'b.bcg_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'hepb_24h':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT h.patient_id) as count
                    FROM hepab h
                    INNER JOIN infant i ON h.patient_id = i.patient_id
                    INNER JOIN patient pt ON h.patient_id = pt.patient_id
                    WHERE h.hepaB_day = 'w/in 24 hours'
                    AND h.hepaB_date IS NOT NULL 
                    AND h.hepaB_date != '0000-00-00'
                    " . getDateCondition($period, 'h.hepaB_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

       case 'hepb_after_24h':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT h.patient_id) as count
                    FROM hepab h
                    INNER JOIN infant i ON h.patient_id = i.patient_id
                    INNER JOIN patient pt ON h.patient_id = pt.patient_id
                    WHERE h.hepaB_day = 'More than 24 hours'
                    AND h.hepaB_date IS NOT NULL 
                    AND h.hepaB_date != '0000-00-00'
                    " . getDateCondition($period, 'h.hepaB_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'pentavalent_1':
        case 'pentavalent_2':
        case 'pentavalent_3':
            $penta_num = str_replace('pentavalent_', '', $indicator);
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT pv.patient_id) as count
                    FROM pentavalent pv
                    INNER JOIN infant i ON pv.patient_id = i.patient_id
                    INNER JOIN patient pt ON pv.patient_id = pt.patient_id
                    WHERE pv.pentavalent_type = 'Pentavalent $penta_num'
                    AND pv.pentavalent_date IS NOT NULL 
                    AND pv.pentavalent_date != '0000-00-00'
                    " . getDateCondition($period, 'pv.pentavalent_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'opv_1':
        case 'opv_2':
        case 'opv_3':
            $opv_num = str_replace('opv_', '', $indicator);
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT o.patient_id) as count
                    FROM opv o
                    INNER JOIN infant i ON o.patient_id = i.patient_id
                    INNER JOIN patient pt ON o.patient_id = pt.patient_id
                    WHERE o.opv_type = 'Opv $opv_num'
                    AND o.opv_date IS NOT NULL 
                    AND o.opv_date != '0000-00-00'
                    " . getDateCondition($period, 'o.opv_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'ipv_1':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT ipv.patient_id) as count
                    FROM ipv
                    INNER JOIN infant i ON ipv.patient_id = i.patient_id
                    INNER JOIN patient pt ON ipv.patient_id = pt.patient_id
                    WHERE ipv.ipv_1 = 1
                    AND ipv.ipv_date IS NOT NULL 
                    AND ipv.ipv_date != '0000-00-00'
                    " . getDateCondition($period, 'ipv.ipv_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'pcv_1':
        case 'pcv_2':
        case 'pcv_3':
            $pcv_num = str_replace('pcv_', '', $indicator);
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT pc.patient_id) as count
                    FROM pcv pc
                    INNER JOIN infant i ON pc.patient_id = i.patient_id
                    INNER JOIN patient pt ON pc.patient_id = pt.patient_id
                    WHERE pc.pcv_type = 'PCV $pcv_num'
                    AND pc.pcv_date IS NOT NULL 
                    AND pc.pcv_date != '0000-00-00'
                    " . getDateCondition($period, 'pc.pcv_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'mcv_1':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT m.patient_id) as count
                    FROM mcv m
                    INNER JOIN infant i ON m.patient_id = i.patient_id
                    INNER JOIN patient pt ON m.patient_id = pt.patient_id
                    WHERE m.mcv_type = 'MCV1 (AMV)'
                    AND m.mcv_date IS NOT NULL 
                    AND m.mcv_date != '0000-00-00'
                    " . getDateCondition($period, 'm.mcv_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'mcv_2':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT m.patient_id) as count
                    FROM mcv m
                    INNER JOIN infant i ON m.patient_id = i.patient_id
                    INNER JOIN patient pt ON m.patient_id = pt.patient_id
                    WHERE m.mcv_type = 'MCV2 (MMR)'
                    AND m.mcv_date IS NOT NULL 
                    AND m.mcv_date != '0000-00-00'
                    " . getDateCondition($period, 'm.mcv_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'fic':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT f.patient_id) as count
                    FROM fic f
                    INNER JOIN infant i ON f.patient_id = i.patient_id
                    INNER JOIN patient pt ON f.patient_id = pt.patient_id
                    WHERE f.fic_check = 1
                    AND f.fic_date IS NOT NULL 
                    AND f.fic_date != '0000-00-00'
                    " . getDateCondition($period, 'f.fic_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        default:
            return $data;
    }

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $data[$row['sex']] = $row['count'];
    }

    return $data;
}

function getNutritionIndicatorData($conn, $barangays, $indicator, $period, $year, $month, $quarter)
{
    $data = ['male' => 0, 'female' => 0];

    $barangayFilter = !empty($barangays) ? "AND pt.health_center_id IN (" . implode(',', array_map('intval', $barangays)) . ")" : "";

    switch ($indicator) {
        case 'bf_initiated':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT ppc.checkup_id) as count
                    FROM post_partum_checkup ppc
                    INNER JOIN pregnancy p ON ppc.pregnancy_id = p.pregnancy_id
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id
                    LEFT JOIN delivery d ON p.pregnancy_id = d.pregnancy_id
                    LEFT JOIN infant i ON d.delivery_id = i.delivery_id
                    WHERE ppc.breastfeeding_date IS NOT NULL 
                    AND ppc.breastfeeding_date != '0000-00-00'
                    " . getDateCondition($period, 'ppc.breastfeeding_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'lbw_iron':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT ir.patient_id) as count
                    FROM iron_infant ir
                    INNER JOIN infant i ON ir.patient_id = i.patient_id
                    INNER JOIN patient pt ON ir.patient_id = pt.patient_id
                    WHERE ir.iron_date IS NOT NULL 
                    AND ir.iron_date != '0000-00-00'
                    AND i.birth_weight < 2500
                    " . getDateCondition($period, 'ir.iron_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'ebf_6month':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT eb.patient_id) as count
                    FROM infant_exclusively_breastfed eb
                    INNER JOIN infant i ON eb.patient_id = i.patient_id
                    INNER JOIN patient pt ON eb.patient_id = pt.patient_id
                    WHERE eb.month_check = '6th Month'
                    AND eb.month_date IS NOT NULL 
                    AND eb.month_date != '0000-00-00'
                    " . getDateCondition($period, 'eb.month_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;


        case 'compl_feeding_6month':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT cf.patient_id) as count
                    FROM infant_complementary_feeding cf
                    INNER JOIN infant i ON cf.patient_id = i.patient_id
                    INNER JOIN patient pt ON cf.patient_id = pt.patient_id
                    WHERE cf.complementary_month_check = '6th Month'
                    AND cf.complementary_month_date IS NOT NULL 
                    AND cf.complementary_month_date != '0000-00-00'
                    " . getDateCondition($period, 'cf.complementary_month_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'compl_no_bf':
            $sql = "SELECT i.sex, COUNT(DISTINCT cf.patient_id) as count
                    FROM infant_complementary_feeding cf
                    INNER JOIN infant i ON cf.patient_id = i.patient_id
                    INNER JOIN patient pt ON i.patient_id = pt.patient_id
                    LEFT JOIN infant_exclusively_breastfed eb ON cf.patient_id = eb.patient_id AND eb.month_check = '6th Month'
                    WHERE cf.complementary_month_check = '6th Month'
                    AND cf.complementary_month_date IS NOT NULL
                    AND cf.complementary_month_date != '0000-00-00'
                    " . getDateCondition($period, 'cf.complementary_month_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND eb.patient_id IS NULL
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;



        case 'vit_a_6_11m':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT va.patient_id) as count
                    FROM vitamin_a_infant va
                    INNER JOIN infant i ON va.patient_id = i.patient_id
                    INNER JOIN patient pt ON va.patient_id = pt.patient_id
                    WHERE va.vitamin_type = 'Vitamin A (6-11 Months)'
                    AND va.vitamin_date IS NOT NULL 
                    AND va.vitamin_date != '0000-00-00'
                    " . getDateCondition($period, 'va.vitamin_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'mnp_6_11m':
            $sql = "SELECT i.sex,
                           COUNT(DISTINCT mnp.patient_id) as count
                    FROM mnp
                    INNER JOIN infant i ON mnp.patient_id = i.patient_id
                    INNER JOIN patient pt ON mnp.patient_id = pt.patient_id
                    WHERE mnp.mnp_type = 'MNP (6-11 Months)'
                    AND mnp.mnp_date IS NOT NULL 
                    AND mnp.mnp_date != '0000-00-00'
                    " . getDateCondition($period, 'mnp.mnp_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        case 'deworming':
            $sql = "SELECT i.sex,
                        COUNT(DISTINCT dw.patient_id) as count
                    FROM deworming_infant dw
                    INNER JOIN infant i ON dw.patient_id = i.patient_id
                    INNER JOIN patient pt ON dw.patient_id = pt.patient_id
                    WHERE dw.deworming_check = 1
                    AND dw.deworming_date IS NOT NULL 
                    AND dw.deworming_date != '0000-00-00'
                    " . getDateCondition($period, 'dw.deworming_date', $year, $month, $quarter) . "
                    $barangayFilter
                    AND i.sex IN ('male', 'female')
                    GROUP BY i.sex";
            break;

        default:
            return $data;
    }

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $data[$row['sex']] = $row['count'];
    }

    return $data;
}

function getDateCondition($period, $dateField, $year, $month, $quarter)
{
    switch ($period) {
        case 'monthly':
            $startDate = "$year-$month-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            return "AND $dateField BETWEEN '$startDate' AND '$endDate'";

        case 'quarterly':
            switch ($quarter) {
                case 1:
                    $startDate = "$year-01-01";
                    $endDate = "$year-03-31";
                    break;
                case 2:
                    $startDate = "$year-04-01";
                    $endDate = "$year-06-30";
                    break;
                case 3:
                    $startDate = "$year-07-01";
                    $endDate = "$year-09-30";
                    break;
                case 4:
                    $startDate = "$year-10-01";
                    $endDate = "$year-12-31";
                    break;
                default:
                    $startDate = "$year-01-01";
                    $endDate = "$year-03-31";
            }
            return "AND $dateField BETWEEN '$startDate' AND '$endDate'";

        case 'yearly':
            $startDate = "$year-01-01";
            $endDate = "$year-12-31";
            return "AND $dateField BETWEEN '$startDate' AND '$endDate'";

        default:
        
    }
}

function generateTitle($reportType, $period, $month, $year, $quarter, $conn, $barangays)
{
    $periodText = '';
    $monthNames = [
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December'
    ];

    switch ($period) {
        case 'monthly':
            $periodText = $monthNames[$month] . ' ' . $year;
            break;
        case 'quarterly':
            $quarterText = ['1st', '2nd', '3rd', '4th'][$quarter - 1];
            $periodText = $quarterText . ' Quarter ' . $year;
            break;
        case 'yearly':
            $periodText = 'Year ' . $year;
            break;
        default:
            $periodText = $monthNames[$month] . ' ' . $year;
    }

    $locationText = '';
    if (!empty($barangays)) {
        if (count($barangays) === 1) {
            $barangayId = $barangays[0];
            $sql = "SELECT barangay_name, municipality, province FROM health_center WHERE health_center_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $barangayId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $locationText = " - " . $row['barangay_name'] . ", " . $row['municipality'] . ", " . $row['province'];
            }
        } else {
            $locationText = " - Selected Barangays";
        }
    } else {
        $locationText = " - All Barangays";
    }

    return "$reportType for $periodText$locationText";
}

$conn->close();
ob_end_flush();
?>