<?php
// search_patients.php
session_start();
require_once "../../module/db.config.php";

header('Content-Type: application/json');

$searchTerm = $_POST['search_term'] ?? '';
$patientType = $_POST['patient_type'] ?? '';

try {
    // Build query based on search term and patient type
    $sql = "SELECT patient_id, first_name, last_name, patient_type, contact_number, name_of_mother 
            FROM patient 
            WHERE 1=1";
    
    $params = [];
    $types = "";

    if (isset($_SESSION['health_center_id'])) {
        $sql .= " AND health_center_id = ?";
        $params[] = $_SESSION['health_center_id'];
        $types .= "i";
    }
    
    // Add patient type filter if specified
    if (!empty($patientType)) {
        $sql .= " AND patient_type = ?";
        $params[] = $patientType;
        $types .= "s";
    }
    
    // Add search term filter if specified
    if (!empty($searchTerm)) {
        $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR name_of_mother LIKE ?)";
        $searchParam = "%$searchTerm%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }
    
    $sql .= " ORDER BY first_name, last_name LIMIT 20";
    
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