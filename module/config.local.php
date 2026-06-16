<?php

$fname = $_POST['firstName'];
$lname = $_POST['lastName'];
$email = $_POST['email'];

require "vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->SMTPAuth = true;

$mail->Host = "RHU.example.com";
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->Username = "rhu@example.com";
$mail->Password = "password";
$mail->setFrom($email, $fname);
$mail->addAddress("");
