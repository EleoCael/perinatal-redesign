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

$birth_query = "SELECT delivery_type, birth_weight_classification, birth_weight, birth_attendant FROM delivery WHERE pregnancy_id  = ?";
$stmt_birth_query = $conn->prepare($birth_query);
$stmt_birth_query->bind_param("i", $pregnancy_id);
$stmt_birth_query->execute();
$birth_result = $stmt_birth_query->get_result();
$birth_info = $birth_result->fetch_assoc() ?? [];

echo json_encode([
    'delivery_type' => insertValues($birth_info, 'delivery_type'),
    'weight_class' => insertValues($birth_info, 'birth_weight_classification'),
    'birth_weight' => insertValues($birth_info, 'birth_weight'),
    'birth_attendant' => insertValues($birth_info, 'birth_attendant')
]);