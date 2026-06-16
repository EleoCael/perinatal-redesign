<?php
//iron supplement display
require_once "../../../module/db.config.php";

$pregnancy_id = $_POST['pregnancy_id'];
$supplement_type = $_POST['supplement_type'] ?? 'Iron Sulfate w/Folic Acid';
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

$query = "SELECT maternal_supplement_id, supp_trimester, supp_tablets_given, date_supp FROM maternal_supplements
          WHERE pregnancy_id = ? AND supplement_type = ? ORDER BY date_supp ASC";
$stmt_query = $conn->prepare($query);

if ($stmt_query === false) {
    echo "<div> Database Error: Failed to prepare statement.</div>";
    error_log("Database Prepare Error: " . $conn->error);
    exit;
}

$stmt_query->bind_param("is", $pregnancy_id, $supplement_type);
$stmt_query->execute();
$result = $stmt_query->get_result();

$iron_html = '';
while ($iron_row = $result->fetch_assoc()) {
    $maternal_supplement_id = $iron_row['maternal_supplement_id'];
    $trimester = insertValues($iron_row, 'supp_trimester');
    $tablets_given = insertValues($iron_row, 'supp_tablets_given');
    $date_supp = insertValues($iron_row, 'date_supp');
    

    $iron_html .= "
        <div class='iron-entry' style='display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid gray; border-radius: 5px; margin-bottom: 10px;'>
            <div>
                <p style='margin: 0;'><strong>Trimester:</strong> {$trimester}</p>
                <p style='margin: 0;'><strong>Tablets Given:</strong> {$tablets_given}</p>
                <p style='margin: 0;'><strong>Date:</strong> {$date_supp}</p>
            </div>
             <button type='button' 
                    class='btn btn-sm btn-outline-primary edit_iron_btn' 
                    data-supplement-id='{$maternal_supplement_id}' 
                    data-preg-id='{$pregnancy_id}'
                    data-trimester='{$trimester}'
                    data-tablets-given='{$tablets_given}'
                    data-date-supp='{$date_supp}'
                    title='Edit this supplement'>
                <i class='bi bi-pencil-fill'></i> Edit
            </button>
        </div>
    ";
}
$iron_html .= "
    
    <button type='button' class='btn btn-outline-primary w-100 add_iron_btn mt-2'
        data-preg-id='{$pregnancy_id}'
        data-bs-toggle='modal'
        data-bs-target='#addIronModal'>
        <i class='bi bi-plus-lg text-white'></i> Add Iron Supplement
    </button>
";
echo $iron_html;
?>