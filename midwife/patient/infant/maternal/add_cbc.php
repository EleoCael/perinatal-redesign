<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $cbc_hgb_hct_date = $_POST['cbc_hgb_hct_date'] ?? '';
    $anemia_status = $_POST['anemia_status'] ?? '';
    $cbc_hgb_hct_count = $_POST['cbc_hgb_hct_count'] ?? '';
    $anemia_remarks = $_POST['anemia_status_remarks'] ?? '';
   
    if (!$pregnancy_id) {
         echo json_encode(["success" => false, "message" => "Missing Pregnancy ID"]);
        exit;
    }

    $query = " UPDATE maternal_disease_screening SET cbc_hgb_hct_date = ?, anemia_status = ?, cbc_hgb_hct_count = ?, anemia_status_remarks = ? WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }
 
    $stmt->bind_param("ssisi", $cbc_hgb_hct_date, $anemia_status, $cbc_hgb_hct_count, $anemia_remarks, $pregnancy_id);

    if ($stmt->execute()) {
          echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
