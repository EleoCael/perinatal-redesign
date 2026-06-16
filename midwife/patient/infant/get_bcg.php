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
        return $array[$key] == 1 ? "BCG was received <span><i class='bi bi-check-circle-fill text-success'></i></span>" : "BCG was not received <span><i class='bi bi-x-circle-fill text-danger'></i></span> ";
    } else {
        return $default;
    }
}

$query = "SELECT bcg_check, bcg_date FROM bcg WHERE patient_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$bcg_row = $result->fetch_assoc();
$bcg_check_value = $bcg_row['bcg_check'] ?? null;
$bcg_check_display = displayCheckbox($bcg_row, 'bcg_check');
$bcg_date_display = insertValues($bcg_row, 'bcg_date');

if ($bcg_check_value === 1 || $bcg_check_value === 0) {
    $button_text = 'Update Status';
} else {
    $button_text = 'Set Status';
}

$bcg_html = '';
$bcg_html  .= "
            <div class = 'bcg-entry'>
                <div>
                    <p><strong>Status:</strong> {$bcg_check_display} </p>
                    <p><strong>Date:</strong> {$bcg_date_display} </p>
                 </div
            </div> 
             <button type='button' class='btn btn-outline-primary w-100 add_bcg_btn mt-2'
                    data-patient-id='{$patient_id}'
                    data-bcg-status = '{$bcg_check_value}'
                    data-bs-toggle='modal'
                    data-bs-target='#addBCGModal'>
                    <i class='bi bi-pencil-lg text-white'></i> {$button_text}
            </button>             
        ";

echo $bcg_html;
