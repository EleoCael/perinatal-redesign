<?php
// report_functions_admin.php
// Shared data-fetching functions for the admin multi-barangay reports.
// Extracted from the original generateAPI.php (admin). The three top-level
// report functions now RETURN ['data'=>..., 'title'=>...] instead of echoing
// JSON directly, so both generateAPI.php (JSON) and generate_pdf.php (PDF)
// can reuse them.

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

    // Indicator definitions now come from the SAME report_indicators table used
    // by the midwife-side report — editing an indicator in the admin "Manage
    // Indicators" page updates both reports identically and stays accurate
    // per barangay, since the underlying per-barangay queries are unchanged.
    $indicators = getActiveIndicators($conn, 'prenatal');

    $mainCounter = 0;
    $letterIndex = 0;

    foreach ($indicators as $indicator) {
        $isIndent = (bool) $indicator['is_indent'];

        if ($isIndent) {
            $letterIndex++;
            $numberPrefix = '&nbsp;&nbsp;' . chr(96 + $letterIndex) . '.';
        } else {
            $mainCounter++;
            $letterIndex = 0;
            $numberPrefix = $mainCounter . '.';
        }

        $threshold = $indicator['has_threshold'] ? (int) $indicator['threshold_value'] : null;
        $labelText = str_replace('{threshold}', (string) $threshold, $indicator['label_template']);
        $label = "{$numberPrefix} {$labelText} - Total";

        $data = getPrenatalIndicatorData($conn, $barangays, $indicator['indicator_key'], $period, $year, $month, $quarter, $threshold);

        $reportData[] = [
            'indicator' => $label,
            'indent' => $isIndent,
            'age_10_14' => $data['10-14'] ?? 0,
            'age_15_19' => $data['15-19'] ?? 0,
            'age_20_49' => $data['20-49'] ?? 0,
            'grand_total' => ($data['10-14'] ?? 0) + ($data['15-19'] ?? 0) + ($data['20-49'] ?? 0)
        ];
    }

    return ['data' => $reportData, 'title' => $title];
}

function getActiveIndicators($conn, $reportType) {
    $sql = "SELECT indicator_key, label_template, is_indent, has_threshold, threshold_value
            FROM report_indicators
            WHERE report_type = ? AND is_active = 1
            ORDER BY display_order ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $reportType);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getChildReport($conn, $barangays, $period, $month, $year, $quarter)
{
    $title = generateTitle('Infant Care & Immunization Report', $period, $month, $year, $quarter, $conn, $barangays);
    $reportData = [];

    $stmt = $conn->prepare("SELECT indicator_key, label_template, threshold_value 
                             FROM report_indicators 
                             WHERE report_type = 'child' AND is_active = 1 
                             ORDER BY display_order ASC");
    $stmt->execute();
    $indicators = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($indicators as $indicator) {
        $label = str_replace('{threshold}', $indicator['threshold_value'] ?? '', $indicator['label_template']);
        $data = getChildIndicatorData($conn, $barangays, $indicator['indicator_key'], $period, $year, $month, $quarter);

        $reportData[] = [
            'indicator' => $label,
            'male' => $data['male'] ?? 0,
            'female' => $data['female'] ?? 0,
            'total' => ($data['male'] ?? 0) + ($data['female'] ?? 0)
        ];
    }

    return ['data' => $reportData, 'title' => $title];
}

function getNutritionReport($conn, $barangays, $period, $month, $year, $quarter)
{
    $title = generateTitle('Nutrition Services Report', $period, $month, $year, $quarter, $conn, $barangays);
    $reportData = [];

    $stmt = $conn->prepare("SELECT indicator_key, label_template, threshold_value 
                             FROM report_indicators 
                             WHERE report_type = 'nutrition' AND is_active = 1 
                             ORDER BY display_order ASC");
    $stmt->execute();
    $indicators = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($indicators as $indicator) {
        $label = str_replace('{threshold}', $indicator['threshold_value'] ?? '', $indicator['label_template']);
        $data = getNutritionIndicatorData($conn, $barangays, $indicator['indicator_key'], $period, $year, $month, $quarter);

        $reportData[] = [
            'indicator' => $label,
            'male' => $data['male'] ?? 0,
            'female' => $data['female'] ?? 0,
            'total' => ($data['male'] ?? 0) + ($data['female'] ?? 0)
        ];
    }

    return ['data' => $reportData, 'title' => $title];
}

function getPrenatalIndicatorData($conn, $barangays, $indicator, $period, $year, $month, $quarter, $threshold = null)
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
        // Attributed to the month the pregnancy ENDED (date_terminated), not
        // to whichever month individual checkups happened 
        $sql = "SELECT pt.age_bracket, COUNT(DISTINCT p.patient_id) as count
                FROM pregnancy p
                INNER JOIN prenatal_checkup pc ON p.pregnancy_id = pc.pregnancy_id
                INNER JOIN patient pt ON p.patient_id = pt.patient_id
                INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                WHERE p.date_terminated IS NOT NULL 
                AND p.date_terminated != '0000-00-00'
                AND pc.checkup_date IS NOT NULL
                AND pc.checkup_date != '0000-00-00'
                " . getDateCondition($period, 'p.date_terminated', $year, $month, $quarter) . "
                $barangayFilter
                GROUP BY pt.age_bracket, p.patient_id
                HAVING COUNT(pc.checkup_id) >= " . intval($threshold ?? 4) . "";
        
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
            // Counted as soon as she has exactly Td1+Td2 — attributed to the
            // date of the later of the two doses (when she "completed" it),
            // NOT delivery date. No date_terminated requirement anymore.
            $tdDoses2 = intval($threshold ?? 2);
            $sql = "SELECT age_bracket
                    FROM (
                        SELECT p.pregnancy_id, pt.age_bracket,
                            MAX(mi.immunization_date) AS completion_date
                        FROM pregnancy p
                        INNER JOIN maternal_immunization mi ON p.pregnancy_id = mi.pregnancy_id
                        INNER JOIN patient pt ON p.patient_id = pt.patient_id
                        INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                        WHERE mi.immunization_date IS NOT NULL
                        AND mi.immunization_date != '0000-00-00'
                        $barangayFilter
                        GROUP BY p.pregnancy_id, pt.age_bracket
                        HAVING COUNT(DISTINCT mi.immunization_type) = {$tdDoses2}
                        AND SUM(CASE WHEN mi.immunization_type IN ('Td1/TT1', 'Td2/TT2') THEN 1 ELSE 0 END) = {$tdDoses2}
                    ) completed
                    WHERE 1=1 " . getDateCondition($period, 'completion_date', $year, $month, $quarter);
            // existing fetch loop below stays exactly the same
            break;

        case 'td_3plus_doses':
            // "At least N doses" — completion date is the date of her Nth dose
            // chronologically, found via ROW_NUMBER, so extra doses after
            // hitting the threshold don't shift her completion date later.
            $tdDoses3 = intval($threshold ?? 3);
            $sql = "SELECT age_bracket
                    FROM (
                        SELECT pregnancy_id, age_bracket, immunization_date,
                            ROW_NUMBER() OVER (PARTITION BY pregnancy_id ORDER BY immunization_date) AS dose_num
                        FROM (
                            SELECT p.pregnancy_id, pt.age_bracket, mi.immunization_date
                            FROM pregnancy p
                            INNER JOIN maternal_immunization mi ON p.pregnancy_id = mi.pregnancy_id
                            INNER JOIN patient pt ON p.patient_id = pt.patient_id
                            INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                            WHERE mi.immunization_type IN ('Td1/TT1','Td2/TT2','Td3/TT3','Td4/TT4','Td5/TT5')
                            AND mi.immunization_date IS NOT NULL
                            AND mi.immunization_date != '0000-00-00'
                            $barangayFilter
                        ) doses
                    ) ranked
                    WHERE dose_num = {$tdDoses3} " . getDateCondition($period, 'immunization_date', $year, $month, $quarter);
            break;

        case 'iron_folic':
            $ironDoses = intval($threshold ?? 4);
            $sql = "SELECT age_bracket
                    FROM (
                        SELECT pregnancy_id, age_bracket, date_supp,
                            ROW_NUMBER() OVER (PARTITION BY pregnancy_id ORDER BY date_supp) AS dose_num
                        FROM (
                            SELECT p.pregnancy_id, pt.age_bracket, ms.date_supp
                            FROM pregnancy p
                            INNER JOIN maternal_supplements ms ON p.pregnancy_id = ms.pregnancy_id
                            INNER JOIN patient pt ON p.patient_id = pt.patient_id
                            INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                            WHERE ms.supplement_type = 'Iron Sulfate w/Folic Acid'
                            AND ms.date_supp IS NOT NULL
                            AND ms.date_supp != '0000-00-00'
                            $barangayFilter
                        ) doses
                    ) ranked
                    WHERE dose_num = {$ironDoses} " . getDateCondition($period, 'date_supp', $year, $month, $quarter);
            break;

        case 'calcium':
            $calciumDoses = intval($threshold ?? 3);
            $sql = "SELECT age_bracket
                    FROM (
                        SELECT pregnancy_id, age_bracket, date_supp,
                            ROW_NUMBER() OVER (PARTITION BY pregnancy_id ORDER BY date_supp) AS dose_num
                        FROM (
                            SELECT p.pregnancy_id, pt.age_bracket, ms.date_supp
                            FROM pregnancy p
                            INNER JOIN maternal_supplements ms ON p.pregnancy_id = ms.pregnancy_id
                            INNER JOIN patient pt ON p.patient_id = pt.patient_id
                            INNER JOIN health_center hc ON pt.health_center_id = hc.health_center_id
                            WHERE ms.supplement_type = 'Calcium Carbonate'
                            AND ms.date_supp IS NOT NULL
                            AND ms.date_supp != '0000-00-00'
                            $barangayFilter
                        ) doses
                    ) ranked
                    WHERE dose_num = {$calciumDoses} " . getDateCondition($period, 'date_supp', $year, $month, $quarter);
            break;

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
            $startDate = "$year-" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . "-01";
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
        case 'annual':
            $startDate = "$year-01-01";
            $endDate = "$year-12-31";
            return "AND $dateField BETWEEN '$startDate' AND '$endDate'";

        default:
            return "";
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

    $monthKey = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

    switch ($period) {
        case 'monthly':
            $periodText = $monthNames[$monthKey] . ' ' . $year;
            break;
        case 'quarterly':
            $quarterText = ['1st', '2nd', '3rd', '4th'][$quarter - 1];
            $periodText = $quarterText . ' Quarter ' . $year;
            break;
        case 'yearly':
        case 'annual':
            $periodText = 'Year ' . $year;
            break;
        default:
            $periodText = $monthNames[$monthKey] . ' ' . $year;
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
        // List the actual barangay names instead of a generic "Selected Barangays" label.
        $barangayIds = array_filter(array_map('intval', $barangays));
        $placeholders = implode(',', array_fill(0, count($barangayIds), '?'));
        $types = str_repeat('i', count($barangayIds));

        $sql = "SELECT barangay_name FROM health_center WHERE health_center_id IN ($placeholders) ORDER BY barangay_name";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$barangayIds);
        $stmt->execute();
        $result = $stmt->get_result();

        $names = [];
        while ($row = $result->fetch_assoc()) {
            $names[] = $row['barangay_name'];
        }

        // Check whether the selection actually covers every barangay
        $totalResult = $conn->query("SELECT COUNT(*) as total FROM health_center");
        $totalBarangays = $totalResult->fetch_assoc()['total'];

        if (count($names) >= $totalBarangays) {
            $locationText = " - All Barangays";
        } else {
            $locationText = " - " . implode(', ', $names);
        }
    }
} else {
    $locationText = " - All Barangays";
}

    return "$reportType for $periodText$locationText";
}