<?php
require_once "../../../module/db.config.php";

if (isset($_POST['patient_id'])) {
    $patient_id = $_POST['patient_id'];

    $query = "SELECT * FROM patient WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(null);
    }
}
?>
