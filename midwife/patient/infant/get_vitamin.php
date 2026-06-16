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

// Updated query to include vitamin_a_infant_id
$query = "SELECT vitamin_a_infant_id, vitamin_type, vitamin_date 
          FROM vitamin_a_infant
          WHERE patient_id = ? ORDER BY vitamin_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$vitamin_html = '';
while ($vitamin_row = $result->fetch_assoc()) {
    $vitamin_id = $vitamin_row['vitamin_a_infant_id'];
    $vitamin_type = insertValues($vitamin_row, 'vitamin_type');
    $vitamin_date = insertValues($vitamin_row, 'vitamin_date');

    $vitamin_html .= "
        <div class='vitamin-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$vitamin_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$vitamin_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_vitamin_btn' 
                    data-vitamin-id='{$vitamin_id}' 
                    data-patient-id='{$patient_id}'
                    data-vitamin-type='{$vitamin_type}'
                    data-vitamin-date='{$vitamin_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$vitamin_html .= "
    <button class='btn btn-outline-primary w-100 add_vit_btn mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addVitInfantModal'>
        <i class='bi bi-plus-lg text-white'></i> Add Vitamin A
    </button> 
";

echo $vitamin_html;

$stmt_query->close();
$conn->close();
?>