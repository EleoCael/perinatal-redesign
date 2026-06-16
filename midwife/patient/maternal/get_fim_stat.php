<?php
require_once "../../../module/db.config.php";

$pregnancy_id = $_POST['pregnancy_id'] ?? NULL;

if (!$pregnancy_id) {
    echo "<div>Error: Missing Pregnancy ID. </div>";
    exit;
}
function displayCheckbox($array, $key, $default = 'N/A')
{
    if (isset($array[$key]) && $array[$key] !== null) {
        return $array[$key] == 1 ? "Fully Immunized (Yes) <span><i class='bi bi-check-circle-fill text-success'></i></span>" : "Not Fully Immunized (No) <span><i class='bi bi-x-circle-fill text-danger'></i></span>";
    } else {
        return $default;
    }
}

$query = "SELECT fim_status FROM fim_status_maternal WHERE pregnancy_id = ?";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $pregnancy_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$fim_row = $result->fetch_assoc();
$fim_status_value = $fim_row['fim_status'] ?? null;
$fim_status_display = displayCheckbox($fim_row, 'fim_status');

if ($fim_status_value === 1 || $fim_status_value === 0) {
    $button_text = 'Update Status';
} else {
    $button_text = 'Set Status';
}

$fim_html = '';
$fim_html  .= "
            <div class = 'fim-entry'>
                <div>
                    <p><strong>Status:</strong> {$fim_status_display} </p>
                 </div
            </div> 
             <button type='button' class='btn btn-outline-primary w-100 add_fim_btn mt-2'
                    data-preg-id='{$pregnancy_id}'
                    data-fim-status = '{$fim_status_value}'
                    data-bs-toggle='modal'
                    data-bs-target='#addFimModal'>
                    <i class='bi bi-pencil-lg text-white'></i> {$button_text}
            </button>             
        ";

echo $fim_html;
