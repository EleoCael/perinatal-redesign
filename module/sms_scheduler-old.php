<?php
// sms_scheduler.php - Run this script automatically via cron job
require_once "db.config.php";
require_once "sms.config.php";

// Set timezone to Philippines
date_default_timezone_set('Asia/Manila');

/**
 * Send SMS reminders for appointments happening in 24 hours
 */
function sendAppointmentReminders() {
    global $conn;
    
    // Calculate date 24 hours from now
    $reminder_time = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $target_date = date('Y-m-d', strtotime('+24 hours'));
    
    echo "Checking appointments for: " . $target_date . "\n";
    echo "Current time: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Find appointments that need reminders
    $sql = "SELECT 
                patient_id,
                first_name,
                last_name,
                contact_number,
                next_appointment_date,
                appointment_time,
                appointment_type,
                reminder_status
            FROM patient 
            WHERE next_appointment_date = ? 
            AND reminder_status = 'pending'
            AND contact_number IS NOT NULL
            AND contact_number != ''";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $target_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reminders_sent = 0;
    $errors = 0;
    
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " appointments needing reminders.\n\n";
        
        while ($patient = $result->fetch_assoc()) {
            echo "Processing: " . $patient['first_name'] . " " . $patient['last_name'] . "\n";
            echo "Appointment: " . $patient['next_appointment_date'] . " at " . $patient['appointment_time'] . "\n";
            echo "Contact: " . $patient['contact_number'] . "\n";
            
            // Format the appointment time for the message
            $appointment_time = date('g:i A', strtotime($patient['appointment_time']));
            
            // Create the reminder message
           $message = "Magandang araw po! Paalala mula sa RHU, may nakatakdang " 
                       . $patient['appointment_type'] . " appointment na naka schedule para kay " . $patient['first_name'] . " " . $patient['last_name'] .
                     " sa darating na" . $appointment_time . " sa oras na " . $appointment_time . ". " .
                      "Mangyaring pumunta sa health center sa oras na ito. Maraming salamat po!";
            
            echo "💬 Message: " . substr($message, 0, 50) . "...\n";
            
            // Send SMS
            $sms_result = sendSMS($patient['contact_number'], $message);
            
            if ($sms_result['success']) {
                echo "✅ SMS sent successfully! Message ID: " . $sms_result['message_id'] . "\n";
                
                // Update reminder status in database
                updateReminderStatus($patient['patient_id'], 'sent');
                $reminders_sent++;
                
            } else {
                echo "❌ SMS failed: " . ($sms_result['error'] ?? $sms_result['message']) . "\n";
                $errors++;
            }
            
            echo "----------------------------------------\n";
        }
        
    } else {
        echo "No appointments found needing reminders for " . $target_date . "\n";
    }
    
    $stmt->close();
    
    echo "\nSummary:\n";
    echo "Reminders sent: " . $reminders_sent . "\n";
    echo "Errors: " . $errors . "\n";
    echo "Total processed: " . ($reminders_sent + $errors) . "\n";
}

/**
 * Update reminder status in database
 */
function updateReminderStatus($patient_id, $status) {
    global $conn;
    
    $sql = "UPDATE patient 
            SET reminder_status = ?, 
                reminder_sent_at = NOW() 
            WHERE patient_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $patient_id);
    $stmt->execute();
    $stmt->close();
}

// Run the reminder function
echo "=== RHU System SMS Scheduler ===\n";
sendAppointmentReminders();
echo "\n=== Scheduler completed ===\n";
?>