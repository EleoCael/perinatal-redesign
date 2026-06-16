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
        return $array[$key] == 1 ? "Fully Immunized Child <span><i class='bi bi-check-circle-fill text-success'></i></span>" : "Not Fully Immunized Child <span><i class='bi bi-x-circle-fill text-danger'></i></span> ";
    } else {
        return $default;
    }
}

$query = "SELECT fic_check, fic_date FROM fic WHERE patient_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$fic_row = $result->fetch_assoc();
$fic_check_value = $fic_row['fic_check'] ?? null;
$fic_check_display = displayCheckbox($fic_row, 'fic_check');
$fic_date_display = insertValues($fic_row, 'fic_date');

if ($fic_check_value === 1 || $fic_check_value === 0) {
    $button_text = 'Update Status';
} else {
    $button_text = 'Set Status';
}

$fic_html = '';
$fic_html  .= "
            <div class = 'fic-entry'>
                <div>
                    <p><strong>Status:</strong> {$fic_check_display} </p>
                    <p><strong>Date:</strong> {$fic_date_display} </p>
                 </div
            </div> 
             <button type='button' class='btn btn-outline-primary add_fic_btn w-100 mt-2'
                    data-patient-id='{$patient_id}'
                    data-fic-status = '{$fic_check_value}'
                    data-bs-toggle='modal'
                    data-bs-target='#addFicModal'>
                    <i class='bi bi-pencil-lg text-white'></i> {$button_text}
            </button>             
        ";

echo $fic_html;
