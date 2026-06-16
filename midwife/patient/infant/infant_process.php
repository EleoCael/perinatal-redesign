<?php
require_once "../../../module/db.config.php";
session_start();

//this is for standalone infant registration
if (isset($_POST['submit_btn'])) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        //infant details
        $first_name = $_POST['infant_first_name'];
        $middle_name = $_POST['infant_middle_name'];
        $last_name = $_POST['infant_last_name'];
        $birth_date = $_POST['infant_birth_date'];
        $name_of_mother = $_POST['name_of_mother'];
        $mother_id = $_POST['mother_id'] ?? null; //para sa standalone na registration for infants

        //check kung may record na yung infant
        $sql_check = "SELECT  patient_id FROM patient WHERE
            first_name = ? AND
            middle_name = ? AND
            last_name = ? AND
            birth_date =? AND
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

        //pag meron
        if ($result_check->num_rows > 0) {
            $_SESSION['statusMessage'] = " Infant Record already exists!";
            $_SESSION['statusMessageCode'] = "error";
            header("Location: ../../midwife_dashboard.php");
            exit();
        }
        $stmt_check_infant->close();
        
        $conn->begin_transaction();

        try {

            //Auto create yung account ng infant lang gamit yung email nung nanay
            $registered_by_midwife_id = (int)$_SESSION['user_id'];
            $health_center_id = (int)$_SESSION['health_center_id'];

            $email_for_user= trim($_POST['email'] ?? '');
            $patient_user_id = NULL;
            if (!empty($email_for_user)) {

               $stmt_chk_user = $conn->prepare("SELECT user_id FROM user WHERE user_email = ? LIMIT 1");
                $stmt_chk_user->bind_param("s", $email_for_user);
                $stmt_chk_user->execute();
                $stmt_chk_user->bind_result($existing_uid);
                if ($stmt_chk_user->fetch()) {
                    $stmt_chk_user->close();
                    throw new Exception("Patient login already exists for: ".$email_for_user);
                }
                $stmt_chk_user->close();

                function _rand_hex($bytes){ return bin2hex(random_bytes($bytes)); }
                $activation_token = _rand_hex(16); // 32 character hex string (plain)
                $activation_token_hash = hash("sha256", $activation_token); // Hash for database
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
                throw new Exception("User insert failed: ".$stmt_user->error);
            }
            $patient_user_id = $stmt_user->insert_id;
            $stmt_user->close();

            //magsesend ng avtivation link sa email na prinovide
            require_once $_SERVER['DOCUMENT_ROOT'] . '/rhusystem/system/forgot-password/mailer.php';

            //pang buo ng activation link para may iki-clink na lang
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
                . "://" . $_SERVER['HTTP_HOST'];

            $activation_link = $base_url . "/rhusystem/system/forgot-password/activate_account.php?token=" . urlencode($activation_token);

            //eto yung laman ng email or yung template
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
                //pag di nagsend yung email, may magsesend sa email ni rhu na warning
                $_SESSION['email_warning'] = "Patient account created, but activation email failed to send.";
            }

            }

            //end->auto create account

            //insert infant patient tab
            $sql_infant_patient = file_get_contents('../../../queries/infant_insert/insert_infant_patient.sql');
            if ($sql_infant_patient === false) {
               $_SESSION['statusMessage'] = "Could not load SQL file. Please contact support.";
                $_SESSION['statusMessageCode'] = "error";
                header("Location: add_infant.php");
                exit();
            }

            $stmt_infant_patient = $conn->prepare($sql_infant_patient);

            $user_id = $_SESSION['user_id'];
            $registered_by_midwife_id = (int)$_SESSION['user_id'];
            $health_center_id = (int)$_SESSION['health_center_id'];
            $patient_type = 'infant';
            $age_bracket = NULL;
            $age = NULL;
            $email = !empty($email_for_user) ? $email_for_user : NULL; 

             $contact_number = preg_replace('/[^0-9]/', '', $_POST['contact_number']); //check kung may ibang invalid char na kasama sa number, pag meron->tatanggalin
            //11 lang length ng phone num dapat
            if (strlen($contact_number) < 10 || strlen($contact_number) > 11) {
                throw new Exception("Invalid contact number length");
            }

            //check kung numbers ba ang input ng user or chars
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
            //end-> insert infant patient tab

            //insert infant screening
            $delivery_id = NULL;
            $sql_infant_screeening = file_get_contents('../../../queries/infant_insert/insert_infant_screening.sql');
            $stmt_infant_screening = $conn->prepare($sql_infant_screeening);

            $stmt_infant_screening->bind_param("iiiddssssss",
                $patient_id,
                $delivery_id,
                $mother_id,
                $_POST['birth_weight'],
                $_POST['birth_height'],
                $_POST['sex'],
                $_POST['newborn_screening_referral'],
                $_POST['newborn_screening_done'],
                $_POST['cpab_tt_status'],
                $_POST['cpab_tt_date'],
                $_POST['cpab_tt_date_assessed']
            );

            if (!$stmt_infant_screening->execute()) {
                throw new Exception("Infant Screening Insert Failed: " .$stmt_infant_screening->error);
            }
            $infant_id = $conn->insert_id;
            $stmt_infant_screening->close();
            //end-> insert infant screening

            //insert infant exclusive breastfeeding
            $sql_infant_exclusive_feeding = file_get_contents('../../../queries/infant_insert/insert_feeding.sql');
            $stmt_infant_exclusive_feeding = $conn->prepare($sql_infant_exclusive_feeding);

            if (isset($_POST['month_check']) && is_array($_POST['month_check'])) {
                $month_check = $_POST['month_check'];
                $month_date = $_POST['month_date'] ?? [];

            $record_exclusive_feeding = count($month_check);

                if ($stmt_infant_exclusive_feeding === false) {
                    error_log("Prepare statement failed: " . $conn->error);
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }

                for ($i = 0; $i < $record_exclusive_feeding; $i++) {
                    $current_month_check = trim($month_check[$i] ?? '');
                    $current_month_date = (!empty($month_date[$i])) ? $month_date[$i] : '0000-00-00';
                    if (empty($current_month_check)) {
                        continue;
                    }

                    $stmt_infant_exclusive_feeding->bind_param(
                        "iss",
                        $patient_id,
                        $current_month_check,
                        $current_month_date
                    );
                    if (!$stmt_infant_exclusive_feeding->execute()) {
                        throw new Exception("Exclusive Feeding Insert Failed: " . ($i + 1) . ": " .$stmt_infant_exclusive_feeding->error);
                    }
                }
            } else {
                error_log("No Exclusive Feeding data submitted.");
            }
            $infant_exclusive_feeding_id = $conn->insert_id;
            $stmt_infant_exclusive_feeding->close();
            //end-> insert infant xclusive breastfeeding

             //insert infant complementary breastfeeding
             $sql_infant_complementary_feeding = file_get_contents('../../../queries/infant_insert/insert_feeding_2.sql');
             $stmt_infant_complementary_feeding = $conn->prepare($sql_infant_complementary_feeding);

             if (isset($_POST['complementary_month_check']) && is_array($_POST['complementary_month_check'])) {
                $month_com_check = $_POST['complementary_month_check'];
                $month_com_date = $_POST['complementary_month_date'] ?? [];

                $record_comple_feeding = count($month_com_check);

                if ($stmt_infant_complementary_feeding === false) {
                    error_log("Prepare statement failed: " . $conn->error);
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }

                for ($i = 0; $i < $record_comple_feeding; $i++) {
                    $current_month_com_check = trim($month_com_check[$i] ?? '');
                    $current_month_com_date = (!empty($month_com_date[$i])) ? $month_com_date[$i] : '0000-00-00';
                    if (empty($current_month_com_check)) {
                        continue;
                    }

                    $stmt_infant_complementary_feeding->bind_param(
                        "iss",
                        $patient_id,
                        $current_month_com_check,
                        $current_month_com_date
                    );
                    if (!$stmt_infant_complementary_feeding->execute()) {
                        throw new Exception("Exclusive Feeding Insert Failed: " . ($i + 1) . ": " .$stmt_infant_complementary_feeding->error);
                    }
                }
            } else {
                error_log("No Exclusive Feeding data submitted.");
            }
            $complementary_feeding_id = $conn->insert_id;
            $stmt_infant_complementary_feeding->close();
             //end-> insert infant complementary breastfeeding

            //is still breastfeeding
           /* $sql_breastfeed = file_get_contents('../../../queries/infant_insert/insert_breastfeed.sql');
            $stmt_breastfeed = $conn->prepare($sql_breastfeed);

            if (isset($_POST['is_still_breastfeed'])) {
                $is_still_breastfeed = 1;
            } else {
                $is_still_breastfeed = 0;
            }

            $stmt_breastfeed->bind_param(
                "ii",
                $patient_id,
                $is_still_breastfeed
            );
            if (!$stmt_breastfeed->execute()) {
                throw new Exception("Fim Status Insert Failed: " . $stmt_breastfeed->error);
            }
            $brestfeed_month= $conn->insert_id;
            $stmt_breastfeed->close();

            //is still breastfeeding
    */
            //======EDIT THIS SHIT=========//

             //insert bcg immunization
             $sql_bcg = file_get_contents('../../../queries/infant_insert/insert_bcg_immunization.sql');
             $stmt_bcg  = $conn->prepare($sql_bcg);

             if (isset($_POST['bcg_check'])) {
                $is_bcg_check = 1;
             } else{
                $is_bcg_check = 0;
             }

             $stmt_bcg->bind_param("iis", $patient_id, $is_bcg_check, $_POST['bcg_date']);
             if (!$stmt_bcg->execute()) {
                throw new Exception("BCG Insert Failed: " . $stmt_bcg->error);
            }
            $bcg_id = $conn->insert_id;
            $stmt_bcg->close();
             //insert bcg immunization

             //insert hepaB immunization
             $sql_hepaB= file_get_contents('../../../queries/infant_insert/insert_hepaB_immunization.sql');
             $stmt_hepaB = $conn->prepare($sql_hepaB);

            $stmt_hepaB->bind_param(
                "iss",
                $patient_id,
                $_POST['hepaB_day'],
                $_POST['hepaB_date']
            );

            if (!$stmt_hepaB->execute()) {
                throw new Exception("HepaB Data Insert Failed: " . $stmt_hepaB->error);
            }

            $hepaB_id = $conn->insert_id;
            $stmt_hepaB->close();
             //insert hepaB immunization

             //insert pentavalent 
             $sql_pentavalent= file_get_contents('../../../queries/infant_insert/insert_pentavalent_immunization.sql');
             $stmt_pentavalent = $conn->prepare($sql_pentavalent);

            $stmt_pentavalent->bind_param(
                "iss",
                $patient_id,
                $_POST['pentavalent_type'],
                $_POST['pentavalent_date']
            );

            if (!$stmt_pentavalent->execute()) {
                throw new Exception("Pentavalent Data Insert Failed: " . $stmt_pentavalent->error);
            }

            $pentavalent_id = $conn->insert_id;
            $stmt_pentavalent->close();
             //insert pentavalent 

             //insert opv 
             $sql_opv= file_get_contents('../../../queries/infant_insert/insert_opv_immunization.sql');
             $stmt_opv = $conn->prepare($sql_opv);

            $stmt_opv->bind_param(
                "iss",
                $patient_id,
                $_POST['opv_type'],
                $_POST['opv_date']
            );

            if (!$stmt_opv->execute()) {
                throw new Exception("OPV Data Insert Failed: " . $stmt_opv->error);
            }

            $opv_id = $conn->insert_id;
            $stmt_opv->close();
             //insert opv

             //insert ipv
             $sql_ipv = file_get_contents('../../../queries/infant_insert/insert_ipv_immunization.sql');
             $stmt_ipv = $conn->prepare($sql_ipv);

             if (isset($_POST['ipv_1'])) {
                $is_ipv = 1;
             } else{
                $is_ipv = 0;
             }

             $stmt_ipv->bind_param("iis", $patient_id, $is_ipv, $_POST['ipv_date']);
             if (!$stmt_ipv->execute()) {
                throw new Exception("IPV Insert Failed: " . $stmt_ipv->error);
            }
            $ipv_id = $conn->insert_id;
            $stmt_ipv->close();
             //insert ipv

              //insert mcv 
             $sql_mcv = file_get_contents('../../../queries/infant_insert/insert_mcv_immunization.sql');
             $stmt_mcv = $conn->prepare($sql_mcv);

            $stmt_mcv->bind_param(
                "iss",
                $patient_id,
                $_POST['mcv_type'],
                $_POST['mcv_date']
            );

            if (!$stmt_mcv->execute()) {
                throw new Exception("MCV Data Insert Failed: " . $stmt_mcv->error);
            }

            $mcv_id = $conn->insert_id;
            $stmt_mcv->close();
             //insert mcv

            //insert fic
             $sql_fic = file_get_contents('../../../queries/infant_insert/insert_fic_immunization.sql');
             $stmt_fic = $conn->prepare($sql_fic);

             if (isset($_POST['fic_check'])) {
                $is_fic = 1;
             } else{
                $is_fic = 0;
             }

             $stmt_fic->bind_param("iis", $patient_id, $is_fic, $_POST['fic_date']);
             if (!$stmt_fic->execute()) {
                throw new Exception("FIC Insert Failed: " . $stmt_fic->error);
            }
            $fic_id = $conn->insert_id;
            $stmt_fic->close();
             //insert fic

            //insert rvv 
             $sql_rvv = file_get_contents('../../../queries/infant_insert/insert_rvv_immunization.sql');
             $stmt_rvv = $conn->prepare($sql_rvv);

            $stmt_rvv->bind_param(
                "iss",
                $patient_id,
                $_POST['rvv_type'],
                $_POST['rvv_date']
            );

            if (!$stmt_rvv->execute()) {
                throw new Exception("RVV Data Insert Failed: " . $stmt_rvv->error);
            }

            $rvv_id = $conn->insert_id;
            $stmt_rvv->close();
             //insert rvv

             //insert rvv 
             $sql_pcv = file_get_contents('../../../queries/infant_insert/insert_pcv_immunization.sql');
             $stmt_pcv = $conn->prepare($sql_pcv);

            $stmt_pcv->bind_param(
                "iss",
                $patient_id,
                $_POST['pcv_type'],
                $_POST['pcv_date']
            );

            if (!$stmt_pcv->execute()) {
                throw new Exception("PCV Data Insert Failed: " . $stmt_pcv->error);
            }

            $pcv_id = $conn->insert_id;
            $stmt_pcv->close();
             //insert rvv

             //insert vitamin A 
             $sql_vitamin = file_get_contents('../../../queries/infant_insert/insert_vitamin_a.sql');
             $stmt_vitamin = $conn->prepare($sql_vitamin);

            $stmt_vitamin->bind_param(
                "iss",
                $patient_id,
                $_POST['vitamin_type'],
                $_POST['vitamin_date']
            );

            if (!$stmt_vitamin->execute()) {
                throw new Exception("Vitamin A Data Insert Failed: " . $stmt_vitamin->error);
            }

            $vitamin_a_infant_id = $conn->insert_id;
            $stmt_vitamin->close();
             //insert vitamin A 

              //insert iron
             $sql_iron = file_get_contents('../../../queries/infant_insert/insert_iron_supp.sql');
             $stmt_iron = $conn->prepare($sql_iron);

            $stmt_iron->bind_param(
                "iss",
                $patient_id,
                $_POST['iron_type'],
                $_POST['iron_date']
            );

            if (!$stmt_iron->execute()) {
                throw new Exception("Vitamin A Data Insert Failed: " . $stmt_iron->error);
            }

            $iron_infant_id = $conn->insert_id;
            $stmt_iron->close();
             //insert iron

            //insert mnp
             $sql_mnp = file_get_contents('../../../queries/infant_insert/insert_mnp_supp.sql');
             $stmt_mnp = $conn->prepare($sql_mnp);

            $stmt_mnp->bind_param(
                "iss",
                $patient_id,
                $_POST['mnp_type'],
                $_POST['mnp_date']
            );

            if (!$stmt_mnp->execute()) {
                throw new Exception("MNP Data Insert Failed: " . $stmt_mnp->error);
            }

            $mnp_id = $conn->insert_id;
            $stmt_mnp->close();
             //insert mnp

             //insert deworming
             $sql_deworming = file_get_contents('../../../queries/infant_insert/insert_deworming.sql');
             $stmt_deworming  = $conn->prepare($sql_deworming);

             if (isset($_POST['deworming_check'])) {
                $is_dewormed = 1;
             } else{
                $is_dewormed = 0;
             }

             $stmt_deworming->bind_param("iis", $patient_id, $is_dewormed, $_POST['deworming_date']);
             if (!$stmt_deworming->execute()) {
                throw new Exception("Deworming Insert Failed: " . $stmt_deworming->error);
            }
            error_log('rows=' . $stmt->affected_rows . ' id=' . $conn->insert_id);

            $deworming_infant_id = $conn->insert_id;
            $stmt_deworming->close();
             //insert deworming

            $conn->commit(); //proceed sa pag save sa db
            //pagsuccess
            $_SESSION['statusMessage'] = "Record Added Successfully!";
            $_SESSION['statusMessageCode'] = "success";
            header("Location: ../../midwife_dashboard.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['statusMessage'] = $e->getMessage();
            $_SESSION['statusMessageCode'] = "error";
            header("Location: add_infant.php");
            exit();
        }
    }
}
