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
$query = "SELECT vitamin_a_date, vitamin_a FROM post_vitamin WHERE pregnancy_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $pregnancy_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$vitamin_row = $result->fetch_assoc();
$vitamin_status = $vitamin_row['vitamin_a'] ?? null;
$vitamin_a_date = $vitamin_row['vitamin_a_date'];
$vitamin_a_display = displayCheckbox($vitamin_row, 'vitamin_a');
$vitamin_date_display = insertValues($vitamin_row, 'vitamin_a_date');

if ($vitamin_status === 1 || $vitamin_status ===0) {
    $button_text = 'Update Status';
}else {
    $button_text = 'Set Status';
}

$vitamin_html = '';
$vitamin_html  .= "
            <div>
                <p><strong>Is Vitamin A given?:</strong> {$vitamin_a_display} </p>
                <p><strong>Date:</strong> {$vitamin_date_display} </p>
            </div>
            <button type='button' class='btn btn-outline-primary add_vitamin_btn mt-2'
                data-preg-id='{$pregnancy_id}'
                data-vitamin-status = '{$vitamin_status}'
                data-bs-toggle='modal'
                data-bs-target='#addVitaminModal'>
                <i class='bi bi-plus-lg text-white'></i> {$button_text}
            </button> 
                  
        ";

echo $vitamin_html;
