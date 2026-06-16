<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $mnp_id = $_POST['mnp_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $mnp_type = $_POST['mnp_type'] ?? '';
    $mnp_date = $_POST['mnp_date'] ?? '';

    // Validate required fields
    if (empty($mnp_id) || empty($patient_id) || empty($mnp_type) || empty($mnp_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
        // Prepare the update query
        $query = "UPDATE mnp 
                  SET mnp_type = ?, mnp_date = ? 
                  WHERE mnp_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $mnp_type, $mnp_date, $mnp_id, $patient_id);
        
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