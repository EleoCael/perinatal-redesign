<?php
session_start();
require_once "../module/db.config.php";
require_once "../module/sms.config.php";

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$result_message = '';
$result_class = '';

// When form is submitted
if (isset($_POST['test_sms'])) {
    $test_number = trim($_POST['test_number']);
    $test_message = trim($_POST['test_message']);
    
    if (empty($test_number) || empty($test_message)) {
        $result_message = "❌ Please fill in all fields!";
        $result_class = "alert-danger";
    } else {
        // Send the SMS
        $sms_result = sendSMS($test_number, $test_message);
        
        if ($sms_result['success']) {
            $result_message = "✅ SMS sent successfully!<br>";
            $result_message .= "Message ID: " . $sms_result['message_id'] . "<br>";
            $result_message .= "Phone: " . $sms_result['phone_number'];
            $result_class = "alert-success";
        } else {
            $result_message = "❌ SMS sending failed!<br>";
            $result_message .= "Error: " . ($sms_result['error'] ?? $sms_result['message']);
            $result_class = "alert-danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SMS - RHU System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📱 Test SMS Functionality</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php if (!empty($result_message)): ?>
                            <div class="alert <?php echo $result_class; ?>">
                                <?php echo $result_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Your Phone Number</label>
                                <input type="text" 
                                       name="test_number" 
                                       class="form-control" 
                                       placeholder="09123456789" 
                                       value="<?php echo $_POST['test_number'] ?? ''; ?>"
                                       required>
                                <small class="text-muted">Format: 09XXXXXXXXX</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Test Message</label>
                                <textarea name="test_message" 
                                          class="form-control" 
                                          rows="4" 
                                          required><?php echo $_POST['test_message'] ?? 'Hello! This is a test SMS from RHU System. If you receive this, the SMS integration is working perfectly!'; ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="test_sms" class="btn btn-primary btn-lg">
                                    📤 Send Test SMS
                                </button>
                                <a href="midwife_dashboard.php" class="btn btn-secondary">
                                    ← Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow mt-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">ℹ️ Accepted Phone Formats</h5>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li><code>09123456789</code> ✅ Recommended</li>
                            <li><code>639123456789</code> ✅ Will be converted to 09 format</li>
                            <li><code>+639123456789</code> ✅ Will be converted to 09 format</li>
                            <li><code>9123456789</code> ✅ Will be converted to 09 format</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>