<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $fim_status_value = isset($_POST['fim_status']) ? 1 : 0;
   
    if (!$pregnancy_id) {
        echo "Missing Pregnancy ID";
        exit;
    }

    $check_fim = "SELECT fim_id FROM fim_status_maternal WHERE pregnancy_id = ?";
    $stmt_check_fim = $conn->prepare($check_fim);

    if ($stmt_check_fim === false) {
         error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }
    $stmt_check_fim->bind_param("i", $pregnancy_id);
    $stmt_check_fim->execute();
    $result_check = $stmt_check_fim->get_result();
    $exist_fim = $result_check->fetch_assoc();
    $stmt_check_fim->close();

    $stmt = null;

    if ($exist_fim) {
        $fim_id = $exist_fim['fim_id'];
        $query = "UPDATE fim_status_maternal SET fim_status = ? WHERE fim_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $fim_status_value, $fim_id);
    } else {
        $insert_fim = "INSERT INTO fim_status_maternal (pregnancy_id,
            fim_status) VALUES (?,?)"; 
        $stmt = $conn->prepare($insert_fim);
        $stmt->bind_param("ii",  $pregnancy_id, $fim_status_value);
    }

    if ($stmt === false) {
        error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }

    
    if ($stmt->execute()) {
        echo "success";
    }else {
        error_log("Execute failed: " . $stmt->error);
        echo "error";
    }
    $stmt->close();
    $conn->close();
    
}