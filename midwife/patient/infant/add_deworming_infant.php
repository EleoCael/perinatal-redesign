<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $deworming_check_value = isset($_POST['deworming_check']) ? 1 : 0;
    $deworming_date = $_POST['deworming_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $check_deworming = "SELECT deworming_infant_id FROM deworming_infant WHERE patient_id = ?";
    $stmt_check_deworming= $conn->prepare($check_deworming);
    if ($stmt_check_deworming === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt_check_deworming->bind_param("i", $patient_id);
    $stmt_check_deworming->execute();
    $result_deworming = $stmt_check_deworming->get_result();
    $exist_deworming = $result_deworming->fetch_assoc();
    $stmt_check_deworming->close();

    if ($exist_deworming) {
        $deworming_infant_id = $exist_deworming['deworming_infant_id'];
        $query = "UPDATE deworming_infant SET deworming_check = ?, deworming_date = ? WHERE deworming_infant_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $deworming_check_value, $deworming_date, $deworming_infant_id);
    } else {
        $insert_bcg = "INSERT INTO deworming_infant (patient_id, deworming_check, deworming_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_bcg);
        $stmt->bind_param("iis", $patient_id, $deworming_check_value, $deworming_date);
    }

    if ($stmt === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    if ($stmt->execute()) {
        echo "success";
    } else {
        error_log("Execute failed: " . $stmt->error);
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
