<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $post_delivery_date = $_POST['post_delivery_date'] ?? '';
    $post_delivery_time = $_POST['post_delivery_time'] ?? '';
    $breastfeeding_date = $_POST['breastfeeding_date'] ?? '';
    $breastfeeding_time = $_POST['breastfeeding_time'] ?? '';
   
    if (!$patient_id) {
        echo json_encode(["success" => false, "message" => "Missing Patient ID"]);
        exit;
    }

    $query = "UPDATE post_partum_checkup 
                  SET post_delivery_date = ?, 
                      post_delivery_time = ?, 
                      breastfeeding_date = ?, 
                      breastfeeding_time = ?
                  WHERE patient_id = ?";

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Error preparing statement"]);
        exit;
    }

     $stmt->bind_param("ssssi",
            $post_delivery_date,
            $post_delivery_time,
            $breastfeeding_date,
            $breastfeeding_time,
            $patient_id
        );

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
