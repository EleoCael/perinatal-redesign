<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $hiv_date = $_POST['hiv_date'] ?? '';
    $hiv_screening = $_POST['hiv_screening'] ?? '';
    $hiv_remarks = $_POST['hiv_screening_remarks'] ?? '';
   
    if (!$pregnancy_id) {
         echo json_encode(["success" => false, "message" => "Missing Pregnancy ID"]);
        exit;
    }

    $query = " UPDATE maternal_disease_screening SET hiv_date = ?, hiv_screening = ?, hiv_screening_remarks = ? WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("sssi",  $hiv_date, $hiv_screening, $hiv_remarks, $pregnancy_id);

    if ($stmt->execute()) {
          echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
