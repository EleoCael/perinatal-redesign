<?php
require_once "../../../module/db.config.php";
session_start();

header('Content-Type: application/json');

function respond($status, $data = []) {
    echo json_encode(array_merge(['status' => $status], $data));
    exit();
}

if (!isset($_POST['submit_btn']) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    respond('error', ['message' => 'Invalid request']);
}

//infant details
$first_name = $_POST['infant_first_name'] ?? '';
$middle_name = $_POST['infant_middle_name'] ?? '';
$last_name = $_POST['infant_last_name'] ?? '';
$birth_date = $_POST['infant_birth_date'] ?? '';
$name_of_mother = $_POST['name_of_mother'] ?? '';
$mother_id = $_POST['mother_id'] ?? null; // para sa standalone na registration for infants

// check kung may record na yung infant
$sql_check = "SELECT patient_id FROM patient WHERE
    first_name = ? AND
    middle_name = ? AND
    last_name = ? AND
    birth_date = ? AND
    name_of_mother = ? AND
    patient_type = 'infant'
    ";

$stmt_check_infant = $conn->prepare($sql_check);
$stmt_check_infant->bind_param(
    "sssss",
    $first_name,
    $middle_name,
    $last_name,
    $birth_date,
    $name_of_mother
);
$stmt_check_infant->execute();
$result_check = $stmt_check_infant->get_result();

if ($result_check->num_rows > 0) {
    $stmt_check_infant->close();
    respond('error', ['message' => 'Infant Record already exists!']);
}
$stmt_check_infant->close();

$conn->begin_transaction();

try {

    // Auto create yung account ng infant lang gamit yung email nung nanay
    $registered_by_midwife_id = (int)$_SESSION['user_id'];
    $health_center_id = (int)$_SESSION['health_center_id'];

    $email_for_user = trim($_POST['email'] ?? '');
    $patient_user_id = NULL;

    if (!empty($email_for_user)) {

        $stmt_chk_user = $conn->prepare("SELECT user_id FROM user WHERE user_email = ? LIMIT 1");
        $stmt_chk_user->bind_param("s", $email_for_user);
        $stmt_chk_user->execute();
        $stmt_chk_user->bind_result($existing_uid);
        if ($stmt_chk_user->fetch()) {
            $stmt_chk_user->close();
            throw new Exception("Patient login already exists for: " . $email_for_user);
        }
        $stmt_chk_user->close();

        function _rand_hex($bytes) { return bin2hex(random_bytes($bytes)); }
        $activation_token = _rand_hex(16);
        $activation_token_hash = hash("sha256", $activation_token);
        $activation_expiry = (new DateTime('+48 hours'))->format('Y-m-d H:i:s');

        $temp_password_hash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

        $stmt_user = $conn->prepare("
            INSERT INTO user
            (first_name, last_name, user_email, password_hash, role,
            account_activation_hash, activation_expires_at, health_center_id,
            registered_by_user_id, is_verified)
            VALUES (?, ?, ?, ?, 'Patient', ?, ?, ?, ?, 0)
        ");
        $stmt_user->bind_param(
            "ssssssii",
            $first_name,
            $last_name,
            $email_for_user,
            $temp_password_hash,
            $activation_token_hash,
            $activation_expiry,
            $health_center_id,
            $registered_by_midwife_id
        );
        if (!$stmt_user->execute()) {
            throw new Exception("User insert failed: " . $stmt_user->error);
        }
        $patient_user_id = $stmt_user->insert_id;
        $stmt_user->close();

        require_once $_SERVER['DOCUMENT_ROOT'] . '/rhusystem/system/forgot-password/mailer.php';

        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
            . "://" . $_SERVER['HTTP_HOST'];
        $activation_link = $base_url . "/rhusystem/system/forgot-password/activate_account.php?token=" . urlencode($activation_token);

        try {
            $mail->setFrom("rhusystem@gmail.com", "RHU System");
            $mail->addAddress($email_for_user, $first_name . ' ' . $last_name);
            $mail->Subject = "Activate Your RHU Account";
            $mail->Body = "
                <html>
                    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                            <h2 style='color: #4a90e2;'>Welcome to RHU System</h2>
                                <p>Hello <strong>{$first_name} {$last_name}</strong></p>
                                <p>Your infant patient account has been created by your healthcare provider.</p>
                                <p>Please click the link below to activate your account and set your password:</p>
                                    <div  style='text-align: center; margin: 30px 0;'>
                                        <a href='{$activation_link}' style='background-color: #4a90e2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Activate Account</a>
                                    </div>
                                <p>Or copy and paste this link in your browser:</p>
                                <p style='word-break: break-all; color: #4a90e2;'>  {$activation_link}</p>
                                <p>This link will expire in 48 hours.</p>
                                <p>If you did not request this account, please ignore this email.</p>
                                <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
                                <p style='font-size: 12px; color: #777;'>RHU System - Perinatal Care Management</p>
                        </div>
                    </body>
                </html>
            ";

            if (!$mail->send()) {
                throw new Exception("Email send failed: " . $mail->ErrorInfo);
            }

            $mail->clearAddresses();

        } catch (Exception $e) {
            error_log("Failed to send activation email to {$email_for_user}: " . $e->getMessage());
            $_SESSION['email_warning'] = "Patient account created, but activation email failed to send.";
        }
    }
    // end -> auto create account

    // insert infant patient tab
    $sql_infant_patient = file_get_contents('../../../queries/infant_insert/insert_infant_patient.sql');
    if ($sql_infant_patient === false) {
        throw new Exception("Could not load SQL file. Please contact support.");
    }

    $stmt_infant_patient = $conn->prepare($sql_infant_patient);

    $patient_type = 'infant';
    $age_bracket = NULL;
    $age = NULL;
    $email = !empty($email_for_user) ? $email_for_user : NULL;

    $contact_number = preg_replace('/[^0-9]/', '', $_POST['contact_number']);
    if (strlen($contact_number) < 10 || strlen($contact_number) > 11) {
        throw new Exception("Invalid contact number length");
    }
    if (!is_numeric($contact_number)) {
        throw new Exception("Invalid contact number format.");
    }

    $stmt_infant_patient->bind_param(
        "iisisssssssssisssi",
        $patient_user_id,
        $registered_by_midwife_id,
        $patient_type,
        $mother_id,
        $_POST['date_of_registration'],
        $_POST['family_serial_number'],
        $first_name,
        $middle_name,
        $last_name,
        $name_of_mother,
        $_POST['address'],
        $age_bracket,
        $birth_date,
        $age,
        $_POST['socio_economic_status'],
        $_POST['contact_number'],
        $email,
        $health_center_id
    );

    if (!$stmt_infant_patient->execute()) {
        throw new Exception("Infant Patient Insert Failed: " . $stmt_infant_patient->error);
    }
    $patient_id = $conn->insert_id;
    $stmt_infant_patient->close();
    // end -> insert infant patient tab

    $conn->commit();

    respond('success', [
        'patient_id'   => $patient_id,
        'patient_type' => 'infant'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    respond('error', ['message' => $e->getMessage()]);
}