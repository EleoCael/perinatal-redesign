<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $date_done = $_POST['newborn_screening_done'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $query = " UPDATE infant SET newborn_screening_done = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("si", $date_done, $patient_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
