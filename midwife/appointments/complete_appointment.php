<?php
// appointments/complete_appointment.php
require_once "../../module/db.config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = $_POST['appointment_id'] ?? '';
    
    if (empty($appointment_id)) {
        echo json_encode([
            'success' => false,
            'message' => 'Appointment ID is required'
        ]);
        exit();
    }
    
    try {
        // Update appointment status to 'completed'
        $sql = "UPDATE appointments SET status = 'completed', updated_at = NOW() WHERE appointment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $appointment_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Appointment marked as completed successfully!'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Appointment not found or already completed'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error updating appointment: ' . $conn->error
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