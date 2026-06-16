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

// Updated query to include pcv_id
$query = "SELECT pcv_id, pcv_type, pcv_date 
          FROM pcv
          WHERE patient_id = ? ORDER BY pcv_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$pcv_html = '';
while ($pcv_row = $result->fetch_assoc()) {
    $pcv_id = $pcv_row['pcv_id'];
    $pcv_type = insertValues($pcv_row, 'pcv_type');
    $pcv_date = insertValues($pcv_row, 'pcv_date');

    $pcv_html .= "
        <div class='pcv-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$pcv_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$pcv_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_pcv_btn' 
                    data-pcv-id='{$pcv_id}' 
                    data-patient-id='{$patient_id}'
                    data-pcv-type='{$pcv_type}'
                    data-pcv-date='{$pcv_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$pcv_html .= "
    <button class='btn btn-outline-primary w-100 add_pcv_btn mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addPcvModal'>
        <i class='bi bi-plus-lg text-white'></i> Add PCV
    </button> 
";

echo $pcv_html;

$stmt_query->close();
$conn->close();
?>