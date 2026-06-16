<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $vitamin_id = $_POST['vitamin_id'] ?? null;
    
    if (!$vitamin_id) {
        echo json_encode(["success" => false, "message" => "Missing vitamin ID"]);
        exit;
    }

    $delete_query = "DELETE FROM vitamin_a_infant WHERE vitamin_a_infant_id = ?";
    $stmt = $conn->prepare($delete_query);
    
    if ($stmt === false) {
        error_log("Delete Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("i", $vitamin_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Vitamin record deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Record not found"]);
        }
    } else {
        error_log("Delete Execute Failed: " . $stmt->error);
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>