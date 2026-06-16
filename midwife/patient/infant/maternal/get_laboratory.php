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

$laboratory_query = "SELECT gestational_diabetes_date, gestational_diabetes_screening, diabetes_remarks FROM maternal_disease_screening WHERE pregnancy_id  = ?";
$stmt_laboratory_query = $conn->prepare($laboratory_query);
$stmt_laboratory_query->bind_param("i", $pregnancy_id);
$stmt_laboratory_query->execute();
$laboratory_result = $stmt_laboratory_query->get_result();
$laboratory_info = $laboratory_result->fetch_assoc() ?? [];

echo json_encode([
    'gestational_date' => insertValues($laboratory_info, 'gestational_diabetes_date'),
    'gestational_screening' => insertValues($laboratory_info, 'gestational_diabetes_screening'),
    'gestational_remarks' => insertValues($laboratory_info, 'diabetes_remarks')
]);