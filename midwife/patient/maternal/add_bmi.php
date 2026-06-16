<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $bmi_class = $_POST['bmi_class'] ?? '';
    $bmi = $_POST['bmi'] ?? '';
    $deworming_stat = $_POST['deworming_status'] ?? '';
    $deworming_date = $_POST['deworming_date_given'] ?? '';

    if (!$pregnancy_id) {
        echo "Missing Pregnancy ID";
        exit;
    }

    $query = " UPDATE prenatal_checkup SET bmi_class = ?, bmi = ?, deworming_status = ?, deworming_date_given = ? WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("sdssi", $bmi_class, $bmi, $deworming_stat, $deworming_date, $pregnancy_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
