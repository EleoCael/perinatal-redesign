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

$given_iron_query = "SELECT given_iron, given_iron_date, maternal_screening_remark FROM maternal_disease_screening WHERE pregnancy_id  = ?";
$stmt_given_iron_query = $conn->prepare($given_iron_query);
$stmt_given_iron_query->bind_param("i", $pregnancy_id);
$stmt_given_iron_query->execute();
$given_iron_result = $stmt_given_iron_query->get_result();
$given_iron_info = $given_iron_result->fetch_assoc() ?? [];

echo json_encode([
    'given_iron' => displayCheckbox($given_iron_info, 'given_iron'),
    'given_iron_date' => insertValues($given_iron_info, 'given_iron_date'),
    'maternal_screening_remark' => insertValues($given_iron_info, 'maternal_screening_remark')
]);