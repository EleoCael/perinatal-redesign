<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $iron_id = $_POST['iron_id'] ?? null;
    
    if (!$iron_id) {
        echo json_encode(["success" => false, "message" => "Missing iron ID"]);
        exit;
    }

    $delete_query = "DELETE FROM iron_infant WHERE iron_infant_id = ?";
    $stmt = $conn->prepare($delete_query);
    
    if ($stmt === false) {
        error_log("Delete Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("i", $iron_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Iron record deleted successfully"]);
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