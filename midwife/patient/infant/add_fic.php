<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $fic_check_value = isset($_POST['fic_check']) ? 1 : 0;
    $fic_date = $_POST['fic_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $check_fic = "SELECT fic_id FROM fic WHERE patient_id = ?";
    $stmt_check_fic= $conn->prepare($check_fic);
    if ($stmt_check_fic === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt_check_fic->bind_param("i", $patient_id);
    $stmt_check_fic->execute();
    $result_fic = $stmt_check_fic->get_result();
    $exist_fic = $result_fic->fetch_assoc();
    $stmt_check_fic->close();

    if ($exist_fic) {
        $fic_id = $exist_fic['fic_id'];
        $query = "UPDATE fic SET fic_check = ?, fic_date = ? WHERE fic_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $fic_check_value, $fic_date, $fic_id);
    } else {
        $insert_bcg = "INSERT INTO fic (patient_id, fic_check, fic_date) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_bcg);
        $stmt->bind_param("iis", $patient_id, $fic_check_value, $fic_date);
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
