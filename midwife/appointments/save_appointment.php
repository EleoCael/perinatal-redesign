<?php
// appointments/save_appointment.php
require_once "../../module/db.config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $patient_id = $_POST['patient_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $appointment_type = $_POST['appointment_type'] ?? '';
    $remarks = $_POST['remarks'] ?? '';
    
    // Validate required fields
    if (empty($patient_id) || empty($appointment_date) || empty($appointment_time) || empty($appointment_type)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields.'
        ]);
        exit();
    }
    
    try {
        // Get current user ID (you'll need to adjust this based on your session)
        // For now, using a default value - replace with actual logged-in user ID
        session_start();
$created_by = $_SESSION['user_id']; // or whatever you use in login
                
        
        // Get health center ID from patient
        $health_center_sql = "SELECT health_center_id FROM patient WHERE patient_id = ?";
        $stmt = $conn->prepare($health_center_sql);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $health_center_result = $stmt->get_result();
        
        if ($health_center_result->num_rows > 0) {
            $patient_data = $health_center_result->fetch_assoc();
            $health_center_id = $patient_data['health_center_id'];
            
            // Insert into appointments table
            $sql = "INSERT INTO appointments (patient_id, health_center_id, appointment_date, appointment_time, appointment_type, remarks, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissssi", $patient_id, $health_center_id, $appointment_date, $appointment_time, $appointment_type, $remarks, $created_by);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Appointment scheduled successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error saving appointment: ' . $conn->error
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Patient not found'
            ]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>