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

    if ($patient_result->num_rows == 0) {
        die("Patient not found or access denied");
    }

    while ($row = $patient_result->fetch_assoc()) {
        $full_name = $row['last_name'] . ", " . $row['first_name'];
        if (!empty($row['middle_name'])) {
            $full_name .= " " . $row['middle_name'];
        }
        //patient table

        $family_serial_no = insertValues($row, 'family_serial_number');
        $email = insertValues($row, 'email');
        $contact = insertValues($row, 'contact_number');

        $output .= "
        <div class='container-fluid'>
            <div class='row'>
                <div class='panel panel-default shadow-lg rounded'>
                    <div class='panel-heading'>
                        <div class='panel-body'>
                            <div class='mt-3'>
                                <button
                                    type='button'
                                    onclick='loadPage(`patient/maternal/view_maternal_patient.php`); return false;'
                                    class='btn btn-outline-secondary btn-sm'>
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

            ";
    }

    //pregnancy list
    $pregnancy_query = "SELECT p.* FROM pregnancy p INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                        WHERE p.patient_id = ? AND pt.health_center_id = ?";
    $stmt_pregnancy = $conn->prepare($pregnancy_query);
    $stmt_pregnancy->bind_param("ii", $patient_id, $health_center_id);
    $stmt_pregnancy->execute();
    $pregnancy_result = $stmt_pregnancy->get_result();

    $output .= "
        <div class = 'table-responsive mt-3' > 
            <table class = 'table table-bordered table-sm table-striped align-middle'>
                <tr>
                    <td class= 'table-dark text-center' colspan = '2'><strong>PREGNANCY LIST</strong></td>
                </tr>";

    if ($pregnancy_result->num_rows > 0) {
        $pregnancy_count = 1;
        while ($pregnancy = $pregnancy_result->fetch_assoc()) {
            $format_date = date('Y-m-d', strtotime($pregnancy['date_created']));
            $output .= "
                <tr>
                    <td>
                        <span class='badge bg-secondary me-2'>#{$pregnancy_count}</span>
                        <span class='text-muted'>Date Created: {$format_date}</span>
                    </td>
                    <td class='text-end'>
                        <button class = 'btn btn-sm btn-outline-primary view_preg_btn'
                                data-preg-id ='{$pregnancy['pregnancy_id']}'
                                data-preg-num = '{$pregnancy_count}'
                                data-date-created = '{$format_date}'
                                data-bs-target= '#viewPregnancyRecord'>
                            View Details
                        </button>
                         <button class = 'btn btn-sm btn-outline-danger delete_preg_btn'
                                data-preg-id ='{$pregnancy['pregnancy_id']}'    
                                data-bs-target= '#viewPregnancyRecord'>
                            Delete
                        </button>
                    </td>
                
                </tr>
            ";
            $pregnancy_count++;
        }
    } else {
        $output .= "
            <tr>
                <td colspan = '2' class = 'text-center'>No Pregnancy Record Found</td>
            </tr>
        ";
    }
    $output .= "</table>
                    <div class='mt-3'>
                        <button id='addPregnancyBtn' class='btn btn-primary' data-patient-id='{$patient_id}'>
                            <i class='bi bi-person-plus me-2' style='color:white;'></i> Add New Pregnancy
                        </button>
                         <button id='addInfantBtn' class='btn btn-danger' data-mother-id='{$patient_id}'>
                            <i class='bi bi-person-plus me-2' style='color:white;'></i> Add Infant Records
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>";
 echo $output;
}