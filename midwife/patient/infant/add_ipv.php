<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $ipv_1_value = isset($_POST['ipv_1']) ? 1 : 0;
    $ipv_date = $_POST['ipv_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $check_ipv = "SELECT ipv_id FROM ipv WHERE patient_id = ?";
    $stmt_check_ipv= $conn->prepare($check_ipv);
    if ($stmt_check_ipv === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt_check_ipv->bind_param("i", $patient_id);
    $stmt_check_ipv->execute();
    $result_ipv = $stmt_check_ipv->get_result();
    $exist_ipv = $result_ipv->fetch_assoc();
    $stmt_check_ipv->close();

    if ($exist_ipv) {
        $ipv_id = $exist_ipv['ipv_id'];
        $query = "UPDATE ipv SET ipv_1 = ?, ipv_date = ? WHERE ipv_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $ipv_1_value, $ipv_date, $ipv_id);
    } else {
        $insert_ipv= "INSERT INTO ipv (patient_id, ipv_1, ipv_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_ipv);
        $stmt->bind_param("iis", $patient_id, $ipv_1_value, $ipv_date);
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
