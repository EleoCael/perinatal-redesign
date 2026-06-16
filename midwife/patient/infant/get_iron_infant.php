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

// Updated query to include iron_infant_id
$query = "SELECT iron_infant_id, iron_type, iron_date 
          FROM iron_infant
          WHERE patient_id = ? ORDER BY iron_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$iron_infant_html = '';
while ($iron_infant_row = $result->fetch_assoc()) {
    $iron_id = $iron_infant_row['iron_infant_id'];
    $iron_type = insertValues($iron_infant_row, 'iron_type');
    $iron_date = insertValues($iron_infant_row, 'iron_date');

    $iron_infant_html .= "
        <div class='iron-infant-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$iron_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$iron_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_iron_infant_btn' 
                    data-iron-id='{$iron_id}' 
                    data-patient-id='{$patient_id}'
                    data-iron-type='{$iron_type}'
                    data-iron-date='{$iron_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$iron_infant_html .= "
    <button class='btn btn-outline-primary w-100 add_iron_infant_btn mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addIronInfantModal'>
        <i class='bi bi-plus-lg text-white'></i> Add Iron
    </button> 
";

echo $iron_infant_html;

$stmt_query->close();
$conn->close();
?>