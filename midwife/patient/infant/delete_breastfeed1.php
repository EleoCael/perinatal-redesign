<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $infant_exclusively_breastfed_id  = $_POST['infant_exclusively_breastfed_id'] ?? null;
    
    if (!$infant_exclusively_breastfed_id) {
        echo json_encode(["success" => false, "message" => "Missing infant exclusively breastfed_id  ID"]);
        exit;
    }

    // Delete the checkup record
    $delete_query = "DELETE FROM infant_exclusively_breastfed WHERE infant_exclusively_breastfed_id = ?";
    $stmt = $conn->prepare($delete_query);
    
    if ($stmt === false) {
        error_log("Delete Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("i", $infant_exclusively_breastfed_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Infant Exclusively Breastfed deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Infant Exclusively Breastfed record not found"]);
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