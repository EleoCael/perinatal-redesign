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

$query = "SELECT infant_exclusively_breastfed_id, month_check, month_date FROM infant_exclusively_breastfed
          WHERE patient_id = ? ORDER BY month_date ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

$exlusive_feed_html = '';
while ($exclusive_feed_row = $result->fetch_assoc()) {
    $infant_exclusively_breastfed_id = $exclusive_feed_row['infant_exclusively_breastfed_id'];
    $month_check = insertValues($exclusive_feed_row, 'month_check');
    $month_date = insertValues($exclusive_feed_row, 'month_date');

    $exlusive_feed_html .= "

        <div class='exclusive-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Month:</strong> {$month_check}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$month_date}</p>
            </div>
           <button type='button' 
                        class='btn btn-sm btn-outline-primary edit_exclusive_breastfeeding_btn' 
                        data-exclusive-breastfeed-id='{$infant_exclusively_breastfed_id}' 
                        data-patient-id='{$patient_id}'
                        data-month-check='{$month_check}'
                        data-month-date='{$month_date}'
                        title='Edit this breastfeed'>
                    <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
     
        ";
}
$exlusive_feed_html .= "
    
     <button class='btn btn-outline-primary w-100 add_exclusive_breastfeed mt-2'
            data-patient-id = '{$patient_id}'
            data-bs-toggle = 'modal'
            data-bs-target = '#addExlusiveFeedModal'>
            <i class='bi bi-plus-lg text-white'></i>Add Exclusively BreastFed</button> 
";
echo $exlusive_feed_html;
$stmt_query->close();
$conn->close();