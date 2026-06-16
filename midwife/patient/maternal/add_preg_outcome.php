<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $outcome = $_POST['outcome'] ?? '';
    $date_terminated = $_POST['date_terminated'] ?? '';
    $sex = $_POST['sex'] ?? '';
 
    if (!$pregnancy_id) {
        echo "Missing Pregnancy ID";
        exit;
    }

    $query = " UPDATE pregnancy SET outcome = ?, date_terminated = ?, sex = ? WHERE pregnancy_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    $stmt->bind_param("sssi", $outcome, $date_terminated, $sex, $pregnancy_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
