<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $iodine_status = isset($_POST['iodine_capsule_given']) ? 1 : 0;
    $date_iodine = $_POST['date_iodine'] ?? '';
    

    if (!$pregnancy_id) {
        echo "Missing Pregnancy ID";
        exit;
    }

    $check_iodine = "SELECT iodine_id FROM iodine_supplement WHERE pregnancy_id = ?";
    $stmt_check_iodine = $conn->prepare($check_iodine);
    if ($stmt_check_iodine === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }
    $stmt_check_iodine->bind_param("i", $pregnancy_id);
    $stmt_check_iodine->execute();
    $result_check = $stmt_check_iodine->get_result();
    $exist_iodine = $result_check->fetch_assoc();
    $stmt_check_iodine->close();

    $stmt = null;

    if ($exist_iodine) {
       $iodine_id = $exist_iodine['iodine_id'];
       $query = " UPDATE iodine_supplement SET date_iodine = ?, iodine_capsule_given = ? WHERE iodine_id = ?";
       $stmt = $conn->prepare($query);
       $stmt->bind_param("sii", $date_iodine, $iodine_status, $iodine_id);
    }else {
        $insert_iodine_query = "INSERT INTO iodine_supplement (pregnancy_id,
        iodine_capsule_given, date_iodine) VALUES (?,?,?)"; 
        $stmt = $conn->prepare($insert_iodine_query);
        $stmt->bind_param("iis",  $pregnancy_id, $iodine_status, $date_iodine);
    }

    if ($stmt === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }
    
    if ($stmt->execute()) {
        echo "success";
    }else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
    
}