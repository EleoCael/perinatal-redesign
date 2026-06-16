<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $patient_id = $_POST['patient_id'] ?? null;
    $iron_folic_month_given  = $_POST['iron_folic_month_given'] ?? '';
    $iron_folic_date_given = $_POST['iron_folic_date_given'] ?? '';
    $tablets_given = $_POST['tablets_given'];


    if (!$patient_id) {
        echo "Missing fields";
        exit;
    }

    $insert_query = "INSERT INTO post_partum_supp (patient_id, iron_folic_month_given, iron_folic_date_given, tablets_given)
                     VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("issi", $patient_id, $iron_folic_month_given, $iron_folic_date_given, $tablets_given);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
