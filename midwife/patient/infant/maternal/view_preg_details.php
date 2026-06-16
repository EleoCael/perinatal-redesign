<?php

require_once "../../../module/db.config.php";
session_start();

if (!isset($_SESSION['health_center_id'])) {
    die("Access denied: No health center assigned");
}

function insertValues($array, $key, $default = 'N/A')
{
    if (!isset($array[$key])) {
        return $default;
    }
    $value = $array[$key];
    if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00' || $value === null) {
        return $default;
    } else {
        return $value;
    }
}
function displayCheckbox($array, $key, $default = 'N/A')
{
    if (isset($array[$key]) && $array[$key] !== null) {
        return $array[$key] == 1 ? 'Yes' : 'No';
    } else {
        return $default;
    }
}

if (isset($_POST['pregnancy_id'])) {
    $pregnancy_id = $_POST['pregnancy_id'];
    $health_center_id = $_SESSION['health_center_id'];
    $output = '';

    //pregnancy table
    $pregnancy_query = "SELECT p.* FROM pregnancy p 
                       INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                       WHERE p.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_pregnancy = $conn->prepare($pregnancy_query);
    $stmt_pregnancy->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_pregnancy->execute();
    $pregnancy_result = $stmt_pregnancy->get_result();
    $pregnancy = $pregnancy_result->fetch_assoc();

    $lmp = insertValues($pregnancy, 'lmp');
    $edc = insertValues($pregnancy, 'edc');
    $gravidity = insertValues($pregnancy, 'gravidity');
    $parity = insertValues($pregnancy, 'parity');

    //Pregnancy outcome
    $query = "SELECT p.outcome, p.date_terminated, p.sex 
             FROM pregnancy p 
             INNER JOIN patient pt ON p.patient_id = pt.patient_id 
             WHERE p.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_query = $conn->prepare($query);
    $stmt_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_query->execute();
    $result = $stmt_query->get_result();
    $preg_outcome = $result->fetch_assoc() ?? [];
    $date_terminated = insertValues($preg_outcome, 'date_terminated');
    $outcome = insertValues($preg_outcome, 'outcome');
    $sex = insertValues($preg_outcome, 'sex');
    //Pregnancy outcome

    //delivery table - >Birth Information
    $birth_query = "SELECT d.delivery_type, d.birth_weight_classification, d.birth_weight, d.birth_attendant 
                   FROM delivery d 
                   INNER JOIN pregnancy p ON d.pregnancy_id = p.pregnancy_id 
                   INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                   WHERE d.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_birth_query = $conn->prepare($birth_query);
    $stmt_birth_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_birth_query->execute();
    $birth_result = $stmt_birth_query->get_result();
    $birth_info = $birth_result->fetch_assoc() ?? [];
    $delivery_type = insertValues($birth_info, 'delivery_type');
    $weight_class = insertValues($birth_info, 'birth_weight_classification');
    $birth_weight = insertValues($birth_info, 'birth_weight');
    $birth_attendant = insertValues($birth_info, 'birth_attendant');
    //delivery table - >Birth Information

    //delivery table - >Place of Delivery--Health Facility
    $place_query = "SELECT d.health_facility_type, d.health_facility_name, d.bemonc_cemonc_capable, d.ownership 
                   FROM delivery d 
                   INNER JOIN pregnancy p ON d.pregnancy_id = p.pregnancy_id 
                   INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                   WHERE d.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_place_query = $conn->prepare($place_query);
    $stmt_place_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_place_query->execute();
    $place_result = $stmt_place_query->get_result();
    $place_info = $place_result->fetch_assoc() ?? [];
    $facility_type = insertValues($place_info, 'health_facility_type');
    $facility_name = insertValues($place_info, 'health_facility_name');
    $ownership = insertValues($place_info, 'ownership');
    $bemonc_cemonc_capable = displayCheckbox($place_info, 'bemonc_cemonc_capable');
    //delivery table - >Place of Delivery-Health Facility

    //delivery table - >Place of Delivery->Non Health Facility
    $place_non_health_query = "SELECT d.non_health_facility_type, d.non_health_facility_name, d.remarks 
                              FROM delivery d 
                              INNER JOIN pregnancy p ON d.pregnancy_id = p.pregnancy_id 
                              INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                              WHERE d.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_place_non_health = $conn->prepare($place_non_health_query);
    $stmt_place_non_health->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_place_non_health->execute();
    $place_non_health_result = $stmt_place_non_health->get_result();
    $place_non_health = $place_non_health_result->fetch_assoc() ?? [];

    $non_facility_type = insertValues($place_non_health, 'non_health_facility_type');
    $non_facility_name = insertValues($place_non_health, 'non_health_facility_name');
    //$remarks = insertValues($place_non_health, 'remarks');
    //delivery table - >Place of Delivery->Non Health Facility


    //prenatal checkup table
    if ($pregnancy && $pregnancy['pregnancy_id']) {

        $prenatal_query = "SELECT pnc.* FROM prenatal_checkup pnc 
                        INNER JOIN pregnancy p ON pnc.pregnancy_id = p.pregnancy_id 
                        INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                        WHERE pnc.pregnancy_id = ? AND pt.health_center_id = ? 
                        ORDER BY pnc.checkup_date";
        $stmt_prenatal = $conn->prepare($prenatal_query);
        $stmt_prenatal->bind_param("ii", $pregnancy_id, $health_center_id);
        $stmt_prenatal->execute();
        $prenatal_result = $stmt_prenatal->get_result();
        $prenatal = $prenatal_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $prenatal = [];
    }
    $prenatal_html = '';

    foreach ($prenatal as $checkup) {
        $checkup_id = $checkup['checkup_id']; // Get the primary key
        $trimester = insertValues($checkup, 'trimester');
        $checkup_date = insertValues($checkup, 'checkup_date');

        $prenatal_html .= "
            <div class='checkup-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
                <div>
                    <p style='margin: 0;'><strong>Trimester:</strong> {$trimester}</p>
                    <p style='margin: 0;'><strong>Date:</strong> {$checkup_date}</p>
                </div>
                <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_checkup_btn' 
                        data-checkup-id='{$checkup_id}' 
                        data-preg-id='{$pregnancy_id}'
                        data-trimester='{$trimester}'
                        data-checkup-date='{$checkup_date}'
                        title='Edit this checkup'>
                    <i class='bi bi-pencil-fill'></i> Edit
                </button>
            </div>
        ";
    }

    $first_checkup = reset($prenatal);
    if ($first_checkup === false) {
        $first_checkup = [];
    }
    //prenatal checkup table

    //prenatal other details
    $bmi_query = "SELECT pnc.bmi_class, pnc.bmi, pnc.deworming_status, pnc.deworming_date_given 
             FROM prenatal_checkup pnc 
             INNER JOIN pregnancy p ON pnc.pregnancy_id = p.pregnancy_id 
             INNER JOIN patient pt ON p.patient_id = pt.patient_id 
             WHERE pnc.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_bmi = $conn->prepare($bmi_query);
    $stmt_bmi->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_bmi->execute();
    $bmi_result = $stmt_bmi->get_result();
    $bmi_info = $bmi_result->fetch_assoc() ?? [];

    $bmi_class = insertValues($bmi_info, 'bmi_class');
    $bmi = insertValues($bmi_info, 'bmi');
    $deworming_stat = insertValues($bmi_info, 'deworming_status');
    $deworming_date = insertValues($bmi_info, 'deworming_date_given');
    //prenatal other details

    //maternal disease screening table
    $disease_query = "SELECT mds.syphilis_date, mds.syphilis_screening, mds.syphilis_screening_remarks,
            mds.hepatitis_b_screening, mds.hepatitisB_date, mds.hepatitis_b_screening_remarks 
            FROM maternal_disease_screening mds 
            INNER JOIN pregnancy p ON mds.pregnancy_id = p.pregnancy_id 
            INNER JOIN patient pt ON p.patient_id = pt.patient_id 
            WHERE mds.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_disease_query = $conn->prepare($disease_query);
    $stmt_disease_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_disease_query->execute();
    $disease_result = $stmt_disease_query->get_result();
    $disease_info = $disease_result->fetch_assoc() ?? [];

    $syphilis_date = insertValues($disease_info, 'syphilis_date');
    $syphilis_screening = insertValues($disease_info, 'syphilis_screening');
    $syphilis_remarks = insertValues($disease_info, 'syphilis_screening_remarks');
    $hepatitisB_date = insertValues($disease_info, 'hepatitisB_date');
    $hepatitis_b_screening = insertValues($disease_info, 'hepatitis_b_screening');
    $hepatitis_b_remarks = insertValues($disease_info, 'hepatitis_b_screening_remarks');
    //maternal disease screening table

    //maternal disease screening table
    $hiv_query = "SELECT mds.hiv_screening, mds.hiv_date, mds.hiv_screening_remarks 
                FROM maternal_disease_screening mds 
                INNER JOIN pregnancy p ON mds.pregnancy_id = p.pregnancy_id 
                INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                WHERE mds.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_hiv_query = $conn->prepare($hiv_query);
    $stmt_hiv_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_hiv_query->execute();
    $hiv_result = $stmt_hiv_query->get_result();
    $hiv_info = $hiv_result->fetch_assoc() ?? [];

    $hiv_date = insertValues($hiv_info, 'hiv_date');
    $hiv_screening = insertValues($hiv_info, 'hiv_screening');
    $hiv_remarks = insertValues($hiv_info, 'hiv_screening_remarks');

    //maternal laboratory screening table
    $laboratory_query = "SELECT mds.gestational_diabetes_date, mds.gestational_diabetes_screening, mds.diabetes_remarks 
                    FROM maternal_disease_screening mds 
                    INNER JOIN pregnancy p ON mds.pregnancy_id = p.pregnancy_id 
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                    WHERE mds.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_laboratory_query = $conn->prepare($laboratory_query);
    $stmt_laboratory_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_laboratory_query->execute();
    $laboratory_result = $stmt_laboratory_query->get_result();
    $laboratory_info = $laboratory_result->fetch_assoc() ?? [];

    $gestational_date = insertValues($laboratory_info, 'gestational_diabetes_date');
    $gestational_screening = insertValues($laboratory_info, 'gestational_diabetes_screening');
    $gestational_remarks = insertValues($laboratory_info, 'diabetes_remarks');
    //maternal laboratory screening table

    //maternal cbc screening table
    $cbc_query = "SELECT mds.cbc_hgb_hct_date, mds.anemia_status, mds.cbc_hgb_hct_count, mds.anemia_status_remarks 
             FROM maternal_disease_screening mds 
             INNER JOIN pregnancy p ON mds.pregnancy_id = p.pregnancy_id 
             INNER JOIN patient pt ON p.patient_id = pt.patient_id 
             WHERE mds.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_cbc_query = $conn->prepare($cbc_query);
    $stmt_cbc_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_cbc_query->execute();
    $cbc_result = $stmt_cbc_query->get_result();
    $cbc_info = $cbc_result->fetch_assoc() ?? [];

    $cbc_hgb_hct_date = insertValues($cbc_info, 'cbc_hgb_hct_date');
    $anemia_status = insertValues($cbc_info, 'anemia_status');
    $cbc_hgb_hct_count = insertValues($cbc_info, 'cbc_hgb_hct_count');
    $anemia_remarks = insertValues($cbc_info, 'anemia_status_remarks');
    //maternal cbc screening table

    //maternal given iron screening table
    $given_iron_query = "SELECT mds.given_iron, mds.given_iron_date, mds.maternal_screening_remark 
                    FROM maternal_disease_screening mds 
                    INNER JOIN pregnancy p ON mds.pregnancy_id = p.pregnancy_id 
                    INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                    WHERE mds.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_given_iron_query = $conn->prepare($given_iron_query);
    $stmt_given_iron_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_given_iron_query->execute();
    $given_iron_result = $stmt_given_iron_query->get_result();
    $given_iron_info = $given_iron_result->fetch_assoc() ?? [];

    $given_iron = displayCheckbox($given_iron_info, 'given_iron');
    $given_iron_date = insertValues($given_iron_info, 'given_iron_date');
    $maternal_screening_remark = insertValues($given_iron_info, 'maternal_screening_remark');
    //maternal given iron screening table

    //maternal immunization table
    if ($pregnancy && $pregnancy['pregnancy_id']) {
        $immunization_query = "SELECT mi.* FROM maternal_immunization mi 
                          INNER JOIN pregnancy p ON mi.pregnancy_id = p.pregnancy_id 
                          INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                          WHERE mi.pregnancy_id = ? AND pt.health_center_id = ?";
        $stmt_immunization = $conn->prepare($immunization_query);
        $stmt_immunization->bind_param("ii", $pregnancy_id, $health_center_id);
        $stmt_immunization->execute();
        $result_immunization = $stmt_immunization->get_result();
        $immunization = $result_immunization->fetch_all(MYSQLI_ASSOC);
    } else {
        $immunization = [];
    }
    $immunization_html = '';

    foreach ($immunization as $immunization_insert) {
        $maternal_immunization_id = $immunization_insert['maternal_immunization_id'];
        $immunization_type = insertValues($immunization_insert, 'immunization_type');
        $immunization_date = insertValues($immunization_insert, 'immunization_date');

        $immunization_html .= "
            <div class='immunization-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$immunization_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$immunization_date}</p>
            </div>
             <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_immunization_btn' 
                    data-immunization-id='{$maternal_immunization_id}' 
                    data-preg-id='{$pregnancy_id}'
                    data-immunization-type='{$immunization_type}'
                    data-immunization-date='{$immunization_date}'
                    title='Edit this immunization'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div> 
        ";
    }

    $first_mat_immunzation = reset($immunization);
    if ($first_mat_immunzation === false) {
        $first_checkup = [];
    }
    //maternal immunization table

    //fim status
    $fim_query = "SELECT fsm.fim_status 
             FROM fim_status_maternal fsm 
             INNER JOIN pregnancy p ON fsm.pregnancy_id = p.pregnancy_id 
             INNER JOIN patient pt ON p.patient_id = pt.patient_id 
             WHERE fsm.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_fim = $conn->prepare($fim_query);
    $stmt_fim->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_fim->execute();
    $result_fim = $stmt_fim->get_result();
    $fim_row = $result_fim->fetch_assoc();
    $fim_status_value = $fim_row['fim_status'] ?? null;

    if ($fim_status_value === 1) {
        $fim_status_display = "<strong>Status:</strong> Fully Immunized (Yes) <span><i class='bi bi-check-circle-fill text-success'></i></span>";
        $button_text = 'Update Status';
    } elseif ($fim_status_value === 0) {
        $fim_status_display = "<strong>Status:</strong> Not Fully Immunized (No) <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
        $button_text = 'Update Status';
    } else {
        $fim_status_display = "<strong>Status:</strong> N/A";
        $button_text = 'Set Status';
    }

    //fim status

    //maternal supplement

    if ($pregnancy && $pregnancy['pregnancy_id']) {
        $supplement_query = "SELECT ms.* FROM maternal_supplements ms 
                        INNER JOIN pregnancy p ON ms.pregnancy_id = p.pregnancy_id 
                        INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                        WHERE ms.pregnancy_id = ? AND pt.health_center_id = ?
                        ORDER BY ms.date_supp ASC";
        $stmt_supplement = $conn->prepare($supplement_query);
        $stmt_supplement->bind_param("ii", $pregnancy_id, $health_center_id);
        $stmt_supplement->execute();
        $result_supplement = $stmt_supplement->get_result();
        $supplement = $result_supplement->fetch_all(MYSQLI_ASSOC);
    } else {
        $supplement = [];
    }

    $iron_html = '';
    $calcium_html = '';
    foreach ($supplement as $supp) {
        $maternal_supplement_id = $supp['maternal_supplement_id'];
        $type = insertValues($supp, 'supplement_type');
        $trimester = insertValues($supp, 'supp_trimester');
        $date = insertValues($supp, 'date_supp');
        $tablets_given = insertValues($supp, 'supp_tablets_given');

        $supp_html = "
        <div class='supp-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Trimester:</strong> {$trimester}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$date}</p>
                <p style='margin: 0;'><strong>Tablets Given:</strong> {$tablets_given}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_{TYPE}_btn' 
                    data-supplement-id='{$maternal_supplement_id}' 
                    data-preg-id='{$pregnancy_id}'
                    data-trimester='{$trimester}'
                    data-tablets-given='{$tablets_given}'
                    data-date-supp='{$date}'
                    data-supplement-type='{$type}'
                    title='Edit this supplement'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";

        if ($type === 'Iron Sulfate w/Folic Acid') {
            $iron_html .= str_replace('{TYPE}', 'iron', $supp_html);
        } elseif ($type === 'Calcium Carbonate') {
            $calcium_html .= str_replace('{TYPE}', 'calcium', $supp_html);
        }
    }

    $iron_html .= "
        <button class='btn btn-outline-primary w-100 add_iron_btn'
                data-preg-id = '{$pregnancy_id}'
                data-bs-toggle = 'modal'
                data-bs-target = '#addIronModal'>
                <i class='bi bi-plus-lg text-white'></i>Add Iron Supplement
        </button>  
    ";

    $calcium_html .= "
        <button class='btn btn-outline-primary w-100 add_calcium_btn'
                data-preg-id = '{$pregnancy_id}'
                data-bs-toggle = 'modal'
                data-bs-target = '#addCalciumModal'>
                <i class='bi bi-plus-lg text-white'></i>Add Calcium Supplement
        </button>
    ";

    //maternal supplement

    //iodine supplement
    $iodine_query = "SELECT isup.iodine_capsule_given, isup.date_iodine 
                FROM iodine_supplement isup 
                INNER JOIN pregnancy p ON isup.pregnancy_id = p.pregnancy_id 
                INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                WHERE isup.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_iodine = $conn->prepare($iodine_query);
    $stmt_iodine->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_iodine->execute();
    $result_iodine = $stmt_iodine->get_result();
    $iodine_row = $result_iodine->fetch_assoc();
    $iodine_status = $iodine_row['iodine_capsule_given'] ?? null;
    //$iodine_date = insertValues($iodine_row, 'date_iodine');

    //$iodine_status_display = ""; 
    $iodine_date_display = "";

    if ($iodine_status === 1) {
        $iodine_date = insertValues($iodine_row, 'date_iodine');
        $iodine_status_display = "<strong>Status:</strong> Yes <span><i class='bi bi-check-circle-fill text-success'></i></span>";
        $iodine_date_display = "<strong>Date:</strong> {$iodine_date}";
        $button_text = "Update Status";
    } elseif ($iodine_status === 0) {
        $iodine_status_display = "<strong>Status:</strong> No <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
        $iodine_date_display = "<strong>Date:</strong> N/A";
        $button_text = "Update Status";
    } else {
        $iodine_status_display = "<strong>Status:</strong> N/A";
        $iodine_date_display = "<strong>Date:</strong> N/A";
        $button_text = "Set Status";
    }
    //iodine supplement

    //postpartum checkup table
    if ($pregnancy && $pregnancy['pregnancy_id']) {
        $postpartum_query = "SELECT ppc.* FROM post_partum_checkup ppc
          INNER JOIN pregnancy p ON ppc.pregnancy_id = p.pregnancy_id 
          INNER JOIN patient pt ON p.patient_id = pt.patient_id 
          WHERE ppc.pregnancy_id = ? AND pt.health_center_id = ? 
          ORDER BY ppc.post_checkup_date";
        $stmt_postpartum = $conn->prepare($postpartum_query);
        $stmt_postpartum->bind_param("ii", $pregnancy_id, $health_center_id);
        $stmt_postpartum->execute();
        $result_postpartum = $stmt_postpartum->get_result();
        $postpartum = $result_postpartum->fetch_all(MYSQLI_ASSOC);
    } else {
        $postpartum = [];
    }
    $post_checkup_html = '';

    foreach ($postpartum as $postpartum_checkup) {
        $checkup_id = $postpartum_checkup['checkup_id'];
        $checkup_visit = insertValues($postpartum_checkup, 'checkup_visit');
        $post_checkup_date = insertValues($postpartum_checkup, 'post_checkup_date');

        $post_checkup_html .= "
            <div class='post-checkup-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Visits:</strong> {$checkup_visit}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$post_checkup_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_post_checkup_btn' 
                    data-post-checkup-id='{$checkup_id}' 
                    data-preg-id='{$pregnancy_id}'
                    data-checkup-visit='{$checkup_visit}'
                    data-post-checkup-date='{$post_checkup_date}'
                    title='Edit this checkup'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>          
        ";
    }
   
    //postpartum checkup table

    //postpartum other details 
    $post_query = "SELECT ppc.post_delivery_date, ppc.post_delivery_time, ppc.breastfeeding_date, ppc.breastfeeding_time 
              FROM post_partum_checkup ppc 
              INNER JOIN pregnancy p ON ppc.pregnancy_id = p.pregnancy_id 
              INNER JOIN patient pt ON p.patient_id = pt.patient_id 
              WHERE ppc.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_post_query = $conn->prepare($post_query);
    $stmt_post_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_post_query->execute();
    $post_result = $stmt_post_query->get_result();
    $post_info = $post_result->fetch_assoc() ?? [];

    $post_delivery_date = insertValues($post_info, 'post_delivery_date');
    $post_delivery_time = insertValues($post_info, 'post_delivery_time');
    $breastfeeding_date = insertValues($post_info, 'breastfeeding_date');
    $breastfeeding_time = insertValues($post_info, 'breastfeeding_time');
    //postpartum other details 

    //postpartum supplement table
    if ($pregnancy && $pregnancy['pregnancy_id']) {
        $postpartum_supp_query = "SELECT pps.* FROM post_partum_supp pps 
                             INNER JOIN pregnancy p ON pps.pregnancy_id = p.pregnancy_id 
                             INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                             WHERE pps.pregnancy_id = ? AND pt.health_center_id = ?
                             ORDER BY pps.iron_folic_month_given ASC";
        $stmt_postpartum_supp = $conn->prepare($postpartum_supp_query);
        $stmt_postpartum_supp->bind_param("ii", $pregnancy_id, $health_center_id);
        $stmt_postpartum_supp->execute();
        $result_postpartum_supp = $stmt_postpartum_supp->get_result();
        $postpartum_supp = $result_postpartum_supp->fetch_all(MYSQLI_ASSOC);
    } else {
        $postpartum_supp = [];
    }
    $post_iron_html = '';
    foreach ($postpartum_supp as $post_iron_supp) {
        $post_supp_id = $post_iron_supp['post_supp_id'];
        $iron_folic_month_given = insertValues($post_iron_supp, 'iron_folic_month_given');
        $iron_folic_date_given = insertValues($post_iron_supp, 'iron_folic_date_given');
        $tablets_given = insertValues($post_iron_supp, 'tablets_given');

        $post_iron_html .= "
            <div class='post-iron-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
                <div>
                    <p style='margin: 0;'><strong>Month Given:</strong> {$iron_folic_month_given}</p>
                    <p style='margin: 0;'><strong>Date:</strong> {$iron_folic_date_given}</p>
                    <p style='margin: 0;'><strong>No. of Tablets Given:</strong> {$tablets_given}</p>
                </div>
                <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_post_iron_btn' 
                        data-post-supp-id='{$post_supp_id}' 
                        data-preg-id='{$pregnancy_id}'
                        data-iron-folic-month-given='{$iron_folic_month_given}'
                        data-iron-folic-date-given='{$iron_folic_date_given}'
                        data-tablets-given='{$tablets_given}'
                        title='Edit this supplement'>
                    <i class='bi bi-pencil-fill'></i> Edit
                </button>
            </div>
        ";
    }

    // Add the "Add Iron Supplement" button
    $post_iron_html .= "
        <button type='button' class='btn btn-outline-primary w-100 add_post_iron_btn mt-2'
            data-preg-id='{$pregnancy_id}'
            data-bs-toggle='modal'
            data-bs-target='#addPostIronModal'>
            <i class='bi bi-plus-lg text-white'></i> Add Iron Sulfate w/Folic Acid
        </button>
    ";

    //postpartum supplement table

    //vitamin A
    $vitamin_query = "SELECT pv.vitamin_a, pv.vitamin_a_date 
                 FROM post_vitamin pv 
                 INNER JOIN pregnancy p ON pv.pregnancy_id = p.pregnancy_id 
                 INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                 WHERE pv.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_vitamin_query = $conn->prepare($vitamin_query);
    $stmt_vitamin_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_vitamin_query->execute();
    $result_vitamin = $stmt_vitamin_query->get_result();
    $vitamin_row = $result_vitamin->fetch_assoc();
    $vitamin_status = $vitamin_row['vitamin_a'] ?? null;

    $vitamin_date_display = "";

    if ($vitamin_status === 1) {
        $vitamin_a_date = insertValues($vitamin_row, 'vitamin_a_date');
        $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> Yes <span><i class='bi bi-check-circle-fill text-success'></i></span>";
        $vitamin_date_display = "<strong>Date:</strong> {$vitamin_a_date}";
        $button_text = "Update Status";
    } elseif ($vitamin_status === 0) {
        $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> No <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
        $vitamin_date_display = "<strong>Date:</strong> N/A";
        $button_text = "Update Status";
    } else {
        $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> N/A";
        $vitamin_date_display = "<strong>Date:</strong> N/A";
        $button_text = "Set Status";
    }
    //vitamin A

    //postpartum remarks

    $post_remarks_query = "SELECT pps.remarks 
                              FROM post_partum_supp pps
                              INNER JOIN pregnancy p ON pps.pregnancy_id = p.pregnancy_id 
                              INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                              WHERE pps.pregnancy_id = ? AND pt.health_center_id = ?";
    $stmt_post_remarks_query = $conn->prepare($post_remarks_query);
    $stmt_post_remarks_query->bind_param("ii", $pregnancy_id, $health_center_id);
    $stmt_post_remarks_query->execute();
    $remarks_post = $stmt_post_remarks_query->get_result();
    $remarks_postpartum = $remarks_post->fetch_assoc() ?? [];

    $remarks = insertValues($remarks_postpartum, 'remarks');

    $output .= "
             <div class = 'table-responsive'>
                    <table class = 'table table-bordered'>
                         <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>PREGNANCY METRICS</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Last Menstrual Period (LMP)(yyyy-mm-dd)</strong></label></td>
                            <td width = '60%'>{$lmp}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Estimated Date of Confinement(EDC)(yyyy-mm-dd)</strong></label></td>
                            <td width = '60%'>{$edc}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Gravidity</strong></label></td>
                            <td width = '60%'>{$gravidity}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Parity</strong></label></td>
                            <td width = '60%'>{$parity}</td>
                        </tr>
                        <tr>
                            <td class = 'table-dark text-center mt-4' colspan = '2'><label><strong>PREGNANCY OUTCOME</strong></label></td>  
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Date Terminated(yyyy-mm-dd)</strong></label></td>
                            <td width = '60%' id = 'date_terminated_<?php echo $pregnancy_id; ?>'>{$date_terminated}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Outcome</strong></label></td>
                            <td width = '60%' id = 'outcome_<?php echo $pregnancy_id; ?>'>{$outcome}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Sex</strong></label></td>
                            <td width = '60%' id = 'sex_<?php echo $pregnancy_id; ?>'>{$sex}</td>
                        </tr>
                        <tr>
                            <td colspan = '2'>        
                                <button class='btn btn-outline-primary w-100 add_preg_outcome_btn mt-2'
                                data-preg-id = '{$pregnancy_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addPregOutcomeModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Pregnancy Outcome</button>
                            </td>
                        </tr>
                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>BIRTH INFORMATION</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Type of Delivery</strong></label></td>
                            <td width = '60%' id = 'delivery_type_<?php echo $pregnancy_id;?>'>{$delivery_type}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Weight Classification</strong></label></td>
                            <td width = '60%' id = 'birth_weight_classification_<?php echo $pregnancy_id;?>'>{$weight_class}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Birth Weight(in grams)</strong></label></td>
                            <td width = '60%' id = 'birth_weight_<?php echo $pregnancy_id;?>'>{$birth_weight}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Birth Attendant</strong></label></td>
                            <td width = '60%' id = 'birth_attendant_<?php echo $pregnancy_id;?>'>{$birth_attendant}</td>
                        </tr>
                        <tr>
                            <td colspan = '2'>        
                                <button class='btn btn-outline-primary w-100 add_birth_info_btn mt-2'
                                data-preg-id = '{$pregnancy_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addBirthInfoModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Birth Information</button>
                            </td>
                        </tr>

                         <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>PLACE OF DELIVERY</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Health Facility Type</strong></label></td>
                            <td width = '60%' id ='health_facility_type_<?php echo $pregnancy_id;?>'>{$facility_type}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Health Facility Name </strong></label></td>
                            <td width = '60%' id ='health_facility_name_<?php echo $pregnancy_id;?>'>{$facility_name}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Ownership</strong></label></td>
                            <td width = '60%' id ='ownership_<?php echo $pregnancy_id;?>'>{$ownership}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Is BEmONC/CEmONC capable?</strong></label></td>
                            <td width = '60%' id ='bemonc_cemonc_capable_<?php echo $pregnancy_id;?>'>{$bemonc_cemonc_capable}</td>
                        </tr>
                        <tr>
                            <td colspan = '2'>        
                                <button class='btn btn-outline-primary w-100 add_place_birth_btn mt-2 mb-2'
                                data-preg-id = '{$pregnancy_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addPlaceBirthModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Health Facility</button>
                            </td>
                        </tr>

                         <tr>
                            <td width = '40%'><label><strong>Non-Health Facility</strong></label></td>
                            <td width = '60%' id ='non_health_facility_type_<?php echo $pregnancy_id;?>'>{$non_facility_type}</td>
                        </tr>
                         <tr>
                            <td width = '40%'><label><strong>Non-Health Facility Name</strong></label></td>
                            <td width = '60%' id ='non_health_facility_name_<?php echo $pregnancy_id;?>'>{$non_facility_name}</td>
                        </tr>
                        <tr>
                            <td colspan = '2'>        
                                <button class='btn btn-outline-primary w-100 add_non_health_btn mt-2'
                                data-preg-id = '{$pregnancy_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addPlaceNonHealthModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Non-Health Facility</button>
                            </td>
                        </tr>

                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>PRENATAL CHECK-UP</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'> 
                                 <label><strong>Date of Pre-natal Checkups(yyyy-mm-dd)</strong></label>
                            </td>
                            <td width = '60%' id = 'checkup-info-{$pregnancy_id}'>
                                    {$prenatal_html}
                                    <button class='btn btn-outline-primary w-100 add_checkup_btn '
                                        data-preg-id = '{$pregnancy_id}'
                                        data-bs-toggle = 'modal'
                                        data-bs-target = '#addCheckupModal'>
                                     <i class='bi bi-plus-lg text-white'></i>Update Check-up</button>
                            </td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>BMI Classification</strong></label></td>
                            <td width = '60%'  id ='bmi_class_<?php echo $pregnancy_id;?>'>{$bmi_class}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>BMI</strong></label></td>
                            <td width = '60%'  id ='bmi_<?php echo $pregnancy_id;?>'>{$bmi}</td>
                        </tr>
                         <tr>
                            <td width = '40%'><label><strong>Deworming</strong></label></td>
                            <td width = '60%'  id ='deworming_stat_<?php echo $pregnancy_id;?>'>{$deworming_stat}</td>
                        </tr>
                         <tr>
                            <td width = '40%'><label><strong>Deworming Date(yyyy-mm-dd)</strong></label></td>
                            <td width = '60%'  id ='deworming_date_<?php echo $pregnancy_id;?>'>{$deworming_date}</td>
                        </tr>
                        <tr>
                            <td colspan = '2'>        
                                <button class='btn btn-outline-primary w-100 add_bmi_btn mt-2'
                                data-preg-id = '{$pregnancy_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addPrenatalBmiModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Prenatal Details </button>
                            </td>
                        </tr>

                        
                       
                         <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>INFECTIOUS DISEASE SURVEILLANCE</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Syphilis Screening</strong></label></td>
                            <td width = '60%'>
                                <p  id ='syphilis_date_<?php echo $pregnancy_id;?>'><strong>Date(yyyy-mm-dd):</strong> {$syphilis_date}</p>
                                <p  id ='syphilis_screening_<?php echo $pregnancy_id;?>'><strong>Screening:</strong> {$syphilis_screening}</p>
                                <p  id ='syphilis_remarks_<?php echo $pregnancy_id;?>'><strong>Note:</strong> {$syphilis_remarks}</p>  
                            </td>  
                        </tr>
                         <tr>
                            <td width = '40%'><label><strong>Hepatitis B Screening</strong></label></td>
                            <td width = '60%'>
                                <p  id ='hepatitisB_date_<?php echo $pregnancy_id;?>'><strong>Date(yyyy-mm-dd):</strong> {$hepatitisB_date}</p>
                                <p  id ='hepatitis_b_screening_<?php echo $pregnancy_id;?>'><strong>Screening:</strong> {$hepatitis_b_screening}</p>
                                <p  id ='hepatitis_b_remarks_<?php echo $pregnancy_id;?>'><strong>Note:</strong> {$hepatitis_b_remarks}</p>  
                            </td>  
                        </tr>
                        <tr>
                            <td colspan = '2'>        
                                <button class='btn btn-outline-primary w-100 add_disease_btn mt-2'
                                data-preg-id = '{$pregnancy_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addDiseaseModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Infectious Disease Screening</button>
                            </td> 
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>HIV Screening</strong> </label></td>
                            <td width = '60%'>
                                <p  id ='hiv_date_<?php echo $pregnancy_id;?>'><strong>Date(yyyy-mm-dd):</strong> {$hiv_date}</p>
                                <p  id ='hiv_screening_<?php echo $pregnancy_id;?>'><strong>Screening:</strong> {$hiv_screening}</p>
                                <p  id ='hiv_remarks_<?php echo $pregnancy_id;?>'><strong>Note:</strong> {$hiv_remarks}</p>  
                                     <button class='btn btn-outline-primary w-100 add_hiv_btn mt-2'
                                        data-preg-id = '{$pregnancy_id}'
                                        data-bs-toggle = 'modal'
                                        data-bs-target = '#addHivModal'>
                                    <i class='bi bi-plus-lg text-white'></i>Update HIV Screening</button>
                            </td>  
                        </tr>
                         
                         <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>LABORATORY SCREENING</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Gestational Diabetes </strong></label></td>
                            <td width = '60%'>
                                <p id ='gestational_date_<?php echo $pregnancy_id;?>'><strong>Date(yyyy-mm-dd):</strong> {$gestational_date}</p>
                                <p id ='gestational_screening_<?php echo $pregnancy_id;?>'><strong>Screening:</strong> {$gestational_screening}</p>
                                <p id ='gestational_remarks_<?php echo $pregnancy_id;?>'><strong>Note:</strong> {$gestational_remarks}</p>  
                                    <button class='btn btn-outline-primary w-100 add_laboratory_btn mt-2'
                                    data-preg-id = '{$pregnancy_id}'
                                    data-bs-toggle = 'modal'
                                    data-bs-target = '#addLaboratoryModal'>
                                    <i class='bi bi-plus-lg text-white'></i>Update Gestational Diabetes Screening</button>
                            </td>  
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>CBC/Hgb&Hct Count</strong> </label></td>
                            <td width = '60%'>
                                <p id ='cbc_hgb_hct_date_<?php echo $pregnancy_id;?>'><strong>Date(yyyy-mm-dd):</strong> {$cbc_hgb_hct_date}</p>
                                <p id ='anemia_status_<?php echo $pregnancy_id;?>'><strong>Screening:</strong> {$anemia_status}</p>
                                <p id ='cbc_hgb_hct_count_<?php echo $pregnancy_id;?>'><strong>Note:</strong> {$anemia_remarks}</p>  
                                <p id ='anemia_remarks_<?php echo $pregnancy_id;?>'><strong>CBC/Hgb&Hct Count:</strong> {$cbc_hgb_hct_count}</p>
                                    <button class='btn btn-outline-primary w-100 add_cbc_btn mt-2'
                                    data-preg-id = '{$pregnancy_id}'
                                    data-bs-toggle = 'modal'
                                    data-bs-target = '#addCbcModal'>
                                    <i class='bi bi-plus-lg text-white'></i>Update CBC/Hgb&Hct Screening</button>
                            </td>  
                        </tr>

                        <tr>
                            <td width = '40%'><label><strong>Given Iron</strong> </label></td>
                            <td width = '60%'>
                                <p id ='given_iron_<?php echo $pregnancy_id;?>'><strong>Is Given Iron?</strong> {$given_iron}</p>
                                <p id ='given_iron_date_<?php echo $pregnancy_id;?>'><strong>Date(yyyy-mm-dd):</strong> {$given_iron_date}</p>
                                <p id ='maternal_screening_remark_<?php echo $pregnancy_id;?>'><strong>Note:</strong> {$maternal_screening_remark}</p>  
                                    <button class='btn btn-outline-primary w-100 add_given_iron_btn mt-2'
                                    data-preg-id = '{$pregnancy_id}'
                                    data-bs-toggle = 'modal'
                                    data-bs-target = '#addGIvenIronModal'>
                                    <i class='bi bi-plus-lg text-white'></i>Update Given Iron</button>       
                            </td>  
                        </tr>
                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>IMMUNIZATION STATUS</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'>
                                <label><strong>Type and Date of Immunization(yyyy-mm-dd)</strong></label>
                                
                            </td>
                            <td width = '60%' id = 'immunization-info-{$pregnancy_id}'>
                                    {$immunization_html}
                                    
                                    <button class='btn btn-outline-primary w-100 add_immunization_btn'
                                        data-preg-id = '{$pregnancy_id}'
                                        data-bs-toggle = 'modal'
                                        data-bs-target = '#addImmunizationForm'>
                                     <i class='bi bi-plus-lg w-100 text-white'></i>Update Immunization</button>
                            </td>   
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>FIM Status (is fully immunized?)</strong> </label></td>
                            <td width = '60%' id = 'fim-info-{pregnancy_id}'>
                                    {$fim_status_display}
                                    <div> 
                                        <button type='button' class='btn btn-outline-primary w-100 add_fim_btn mt-2'
                                            data-preg-id='{$pregnancy_id}'
                                            data-fim-status = '{$fim_status_value}'
                                            data-bs-toggle='modal'
                                            data-bs-target='#addFimModal'>
                                            <i class='bi bi-plus-lg w-100 text-white'></i> {$button_text}
                                        </button> 
                                    </diV>
                              
                            </td>  
                        </tr>
                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>MICRONUTRIENT SUPPLEMENTATION</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Iron Sulfate w/Folic Acid</strong></label></td>                        
                            <td width = '60%' id = 'iron-info-{$pregnancy_id}'>
                                    {$iron_html}                                
                                                             
                            </td>  
                        </tr>
                            <td width = '40%'><label><strong>Calcium Carbonate</strong></label></td>
                            <td width = '60%' id = 'calcium-info-{$pregnancy_id}'>
                                    {$calcium_html}                                
                                                              
                            </td>  
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>2 Iodine Capsule were given?</strong> </label></td>
                            <td width = '60%' id = 'iodine-info-{$pregnancy_id}'>
                                    <div>{$iodine_status_display}</div>
                                    <div>{$iodine_date_display}</div>  
                                     <button type='button' class='btn btn-outline-primary w-100 add_iodine_btn mt-2'
                                        data-preg-id='{$pregnancy_id}'
                                        data-iodine-status = '{$iodine_status}'
                                        data-bs-toggle='modal'
                                        data-bs-target='#addIodineModal'>
                                        <i class='bi bi-plus-lg text-white'></i> {$button_text}
                                    </button>                              
                                                            
                            </td>  
                        </tr>
                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>POSTPARTUM CARE</strong></label></td>  
                         </tr>
                          <tr>
                            <td width = '40%'><label><strong>Postpartum Check-Ups</strong></label></td>
                            <td width = '60%' id = 'post-checkup-info-{$pregnancy_id}'>
                                {$post_checkup_html}
                                <button type='button' class='btn btn-outline-primary w-100 add_post_checkup_btn mt-2'
                                    data-preg-id='{$pregnancy_id}'
                                    data-bs-toggle='modal'
                                    data-bs-target='#addPostpartumCheckupModal'>
                                    <i class='bi bi-plus-lg text-white'></i> Update Check-up
                                </button>
                            </td>  
                        </tr>
                         <tr>
                            <td width = '40%'><label><strong>Date and Time of Delivery</strong></label></td>
                            <td width = '60%'>
                                <p>Date(yyyy-mm-dd): <span id='post_delivery_date_<?php echo $pregnancy_id;?>'>{$post_delivery_date}</span></p>
                                <p>Time: <span id='post_delivery_time_<?php echo $pregnancy_id;?>'>{$post_delivery_time}</span></p>
                            </td>  
                        </tr>
                       
                        <tr>
                            <td width = '40%'><label><strong>Date and Time Initiated Breastfeeding</strong></label></td>
                            <td width = '60%'>
                                <p>Date Breastfed(yyyy-mm-dd): <span id='breastfeeding_date_<?php echo $pregnancy_id;?>'>{$breastfeeding_date}</span></p>
                                <p>Time Breastfed: <span id='breastfeeding_time_<?php echo $pregnancy_id;?>'>{$breastfeeding_time}</span></p>
                            </td>  
                        </tr>
                        <tr>
                            <td colspan = '2'>        
                                <button class='btn btn-outline-primary w-100 add_postpartum_btn mt-2'
                                data-preg-id = '{$pregnancy_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addPostpartumModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Postpartum Details</button>
                            </td> 
                        </tr>
                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>MICRONUTRIENT SUPPLEMENTATION(postpartum)</strong></label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Iron w/Folic Acid</strong></label></td>
                            <td width = '60%' id = 'post-iron-info-{$pregnancy_id}'>
                                {$post_iron_html}
                            </td>  
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Vitamin A</strong></label></td>
                            <td width = '60%' id = 'vitamin-info-{$pregnancy_id}'>
                                <div>{$vitamin_a_display}</div>

                                <div>{$vitamin_date_display}</div>  
                                     <button type='button' class='btn btn-outline-primary w-100 add_vitamin_btn mt-2'
                                        data-preg-id='{$pregnancy_id}'
                                        data-vitamin-status = '{$vitamin_status}'
                                        data-bs-toggle='modal'
                                        data-bs-target='#addVitaminModal'>
                                        <i class='bi bi-plus-lg text-white'></i> {$button_text}
                                    </button>                             
                            </td>  
                        </tr>
                                   
                    </table>
               </div>

            ";
}

echo $output;
