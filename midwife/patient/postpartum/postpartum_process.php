<?php

require_once "../../../module/db.config.php";
session_start();

if (isset($_POST['submit_btn'])) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'];
        $last_name = $_POST['last_name'];
        $birth_date = $_POST['birth_date'];

        $sql_pospartum_patient_check = "SELECT patient_id FROM patient WHERE
                first_name = ? AND
                middle_name = ? AND
                last_name = ? AND
                birth_date = ? AND
                patient_type = 'postpartum_mother'
            ";

        $stmt_postpartum_patient_check = $conn->prepare($sql_pospartum_patient_check);
        $stmt_postpartum_patient_check->bind_param(
            "ssss",
            $first_name,
            $middle_name,
            $last_name,
            $birth_date
        );
        $stmt_postpartum_patient_check->execute();
        $postpartum_patient_result = $stmt_postpartum_patient_check->get_result();

        if ($postpartum_patient_result->num_rows > 0) {
            $_SESSION['statusMessage'] = " Maternal Postpartum Record already exists!";
            $_SESSION['statusMessageCode'] = "error";
            header("Location: ../../midwife_dashboard.php");
            exit();
        }
        $stmt_postpartum_patient_check->close();

        $conn->begin_transaction();

        try {

            $sql_postpartum_patient_record = file_get_contents('../../../queries/maternal_insert/insert_maternal_patient.sql');
            $stmt_postpartum_patient_insert = $conn->prepare($sql_postpartum_patient_record);

            $user_id = $_SESSION['user_id'];
            $registered_by_midwife_id = $_SESSION['user_id'];
            $health_center_id = $_SESSION['health_center_id'];
            $patient_type = 'postpartum_mother'; //para maindicate na for mother itong record haha
            $mother_id = NULL; //since maternal record to, di nya need ng mother id, and purpose ng mother id ay para malink yung infant record sa record ng mother nya
            $name_of_mother = NULL;
            $pregnancy_id = !empty($_POST['pregnancy_id']) ? intval($_POST['pregnancy_id']) : null;
            
            //check kung valid yung age na ininput
            if (!is_numeric($_POST['age']) || intval($_POST['age']) < 0) {
                throw new Exception("Invalid age provided");
            }
            $age = intval($_POST['age']);

            $contact_number = preg_replace('/[^0-9]/', '', $_POST['contact_number']); //check kung may ibang invalid char na kasama sa number, pag meron->tatanggalin
            //11 lang length ng phone num dapat
            if (strlen($contact_number) < 10 || strlen($contact_number) > 11) {
                throw new Exception("Invalid contact number length");
            }

            //check kung numbers ba ang input ng user or chars
            if (!is_numeric($contact_number)) {
                throw new Exception("Invalid contact number format.");
            }


            $stmt_postpartum_patient_insert->bind_param(
                "iisssssssssssisssi",
                $user_id,
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
                $_POST['age_bracket'],
                $birth_date,
                $age,
                $_POST['socio_economic_status'],
                $_POST['contact_number'],
                $_POST['email'],
                $health_center_id
            );

            if (!$stmt_postpartum_patient_insert->execute()) {
                throw new Exception("Postpartum Patient Record Insert Failed: " . $stmt_postpartum_patient_insert->error);
            }

            $patient_id = $conn->insert_id;
            $stmt_postpartum_patient_insert->close();

            //postpartum insert
            $sql_pospartum_insert_record = file_get_contents('../../../queries/maternal_insert/insert_postpartum.sql');
            $stmt_postpartum_insert_record = $conn->prepare($sql_pospartum_insert_record);
            

            $stmt_postpartum_insert_record->bind_param(
                "iissssss",
                $patient_id,
                $pregnancy_id,
                $_POST['post_delivery_date'],
                $_POST['post_delivery_time'],
                $_POST['checkup_visit'],
                $_POST['post_checkup_date'],
                $_POST['breastfeeding_date'],
                $_POST['breastfeeding_time']
            );

            if (!$stmt_postpartum_insert_record->execute()) {
                throw new Exception("Postpartum Record Insert Failed: " . $stmt_postpartum_insert_record->error);
            }

            $checkup_id = $conn->insert_id;
            $stmt_postpartum_insert_record->close();

            //postpartum supplement
                $sql_postpartum_supp = file_get_contents('../../../queries/maternal_insert/insert_post_supp.sql');
                $stmt_postpartum_supp = $conn->prepare($sql_postpartum_supp);

                if (isset($_POST['vitamin_a'])) {
                    $vitamin_a = 1;
                }else{
                    $vitamin_a = 0;
                }

                $stmt_postpartum_supp->bind_param("iissis",
                $patient_id,
                    $pregnancy_id,
                    $_POST['iron_folic_month_given'],
                    $_POST['iron_folic_date_given'],
                    $_POST['tablets_given'],
                    $_POST['remarks']
                );

                if (!$stmt_postpartum_supp->execute()) {
                    throw new Exception("Postpartum Supplement Insert Failed: " .$stmt_postpartum_supp->error);
                }

                $post_supp_id = $conn->insert_id;
                $stmt_postpartum_supp->close();

                //postpartum vitamins
               
                $sql_postpartum_vit= file_get_contents('../../../queries/maternal_insert/insert_vitamin.sql');
                $stmt_postpartum_vita = $conn->prepare($sql_postpartum_vit);

                if (isset($_POST['vitamin_a'])) {
                    $vitamin_a = 1;
                }else{
                    $vitamin_a = 0;
                }

                $stmt_postpartum_vita->bind_param("iiis",
                $patient_id,
                    $pregnancy_id,    
                    $vitamin_a,
                    $_POST['vitamin_a_date']

                );

                if (!$stmt_postpartum_vita->execute()) {
                    throw new Exception("Postpartum Supplement Insert Failed: " .$stmt_postpartum_vita->error);
                }

                $vitamin_a_id = $conn->insert_id;
                $stmt_postpartum_vita->close();


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
                header("Location: add_postpartum.php");
                exit();
        }
    }
}
