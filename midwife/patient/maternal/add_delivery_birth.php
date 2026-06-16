<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $delivery_type = $_POST['delivery_type'] ?? '';
    $weight_class = $_POST['birth_weight_classification'] ?? '';
    $birth_weight = $_POST['birth_weight'] ?? '';
    $birth_attendant = $_POST['birth_attendant'] ?? '';
 
    if (!$pregnancy_id) {
        echo "Missing Pregnancy ID";
        exit;
    }

    $query = " UPDATE delivery SET delivery_type = ?, birth_weight_classification = ?, birth_weight = ?, birth_attendant = ? WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("ssdsi", $delivery_type, $weight_class, $birth_weight, $birth_attendant, $pregnancy_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
