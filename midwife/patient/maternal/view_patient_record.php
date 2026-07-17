<?php
require_once "../../../module/db.config.php";
session_start();

if (!isset($_SESSION['health_center_id'])) {
    die("Access denied: No health center assigned");
}

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

$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$health_center_id = $_SESSION['health_center_id'];

$view_query = "SELECT * FROM patient WHERE patient_id = ? AND health_center_id = ?";
$stmt_view = $conn->prepare($view_query);
$stmt_view->bind_param("ii", $patient_id, $health_center_id);
$stmt_view->execute();
$patient_result = $stmt_view->get_result();

if ($patient_result->num_rows == 0) {
    echo '<div class="main-container"><div class="alert alert-danger">Patient not found or access denied.</div></div>';
    exit;
}
$row = $patient_result->fetch_assoc();

$full_name = $row['last_name'] . ", " . $row['first_name'];
if (!empty($row['middle_name'])) {
    $full_name .= " " . $row['middle_name'];
}
$family_serial_no = insertValues($row, 'family_serial_number');
$email = insertValues($row, 'email');
$contact = insertValues($row, 'contact_number');

// Pregnancy list
$pregnancy_query = "SELECT p.* FROM pregnancy p INNER JOIN patient pt ON p.patient_id = pt.patient_id 
                    WHERE p.patient_id = ? AND pt.health_center_id = ? ORDER BY p.date_created DESC";
$stmt_pregnancy = $conn->prepare($pregnancy_query);
$stmt_pregnancy->bind_param("ii", $patient_id, $health_center_id);
$stmt_pregnancy->execute();
$pregnancy_result = $stmt_pregnancy->get_result();
$pregnancies = $pregnancy_result->fetch_all(MYSQLI_ASSOC);
$pregnancy_count = count($pregnancies);
?>

<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-body-tertiary  " >Patient Record > <?php echo htmlspecialchars($full_name); ?></h5>
        <button class="btn btn-secondary" type="button" onclick="loadPage('patient/maternal/view_maternal_patient.php')">Back to Patient List</button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <tr><td class="table-dark text-center" colspan="2"><label><strong>BASIC INFORMATION</strong></label></td></tr>
            <tr><td width="40%"><label><strong>No.</strong></label></td><td width="60%"><?php echo $row['patient_id']; ?></td></tr>
            <tr><td width="40%"><label><strong>Date of Registration</strong></label></td><td width="60%"><?php echo $row['date_of_registration']; ?></td></tr>
            <tr><td width="40%"><label><strong>Family Serial No.</strong></label></td><td width="60%"><?php echo htmlspecialchars($family_serial_no); ?></td></tr>
            <tr><td width="40%"><label><strong>Full Name</strong></label></td><td width="60%"><?php echo htmlspecialchars($full_name); ?></td></tr>
            <tr><td width="40%"><label><strong>Address</strong></label></td><td width="60%"><?php echo htmlspecialchars($row['address']); ?></td></tr>
            <tr><td width="40%"><label><strong>Socio Economic Status</strong></label></td><td width="60%"><?php echo htmlspecialchars($row['socio_economic_status']); ?></td></tr>
            <tr><td width="40%"><label><strong>Date of Birth</strong></label></td><td width="60%"><?php echo $row['birth_date']; ?></td></tr>
            <tr><td width="40%"><label><strong>Age Bracket</strong></label></td><td width="60%"><?php echo $row['age_bracket']; ?></td></tr>
            <tr><td width="40%"><label><strong>Age</strong></label></td><td width="60%"><?php echo $row['age']; ?></td></tr>
            <tr><td width="40%"><label><strong>Email</strong></label></td><td width="60%"><?php echo htmlspecialchars($email); ?></td></tr>
            <tr><td width="40%"><label><strong>Contact No.</strong></label></td><td width="60%"><?php echo htmlspecialchars($contact); ?></td></tr>
        </table>
    </div>

    <div class="table-responsive mt-3">
        <table class="table table-bordered">
            <tr><td class="table-dark text-center" colspan="2"><label><strong>PREGNANCY LIST</strong></label></td></tr>
        </table>

        <?php if ($pregnancy_count === 0): ?>
            <p class="text-center text-muted">No Pregnancy Record Found</p>
        <?php else: ?>
            <div id="pregnancy-list-container">
                <?php
                $per_page = 5;
                $i = 1;
                foreach ($pregnancies as $pregnancy):
                    $page_index = (int) floor(($i - 1) / $per_page);
                    $format_date = date('Y-m-d', strtotime($pregnancy['date_created']));
                    ?>
                    <div class="pregnancy-list-item" data-page="<?php echo $page_index; ?>" style="display:flex; justify-content:space-between; align-items:center; padding:10px; border:1px solid gray; border-radius:5px; margin-bottom:10px;">
                        <div><strong>Pregnancy #<?php echo $i; ?></strong> (Date Created: <?php echo $format_date; ?>)</div>
                        <div>
                            <button class="btn btn-sm btn-outline-primary view_preg_btn" data-preg-id="<?php echo $pregnancy['pregnancy_id']; ?>">View Details</button>
                        </div>
                    </div>
                    <?php
                    $i++;
                endforeach;
                ?>
            </div>

            <?php if ($pregnancy_count > $per_page): ?>
                <div class="d-flex justify-content-center align-items-center gap-2 mt-3" id="pregnancy-pagination">
                    <button class="btn btn-sm btn-outline-secondary js-preg-prev" type="button">Previous</button>
                    <span class="js-preg-page-indicator">Page 1</span>
                    <button class="btn btn-sm btn-outline-secondary js-preg-next" type="button">Next</button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="mt-3">
            <button id="addPregnancyBtn" class="btn btn-primary" data-patient-id="<?php echo $patient_id; ?>">
                <i class="bi bi-person-plus me-2" style="color:white;"></i> Add New Pregnancy
            </button>
            <button id="addInfantBtn" class="btn btn-danger" data-mother-id="<?php echo $patient_id; ?>">
                <i class="bi bi-person-plus me-2" style="color:white;"></i> Add Infant Records
            </button>
        </div>
    </div>
</div>