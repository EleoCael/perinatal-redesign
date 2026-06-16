<?php
require_once "../../../module/db.config.php";

$patient_id = $_POST['patient_id'] ?? NULL;

if (!$patient_id) {
    echo "<div>Error: Missing Patient ID. </div>";
    exit;
}
function displayCheckbox($array, $key, $default = 'N/A')
{
    if (isset($array[$key]) && $array[$key] !== null) {
        return $array[$key] == 1 ? "Exclusively BreastFeeding (6th Month) <span><i class='bi bi-check-circle-fill text-success'></i></span>" : "Not Exclusively BreastFeeding (6th Month) <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
    } else {
        return $default;
    }
}

$query = "SELECT is_still_breastfeed FROM 6th_month_check WHERE patient_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$breastfeed_row = $result->fetch_assoc();
$breastfeed_status_value = $breastfeed_row['is_still_breastfeed'] ?? null;
$breastfeed_status_display = displayCheckbox($breastfeed_row, 'is_still_breastfeed');

if ($breastfeed_status_value === 1 || $breastfeed_status_value === 0) {
    $button_text = 'Update Status';
} else {
    $button_text = 'Set Status';
}

$breastfeed_html = '';
$breastfeed_html  .= "
            <div class = 'breastfeed-entry'>
                <div>
                    <p><strong>Status:</strong> {$breastfeed_status_display} </p>
                 </div
            </div> 
             <button type='button' class='btn btn-outline-primary add_breastfeed_btn w-100 mt-2'
                    data-patient-id='{$patient_id}'
                    data-breastfeed-status = '{$breastfeed_status_value}'
                    data-bs-toggle='modal'
                    data-bs-target='#addBreastfeedModal'>
                    <i class='bi bi-pencil-lg text-white'></i> {$button_text}
            </button>             
        ";

echo $breastfeed_html;
