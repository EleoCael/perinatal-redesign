<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $infant_exclusively_breastfed_id = $_POST['infant_exclusively_breastfed_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $month_check = $_POST['month_check'] ?? '';
    $month_date = $_POST['month_date'] ?? '';

    if (empty($infant_exclusively_breastfed_id) || empty($patient_id) || empty($month_check) || empty($month_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
        $query = "UPDATE infant_exclusively_breastfed 
                  SET month_check = ?, month_date = ? 
                  WHERE infant_exclusively_breastfed_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $month_check, $month_date, $infant_exclusively_breastfed_id, $patient_id);
        
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