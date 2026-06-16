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

// Updated query to include checkup_id
$query = "SELECT checkup_id, checkup_visit, post_checkup_date 
          FROM post_partum_checkup
          WHERE patient_id = ? ORDER BY post_checkup_date ASC";
$stmt_query = $conn->prepare($query);

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$post_checkup_html = '';
while ($post_checkup_row = $result->fetch_assoc()) {
    $checkup_id = $post_checkup_row['checkup_id'];
    $checkup_visit = insertValues($post_checkup_row, 'checkup_visit');
    $post_checkup_date = insertValues($post_checkup_row, 'post_checkup_date');

    $post_checkup_html .= "
        <div class='post-checkup-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Visits:</strong> {$checkup_visit}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$post_checkup_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_postpartum_checkup_btn' 
                    data-checkup-id='{$checkup_id}' 
                    data-patient-id='{$patient_id}'
                    data-checkup-visit='{$checkup_visit}'
                    data-post-checkup-date='{$post_checkup_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$post_checkup_html .= "
    <button type='button' class='btn btn-outline-primary w-100 add_postpartum_checkup_btn mt-2'
        data-patient-id='{$patient_id}'
        data-bs-toggle='modal'
        data-bs-target='#addPostCheckupModal'>
        <i class='bi bi-plus-lg text-white'></i> Add Check-up
    </button>
";

echo $post_checkup_html;

$stmt_query->close();
$conn->close();
?>