<?php
ob_start();
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function sendJsonResponse($data, $conn = null) {
    header('Content-Type: application/json');
    ob_clean();
    echo json_encode($data);
    if ($conn) {
        mysqli_close($conn);
    }
    exit;
}

$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "rhusystem";

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

if (!$conn) {
    sendJsonResponse(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
}

mysqli_set_charset($conn, "utf8mb4");

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    sendJsonResponse(['success' => false, 'message' => 'Unauthorized access. Please login as Admin.'], $conn);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(['success' => false, 'message' => 'Invalid request method.'], $conn);
}

$action = $_POST['action'] ?? '';
$user_id = intval($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    sendJsonResponse(['success' => false, 'message' => 'Invalid user ID.'], $conn);
}

$check_sql = "SELECT u.user_id, u.first_name, u.last_name, u.health_center_id, hc.barangay_name 
              FROM user u 
              LEFT JOIN health_center hc ON u.health_center_id = hc.health_center_id 
              WHERE u.user_id = ? AND u.role = 'Midwife' AND u.is_verified = 1";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$midwife = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$midwife) {
    sendJsonResponse(['success' => false, 'message' => 'Midwife not found or already deleted.'], $conn);
}

switch ($action) {
    case 'reassign_barangay':
        reassignBarangay($conn, $user_id, $midwife);
        break;
    
    case 'delete_midwife':
        deleteMidwife($conn, $user_id, $midwife);
        break;
    
    default:
        sendJsonResponse(['success' => false, 'message' => 'Invalid action.'], $conn);
}

function reassignBarangay($conn, $user_id, $midwife) {
    $new_barangay_id = intval($_POST['new_barangay_id'] ?? 0);
    
    if ($new_barangay_id <= 0) {
        sendJsonResponse(['success' => false, 'message' => 'Invalid barangay selection.'], $conn);
    }
    
    $check_barangay_sql = "SELECT hc.health_center_id, hc.barangay_name
                           FROM health_center hc 
                           LEFT JOIN user u ON hc.health_center_id = u.health_center_id AND u.role = 'Midwife' AND u.is_verified = 1
                           WHERE hc.health_center_id = ? AND (u.user_id IS NULL OR u.user_id = ?)";
    $stmt = mysqli_prepare($conn, $check_barangay_sql);
    mysqli_stmt_bind_param($stmt, "ii", $new_barangay_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $new_barangay = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$new_barangay) {
        sendJsonResponse(['success' => false, 'message' => 'Selected barangay not found or already occupied by another midwife.'], $conn);
    }
    
    $update_sql = "UPDATE user SET health_center_id = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "ii", $new_barangay_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        sendJsonResponse([
            'success' => true, 
            'message' => "Successfully reassigned {$midwife['first_name']} {$midwife['last_name']} to {$new_barangay['barangay_name']}."
        ], $conn);
    } else {
        sendJsonResponse([
            'success' => false, 
            'message' => 'Database error: ' . mysqli_stmt_error($stmt)
        ], $conn);
    }
    
    mysqli_stmt_close($stmt);
}

function deleteMidwife($conn, $user_id, $midwife) {
    $delete_sql = "UPDATE user SET is_verified = 0 WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        sendJsonResponse([
            'success' => true, 
            'message' => "Successfully deleted midwife {$midwife['first_name']} {$midwife['last_name']}."
        ], $conn);
    } else {
        sendJsonResponse([
            'success' => false, 
            'message' => 'Database error: ' . mysqli_stmt_error($stmt)
        ], $conn);
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
ob_end_flush();
?>