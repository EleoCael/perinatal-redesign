<?php
//delete_checkup.php before
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $checkup_id = $_POST['checkup_id'] ?? null;
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $trimester = $_POST['trimester'] ?? null;
    $checkup_date = $_POST['checkup_date'] ?? null;
    
    // Validate required fields
    if (!$checkup_id || !$pregnancy_id || !$trimester || !$checkup_date) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    // Update the checkup record
    $update_query = "UPDATE prenatal_checkup SET trimester = ?, checkup_date = ? WHERE checkup_id = ? AND pregnancy_id = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        error_log("Update Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("ssii", $trimester, $checkup_date, $checkup_id, $pregnancy_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Checkup updated successfully"]);
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