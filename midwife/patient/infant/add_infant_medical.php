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
$mother_id = isset($_GET['mother_id']) ? intval($_GET['mother_id']) : 0;
$patient_display_name = '';

if ($patient_id > 0) {
    $stmt_name = $conn->prepare("SELECT first_name, middle_name, last_name FROM patient WHERE patient_id = ? LIMIT 1");
    $stmt_name->bind_param("i", $patient_id);
    $stmt_name->execute();
    $res_name = $stmt_name->get_result();
    if ($row = $res_name->fetch_assoc()) {
        $patient_display_name = trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']);
    }
    $stmt_name->close();
}
?>

<div class="main-container">

    <?php if ($patient_id <= 0): ?>
        <div class="alert alert-danger">
            No patient selected. Please go back and add the infant's basic info first.
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Adding Medical Info for: <strong><?php echo htmlspecialchars($patient_display_name ?: ('Patient #' . $patient_id)); ?></strong>
        </div>
    <?php endif; ?>

    <form id="infantMedicalForm" action="/rhusystem/midwife/patient/infant/infant_medical_process.php" method="POST">
        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">
        <input type="hidden" name="mother_id" value="<?php echo htmlspecialchars($mother_id); ?>">
        <input type="hidden" name="submit_btn" value="1">

        <ul class="nav nav-tabs" id="myTabsInfant">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#screening-tab">Screening</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#feeding-tab">Feeding</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#immunization-tab">Immunizations</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#supplement-tab">Supplementation & Deworming</a>
            </li>
        </ul>

        <div class="tab-content content-main-div">

            <!--Screening-->
            <div class="tab-pane fade show active" id="screening-tab">

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        NEWBORN SCREENING
                    </div>
                    <div class="card-body">
                        <div class="form-group group-form row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sex</label>
                                <select class="form-select" name="sex">
                                    <option value="" disabled selected>Select sex</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Birth Weight (grams)</label>
                                <input type="number" step="0.01" class="form-control" name="birth_weight" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Birth Height (cm)</label>
                                <input type="number" step="0.01" class="form-control" name="birth_height" min="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Newborn Screening Referral</label>
                                <select class="form-select" name="newborn_screening_referral">
                                    <option value="" disabled selected>Select status</option>
                                    <option value="Referred">Referred</option>
                                    <option value="Not Referred">Not Referred</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Newborn Screening Done</label>
                                <select class="form-select" name="newborn_screening_done">
                                    <option value="" disabled selected>Select status</option>
                                    <option value="Done">Done</option>
                                    <option value="Not Done">Not Done</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        CPAB / TT STATUS
                    </div>
                    <div class="card-body">
                        <div class="form-group group-form row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">TT Status</label>
                                <select class="form-select" name="cpab_tt_status">
                                    <option value="" disabled selected>Select status</option>
                                    <option value="td">TD</option>
                                    <option value="td1">TD1</option>
                                    <option value="td2">TD2</option>
                                    <option value="td3">TD3</option>
                                    <option value="td4">TD4</option>
                                    <option value="td5">TD5</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="cpab_tt_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date Assessed</label>
                                <input type="date" class="form-control" name="cpab_tt_date_assessed" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!--Screening-->

            <!--Feeding-->
            <div class="tab-pane fade" id="feeding-tab">

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        EXCLUSIVE BREASTFEEDING
                    </div>
                    <div class="card-body">
                        <div class="form-group group-form">
                            <?php
                            $monthLabels = ['1st Month', '2nd Month', '3rd Month', '4th Month', '5th Month', '6th Month'];
                            foreach ($monthLabels as $i => $monthLabel):
                            ?>
                            <div class="row g-3 mb-3 align-items-center">
                                <div class="col-md-1 d-flex align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="month_check[]" value="<?php echo htmlspecialchars($monthLabel); ?>" id="ebf_month_<?php echo $i; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="ebf_month_<?php echo $i; ?>"><?php echo htmlspecialchars($monthLabel); ?></label>
                                </div>
                                <div class="col-md-4">
                                    <input type="date" class="form-control" name="month_date[]" max="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        COMPLEMENTARY FEEDING
                    </div>
                    <div class="card-body">
                        <div class="form-group group-form">
                            <?php foreach ($monthLabels as $i => $monthLabel): ?>
                            <div class="row g-3 mb-3 align-items-center">
                                <div class="col-md-1 d-flex align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="complementary_month_check[]" value="<?php echo htmlspecialchars($monthLabel); ?>" id="cf_month_<?php echo $i; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="cf_month_<?php echo $i; ?>"><?php echo htmlspecialchars($monthLabel); ?></label>
                                </div>
                                <div class="col-md-4">
                                    <input type="date" class="form-control" name="complementary_month_date[]" max="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>
            <!--Feeding-->

            <!--Immunizations-->
            <div class="tab-pane fade" id="immunization-tab">

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">BCG</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="bcg_check" value="1" id="bcg_check">
                                    <label class="form-check-label" for="bcg_check">Given</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="bcg_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">HEPATITIS B</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Given</label>
                                <select class="form-select" name="hepaB_day">
                                    <option value="" disabled selected>Select timing</option>
                                    <option value="w/in 24 hours">Within 24 hours</option>
                                    <option value="More than 24 hours">More than 24 hours</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="hepaB_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">PENTAVALENT (DPT-HIB-HepB)</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Dose</label>
                                <select class="form-select" name="pentavalent_type">
                                    <option value="" disabled selected>Select dose</option>
                                    <option value="Pentavalent 1">Pentavalent 1</option>
                                    <option value="Pentavalent 2">Pentavalent 2</option>
                                    <option value="Pentavalent 3">Pentavalent 3</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="pentavalent_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">OPV (Oral Polio Vaccine)</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Dose</label>
                                <select class="form-select" name="opv_type">
                                    <option value="" disabled selected>Select dose</option>
                                    <option value="Opv 1">OPV 1</option>
                                    <option value="Opv 2">OPV 2</option>
                                    <option value="Opv 3">OPV 3</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="opv_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">IPV (Injectable Polio Vaccine)</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ipv_1" value="1" id="ipv_1">
                                    <label class="form-check-label" for="ipv_1">Given</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="ipv_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">PCV (Pneumococcal Conjugate Vaccine)</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Dose</label>
                                <select class="form-select" name="pcv_type">
                                    <option value="" disabled selected>Select dose</option>
                                    <option value="PCV 1">PCV 1</option>
                                    <option value="PCV 2">PCV 2</option>
                                    <option value="PCV 3">PCV 3</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="pcv_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">MCV (Measles-Containing Vaccine)</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Dose</label>
                                <select class="form-select" name="mcv_type">
                                    <option value="" disabled selected>Select dose</option>
                                    <option value="MCV1 (AMV)">MCV1 (AMV)</option>
                                    <option value="MCV2 (MMR)">MCV2 (MMR)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="mcv_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">ROTAVIRUS VACCINE (RVV)</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Dose</label>
                                <select class="form-select" name="rvv_type">
                                    <option value="" disabled selected>Select dose</option>
                                    <option value="Rota 1">Rota 1</option>
                                    <option value="Rota 2">Rota 2</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="rvv_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">FULLY IMMUNIZED CHILD (FIC)</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="fic_check" value="1" id="fic_check">
                                    <label class="form-check-label" for="fic_check">Completed</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Completed</label>
                                <input type="date" class="form-control" name="fic_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!--Immunizations-->

            <!--Supplementation & Deworming-->
            <div class="tab-pane fade" id="supplement-tab">

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">VITAMIN A</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="vitamin_type">
                                    <option value="" disabled selected>Select type</option>
                                    <option value="Vitamin A (6-11 Months)">Vitamin A (6-11 Months)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="vitamin_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">IRON SUPPLEMENTATION <small class="text-muted">(for preterm/low birth weight infants)</small></div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="iron_type">
                                    <option value="" disabled selected>Select type</option>
                                    <option value="Iron Drops">Iron Drops</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="iron_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">MICRONUTRIENT POWDER (MNP)</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-5">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="mnp_type">
                                    <option value="" disabled selected>Select type</option>
                                    <option value="MNP (6-11 Months)">MNP (6-11 Months)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="mnp_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">DEWORMING</div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="deworming_check" value="1" id="deworming_check">
                                    <label class="form-check-label" for="deworming_check">Given</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Date Given</label>
                                <input type="date" class="form-control" name="deworming_date" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3">
                    <button class="btn btn-secondary js-back_btn_infant col-md-2" type="button">Back</button>
                    <button class="btn btn-primary js-submit_infant_medical_info col-md-2" type="button">Submit</button>
                </div>

            </div>
            <!--Supplementation & Deworming-->

        </div>
    </form>
</div>