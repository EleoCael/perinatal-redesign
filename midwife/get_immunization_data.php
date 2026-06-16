<?php

require_once "../module/db.config.php";
session_start();

if (!isset($_SESSION['health_center_id']) || empty($_SESSION['health_center_id'])) {
    die(json_encode(['error' => 'Access denied']));
}

$health_center_id = $_SESSION['health_center_id'];
$current_year = date('Y');

header('Content-Type: application/json');

try {
  
$target_sql = "SELECT target_month, target_value 
               FROM monthly_targets 
               WHERE target_year = ? AND health_center_id = ?
               ORDER BY target_month";
               
error_log("SQL: " . $target_sql);
error_log("Params: year=$current_year, health_center_id=$health_center_id");

$stmt = $conn->prepare($target_sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    die(json_encode(['error' => 'SQL prepare failed']));
}

if (!$stmt->bind_param("ii", $current_year, $health_center_id)) {
    error_log("Bind failed: " . $stmt->error);
    die(json_encode(['error' => 'SQL bind failed']));
}

if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    die(json_encode(['error' => 'SQL execute failed']));
}

$target_result = $stmt->get_result();
if (!$target_result) {
    error_log("Get result failed: " . $stmt->error);
    die(json_encode(['error' => 'SQL get result failed']));
}
    
    $monthly_targets = array_fill(1, 12, 0);
    while ($row = $target_result->fetch_assoc()) {
        $monthly_targets[$row['target_month']] = $row['target_value'];
    }
    $stmt->close();
  
    $actual_sql = "SELECT MONTH(fic_date) as month, COUNT(*) as count 
                   FROM fic 
                   JOIN patient ON fic.patient_id = patient.patient_id 
                   WHERE fic.fic_check = 1 
                   AND YEAR(fic_date) = ? 
                   AND patient.health_center_id = ?
                   AND fic_date != '0000-00-00'
                   GROUP BY MONTH(fic_date) 
                   ORDER BY month";
    $stmt = $conn->prepare($actual_sql);
    $stmt->bind_param("ii", $current_year, $health_center_id);
    $stmt->execute();
    $actual_result = $stmt->get_result();
    
    $monthly_actual = array_fill(1, 12, 0); 
    while ($row = $actual_result->fetch_assoc()) {
        $monthly_actual[$row['month']] = $row['count'];
    }
    $stmt->close();
    

    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
               'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    $target_data = array_values($monthly_targets);
    $actual_data = array_values($monthly_actual);
    
    echo json_encode([
        'months' => $months,
        'targets' => $target_data,
        'actual' => $actual_data
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to fetch data: ' . $e->getMessage()]);
}

$conn->close();
?>