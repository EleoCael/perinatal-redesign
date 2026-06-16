<?php
//for dynamic supplement
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $supplement_type = $_POST['supplement_type'] ?? '';
    $trimester = $_POST['supp_trimester'] ?? '';
    $date = $_POST['date_supp'] ?? '';
    $tablets_given = $_POST['supp_tablets_given'] ?? '';

    if (!$pregnancy_id || !$supplement_type || !$trimester || !$date || !$tablets_given ) {
        echo "Missing Fields";
        exit;
    }

    $insert_iron_query = "INSERT INTO maternal_supplements (pregnancy_id,
        supplement_type, supp_trimester, date_supp, supp_tablets_given) VALUES 
        (?,?,?,?,?)"; 
    $stmt_iron = $conn->prepare($insert_iron_query);
    $stmt_iron->bind_param("isssi",  $pregnancy_id, $supplement_type,
        $trimester,  $date, $tablets_given);

    if ($stmt_iron->execute()) {
        echo "success";
    }else {
        echo "error";
    }

    $stmt_iron->close();
    $conn->close();
    
}