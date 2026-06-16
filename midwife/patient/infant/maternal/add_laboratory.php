<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $gestational_date = $_POST['gestational_diabetes_date'] ?? '';
    $gestational_screening = $_POST['gestational_diabetes_screening'] ?? '';
    $gestational_remarks = $_POST['diabetes_remarks'] ?? '';
   
    if (!$pregnancy_id) {
         echo json_encode(["success" => false, "message" => "Missing Pregnancy ID"]);
        exit;
    }

    $query = " UPDATE maternal_disease_screening SET gestational_diabetes_date = ?, gestational_diabetes_screening = ?, diabetes_remarks = ?  WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }
 
    $stmt->bind_param("sssi", $gestational_date, $gestational_screening, $gestational_remarks,$pregnancy_id);

    if ($stmt->execute()) {
          echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
