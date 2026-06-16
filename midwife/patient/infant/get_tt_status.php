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

$tt_query = "SELECT cpab_tt_status, cpab_tt_date FROM infant WHERE patient_id  = ?";
$stmt_tt_status = $conn->prepare($tt_query);
$stmt_tt_status->bind_param("i", $patient_id);
$stmt_tt_status->execute();
$tt_status_result = $stmt_tt_status->get_result();
$tt_status_date = $tt_status_result->fetch_assoc() ?? [];

echo json_encode([
    'cpab_tt_status' => insertValues($tt_status_date, 'cpab_tt_status'),
    'cpab_tt_date' => insertValues($tt_status_date, 'cpab_tt_date')
   
]);