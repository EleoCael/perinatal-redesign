<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $non_facility_type = $_POST['non_health_facility_type'] ?? '';
    $non_facility_name = $_POST['non_health_facility_name'] ?? '';
 
    if (!$pregnancy_id) {
        echo "Missing Pregnancy ID";
        exit;
    }

    $query = " UPDATE delivery SET non_health_facility_type = ?, non_health_facility_name = ?  WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("ssi", $non_facility_type, $non_facility_name, $pregnancy_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
