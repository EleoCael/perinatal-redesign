<?php
// appointments/sms_scheduler_v2.php - Updated for appointments table
require_once "../../module/db.config.php";
require_once "../../module/sms.config.php";

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

function sendAppointmentRemindersV2() {
    global $conn;
    
    // Calculate date 24 hours from now
    $target_date = date('Y-m-d', strtotime('+24 hours'));
    
    echo "=== RHU System SMS Scheduler V2 (Appointments Table) ===\n";
    echo "Time: " . date('Y-m-d H:i:s') . "\n";
    echo "Checking appointments for: " . $target_date . "\n\n";
    
    // Find appointments that need reminders from appointments table
    $sql = "SELECT 
                a.appointment_id,
                a.patient_id,
                p.first_name,
                p.last_name,
                p.contact_number,
                a.appointment_date,
                a.appointment_time,
                a.appointment_type
            FROM appointments a 
            JOIN patient p ON a.patient_id = p.patient_id 
            WHERE a.appointment_date = ? 
            AND a.status = 'scheduled'
            AND p.contact_number IS NOT NULL
            AND LENGTH(TRIM(p.contact_number)) >= 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $target_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reminders_sent = 0;
    $errors = 0;
    
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " appointments needing reminders.\n\n";
        
        while ($appointment = $result->fetch_assoc()) {
            echo "👤 Patient: " . $appointment['first_name'] . " " . $appointment['last_name'] . "\n";
            echo "📅 Appointment: " . $appointment['appointment_date'] . " at " . $appointment['appointment_time'] . "\n";
            echo "📞 Contact: " . $appointment['contact_number'] . "\n";
            
            // Format the appointment time for the message
            $appointment_time = date('g:i A', strtotime($appointment['appointment_time']));
            $appointment_date = date('F j, Y', strtotime($appointment['appointment_date']));
            
            // Create the reminder message
            $message = "Magandang araw po! Paalala mula sa RHU, may nakatakdang " 
                       . $appointment['appointment_type'] . " appointment na naka schedule para kay " . 
                       $appointment['first_name'] . " " . $appointment['last_name'] .
                     " sa darating na " . $appointment_date .", ". " sa oras na " . $appointment_time . ". " .
                      "Mangyaring pumunta sa health center sa oras na ito. Maraming salamat po!";
            
            echo "💬 Message: " . substr($message, 0, 50) . "...\n";
   
            // Send SMS
            $sms_result = sendSMS($appointment['contact_number'], $message);
            
            if ($sms_result['success']) {
                echo "✅ SMS sent successfully! Message ID: " . $sms_result['message_id'] . "\n";
                $reminders_sent++;
            } else {
                echo "❌ SMS failed: " . ($sms_result['error'] ?? $sms_result['message']) . "\n";
                $errors++;
            }
            
            echo "----------------------------------------\n";
        }
    } else {
        echo "✅ No appointments found needing reminders for " . $target_date . "\n";
    }
    
    $stmt->close();
    
    echo "\n📊 Summary:\n";
    echo "Reminders sent: " . $reminders_sent . "\n";
    echo "Errors: " . $errors . "\n";
    echo "Total processed: " . ($reminders_sent + $errors) . "\n";
}

// Run the reminder function
try {
    sendAppointmentRemindersV2();
    echo "\n=== Scheduler V2 completed successfully ===\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    error_log("SMS Scheduler V2 Error: " . $e->getMessage());
}
?>