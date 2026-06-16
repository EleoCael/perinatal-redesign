<?php
require_once "../../../module/db.config.php";
session_start();

if (!isset($_SESSION['health_center_id'])) {
    die("Access denied: No health center assigned");
}

function insertValues($array, $key, $default = 'N/A')
{
    if (!isset($array[$key])) {
        return $default;
    }
    $value = $array[$key];
    if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00' || $value === null) {
        return $default;
    } else {
        return $value;
    }
}
//lilipat sa pregnancy 
function displayCheckbox($array, $key, $default = 'N/A')
{
    if (isset($array[$key]) && $array[$key] !== null) {
        return $array[$key] == 1 ? 'Yes' : 'No';
    } else {
        return $default;
    }
}

if (isset($_POST["patient_id"])) {
    $patient_id = $_POST['patient_id'];
    $health_center_id = $_SESSION['health_center_id'];
    $output = '';

    //patient table
    $view_query = "SELECT * FROM patient WHERE patient_id = ? AND health_center_id =?";
    $stmt_view = $conn->prepare($view_query);
    $stmt_view->bind_param("ii", $patient_id, $health_center_id);
    $stmt_view->execute();
    $patient_result = $stmt_view->get_result();

    if ($patient_result->num_rows == 0) {
        die("Patient not found or access denied");
    }

    while ($row = $patient_result->fetch_assoc()) {
        $full_name = $row['last_name'] . ", " . $row['first_name'];
        if (!empty($row['middle_name'])) {
            $full_name .= " " . $row['middle_name'];
        }
        //$mother_id = $infant_row['mother_id'] ?? null;
        //patient table

        $family_serial_no = insertValues($row, 'family_serial_number');
        $name_of_mother = insertValues($row, 'name_of_mother');
        $contact = insertValues($row, 'contact_number');
        $contact = insertValues($row, 'contact_number');

        //infant assessment
        $screening1_query = "SELECT i.birth_weight, i.birth_height, i.sex 
                                FROM infant i 
                                JOIN patient p ON i.patient_id = p.patient_id
                                WHERE i.patient_id = ? AND p.health_center_id = ?";

        $stmt_screening1 = $conn->prepare($screening1_query);
        if (!$stmt_screening1) {
            die("Query error: " . $conn->error);
        }

        $stmt_screening1->bind_param("ii", $patient_id, $health_center_id);
        $stmt_screening1->execute();
        $screening_result = $stmt_screening1->get_result();
        $screening = $screening_result->fetch_assoc();
        $stmt_screening1->close();

        $birth_weight = insertValues($screening, 'birth_weight');
        $birth_height = insertValues($screening, 'birth_height');
        $sex = insertValues($screening, 'sex');
        //infant assessment

        //infant referral
        $referral_query = "SELECT i.newborn_screening_referral FROM infant i 
                          INNER JOIN patient p ON i.patient_id = p.patient_id 
                          WHERE i.patient_id = ? AND p.health_center_id = ?";
        $stmt_referral = $conn->prepare($referral_query);
        $stmt_referral->bind_param("ii", $patient_id, $health_center_id);
        $stmt_referral->execute();
        $referral_result = $stmt_referral->get_result();
        $referral_date = $referral_result->fetch_assoc() ?? [];

        $referral = insertValues($referral_date, 'newborn_screening_referral');
        //infant referral

        //infant date done
        $date_done_query = "SELECT i.newborn_screening_done FROM infant i 
                           INNER JOIN patient p ON i.patient_id = p.patient_id 
                           WHERE i.patient_id = ? AND p.health_center_id = ?";
        $stmt_date_done = $conn->prepare($date_done_query);
        $stmt_date_done->bind_param("ii", $patient_id, $health_center_id);
        $stmt_date_done->execute();
        $date_done_result = $stmt_date_done->get_result();
        $date_done = $date_done_result->fetch_assoc() ?? [];

        $date_done_infant = insertValues($date_done, 'newborn_screening_done');
        //infant date done

        //infant tt status
        $tt_query = "SELECT i.cpab_tt_status, i.cpab_tt_date 
                    FROM infant i 
                    INNER JOIN patient p ON i.patient_id = p.patient_id 
                    WHERE i.patient_id = ? AND p.health_center_id = ?";
        $stmt_tt_status = $conn->prepare($tt_query);
        $stmt_tt_status->bind_param("ii", $patient_id, $health_center_id);
        $stmt_tt_status->execute();
        $tt_status_result = $stmt_tt_status->get_result();
        $tt_status_date = $tt_status_result->fetch_assoc() ?? [];

        $cpab_tt_status = insertValues($tt_status_date, 'cpab_tt_status');
        $cpab_tt_date = insertValues($tt_status_date, 'cpab_tt_date');
        //infant tt status

        //infant date assessed
        $assessed_query = "SELECT i.cpab_tt_date_assessed FROM infant i 
                          INNER JOIN patient p ON i.patient_id = p.patient_id 
                          WHERE i.patient_id = ? AND p.health_center_id = ?";
        $stmt_assessed = $conn->prepare($assessed_query);
        $stmt_assessed->bind_param("ii", $patient_id, $health_center_id);
        $stmt_assessed->execute();
        $assessed_result = $stmt_assessed->get_result();
        $assessed_date = $assessed_result->fetch_assoc() ?? [];

        $cpab_tt_date_assessed = insertValues($assessed_date, 'cpab_tt_date_assessed');
        //infant date assessed

        //infant exclusive feeding
        if ($row && $row['patient_id']) {
            $exclusive_query = "SELECT ief.*
                               FROM infant_exclusively_breastfed ief 
                               INNER JOIN patient p ON ief.patient_id = p.patient_id 
                               WHERE ief.patient_id = ? AND p.health_center_id = ?";
            $stmt_exclusive = $conn->prepare($exclusive_query);
            $stmt_exclusive->bind_param("ii", $patient_id, $health_center_id);
            $stmt_exclusive->execute();
            $result_exclusively = $stmt_exclusive->get_result();
            $exlusive_feeding = $result_exclusively->fetch_all(MYSQLI_ASSOC);
        } else {
            $exlusive_feeding = [];
        }
        $exlusive_feed_html = '';

        foreach ($exlusive_feeding as $exclusive) {
            $infant_exclusively_breastfed_id = $exclusive['infant_exclusively_breastfed_id'];
            $month_check = insertValues($exclusive, 'month_check');
            $month_date = insertValues($exclusive, 'month_date');

            $exlusive_feed_html .= "
            <div class='exclusive-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
                <div>
                    <p style='margin: 0;'><strong>Month:</strong> {$month_check}</p>
                    <p style='margin: 0;'><strong>Date:</strong> {$month_date}</p>
                </div>
                <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_exclusive_breastfeeding_btn' 
                        data-exclusive-breastfeed-id='{$infant_exclusively_breastfed_id}' 
                        data-patient-id='{$patient_id}'
                        data-month-check='{$month_check}'
                        data-month-date='{$month_date}'
                        title='Edit this breastfeed'>
                    <i class='bi bi-pencil-fill'></i> Edit
                </button>
            </div>
            ";
        }
        $first_exclusive_breastfeed1 = reset($exlusive_feeding);
        if ($first_exclusive_breastfeed1 === false) {
            $first_exclusive_breastfeed1 = [];
        }

        //infant exclusive feeding


        //fim status
        $breastfeed_query = "SELECT smc.is_still_breastfeed 
                            FROM 6th_month_check smc 
                            INNER JOIN patient p ON smc.patient_id = p.patient_id 
                            WHERE smc.patient_id = ? AND p.health_center_id = ?";
        $stmt_breastfeed = $conn->prepare($breastfeed_query);
        $stmt_breastfeed->bind_param("ii", $patient_id, $health_center_id);
        $stmt_breastfeed->execute();
        $result_breastfeed = $stmt_breastfeed->get_result();
        $breastfeed_row = $result_breastfeed->fetch_assoc();
        $breastfeed_status_value = $breastfeed_row['is_still_breastfeed'] ?? null;

        if ($breastfeed_status_value === 1) {
            $breastfeed_status_display = "<strong>Status:</strong> Exclusively BreastFeeding (6th Month) <span><i class='bi bi-check-circle-fill text-success'></i></span>";
            $button_text = 'Update Status';
        } elseif ($breastfeed_status_value === 0) {
            $breastfeed_status_display = "<strong>Status:</strong> Not Exclusively BreastFeeding (6th Month) <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
            $button_text = 'Update Status';
        } else {
            $breastfeed_status_display = "<strong>Status:</strong> N/A";
            $button_text = 'Set Status';
        }

        //infant breastfeeding checkbox

        //infant complementary feeding
        if ($row && $row['patient_id']) {
            $complementary_query = "SELECT icf.complementary_feeding_id, icf.complementary_month_check, icf.complementary_month_date 
                           FROM infant_complementary_feeding icf 
                           INNER JOIN patient p ON icf.patient_id = p.patient_id 
                           WHERE icf.patient_id = ? AND p.health_center_id = ?";
            $stmt_complementary = $conn->prepare($complementary_query);
            $stmt_complementary->bind_param("ii", $patient_id, $health_center_id);
            $stmt_complementary->execute();
            $result_complementary = $stmt_complementary->get_result();
            $complementary_feeding = $result_complementary->fetch_all(MYSQLI_ASSOC);
        } else {
            $complementary_feeding = [];
        }
        $complementary_feed_html = '';

        foreach ($complementary_feeding as $complementary) {
            $complementary_feeding_id = $complementary['complementary_feeding_id'];
            $complementary_month_check = insertValues($complementary, 'complementary_month_check');
            $complementary_month_date = insertValues($complementary, 'complementary_month_date');

            $complementary_feed_html .= "
        <div class='complementary-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Month:</strong> {$complementary_month_check}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$complementary_month_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_complementary_btn' 
                    data-complementary-id='{$complementary_feeding_id}' 
                    data-patient-id='{$patient_id}'
                    data-complementary-month-check='{$complementary_month_check}'
                    data-complementary-month-date='{$complementary_month_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
        }
        //infant complementary feeding

        //infant bcg checkbox
        $query = "SELECT b.bcg_check, b.bcg_date 
                 FROM bcg b 
                 INNER JOIN patient p ON b.patient_id = p.patient_id 
                 WHERE b.patient_id = ? AND p.health_center_id = ?";
        $stmt_query = $conn->prepare($query);
        $stmt_query->bind_param("ii", $patient_id, $health_center_id);
        $stmt_query->execute();
        $result = $stmt_query->get_result();
        $bcg_row = $result->fetch_assoc();
        $bcg_check_value = $bcg_row['bcg_check'] ?? null;
        $bcg_date = $bcg_row['bcg_date'] ?? null;

        if ($bcg_check_value === 1) {
            $bcg_status_display = "<strong>Status:</strong> BCG was received <span><i class='bi bi-check-circle-fill text-success'></i></span>";
            $bcg_date_display = "<strong>Date:</strong> {$bcg_date}";
            $button_text = 'Update Status';
        } elseif ($bcg_check_value === 0) {
            $bcg_status_display = "<strong>Status:</strong> BCG was not received <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
            $bcg_date_display = "<strong>Date:</strong> N/A";
            $button_text = 'Update Status';
        } else {
            $bcg_status_display = "<strong>Status:</strong> N/A";
            $bcg_date_display = "<strong>Date:</strong> N/A";
            $button_text = 'Set Status';
        }
        //infant bcg checkbox

        //infant hepa
        $hepaB_query = "SELECT h.hepaB_day, h.hepaB_date 
                        FROM hepab h 
                        INNER JOIN patient p ON h.patient_id = p.patient_id 
                        WHERE h.patient_id = ? AND p.health_center_id = ?";
        $stmt_hepaB_query = $conn->prepare($hepaB_query);
        $stmt_hepaB_query->bind_param("ii", $patient_id, $health_center_id);
        $stmt_hepaB_query->execute();
        $hepaB_result = $stmt_hepaB_query->get_result();
        $hepaB_row = $hepaB_result->fetch_assoc() ?? [];

        $hepaB_day = insertValues($hepaB_row, 'hepaB_day');
        $hepaB_date = insertValues($hepaB_row, 'hepaB_date');

        //infant hepa

        //infant pentavalent
        if ($row && $row['patient_id']) {
            $pentavalent_query = "SELECT pv.pentavalent_id, pv.pentavalent_type, pv.pentavalent_date 
                                FROM pentavalent pv 
                                INNER JOIN patient p ON pv.patient_id = p.patient_id 
                                WHERE pv.patient_id = ? AND p.health_center_id = ?";
            $stmt_pentavalent = $conn->prepare($pentavalent_query);
            $stmt_pentavalent->bind_param("ii", $patient_id, $health_center_id);
            $stmt_pentavalent->execute();
            $result_pentavalent = $stmt_pentavalent->get_result();
            $pentavalent = $result_pentavalent->fetch_all(MYSQLI_ASSOC);
        } else {
            $pentavalent = [];
        }
        $pentavalent_html = '';

        foreach ($pentavalent as $pentavalent_row) {
            $pentavalent_id = $pentavalent_row['pentavalent_id'];
            $pentavalent_type = insertValues($pentavalent_row, 'pentavalent_type');
            $pentavalent_date = insertValues($pentavalent_row, 'pentavalent_date');

            $pentavalent_html .= "
                <div class='pentavalent-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
                    <div>
                        <p style='margin: 0;'><strong>Type:</strong> {$pentavalent_type}</p>
                        <p style='margin: 0;'><strong>Date:</strong> {$pentavalent_date}</p>
                    </div>
                    <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_pentavalent_btn' 
                        data-pentavalent-id='{$pentavalent_id}' 
                        data-patient-id='{$patient_id}'
                        data-pentavalent-type='{$pentavalent_type}'
                        data-pentavalent-date='{$pentavalent_date}'
                        title='Edit this record'>
                    <i class='bi bi-pencil-fill'></i> Edit
                </button>
                </div>
            ";
        }
        //infant pentavalent

        //infant opv
        if ($row && $row['patient_id']) {
            $opv_query = "SELECT o.*
                        FROM opv o 
                        INNER JOIN patient p ON o.patient_id = p.patient_id 
                        WHERE o.patient_id = ? AND p.health_center_id = ? 
                        ORDER BY o.opv_date ASC";
            $stmt_opv = $conn->prepare($opv_query);
            $stmt_opv->bind_param("ii", $patient_id, $health_center_id);
            $stmt_opv->execute();
            $result_opv = $stmt_opv->get_result();
            $opv = $result_opv->fetch_all(MYSQLI_ASSOC);
        } else {
            $opv = [];
        }
        $opv_html = '';

        foreach ($opv as $opv_row) {
            $opv_id = $opv_row['opv_id'];
            $opv_type = insertValues($opv_row, 'opv_type');
            $opv_date = insertValues($opv_row, 'opv_date');

            $opv_html .= "
                 <div class='opv-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
                <div>
                    <p style='margin: 0;'><strong>Type:</strong> {$opv_type}</p>
                    <p style='margin: 0;'><strong>Date:</strong> {$opv_date}</p>
                </div>
                <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_opv_btn' 
                        data-opv-id='{$opv_id}' 
                        data-patient-id='{$patient_id}'
                        data-opv-type='{$opv_type}'
                        data-opv-date='{$opv_date}'
                        title='Edit this record'>
                    <i class='bi bi-pencil-fill'></i> Edit
                </button>
            </div>
            ";
        }
        //infant opv

        //infant ipv checkbox
        $query = "SELECT i.ipv_1, i.ipv_date 
             FROM ipv i 
             INNER JOIN patient p ON i.patient_id = p.patient_id 
             WHERE i.patient_id = ? AND p.health_center_id = ?";
        $stmt_query = $conn->prepare($query);
        $stmt_query->bind_param("ii", $patient_id, $health_center_id);
        $stmt_query->execute();
        $result = $stmt_query->get_result();
        $ipv_row = $result->fetch_assoc();
        $ipv_check_value = $ipv_row['ipv_1'] ?? null;
        $ipv_date = $ipv_row['ipv_date'] ?? null;

        if ($ipv_check_value === 1) {
            $ipv_check_display = "<strong>Status:</strong> IPV was received <span><i class='bi bi-check-circle-fill text-success'></i></span>";
            $ipv_date_display = "<strong>Date:</strong> {$ipv_date}";
            $button_text = 'Update Status';
        } elseif ($ipv_check_value === 0) {
            $ipv_check_display = "<strong>Status:</strong> IPV was not received <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
            $ipv_date_display = "<strong>Date:</strong> N/A";
            $button_text = 'Update Status';
        } else {
            $ipv_check_display = "<strong>Status:</strong> N/A";
            $ipv_date_display = "<strong>Date:</strong> N/A";
            $button_text = 'Set Status';
        }
        //infant ipv checkbox

        //infant mcv
        if ($row && $row['patient_id']) {
            $mcv_query = "SELECT m.*
                            FROM mcv m 
                            INNER JOIN patient p ON m.patient_id = p.patient_id 
                            WHERE m.patient_id = ? AND p.health_center_id = ?";
            $stmt_mcv = $conn->prepare($mcv_query);
            $stmt_mcv->bind_param("ii", $patient_id, $health_center_id);
            $stmt_mcv->execute();
            $result_mcv = $stmt_mcv->get_result();
            $mcv = $result_mcv->fetch_all(MYSQLI_ASSOC);
        } else {
            $mcv = [];
        }
        $mcv_html = '';

        foreach ($mcv as $mcv_row) {
            $mcv_id = $mcv_row['mcv_id'];
            $mcv_type = insertValues($mcv_row, 'mcv_type');
            $mcv_date = insertValues($mcv_row, 'mcv_date');

            $mcv_html .= "
                 <div class='mcv-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$mcv_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$mcv_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_mcv_btn' 
                    data-mcv-id='{$mcv_id}' 
                    data-patient-id='{$patient_id}'
                    data-mcv-type='{$mcv_type}'
                    data-mcv-date='{$mcv_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
            ";
        }
        //infant mcv

        //infant fic checkbox
        $fic_query = "SELECT f.fic_check, f.fic_date 
             FROM fic f 
             INNER JOIN patient p ON f.patient_id = p.patient_id 
             WHERE f.patient_id = ? AND p.health_center_id = ?";
        $stmt_fic_query = $conn->prepare($fic_query);
        $stmt_fic_query->bind_param("ii", $patient_id, $health_center_id);
        $stmt_fic_query->execute();
        $result_fic = $stmt_fic_query->get_result();
        $fic_row = $result_fic->fetch_assoc();
        $fic_check_value = $fic_row['fic_check'] ?? null;
        $fic_date = $fic_row['fic_date'] ?? null;

        if ($fic_check_value === 1) {
            $fic_check_display = "<strong>Status:</strong> Fully Immunized Child <span><i class='bi bi-check-circle-fill text-success'></i></span>";
            $fic_date_display = "<strong>Date:</strong> {$fic_date}";
            $button_text = 'Update Status';
        } elseif ($fic_check_value === 0) {
            $fic_check_display = "<strong>Status:</strong> Not Fully Immunized Child <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
            $fic_date_display = "<strong>Date:</strong> N/A";
            $button_text = 'Update Status';
        } else {
            $fic_check_display = "<strong>Status:</strong> N/A";
            $fic_date_display = "<strong>Date:</strong> N/A";
            $button_text = 'Set Status';
        }
        //infant fic checkbox

        //infant rvv
        if ($row && $row['patient_id']) {
            $rvv_query = "SELECT r.rvv_id, r.rvv_type, r.rvv_date 
         FROM rota_virus_vaccine r 
         INNER JOIN patient p ON r.patient_id = p.patient_id 
         WHERE r.patient_id = ? AND p.health_center_id = ?";
            $stmt_rvv = $conn->prepare($rvv_query);
            $stmt_rvv->bind_param("ii", $patient_id, $health_center_id);
            $stmt_rvv->execute();
            $result_rvv = $stmt_rvv->get_result();
            $rvv = $result_rvv->fetch_all(MYSQLI_ASSOC);
        } else {
            $rvv = [];
        }
        $rvv_html = '';

        foreach ($rvv as $rvv_row) {
            $rvv_id = $rvv_row['rvv_id']; // Add this line
            $rvv_type = insertValues($rvv_row, 'rvv_type');
            $rvv_date = insertValues($rvv_row, 'rvv_date');

            $rvv_html .= "
        <div class='rvv-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$rvv_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$rvv_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_rvv_btn' 
                    data-rvv-id='{$rvv_id}' 
                    data-patient-id='{$patient_id}'
                    data-rvv-type='{$rvv_type}'
                    data-rvv-date='{$rvv_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
        }
        //infant rvv

        //infant pcv
        if ($row && $row['patient_id']) {
            $pcv_query = "SELECT pc.*
                 FROM pcv pc 
                 INNER JOIN patient p ON pc.patient_id = p.patient_id 
                 WHERE pc.patient_id = ? AND p.health_center_id = ?";
            $stmt_pcv = $conn->prepare($pcv_query);
            $stmt_pcv->bind_param("ii", $patient_id, $health_center_id);
            $stmt_pcv->execute();
            $result_pcv = $stmt_pcv->get_result();
            $pcv = $result_pcv->fetch_all(MYSQLI_ASSOC);
        } else {
            $pcv = [];
        }
        $pcv_html = '';

        foreach ($pcv as $pcv_row) {
            $pcv_id = $pcv_row['pcv_id'];
            $pcv_type = insertValues($pcv_row, 'pcv_type');
            $pcv_date = insertValues($pcv_row, 'pcv_date');

            $pcv_html .= "
                <div class='pcv-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
                <div>
                    <p style='margin: 0;'><strong>Type:</strong> {$pcv_type}</p>
                    <p style='margin: 0;'><strong>Date:</strong> {$pcv_date}</p>
                </div>
                <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_pcv_btn' 
                        data-pcv-id='{$pcv_id}' 
                        data-patient-id='{$patient_id}'
                        data-pcv-type='{$pcv_type}'
                        data-pcv-date='{$pcv_date}'
                        title='Edit this record'>
                    <i class='bi bi-pencil-fill'></i> Edit
                </button>
            </div>
            ";
        }
        //infant pcv

        //infant vitamin
        if ($row && $row['patient_id']) {
            $vitamin_query = "SELECT v.*
                     FROM vitamin_a_infant v 
                     INNER JOIN patient p ON v.patient_id = p.patient_id 
                     WHERE v.patient_id = ? AND p.health_center_id = ?";
            $stmt_vitamin = $conn->prepare($vitamin_query);
            $stmt_vitamin->bind_param("ii", $patient_id, $health_center_id);
            $stmt_vitamin->execute();
            $result_vitamin = $stmt_vitamin->get_result();
            $vitamin = $result_vitamin->fetch_all(MYSQLI_ASSOC);
        } else {
            $vitamin = [];
        }
        $vitamin_html = '';

        foreach ($vitamin as $vitamin_row) {
            $vitamin_id = $vitamin_row['vitamin_a_infant_id'];
            $vitamin_type = insertValues($vitamin_row, 'vitamin_type');
            $vitamin_date = insertValues($vitamin_row, 'vitamin_date');

            $vitamin_html .= "
                <div class='vitamin-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
                <div>
                    <p style='margin: 0;'><strong>Type:</strong> {$vitamin_type}</p>
                    <p style='margin: 0;'><strong>Date:</strong> {$vitamin_date}</p>
                </div>
                <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_vitamin_btn' 
                    data-vitamin-id='{$vitamin_id}' 
                    data-patient-id='{$patient_id}'
                    data-vitamin-type='{$vitamin_type}'
                    data-vitamin-date='{$vitamin_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
            </div>
            ";
        }
        //infant vitamin

        //infant iron
        if ($row && $row['patient_id']) {
            $iron_infant_query = "SELECT ii.*
                         FROM iron_infant ii 
                         INNER JOIN patient p ON ii.patient_id = p.patient_id 
                         WHERE ii.patient_id = ? AND p.health_center_id = ?";
            $stmt_iron_infant = $conn->prepare($iron_infant_query);
            $stmt_iron_infant->bind_param("ii", $patient_id, $health_center_id);
            $stmt_iron_infant->execute();
            $result_iron_infant = $stmt_iron_infant->get_result();
            $iron_infant = $result_iron_infant->fetch_all(MYSQLI_ASSOC);
        } else {
            $iron_infant = [];
        }
        $iron_infant_html = '';

        foreach ($iron_infant as $iron_infant_row) {
            $iron_id = $iron_infant_row['iron_infant_id'];
            $iron_type = insertValues($iron_infant_row, 'iron_type');
            $iron_date = insertValues($iron_infant_row, 'iron_date');

            $iron_infant_html .= "
                <div class='iron-infant-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px;'>
                <div>
                    <p style='margin: 0;'><strong>Type:</strong> {$iron_type}</p>
                    <p style='margin: 0;'><strong>Date:</strong> {$iron_date}</p>
                </div>
                <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_iron_infant_btn' 
                        data-iron-id='{$iron_id}' 
                        data-patient-id='{$patient_id}'
                        data-iron-type='{$iron_type}'
                        data-iron-date='{$iron_date}'
                        title='Edit this record'>
                    <i class='bi bi-pencil-fill'></i> Edit
                </button>
            </div>
            ";
        }
        //infant iron

        //infant MNP
        if ($row && $row['patient_id']) {
            $mnp_query = "SELECT m.*
                 FROM mnp m 
                 INNER JOIN patient p ON m.patient_id = p.patient_id 
                 WHERE m.patient_id = ? AND p.health_center_id = ?";
            $stmt_mnp = $conn->prepare($mnp_query);
            $stmt_mnp->bind_param("ii", $patient_id, $health_center_id);
            $stmt_mnp->execute();
            $result_mnp = $stmt_mnp->get_result();
            $mnp = $result_mnp->fetch_all(MYSQLI_ASSOC);
        } else {
            $mnp = [];
        }
        $mnp_html = '';

        foreach ($mnp as $mnp_row) {
            $mnp_id = $mnp_row['mnp_id'];
            $mnp_type = insertValues($mnp_row, 'mnp_type');
            $mnp_date = insertValues($mnp_row, 'mnp_date');

            $mnp_html .= "
                <div class='mnp-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px;'>
                <div>
                    <p style='margin: 0;'><strong>Type:</strong> {$mnp_type}</p>
                    <p style='margin: 0;'><strong>Date:</strong> {$mnp_date}</p>
                </div>
                <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_mnp_btn' 
                        data-mnp-id='{$mnp_id}' 
                        data-patient-id='{$patient_id}'
                        data-mnp-type='{$mnp_type}'
                        data-mnp-date='{$mnp_date}'
                        title='Edit this record'>
                    <i class='bi bi-pencil-fill'></i> Edit
                </button>
            </div>
            ";
        }
        //infant MNP

        //infant deworming checkbox
        $deworm_query = "SELECT di.deworming_check, di.deworming_date 
                FROM deworming_infant di 
                INNER JOIN patient p ON di.patient_id = p.patient_id 
                WHERE di.patient_id = ? AND p.health_center_id = ?";
        $stmt_deworm_query = $conn->prepare($deworm_query);
        $stmt_deworm_query->bind_param("ii", $patient_id, $health_center_id);
        $stmt_deworm_query->execute();
        $result_deworm = $stmt_deworm_query->get_result();
        $deworm_row = $result_deworm->fetch_assoc();
        $deworming_check_value = $deworm_row['deworming_check'] ?? null;
        $deworming_date = $deworm_row['deworming_date'] ?? null;

        if ($deworming_check_value === 1) {
            $deworming_check_display = "<strong>Status:</strong> Child was Dewormed (Yes) <span><i class='bi bi-check-circle-fill text-success'></i></span>";
            $deworming_date_display = "<strong>Date:</strong> {$fic_date}";
            $button_text = 'Update Status';
        } elseif ($deworming_check_value === 0) {
            $deworming_check_display = "<strong>Status:</strong> Child was not Dewormed (No) <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
            $deworming_date_display = "<strong>Date:</strong> N/A";
            $button_text = 'Update Status';
        } else {
            $deworming_check_display = "<strong>Status:</strong> N/A";
            $deworming_date_display = "<strong>Date:</strong> N/A";
            $button_text = 'Set Status';
        }
        //infant deworming checkbox



        $output .= " 
                <div class = 'table-responsive'>
                    <table class = 'table table-bordered'>
                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label>BASIC INFORMATION</label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>No.</strong></label></td>
                            <td width = '60%'>{$row['patient_id']}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Date of Registration(yyyy-mm-dd)</strong></label></td>
                            <td width = '60%'>{$row['date_of_registration']}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Family Serial No.</strong></label></td>
                            <td width = '60%'>{$family_serial_no}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Full Name</strong></label></td>
                            <td width = '60%'>{$full_name}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Address</strong></label></td>
                            <td width = '60%'>{$row['address']}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Socio Economic Status</strong></label></td>
                            <td width = '60%'>{$row['socio_economic_status']}</td>
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Date of Birth(yyyy-mm-dd)</strong></label></td>
                            <td width = '60%'>{$row['birth_date']}</td>
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Complete Name of Mother</strong></label></td>
                            <td width = '60%'>{$name_of_mother}</td>
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Contact No.</strong></label></td>
                            <td width = '60%'>{$contact}</td>
                         </tr>  
                         <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label>NEWBORN MEASUREMENT</label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Weight in grams:</strong></label></td>
                            <td width = '60%' id = 'birth_weight_{$patient_id}'>{$birth_weight}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Length/Height in cm:</strong></label></td>
                            <td width = '60%' id = 'birth_height_ {$patient_id}'>{$birth_height}</td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Sex:</strong></label></td>
                            <td width = '60%' id = 'sex_{$patient_id}'>{$sex}</td>
                        </tr>
                        <tr>
                            <td colspan = '2'>        
                                <button class='btn btn-outline-primary w-100 add_infant_assessment_btn mt-2'
                                 data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addInfantScreenModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Newborn Measurement</button>
                            </td>
                        </tr>
                         <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label>NEWBORN SCREENING</label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Referral Date:</strong></label></td>
                            <td width = '60%' id = 'referral_{$patient_id}'>{$referral}
                                <button class='btn btn-outline-primary w-100 add_referral_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addReferralModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Referral Date</button>
                            
                            </td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Date Done:</strong></label></td>
                            <td width = '60%' id = 'date_done_<?php echo $patient_id;?>'>{$date_done_infant}
                                <button class='btn btn-outline-primary w-100 add_date_done_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addDateDoneModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Date Done</button>
                            </td>
                        </tr>
                         <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label>CHILD PROTECTED AT BIRTH (CPAB)</label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Child Protected at Birth (CPAB):</strong></label></td>
                            <td width = '60%' id = 'referral_<?php echo $patient_id;?>'>
                                    <div><strong>TT Status (Tetanus Toxoid):</strong> {$cpab_tt_status} </div>
                                    <div><strong>Date:</strong> {$cpab_tt_date} </div>
                                <button class='btn btn-outline-primary w-100 add_ttstatus_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addTTStatusModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update TT Status</button>
                            
                            </td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Date Assessed:</strong></label></td>
                            <td width = '60%' id = 'cpab_tt_date_assessed_<?php echo $patient_id;?>'>
                                {$cpab_tt_date_assessed}
                                <button class='btn btn-outline-primary w-100 add_date_assessed_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addDateAssessedModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Date Assessed</button>
                            </td>
                        </tr>
                         <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label>EXCLUSIVELY BREASTFED</label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Child was Exclusively BreastFed</strong></label></td>
                            <td width = '60%' id = 'exclusive-info-{$patient_id}'>
                                    {$exlusive_feed_html}
                                <button class='btn btn-outline-primary w-100 add_exclusive_breastfeed mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addExlusiveFeedModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add Exclusively BreastFed</button>      
                            </td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Breastfeeding Status</strong></label></td>
                            <td width = '60%' id = 'breastfeed-info-{$patient_id}'>
                                    {$breastfeed_status_display}
                                    
                                    <button class='btn btn-outline-primary add_breastfeed_btn w-100 mt-2'
                                        data-patient-id= '{$patient_id}'
                                        data-bs-toggle = 'modal'
                                        data-bs-target = '#addBreastfeedModal'>
                                     <i class='bi bi-plus-lg text-white'></i>{$button_text}</button>
                            </td>   
                        </tr>
                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label>COMPLEMENTARY FEEDING</label></td>  
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Complementary Feeding</strong></label></td>
                            <td width = '60%' id = 'complementary-info-{$patient_id}'>
                                    {$complementary_feed_html}
                                <button class='btn btn-outline-primary w-100 add_complementary_feed mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addComplimentaryModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add Complementary BreastFed</button>      
                            </td>
                        </tr>

                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label>IMMUNIZATION</label></td>  
                         </tr>

                        <tr>
                            <td width = '40%'><label><strong>Bacillus Calmette–Guérin (BCG)</strong></label> </td>
                            <td width = '60%' id = 'bcg-info-{$patient_id}'>
                                    <div>{$bcg_status_display} </div>
                                    <div> {$bcg_date_display}</div>
                                    
                                    <button class='btn btn-outline-primary add_bcg_btn w-100 mt-2'
                                        data-patient-id= '{$patient_id}'
                                        data-bs-toggle = 'modal'
                                        data-bs-target = '#addBCGModal'>
                                     <i class='bi bi-plus-lg text-white'></i>{$button_text}</button>
                            </td>   
                        </tr>
                         <tr>
                            <td width = '40%'><label><strong>Hepatitis B1</strong></label></td>       
                            <td width = '60%'>
                                    <div id = 'hepaB_day_<?php echo $patient_id;'><strong>Selected:</strong> {$hepaB_day}</div>
                                    <div id = 'hepaB_date_<?php echo $patient_id;'><strong>Date:</strong> {$hepaB_date}</div>
                                <button class='btn btn-outline-primary w-100 add_hepa_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addHepaModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Hepa B1</button>      
                            </td>
                        </tr>

                         <tr>
                            <td width = '40%'><label><strong>Pentavalent</strong></label></td>
                            <td width = '60%' id = 'pentavalent-info-{$patient_id}'>
                                    {$pentavalent_html}
                                <button class='btn btn-outline-primary w-100 add_pentavalent_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addPentavalentModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add Pentavalent</button>      
                            </td>
                        </tr>
                      
                         <tr>
                            <td width = '40%'><label><strong>Oral Polio Vaccine (OPV)</strong></label></td>
                            <td width = '60%' id = 'opv-info-{$patient_id}'>
                                    {$opv_html}
                                <button class='btn btn-outline-primary w-100 add_opv_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addOpvModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add OPV</button>      
                            </td>
                        </tr>
                      

                        <tr>
                            <td width = '40%'><label><strong>Inactivated Polio Vaccine (IPV)</strong></label> </td>
                            <td width = '60%' id = 'ipv-info-{$patient_id}'>
                                    <div>{$ipv_check_display} </div>
                                    <div> {$ipv_date_display}</div>
                                    
                                    <button class='btn btn-outline-primary add_ipv_btn w-100 mt-2'
                                        data-patient-id= '{$patient_id}'
                                        data-bs-toggle = 'modal'
                                        data-bs-target = '#addIpvModal'>
                                     <i class='bi bi-plus-lg text-white'></i>{$button_text}</button>
                            </td>   
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Measles-Containing Vaccine (MCV)</strong></label></td>
                            <td width = '60%' id ='mcv-info-{$patient_id}'>
                                {$mcv_html}   
                                 <button class='btn btn-outline-primary w-100 add_mcv_btn mt-2'
                                    data-patient-id = '{$patient_id}'
                                    data-bs-toggle = 'modal'
                                    data-bs-target = '#addMcvModal'>
                                    <i class='bi bi-plus-lg text-white'></i>Add MCV </button>       
                            </td>
                        </tr>

                        <tr>
                            <td width = '40%'><label><strong>FIC</strong></label> </td>
                            <td width = '60%' id = 'fic-info-{$patient_id}'>
                                    <div>{$fic_check_display} </div>
                                    <div> {$fic_date_display}</div>
                                    
                                    <button class='btn btn-outline-primary add_fic_btn w-100 mt-2'
                                        data-patient-id= '{$patient_id}'
                                        data-bs-toggle = 'modal'
                                        data-bs-target = '#addFicModal'>
                                     <i class='bi bi-plus-lg text-white'></i>{$button_text}</button>
                            </td>   
                        </tr>
                         <tr>
                            <td width = '40%'><label><strong>Rota Virus Vaccine (RVV)</strong></label></td>
                            <td width = '60%' id = 'rvv-info-{$patient_id}'>
                                    {$rvv_html}
                                <button class='btn btn-outline-primary w-100 add_rvv_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addRvvModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add RVV</button>      
                            </td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Pneumococcal Conjugate Vaccine (PCV)</strong></label></td>
                            <td width = '60%' id ='pcv-info-{$patient_id}'>
                                    {$pcv_html}
                                <button class='btn btn-outline-primary w-100 add_pcv_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addPcvModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add PCV </button>      
                            </td>
                        </tr>

                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label>MICRONUTRIENT SUPPLEMENTATION</label></td>  
                        </tr>

                         <tr>
                            <td width = '40%'><label><strong>Vitamin A</strong></label></td>
                            <td width = '60%' id = 'vitamin-info-{$patient_id}'>
                                    {$vitamin_html}
                                <button class='btn btn-outline-primary w-100 add_vit_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addVitInfantModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add Vitamin</button>      
                            </td>
                        </tr>

                         <tr>
                            <td width = '40%'><label><strong>Iron</strong></label></td>
                            <td width = '60%' id = 'iron-infant-info-{$patient_id}'>
                                    {$iron_infant_html}
                                <button class='btn btn-outline-primary w-100 add_iron_infant_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addIronInfantModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add Iron</button>      
                            </td>
                        </tr>

                        <tr>
                            <td width = '40%'><label><strong>Micronutrient Powder (MNP)</strong></label></td>
                            <td width = '60%' id = 'mnp-info-{$patient_id}'>
                                    {$mnp_html}
                                <button class='btn btn-outline-primary w-100 add_mnp_btn mt-2'
                                data-patient-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addMnpModal'>
                                <i class='bi bi-plus-lg text-white'></i>Add MNP</button>      
                            </td>
                        </tr>
                        <tr>
                            <td width = '40%'><label><strong>Deworming</strong></label> </td>
                            <td width = '60%' id = 'deworming-info-{$patient_id}'>
                                    <div>{$deworming_check_display} </div>
                                    <div> {$deworming_date_display}</div>
                                    
                                    <button class='btn btn-outline-primary add_deworming_btn w-100 mt-2'
                                        data-patient-id= '{$patient_id}'
                                        data-bs-toggle = 'modal'
                                        data-bs-target = '#addDewormingInfantModal'>
                                     <i class='bi bi-plus-lg text-white'></i>{$button_text}</button>
                            </td>   
                        </tr>
                        
                       
                        
    
                    </table>
               </div>   

            ";
    }
    error_reporting(E_ALL);
    echo $output;
}
