<?php
require_once "../../../module/db.config.php";

$patient_id = $_POST['patient_id'] ?? null;

if (!$patient_id) {
    echo json_encode(['error' => 'Missing Patient ID']);
    exit;
}

function insertValues($array, $key, $default = 'N/A') {
    if (!isset($array[$key])) return $default;
    $value = $array[$key];
    if ($value === '' || $value === '0000-00-00' || $value === null) {
        return $default;
    }
    return $value;
}

$query = "SELECT birth_weight, birth_height, sex FROM infant WHERE patient_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();
$infant_assessment = $result->fetch_assoc() ?? [];

echo json_encode([
    'birth_weight' => insertValues($infant_assessment, 'birth_weight'),
    'birth_height' => insertValues($infant_assessment, 'birth_height'),
    'sex' => insertValues($infant_assessment, 'sex')
]);
