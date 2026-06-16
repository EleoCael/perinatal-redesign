<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $is_still_breastfeed_value = isset($_POST['is_still_breastfeed']) ? 1 : 0;
   
    if (!$patient_id) {
        echo "Missing Patient ID";
        exit;
    }

    $check_breastfeed = "SELECT 6th_month_id FROM 6th_month_check WHERE patient_id = ?";
    $stmt_check_breastfeed= $conn->prepare($check_breastfeed);

    if ($stmt_check_breastfeed === false) {
         error_log("Prepare Failed: " .$conn->error);
        echo "Error Preparing Statement";
        exit;
    }
    $stmt_check_breastfeed->bind_param("i", $patient_id);
    $stmt_check_breastfeed->execute();
    $result_breastfeed = $stmt_check_breastfeed->get_result();
    $exist_breastfeed = $result_breastfeed->fetch_assoc();
    $stmt_check_breastfeed->close();

    $stmt = null;

    if ($exist_breastfeed) {
        $month_id = $exist_breastfeed['6th_month_id'];
        $query = "UPDATE 6th_month_check SET is_still_breastfeed = ? WHERE 6th_month_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $is_still_breastfeed_value, $month_id);
    } else {
        $insert_fim = "INSERT INTO 6th_month_check (patient_id,
            is_still_breastfeed) VALUES (?,?)"; 
        $stmt = $conn->prepare($insert_fim);
        $stmt->bind_param("ii",  $patient_id, $is_still_breastfeed_value);
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