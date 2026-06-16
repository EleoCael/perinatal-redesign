<?php
// test_sms_scheduler.php - For testing with today's date
require_once "db.config.php";
require_once "sms.config.php";

date_default_timezone_set('Asia/Manila');

// FOR TESTING: Use today's date instead of +24 hours
$target_date = '2025-10-25'; // Use your test appointment date

echo "=== TEST SMS SCHEDULER ===\n";
echo "Checking appointments for: " . $target_date . "\n\n";

$sql = "SELECT * FROM patient WHERE next_appointment_date = ? AND reminder_status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $target_date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($patient = $result->fetch_assoc()) {
        echo "Patient: " . $patient['first_name'] . " " . $patient['last_name'] . "\n";
        
        $message = "TEST: Hello " . $patient['first_name'] . "! Reminder for your " . 
                  $patient['appointment_type'] . " appointment today at " . 
                  date('g:i A', strtotime($patient['appointment_time'])) . ". Thank you!";
        
        $sms_result = sendSMS($patient['contact_number'], $message);
        
        if ($sms_result['success']) {
            echo "✅ SMS SENT! Message ID: " . $sms_result['message_id'] . "\n";
            
            // Update status
            $update_sql = "UPDATE patient SET reminder_status = 'sent', reminder_sent_at = NOW() WHERE patient_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $patient['patient_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
        } else {
            echo "❌ FAILED: " . ($sms_result['error'] ?? $sms_result['message']) . "\n";
        }
        echo "----------------------------------------\n";
    }
} else {
    echo "No pending appointments found for " . $target_date . "\n";
}

$stmt->close();
echo "Test completed.\n";
?>