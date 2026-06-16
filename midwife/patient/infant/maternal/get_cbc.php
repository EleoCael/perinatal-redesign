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

$cbc_query = "SELECT cbc_hgb_hct_date, anemia_status, cbc_hgb_hct_count, anemia_status_remarks FROM maternal_disease_screening WHERE pregnancy_id  = ?";
$stmt_cbc_query = $conn->prepare($cbc_query);
$stmt_cbc_query->bind_param("i", $pregnancy_id);
$stmt_cbc_query->execute();
$cbc_result = $stmt_cbc_query->get_result();
$cbc_info = $cbc_result->fetch_assoc() ?? [];

echo json_encode([
    'cbc_hgb_hct_date' => insertValues($cbc_info, 'cbc_hgb_hct_date'),
    'anemia_status' => insertValues($cbc_info, 'anemia_status'),
    'cbc_hgb_hct_count' => insertValues($cbc_info, 'cbc_hgb_hct_count'),
    'anemia_remarks' => insertValues($cbc_info, 'anemia_status_remarks')
]);