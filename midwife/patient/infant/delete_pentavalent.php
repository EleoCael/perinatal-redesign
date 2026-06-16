<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $pentavalent_id = $_POST['pentavalent_id'] ?? null;
    
    if (!$pentavalent_id) {
        echo json_encode(["success" => false, "message" => "Missing pentavalent ID"]);
        exit;
    }

    $delete_query = "DELETE FROM pentavalent WHERE pentavalent_id = ?";
    $stmt = $conn->prepare($delete_query);
    
    if ($stmt === false) {
        error_log("Delete Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("i", $pentavalent_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Pentavalent record deleted successfully"]);
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