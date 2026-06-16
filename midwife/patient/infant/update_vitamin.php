<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $vitamin_a_infant_id = $_POST['vitamin_a_infant_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $vitamin_type = $_POST['vitamin_type'] ?? '';
    $vitamin_date = $_POST['vitamin_date'] ?? '';

    if (empty($vitamin_a_infant_id) || empty($patient_id) || empty($vitamin_type) || empty($vitamin_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
    
        $query = "UPDATE vitamin_a_infant 
                  SET vitamin_type = ?, vitamin_date = ? 
                  WHERE vitamin_a_infant_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $vitamin_type, $vitamin_date, $vitamin_a_infant_id, $patient_id);
        
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