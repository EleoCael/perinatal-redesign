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
function displayCheckbox($array, $key, $default = 'N/A')
{
    if (isset($array[$key]) && $array[$key] !== null) {
        return $array[$key] == 1 ? 'Yes' : 'No';
    } else {
        return $default;
    }
}

$place_query = "SELECT health_facility_type, health_facility_name, bemonc_cemonc_capable, ownership FROM delivery WHERE pregnancy_id  = ?";
$stmt_place_query = $conn->prepare($place_query);
$stmt_place_query->bind_param("i", $pregnancy_id);
$stmt_place_query->execute();
$place_result = $stmt_place_query->get_result();
$place_info = $place_result->fetch_assoc() ?? [];

echo json_encode([
    'facility_type' => insertValues($place_info, 'health_facility_type'),
    'facility_name' => insertValues($place_info, 'health_facility_name'),
    'bemonc_cemonc_capable' => displayCheckbox($place_info, 'bemonc_cemonc_capable'),
    'ownership' => insertValues($place_info, 'ownership')
]);