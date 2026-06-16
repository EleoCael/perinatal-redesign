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

$date_done_query = "SELECT newborn_screening_done FROM infant WHERE patient_id  = ?";
$stmt_date_done = $conn->prepare($date_done_query);
$stmt_date_done->bind_param("i", $patient_id);
$stmt_date_done->execute();
$date_done_result = $stmt_date_done->get_result();
$date_done = $date_done_result->fetch_assoc() ?? [];

echo json_encode([
    'date_done' => insertValues($date_done, 'newborn_screening_done'),
   
]);