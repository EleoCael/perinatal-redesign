<?php
header('Content-Type: application/json');
ob_start();

if (!file_exists('../../module/db.config.php')) {
    die(json_encode(['success' => false, 'error' => 'Database config not found']));
}

require_once '../../module/db.config.php';
require_once 'report_functions.php';

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

session_start();
$health_center_id = $_SESSION['health_center_id'] ?? 1;

$action = $_GET['action'] ?? '';
$period = $_GET['period'] ?? 'monthly';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$quarter = $_GET['quarter'] ?? 1;

try {
    if ($action === 'prenatal') {
        $data = getPrenatalReport($conn, $health_center_id, $period, $month, $year, $quarter);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($action === 'child') {
        $data = getChildReport($conn, $health_center_id, $period, $month, $year, $quarter);
        echo json_encode(['success' => true, 'data' => $data]);
    } elseif ($action === 'nutrition') {
        $data = getNutritionReport($conn, $health_center_id, $period, $month, $year, $quarter);
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}