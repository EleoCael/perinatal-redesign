<?php
require_once "../../../module/db.config.php";

$patient_id = $_POST['patient_id'];
function insertValues($array, $key, $default = 'N/A')
{
    if (!isset($array[$key])) {
        return $default;
    }
    $value = $array[$key];
    if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00' || $value === null) {
        return $default;
    } else {
        return $value;
    }
}

$query = "SELECT hepaB_day, hepaB_date FROM hepab
          WHERE patient_id = ? ORDER BY hepaB_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();
$hepaB_row = $result->fetch_assoc() ?? [];

    echo json_encode([
    'hepaB_day' => insertValues($hepaB_row, 'hepaB_day'),
    'hepaB_date' => insertValues($hepaB_row, 'hepaB_date')
   
]);


