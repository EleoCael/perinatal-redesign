<?php
require_once "../../../module/db.config.php";

$patient_id = $_POST['patient_id'];

if (!$patient_id) {
    echo json_encode(['error' => 'Missing Patient ID']);
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

$assessed_query = "SELECT cpab_tt_date_assessed FROM infant WHERE patient_id  = ?";
$stmt_assessed = $conn->prepare($assessed_query);
$stmt_assessed->bind_param("i", $patient_id);
$stmt_assessed->execute();
$assessed_result = $stmt_assessed->get_result();
$assessed_date = $assessed_result->fetch_assoc() ?? [];

echo json_encode([
    'cpab_tt_date_assessed' => insertValues($assessed_date, 'cpab_tt_date_assessed')
  
]);