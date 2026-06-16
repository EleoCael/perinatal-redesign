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

// Updated query to include opv_id
$query = "SELECT opv_id, opv_type, opv_date 
          FROM opv
          WHERE patient_id = ? ORDER BY opv_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$opv_html = '';
while ($opv_row = $result->fetch_assoc()) {
    $opv_id = $opv_row['opv_id'];
    $opv_type = insertValues($opv_row, 'opv_type');
    $opv_date = insertValues($opv_row, 'opv_date');

    $opv_html .= "
        <div class='opv-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$opv_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$opv_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_opv_btn' 
                    data-opv-id='{$opv_id}' 
                    data-patient-id='{$patient_id}'
                    data-opv-type='{$opv_type}'
                    data-opv-date='{$opv_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$opv_html .= "
    <button class='btn btn-outline-primary w-100 add_opv_btn mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addOpvModal'>
        <i class='bi bi-plus-lg text-white'></i> Add OPV
    </button> 
";

echo $opv_html;

$stmt_query->close();
$conn->close();
?>