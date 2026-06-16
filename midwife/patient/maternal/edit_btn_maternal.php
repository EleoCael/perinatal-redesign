<?php
require_once "../../../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $patient_id = $_POST['patient_id'] ?? null;
    $first_name = $_POST['first_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $date_registration = $_POST['date_of_registration'] ?? '';
    $family_serial_no = $_POST['family_serial_number'] ?? '';
    $socio_economic_status = $_POST['socio_economic_status'] ?? '';
    $address = $_POST['address'] ?? '';
    $age_bracket = $_POST['age_bracket'] ?? '';
    $age = $_POST['age'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
   
    if (!$patient_id) {
         echo json_encode(["success" => false, "message" => "Missing Patient ID"]);
        exit;
    }

    $query = " UPDATE patient SET first_name = ?, middle_name = ?, last_name = ?, 
    date_of_registration = ?, family_serial_number = ?, socio_economic_status = ?, address = ?, 
    age_bracket = ?, age = ? , email = ?, contact_number = ?  WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        error_log("Prepare Failed: " . $conn->error);
        echo "Error Preparing Statement";
        exit;
    }
 
    $stmt->bind_param("ssssssssissi", $first_name, $middle_name, $last_name,
    $date_registration, $family_serial_no, $socio_economic_status, $address, $age_bracket, $age, $email, $contact_number, $patient_id);

    if ($stmt->execute()) {
          echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
