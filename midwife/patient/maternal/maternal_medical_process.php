<?php
// SCAFFOLD — wire this up once the "Add Medical Info" form (Maternal) is built.
// Extracted from the original monolithic maternal_process.php. Expects an
// existing patient_id (created by the slim maternal_process.php) in $_POST.

require_once "../../../module/db.config.php";
session_start();

header('Content-Type: application/json');

function respond($status, $data = []) {
    echo json_encode(array_merge(['status' => $status], $data));
    exit();
}

if (!isset($_POST['submit_btn']) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    respond('error', ['message' => 'Invalid request']);
}

if (empty($_POST['patient_id']) || !is_numeric($_POST['patient_id'])) {
    respond('error', ['message' => 'Missing or invalid patient_id']);
}
$patient_id = (int)$_POST['patient_id'];

$conn->begin_transaction();

try {

    // insert pregnancy
    $sql_pregnancy = file_get_contents('../../../queries/maternal_insert/insert_pregnancy.sql');
    $stmt_pregnancy = $conn->prepare($sql_pregnancy);

    $gravidity = (isset($_POST['gravidity']) && $_POST['gravidity'] !== '') ? $_POST['gravidity'] : null;
    $parity    = (isset($_POST['parity']) && $_POST['parity'] !== '') ? $_POST['parity'] : null;

    $stmt_pregnancy->bind_param(
        "isiissss",
        $patient_id,
        $_POST['lmp'],
        $gravidity,
        $parity,
        $_POST['edc'],
        $_POST['outcome'],
        $_POST['date_terminated'],
        $_POST['sex']
    );

    if (!$stmt_pregnancy->execute()) {
        throw new Exception("Pregnancy Details Failed: " . $stmt_pregnancy->error);
    }
    $pregnancy_id = $conn->insert_id;
    $stmt_pregnancy->close();
    // end -> insert pregnancy

    // insert delivery
    $sql_delivery = file_get_contents('../../../queries/maternal_insert/insert_delivery.sql');
    $stmt_delivery = $conn->prepare($sql_delivery);

    $bemonc_cemonc_capable = isset($_POST['bemonc_cemonc_capable']) ? 1 : 0;
    $birth_weight = (isset($_POST['birth_weight']) && $_POST['birth_weight'] !== '') ? $_POST['birth_weight'] : null;

    $stmt_delivery->bind_param(
        "issdssisssss",
        $pregnancy_id,
        $_POST['delivery_type'],
        $_POST['birth_weight_classification'],
        $birth_weight,
        $_POST['health_facility_type'],
        $_POST['health_facility_name'],
        $bemonc_cemonc_capable,
        $_POST['ownership'],
        $_POST['non_health_facility_type'],
        $_POST['non_health_facility_name'],
        $_POST['birth_attendant'],
        $_POST['remarks']
    );

    if (!$stmt_delivery->execute()) {
        throw new Exception("Delivery Insert Failed: " . $stmt_delivery->error);
    }
    $stmt_delivery->close();
    // end -> insert delivery

    // insert prenatal checkup
    $sql_prenatalCheckup = file_get_contents('../../../queries/maternal_insert/insert_prenatal_checkup.sql');
    $stmt_prenatalCheckup = $conn->prepare($sql_prenatalCheckup);

    $bmi = (isset($_POST['bmi']) && $_POST['bmi'] !== '') ? $_POST['bmi'] : null;

    $stmt_prenatalCheckup->bind_param(
        "isssdsss",
        $pregnancy_id,
        $_POST['checkup_date'],
        $_POST['trimester'],
        $_POST['bmi_class'],
        $bmi,
        $_POST['deworming_status'],
        $_POST['deworming_date_given'],
        $_POST['remarks']
    );

    if (!$stmt_prenatalCheckup->execute()) {
        throw new Exception("Prenatal Check-up Insert Failed: " . $stmt_prenatalCheckup->error);
    }
    $stmt_prenatalCheckup->close();
    // end -> insert prenatal checkup

    // insert disease screening
    $sql_disease_screening = file_get_contents('../../../queries/maternal_insert/insert_maternal_screening.sql');
    $stmt_disease_screening = $conn->prepare($sql_disease_screening);

    $given_iron = isset($_POST['given_iron']) ? 1 : 0;
    $cbc_hgb_hct_count = (isset($_POST['cbc_hgb_hct_count']) && $_POST['cbc_hgb_hct_count'] !== '') ? $_POST['cbc_hgb_hct_count'] : null;

    $stmt_disease_screening->bind_param(
        "issssssssssssdsssiss",
        $pregnancy_id,
        $_POST['syphilis_screening'],
        $_POST['syphilis_date'],
        $_POST['syphilis_screening_remarks'],
        $_POST['hepatitis_b_screening'],
        $_POST['hepatitisB_date'],
        $_POST['hepatitis_b_screening_remarks'],
        $_POST['hiv_screening'],
        $_POST['hiv_date'],
        $_POST['hiv_screening_remarks'],
        $_POST['gestational_diabetes_screening'],
        $_POST['gestational_diabetes_date'],
        $_POST['diabetes_remarks'],
        $cbc_hgb_hct_count,
        $_POST['cbc_hgb_hct_date'],
        $_POST['anemia_status'],
        $_POST['anemia_status_remarks'],
        $given_iron,
        $_POST['given_iron_date'],
        $_POST['remarks']
    );

    if (!$stmt_disease_screening->execute()) {
        throw new Exception("Maternal Disease Screening Insert Failed: " . $stmt_disease_screening->error);
    }
    $stmt_disease_screening->close();
    // end -> insert disease screening

    // insert immunization (repeatable rows)
    $sql_immunization = file_get_contents('../../../queries/maternal_insert/insert_maternal_immunization.sql');
    $stmt_immunization = $conn->prepare($sql_immunization);

    if (isset($_POST['immunization_type']) && is_array($_POST['immunization_type'])) {
        $immunization_types = $_POST['immunization_type'];
        $immunization_dates = $_POST['immunization_date'] ?? [];

        for ($i = 0; $i < count($immunization_types); $i++) {
            $current_type = trim($immunization_types[$i] ?? '');
            $current_date = (!empty($immunization_dates[$i])) ? $immunization_dates[$i] : '0000-00-00';
            if (empty($current_type)) continue;

            $stmt_immunization->bind_param("iss", $pregnancy_id, $current_type, $current_date);
            if (!$stmt_immunization->execute()) {
                throw new Exception("Immunization Insert Failed " . ($i + 1) . ": " . $stmt_immunization->error);
            }
        }
    }
    $stmt_immunization->close();
    // end -> insert immunization

    // insert fim status
    $sql_fim = file_get_contents('../../../queries/maternal_insert/fim_status_mat.sql');
    $stmt_fim = $conn->prepare($sql_fim);
    $fim_status = isset($_POST['fim_status']) ? 1 : 0;
    $stmt_fim->bind_param("ii", $pregnancy_id, $fim_status);
    if (!$stmt_fim->execute()) {
        throw new Exception("Fim Status Insert Failed: " . $stmt_fim->error);
    }
    $stmt_fim->close();
    // end -> insert fim status

    // insert dynamic supplements (repeatable rows)
    $sql_supplements = file_get_contents('../../../queries/maternal_insert/insert_maternal_supp.sql');
    $stmt_supplements = $conn->prepare($sql_supplements);

    if (isset($_POST['supplement_type']) && is_array($_POST['supplement_type'])) {
        $supplement_types = $_POST['supplement_type'];
        $trimesters = $_POST['supp_trimester'] ?? [];
        $dates_supp = $_POST['date_supp'] ?? [];
        $tablets_given_array = $_POST['supp_tablets_given'] ?? [];

        for ($i = 0; $i < count($supplement_types); $i++) {
            $current_supplement_type = trim($supplement_types[$i] ?? '');
            if (empty($current_supplement_type)) continue;
            $current_trimester = trim($trimesters[$i] ?? '');
            $current_date_supp = (!empty($dates_supp[$i])) ? $dates_supp[$i] : '0000-00-00';
            $current_tablets_given = (int)intval($tablets_given_array[$i] ?? 0);

            $stmt_supplements->bind_param(
                "isssi",
                $pregnancy_id,
                $current_supplement_type,
                $current_trimester,
                $current_date_supp,
                $current_tablets_given
            );
            if (!$stmt_supplements->execute()) {
                throw new Exception("Maternal Supplements Insert Failed " . ($i + 1) . ": " . $stmt_supplements->error);
            }
        }
    }
    $stmt_supplements->close();
    // end -> insert dynamic supplements

    // insert iodine supplement
    $sql_iodine_supp = file_get_contents('../../../queries/maternal_insert/insert_iodine_pre.sql');
    $stmt_iodine = $conn->prepare($sql_iodine_supp);
    $iodine_capsule_given = isset($_POST['iodine_capsule_given']) ? 1 : 0;
    $stmt_iodine->bind_param("isi", $pregnancy_id, $_POST['date_iodine'], $iodine_capsule_given);
    if (!$stmt_iodine->execute()) {
        throw new Exception("Iodine Supplement Insert Failed: " . $stmt_iodine->error);
    }
    $stmt_iodine->close();
    // end -> insert iodine supplement

    $conn->commit();

    respond('success', [
        'patient_id'   => $patient_id,
        'pregnancy_id' => $pregnancy_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    respond('error', ['message' => $e->getMessage()]);
}