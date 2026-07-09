<?php
// SCAFFOLD — wire this up once the "Add Medical Info" form (Infant) is built.
// Extracted from the original monolithic infant_process.php. Expects an
// existing patient_id (created by the slim infant_process.php) in $_POST.

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
$mother_id = $_POST['mother_id'] ?? null;

$conn->begin_transaction();

try {

    // insert infant screening
    $delivery_id = NULL;
    $sql_infant_screening = file_get_contents('../../../queries/infant_insert/insert_infant_screening.sql');
    $stmt_infant_screening = $conn->prepare($sql_infant_screening);

    $stmt_infant_screening->bind_param(
        "iiiddssssss",
        $patient_id,
        $delivery_id,
        $mother_id,
        $_POST['birth_weight'],
        $_POST['birth_height'],
        $_POST['sex'],
        $_POST['newborn_screening_referral'],
        $_POST['newborn_screening_done'],
        $_POST['cpab_tt_status'],
        $_POST['cpab_tt_date'],
        $_POST['cpab_tt_date_assessed']
    );

    if (!$stmt_infant_screening->execute()) {
        throw new Exception("Infant Screening Insert Failed: " . $stmt_infant_screening->error);
    }
    $stmt_infant_screening->close();
    // end -> insert infant screening

    // insert infant exclusive breastfeeding (repeatable rows)
    $sql_infant_exclusive_feeding = file_get_contents('../../../queries/infant_insert/insert_feeding.sql');
    $stmt_infant_exclusive_feeding = $conn->prepare($sql_infant_exclusive_feeding);

    if (isset($_POST['month_check']) && is_array($_POST['month_check'])) {
        $month_check = $_POST['month_check'];
        $month_date = $_POST['month_date'] ?? [];

        for ($i = 0; $i < count($month_check); $i++) {
            $current_month_check = trim($month_check[$i] ?? '');
            if (empty($current_month_check)) continue;
            $current_month_date = (!empty($month_date[$i])) ? $month_date[$i] : '0000-00-00';

            $stmt_infant_exclusive_feeding->bind_param(
                "iss",
                $patient_id,
                $current_month_check,
                $current_month_date
            );
            if (!$stmt_infant_exclusive_feeding->execute()) {
                throw new Exception("Exclusive Feeding Insert Failed " . ($i + 1) . ": " . $stmt_infant_exclusive_feeding->error);
            }
        }
    }
    $stmt_infant_exclusive_feeding->close();
    // end -> insert infant exclusive breastfeeding

    // insert infant complementary breastfeeding (repeatable rows)
    $sql_infant_complementary_feeding = file_get_contents('../../../queries/infant_insert/insert_feeding_2.sql');
    $stmt_infant_complementary_feeding = $conn->prepare($sql_infant_complementary_feeding);

    if (isset($_POST['complementary_month_check']) && is_array($_POST['complementary_month_check'])) {
        $month_com_check = $_POST['complementary_month_check'];
        $month_com_date = $_POST['complementary_month_date'] ?? [];

        for ($i = 0; $i < count($month_com_check); $i++) {
            $current_month_com_check = trim($month_com_check[$i] ?? '');
            if (empty($current_month_com_check)) continue;
            $current_month_com_date = (!empty($month_com_date[$i])) ? $month_com_date[$i] : '0000-00-00';

            $stmt_infant_complementary_feeding->bind_param(
                "iss",
                $patient_id,
                $current_month_com_check,
                $current_month_com_date
            );
            if (!$stmt_infant_complementary_feeding->execute()) {
                throw new Exception("Complementary Feeding Insert Failed " . ($i + 1) . ": " . $stmt_infant_complementary_feeding->error);
            }
        }
    }
    $stmt_infant_complementary_feeding->close();
    // end -> insert infant complementary breastfeeding

    // insert bcg immunization
    $sql_bcg = file_get_contents('../../../queries/infant_insert/insert_bcg_immunization.sql');
    $stmt_bcg = $conn->prepare($sql_bcg);
    $is_bcg_check = isset($_POST['bcg_check']) ? 1 : 0;
    $stmt_bcg->bind_param("iis", $patient_id, $is_bcg_check, $_POST['bcg_date']);
    if (!$stmt_bcg->execute()) {
        throw new Exception("BCG Insert Failed: " . $stmt_bcg->error);
    }
    $stmt_bcg->close();

    // insert hepaB immunization
    $sql_hepaB = file_get_contents('../../../queries/infant_insert/insert_hepaB_immunization.sql');
    $stmt_hepaB = $conn->prepare($sql_hepaB);
    $stmt_hepaB->bind_param("iss", $patient_id, $_POST['hepaB_day'], $_POST['hepaB_date']);
    if (!$stmt_hepaB->execute()) {
        throw new Exception("HepaB Insert Failed: " . $stmt_hepaB->error);
    }
    $stmt_hepaB->close();

    // insert pentavalent
    $sql_pentavalent = file_get_contents('../../../queries/infant_insert/insert_pentavalent_immunization.sql');
    $stmt_pentavalent = $conn->prepare($sql_pentavalent);
    $stmt_pentavalent->bind_param("iss", $patient_id, $_POST['pentavalent_type'], $_POST['pentavalent_date']);
    if (!$stmt_pentavalent->execute()) {
        throw new Exception("Pentavalent Insert Failed: " . $stmt_pentavalent->error);
    }
    $stmt_pentavalent->close();

    // insert opv
    $sql_opv = file_get_contents('../../../queries/infant_insert/insert_opv_immunization.sql');
    $stmt_opv = $conn->prepare($sql_opv);
    $stmt_opv->bind_param("iss", $patient_id, $_POST['opv_type'], $_POST['opv_date']);
    if (!$stmt_opv->execute()) {
        throw new Exception("OPV Insert Failed: " . $stmt_opv->error);
    }
    $stmt_opv->close();

    // insert ipv
    $sql_ipv = file_get_contents('../../../queries/infant_insert/insert_ipv_immunization.sql');
    $stmt_ipv = $conn->prepare($sql_ipv);
    $is_ipv = isset($_POST['ipv_1']) ? 1 : 0;
    $stmt_ipv->bind_param("iis", $patient_id, $is_ipv, $_POST['ipv_date']);
    if (!$stmt_ipv->execute()) {
        throw new Exception("IPV Insert Failed: " . $stmt_ipv->error);
    }
    $stmt_ipv->close();

    // insert mcv
    $sql_mcv = file_get_contents('../../../queries/infant_insert/insert_mcv_immunization.sql');
    $stmt_mcv = $conn->prepare($sql_mcv);
    $stmt_mcv->bind_param("iss", $patient_id, $_POST['mcv_type'], $_POST['mcv_date']);
    if (!$stmt_mcv->execute()) {
        throw new Exception("MCV Insert Failed: " . $stmt_mcv->error);
    }
    $stmt_mcv->close();

    // insert fic
    $sql_fic = file_get_contents('../../../queries/infant_insert/insert_fic_immunization.sql');
    $stmt_fic = $conn->prepare($sql_fic);
    $is_fic = isset($_POST['fic_check']) ? 1 : 0;
    $stmt_fic->bind_param("iis", $patient_id, $is_fic, $_POST['fic_date']);
    if (!$stmt_fic->execute()) {
        throw new Exception("FIC Insert Failed: " . $stmt_fic->error);
    }
    $stmt_fic->close();

    // insert rvv
    $sql_rvv = file_get_contents('../../../queries/infant_insert/insert_rvv_immunization.sql');
    $stmt_rvv = $conn->prepare($sql_rvv);
    $stmt_rvv->bind_param("iss", $patient_id, $_POST['rvv_type'], $_POST['rvv_date']);
    if (!$stmt_rvv->execute()) {
        throw new Exception("RVV Insert Failed: " . $stmt_rvv->error);
    }
    $stmt_rvv->close();

    // insert pcv
    $sql_pcv = file_get_contents('../../../queries/infant_insert/insert_pcv_immunization.sql');
    $stmt_pcv = $conn->prepare($sql_pcv);
    $stmt_pcv->bind_param("iss", $patient_id, $_POST['pcv_type'], $_POST['pcv_date']);
    if (!$stmt_pcv->execute()) {
        throw new Exception("PCV Insert Failed: " . $stmt_pcv->error);
    }
    $stmt_pcv->close();

    // insert vitamin A
    $sql_vitamin = file_get_contents('../../../queries/infant_insert/insert_vitamin_a.sql');
    $stmt_vitamin = $conn->prepare($sql_vitamin);
    $stmt_vitamin->bind_param("iss", $patient_id, $_POST['vitamin_type'], $_POST['vitamin_date']);
    if (!$stmt_vitamin->execute()) {
        throw new Exception("Vitamin A Insert Failed: " . $stmt_vitamin->error);
    }
    $stmt_vitamin->close();

    // insert iron
    $sql_iron = file_get_contents('../../../queries/infant_insert/insert_iron_supp.sql');
    $stmt_iron = $conn->prepare($sql_iron);
    $stmt_iron->bind_param("iss", $patient_id, $_POST['iron_type'], $_POST['iron_date']);
    if (!$stmt_iron->execute()) {
        throw new Exception("Iron Supplement Insert Failed: " . $stmt_iron->error);
    }
    $stmt_iron->close();

    // insert mnp
    $sql_mnp = file_get_contents('../../../queries/infant_insert/insert_mnp_supp.sql');
    $stmt_mnp = $conn->prepare($sql_mnp);
    $stmt_mnp->bind_param("iss", $patient_id, $_POST['mnp_type'], $_POST['mnp_date']);
    if (!$stmt_mnp->execute()) {
        throw new Exception("MNP Insert Failed: " . $stmt_mnp->error);
    }
    $stmt_mnp->close();

    // insert deworming
    $sql_deworming = file_get_contents('../../../queries/infant_insert/insert_deworming.sql');
    $stmt_deworming = $conn->prepare($sql_deworming);
    $is_dewormed = isset($_POST['deworming_check']) ? 1 : 0;
    $stmt_deworming->bind_param("iis", $patient_id, $is_dewormed, $_POST['deworming_date']);
    if (!$stmt_deworming->execute()) {
        throw new Exception("Deworming Insert Failed: " . $stmt_deworming->error);
    }
    $stmt_deworming->close();

    $conn->commit();

    respond('success', ['patient_id' => $patient_id]);

} catch (Exception $e) {
    $conn->rollback();
    respond('error', ['message' => $e->getMessage()]);
}