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
                <div class = 'table-responsive'>
                    <table class = 'table table-bordered'>
                        <tr>
                            <td class = 'table-dark text-center' colspan = '2'><label><strong>BASIC INFORMATION</strong></label></td>  
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
                            <td width = '40%'><label><strong>Age Bracket</strong></label></td>
                            <td width = '60%'>{$row['age_bracket']}</td>
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Age</strong></label></td>
                            <td width = '60%'>{$row['age']}</td>
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Email</strong></label></td>
                            <td width = '60%'>{$email}</td>
                         </tr>
                         <tr>
                            <td width = '40%'><label><strong>Contact No.</strong></label></td>
                            <td width = '60%'>{$contact}</td>
                         </tr>         
                    </table>
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
            <table class = 'table table-bordered'>
                <tr>
                    <td class= 'table-dark text-center' colspan = '2'><label?><strong>PREGNANCY LIST</strong></label></td>
                </tr>";

    if ($pregnancy_result->num_rows > 0) {
        $pregnancy_count = 1;
        while ($pregnancy = $pregnancy_result->fetch_assoc()) {
            $format_date = date('Y-m-d', strtotime($pregnancy['date_created']));
            $output .= "
                <tr>
                    <td><strong>Pregnancy #</strong>{$pregnancy_count}( Date Created: {$format_date}) </td>
                    <td>
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
                    <div>
                        <button id='addPregnancyBtn' class='btn btn-primary' data-patient-id='{$patient_id}'>
                            <i class='bi bi-person-plus me-2' style='color:white;'></i> Add New Pregnancy
                        </button>
                         <button id='addInfantBtn' class='btn btn-danger' data-mother-id='{$patient_id}'>
                            <i class='bi bi-person-plus me-2' style='color:white;'></i> Add Infant Records
                        </button>
                     </div>
            </div>";
 echo $output;
}
