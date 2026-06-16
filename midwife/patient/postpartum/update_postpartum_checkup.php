<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $checkup_id = $_POST['checkup_id'] ?? null;
    $patient_id = $_POST['patient_id'] ?? null;
    $checkup_visit = $_POST['checkup_visit'] ?? null;
    $post_checkup_date = $_POST['post_checkup_date'] ?? null;
    
    if (!$checkup_id || !$patient_id || !$checkup_visit || !$post_checkup_date) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    $update_query = "UPDATE post_partum_checkup SET checkup_visit = ?, post_checkup_date = ? WHERE checkup_id = ? AND patient_id = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        error_log("Update Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("ssii", $checkup_visit, $post_checkup_date, $checkup_id, $patient_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Postpartum checkup updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "No changes made or checkup not found"]);
        }
    } else {
        error_log("Update Execute Failed: " . $stmt->error);
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>