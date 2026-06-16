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

// Updated query to include pentavalent_id
$query = "SELECT pentavalent_id, pentavalent_type, pentavalent_date 
          FROM pentavalent
          WHERE patient_id = ? ORDER BY pentavalent_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$pentavalent_html = '';
while ($pentavalent_row = $result->fetch_assoc()) {
    $pentavalent_id = $pentavalent_row['pentavalent_id'];
    $pentavalent_type = insertValues($pentavalent_row, 'pentavalent_type');
    $pentavalent_date = insertValues($pentavalent_row, 'pentavalent_date');

    $pentavalent_html .= "
        <div class='pentavalent-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$pentavalent_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$pentavalent_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_pentavalent_btn' 
                    data-pentavalent-id='{$pentavalent_id}' 
                    data-patient-id='{$patient_id}'
                    data-pentavalent-type='{$pentavalent_type}'
                    data-pentavalent-date='{$pentavalent_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$pentavalent_html .= "
    <button class='btn btn-outline-primary w-100 add_pentavalent_btn mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addPentavalentModal'>
        <i class='bi bi-plus-lg text-white'></i> Add Pentavalent
    </button> 
";

echo $pentavalent_html;

$stmt_query->close();
$conn->close();
?>