<?php

require_once "../../../module/db.config.php";
session_start();

if (isset($_POST['submit_pregnancy'])) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        if (empty($_POST['patient_id'])) {
            $_SESSION['statusMessage'] = "Missing patient id";
            $_SESSION['statusMessageCode'] = "error";
            header("Location: ../../midwife_dashboard.php");
            exit;
        }

        $patient_id = intval($_POST['patient_id']);

        $conn->begin_transaction();

        try {
            //insert pregnancy 
            $sql_pregnancy = file_get_contents('../../../queries/maternal_insert/insert_pregnancy.sql');
            $stmt_pregnancy = $conn->prepare($sql_pregnancy);

            if (isset($_POST['gravidity']) && $_POST['gravidity'] !== '') {
                $gravidity = $_POST['gravidity'];
            } else {
                $gravidity = null;
            }
            if (isset($_POST['parity']) && $_POST['parity'] !== '') {
                $parity = $_POST['parity'];
            } else {
                $parity = null;
            }
            $stmt_pregnancy->bind_param(
                "isiissss",
                $patient_id,
                $_POST['lmp'],
                $gravidity,
                $parity,
                $_POST['edc'],
                $_POST['outcome'],
                $_POST['date_terminated'],
                $_POST['sex']
            );

            if (!$stmt_pregnancy->execute()) {
                throw new Exception("Pregnancy Details Failed: " . $stmt_pregnancy->error);
            }

            $pregnancy_id = $conn->insert_id;
            $stmt_pregnancy->close();
            //end-> insert pregnancy

            //insert delivery
            $sql_delivery = file_get_contents('../../../queries/maternal_insert/insert_delivery.sql');
            $stmt_delivery = $conn->prepare($sql_delivery);

            if (isset($_POST['bemonc_cemonc_capable'])) {
                $bemonc_cemonc_capable = 1;
            } else {
                $bemonc_cemonc_capable = 0;
            }
            if (isset($_POST['birth_weight']) &&  $_POST['birth_weight'] !== '') {
                $birth_weight = $_POST['birth_weight'];
            } else {
                $birth_weight = null;
            }

            $stmt_delivery->bind_param(
                "issdssisssss",
                $pregnancy_id,
                $_POST['delivery_type'],
                $_POST['birth_weight_classification'],
                $birth_weight,
                $_POST['health_facility_type'],
                $_POST['health_facility_name'],
                $bemonc_cemonc_capable,
                $_POST['ownership'],
                $_POST['non_health_facility_type'],
                $_POST['non_health_facility_name'],
                $_POST['birth_attendant'],
                $_POST['remarks']
            );

            if (!$stmt_delivery->execute()) {
                throw new Exception("Delivery Insert Failed: " . $stmt_delivery->error);
            }
            $delivery_id = $conn->insert_id;
            $stmt_delivery->close();
            //end-> insert delivery

            //insert prenatal checkup
            $sql_prenatalCheckup = file_get_contents('../../../queries/maternal_insert/insert_prenatal_checkup.sql');
            $stmt_prenatalCheckup = $conn->prepare($sql_prenatalCheckup);

            if (isset($_POST['bmi']) && $_POST['bmi'] !== '') {
                $bmi = $_POST['bmi'];
            } else {
                $bmi = null;
            }
            $stmt_prenatalCheckup->bind_param(
                "isssdsss",
                $pregnancy_id,
                $_POST['checkup_date'],
                $_POST['trimester'],
                $_POST['bmi_class'],
                $bmi,
                $_POST['deworming_status'],
                $_POST['deworming_date_given'],
                $_POST['remarks']
            );

            if (!$stmt_prenatalCheckup->execute()) {
                throw new Exception("Prenatal Check-up Insert Failed: " . $stmt_prenatalCheckup->error);
            }
            $checkup_id = $conn->insert_id;
            $stmt_prenatalCheckup->close();
            //end-> insert prenatal checkup

            // insert disease screening
            $sql_disease_screening = file_get_contents('../../../queries/maternal_insert/insert_maternal_screening.sql');
            $stmt_disease_screening = $conn->prepare($sql_disease_screening);

            if (isset($_POST['given_iron'])) {
                $given_iron = 1;
            } else {
                $given_iron = 0;
            }
            if (isset($_POST['cbc_hgb_hct_count']) && $_POST['cbc_hgb_hct_count'] !== '') {
                $cbc_hgb_hct_count = $_POST['cbc_hgb_hct_count'];
            } else {
                $cbc_hgb_hct_count = null;
            }

            $stmt_disease_screening->bind_param(
                "issssssssssssdsssiss",
                $pregnancy_id,
                $_POST['syphilis_screening'],
                $_POST['syphilis_date'],
                $_POST['syphilis_screening_remarks'],
                $_POST['hepatitis_b_screening'],
                $_POST['hepatitisB_date'],
                $_POST['hepatitis_b_screening_remarks'],
                $_POST['hiv_screening'],
                $_POST['hiv_date'],
                $_POST['hiv_screening_remarks'],
                $_POST['gestational_diabetes_screening'],
                $_POST['gestational_diabetes_date'],
                $_POST['diabetes_remarks'],
                $cbc_hgb_hct_count,
                $_POST['cbc_hgb_hct_date'],
                $_POST['anemia_status'],
                $_POST['anemia_status_remarks'],
                $given_iron,
                $_POST['given_iron_date'],
                $_POST['maternal_screening_remark']
            );

            if (!$stmt_disease_screening->execute()) {
                throw new Exception("Maternal Disease Screening Insert Failed: " . $stmt_disease_screening->error);
            }
            $screening_id = $conn->insert_id;
            $stmt_disease_screening->close();
            //end-> insert disease screening

             //insert immunization
            $sql_immunization = file_get_contents('../../../queries/maternal_insert/insert_maternal_immunization.sql');
            $stmt_immunization = $conn->prepare($sql_immunization);

            if (isset($_POST['immunization_type']) && is_array($_POST['immunization_type'])) {
                $immunization_types = $_POST['immunization_type'];
                $immunization_dates = $_POST['immunization_date'] ?? [];

                $record_immunine_count = count($immunization_types);

                if ($stmt_immunization === false) {
                    error_log("Prepare statement failed: " . $conn->error);
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }

                for ($i = 0; $i < $record_immunine_count; $i++) {
                    $current_immunization_type = trim($immunization_types[$i] ?? '');
                    $current_immunization_date = (!empty($immunization_dates[$i])) ? $immunization_dates[$i] : '0000-00-00';
                    if (empty($current_immunization_type)) {
                        continue;
                    }

                    $stmt_immunization->bind_param(
                        "iss",
                        $pregnancy_id,
                        $current_immunization_type,
                        $current_immunization_date
                    );
                    if (!$stmt_immunization->execute()) {
                        throw new Exception("Immunization Insert Failed: " . ($i + 1) . ": " .$stmt_immunization->error);
                    }
                }
            } else {
                error_log("No supplement data submitted.");
            }
            $maternal_immunization_id = $conn->insert_id;
            $stmt_immunization->close();
            //end-> insert immunization

            //insert fim status
            $sql_fim = file_get_contents('../../../queries/maternal_insert/fim_status_mat.sql');
            $stmt_fim = $conn->prepare($sql_fim);

            if (isset($_POST['fim_status'])) {
                $fim_status = 1;
            } else {
                $fim_status = 0;
            }
            $stmt_fim->bind_param(
                "ii",
                $pregnancy_id,
                $fim_status
            );
            if (!$stmt_fim->execute()) {
                throw new Exception("Fim Status Insert Failed: " . $stmt_fim->error);
            }
            $fim_id = $conn->insert_id;
            $stmt_fim->close();
            //end insert fim status

             //insert dynamic supplements
            $sql_supplements = file_get_contents('../../../queries/maternal_insert/insert_maternal_supp.sql');
            $stmt_supplements = $conn->prepare($sql_supplements);

            if (isset($_POST['supplement_type']) && is_array($_POST['supplement_type'])) {
                $supplement_types = $_POST['supplement_type'];
                $trimesters = $_POST['supp_trimester'] ?? [];
                $dates_supp = $_POST['date_supp'] ?? [];
                $tablets_given_array = $_POST['supp_tablets_given'] ?? [];

                $record_count = count($supplement_types);

                if ($stmt_supplements === false) {
                    error_log("Prepare statement failed: " . $conn->error);
                    throw new Exception("Prepare statement failed: " . $conn->error);
                }

                for ($i = 0; $i < $record_count; $i++) {
                    $current_supplement_type = trim($supplement_types[$i] ?? '');
                    $current_trimester = trim($trimesters[$i] ?? '');
                    $current_date_supp = (!empty($dates_supp[$i])) ? $dates_supp[$i] : '0000-00-00';
                    $current_tablets_given = (int)intval($tablets_given_array[$i] ?? 0);

                    if (empty($current_supplement_type)) {
                        continue;
                    }

                    $stmt_supplements->bind_param(
                        "isssi",
                        $pregnancy_id,
                        $current_supplement_type,
                        $current_trimester,
                        $current_date_supp,
                        $current_tablets_given
                    );

                    if (!$stmt_supplements->execute()) {
                        throw new Exception("Maternal Supplements Insert Failed  " . ($i + 1) . ": " . $stmt_supplements->error);
                    }
                }
            } else {
                error_log("No supplement data submitted.");
            }

            $stmt_supplements->close();
            $maternal_supplement_id = $conn->insert_id;
            //end-> insert dynamic supplements

            //insert iodine supplement
            $sql_iodine_supp = file_get_contents('../../../queries/maternal_insert/insert_iodine_pre.sql');
            $stmt_iodine = $conn->prepare($sql_iodine_supp);

            if (isset($_POST['iodine_capsule_given'])) {
                $iodine_capsule_given = 1;
            } else {
                $iodine_capsule_given = 0;
            }

            $stmt_iodine->bind_param(
                "isi",
                $pregnancy_id,
                $_POST['date_iodine'],
                $iodine_capsule_given
            );

            if (!$stmt_iodine->execute()) {
                throw new Exception("Iodine Supplement Insert Failed: " . $stmt_iodine->error);
            }

            $stmt_iodine->close();
            $iodine_id = $conn->insert_id;
            //insert iodine supplement

            //postpartum checkup
            $sql_postpartum_checkup = file_get_contents('../../../queries/maternal_insert/insert_postpartum.sql');
            $stmt_postpartum_checkup = $conn->prepare($sql_postpartum_checkup);

            $stmt_postpartum_checkup->bind_param(
                "iissssss",
                $_POST['patient_id'],
                $pregnancy_id,
                $_POST['post_delivery_date'],
                $_POST['post_delivery_time'],
                $_POST['checkup_visit'],
                $_POST['post_checkup_date'],
                $_POST['breastfeeding_date'],
                $_POST['breastfeeding_time']
            );

            if (!$stmt_postpartum_checkup->execute()) {
                throw new Exception("Postpartum Checkup Insert Failed: " . $stmt_postpartum_checkup->error);
            }

            $checkup_id = $conn->insert_id;
            $stmt_postpartum_checkup->close();
            //end-> postpartum checkup

            //insert postpartum supplement
            $sql_postpartum_supp = file_get_contents('../../../queries/maternal_insert/insert_post_supp.sql');
            $stmt_postpartum_supp = $conn->prepare($sql_postpartum_supp);

            if (isset($_POST['tablets_given']) && $_POST['tablets_given'] !== '') {
                $tablets_given = $_POST['tablets_given'];
            } else {
                $tablets_given = null;
            }

            $stmt_postpartum_supp->bind_param(
                "iissis",
                $_POST['patient_id'],
                $pregnancy_id,
                $_POST['iron_folic_month_given'],
                $_POST['iron_folic_date_given'],
                $_POST['tablets_given'],
                $_POST['remarks']
            );

            if (!$stmt_postpartum_supp->execute()) {
                throw new Exception("Postpartum Supplemet Insert Failed: " . $stmt_postpartum_supp->error);
            }

            $post_supp_id = $conn->insert_id;
            $stmt_postpartum_supp->close();
            //end-> postpartum supplement

            //vitamin a postpartum
            $sql_vitamin = file_get_contents('../../../queries/maternal_insert/insert_vitamin.sql');          
            $stmt_vitamin = $conn->prepare($sql_vitamin);

            if (isset($_POST['vitamin_a'])) {
                $vitamin_a = 1;
            } else {
                $vitamin_a = 0;
            }

            $stmt_vitamin->bind_param(
                "iiis",
                $_POST['patient_id'],
                $pregnancy_id,
                $vitamin_a,
                $_POST['vitamin_a_date'] 
            );
            
            if (!$stmt_vitamin->execute()) {
                throw new Exception("Postpartum Supplement Insert Failed: " . $stmt_vitamin->error);
            }
            $stmt_vitamin->close();
            $vitamin_a_id = $conn->insert_id;
            //vitamin a postpartum

            $conn->commit(); //proceed sa pag save sa db

            //pagsuccess
            $_SESSION['statusMessage'] = "Record Added Successfully!";
            $_SESSION['statusMessageCode'] = "success";
            header("Location: ../../midwife_dashboard.php");
            exit();
        } catch (\Throwable $e) {
            $conn->rollback(); //pag may error undo yung changes
            die("Error: " . $e->getMessage());
        }
    }
}else {
        header("Location: ../../midwife_dashboard.php");
        exit;
    }
