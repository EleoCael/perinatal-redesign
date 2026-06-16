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

// Updated query to include mnp_id
$query = "SELECT mnp_id, mnp_type, mnp_date 
          FROM mnp
          WHERE patient_id = ? ORDER BY mnp_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$mnp_html = '';
while ($mnp_row = $result->fetch_assoc()) {
    $mnp_id = $mnp_row['mnp_id'];
    $mnp_type = insertValues($mnp_row, 'mnp_type');
    $mnp_date = insertValues($mnp_row, 'mnp_date');

    $mnp_html .= "
        <div class='mnp-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$mnp_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$mnp_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_mnp_btn' 
                    data-mnp-id='{$mnp_id}' 
                    data-patient-id='{$patient_id}'
                    data-mnp-type='{$mnp_type}'
                    data-mnp-date='{$mnp_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$mnp_html .= "
    <button class='btn btn-outline-primary w-100 add_mnp_btn mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addMnpModal'>
        <i class='bi bi-plus-lg text-white'></i> Add MNP
    </button> 
";

echo $mnp_html;

$stmt_query->close();
$conn->close();
?>