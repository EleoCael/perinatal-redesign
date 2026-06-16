<?php
require_once "../../../module/db.config.php";

$patient_id = $_POST['patient_id'] ?? NULL;

if (!$patient_id) {
    echo "<div>Error: Missing Patient ID. </div>";
    exit;
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
function displayCheckbox($array, $key, $default = 'N/A')
{
    if (isset($array[$key]) && $array[$key] !== null) {
        return $array[$key] == 1 ? "Child was Dewormed (Yes)<span><i class='bi bi-check-circle-fill text-success'></i></span>" : "Child was not Dewormed (No) <span><i class='bi bi-x-circle-fill text-danger'></i></span> ";
    } else {
        return $default;
    }
}

$query = "SELECT deworming_check, deworming_date FROM deworming_infant WHERE patient_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$deworming_row = $result->fetch_assoc();
$deworming_check_value = $deworming_row['deworming_check'] ?? null;
$deworming_check_display = displayCheckbox($deworming_row, 'deworming_check');
$deworming_date_display = insertValues($deworming_row, 'deworming_date');

if ($deworming_check_value === 1 || $deworming_check_value === 0) {
    $button_text = 'Update Status';
} else {
    $button_text = 'Set Status';
}

$deworming_infant_html = '';
$deworming_infant_html  .= "
            <div class = 'deworming-infant-entry'>
                <div>
                    <p><strong>Status:</strong> {$deworming_check_display} </p>
                    <p><strong>Date:</strong> {$deworming_date_display} </p>
                 </div
            </div> 
             <button type='button' class='btn btn-outline-primary w-100 add_deworming_btn mt-2'
                    data-patient-id='{$patient_id}'
                    data-deworming-status = '{$deworming_check_value}'
                    data-bs-toggle='modal'
                    data-bs-target='#addDewormingInfantModal'>
                    <i class='bi bi-pencil-lg text-white'></i> {$button_text}
            </button>             
        ";

echo $deworming_infant_html;
