<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $immunization_type = $_POST['immunization_type'] ?? '';
    $immunization_date = $_POST['immunization_date'] ?? '';

    if (!$pregnancy_id || !$immunization_type || !$immunization_date) {
        echo "Missing fields";
        exit;
    }

    $insert_query = "INSERT INTO maternal_immunization (pregnancy_id, immunization_type, immunization_date)
                     VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iss", $pregnancy_id, $immunization_type, $immunization_date);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
