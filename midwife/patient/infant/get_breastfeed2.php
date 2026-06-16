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

// Updated query to include complementary_feeding_id
$query = "SELECT complementary_feeding_id, complementary_month_check, complementary_month_date 
          FROM infant_complementary_feeding
          WHERE patient_id = ? ORDER BY complementary_month_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$complementary_feed_html = '';
while ($complementary_feed_row = $result->fetch_assoc()) {
    $complementary_feeding_id = $complementary_feed_row['complementary_feeding_id'];
    $complementary_month_check = insertValues($complementary_feed_row, 'complementary_month_check');
    $complementary_month_date = insertValues($complementary_feed_row, 'complementary_month_date');

    $complementary_feed_html .= "
        <div class='complementary-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Month:</strong> {$complementary_month_check}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$complementary_month_date}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_complementary_btn' 
                    data-complementary-id='{$complementary_feeding_id}' 
                    data-patient-id='{$patient_id}'
                    data-complementary-month-check='{$complementary_month_check}'
                    data-complementary-month-date='{$complementary_month_date}'
                    title='Edit this record'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$complementary_feed_html .= "
    <button class='btn btn-outline-primary w-100 add_complementary_feed mt-2'
            data-patient-id='{$patient_id}'
            data-bs-toggle='modal'
            data-bs-target='#addComplimentaryModal'>
        <i class='bi bi-plus-lg text-white'></i> Add Complementary BreastFed
    </button> 
";

echo $complementary_feed_html;

$stmt_query->close();
$conn->close();
?>