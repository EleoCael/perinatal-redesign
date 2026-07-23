<?php
require_once "../../../module/db.config.php";

if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])) {
    echo '<div class="alert alert-danger">';
    foreach ($_SESSION['form_errors'] as $error) {
        echo '<div>• ' . htmlspecialchars($error) . '</div>';
    }
    echo '</div>';
    unset($_SESSION['form_errors']);
}

$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$patient_display_name = '';
$pregnancies = [];

if ($patient_id > 0) {
    $stmt_name = $conn->prepare("SELECT first_name, middle_name, last_name FROM patient WHERE patient_id = ? LIMIT 1");
    $stmt_name->bind_param("i", $patient_id);
    $stmt_name->execute();
    $res_name = $stmt_name->get_result();
    if ($row = $res_name->fetch_assoc()) {
        $patient_display_name = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    }
    $stmt_name->close();

    // Fetch this patient's pregnancy records so the midwife can optionally
    // link this postpartum checkup back to a specific pregnancy.
    $stmt_preg = $conn->prepare("SELECT pregnancy_id, date_terminated, outcome, edc 
                                  FROM pregnancy 
                                  WHERE patient_id = ? 
                                  ORDER BY pregnancy_id DESC");
    $stmt_preg->bind_param("i", $patient_id);
    $stmt_preg->execute();
    $pregnancies = $stmt_preg->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_preg->close();
}

$outcomeLabels = [
    'FT' => 'Full Term',
    'PT' => 'Preterm',
    'FD' => 'Fetal Death',
    'AB' => 'Abortion',
];
?>

<div class="main-container">

    <?php if ($patient_id <= 0): ?>
        <div class="alert alert-danger">
            No patient selected. Please go back and add the postpartum patient's basic info first.
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Adding Medical Info for: <strong><?php echo htmlspecialchars($patient_display_name ?: ('Patient #' . $patient_id)); ?></strong>
        </div>
    <?php endif; ?>

    <form id="postpartumMedicalForm" action="/rhusystem/midwife/patient/postpartum/postpartum_medical_process.php" method="POST">
        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">
        <input type="hidden" name="submit_btn" value="1">

        <div class="card mb-3 shadow-sm">
            <div class="card-header text-center">
                LINKED PREGNANCY <small class="text-muted">(optional)</small>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Select the pregnancy this checkup follows</label>
                    <select class="form-select" name="pregnancy_id">
                        <option value="">Not linked / none</option>
                        <?php foreach ($pregnancies as $preg): ?>
                            <?php
                            $termDate = ($preg['date_terminated'] && $preg['date_terminated'] !== '0000-00-00')
                                ? date('M j, Y', strtotime($preg['date_terminated']))
                                : 'no delivery date recorded';
                            $outcomeText = $outcomeLabels[$preg['outcome']] ?? 'Outcome not set';
                            ?>
                            <option value="<?php echo $preg['pregnancy_id']; ?>">
                                Pregnancy #<?php echo $preg['pregnancy_id']; ?> — <?php echo htmlspecialchars($outcomeText); ?>, <?php echo htmlspecialchars($termDate); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($patient_id > 0 && empty($pregnancies)): ?>
                        <small class="text-muted">No pregnancy records found for this patient yet.</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-header text-center">
                POSTPARTUM CHECKUP
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date of Delivery</label>
                        <input type="date" class="form-control" name="post_delivery_date" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Time of Delivery</label>
                        <input type="time" class="form-control" name="post_delivery_time">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Checkup Visit</label>
                        <select class="form-select" name="checkup_visit">
                            <option value="" disabled selected>Select visit</option>
                            <option value="1st visit">1st visit</option>
                            <option value="2nd visit">2nd visit</option>
                            <option value="3rd visit">3rd visit</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Checkup Date</label>
                        <input type="date" class="form-control" name="post_checkup_date" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Breastfeeding Initiated Date</label>
                        <input type="date" class="form-control" name="breastfeeding_date" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Breastfeeding Initiated Time</label>
                        <input type="time" class="form-control" name="breastfeeding_time">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-header text-center">
                IRON WITH FOLIC ACID SUPPLEMENTATION
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Month Given</label>
                        <select class="form-select" name="iron_folic_month_given">
                            <option value="" disabled selected>Select month</option>
                            <option value="1st month postpartum">1st month postpartum</option>
                            <option value="2nd month postpartum">2nd month postpartum</option>
                            <option value="3rd month postpartum">3rd month postpartum</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Given</label>
                        <input type="date" class="form-control" name="iron_folic_date_given" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tablets Given</label>
                        <input type="number" class="form-control" min="0" name="tablets_given">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-header text-center">
                VITAMIN A
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="vitamin_a" value="1" id="vitamin_a">
                            <label class="form-check-label" for="vitamin_a">Given</label>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Date Given</label>
                        <input type="date" class="form-control" name="vitamin_a_date" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="remarks" rows="4" placeholder="Notes covering the postpartum checkup and supplementation"></textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-3">
            <button class="btn btn-secondary js-back_btn_postpartum col-md-2" type="button">Back</button>
            <button class="btn btn-primary js-submit_postpartum_medical_info col-md-2" type="button">Submit</button>
        </div>

    </form>
</div>