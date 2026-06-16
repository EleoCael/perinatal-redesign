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

// Updated query to include checkup_id
$query = "SELECT checkup_id, trimester, checkup_date FROM prenatal_checkup
          WHERE pregnancy_id = ? ORDER BY checkup_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $pregnancy_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$prenatal_html = '';
while ($prenatal_row = $result->fetch_assoc()) {
    $checkup_id = $prenatal_row['checkup_id'];
    $trimester = insertValues($prenatal_row, 'trimester');
    $checkup_date = insertValues($prenatal_row, 'checkup_date');

    $prenatal_html .= "
        <div class='checkup-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Trimester:</strong> {$trimester}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$checkup_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_checkup_btn' 
                    data-checkup-id='{$checkup_id}' 
                    data-preg-id='{$pregnancy_id}'
                    data-trimester='{$trimester}'
                    data-checkup-date='{$checkup_date}'
                    title='Edit this checkup'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$prenatal_html .= "
    <button type='button' class='btn btn-outline-primary w-100 add_checkup_btn mt-2'
        data-preg-id='{$pregnancy_id}'
        data-bs-toggle='modal'
        data-bs-target='#addCheckupModal'>
        <i class='bi bi-plus-lg w-100 text-white'></i> Add Check-up
    </button>
";

echo $prenatal_html;

$stmt_query->close();
$conn->close();
?>