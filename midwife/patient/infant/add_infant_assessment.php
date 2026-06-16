<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $birth_weight = $_POST['birth_weight'] ?? '';
    $birth_height = $_POST['birth_height'] ?? '';
    $sex = $_POST['sex'] ?? '';
 
    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $query = " UPDATE infant SET birth_weight = ?, birth_height = ?, sex = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("ddsi", $birth_weight, $birth_height, $sex, $patient_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
