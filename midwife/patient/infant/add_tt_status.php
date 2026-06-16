<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $cpab_tt_status = $_POST['cpab_tt_status'] ?? '';
    $cpab_tt_date = $_POST['cpab_tt_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $query = " UPDATE infant SET cpab_tt_status = ?, cpab_tt_date = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("ssi", $cpab_tt_status, $cpab_tt_date, $patient_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
