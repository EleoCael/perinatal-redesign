<?php

require "../../module/db.config.php";
session_start();

if (isset($_POST["login"])) {

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = htmlspecialchars($_POST["email"]);
        $password = htmlspecialchars($_POST["password"]);

        if (empty($email) || empty($password)) {
            $_SESSION['errorMessage1'] = "Email/Password is Empty! Please complete login credential!";
            header('location: login.php');
            exit;
        }

        $sql = "SELECT user_id, first_name, last_name, role, health_center_id,
                 registered_by_user_id, password_hash, is_verified
                 FROM `user`
                 WHERE user_email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = mysqli_stmt_get_result($stmt);

        
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Check if account is verified (for Midwife role)
            if ($row["role"] == "Midwife" && $row["is_verified"] == 0) {
                $_SESSION['errorMessage1'] = "Please activate your account first. Check your email for the activation link.";
                header('location: login.php');
                exit;
            }
            
            if (password_verify($password, $row["password_hash"])) {
                $_SESSION["user_id"] = $row["user_id"];
                $_SESSION["first_name"] = $row["first_name"];
                $_SESSION["last_name"] = $row["last_name"];
                $_SESSION["role"] = $row["role"];
                $_SESSION["health_center_id"] = $row["health_center_id"];
                $_SESSION["registered_by_user_id"] = $row["registered_by_user_id"];

                if ($row["role"] == "Admin") {
                    header("Location: ../../admin/admin_dashboard.php");
                    exit;
                } elseif ($row["role"] == "Midwife") {
                    header("Location: ../../midwife/midwife_dashboard.php");
                    exit;
                } elseif ($row["role"] == "Patient") {
                    header("Location: ../../patient/patient_access.php");
                    exit;
                } else {
                    // fallback kung may ibang role o wala
                    header("Location: ../../landing-page/system_LandingPg.php");
                    exit;
                }

            } else {
                $_SESSION['errorMessage2'] = "Email/Password does not match!";
                header('location: login.php');
                exit;
            }
        } else {
            $_SESSION['errorMessage3'] = "Account not Found";
            header('location: login.php');
            exit;
        }
    }
}
?>