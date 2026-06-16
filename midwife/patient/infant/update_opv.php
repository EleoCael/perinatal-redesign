<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $opv_id = $_POST['opv_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $opv_type = $_POST['opv_type'] ?? '';
    $opv_date = $_POST['opv_date'] ?? '';

    if (empty($opv_id) || empty($patient_id) || empty($opv_type) || empty($opv_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
        $query = "UPDATE opv 
                  SET opv_type = ?, opv_date = ? 
                  WHERE opv_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $opv_type, $opv_date, $opv_id, $patient_id);
        
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