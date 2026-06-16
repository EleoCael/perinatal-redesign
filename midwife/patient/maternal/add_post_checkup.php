<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $checkup_visit = $_POST['checkup_visit'] ?? '';
    $post_checkup_date = $_POST['post_checkup_date'] ?? '';

    if (!$pregnancy_id || !$checkup_visit || !$post_checkup_date) {
        echo "Missing fields";
        exit;
    }

    $insert_query = "INSERT INTO post_partum_checkup (pregnancy_id, checkup_visit, post_checkup_date)
                     VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iss", $pregnancy_id, $checkup_visit, $post_checkup_date);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
