<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $complementary_month_check = $_POST['complementary_month_check'] ?? '';
    $complementary_month_date = $_POST['complementary_month_date'] ?? '';

    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

   $query = "INSERT INTO infant_complementary_feeding (patient_id, complementary_month_check, complementary_month_date)
                     VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("iss", $patient_id, $complementary_month_check, $complementary_month_date);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
