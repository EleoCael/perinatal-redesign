<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $vitamin_status = isset($_POST['vitamin_a']) ? 1 : 0;
    $vitamin_a_date = $_POST['vitamin_a_date'] ?? '';
    

    if (!$pregnancy_id) {
        echo "Missing Pregnancy ID";
        exit;
    }

    $check_vitamin = "SELECT vitamin_a_id FROM post_vitamin WHERE pregnancy_id = ?";
    $stmt_check_vitamin = $conn->prepare($check_vitamin);
    if ($stmt_check_vitamin === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }
    $stmt_check_vitamin->bind_param("i", $pregnancy_id);
    $stmt_check_vitamin->execute();
    $result_check = $stmt_check_vitamin->get_result();
    $exist_vitamin = $result_check->fetch_assoc();
    $stmt_check_vitamin->close();

    $stmt = null;

    if ($exist_vitamin) {
       $vitamin_id = $exist_vitamin['vitamin_a_id'];
       $query = " UPDATE post_vitamin SET vitamin_a_date = ?, vitamin_a = ? WHERE vitamin_a_id = ?";
       $stmt = $conn->prepare($query);
       $stmt->bind_param("sii", $vitamin_a_date, $vitamin_status, $vitamin_id);
    }else {
        $insert_vitamin_query = "INSERT INTO post_vitamin (pregnancy_id,
        vitamin_a, vitamin_a_date) VALUES (?,?,?)"; 
        $stmt = $conn->prepare($insert_vitamin_query);
        $stmt->bind_param("iis",  $pregnancy_id, $vitamin_status, $vitamin_a_date);
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