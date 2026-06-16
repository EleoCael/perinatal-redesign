<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $rvv_type = $_POST['rvv_type'] ?? '';
    $rvv_date = $_POST['rvv_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

   $query = "INSERT INTO rota_virus_vaccine (patient_id, rvv_type, rvv_date)
                     VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("iss", $patient_id, $rvv_type, $rvv_date);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
