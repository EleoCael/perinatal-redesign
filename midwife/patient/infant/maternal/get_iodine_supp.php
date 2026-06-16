<?php
require_once "../../../module/db.config.php";

$pregnancy_id = $_POST['pregnancy_id'];

if (!$pregnancy_id) {
    echo "<div>Error: Missing Pregnancy ID. </div>";
    exit;
}
function displayCheckbox($array, $key, $default = 'N/A')
{
    if (isset($array[$key]) && $array[$key] !== null) {
        return $array[$key] == 1 ? "Yes <span><i class='bi bi-check-circle-fill text-success'></i></span>" : "No <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
    } else {
        return $default;
    }
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
$query = "SELECT date_iodine, iodine_capsule_given FROM iodine_supplement WHERE pregnancy_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $pregnancy_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$iodine_row = $result->fetch_assoc();
$iodine_status = $iodine_row['iodine_capsule_given'] ?? null;
$date_iodine = $iodine_row['date_iodine'];
$iodine_status_display = displayCheckbox($iodine_row, 'iodine_capsule_given');
$iodine_date_display = insertValues($iodine_row, 'date_iodine');

if ($iodine_status === 1 || $iodine_status ===0) {
    $button_text = 'Update Status';
}else {
    $button_text = 'Set Status';
}

$iodine_html ='';

$iodine_html  .= "
            <div class = 'iodine-entry'>
                <p><strong>Status:</strong> {$iodine_status_display} </p>
                <p><strong>Date:</strong> {$iodine_date_display} </p>
            </div>
            <button type='button' class='btn btn-outline-primary w-100 add_iodine_btn mt-2'
                data-preg-id='{$pregnancy_id}'
                data-iodine-status = '{$iodine_status}'
                data-bs-toggle='modal'
                data-bs-target='#addIodineModal'>
                <i class='bi bi-plus-lg text-white'></i> {$button_text}
            </button> 
                  
        ";

echo $iodine_html;
