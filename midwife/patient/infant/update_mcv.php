<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $mcv_id = $_POST['mcv_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $mcv_type = $_POST['mcv_type'] ?? '';
    $mcv_date = $_POST['mcv_date'] ?? '';

    if (empty($mcv_id) || empty($patient_id) || empty($mcv_type) || empty($mcv_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
      
        $query = "UPDATE mcv 
                  SET mcv_type = ?, mcv_date = ? 
                  WHERE mcv_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $mcv_type, $mcv_date, $mcv_id, $patient_id);
        
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