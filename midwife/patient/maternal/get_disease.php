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

$disease_query = "SELECT syphilis_date, syphilis_screening, syphilis_screening_remarks,
hepatitis_b_screening, hepatitisB_date, hepatitis_b_screening_remarks FROM maternal_disease_screening WHERE pregnancy_id  = ?";
$stmt_disease_query = $conn->prepare($disease_query);
$stmt_disease_query->bind_param("i", $pregnancy_id);
$stmt_disease_query->execute();
$disease_result = $stmt_disease_query->get_result();
$disease_info = $disease_result->fetch_assoc() ?? [];

echo json_encode([
    'syphilis_date' => insertValues($disease_info, 'syphilis_date'),
    'syphilis_screening' => insertValues($disease_info, 'syphilis_screening'),
    'syphilis_remarks' => insertValues($disease_info, 'syphilis_screening_remarks'),
    'hepatitisB_date' => insertValues($disease_info, 'hepatitisB_date'),
    'hepatitis_b_screening' => insertValues($disease_info, 'hepatitis_b_screening'),
    'hepatitis_b_remarks' => insertValues($disease_info, 'hepatitis_b_screening_remarks'),
]);