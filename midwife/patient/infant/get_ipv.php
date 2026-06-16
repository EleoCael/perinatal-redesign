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
        return $array[$key] == 1 ? "IPV was received <span><i class='bi bi-check-circle-fill text-success'></i></span>" : "IPV was not received <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
    } else {
        return $default;
    }
}

$query = "SELECT ipv_1, ipv_date FROM ipv WHERE patient_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$ipv_row = $result->fetch_assoc();
$ipv_check_value = $ipv_row['ipv_1'] ?? null;
$ipv_check_display = displayCheckbox($ipv_row, 'ipv_1');
$ipv_date_display = insertValues($ipv_row, 'ipv_date');

if ($ipv_check_value === 1 || $ipv_check_value === 0) {
    $button_text = 'Update Status';
} else {
    $button_text = 'Set Status';
}

$ipv_html = '';
$ipv_html  .= "
            <div class = 'ipv-entry'>
                <div>
                    <p><strong>Status:</strong> {$ipv_check_display} </p>
                    <p><strong>Date:</strong> {$ipv_date_display} </p>
                 </div
            </div> 
             <button type='button' class='btn btn-outline-primary w-100 add_ipv_btn mt-2'
                    data-patient-id='{$patient_id}'
                    data-ipv-status = '{$ipv_check_value}'
                    data-bs-toggle='modal'
                    data-bs-target='#addIpvModal'>
                    <i class='bi bi-pencil-lg text-white'></i> {$button_text}
            </button>             
        ";

echo $ipv_html;
