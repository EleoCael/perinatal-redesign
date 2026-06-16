<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $given_iron = $_POST['given_iron'] ?? '';
    $given_iron_date = $_POST['given_iron_date'] ?? '';
    $maternal_screening_remark = $_POST['maternal_screening_remark'] ?? '';
   
    if (!$pregnancy_id) {
         echo json_encode(["success" => false, "message" => "Missing Pregnancy ID"]);
        exit;
    }

    $query = " UPDATE maternal_disease_screening SET given_iron = ?, given_iron_date = ?, maternal_screening_remark = ? WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }
 
    $stmt->bind_param("issi",  $given_iron, $given_iron_date, $maternal_screening_remark ,$pregnancy_id);

    if ($stmt->execute()) {
          echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
