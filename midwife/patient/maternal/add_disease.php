<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $syphilis_date = $_POST['syphilis_date'] ?? '';
    $syphilis_screening = $_POST['syphilis_screening'] ?? '';
    $syphilis_remarks = $_POST['syphilis_screening_remarks'] ?? '';
    $hepatitisB_date = $_POST['hepatitisB_date'] ?? '';
    $hepatitis_b_screening = $_POST['hepatitis_b_screening'] ?? '';
    $hepatitis_b_remarks = $_POST['hepatitis_b_screening_remarks'] ?? '';
   
    if (!$pregnancy_id) {
         echo json_encode(["success" => false, "message" => "Missing Pregnancy ID"]);
        exit;
    }

    $query = " UPDATE maternal_disease_screening SET syphilis_date = ?, syphilis_screening = ?, syphilis_screening_remarks = ?,
     hepatitisB_date = ?, hepatitis_b_screening = ?, hepatitis_b_screening_remarks = ? WHERE pregnancy_id = ?";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("ssssssi", $syphilis_date, $syphilis_screening, $syphilis_remarks,
     $hepatitisB_date, $hepatitis_b_screening, $hepatitis_b_remarks, $pregnancy_id);

    if ($stmt->execute()) {
          echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
