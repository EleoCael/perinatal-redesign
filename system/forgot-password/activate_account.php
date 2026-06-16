<?php
session_start();
require "../../module/db.config.php";

if (!isset($_GET['token']) || empty($_GET['token'])) {
    $_SESSION['errorMessage'] = "Invalid activation link.";
    header("Location: ../login/login.php");
    exit;
}

$token = $_GET['token'];
$token_hash = hash("sha256", $token);

$sql = "SELECT user_id, first_name, last_name, user_email, is_verified, activation_expires_at 
        FROM user 
        WHERE account_activation_hash = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['errorMessage'] = "Database error occurred.";
    header("Location: ../login/login.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $token_hash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    
 
    if ($row['is_verified'] == 1) {
        $_SESSION['errorMessage'] = "Account already activated. Please login.";
        header("Location: ../login/login.php");
        exit;
    }
   
    if (strtotime($row['activation_expires_at']) <= time()) {
        $_SESSION['errorMessage'] = "Activation link has expired. Please contact the administrator.";
        header("Location: ../login/login.php");
        exit;
    }
    

    $reset_token = bin2hex(random_bytes(16));
    $reset_token_hash = hash("sha256", $reset_token);
    $reset_expiry = date("Y-m-d H:i:s", time() + 60 * 30);
    

    $update_sql = "UPDATE user 
                   SET is_verified = 1, 
                       account_activation_hash = NULL,
                       activation_expires_at = NULL,
                       password_reset_token = ?,
                       token_expiration = ?
                   WHERE user_id = ?";
    
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ssi", $reset_token_hash, $reset_expiry, $row['user_id']);
    
    if (mysqli_stmt_execute($update_stmt)) {
        mysqli_stmt_close($update_stmt);
        
       
        header("Location: reset_password_trial.php?token=" . $reset_token);
        exit;
    } else {
        $_SESSION['errorMessage'] = "Error activating account. Please try again.";
        header("Location: ../login/login.php");
        exit;
    }
    
} else {
    $_SESSION['errorMessage'] = "Invalid or expired activation link.";
    header("Location: ../login/login.php");
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>