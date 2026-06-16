<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $cpab_tt_date_assessed = $_POST['cpab_tt_date_assessed'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $query = " UPDATE infant SET cpab_tt_date_assessed = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("si", $cpab_tt_date_assessed,  $patient_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
