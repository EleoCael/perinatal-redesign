<?php

session_start();
require_once "../../module/db.config.php";

header('Content-Type: application/json');

try {
    $sql = "SELECT patient_id, first_name, last_name, patient_type 
            FROM patient 
            WHERE 1=1";
    
    $params = [];
    $types = "";
   
    if (isset($_SESSION['health_center_id'])) {
        $sql .= " AND health_center_id = ?";
        $params[] = $_SESSION['health_center_id'];
        $types .= "i";
    }
    
    $sql .= " ORDER BY first_name, last_name";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $patients = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $patients[] = $row;
        }
    }
    
    echo json_encode(['patients' => $patients]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>