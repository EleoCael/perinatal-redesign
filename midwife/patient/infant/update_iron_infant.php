<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $iron_infant_id = $_POST['iron_infant_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $iron_type = $_POST['iron_type'] ?? '';
    $iron_date = $_POST['iron_date'] ?? '';

    if (empty($iron_infant_id) || empty($patient_id) || empty($iron_type) || empty($iron_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
   
        $query = "UPDATE iron_infant 
                  SET iron_type = ?, iron_date = ? 
                  WHERE iron_infant_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $iron_type, $iron_date, $iron_infant_id, $patient_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo "success";
            } else {
                echo "error: No records updated. Record may not exist.";
            }
        } else {
            echo "error: Database update failed";
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo "error: " . $e->getMessage();
    }
    
} else {
    echo "error: Invalid request method";
}

$conn->close();
?>