<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $pentavalent_type = $_POST['pentavalent_type'] ?? '';
    $pentavalent_date = $_POST['pentavalent_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

   $query = "INSERT INTO pentavalent (patient_id, pentavalent_type, pentavalent_date)
                     VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("iss", $patient_id, $pentavalent_type, $pentavalent_date);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
