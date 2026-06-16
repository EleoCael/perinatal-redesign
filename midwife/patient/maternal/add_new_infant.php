<?php
    require_once "../../../module/db.config.php";

    $patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? intval($_GET['patient_id']) : 0;

    if ($patient_id <=0) {
        header("Location: add_maternal.php");
        exit;
    }

    $stmt_patient = $conn->prepare("SELECT patient_id, first_name,
     last_name FROM patient WHERE
     patient_id = ?");
    $stmt_patient->bind_param("i", $patient_id);
    $stmt_patient->execute();
    $patient = $stmt_patient->get_result()->fetch_assoc();
    $stmt_patient->close();
?>
