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

$place_non_health_query = "SELECT non_health_facility_type, non_health_facility_name  FROM delivery WHERE pregnancy_id  = ?";
$stmt_place_non_health = $conn->prepare($place_non_health_query);
$stmt_place_non_health->bind_param("i", $pregnancy_id);
$stmt_place_non_health->execute();
$place_non_health_result = $stmt_place_non_health->get_result();
$place_non_health = $place_non_health_result->fetch_assoc() ?? [];

echo json_encode([
    'non_facility_type' => insertValues($place_non_health, 'non_health_facility_type'),
    'non_facility_name' => insertValues($place_non_health, 'non_health_facility_name')
]);