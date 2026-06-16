<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $pentavalent_id = $_POST['pentavalent_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $pentavalent_type = $_POST['pentavalent_type'] ?? '';
    $pentavalent_date = $_POST['pentavalent_date'] ?? '';

    if (empty($pentavalent_id) || empty($patient_id) || empty($pentavalent_type) || empty($pentavalent_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
        $query = "UPDATE pentavalent 
                  SET pentavalent_type = ?, pentavalent_date = ? 
                  WHERE pentavalent_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $pentavalent_type, $pentavalent_date, $pentavalent_id, $patient_id);
        
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