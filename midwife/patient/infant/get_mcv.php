<?php
require_once "../../../module/db.config.php";

$patient_id = $_POST['patient_id'];

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

// Updated query to include mcv_id
$query = "SELECT mcv_id, mcv_type, mcv_date 
          FROM mcv
          WHERE patient_id = ? ORDER BY mcv_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$mcv_html = '';
while ($mcv_row = $result->fetch_assoc()) {
    $mcv_id = $mcv_row['mcv_id'];
    $mcv_type = insertValues($mcv_row, 'mcv_type');
    $mcv_date = insertValues($mcv_row, 'mcv_date');

    $mcv_html .= "
        <div class='mcv-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$mcv_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$mcv_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_mcv_btn' 
                    data-mcv-id='{$mcv_id}' 
                    data-patient-id='{$patient_id}'
                    data-mcv-type='{$mcv_type}'
                    data-mcv-date='{$mcv_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$mcv_html .= "
    <button class='btn btn-outline-primary w-100 add_mcv_btn mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addMcvModal'>
        <i class='bi bi-plus-lg text-white'></i> Add MCV
    </button> 
";

echo $mcv_html;

$stmt_query->close();
$conn->close();
?>