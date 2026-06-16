<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $trimester = $_POST['trimester'] ?? '';
    $checkup_date = $_POST['checkup_date'] ?? '';

    
    if (!$pregnancy_id || !$trimester || !$checkup_date) {
        echo "Missing fields";
        exit;
    }

    $insert_query = "INSERT INTO prenatal_checkup (pregnancy_id, trimester, checkup_date)
                     VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iss", $pregnancy_id, $trimester, $checkup_date);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>
