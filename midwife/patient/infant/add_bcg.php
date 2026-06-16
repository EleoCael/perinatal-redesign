<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $bcg_check_value = isset($_POST['bcg_check']) ? 1 : 0;
    $bcg_date = $_POST['bcg_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $check_bcg = "SELECT bcg_id FROM bcg WHERE patient_id = ?";
    $stmt_check_bcg= $conn->prepare($check_bcg);
    if ($stmt_check_bcg === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt_check_bcg->bind_param("i", $patient_id);
    $stmt_check_bcg->execute();
    $result_bcg = $stmt_check_bcg->get_result();
    $exist_bcg = $result_bcg->fetch_assoc();
    $stmt_check_bcg->close();

    if ($exist_bcg) {
        $bcg_id = $exist_bcg['bcg_id'];
        $query = "UPDATE bcg SET bcg_check = ?, bcg_date = ? WHERE bcg_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $bcg_check_value, $bcg_date, $bcg_id);
    } else {
        $insert_bcg = "INSERT INTO bcg (patient_id, bcg_check, bcg_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_bcg);
        $stmt->bind_param("iis", $patient_id, $bcg_check_value, $bcg_date);
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
