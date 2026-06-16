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

// Updated query to include rvv_id
$query = "SELECT rvv_id, rvv_type, rvv_date 
          FROM rota_virus_vaccine
          WHERE patient_id = ? ORDER BY rvv_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$rvv_html = '';
while ($rvv_row = $result->fetch_assoc()) {
    $rvv_id = $rvv_row['rvv_id'];
    $rvv_type = insertValues($rvv_row, 'rvv_type');
    $rvv_date = insertValues($rvv_row, 'rvv_date');

    $rvv_html .= "
        <div class='rvv-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$rvv_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$rvv_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_rvv_btn' 
                    data-rvv-id='{$rvv_id}' 
                    data-patient-id='{$patient_id}'
                    data-rvv-type='{$rvv_type}'
                    data-rvv-date='{$rvv_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$rvv_html .= "
    <button class='btn btn-outline-primary w-100 add_rvv_btn mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addRvvModal'>
        <i class='bi bi-plus-lg text-white'></i> Add RVV
    </button> 
";

echo $rvv_html;

$stmt_query->close();
$conn->close();
?>