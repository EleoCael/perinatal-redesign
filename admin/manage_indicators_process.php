<?php
require_once "../module/db.config.php";
session_start();

header('Content-Type: application/json');

function respond($success, $message = '') {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method');
}

$report_type = $_POST['report_type'] ?? '';
$indicators = $_POST['indicator'] ?? [];

if (empty($report_type) || empty($indicators) || !is_array($indicators)) {
    respond(false, 'No indicator data received');
}

$conn->begin_transaction();

try {
    $sql = "UPDATE report_indicators
            SET label_template = ?, threshold_value = ?, display_order = ?, is_active = ?
            WHERE indicator_id = ? AND report_type = ?";
    $stmt = $conn->prepare($sql);

    foreach ($indicators as $indicator_id => $fields) {
        $indicator_id = intval($indicator_id);
        $label = trim($fields['label_template'] ?? '');
        $threshold = isset($fields['threshold_value']) && $fields['threshold_value'] !== ''
            ? intval($fields['threshold_value'])
            : null;
        $display_order = intval($fields['display_order'] ?? 0);
        $is_active = isset($fields['is_active']) ? 1 : 0;

        if ($label === '') {
            throw new Exception("Label cannot be empty (indicator_id $indicator_id)");
        }

        $stmt->bind_param(
            "siiiis",
            $label,
            $threshold,
            $display_order,
            $is_active,
            $indicator_id,
            $report_type
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to update indicator_id $indicator_id: " . $stmt->error);
        }
    }

    $stmt->close();
    $conn->commit();

    respond(true, 'Indicators updated successfully.');

} catch (Exception $e) {
    $conn->rollback();
    respond(false, $e->getMessage());
}