<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $rvv_id = $_POST['rvv_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $rvv_type = $_POST['rvv_type'] ?? '';
    $rvv_date = $_POST['rvv_date'] ?? '';

    if (empty($rvv_id) || empty($patient_id) || empty($rvv_type) || empty($rvv_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
        
        $query = "UPDATE rota_virus_vaccine 
                  SET rvv_type = ?, rvv_date = ? 
                  WHERE rvv_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $rvv_type, $rvv_date, $rvv_id, $patient_id);
        
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