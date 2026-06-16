<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $facility_type = $_POST['health_facility_type'] ?? '';
    $facility_name = $_POST['health_facility_name'] ?? '';
    $ownership = $_POST['ownership'] ?? '';
    $bemonc_cemonc_capable = isset($_POST['bemonc_cemonc_capable']) ? 1 : 0;

 
    if (!$pregnancy_id) {
        echo "Missing Pregnancy ID";
        exit;
    }

    $query = " UPDATE delivery SET health_facility_type = ?, health_facility_name = ?, bemonc_cemonc_capable = ?, ownership = ? WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("ssisi", $facility_type, $facility_name, $bemonc_cemonc_capable, $ownership, $pregnancy_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
