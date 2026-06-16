<?php
require_once "../../../module/db.config.php";

$pregnancy_id = $_POST['pregnancy_id'];
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

$query = "SELECT maternal_immunization_id, immunization_type, immunization_date FROM maternal_immunization
          WHERE pregnancy_id = ? ORDER BY immunization_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $pregnancy_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$immunization_html = '';
while ($immunization_row = $result->fetch_assoc()) {
    $maternal_immunization_id =$immunization_row['maternal_immunization_id'];
    $immunization_type = insertValues($immunization_row, 'immunization_type');
    $immunization_date = insertValues($immunization_row, 'immunization_date');

    $immunization_html .= "

        <div class='immunization-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Type:</strong> {$immunization_type}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$immunization_date}</p>
            </div>
             <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_immunization_btn' 
                    data-immunization-id='{$maternal_immunization_id}' 
                    data-preg-id='{$pregnancy_id}'
                    data-immunization-type='{$immunization_type}'
                    data-immunization-date='{$immunization_date}'
                    title='Edit this immunization'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>        
        ";
}
$immunization_html .= "
    
    <button type='button' class='btn btn-outline-primary w-100 add_immunization_btn mt-2'
        data-preg-id='{$pregnancy_id}'
        data-bs-toggle='modal'
        data-bs-target='#addImmunizationModal'>
        <i class='bi bi-plus-lg w-100 text-white'></i> Add Immunization
    </button>
";
echo $immunization_html;

$stmt_query->close();
$conn->close();