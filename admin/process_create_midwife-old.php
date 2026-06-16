<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
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
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

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
if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters";
}
if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match";
}

// check kung may midwife na
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

// Check kung may midwifie na sa barnagay
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

$hashed_password = password_hash($password, PASSWORD_DEFAULT);


$insert_sql = "INSERT INTO user (first_name, last_name, user_email, password_hash, role, health_center_id, is_verified) 
               VALUES (?, ?, ?, ?, 'Midwife', ?, 1)";

$stmt = mysqli_prepare($conn, $insert_sql);

if (!$stmt) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Database error preparing statement'
    ]);
}


mysqli_stmt_bind_param($stmt, "ssssi", 
    $firstName, 
    $lastName, 
    $email, 
    $hashed_password, 
    $health_center_id
);

if (mysqli_stmt_execute($stmt)) {
    $new_user_id = mysqli_insert_id($conn);
    
    sendJsonResponse([
        'success' => true,
        'message' => "Midwife account created successfully! Email: {$email}",
        'user_id' => $new_user_id
    ]);
} else {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error creating account. Please try again.'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>