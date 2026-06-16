<?php
$email = $_POST['email'];
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

$conn = require __DIR__ . "../../../module/db.config.php";

$sql = "UPDATE user
        SET password_reset_token = ?, token_expiration = ?
        WHERE user_email = ?";

$stmt_reset_pass = $conn->prepare($sql);
$stmt_reset_pass->bind_param("sss", $token_hash, $expiry, $email);
$stmt_reset_pass->execute();

if ($conn->affected_rows) {
    $mail = require __DIR__ . "/mailer.php";
    $mail->setFrom("noreply@rhusystem.com", "RHU System");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset Link";

   define('BASE_URL', 'http://192.168.254.109');
    $password_reset_link = BASE_URL . "/rhusystem/system/forgot-password/reset_password_trial.php?token=" . $token;

    $mail->Body = <<<END
            <html>
                <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h2 style="color: #4a90e2;">Welcome to RHU System!</h2>
                        <p>Hello <strong>{$firstName} {$lastName}</strong>,</p>
                        <p>We received a request to reset your password for your account.</p>
                        <p>Click the button below to reset your password:</p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="{$password_reset_link}" style="background-color: #4a90e2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a>
                        </div>
                        <p>Or copy and paste this link in your browser:</p>
                        <p style="word-break: break-all; color: #4a90e2;">{$password_reset_link}</p>
                        <p><strong>This link will expire in 30 minutes.</strong></p>
                        <p>If you did not expect this email, please ignore it.</p>
                        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
                        <p style="font-size: 12px; color: #777;">RHU System - Perinatal Care Management</p>
                    </div>
                </body>
            </html>
END;

    try {
        $mail->send();

        session_start();
        $_SESSION['flash_msg'] = "Message sent, please check your inbox.";
        $_SESSION['flash_type'] = "success";
    } catch (Exception $e) {
        session_start();
        $_SESSION['flash_msg'] = "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
        $_SESSION['flash_type'] = "error";
    }
} else {
    session_start();
    $_SESSION['flash_msg'] = "Email address not found.";
    $_SESSION['flash_type'] = "error";
}

header("Location: forgot_password_trial.php");
exit;
