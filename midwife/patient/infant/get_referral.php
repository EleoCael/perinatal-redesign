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

$referral_query = "SELECT newborn_screening_referral FROM infant WHERE patient_id  = ?";
$stmt_referral = $conn->prepare($referral_query);
$stmt_referral->bind_param("i", $patient_id);
$stmt_referral->execute();
$referral_result = $stmt_referral->get_result();
$referral_date = $referral_result->fetch_assoc() ?? [];

echo json_encode([
    'referral_date' => insertValues($referral_date, 'newborn_screening_referral'),
   
]);