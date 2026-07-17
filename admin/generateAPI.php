<?php
header('Content-Type: application/json');
ob_start();
//generateAPI for admin
// Correct path: from /admin/ go up one level to root, then into /module/
$configFile = '../module/db.config.php';

if (!file_exists($configFile)) {
    die(json_encode([
        'success' => false,
        'error' => 'Database config not found at: ' . realpath($configFile) . '. Looking in: ' . $configFile
    ]));
}

require_once $configFile;
require_once 'report_functions_admin.php';

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . mysqli_connect_error()]));
}

session_start();

$action = $_GET['action'] ?? '';
$period = $_GET['period'] ?? 'monthly';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$quarter = $_GET['quarter'] ?? 1;
$barangays = isset($_GET['barangays']) ? explode(',', $_GET['barangays']) : [];

try {
    if ($action === 'getBarangays') {
        getBarangays($conn);
    } elseif ($action === 'maternal') {
        $result = getPrenatalReport($conn, $barangays, $period, $month, $year, $quarter);
        echo json_encode(['success' => true, 'data' => $result['data'], 'title' => $result['title']]);
    } elseif ($action === 'child') {
        $result = getChildReport($conn, $barangays, $period, $month, $year, $quarter);
        echo json_encode(['success' => true, 'data' => $result['data'], 'title' => $result['title']]);
    } elseif ($action === 'nutrition') {
        $result = getNutritionReport($conn, $barangays, $period, $month, $year, $quarter);
        echo json_encode(['success' => true, 'data' => $result['data'], 'title' => $result['title']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
ob_end_flush();