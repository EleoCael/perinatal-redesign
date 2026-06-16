<?php
require_once "../../../module/db.config.php";

$pregnancy_id = $_POST['pregnancy_id'];

if (!$pregnancy_id) {
    echo json_encode(['error' => 'Missing Pregnancy ID']);
    exit;
}

function insertValues($array, $key, $default = 'N/A')
{
    if (!isset($array[$key])) return $default;
    $value = $array[$key];
    if ($value === '' || $value === '0000-00-00' || $value === null) {
        return $default;
    }
    return $value;
}

$query = "SELECT outcome, date_terminated, sex FROM pregnancy WHERE pregnancy_id  = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $pregnancy_id);
$stmt_query->execute();
$result = $stmt_query->get_result();
$preg_outcome = $result->fetch_assoc() ?? [];

echo json_encode([
    'date_terminated' => insertValues($preg_outcome, 'date_terminated'),
    'outcome' => insertValues($preg_outcome, 'outcome'),
    'sex' => insertValues($preg_outcome, 'sex')
]);