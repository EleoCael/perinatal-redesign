<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $pcv_id = $_POST['pcv_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $pcv_type = $_POST['pcv_type'] ?? '';
    $pcv_date = $_POST['pcv_date'] ?? '';

    if (empty($pcv_id) || empty($patient_id) || empty($pcv_type) || empty($pcv_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
        
        $query = "UPDATE pcv
                  SET pcv_type = ?, pcv_date = ? 
                  WHERE pcv_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $pcv_type, $pcv_date, $pcv_id, $patient_id);
        
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