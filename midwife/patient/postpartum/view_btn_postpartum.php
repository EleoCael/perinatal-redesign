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

if (isset($_GET["patient_id"])) {
    $patient_id = $_GET['patient_id'];
    $health_center_id = $_SESSION['health_center_id'];
    $output = '';

    //patient table
    $view_query = "SELECT * FROM patient WHERE patient_id = ? AND health_center_id = ?";
    $stmt_view = $conn->prepare($view_query);
    $stmt_view->bind_param("ii", $patient_id, $health_center_id);
    $stmt_view->execute();
    $patient_result = $stmt_view->get_result();

    while ($row = $patient_result->fetch_assoc()) {
        $full_name = $row['last_name'] . ", " . $row['first_name'];
        if (!empty($row['middle_name'])) {
            $full_name .= " " . $row['middle_name'];
        }
        //patient table

        $family_serial_no = insertValues($row, 'family_serial_number');
        $email = insertValues($row, 'email');
        $contact = insertValues($row, 'contact_number');

     //postpartum checkup table
    if ($row && $row['patient_id']) {
        $postpartum_query = "SELECT ppc.* FROM post_partum_checkup ppc
                                INNER JOIN patient p ON ppc.patient_id = p.patient_id 
                                WHERE ppc.patient_id = ? AND p.health_center_id = ? 
                                ORDER BY ppc.post_checkup_date";
        $stmt_postpartum = $conn->prepare($postpartum_query);
        $stmt_postpartum->bind_param("ii", $patient_id, $health_center_id);
        $stmt_postpartum->execute();
        $result_postpartum = $stmt_postpartum->get_result();
        $postpartum = $result_postpartum->fetch_all(MYSQLI_ASSOC);
    } else {
        $postpartum = [];
    }
    $post_checkup_html = '';

    foreach ($postpartum as $postpartum_checkup) {
        $checkup_id = $postpartum_checkup['checkup_id'];
        $checkup_visit = insertValues($postpartum_checkup, 'checkup_visit');
        $post_checkup_date = insertValues($postpartum_checkup, 'post_checkup_date');

        $post_checkup_html .= "
            <div class='post-checkup-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Visits:</strong> {$checkup_visit}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$post_checkup_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_postpartum_checkup_btn' 
                    data-checkup-id='{$checkup_id}' 
                    data-patient-id='{$patient_id}'
                    data-checkup-visit='{$checkup_visit}'
                    data-post-checkup-date='{$post_checkup_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>  ";
    } 

    $first_post_checkup = reset($postpartum); // since array yung $prenatal, purpose ng reset ay para kunin yung 1st element
    if ($first_post_checkup === false) {
        $first_post_checkup = [];
    }
     //postpartum checkup table

     //postpartum other details 
    $post_query = "SELECT ppc.post_delivery_date, ppc.post_delivery_time, ppc.breastfeeding_date, ppc.breastfeeding_time 
                      FROM post_partum_checkup ppc 
                      INNER JOIN patient p ON ppc.patient_id = p.patient_id 
                      WHERE ppc.patient_id = ? AND p.health_center_id = ?";
    $stmt_post_query = $conn->prepare($post_query);
    $stmt_post_query->bind_param("ii", $patient_id, $health_center_id);
    $stmt_post_query->execute();
    $post_result = $stmt_post_query->get_result();
    $post_info = $post_result->fetch_assoc() ?? [];

    $post_delivery_date = insertValues($post_info, 'post_delivery_date');
    $post_delivery_time = insertValues($post_info, 'post_delivery_time');
    $breastfeeding_date = insertValues($post_info, 'breastfeeding_date');
    $breastfeeding_time = insertValues($post_info, 'breastfeeding_time');
    //postpartum other details 

   //postpartum supplement table
if ($row && $row['patient_id']) {
    $postpartum_supp_query = "SELECT pps.* FROM post_partum_supp pps 
                                 INNER JOIN patient p ON pps.patient_id = p.patient_id 
                                 WHERE pps.patient_id = ? AND p.health_center_id = ?";
    $stmt_postpartum_supp = $conn->prepare($postpartum_supp_query);
    $stmt_postpartum_supp->bind_param("ii", $patient_id, $health_center_id);
    $stmt_postpartum_supp->execute();
    $result_postpartum_supp = $stmt_postpartum_supp->get_result();
    $postpartum_supp = $result_postpartum_supp->fetch_all(MYSQLI_ASSOC);
} else {
    $postpartum_supp = [];
}

$post_iron_html = '';
foreach ($postpartum_supp as $post_iron_supp) {
    $post_supp_id = $post_iron_supp['post_supp_id'];
    $patient_id_row = $post_iron_supp['patient_id']; // Use patient_id from row
    $iron_folic_month_given = insertValues($post_iron_supp, 'iron_folic_month_given');
    $iron_folic_date_given = insertValues($post_iron_supp, 'iron_folic_date_given');
    $tablets_given = insertValues($post_iron_supp, 'tablets_given');

    $post_iron_html .= "
        <div class='post-iron-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
        <div>
            <p style='margin: 0;'><strong>Month Given:</strong> {$iron_folic_month_given}</p>
            <p style='margin: 0;'><strong>Date:</strong> {$iron_folic_date_given}</p>
            <p style='margin: 0;'><strong>Tablets Given:</strong> {$tablets_given}</p>
        </div>
         <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_postpartum_iron_btn' 
                    data-post-supp-id='{$post_supp_id}' 
                    data-patient-id='{$patient_id_row}'
                    data-iron-folic-month-given='{$iron_folic_month_given}'
                    data-iron-folic-date-given='{$iron_folic_date_given}'
                    data-tablets-given='{$tablets_given}'
                    title='Edit this supplement'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
    </div>
    ";
}

    $remarks = insertValues($postpartum_supp, 'remarks');

    //vitamin A
    $vitamin_query = "SELECT pv.vitamin_a, pv.vitamin_a_date 
                         FROM post_vitamin pv 
                         INNER JOIN patient p ON pv.patient_id = p.patient_id 
                         WHERE pv.patient_id = ? AND p.health_center_id = ?";
    $stmt_vitamin_query = $conn->prepare($vitamin_query);
    $stmt_vitamin_query->bind_param("ii", $patient_id, $health_center_id);
    $stmt_vitamin_query->execute();
    $result_vitamin = $stmt_vitamin_query->get_result();
    $vitamin_row = $result_vitamin->fetch_assoc();
    $vitamin_status = $vitamin_row['vitamin_a'] ?? null;

    $vitamin_date_display = "";

    if ($vitamin_status === 1) {
        $vitamin_a_date = insertValues($vitamin_row, 'vitamin_a_date');
        $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> Yes  <span><i class='bi bi-check-circle-fill text-success'></i></span>";
        $vitamin_date_display = "<strong>Date:</strong> {$vitamin_a_date}";
        $button_text = "Update Status";
    } elseif ($vitamin_status === 0) {
        $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> No <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
        $vitamin_date_display = "<strong>Date:</strong> N/A";
        $button_text = "Update Status";
    } else {
        $vitamin_a_display = "<strong>Is Vitamin A given?:</strong> N/A";
        $vitamin_date_display = "<strong>Date:</strong> N/A";
        $button_text = "Set Status";
    }
    //vitamin A
        $output .= " 
        <div class='container-fluid'>
    <div class='row'>
        <div class='panel panel-default shadow-lg rounded'>
            <div class='panel-heading'>
                <div class='panel-body'>
                    <div class='mt-3'>
                        <button
                            type='button'
                            onclick='loadPage(`patient/postpartum/view_postpartum_record.php`); return false;'
                            class='btn btn-outline-secondary btn-sm'
                        >
                            Back to Patients
                        </button>
                   </div>
                    <div class='card shadow-sm rounded mb-3'>
                        <div class='card-header bg-dark text-white text-center'>
                            <strong>BASIC INFORMATION</strong>
                        </div>
                        <div class='card-body'>
                            <div class='row row-cols-1 row-cols-md-2 g-3'>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>No.</div>
                                    <div class='fw-semibold'>{$row['patient_id']}</div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Full Name</div>
                                    <div class='fw-semibold'>{$full_name}</div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Family Serial No.</div>
                                    <div class='fw-semibold'>{$family_serial_no}</div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Date of Registration <span class='fw-normal'>(yyyy-mm-dd)</span></div>
                                    <div class='fw-semibold'>{$row['date_of_registration']}</div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Date of Birth <span class='fw-normal'>(yyyy-mm-dd)</span></div>
                                    <div class='fw-semibold'>{$row['birth_date']}</div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Age / Age Bracket</div>
                                    <div class='fw-semibold'>{$row['age']} &nbsp;<span class='text-muted'>({$row['age_bracket']})</span></div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Socio Economic Status</div>
                                    <div class='fw-semibold'>{$row['socio_economic_status']}</div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Address</div>
                                    <div class='fw-semibold'>{$row['address']}</div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Email</div>
                                    <div class='fw-semibold'>{$email}</div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Contact No.</div>
                                    <div class='fw-semibold'>{$contact}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='card shadow-sm rounded mb-3'>
                        <div class='card-header bg-dark text-white text-center'>
                            <strong>POSTPARTUM CARE</strong>
                        </div>
                        <div class='card-body'>
                            <div class='mb-3'>
                                <div class='text-muted small mb-1'>Postpartum Check-Ups</div>
                                <div id='post-checkup-info-{$patient_id}'>
                                    {$post_checkup_html}
                                    <button type='button' class='btn btn-outline-primary w-100 add_postpartum_checkup_btn mt-2'
                                        data-patient-id='{$patient_id}'
                                        data-bs-toggle='modal'
                                        data-bs-target='#addPostCheckupModal'>
                                        <i class='bi bi-plus-lg text-white'></i> Add Check-up
                                    </button>
                                </div>
                            </div>
                            <div class='row row-cols-1 row-cols-md-2 g-3 mb-3'>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Date and Time of Delivery</div>
                                    <div class='fw-semibold'>
                                        <div>Date (yyyy-mm-dd): <span id='post_delivery_date_<?php echo $patient_id;?>'>{$post_delivery_date}</span></div>
                                        <div>Time: <span id='post_delivery_time_<?php echo $patient_id;?>'>{$post_delivery_time}</span></div>
                                    </div>
                                </div>
                                <div class='col'>
                                    <div class='text-muted small mb-1'>Date and Time Initiated Breastfeeding</div>
                                    <div class='fw-semibold'>
                                        <div>Date Breastfed (yyyy-mm-dd): <span id='breastfeeding_date_<?php echo $patient_id;?>'>{$breastfeeding_date}</span></div>
                                        <div>Time Breastfed: <span id='breastfeeding_time_<?php echo $patient_id;?>'>{$breastfeeding_time}</span></div>
                                    </div>
                                </div>
                            </div>
                            <button class='btn btn-outline-primary w-100 add_postpartum_details_btn'
                                data-preg-id = '{$patient_id}'
                                data-bs-toggle = 'modal'
                                data-bs-target = '#addPostpartumDetailsModal'>
                                <i class='bi bi-plus-lg text-white'></i>Update Postpartum Details</button>
                        </div>
                    </div>

                    <div class='card shadow-sm rounded mb-3'>
                        <div class='card-header bg-dark text-white text-center'>
                            <strong>MICRONUTRIENT SUPPLEMENTATION (postpartum)</strong>
                        </div>
                        <div class='card-body'>
                            <div class='mb-3'>
                                <div class='text-muted small mb-1'>Iron w/Folic Acid</div>
                                <div id='post-iron-info-{$patient_id}'>
                                    {$post_iron_html}
                                    <button class='btn btn-outline-primary w-100 add_postpartum_iron_btn mt-2'
                                            data-preg-id = '{$patient_id}'
                                            data-bs-toggle = 'modal'
                                            data-bs-target = '#addPostpartumIronModal'>
                                         <i class='bi bi-plus-lg text-white'></i>Update Iron Supplement
                                         </button>
                                </div>
                            </div>
                            <div>
                                <div class='text-muted small mb-1'>Vitamin A</div>
                                <div id='vitamin-info-{$patient_id}'>
                                    <div>{$vitamin_a_display}</div>
                                    <div>{$vitamin_date_display}</div>
                                    <button type='button' class='btn btn-outline-primary w-100 add_post_vitamin_btn mt-2'
                                        data-patient-id='{$patient_id}'
                                        data-vitamin-status = '{$vitamin_status}'
                                        data-bs-toggle='modal'
                                        data-bs-target='#addPostVitaminModal'>
                                        <i class='bi bi-plus-lg text-white'></i> {$button_text}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
               </div>
               </div>
               </div>
            ";
    }
 echo $output;
}