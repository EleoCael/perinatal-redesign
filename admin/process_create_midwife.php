<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function sendJsonResponse($data) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function generateSecurePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?';
    $password = '';
    $charsLength = strlen($chars) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $charsLength)];
    }
    
    return $password;
}
function isLikelyTypoDomain($domain) {
    $veryCommonDomains = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 
        'icloud.com', 'aol.com'
    ];
    
    $definiteTypos = [
        'gmial.com' => 'gmail.com',
        'gmal.com' => 'gmail.com',
        'gmai.com' => 'gmail.com',
        'gnail.com' => 'gmail.com',
        'gnai.com' => 'gmail.com',
        'mailg.com' => 'gmail.com',
        'gma.com' => 'gmail.com',

        
        'yaho.com' => 'yahoo.com',
        'yhoo.com' => 'yahoo.com',
        'yaoo.com' => 'yahoo.com',
        
        'hotmal.com' => 'hotmail.com',
        'hotmai.com' => 'hotmail.com',
        
        'outloo.com' => 'outlook.com',
        'outlok.com' => 'outlook.com',
    ];
    
    if (array_key_exists($domain, $definiteTypos)) {
        return $definiteTypos[$domain];
    }

    foreach ($veryCommonDomains as $commonDomain) {
        $distance = levenshtein($domain, $commonDomain);
        if ($distance === 1) {
            return $commonDomain;
        }
    }
    
    return false; 
}

require "../module/db.config.php";
if (!$conn) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
}

mysqli_set_charset($conn, "utf8mb4");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    sendJsonResponse([
        'success' => false,
        'message' => 'Unauthorized access. Please login as Admin.'
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

if (!isset($_POST['ajax_submit'])) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}

$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$health_center_id = intval($_POST['health_center_id'] ?? 0);

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
} else {
    $domain = strtolower(substr(strrchr($email, "@"), 1));
    
    $blockedDomains = [
        'tempmail.com', '10minutemail.com', 'guerrillamail.com', 'mailinator.com',
        'yopmail.com', 'throwawaymail.com', 'fakeinbox.com', 'temp-mail.org',
        'trashmail.com', 'disposableemail.com', 'getairmail.com', 'tmpmail.org',
        'sharklasers.com', 'grr.la', 'guerrillamail.biz', 'maildrop.cc',
        'tempail.com', 'fake-mail.com', 'mailnesia.com'
    ];

    $suggestedDomain = isLikelyTypoDomain($domain);
    if ($suggestedDomain !== false) {
        $suggestedEmail = str_replace("@" . $domain, "@" . $suggestedDomain, $email);
        $errors[] = "The email domain '{$domain}' appears to have a typo. Did you mean '{$suggestedDomain}'?";
    }

    elseif (in_array($domain, $blockedDomains)) {
        $errors[] = "Disposable/temporary email addresses are not allowed. Please use a permanent email address.";
    }
  
    elseif (!checkdnsrr($domain, 'MX')) {
        $errors[] = "Please use a valid email address from a recognized email provider. The domain '{$domain}' does not exist or cannot receive emails.";
    }
    
}

if ($health_center_id <= 0) {
    $errors[] = "Please select a valid barangay";
}

if (!empty($email) && empty($errors)) {
    $check_email_sql = "SELECT user_id FROM user WHERE user_email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $check_email_sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Email address already exists in the system";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors[] = "Database error checking email";
    }
}

if ($health_center_id > 0 && empty($errors)) {
    $check_midwife_sql = "SELECT user_id FROM user WHERE health_center_id = ? AND role = 'Midwife' LIMIT 1";
    $stmt = mysqli_prepare($conn, $check_midwife_sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $health_center_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $hc_name = '';
            $name_sql = "SELECT barangay_name FROM health_center WHERE health_center_id = ? LIMIT 1";
            $name_stmt = mysqli_prepare($conn, $name_sql);
            
            if ($name_stmt) {
                mysqli_stmt_bind_param($name_stmt, "i", $health_center_id);
                mysqli_stmt_execute($name_stmt);
                mysqli_stmt_bind_result($name_stmt, $hc_name);
                mysqli_stmt_fetch($name_stmt);
                mysqli_stmt_close($name_stmt);
            }

            $hc_display = $hc_name ? htmlspecialchars($hc_name) : 'selected';
            $errors[] = "Barangay {$hc_display} already has an assigned midwife";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors[] = "Database error checking midwife assignment";
    }
}

if (!empty($errors)) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Validation errors: ' . implode('; ', $errors)
    ]);
}

$generatedPassword = generateSecurePassword(12);
$hashed_password = password_hash($generatedPassword, PASSWORD_DEFAULT);

$activation_token = bin2hex(random_bytes(32));
$token_hash = hash("sha256", $activation_token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 60 * 24); 

$insert_sql = "INSERT INTO user (first_name, last_name, user_email, password_hash, role, health_center_id, is_verified, account_activation_hash, activation_expires_at) 
               VALUES (?, ?, ?, ?, 'Midwife', ?, 0, ?, ?)";

$stmt = mysqli_prepare($conn, $insert_sql);

if (!$stmt) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Database error preparing statement'
    ]);
}

mysqli_stmt_bind_param($stmt, "ssssiss", 
    $firstName, 
    $lastName, 
    $email, 
    $hashed_password, 
    $health_center_id,
    $token_hash,
    $expiry
);

if (mysqli_stmt_execute($stmt)) {
    $new_user_id = mysqli_insert_id($conn);
 
    try {
        $mail = require __DIR__ . "../../system/forgot-password/mailer.php";
        $mail->setFrom("noreply@rhusystem.com", "RHU System");
        $mail->addAddress($email);
        $mail->Subject = "Activate Your Midwife Account";
        
        $activation_link = "http://localhost/rhusystem/system/forgot-password/activate_account.php?token=" . $activation_token;
        
        $mail->Body = <<<END
            <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h2 style="color: #4a90e2;">Welcome to RHU System!</h2>
                        <p>Hello <strong>{$firstName} {$lastName}</strong>,</p>
                        <p>Your midwife account has been created by the administrator.</p>
                        <p>To activate your account and set your own password, please click the button below:</p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="{$activation_link}" style="background-color: #4a90e2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Activate Account</a>
                        </div>
                        <p>Or copy and paste this link in your browser:</p>
                        <p style="word-break: break-all; color: #4a90e2;">{$activation_link}</p>
                        <p><strong>This link will expire in 24 hours.</strong></p>
                        <p>After activation, you will be prompted to set your own secure password.</p>
                        <p>If you did not expect this email, please ignore it.</p>
                        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
                        <p style="font-size: 12px; color: #777;">RHU System - Perinatal Care Management</p>
                    </div>
                </body>
            </html>
        END;

        $mail->send();

        sendJsonResponse([
            'success' => true,
            'message' => "Midwife account created successfully! An activation email has been sent to {$email}",
            'user_id' => $new_user_id
        ]);
        
    } catch (Exception $e) {
        sendJsonResponse([
            'success' => true,
            'message' => "Account created but activation email could not be sent. Error: {$mail->ErrorInfo}",
            'user_id' => $new_user_id,
            'email_error' => true
        ]);
    }
} else {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error creating account. Please try again.'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>