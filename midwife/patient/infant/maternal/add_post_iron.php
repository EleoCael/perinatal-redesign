<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $iron_folic_month_given  = $_POST['iron_folic_month_given'] ?? '';
    $iron_folic_date_given = $_POST['iron_folic_date_given'] ?? '';
    $tablets_given = $_POST['tablets_given'];


    if (!$pregnancy_id) {
        echo "Missing fields";
        exit;
    }

    $insert_query = "INSERT INTO post_partum_supp (pregnancy_id, iron_folic_month_given, iron_folic_date_given, tablets_given)
                     VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("issi", $pregnancy_id, $iron_folic_month_given, $iron_folic_date_given, $tablets_given);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
