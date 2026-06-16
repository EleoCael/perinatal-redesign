<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $maternal_supplement_id = $_POST['maternal_supplement_id'] ?? null;
    $pregnancy_id = $_POST['pregnancy_id'] ?? null;
    $supp_trimester = $_POST['supp_trimester'] ?? null;
    $supp_tablets_given = $_POST['supp_tablets_given'] ?? null;
    $date_supp = $_POST['date_supp'] ?? null;
    
    if (!$maternal_supplement_id || !$pregnancy_id || !$supp_trimester || !$supp_tablets_given || !$date_supp) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    $update_query = "UPDATE maternal_supplements SET supp_trimester = ?, supp_tablets_given = ?, date_supp = ? WHERE maternal_supplement_id = ? AND pregnancy_id = ? AND supplement_type = 'Iron Sulfate w/Folic Acid'";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt === false) {
        error_log("Update Prepare Failed: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Database error"]);
        exit;
    }
    
    $stmt->bind_param("sissi", $supp_trimester, $supp_tablets_given, $date_supp, $maternal_supplement_id, $pregnancy_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Iron supplement updated successfully"]);
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