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

$query = "SELECT post_supp_id, patient_id, iron_folic_month_given, iron_folic_date_given, tablets_given  
          FROM post_partum_supp
          WHERE patient_id = ? ORDER BY iron_folic_month_given ASC";
$stmt_query = $conn->prepare($query);
$stmt_query->bind_param("i", $patient_id);
$stmt_query->execute();
$result = $stmt_query->get_result();

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$post_iron_html = '';
while ($post_iron_row = $result->fetch_assoc()) {

    $post_supp_id = $post_iron_row['post_supp_id'];
    $patient_id_row = $post_iron_row['patient_id'];
    $iron_folic_month_given = insertValues($post_iron_row, 'iron_folic_month_given');
    $iron_folic_date_given = insertValues($post_iron_row, 'iron_folic_date_given');
    $tablets_given = insertValues($post_iron_row, 'tablets_given');

    $post_iron_html .= "
        <div class='post-iron-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #dee2e6; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Month Given:</strong> {$iron_folic_month_given}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$iron_folic_date_given}</p>
                <p style='margin: 0;'><strong>Tablets Given:</strong> {$tablets_given}</p>
            </div>
            <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_postpartum_iron_btn' 
                    data-post-supp-id='{$post_supp_id}' 
                    data-patient-id='{$patient_id_row}'
                    data-iron-folic-month-given='{$iron_folic_month_given}'
                    data-iron-folic-date-given='{$iron_folic_date_given}'
                    data-tablets-given='{$tablets_given}'
                    title='Edit this supplement'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}

$post_iron_html .= "
    <button type='button' class='btn btn-outline-primary w-100 add_postpartum_iron_btn mt-2'
        data-patient-id='{$patient_id}'
        data-bs-toggle='modal'
        data-bs-target='#addPostpartumIronModal'>
        <i class='bi bi-plus-lg text-white'></i> Add Iron Sulfate w/Folic Acid
    </button>
";
echo $post_iron_html;

$stmt_query->close();
$conn->close();
?>