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

$post_query = "SELECT post_delivery_date, post_delivery_time, breastfeeding_date, breastfeeding_time FROM post_partum_checkup WHERE pregnancy_id  = ?";
$stmt_post_query = $conn->prepare($post_query);
$stmt_post_query->bind_param("i", $pregnancy_id);
$stmt_post_query->execute();
$post_result = $stmt_post_query->get_result();
$post_info = $post_result->fetch_assoc() ?? [];

echo json_encode([
    'post_delivery_date' => insertValues($post_info, 'post_delivery_date'),
    'post_delivery_time' => insertValues($post_info, 'post_delivery_time'),
    'breastfeeding_date' => insertValues($post_info, 'breastfeeding_date'),
    'breastfeeding_time' => insertValues($post_info, 'breastfeeding_time')
]);