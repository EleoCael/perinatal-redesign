<?php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

ini_set('error_log', 'php_errors.log');

function sendJsonResponse($data, $conn = null) {
    header('Content-Type: application/json');
    ob_clean();
    echo json_encode($data);
    if ($conn) {
        mysqli_close($conn);
    }
    exit;
}

require "../module/db.config.php";
mysqli_set_charset($conn, "utf8mb4");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    error_log("Unauthorized access attempt");
    sendJsonResponse(['success' => false, 'message' => 'Unauthorized access. Please login as Admin.'], $conn);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method. Expected POST.'], $conn);
}

error_log("Received POST data: " . print_r($_POST, true));

$user_id = intval($_POST['user_id'] ?? 0);
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$health_center_id = intval($_POST['health_center_id'] ?? 0);

error_log("Processing edit for user_id: $user_id");
error_log("First Name: $firstName, Last Name: $lastName, Email: $email");
error_log("Health Center ID: $health_center_id");

$errors = [];

if (empty($firstName)) {
    $errors[] = "First name is required";
}
if (empty($lastName)) {
    $errors[] = "Last name is required";
}
if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}
if ($health_center_id <= 0) {
    $errors[] = "Please select a valid barangay";
}

if ($user_id <= 0) {
    $errors[] = "Invalid user ID";
} else {
    $check_user_sql = "SELECT user_id, first_name, last_name, user_email, health_center_id, is_verified 
                      FROM user 
                      WHERE user_id = ? AND role = 'Midwife'";
    $stmt = mysqli_prepare($conn, $check_user_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existing_user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$existing_user) {
            $errors[] = "Midwife not found";
        }
    } else {
        $errors[] = "Database error checking user: " . mysqli_error($conn);
    }
}

if (!empty($email) && empty($errors)) {
    $check_email_sql = "SELECT user_id FROM user WHERE user_email = ? AND user_id != ?";
    $stmt = mysqli_prepare($conn, $check_email_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Email address already exists in the system";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors[] = "Database error checking email: " . mysqli_error($conn);
    }
}

if ($health_center_id > 0 && empty($errors)) {
    $check_midwife_sql = "SELECT user_id, first_name, last_name 
                          FROM user 
                          WHERE health_center_id = ? 
                          AND role = 'Midwife' 
                          AND user_id != ?";
    $stmt = mysqli_prepare($conn, $check_midwife_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $health_center_id, $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $occupying_midwife = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($occupying_midwife) {
            $hc_name = '';
            $name_sql = "SELECT barangay_name FROM health_center WHERE health_center_id = ?";
            $name_stmt = mysqli_prepare($conn, $name_sql);
            if($name_stmt) {
                mysqli_stmt_bind_param($name_stmt, "i", $health_center_id);
                mysqli_stmt_execute($name_stmt);
                mysqli_stmt_bind_result($name_stmt, $hc_name);
                mysqli_stmt_fetch($name_stmt);
                mysqli_stmt_close($name_stmt);
            }

            $midwife_name = $occupying_midwife['first_name'] . ' ' . $occupying_midwife['last_name'];
            $errors[] = "Barangay " . ($hc_name ? htmlspecialchars($hc_name) : 'selected') . " is already assigned to midwife: " . $midwife_name;
        }
    } else {
        $errors[] = "Database error checking midwife assignment: " . mysqli_error($conn);
    }
}

if (!empty($errors)) {
    error_log("Validation errors: " . implode(', ', $errors));
    sendJsonResponse([
        'success' => false, 
        'message' => 'The following errors occurred:<br>- ' . implode('<br>- ', $errors)
    ], $conn);
}

$email_changed = false;
$original_email = '';

if (!empty($existing_user)) {
    $original_email = $existing_user['user_email'];
    $email_changed = ($email !== $original_email);
}

if ($email_changed) {
    $activation_token = bin2hex(random_bytes(32));
    $token_hash = hash("sha256", $activation_token);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 60 * 24); 

    $update_sql = "UPDATE user SET first_name = ?, last_name = ?, user_email = ?, health_center_id = ?, 
                          is_verified = 0, account_activation_hash = ?, activation_expires_at = ? 
                   WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssi", $firstName, $lastName, $email, $health_center_id, $token_hash, $expiry, $user_id);
    } else {
        error_log("Prepare failed for update with email change: " . mysqli_error($conn));
        sendJsonResponse([
            'success' => false, 
            'message' => 'Database prepare error: ' . mysqli_error($conn)
        ], $conn);
    }
} else {
    $update_sql = "UPDATE user SET first_name = ?, last_name = ?, user_email = ?, health_center_id = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssii", $firstName, $lastName, $email, $health_center_id, $user_id);
    } else {
        error_log("Prepare failed for update without email change: " . mysqli_error($conn));
        sendJsonResponse([
            'success' => false, 
            'message' => 'Database prepare error: ' . mysqli_error($conn)
        ], $conn);
    }
}

if (mysqli_stmt_execute($stmt)) {
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    error_log("Update executed successfully. Affected rows: $affected_rows");
 
    if ($email_changed) {
        try {
            $mail = require __DIR__ . "/../system/forgot-password/mailer.php";
            $mail->setFrom("noreply@rhusystem.com", "RHU System");
            $mail->addAddress($email);
            $mail->Subject = "Verify Your Updated Email Address";
            
            $activation_link = "http://localhost/rhusystem/system/forgot-password/activate_account.php?token=" . $activation_token;
            
            $mail->Body = <<<END
                <html>
                    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                        <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                            <h2 style="color: #4a90e2;">Email Address Updated</h2>
                            <p>Hello <strong>{$firstName} {$lastName}</strong>,</p>
                            <p>Your email address for the RHU System has been updated by the administrator.</p>
                            <p>To verify your new email address and regain access to your account, please click the button below:</p>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{$activation_link}" style="background-color: #4a90e2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Verify Email Address</a>
                            </div>
                            <p>Or copy and paste this link in your browser:</p>
                            <p style="word-break: break-all; color: #4a90e2;">{$activation_link}</p>
                            <p><strong>This link will expire in 24 hours.</strong></p>
                            <p>If you did not expect this email, please contact the administrator immediately.</p>
                            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
                            <p style="font-size: 12px; color: #777;">RHU System - Perinatal Care Management</p>
                        </div>
                    </body>
                </html>
            END;

            $mail->send();
            $email_message = " Verification email has been sent to the new email address.";
            error_log("Activation email sent successfully to: $email");
            
        } catch (Exception $e) {
            $email_message = " Account updated but verification email could not be sent. Error: {$mail->ErrorInfo}";
            error_log("Email sending failed: " . $mail->ErrorInfo);
        }
    } else {
        $email_message = "";
    }
  
    $response = [
        'success' => true, 
        'message' => "Midwife account updated successfully!" . ($email_changed ? $email_message : ""),
        'affected_rows' => $affected_rows,
        'email_changed' => $email_changed
    ];
    
    error_log("Sending success response");
    sendJsonResponse($response, $conn);

} else {
    $error_msg = mysqli_stmt_error($stmt);
    error_log("Update execution failed: " . $error_msg);
    sendJsonResponse([
        'success' => false, 
        'message' => 'Error updating account: ' . $error_msg
    ], $conn);
}

if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
ob_end_flush();
?>