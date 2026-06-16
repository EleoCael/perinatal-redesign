<?php
require_once "../../../module/db.config.php";

header('Content-Type: text/plain');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $complementary_feeding_id = $_POST['complementary_feeding_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';
    $complementary_month_check = $_POST['complementary_month_check'] ?? '';
    $complementary_month_date = $_POST['complementary_month_date'] ?? '';


    if (empty($complementary_feeding_id) || empty($patient_id) || empty($complementary_month_check) || empty($complementary_month_date)) {
        echo "error: Missing required fields";
        exit;
    }

    try {
        $query = "UPDATE infant_complementary_feeding 
                  SET complementary_month_check = ?, complementary_month_date = ? 
                  WHERE complementary_feeding_id = ? AND patient_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $complementary_month_check, $complementary_month_date, $complementary_feeding_id, $patient_id);
        
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