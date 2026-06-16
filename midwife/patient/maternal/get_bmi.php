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

$bmi_query = "SELECT bmi_class, bmi, deworming_status, deworming_date_given FROM prenatal_checkup WHERE pregnancy_id  = ?";
$stmt_bmi = $conn->prepare($bmi_query);
$stmt_bmi->bind_param("i", $pregnancy_id);
$stmt_bmi->execute();
$bmi_result = $stmt_bmi->get_result();
$bmi_info = $bmi_result->fetch_assoc() ?? [];

echo json_encode([
    'bmi_class' => insertValues($bmi_info, 'bmi_class'),
    'bmi' => insertValues($bmi_info, 'bmi'),
    'deworming_stat' => displayCheckbox($bmi_info, 'deworming_status'),
    'deworming_date' => insertValues($bmi_info, 'deworming_date_given')
]);