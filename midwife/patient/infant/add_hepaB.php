<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $hepaB_day = $_POST['hepaB_day'] ?? '';
    $hepaB_date = $_POST['hepaB_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }


    $query = "UPDATE hepab SET hepaB_day = ?, hepaB_date = ? WHERE patient_id = ?";                 
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("ssi", $hepaB_day, $hepaB_date, $patient_id);


    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
