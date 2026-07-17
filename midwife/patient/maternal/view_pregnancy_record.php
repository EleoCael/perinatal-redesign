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

$pregnancy_id = isset($_GET['pregnancy_id']) ? intval($_GET['pregnancy_id']) : 0;
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

if (!$pregnancy) {
    echo '<div class="main-container"><div class="alert alert-danger">Pregnancy record not found or access denied.</div></div>';
    exit;
}

// patient name, for the header
$patient_id = $pregnancy['patient_id'];
$stmt_patient_name = $conn->prepare("SELECT first_name, middle_name, last_name FROM patient WHERE patient_id = ? AND health_center_id = ?");
$stmt_patient_name->bind_param("ii", $patient_id, $health_center_id);
$stmt_patient_name->execute();
$patient_row = $stmt_patient_name->get_result()->fetch_assoc();
$patient_display_name = $patient_row ? trim($patient_row['first_name'] . ' ' . $patient_row['middle_name'] . ' ' . $patient_row['last_name']) : ('Patient #' . $patient_id);
$stmt_patient_name->close();

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
//delivery table - >Place of Delivery->Non Health Facility

//prenatal checkup table
$prenatal_query = "SELECT pnc.* FROM prenatal_checkup pnc 
                WHERE pnc.pregnancy_id = ? 
                ORDER BY pnc.checkup_date";
$stmt_prenatal = $conn->prepare($prenatal_query);
$stmt_prenatal->bind_param("i", $pregnancy_id);
$stmt_prenatal->execute();
$prenatal = $stmt_prenatal->get_result()->fetch_all(MYSQLI_ASSOC);
$prenatal_html = '';
foreach ($prenatal as $checkup) {
    $checkup_id = $checkup['checkup_id'];
    $trimester = insertValues($checkup, 'trimester');
    $checkup_date = insertValues($checkup, 'checkup_date');
    $prenatal_html .= "
        <div class='checkup-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Trimester:</strong> {$trimester}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$checkup_date}</p>
            </div>
            <button type='button' class='btn btn-sm btn-outline-primary edit_checkup_btn' 
                    data-checkup-id='{$checkup_id}' data-preg-id='{$pregnancy_id}'
                    data-trimester='{$trimester}' data-checkup-date='{$checkup_date}' title='Edit this checkup'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}
//prenatal checkup table

//prenatal other details
$bmi_query = "SELECT bmi_class, bmi, deworming_status, deworming_date_given FROM prenatal_checkup WHERE pregnancy_id = ?";
$stmt_bmi = $conn->prepare($bmi_query);
$stmt_bmi->bind_param("i", $pregnancy_id);
$stmt_bmi->execute();
$bmi_info = $stmt_bmi->get_result()->fetch_assoc() ?? [];
$bmi_class = insertValues($bmi_info, 'bmi_class');
$bmi = insertValues($bmi_info, 'bmi');
$deworming_stat = insertValues($bmi_info, 'deworming_status');
$deworming_date = insertValues($bmi_info, 'deworming_date_given');
//prenatal other details

//maternal disease screening
$disease_query = "SELECT * FROM maternal_disease_screening WHERE pregnancy_id = ?";
$stmt_disease_query = $conn->prepare($disease_query);
$stmt_disease_query->bind_param("i", $pregnancy_id);
$stmt_disease_query->execute();
$disease_info = $stmt_disease_query->get_result()->fetch_assoc() ?? [];

$syphilis_date = insertValues($disease_info, 'syphilis_date');
$syphilis_screening = insertValues($disease_info, 'syphilis_screening');
$syphilis_remarks = insertValues($disease_info, 'syphilis_screening_remarks');
$hepatitisB_date = insertValues($disease_info, 'hepatitisB_date');
$hepatitis_b_screening = insertValues($disease_info, 'hepatitis_b_screening');
$hepatitis_b_remarks = insertValues($disease_info, 'hepatitis_b_screening_remarks');
$hiv_date = insertValues($disease_info, 'hiv_date');
$hiv_screening = insertValues($disease_info, 'hiv_screening');
$hiv_remarks = insertValues($disease_info, 'hiv_screening_remarks');
$gestational_date = insertValues($disease_info, 'gestational_diabetes_date');
$gestational_screening = insertValues($disease_info, 'gestational_diabetes_screening');
$gestational_remarks = insertValues($disease_info, 'diabetes_remarks');
$cbc_hgb_hct_date = insertValues($disease_info, 'cbc_hgb_hct_date');
$anemia_status = insertValues($disease_info, 'anemia_status');
$cbc_hgb_hct_count = insertValues($disease_info, 'cbc_hgb_hct_count');
$anemia_remarks = insertValues($disease_info, 'anemia_status_remarks');
$given_iron = displayCheckbox($disease_info, 'given_iron');
$given_iron_date = insertValues($disease_info, 'given_iron_date');
$maternal_screening_remark = insertValues($disease_info, 'maternal_screening_remark');
//maternal disease screening

//maternal immunization table
$immunization_query = "SELECT * FROM maternal_immunization WHERE pregnancy_id = ?";
$stmt_immunization = $conn->prepare($immunization_query);
$stmt_immunization->bind_param("i", $pregnancy_id);
$stmt_immunization->execute();
$immunization = $stmt_immunization->get_result()->fetch_all(MYSQLI_ASSOC);
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
            <button type='button' class='btn btn-sm btn-outline-primary'
                    onclick=\"loadPage('patient/maternal/manage_immunization.php?pregnancy_id={$pregnancy_id}&maternal_immunization_id={$maternal_immunization_id}')\"
                    title='Edit this immunization'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}
//maternal immunization table

//fim status
$fim_query = "SELECT fim_status FROM fim_status_maternal WHERE pregnancy_id = ?";
$stmt_fim = $conn->prepare($fim_query);
$stmt_fim->bind_param("i", $pregnancy_id);
$stmt_fim->execute();
$fim_row = $stmt_fim->get_result()->fetch_assoc();
$fim_status_value = $fim_row['fim_status'] ?? null;
if ($fim_status_value === 1) {
    $fim_status_display = "<strong>Status:</strong> Fully Immunized (Yes) <span><i class='bi bi-check-circle-fill text-success'></i></span>";
    $fim_button_text = 'Update Status';
} elseif ($fim_status_value === 0) {
    $fim_status_display = "<strong>Status:</strong> Not Fully Immunized (No) <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
    $fim_button_text = 'Update Status';
} else {
    $fim_status_display = "<strong>Status:</strong> N/A";
    $fim_button_text = 'Set Status';
}
//fim status

//maternal supplement
$supplement_query = "SELECT * FROM maternal_supplements WHERE pregnancy_id = ? ORDER BY date_supp ASC";
$stmt_supplement = $conn->prepare($supplement_query);
$stmt_supplement->bind_param("i", $pregnancy_id);
$stmt_supplement->execute();
$supplement = $stmt_supplement->get_result()->fetch_all(MYSQLI_ASSOC);
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
        <button type='button' class='btn btn-sm btn-outline-primary edit_{TYPE}_btn' 
                data-supplement-id='{$maternal_supplement_id}' data-preg-id='{$pregnancy_id}'
                data-trimester='{$trimester}' data-tablets-given='{$tablets_given}'
                data-date-supp='{$date}' data-supplement-type='{$type}' title='Edit this supplement'>
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
    <button class='btn btn-outline-primary w-100 add_iron_btn' data-preg-id='{$pregnancy_id}'
            data-bs-toggle='modal' data-bs-target='#addIronModal'>
            <i class='bi bi-plus-lg text-white'></i>Add Iron Supplement
    </button>
";
$calcium_html .= "
    <button class='btn btn-outline-primary w-100 add_calcium_btn' data-preg-id='{$pregnancy_id}'
            data-bs-toggle='modal' data-bs-target='#addCalciumModal'>
            <i class='bi bi-plus-lg text-white'></i>Add Calcium Supplement
    </button>
";
//maternal supplement

//iodine supplement
$iodine_query = "SELECT iodine_capsule_given, date_iodine FROM iodine_supplement WHERE pregnancy_id = ?";
$stmt_iodine = $conn->prepare($iodine_query);
$stmt_iodine->bind_param("i", $pregnancy_id);
$stmt_iodine->execute();
$iodine_row = $stmt_iodine->get_result()->fetch_assoc();
$iodine_status = $iodine_row['iodine_capsule_given'] ?? null;
$iodine_date_display = "";
if ($iodine_status === 1) {
    $iodine_date = insertValues($iodine_row, 'date_iodine');
    $iodine_status_display = "<strong>Status:</strong> Yes <span><i class='bi bi-check-circle-fill text-success'></i></span>";
    $iodine_date_display = "<strong>Date:</strong> {$iodine_date}";
    $iodine_button_text = "Update Status";
} elseif ($iodine_status === 0) {
    $iodine_status_display = "<strong>Status:</strong> No <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
    $iodine_date_display = "<strong>Date:</strong> N/A";
    $iodine_button_text = "Update Status";
} else {
    $iodine_status_display = "<strong>Status:</strong> N/A";
    $iodine_date_display = "<strong>Date:</strong> N/A";
    $iodine_button_text = "Set Status";
}
//iodine supplement

//postpartum checkup table
$postpartum_query = "SELECT * FROM post_partum_checkup WHERE pregnancy_id = ? ORDER BY post_checkup_date";
$stmt_postpartum = $conn->prepare($postpartum_query);
$stmt_postpartum->bind_param("i", $pregnancy_id);
$stmt_postpartum->execute();
$postpartum = $stmt_postpartum->get_result()->fetch_all(MYSQLI_ASSOC);
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
        <button type='button' class='btn btn-sm btn-outline-primary edit_post_checkup_btn' 
                data-post-checkup-id='{$checkup_id}' data-preg-id='{$pregnancy_id}'
                data-checkup-visit='{$checkup_visit}' data-post-checkup-date='{$post_checkup_date}' title='Edit this checkup'>
            <i class='bi bi-pencil-fill'></i> Edit
        </button>
    </div>
    ";
}
//postpartum checkup table

//postpartum other details
$post_query = "SELECT post_delivery_date, post_delivery_time, breastfeeding_date, breastfeeding_time FROM post_partum_checkup WHERE pregnancy_id = ?";
$stmt_post_query = $conn->prepare($post_query);
$stmt_post_query->bind_param("i", $pregnancy_id);
$stmt_post_query->execute();
$post_info = $stmt_post_query->get_result()->fetch_assoc() ?? [];
$post_delivery_date = insertValues($post_info, 'post_delivery_date');
$post_delivery_time = insertValues($post_info, 'post_delivery_time');
$breastfeeding_date = insertValues($post_info, 'breastfeeding_date');
$breastfeeding_time = insertValues($post_info, 'breastfeeding_time');
//postpartum other details

//postpartum supplement table
$postpartum_supp_query = "SELECT * FROM post_partum_supp WHERE pregnancy_id = ? ORDER BY iron_folic_month_given ASC";
$stmt_postpartum_supp = $conn->prepare($postpartum_supp_query);
$stmt_postpartum_supp->bind_param("i", $pregnancy_id);
$stmt_postpartum_supp->execute();
$postpartum_supp = $stmt_postpartum_supp->get_result()->fetch_all(MYSQLI_ASSOC);
$post_iron_html = '';
foreach ($postpartum_supp as $post_iron_supp) {
    $post_supp_id = $post_iron_supp['post_supp_id'];
    $iron_folic_month_given = insertValues($post_iron_supp, 'iron_folic_month_given');
    $iron_folic_date_given = insertValues($post_iron_supp, 'iron_folic_date_given');
    $tablets_given_post = insertValues($post_iron_supp, 'tablets_given');
    $post_iron_html .= "
        <div class='post-iron-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Month Given:</strong> {$iron_folic_month_given}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$iron_folic_date_given}</p>
                <p style='margin: 0;'><strong>No. of Tablets Given:</strong> {$tablets_given_post}</p>
            </div>
            <button type='button' class='btn btn-sm btn-outline-primary edit_post_iron_btn' 
                    data-post-supp-id='{$post_supp_id}' data-preg-id='{$pregnancy_id}'
                    data-iron-folic-month-given='{$iron_folic_month_given}' data-iron-folic-date-given='{$iron_folic_date_given}'
                    data-tablets-given='{$tablets_given_post}' title='Edit this supplement'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}
$post_iron_html .= "
    <button type='button' class='btn btn-outline-primary w-100 add_post_iron_btn mt-2' data-preg-id='{$pregnancy_id}'
        data-bs-toggle='modal' data-bs-target='#addPostIronModal'>
        <i class='bi bi-plus-lg text-white'></i> Add Iron Sulfate w/Folic Acid
    </button>
";
//postpartum supplement table

//vitamin A
$vitamin_query = "SELECT vitamin_a, vitamin_a_date FROM post_vitamin WHERE pregnancy_id = ?";
$stmt_vitamin_query = $conn->prepare($vitamin_query);
$stmt_vitamin_query->bind_param("i", $pregnancy_id);
$stmt_vitamin_query->execute();
$vitamin_row = $stmt_vitamin_query->get_result()->fetch_assoc();
$vitamin_status = $vitamin_row['vitamin_a'] ?? null;
$vitamin_date_display = "";
if ($vitamin_status === 1) {
    $vitamin_a_date = insertValues($vitamin_row, 'vitamin_a_date');
    $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> Yes <span><i class='bi bi-check-circle-fill text-success'></i></span>";
    $vitamin_date_display = "<strong>Date:</strong> {$vitamin_a_date}";
    $vitamin_button_text = "Update Status";
} elseif ($vitamin_status === 0) {
    $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> No <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
    $vitamin_date_display = "<strong>Date:</strong> N/A";
    $vitamin_button_text = "Update Status";
} else {
    $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> N/A";
    $vitamin_date_display = "<strong>Date:</strong> N/A";
    $vitamin_button_text = "Set Status";
}
//vitamin A
?>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-body-tertiary">Pregnancy Record > <?php echo htmlspecialchars($patient_display_name); ?></h5>
        <button class="btn btn-secondary" type="button" onclick="loadPage('home.php')">Back to Dashboard</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <tr><td class="table-dark text-center" colspan="2"><label><strong>PREGNANCY METRICS</strong></label></td></tr>
            <tr><td width="40%"><label><strong>Last Menstrual Period (LMP)</strong></label></td><td width="60%"><?php echo $lmp; ?></td></tr>
            <tr><td width="40%"><label><strong>Estimated Date of Confinement (EDC)</strong></label></td><td width="60%"><?php echo $edc; ?></td></tr>
            <tr><td width="40%"><label><strong>Gravidity</strong></label></td><td width="60%"><?php echo $gravidity; ?></td></tr>
            <tr><td width="40%"><label><strong>Parity</strong></label></td><td width="60%"><?php echo $parity; ?></td></tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>PREGNANCY OUTCOME</strong></label></td></tr>
            <tr><td width="40%"><label><strong>Date Terminated</strong></label></td><td width="60%"><?php echo $date_terminated; ?></td></tr>
            <tr><td width="40%"><label><strong>Outcome</strong></label></td><td width="60%"><?php echo $outcome; ?></td></tr>
            <tr><td width="40%"><label><strong>Sex</strong></label></td><td width="60%"><?php echo $sex; ?></td></tr>
            <tr><td colspan="2"><button class="btn btn-outline-primary w-100 add_preg_outcome_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addPregOutcomeModal"><i class="bi bi-plus-lg text-white"></i>Update Pregnancy Outcome</button></td></tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>BIRTH INFORMATION</strong></label></td></tr>
            <tr><td width="40%"><label><strong>Type of Delivery</strong></label></td><td width="60%"><?php echo $delivery_type; ?></td></tr>
            <tr><td width="40%"><label><strong>Weight Classification</strong></label></td><td width="60%"><?php echo $weight_class; ?></td></tr>
            <tr><td width="40%"><label><strong>Birth Weight (in grams)</strong></label></td><td width="60%"><?php echo $birth_weight; ?></td></tr>
            <tr><td width="40%"><label><strong>Birth Attendant</strong></label></td><td width="60%"><?php echo $birth_attendant; ?></td></tr>
            <tr><td colspan="2"><button class="btn btn-outline-primary w-100 add_birth_info_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addBirthInfoModal"><i class="bi bi-plus-lg text-white"></i>Update Birth Information</button></td></tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>PLACE OF DELIVERY</strong></label></td></tr>
            <tr><td width="40%"><label><strong>Health Facility Type</strong></label></td><td width="60%"><?php echo $facility_type; ?></td></tr>
            <tr><td width="40%"><label><strong>Health Facility Name</strong></label></td><td width="60%"><?php echo $facility_name; ?></td></tr>
            <tr><td width="40%"><label><strong>Ownership</strong></label></td><td width="60%"><?php echo $ownership; ?></td></tr>
            <tr><td width="40%"><label><strong>Is BEmONC/CEmONC capable?</strong></label></td><td width="60%"><?php echo $bemonc_cemonc_capable; ?></td></tr>
            <tr><td colspan="2"><button class="btn btn-outline-primary w-100 add_place_birth_btn mt-2 mb-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addPlaceBirthModal"><i class="bi bi-plus-lg text-white"></i>Update Health Facility</button></td></tr>

            <tr><td width="40%"><label><strong>Non-Health Facility</strong></label></td><td width="60%"><?php echo $non_facility_type; ?></td></tr>
            <tr><td width="40%"><label><strong>Non-Health Facility Name</strong></label></td><td width="60%"><?php echo $non_facility_name; ?></td></tr>
            <tr><td colspan="2"><button class="btn btn-outline-primary w-100 add_non_health_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addPlaceNonHealthModal"><i class="bi bi-plus-lg text-white"></i>Update Non-Health Facility</button></td></tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>PRENATAL CHECK-UP</strong></label></td></tr>
            <tr>
                <td width="40%"><label><strong>Date of Pre-natal Checkups</strong></label></td>
                <td width="60%">
                    <?php echo $prenatal_html; ?>
                    <button class="btn btn-outline-primary w-100 add_checkup_btn" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addCheckupModal"><i class="bi bi-plus-lg text-white"></i>Update Check-up</button>
                </td>
            </tr>
            <tr><td width="40%"><label><strong>BMI Classification</strong></label></td><td width="60%"><?php echo $bmi_class; ?></td></tr>
            <tr><td width="40%"><label><strong>BMI</strong></label></td><td width="60%"><?php echo $bmi; ?></td></tr>
            <tr><td width="40%"><label><strong>Deworming</strong></label></td><td width="60%"><?php echo $deworming_stat; ?></td></tr>
            <tr><td width="40%"><label><strong>Deworming Date</strong></label></td><td width="60%"><?php echo $deworming_date; ?></td></tr>
            <tr><td colspan="2"><button class="btn btn-outline-primary w-100 add_bmi_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addPrenatalBmiModal"><i class="bi bi-plus-lg text-white"></i>Update Prenatal Details</button></td></tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>INFECTIOUS DISEASE SURVEILLANCE</strong></label></td></tr>
            <tr>
                <td width="40%"><label><strong>Syphilis Screening</strong></label></td>
                <td width="60%">
                    <p><strong>Date:</strong> <?php echo $syphilis_date; ?></p>
                    <p><strong>Screening:</strong> <?php echo $syphilis_screening; ?></p>
                    <p><strong>Note:</strong> <?php echo $syphilis_remarks; ?></p>
                </td>
            </tr>
            <tr>
                <td width="40%"><label><strong>Hepatitis B Screening</strong></label></td>
                <td width="60%">
                    <p><strong>Date:</strong> <?php echo $hepatitisB_date; ?></p>
                    <p><strong>Screening:</strong> <?php echo $hepatitis_b_screening; ?></p>
                    <p><strong>Note:</strong> <?php echo $hepatitis_b_remarks; ?></p>
                </td>
            </tr>
            <tr><td colspan="2"><button class="btn btn-outline-primary w-100 add_disease_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addDiseaseModal"><i class="bi bi-plus-lg text-white"></i>Update Infectious Disease Screening</button></td></tr>
            <tr>
                <td width="40%"><label><strong>HIV Screening</strong></label></td>
                <td width="60%">
                    <p><strong>Date:</strong> <?php echo $hiv_date; ?></p>
                    <p><strong>Screening:</strong> <?php echo $hiv_screening; ?></p>
                    <p><strong>Note:</strong> <?php echo $hiv_remarks; ?></p>
                    <button class="btn btn-outline-primary w-100 add_hiv_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addHivModal"><i class="bi bi-plus-lg text-white"></i>Update HIV Screening</button>
                </td>
            </tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>LABORATORY SCREENING</strong></label></td></tr>
            <tr>
                <td width="40%"><label><strong>Gestational Diabetes</strong></label></td>
                <td width="60%">
                    <p><strong>Date:</strong> <?php echo $gestational_date; ?></p>
                    <p><strong>Screening:</strong> <?php echo $gestational_screening; ?></p>
                    <p><strong>Note:</strong> <?php echo $gestational_remarks; ?></p>
                    <button class="btn btn-outline-primary w-100 add_laboratory_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addLaboratoryModal"><i class="bi bi-plus-lg text-white"></i>Update Gestational Diabetes Screening</button>
                </td>
            </tr>
            <tr>
                <td width="40%"><label><strong>CBC/Hgb&Hct Count</strong></label></td>
                <td width="60%">
                    <p><strong>Date:</strong> <?php echo $cbc_hgb_hct_date; ?></p>
                    <p><strong>Screening:</strong> <?php echo $anemia_status; ?></p>
                    <p><strong>CBC/Hgb&Hct Count:</strong> <?php echo $cbc_hgb_hct_count; ?></p>
                    <p><strong>Note:</strong> <?php echo $anemia_remarks; ?></p>
                    <button class="btn btn-outline-primary w-100 add_cbc_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addCbcModal"><i class="bi bi-plus-lg text-white"></i>Update CBC/Hgb&Hct Screening</button>
                </td>
            </tr>
            <tr>
                <td width="40%"><label><strong>Given Iron</strong></label></td>
                <td width="60%">
                    <p><strong>Is Given Iron?</strong> <?php echo $given_iron; ?></p>
                    <p><strong>Date:</strong> <?php echo $given_iron_date; ?></p>
                    <p><strong>Note:</strong> <?php echo $maternal_screening_remark; ?></p>
                    <button class="btn btn-outline-primary w-100 add_given_iron_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addGIvenIronModal"><i class="bi bi-plus-lg text-white"></i>Update Given Iron</button>
                </td>
            </tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>IMMUNIZATION STATUS</strong></label></td></tr>
            <tr>
                <td width="40%"><label><strong>Type and Date of Immunization</strong></label></td>
                <td width="60%">
                    <?php echo $immunization_html; ?>
                    <button class="btn btn-outline-primary w-100" type="button"
                            onclick="loadPage('patient/maternal/manage_immunization.php?pregnancy_id=<?php echo $pregnancy_id; ?>')">
                        <i class="bi bi-plus-lg text-white"></i> Add Immunization
                    </button>
                </td>
            </tr>
            <tr>
                <td width="40%"><label><strong>FIM Status (is fully immunized?)</strong></label></td>
                <td width="60%">
                    <?php echo $fim_status_display; ?>
                    <div>
                        <button type="button" class="btn btn-outline-primary w-100 add_fim_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-fim-status="<?php echo $fim_status_value; ?>" data-bs-toggle="modal" data-bs-target="#addFimModal"><i class="bi bi-plus-lg w-100 text-white"></i> <?php echo $fim_button_text; ?></button>
                    </div>
                </td>
            </tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>MICRONUTRIENT SUPPLEMENTATION</strong></label></td></tr>
            <tr><td width="40%"><label><strong>Iron Sulfate w/Folic Acid</strong></label></td><td width="60%"><?php echo $iron_html; ?></td></tr>
            <tr><td width="40%"><label><strong>Calcium Carbonate</strong></label></td><td width="60%"><?php echo $calcium_html; ?></td></tr>
            <tr>
                <td width="40%"><label><strong>2 Iodine Capsules were given?</strong></label></td>
                <td width="60%">
                    <div><?php echo $iodine_status_display; ?></div>
                    <div><?php echo $iodine_date_display; ?></div>
                    <button type="button" class="btn btn-outline-primary w-100 add_iodine_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-iodine-status="<?php echo $iodine_status; ?>" data-bs-toggle="modal" data-bs-target="#addIodineModal"><i class="bi bi-plus-lg text-white"></i> <?php echo $iodine_button_text; ?></button>
                </td>
            </tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>POSTPARTUM CARE</strong></label></td></tr>
            <tr>
                <td width="40%"><label><strong>Postpartum Check-Ups</strong></label></td>
                <td width="60%">
                    <?php echo $post_checkup_html; ?>
                    <button type="button" class="btn btn-outline-primary w-100 add_post_checkup_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addPostpartumCheckupModal"><i class="bi bi-plus-lg text-white"></i> Update Check-up</button>
                </td>
            </tr>
            <tr>
                <td width="40%"><label><strong>Date and Time of Delivery</strong></label></td>
                <td width="60%"><p>Date: <?php echo $post_delivery_date; ?></p><p>Time: <?php echo $post_delivery_time; ?></p></td>
            </tr>
            <tr>
                <td width="40%"><label><strong>Date and Time Initiated Breastfeeding</strong></label></td>
                <td width="60%"><p>Date: <?php echo $breastfeeding_date; ?></p><p>Time: <?php echo $breastfeeding_time; ?></p></td>
            </tr>
            <tr><td colspan="2"><button class="btn btn-outline-primary w-100 add_postpartum_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-bs-toggle="modal" data-bs-target="#addPostpartumModal"><i class="bi bi-plus-lg text-white"></i>Update Postpartum Details</button></td></tr>

            <tr><td class="table-dark text-center" colspan="2"><label><strong>MICRONUTRIENT SUPPLEMENTATION (postpartum)</strong></label></td></tr>
            <tr><td width="40%"><label><strong>Iron w/Folic Acid</strong></label></td><td width="60%"><?php echo $post_iron_html; ?></td></tr>
            <tr>
                <td width="40%"><label><strong>Vitamin A</strong></label></td>
                <td width="60%">
                    <div><?php echo $vitamin_a_display; ?></div>
                    <div><?php echo $vitamin_date_display; ?></div>
                    <button type="button" class="btn btn-outline-primary w-100 add_vitamin_btn mt-2" data-preg-id="<?php echo $pregnancy_id; ?>" data-vitamin-status="<?php echo $vitamin_status; ?>" data-bs-toggle="modal" data-bs-target="#addVitaminModal"><i class="bi bi-plus-lg text-white"></i> <?php echo $vitamin_button_text; ?></button>
                </td>
            </tr>
        </table>
    </div>
</div>