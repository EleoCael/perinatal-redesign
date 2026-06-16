<?php
//delete_immunization.php dati
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $maternal_immunization_id = $_POST['maternal_immunization_id'] ?? null;
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $immunization_type = $_POST['immunization_type'] ?? null;
    $immunization_date = $_POST['immunization_date'] ?? null;
    
    // Validate required fields
    if (!$maternal_immunization_id || !$pregnancy_id || !$immunization_type || !$immunization_date) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    // Update the immunization record
    $update_query = "UPDATE maternal_immunization SET immunization_type = ?, immunization_date = ? WHERE maternal_immunization_id = ? AND pregnancy_id = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        error_log("Update Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("ssii", $immunization_type, $immunization_date, $maternal_immunization_id, $pregnancy_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Immunization updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "No changes made or immunization not found"]);
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