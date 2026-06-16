<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $post_supp_id = $_POST['post_supp_id'] ?? null;
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $iron_folic_month_given = $_POST['iron_folic_month_given'] ?? null;
    $iron_folic_date_given = $_POST['iron_folic_date_given'] ?? null;
    $tablets_given = $_POST['tablets_given'] ?? null;
    
    if (!$post_supp_id || !$pregnancy_id || !$iron_folic_month_given || !$iron_folic_date_given || !$tablets_given) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    $update_query = "UPDATE post_partum_supp SET iron_folic_month_given = ?, iron_folic_date_given = ?, tablets_given = ? WHERE post_supp_id = ? AND pregnancy_id = ?";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        error_log("Update Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("ssiii", $iron_folic_month_given, $iron_folic_date_given, $tablets_given, $post_supp_id, $pregnancy_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Postpartum iron supplement updated successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "No changes made or supplement not found"]);
        }
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