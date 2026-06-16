<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $post_supp_id = $_POST['post_supp_id'] ?? null;
    $patient_id = $_POST['patient_id'] ?? null;
    $iron_folic_month_given = $_POST['iron_folic_month_given'] ?? null;
    $iron_folic_date_given = $_POST['iron_folic_date_given'] ?? null;
    $tablets_given = $_POST['tablets_given'] ?? null;
    
    if (!$post_supp_id || !$patient_id || !$iron_folic_month_given || !$iron_folic_date_given || !$tablets_given) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    $update_query = "UPDATE post_partum_supp 
                     SET iron_folic_month_given = ?, 
                         iron_folic_date_given = ?, 
                         tablets_given = ? 
                     WHERE post_supp_id = ? AND patient_id = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        error_log("Update Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("ssiii", $iron_folic_month_given, $iron_folic_date_given, $tablets_given, $post_supp_id, $patient_id);

    if ($stmt->execute()) {
        // Check if record exists first
        $check_query = "SELECT post_supp_id FROM post_partum_supp WHERE post_supp_id = ? AND patient_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $post_supp_id, $patient_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Record exists, update was successful (even if no actual changes)
            echo json_encode(["success" => true, "message" => "Postpartum iron supplement updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Supplement not found"]);
        }
        $check_stmt->close();
    } else {
        error_log("Update Execute Failed: " . $stmt->error);
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>