<?php
// SCAFFOLD — wire this up once the "Add Medical Info" form (Postpartum) is built.
// Extracted from the original monolithic maternal_process.php / postpartum_process.php.
// Expects an existing patient_id (created by the slim postpartum_process.php) in $_POST.
// pregnancy_id is optional — pass it if this postpartum checkup should link back to
// an antenatal pregnancy record; otherwise it's stored as NULL.

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
$pregnancy_id = !empty($_POST['pregnancy_id']) ? intval($_POST['pregnancy_id']) : null;

$conn->begin_transaction();

try {

    // postpartum checkup
    $sql_postpartum_checkup = file_get_contents('../../../queries/maternal_insert/insert_postpartum.sql');
    $stmt_postpartum_checkup = $conn->prepare($sql_postpartum_checkup);

    $stmt_postpartum_checkup->bind_param(
        "iissssss",
        $patient_id,
        $pregnancy_id,
        $_POST['post_delivery_date'],
        $_POST['post_delivery_time'],
        $_POST['checkup_visit'],
        $_POST['post_checkup_date'],
        $_POST['breastfeeding_date'],
        $_POST['breastfeeding_time']
    );

    if (!$stmt_postpartum_checkup->execute()) {
        throw new Exception("Postpartum Checkup Insert Failed: " . $stmt_postpartum_checkup->error);
    }
    $stmt_postpartum_checkup->close();
    // end -> postpartum checkup

    // postpartum supplement
    $sql_postpartum_supp = file_get_contents('../../../queries/maternal_insert/insert_post_supp.sql');
    $stmt_postpartum_supp = $conn->prepare($sql_postpartum_supp);

    $tablets_given = (isset($_POST['tablets_given']) && $_POST['tablets_given'] !== '') ? $_POST['tablets_given'] : null;

    $stmt_postpartum_supp->bind_param(
        "iissis",
        $patient_id,
        $pregnancy_id,
        $_POST['iron_folic_month_given'],
        $_POST['iron_folic_date_given'],
        $tablets_given,
        $_POST['remarks']
    );

    if (!$stmt_postpartum_supp->execute()) {
        throw new Exception("Postpartum Supplement Insert Failed: " . $stmt_postpartum_supp->error);
    }
    $stmt_postpartum_supp->close();
    // end -> postpartum supplement

    // vitamin A postpartum
    $sql_vitamin = file_get_contents('../../../queries/maternal_insert/insert_vitamin.sql');
    $stmt_vitamin = $conn->prepare($sql_vitamin);

    $vitamin_a = isset($_POST['vitamin_a']) ? 1 : 0;

    $stmt_vitamin->bind_param(
        "iiis",
        $patient_id,
        $pregnancy_id,
        $vitamin_a,
        $_POST['vitamin_a_date']
    );

    if (!$stmt_vitamin->execute()) {
        throw new Exception("Vitamin A Insert Failed: " . $stmt_vitamin->error);
    }
    $stmt_vitamin->close();
    // end -> vitamin A postpartum

    $conn->commit();

    respond('success', ['patient_id' => $patient_id]);

} catch (Exception $e) {
    $conn->rollback();
    respond('error', ['message' => $e->getMessage()]);
}