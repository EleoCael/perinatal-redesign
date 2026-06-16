<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $supplement_type = $_POST['supplement_type'] ?? '';
    $trimester = $_POST['supp_trimester'] ?? '';
    $date_supp = $_POST['date_supp'] ?? '';
    $tablets_given = $_POST['supp_tablets_given'] ?? '';

    if (!$pregnancy_id || !$supplement_type || !$supplement_type || !$date_supp || !$tablets_given ) {
        echo "Missing Fields";
        exit;
    }

    $insert_calcium_query = "INSERT INTO maternal_supplements (pregnancy_id,
        supplement_type, supp_trimester, date_supp, supp_tablets_given) VALUES 
        (?,?,?,?,?)"; 
    $stmt_calcium = $conn->prepare($insert_calcium_query);
    $stmt_calcium->bind_param("isssi",  $pregnancy_id, $supplement_type,
        $trimester,  $date_supp, $tablets_given);

    if ($stmt_calcium->execute()) {
        echo "success";
    }else {
        echo "error";
    }

    $stmt_calcium->close();
    $conn->close();
    
}