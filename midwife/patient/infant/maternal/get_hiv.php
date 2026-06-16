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

$hiv_query = "SELECT hiv_screening, hiv_date, hiv_screening_remarks FROM maternal_disease_screening WHERE pregnancy_id  = ?";
$stmt_hiv_query = $conn->prepare($hiv_query);
$stmt_hiv_query->bind_param("i", $pregnancy_id);
$stmt_hiv_query->execute();
$hiv_result = $stmt_hiv_query->get_result();
$hiv_info = $hiv_result->fetch_assoc() ?? [];

echo json_encode([
    'hiv_date' => insertValues($hiv_info, 'hiv_date'),
    'hiv_screening' => insertValues($hiv_info, 'hiv_screening'),
    'hiv_remarks' => insertValues($hiv_info, 'hiv_screening_remarks')
]);