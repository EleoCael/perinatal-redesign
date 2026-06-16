<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Midwife") {

    header("Location: ../system/login.php");

    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Midwife Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="../assets/css/midwife_dashboard.css">
    <link rel="stylesheet" href="../assets/css/report_midwife.css">
    <link rel="stylesheet" href="../assets/css/filter_btn.css">
    
    


</head>

<body>
    <nav class="navbar navbar-expand-lg main-navbar">
        <div class="container-fluid">
            <!--  Navbar -->
            <a class="navbar-brand" href="#" onclick="loadHomePage();">
                Perinatal Care
            </a>

        </div>
    </nav>

    <!--  Desktop sidebar container -->
    <div class="sidebar-container d-none d-lg-block">
        <nav class="sidebar-nav">

            <div class="sidebar-section-title">Dashboard</div>

            <a href="#" class="sidebar-nav-link active" onclick="loadHomePage();">
                <i class="bi bi-house-door  text-white"></i>
                <span>Home</span>
            </a>


            <div class="sidebar-section-title">Patient Management</div>

            <a href="#" class="sidebar-nav-link" onclick="loadPage('viewPatient_LandingPg.php', this);">
                <i class="bi bi-file-earmark-person  text-white"></i>
                <span>Patient Records</span>
            </a>

            <a href="#" class="sidebar-nav-link" onclick="loadPage('addPatient_LandingPg.php', this);">
                <i class="bi bi-person-plus  text-white"></i>
                <span>Add New Patient</span>
            </a>

            <div class="sidebar-section-title">Schedule</div>

            <a href="#" class="sidebar-nav-link" onclick="loadPage('appointments/appointment_page.php')">
                <i class="bi bi-calendar4-week text-white"></i>
                <span> Appoinments</span>
            </a>


            <div class="sidebar-section-title">Reports </div>

            <a href="#" class="sidebar-nav-link" onclick="loadPage('reports/report_dashboard.php')">
                <i class="bi bi-bar-chart text-white"></i>
                <span>Health Reports</span>
            </a>

            <div class="sidebar-section-title">Account</div>
            <a href="#" class="sidebar-nav-link" onclick="confirmLogout()">
                <i class="bi bi-box-arrow-right text-white"></i>
                <span>Logout </span>
            </a>

        </nav>
    </div>
    <!--  Desktop sidebar container -->

    <!-- Main Page -->
    <main class="main-content">
        <div id="main-content" class="fade-in">
         
        </div>
    </main>
    <!-- Main Page -->

    <!--=========================================MATERNAL MODAL ============================-->

    <!-- Modal for viewing maternal record -->
    <div class="modal" tabindex="-1" id="myModal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Maternal Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalContent"></div>

                    <div id="pregnancyList"> </div>

                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for viewing maternal record -->

    <!-- Modal for viewing pregnancy record -->
    <div class="modal" tabindex="-1" id="viewPregnancyRecord">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pregnancyModalTitle">Pregnancy Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="pregDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for viewing pregnancy record -->

    <!-- Modal for adding prenatal checkup -->
    <div class="modal" tabindex="-1" id="addCheckupModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Prenatal Check-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCheckupForm">
                        <input type="hidden" id="checkup_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label for="trimester" class="form-label">Trimester</label>
                            <select class="form-select" name="trimester">
                                <option value="" disabled selected>Select trimester</option>
                                <option value="1st">1st Tri</option>
                                <option value="2nd">2nd Tri</option>
                                <option value="3rd">3rd Tri</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Check-up Date</label>
                            <input type="date" class="form-control" name="checkup_date"
                             max="<?php echo date('Y-m-d'); ?>" id="checkup_date">
                            <span id="error_checkup_date" class="text-danger"></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Check-up</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding prenatal checkup -->

    <!-- Modal for adding birth information -->
    <div class="modal" tabindex="-1" id="addBirthInfoModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Birth Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBirthInfoForm">
                        <input type="hidden" id="delivery_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Type of Delivery</label>
                            <select class="form-select" name="delivery_type">
                                <option value="" disabled selected>Select delivery type</option>
                                <option value="CS">CS-Caesarian Section</option>
                                <option value="VD">VD-Vaginal Delivery</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Weight Classification</label>
                            <select class="form-select" name="birth_weight_classification">
                                <option value="" disabled selected>Select classification</option>
                                <option value="Low">Low(< 2,500g) </option>
                                <option value="Normal">Normal(≥ 2,500g)</option>
                                <option value="Unknown">Unknown</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Birth Weight(in grams)</label>
                            <input type="number" name="birth_weight" min="0" step="0.01" placeholder="leave blank if unknown" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Birth Attendant</label>
                            <select class="form-select" name="birth_attendant">
                                <option value="" disabled selected>Select birth attendant</option>
                                <option value="MD">MD-Doctor </option>
                                <option value="RN">RN-Nurse</option>
                                <option value="MW">MW-Midwife</option>
                                <option value="H">H-HilotTBA</option>
                                <option value="O">O-Other</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Birth Information</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding birth information -->

    <!-- Modal for adding place of delivery/Health Facility -->
    <div class="modal" tabindex="-1" id="addPlaceBirthModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Place of Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPlaceBirthForm">
                        <input type="hidden" id="place_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Health Facility Type</label>
                            <select class="form-select" name="health_facility_type">
                                <option value="" disabled selected>Select facility type</option>
                                <option value="BHS">BHS</option>
                                <option value="RHU/MHC">RHU/MHC</option>
                                <option value="Lying-in">Lying-in</option>
                                <option value="Hospital">Hospital/Birthing Homes</option>
                                <option value="DOH">DOH-Licensed Ambulance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Health Facility Name</label>
                            <input type="text" class="form-control" name="health_facility_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ownership</label>
                            <select class="form-select" name="ownership">
                                <option value="" disabled selected>Select ownership</option>
                                <option value="Public">Public</option>
                                <option value="Private">Private</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input class="form-check-input" type="checkbox" value="1" name="bemonc_cemonc_capable" id="checkDefault">
                            <label class="form-check-label" for="checkDefault">
                                place a check if BEmONC/CEmONC capable
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Place of Delivery</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding place of delivery/Health Facility -->

    <!-- Modal for adding place of delivery/Non Health Facility -->
    <div class="modal" tabindex="-1" id="addPlaceNonHealthModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Place of Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPlaceNonHealthForm">
                        <input type="hidden" id="place_non_health_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Non-Health Facility(Skip if not applicable)</label>
                            <select class="form-select" name="non_health_facility_type">
                                <option value="" disabled selected>Select non-health facility </option>
                                <option value="Home">1-Home</option>
                                <option value="Others">2-Others(including emergency)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Non-Health Facility Name</label>
                            <input type="text" class="form-control" name="non_health_facility_name" placeholder="leave blank if not applicable">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Non-Health Facility</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding place of delivery/Non Health Facility -->

    <!-- Modal for adding immunization maternal -->
    <div class="modal" tabindex="-1" id="addImmunizationModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Immunization</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addImmunizationForm">
                        <input type="hidden" id="immunization_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Immunization Type</label>
                            <select class="form-select" name="immunization_type">
                                <option value="" disabled selected>Select type</option>
                                <option value="Td1/TT1">Td1/TT1</option>
                                <option value="Td2/TT2">Td2/TT2</option>
                                <option value="Td3/TT3">Td3/TT3</option>
                                <option value="Td4/TT4">Td4/TT4</option>
                                <option value="Td5/TT5">Td5/TT5</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Given</label>
                            <input type="date" class="form-control" name="immunization_date"
                            max="<?php echo date('Y-m-d'); ?>" id="immunization_date">
                            <span id="error_immunization_date" class="text-danger"></span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Immunization</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding immunization maternal  -->

    <!-- Modal for adding FIM maternal  -->
    <div class="modal" tabindex="-1" id="addFimModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input FIM Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addFimForm">
                        <input type="hidden" id="fim_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">FIM Status</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="fim_status" id="fim_status_checkbox">
                                <label class="form-check-label" for="fim_status">
                                    FIM Status (check if fully immunized)
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save FIM Status</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding FIM maternal  -->

    <!-- Modal for adding iron supplement maternal -->
    <div class="modal" tabindex="-1" id="addIronModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Iron Sulfate w/Folic Acid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addIronForm">
                        <input type="hidden" id="iron_pregnancy_id" name="pregnancy_id">
                        <input type="hidden" name="supplement_type" value="Iron Sulfate w/Folic Acid">
                        <div class="mb-3">
                            <label class="form-label">Trimester</label>
                            <select class="form-select" name="supp_trimester">
                                <option value="" disabled selected>Select trimester</option>
                                <option value="1st visit (1st Tri)">1st visit(1st tri)</option>
                                <option value="2nd visit (2nd Tri)">2nd visit (2nd tri)</option>
                                <option value="3rd visit (3rd Tri)">3rd visit (3rd tri)</option>
                                <option value="4th visit (3rd Tri)">4th visit (3rd tri)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Given</label>
                            <input type="date" class="form-control" name="date_supp"
                             max="<?php echo date('Y-m-d'); ?>" id="date_supp">
                            <span id="error_date_supp" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tablets Given</label>
                            <input type="number" class="form-control" min="0" name="supp_tablets_given">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Iron Supplement</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding  iron supplement maternal -->

    <!-- Modal for adding calcium supplement maternal -->
    <div class="modal" tabindex="-1" id="addCalciumModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Calcium Carbonate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCalciumForm">
                        <input type="hidden" id="calcium_pregnancy_id" name="pregnancy_id">
                        <input type="hidden" name="supplement_type" value="Calcium Carbonate">
                        <div class="mb-3">
                            <label class="form-label">Trimester</label>
                            <select class="form-select" name="supp_trimester">
                                <option value="" disabled selected>Select trimester</option>
                                <option value="2nd visit (2nd Tri)">2nd visit (2nd tri)</option>
                                <option value="3rd visit (3rd Tri)">3rd visit (3rd tri)</option>
                                <option value="4th visit (3rd Tri)">4th visit (3rd tri)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Given</label>
                            <input type="date" class="form-control" name="date_supp">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tablets Given</label>
                            <input type="number" class="form-control" min="0" name="supp_tablets_given">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Calcium Supplement</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding calcium supplement maternal -->

    <!-- Modal for adding iodine supplement maternal -->
    <div class="modal" tabindex="-1" id="addIodineModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Iodine Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addIodineForm">
                        <input type="hidden" id="iodine_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Iodine Capsule</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="iodine_capsule_given" id="iodine_checkbox">
                                <label class="form-check-label">
                                    check if 2 Capsules were given
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Given(1st visit/tri)</label>
                            <input type="date" class="form-control" name="date_iodine">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Iodine Supplement</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding iodine supplement maternal -->

    <!-- Modal for adding postpartum checkup -->
    <div class="modal" tabindex="-1" id="addPostpartumCheckupModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Postpartum Check-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPostCheckupForm">
                        <input type="hidden" id="post_checkup_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Post-Partum Visits</label>
                            <select class="form-select" name="checkup_visit">
                                <option disabled selected>Select visit</option>
                                <option value="Within 24hrs after delivery">Within 24hrs</option>
                                <option value="3rd day">3rd day</option>
                                <option value="7 to 14 days">7-14 days</option>
                                <option value="6 weeks">6 weeks</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Checkup Date</label>
                            <input type="date" class="form-control" name="post_checkup_date"
                            max="<?php echo date('Y-m-d'); ?>" id="post_checkup_date">
                            <span id="error_post_checkup_date" class="text-danger"></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Check-up</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding postpartum checkup -->

    <!-- Modal for adding prenatal bmi -->
    <div class="modal" tabindex="-1" id="addPrenatalBmiModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Add Prenatal Details </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPrenatalBmiForm">
                        <input type="hidden" id="bmi_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">BMI Classification</label>
                            <select class="form-select" name="bmi_class">
                                <option value="" disabled selected>Select bmi classification</option>
                                <option value="Low">Low (18.5)</option>
                                <option value="Normal">Normal (18.5)</option>
                                <option value="High">High (≥23.0)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">BMI</label>
                            <input type="number" class="form-control" min="0" step="0.01" name="bmi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deworming</label>
                            <select class="form-select" name="deworming_status">
                                <option value="" disabled selected>Select trimester</option>
                                <option value="2nd Tri">2nd Tri</option>
                                <option value="3rd Tri">3rd Tri</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deworming Date</label>
                            <input type="date" class="form-control" name="deworming_date_given"
                             max="<?php echo date('Y-m-d'); ?>" id="deworming_date_given">
                            <span id="error_deworming_date_given" class="text-danger"></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Prenatal Details</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding prenatal bmi-->

    <!-- Modal for adding disease screening -->
    <div class="modal" tabindex="-1" id="addDiseaseModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Infectious Disease Screening </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDiseaseForm">
                        <input type="hidden" id="disease_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label"><strong>Syphilis Screening</strong></label>
                            <input type="date" class="form-control" name="syphilis_date"
                             max="<?php echo date('Y-m-d'); ?>" id="syphilis_date">
                            <span id="error_syphilis_date" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <input class="form-check-input" type="radio" name="syphilis_screening" value="positive">
                            <label class="form-check-label">Positive</label>

                            <input class="form-check-input" type="radio" name="syphilis_screening" value="negative">
                            <label class="form-check-label">Negative</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Syphilis Screening Note:</strong></label>
                            <input type="text" class="form-control" name="syphilis_screening_remarks">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label"><strong>Hepatitis B Screening</strong></label>
                            <input type="date" class="form-control" name="hepatitisB_date"
                            max="<?php echo date('Y-m-d'); ?>" id="hepatitisB_date">
                            <span id="error_hepatitisB_date" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <input class="form-check-input" type="radio" name="hepatitis_b_screening" value="positive">
                            <label class="form-check-label">Positive</label>
                            <input class="form-check-input" type="radio" name="hepatitis_b_screening" value="negative">
                            <label class="form-check-label">Negative</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Hepatitis B Screening Note: </strong></label>
                            <input type="text" class="form-control" name="hepatitis_b_screening_remarks">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Infectious Disease Screening</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding disease screening -->

    <!-- Modal for adding disease screening -->
    <div class="modal" tabindex="-1" id="addHivModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">HIV Screening </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addHivForm">
                        <input type="hidden" id="hiv_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label"><strong>HIV Screening</strong></label>
                            <input type="date" class="form-control" name="hiv_date"
                             max="<?php echo date('Y-m-d'); ?>" id="hiv_date">
                            <span id="error_hiv_date" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <input class="form-check-input" type="radio" name="hiv_screening" value="positive">
                            <label class="form-check-label">Positive</label>
                            <input class="form-check-input" type="radio" name="hiv_screening" value="negative">
                            <label class="form-check-label">Negative</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>HIV Screening Note: </strong></label>
                            <input type="text" class="form-control" name="hiv_screening_remarks">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save HIV Screening</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding disease screening -->

    <!-- Modal for adding gestational screening -->
    <div class="modal" tabindex="-1" id="addLaboratoryModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Laboratory Screening </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addLaboratoryForm">
                        <input type="hidden" id="laboratory_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label"><strong>Gestational Diabetes</strong> </label>
                            <input type="date" class="form-control" name="gestational_diabetes_date"
                            max="<?php echo date('Y-m-d'); ?>" id="gestational_diabetes_date">
                            <span id="error_gestational_diabetes_date" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <input class="form-check-input" type="radio" name="gestational_diabetes_screening" value="positive">
                            <label class="form-check-label">Positive</label>

                            <input class="form-check-input" type="radio" name="gestational_diabetes_screening" value="negative">
                            <label class="form-check-label">Negative</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Gestational Diabetes Note:</strong></label>
                            <input type="text" class="form-control" name="diabetes_remarks">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Laboratory Screening</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding gestational screening -->

    <!-- Modal for adding CBC/Hgb&Hct Count -->
    <div class="modal" tabindex="-1" id="addCbcModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">CBC/Hgb&Hct Count</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCbcForm">
                        <input type="hidden" id="cbc_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">CBC/Hgb&Hct Date Screened</label>
                            <input type="date" class="form-control" name="cbc_hgb_hct_date"
                            max="<?php echo date('Y-m-d'); ?>" id="cbc_hgb_hct_date">
                            <span id="error_cbc_hgb_hct_date" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <input class="form-check-input" type="radio" name="anemia_status" value="with anemia">
                            <label class="form-check-label">w/anemia</label>

                            <input class="form-check-input" type="radio" name="anemia_status" value="w/o anemia">
                            <label class="form-check-label">w/o anemia</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CBC/Hgb&Hct Count</label>
                            <input type="number" step="0.01" class="form-control" name="cbc_hgb_hct_count">
                        </div>
                        <div>
                            <label class="form-label">Note:</label>
                            <input type="text" class="form-control" name="anemia_status_remarks">

                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save CBC/Hgb&Hct Screening</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding CBC/Hgb&Hct Count -->

    <!-- Modal for adding gestational screening -->
    <div class="modal" tabindex="-1" id="addGIvenIronModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Given Iron</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addGIvenIronForm">
                        <input type="hidden" id="given_iron_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Given Iron Date</label>
                            <input type="date" class="form-control" name="given_iron_date"
                             max="<?php echo date('Y-m-d'); ?>" id="given_iron_date">
                                      <span id="error_given_iron_date" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <input class="form-check-input" type="checkbox" value="1" name="given_iron" id="checkDefault">
                            <label class="form-check-label" for="checkDefault">
                                place a check if Given Iron
                            </label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note:</label>
                            <input type="text" class="form-control" name="maternal_screening_remark">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Given Iron</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding gestational screening -->

    <!-- Modal for adding postpartum iron supplement maternal -->
    <div class="modal" tabindex="-1" id="addPostIronModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Iron Sulfate w/Folic Acid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPostIronForm">
                        <input type="hidden" id="post_iron_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Iron w/Folic Acid Month Given</label>
                            <select class="form-select" name="iron_folic_month_given">
                                <option disabled selected>Select month</option>
                                <option value="1st month postpartum">1st month postpartum</option>
                                <option value="2nd month postpartum">2nd month postpartum</option>
                                <option value="3rd month postpartum">3rd month postpartum</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Given</label>
                            <input type="date" class="form-control" name="iron_folic_date_given"
                             max="<?php echo date('Y-m-d'); ?>" id="iron_folic_date_given">
                            <span id="error_iron_folic_date_given" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Tablets Given</label>
                            <input type="number" min="0" class="form-control" name="tablets_given">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Iron Supplement</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding postpartum iron supplement maternal -->

    <!-- Modal for adding vitamin supplement maternal -->
    <div class="modal" tabindex="-1" id="addVitaminModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Vitamin A Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addVitaminForm">
                        <input type="hidden" id="vitamin_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Vitamin A</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="vitamin_a" id="vitamin_checkbox">
                                <label class="form-check-label">
                                    check if Vitamin A was given
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Given</label>
                            <input type="date" class="form-control" name="vitamin_a_date"
                             max="<?php echo date('Y-m-d'); ?>" id="vitamin_a_date">
                            <span id="error_vitamin_a_date" class="text-danger"></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Vitamin A Supplement</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding vitamin supplement maternal -->

    <!-- Modal for adding postpartum iron supplement maternal -->
    <div class="modal" tabindex="-1" id="addPregOutcomeModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Pregnancy Outcome</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPregOutcomeForm">
                        <input type="hidden" id="outcome_pregnancy_id" name="pregnancy_id">
                        <div class="mb-3">
                            <label class="form-label">Date Terminated</label>
                            <input type="date" class="form-control" name="date_terminated"
                             max="<?php echo date('Y-m-d'); ?>" id="date_terminated">
                            <span id="error_date_terminated" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Outcome</label>
                            <select class="form-select" name="outcome">
                                <option value="" disabled selected>Select Outcome</option>
                                <option value="FT">FT-Full Term</option>
                                <option value="PT">PT-Pre Term</option>
                                <option value="FD">FD-Fetal Death</option>
                                <option value="AB">AB-Abortion/Miscarriage</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sex</label>
                            <div class="age-bracket-container">
                                <input type="radio" name="sex" value="M">
                                <label>Male</label>
                                <input type="radio" name="sex" value="F">
                                <label>Female</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Pregnancy Outcome</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding postpartum iron supplement maternal -->

    <!-- Modal for adding postpartum details maternal -->
    <div class="modal" tabindex="-1" id="addPostpartumModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Postpartum Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPostpartumForm">
                        <input type="hidden" id="postpartum_pregnancy_id" name="pregnancy_id">
                        <div class="">
                            <strong> DATE AND TIME OF DELIVERY</strong>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label"><strong>Date of Delivery</strong></label>
                            <input type="date" class="form-control" name="post_delivery_date"
                             max="<?php echo date('Y-m-d'); ?>" id="post_delivery_date">
                            <span id="error_post_delivery_date" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Time of Delivery</strong></label>
                            <input type="time" class="form-control" name="post_delivery_time">
                        </div>
                        <hr>
                        <div class="">
                            <strong>DATE & TIME INITIATED BREASTFEEDING</strong>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Date Breastfed</label>
                            <input type="date" class="form-control" name="breastfeeding_date"
                             max="<?php echo date('Y-m-d'); ?>" id="breastfeeding_date">
                            <span id="error_breastfeeding_date" class="text-danger"></span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time Breastfed</label>
                            <input type="time" class="form-control" name="breastfeeding_time">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Pregnancy Outcome</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding postpartum details maternal -->


    <!-- Modal for editing/updating maternal record -->
    <div class="modal fade" tabindex="-1" id="editModal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <form id="editMaternalForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Maternal Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="editModalBody" style="max-height: 70vh; overflow-y: auto;">
                        <input type="hidden" name="patient_id" id="edit_patient_id">

                        <div class="mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date of Registration</label>
                            <input type="date" class="form-control" name="date_of_registration"
                             max="<?php echo date('Y-m-d'); ?>" id="date_of_registration">
                            <span id="error_date_of_registration" class="text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Family Serial No.</label>
                            <input type="text" class="form-control" name="family_serial_number">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Socio-Economic Status</label>
                            <select class="form-select" name="socio_economic_status">
                                <option value="" disabled selected>Select Status</option>
                                <option value="1 - NHTS">1-NHTS</option>
                                <option value="2 - Non-NHTS">2-Non-NHTS</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="birth_date"
                             max="<?php echo date('Y-m-d'); ?>" id="birth_date">
                            <span id="error_birth_date" class="text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Age Bracket</label>
                            <div class="age-bracket-container mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="age_bracket" value="10-14" id="age_bracket_1">
                                    <label class="form-check-label" for="age_bracket_1">10–14 y/o</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="age_bracket" value="15-19" id="age_bracket_2">
                                    <label class="form-check-label" for="age_bracket_2">15–19 y/o</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="age_bracket" value="20-49" id="age_bracket_3">
                                    <label class="form-check-label" for="age_bracket_3">20–49 y/o</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" min="0" max="120" name="age" id="age" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email (optional)</label>
                            <input type="email" class="form-control" name="email">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" name="contact_number">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </div>

                    <div class="modal-footer bg-white" style="position: sticky; bottom: 0;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- Modal for editing/updating maternal record -->

    <!-- Modal for editing/updating postpartum record -->
    <div class="modal fade" tabindex="-1" id="editPostpartumModal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <form id="editPostpartumForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Postpartum Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="editPostpartumModalBody" style="max-height: 70vh; overflow-y: auto;">
                        <input type="hidden" name="patient_id" id="edit_post_patient_id">

                        <div class="mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="middle_name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date of Registration</label>
                            <input type="date" class="form-control" name="date_of_registration"
                             max="<?php echo date('Y-m-d'); ?>" id="date_of_registration">
                            <span id="error_date_of_registration" class="text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Family Serial No.</label>
                            <input type="text" class="form-control" name="family_serial_number">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Socio-Economic Status</label>
                            <select class="form-select" name="socio_economic_status">
                                <option value="" disabled selected>Select Status</option>
                                <option value="1 - NHTS">1-NHTS</option>
                                <option value="2 - Non-NHTS">2-Non-NHTS</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="birth_date"
                             max="<?php echo date('Y-m-d'); ?>" id="birth_date">
                            <span id="error_birth_date" class="text-danger"></span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Age Bracket</label>
                            <div class="age-bracket-container mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="age_bracket" value="10-14" id="age_bracket_1">
                                    <label class="form-check-label" for="age_bracket_1">10–14 y/o</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="age_bracket" value="15-19" id="age_bracket_2">
                                    <label class="form-check-label" for="age_bracket_2">15–19 y/o</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="age_bracket" value="20-49" id="age_bracket_3">
                                    <label class="form-check-label" for="age_bracket_3">20–49 y/o</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Age <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" min="0" max="120" name="age" id="age" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email (optional)</label>
                            <input type="email" class="form-control" name="email">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" name="contact_number">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </div>

                    <div class="modal-footer bg-white" style="position: sticky; bottom: 0;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- Modal for editing/updating postpartum record --></div>

    <!-- Modal for editing/updating infant record -->
    <div class="modal fade" tabindex="-1" id="editInfantModal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="editInfantForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Infant Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body" id="editInfantModalBody" style="max-height: 70vh; overflow-y: auto;">
                        <input type="hidden" name="patient_id" id="edit_infant_patient_id">

                        <div class="mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="infant_first_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" name="infant_middle_name">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="infant_last_name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date of Registration</label>
                            <input type="date" class="form-control" name="date_of_registration">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Family Serial No.</label>
                            <input type="text" class="form-control" name="family_serial_number">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Socio-Economic Status</label>
                            <select class="form-select" name="socio_economic_status">
                                <option value="" disabled selected>Select Status</option>
                                <option value="1 - NHTS">1-NHTS</option>
                                <option value="2 - Non-NHTS">2-Non-NHTS</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="infant_birth_date">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Complete name of Mother<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name_of_mother" placeholder="Surname, Firstname Middle Initial.">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email (optional)</label>
                            <input type="email" class="form-control" name="email">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" name="contact_number">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                    </div>

                    <div class="modal-footer bg-white" style="position: sticky; bottom: 0;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal for editing/updating infant record -->
    <!--=========================================MATERNAL MODAL ============================-->

    <!--========================================INFANT MODAL =================================-->

    <!-- Modal for viewing maternal record -->
    <div class="modal" tabindex="-1" id="myInfantModal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Infant Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="infantModalContent"></div>

                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for viewing maternal record -->

    <!-- Modal for adding referral date -->
    <div class="modal" tabindex="-1" id="addReferralModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Referral Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addReferralForm">
                        <input type="hidden" id="referral_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Referral Date:</label>
                            <input type="date" class="form-control" name="newborn_screening_referral">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Check-up</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding referral date -->

    <!-- Modal for adding date done -->
    <div class="modal" tabindex="-1" id="addDateDoneModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Date Done</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDateDoneForm">
                        <input type="hidden" id="dane_done_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Date Done:</label>
                            <input type="date" class="form-control" name="newborn_screening_done">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Date Done</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding date done -->

    <!-- Modal for adding date done -->
    <div class="modal" tabindex="-1" id="addTTStatusModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add TT Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addTTStatusForm">
                        <input type="hidden" id="tt_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">TT Status:</label>
                            <select class="form-select" name="cpab_tt_status">
                                <option value="" disabled selected>Select type</option>
                                <option value="td">Td</option>
                                <option value="td1">Td1</option>
                                <option value="td2">Td2</option>
                                <option value="td3">Td3</option>
                                <option value="td4">Td4</option>
                                <option value="td5">Td5</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="cpab_tt_date">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Date Done</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding date done -->

    <!-- Modal for adding date assessed -->
    <div class="modal" tabindex="-1" id="addDateAssessedModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Date Assessed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDateAssessedForm">
                        <input type="hidden" id="dane_assessed_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Date Assessed:</label>
                            <input type="date" class="form-control" name="cpab_tt_date_assessed">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Date Assessed</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding date assessed -->

    <!-- Modal for adding  exclusive feeding infant -->
    <div class="modal" tabindex="-1" id="addExlusiveFeedModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Exclusive Feeding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addExlusiveFeedForm">
                        <input type="hidden" id="exlusive_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Month Child was exclusively breastfed</label>
                            <select class="form-select" name="month_check">
                                <option value="" disabled selected>Select Month</option>
                                <option value="1st Month">1st Month</option>
                                <option value="2nd Month">2nd Month</option>
                                <option value="3rd Month">3rd Month</option>
                                <option value="4th Month">4th Month</option>
                                <option value="5th Month">5th Month</option>
                                <option value="6th Month">6th Month</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="month_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Exclusive Feeding</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding exclusive feeding infant  -->

    <!-- Modal for adding breastfeed infant  -->
    <div class="modal" tabindex="-1" id="addBreastfeedModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBreastfeedForm">
                        <input type="hidden" id="breastfeed_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Put a check for 6th Month</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="is_still_breastfeed" id="breastfeed_checkbox">
                                <label class="form-check-label" for="is_still_breastfeed">
                                    Check if reached 6th Month
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save FIM Status</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding breastfeed infant  -->

    <!-- Modal for adding  complimentary feeding -->
    <div class="modal" tabindex="-1" id="addComplimentaryModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Complementary Feeding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addComplimentaryForm">
                        <input type="hidden" id="complementary_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Complementary Feeding</label>
                            <select class="form-select" name="complementary_month_check">
                                <option value="" disabled selected>Select Month</option>
                                <option value="6th Month">6th Month</option>
                                <option value="7th Month">7th Month</option>
                                <option value="8th Month">8th Month</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="complementary_month_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Complementary Feeding</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding complimentary feeding  -->

    <!-- Modal for adding bcg infant  -->
    <div class="modal" tabindex="-1" id="addBCGModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">BCG</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBCGForm">
                        <input type="hidden" id="bcg_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Put a check if BCG was received</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="bcg_check" id="bcg_checkbox">
                                <label class="form-check-label">
                                    place a check if BCG was received
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date Received</label>
                                <input type="date" class="form-control" name="bcg_date">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save BCG Status</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding bcg infant  -->

    <!-- Modal for adding hepa -->
    <div class="modal" tabindex="-1" id="addHepaModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Hepa B1</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addHepaForm">
                        <input type="hidden" id="hepa_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Hepa B1</label>
                            <select class="form-select" name="hepaB_day">
                                <option value="" disabled selected>Select </option>
                                <option value="w/in 24 hours">w/in 24 hours</option>
                                <option value="More than 24 hours">More than 24 hours</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="hepaB_date">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Hepa B1</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding hepa -->

    <!-- Modal for adding pentavalent infant  -->
    <div class="modal" tabindex="-1" id="addPentavalentModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Pentavalent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPentavalentForm">
                        <input type="hidden" id="pentavalent_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Pentavalent</label>
                            <select class="form-select" name="pentavalent_type">
                                <option value="" disabled selected>Select</option>
                                <option value="Pentavalent 1">Pentavalent 1</option>
                                <option value="Pentavalent 2">Pentavalent 2</option>
                                <option value="Pentavalent 3">Pentavalent 3</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="pentavalent_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Pentavalent</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding pentavalent infant  -->

    <!-- Modal for adding opv infant  -->
    <div class="modal" tabindex="-1" id="addOpvModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add OPV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addOpvForm">
                        <input type="hidden" id="opv_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">OPV</label>
                            <select class="form-select" name="opv_type">
                                <option value="" disabled selected>Select</option>
                                <option value="Opv 1">Opv 1</option>
                                <option value="Opv 2">Opv 2</option>
                                <option value="Opv 3">Opv 3</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="opv_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save OPV</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding opv infant  -->

    <!-- Modal for adding ipv infant  -->
    <div class="modal" tabindex="-1" id="addIpvModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">IPV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addIpvForm">
                        <input type="hidden" id="ipv_patient_id" name="patient_id">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="ipv_1" id="ipv_checkbox">
                                <label class="form-check-label">
                                    place a check if IPV was received
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date:</label>
                                <input type="date" class="form-control" name="ipv_date">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save IPV Status</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding ipv infant  -->

    <!-- Modal for adding mcv infant  -->
    <div class="modal" tabindex="-1" id="addMcvModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add MCV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addMcvForm">
                        <input type="hidden" id="mcv_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">MCV</label>
                            <select class="form-select" name="mcv_type">
                                <option value="" disabled selected>Select</option>
                                <option value="MCV1 (AMV)">MCV1 (AMV)</option>
                                <option value="MCV2 (MMR)">MCV2 (MMR)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="mcv_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save MCV</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding mcv infant  -->

    <!-- Modal for adding fic infant  -->
    <div class="modal" tabindex="-1" id="addFicModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">FULLY IMMUNIZED CHILD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addFicForm">
                        <input type="hidden" id="fic_patient_id" name="patient_id">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="fic_check" id="fic_checkbox">
                                <label class="form-check-label">
                                    place a check if child was fully immunized
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date:</label>
                                <input type="date" class="form-control" name="fic_date">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save FIC Status</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding fic infant  -->

    <!-- Modal for adding rvv infant  -->
    <div class="modal" tabindex="-1" id="addRvvModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add ROTA VIRUS VACCINE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addRvvForm">
                        <input type="hidden" id="rvv_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">RVV</label>
                            <select class="form-select" name="rvv_type">
                                <option value="" disabled selected>Select</option>
                                <option value="Rota Virus Vaccine 1">Rota Virus Vaccine 1</option>
                                <option value="Rota Virus Vaccine 2">Rota Virus Vaccine 2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="rvv_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save RVV</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding rvv infant  -->

    <!-- Modal for adding pcv infant  -->
    <div class="modal" tabindex="-1" id="addPcvModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add PCV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPcvForm">
                        <input type="hidden" id="pcv_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">PCV</label>
                            <select class="form-select" name="pcv_type">
                                <option value="" disabled selected>Select</option>
                                <option value="PCV 1">PCV 1</option>
                                <option value="PCV 2">PCV 2</option>
                                <option value="PCV 3">PCV 3</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="pcv_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save PCV</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding pcv infant  -->

    <!-- Modal for adding VITAMIN A  infant  -->
    <div class="modal" tabindex="-1" id="addVitInfantModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Vitamin A</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addVitInfantForm">
                        <input type="hidden" id="vitamin_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Vitamin A</label>
                            <select class="form-select" name="vitamin_type">
                                <option value="" disabled selected>Select</option>
                                <option value="Vitamin A (6-11 Months)">6-11 Months</option>
                                <option value="Vitamin A (12-59 Months) Dose 1">(12-59 Months) Dose 1</option>
                                <option value="Vitamin A (12-59 Months) Dose 2">(12-59 Months) Dose 2</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="vitamin_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Vitamin A</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding VITAMIN A infant  -->

    <!-- Modal for adding iron infant  -->
    <div class="modal" tabindex="-1" id="addIronInfantModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Iron</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addIronInfantForm">
                        <input type="hidden" id="iron_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Iron</label>
                            <select class="form-select" name="iron_type">
                                <option value="" disabled selected>Select</option>
                                <option value="Iron (6-11 Month)">6-11 Months</option>
                                <option value="Iron (12-23 Month)">12-59 Months</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="iron_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Iron</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding iron infant  -->

    <!-- Modal for adding mnp infant  -->
    <div class="modal" tabindex="-1" id="addMnpModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add MNP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addMnpForm">
                        <input type="hidden" id="mnp_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">MNP</label>
                            <select class="form-select" name="mnp_type">
                                <option value="" disabled selected>Select</option>
                                <option value="MNP (6-11 Months)">6-11 Months</option>
                                <option value="MNP (12-23 Months)">12-23 Months</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="mnp_date">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save MNP</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding mnp infant  -->

    <!-- Modal for adding deworming infant  -->
    <div class="modal" tabindex="-1" id="addDewormingInfantModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">DEWORMING</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addDewormingInfantForm">
                        <input type="hidden" id="deworming_patient_id" name="patient_id">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="deworming_check" id="deworm_checkbox">
                                <label class="form-check-label">
                                    Deworming (12-59 months)
                                </label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date:</label>
                                <input type="date" class="form-control" name="deworming_date">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Deworming</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding deworming infant  -->

    <!-- Modal for adding infant assessment -->
    <div class="modal" tabindex="-1" id="addInfantScreenModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Newborn Measurement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addInfantScreenForm">
                        <input type="hidden" id="infant_assess_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Weight in grams:</label>
                            <input type="number" min="0" class="form-control" name="birth_weight">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Length/Height in cm: </label>
                            <input type="number" min="0" class="form-control" name="birth_height">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sex:</label>
                            <div class="age-bracket-container">
                                <input type="radio" name="sex" value="male">
                                <label>Male</label>
                                <input type="radio" name="sex" value="female">
                                <label>Female</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Newborn Measurement</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding infant assessment -->

    <!--========================================INFANT MODAL =================================-->

    <!--========================================POSTPARTUM MODAL =================================-->
    <!-- Modal for viewing postpartum record -->
    <div class="modal" tabindex="-1" id="myPostpartumModal">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Postpartum Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="postpartumModalContent"></div>

                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for viewing postpartum record -->

    <!-- Modal for adding postpartum checkup -->
    <div class="modal" tabindex="-1" id="addPostCheckupModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Postpartum Check-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPostpartumCheckupForm">
                        <input type="hidden" id="postpartum_checkup_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Post-Partum Visits</label>
                            <select class="form-select" name="checkup_visit">
                                <option disabled selected>Select visit</option>
                                <option value="Within 24hrs after delivery">Within 24hrs</option>
                                <option value="3rd day">3rd day</option>
                                <option value="7 to 14 days">7-14 days</option>
                                <option value="6 weeks">6 weeks</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Checkup Date</label>
                            <input type="date" class="form-control" name="post_checkup_date">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Check-up</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding postpartum checkup -->

    <!-- Modal for adding postpartum details  -->
    <div class="modal" tabindex="-1" id="addPostpartumDetailsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Postpartum Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPostpartumDetailsForm">
                        <input type="hidden" id="postpartum_details_patient_id" name="patient_id">
                        <div class="">
                            <strong> DATE AND TIME OF DELIVERY</strong>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label"><strong>Date of Delivery</strong></label>
                            <input type="date" class="form-control" name="post_delivery_date">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Time of Delivery</strong></label>
                            <input type="time" class="form-control" name="post_delivery_time">
                        </div>
                        <hr>
                        <div class="">
                            <strong>DATE & TIME INITIATED BREASTFEEDING</strong>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Date Breastfed</label>
                            <input type="date" class="form-control" name="breastfeeding_date">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Time Breastfed</label>
                            <input type="time" class="form-control" name="breastfeeding_time">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Pregnancy Outcome</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding postpartum details -->

    <!-- Modal for adding postpartum iron supplement -->
    <div class="modal" tabindex="-1" id="addPostpartumIronModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Iron Sulfate w/Folic Acid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPostpartumIronForm">
                        <input type="hidden" id="postpartum_iron_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Iron w/Folic Acid Month Given</label>
                            <select class="form-select" name="iron_folic_month_given">
                                <option disabled selected>Select month</option>
                                <option value="1st month postpartum">1st month postpartum</option>
                                <option value="2nd month postpartum">2nd month postpartum</option>
                                <option value="3rd month postpartum">3rd month postpartum</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Given</label>
                            <input type="date" class="form-control" name="iron_folic_date_given">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Tablets Given</label>
                            <input type="number" min="0" class="form-control" name="tablets_given">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Iron Supplement</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding postpartum iron supplement -->

    <!-- Modal for adding vitamin supplement maternal -->
    <div class="modal" tabindex="-1" id="addPostVitaminModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Vitamin A Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPostVitaminForm">
                        <input type="hidden" id="post_vitamin_patient_id" name="patient_id">
                        <div class="mb-3">
                            <label class="form-label">Vitamin A</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="vitamin_a" id="vitamin_checkbox">
                                <label class="form-check-label">
                                    check if Vitamin A was given
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Given</label>
                            <input type="date" class="form-control" name="vitamin_a_date">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Vitamin A Supplement</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"></i>Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal for adding vitamin supplement maternal -->

    <!--========================================POSTPARTUM MODAL =================================-->

    <!--========================================EDIT MATERNAL MODALs =================================-->
    <!-- Edit Checkup Modal -->
<div class="modal fade" id="editCheckupModal" tabindex="-1" aria-labelledby="editCheckupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCheckupModalLabel">Edit Prenatal Check-up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCheckupForm">
                    <input type="hidden" id="edit_checkup_id" name="checkup_id">
                    <input type="hidden" id="edit_pregnancy_id" name="pregnancy_id">
                    
                    <div class="mb-3">
                        <label for="edit_trimester" class="form-label">Trimester</label>
                        <select class="form-select" id="edit_trimester" name="trimester" required>
                            <option value="">Select Trimester</option>
                            <option value="1st">1st Trimester</option>
                            <option value="2nd">2nd Trimester</option>
                            <option value="3rd">3rd Trimester</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_checkup_date" class="form-label">Check-up Date</label>
                        <input type="date" class="form-control" id="edit_checkup_date" name="checkup_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditCheckupBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Immunization Modal -->
<div class="modal fade" id="editImmunizationModal" tabindex="-1" aria-labelledby="editImmunizationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editImmunizationModalLabel">Edit Immunization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editImmunizationForm">
                    <input type="hidden" id="edit_immunization_id" name="maternal_immunization_id">
                    <input type="hidden" id="edit_immunization_pregnancy_id" name="pregnancy_id">
                    
                    <div class="mb-3">
                        <label for="edit_immunization_type" class="form-label">Immunization Type</label>
                        <select class="form-select" id="edit_immunization_type" name="immunization_type" required>
                            <option value="">Select Type</option>
                            <option value="Td1/TT1">Td1/TT1</option>
                            <option value="Td2/TT2">Td2/TT2</option>
                            <option value="Td3/TT3">Td3/TT3</option>
                            <option value="Td4/TT4">Td4/TT4</option>
                            <option value="Td5/TT5">Td5/TT5</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_immunization_date" class="form-label">Immunization Date</label>
                        <input type="date" class="form-control" id="edit_immunization_date" name="immunization_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditImmunizationBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Iron Supplement Modal -->
<div class="modal fade" id="editIronModal" tabindex="-1" aria-labelledby="editIronModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIronModalLabel">Edit Iron Supplement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editIronForm">
                    <input type="hidden" id="edit_iron_supplement_id" name="maternal_supplement_id">
                    <input type="hidden" id="edit_iron_pregnancy_id" name="pregnancy_id">
                    <input type="hidden" name="supplement_type" value="Iron Sulfate w/Folic Acid">
                    
                    <div class="mb-3">
                        <label for="edit_iron_trimester" class="form-label">Trimester</label>
                        <select class="form-select" id="edit_iron_trimester" name="supp_trimester" required>
                            <option value="">Select Trimester</option>
                            <option value="1st visit (1st Tri)">1st Visit (1st Trimester)</option>
                            <option value="2nd visit (2nd Tri)">2nd Visit (2nd Trimester)</option>
                            <option value="3rd visit (3rd Tri)">3rd Visit (3rd Trimester)</option>
                            <option value="4th visit (3rd Tri)">4th Visit (3rd Trimester)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_iron_tablets_given" class="form-label">Tablets Given</label>
                        <input type="number" class="form-control" id="edit_iron_tablets_given" name="supp_tablets_given" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_iron_date_supp" class="form-label">Date Given</label>
                        <input type="date" class="form-control" id="edit_iron_date_supp" name="date_supp" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditIronBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Calcium Supplement Modal -->
<div class="modal fade" id="editCalciumModal" tabindex="-1" aria-labelledby="editCalciumModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCalciumModalLabel">Edit Calcium Supplement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCalciumForm">
                    <input type="hidden" id="edit_calcium_supplement_id" name="maternal_supplement_id">
                    <input type="hidden" id="edit_calcium_pregnancy_id" name="pregnancy_id">
                    <input type="hidden" name="supplement_type" value="Calcium Carbonate">
                    
                    <div class="mb-3">
                        <label for="edit_calcium_trimester" class="form-label">Trimester</label>
                        <select class="form-select" id="edit_calcium_trimester" name="supp_trimester" required>
                            <option value="">Select Trimester</option>
                            <option value="1st visit (1st Tri)">1st Visit (1st Trimester)</option>
                            <option value="2nd visit (2nd Tri)">2nd Visit (2nd Trimester)</option>
                            <option value="3rd visit (3rd Tri)">3rd Visit (3rd Trimester)</option>
                            <option value="4th visit (3rd Tri)">4th Visit (3rd Trimester)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_calcium_tablets_given" class="form-label">Tablets Given</label>
                        <input type="number" class="form-control" id="edit_calcium_tablets_given" name="supp_tablets_given" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_calcium_date_supp" class="form-label">Date Given</label>
                        <input type="date" class="form-control" id="edit_calcium_date_supp" name="date_supp" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditCalciumBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Postpartum Checkup Modal -->
<div class="modal fade" id="editPostCheckupModal" tabindex="-1" aria-labelledby="editPostCheckupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPostCheckupModalLabel">Edit Postpartum Checkup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPostCheckupForm">
                    <input type="hidden" id="edit_post_checkup_id" name="checkup_id">
                    <input type="hidden" id="edit_post_pregnancy_id" name="pregnancy_id">
                    
                    <div class="mb-3">
                        <label for="edit_checkup_visit" class="form-label">Checkup Visit</label>
                        <select class="form-select" id="edit_checkup_visit" name="checkup_visit" required>
                            <option value="">Select Visit Type</option>
                            <option value="Within 24hrs after delivery">Within 24hrs after delivery</option>
                            <option value="3rd day">3rd day</option>
                            <option value="7 to 14 days">7 to 14 days</option>
                            <option value="6 weeks">6 weeks</option>
                            <option value="Within 1 week after delivery">Within 1 week after delivery</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_post_checkup_date" class="form-label">Checkup Date</label>
                        <input type="date" class="form-control" id="edit_post_checkup_date" name="post_checkup_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditPostCheckupBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Postpartum Iron Supplement Modal -->
<div class="modal fade" id="editPostIronModal" tabindex="-1" aria-labelledby="editPostIronModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPostIronModalLabel">Edit Postpartum Iron Supplement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPostIronForm">
    <input type="hidden" id="edit_post_supp_id" name="post_supp_id">
    <input type="hidden" id="edit_post_iron_patient_id" name="patient_id">
    
    <div class="mb-3">
        <label for="edit_iron_folic_month_given" class="form-label">Month Given</label>
        <select id="edit_iron_folic_month_given" name="iron_folic_month_given" class="form-control" required>
            <option value="">Select Month</option>
            <option value="1st month postpartum">1st month postpartum</option>
            <option value="2nd month postpartum">2nd month postpartum</option>
            <option value="3rd month postpartum">3rd month postpartum</option>
        </select>
    </div>
    
    <div class="mb-3">
        <label for="edit_iron_folic_date_given" class="form-label">Date Given</label>
        <input type="date" id="edit_iron_folic_date_given" name="iron_folic_date_given" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label for="edit_post_tablets_given" class="form-label">Tablets Given</label>
        <input type="number" id="edit_post_tablets_given" name="tablets_given" class="form-control" required>
    </div>
</form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditPostIronBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>


    <!--========================================EDIT MATERNAL MODALs =================================-->

     <!--========================================EDIT POSTPARTUM MODALs =================================-->

     <!-- Edit Postpartum Checkup Modal -->
<div class="modal fade" id="editPostpartumCheckupModal" tabindex="-1" aria-labelledby="editPostpartumCheckupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPostpartumCheckupModalLabel">Edit Postpartum Checkup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPostpartumCheckupForm">
                    <input type="hidden" id="edit_postpartum_checkup_id" name="checkup_id">
                    <input type="hidden" id="edit_postpartum_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_postpartum_checkup_visit" class="form-label">Checkup Visit</label>
                        <select class="form-select" id="edit_postpartum_checkup_visit" name="checkup_visit" required>
                            <option value="">Select Visit Type</option>
                            <option value="Within 24hrs after delivery">Within 24hrs after delivery</option>
                            <option value="3rd day">3rd day</option>
                            <option value="7 to 14 days">7 to 14 days</option>
                            <option value="6 weeks">6 weeks</option>
                            <option value="Within 1 week after delivery">Within 1 week after delivery</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_postpartum_checkup_date" class="form-label">Checkup Date</label>
                        <input type="date" class="form-control" id="edit_postpartum_checkup_date" name="post_checkup_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditPostpartumCheckupBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editPostIronModal" tabindex="-1" aria-labelledby="editPostIronModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPostIronModalLabel">Edit Postpartum Iron Supplement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPostIronForm">
                    <input type="hidden" id="edit_post_supp_id" name="post_supp_id">
                    <input type="hidden" id="edit_post_iron_pregnancy_id" name="pregnancy_id">
                    
                    <div class="mb-3">
                        <label for="edit_iron_folic_month_given" class="form-label">Month Given</label>
                        <select class="form-select" id="edit_iron_folic_month_given" name="iron_folic_month_given" required>
                            <option value="">Select Month</option>
                            <option value="1st month postpartum">1st month postpartum</option>
                            <option value="2nd month postpartum">2nd month postpartum</option>
                            <option value="3rd month postpartum">3rd month postpartum</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_iron_folic_date_given" class="form-label">Date Given</label>
                        <input type="date" class="form-control" id="edit_iron_folic_date_given" name="iron_folic_date_given" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_post_tablets_given" class="form-label">Tablets Given</label>
                        <input type="number" class="form-control" id="edit_post_tablets_given" name="tablets_given" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditPostIronBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

      <!--========================================EDIT POSTPARTUM MODALs =================================-->

      <!--========================================EDIT INFANT MODALs =================================-->

      <!-- Edit Exclusive Breastfeed Modal -->
<div class="modal fade" id="editExlusiveFeedModal" tabindex="-1" aria-labelledby="editExlusiveFeedModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExlusiveFeedModalLabel">Edit Exclusive Breastfeeding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editExlusiveFeedForm">
                    <input type="hidden" id="edit_exclusive_breastfeed_id" name="infant_exclusively_breastfed_id">
                    <input type="hidden" id="edit_exlusive_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_month_check" class="form-label">Month Check</label>
                        <select class="form-select" id="edit_month_check" name="month_check" required>
                            <option value="">Select Month</option>
                            <option value="1st Month">1st Month</option>
                            <option value="2nd Month">2nd Month</option>
                            <option value="3rd Month">3rd Month</option>
                            <option value="4th Month">4th Month</option>
                            <option value="5th Month">5th Month</option>
                            <option value="6th Month">6th Month</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_month_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_month_date" name="month_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Complementary Feeding Modal -->
<div class="modal fade" id="editComplementaryModal" tabindex="-1" aria-labelledby="editComplementaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editComplementaryModalLabel">Edit Complementary Feeding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editComplementaryForm">
                    <input type="hidden" id="edit_complementary_feeding_id" name="complementary_feeding_id">
                    <input type="hidden" id="edit_complementary_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_complementary_month_check" class="form-label">Month Check</label>
                        <select class="form-select" id="edit_complementary_month_check" name="complementary_month_check" required>
                            <option value="">Select Month</option>
                            <option value="6th Month">6th Month</option>
                            <option value="7th Month">7th Month</option>
                            <option value="8th Month">8th Month</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_complementary_month_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_complementary_month_date" name="complementary_month_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Pentavalent Modal -->
<div class="modal fade" id="editPentavalentModal" tabindex="-1" aria-labelledby="editPentavalentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPentavalentModalLabel">Edit Pentavalent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPentavalentForm">
                    <input type="hidden" id="edit_pentavalent_id" name="pentavalent_id">
                    <input type="hidden" id="edit_pentavalent_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_pentavalent_type" class="form-label">Pentavalent Type</label>
                        <select class="form-select" id="edit_pentavalent_type" name="pentavalent_type" required>
                            <option value="">Select Type</option>
                            <option value="Pentavalent 1">Pentavalent 1</option>
                            <option value="Pentavalent 2">Pentavalent 2</option>
                            <option value="Pentavalent 3">Pentavalent 3</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_pentavalent_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_pentavalent_date" name="pentavalent_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit OPV Modal -->
<div class="modal fade" id="editOpvModal" tabindex="-1" aria-labelledby="editOpvModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOpvModalLabel">Edit OPV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editOpvForm">
                    <input type="hidden" id="edit_opv_id" name="opv_id">
                    <input type="hidden" id="edit_opv_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_opv_type" class="form-label">OPV Type</label>
                        <select class="form-select" id="edit_opv_type" name="opv_type" required>
                            <option value="">Select Type</option>
                            <option value="Opv 1">OPV 1</option>
                            <option value="Opv 2">OPV 2</option>
                            <option value="Opv 3">OPV 3</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_opv_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_opv_date" name="opv_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update OPV</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit MCV Modal -->
<div class="modal fade" id="editMcvModal" tabindex="-1" aria-labelledby="editMcvModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMcvModalLabel">Edit MCV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMcvForm">
                    <input type="hidden" id="edit_mcv_id" name="mcv_id">
                    <input type="hidden" id="edit_mcv_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_mcv_type" class="form-label">MCV Type</label>
                        <select class="form-select" id="edit_mcv_type" name="mcv_type" required>
                            <option value="">Select Type</option>
                            <option value="MCV1 (AMV)">MCV1 (AMV)</option>
                            <option value="MCV2 (MMR)">MCV2 (MMR)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_mcv_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_mcv_date" name="mcv_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit RVV Modal -->
<div class="modal fade" id="editRvvModal" tabindex="-1" aria-labelledby="editRvvModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRvvModalLabel">Edit Rota Virus Vaccine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editRvvForm">
                    <input type="hidden" id="edit_rvv_id" name="rvv_id">
                    <input type="hidden" id="edit_rvv_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_rvv_type" class="form-label">RVV Type</label>
                        <select class="form-select" id="edit_rvv_type" name="rvv_type" required>
                            <option value="">Select Type</option>
                            <option value="Rota Virus Vaccine 1">Rota Virus Vaccine 1</option>
                            <option value="Rota Virus Vaccine 2">Rota Virus Vaccine 2</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_rvv_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_rvv_date" name="rvv_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit PCV Modal -->
<div class="modal fade" id="editPcvModal" tabindex="-1" aria-labelledby="editPcvModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPcvModalLabel">Edit PCV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPcvForm">
                    <input type="hidden" id="edit_pcv_id" name="pcv_id">
                    <input type="hidden" id="edit_pcv_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_pcv_type" class="form-label">PCV Type</label>
                        <select class="form-select" id="edit_pcv_type" name="pcv_type" required>
                            <option value="">Select Type</option>
                            <option value="PCV 1">PCV 1</option>
                            <option value="PCV 2">PCV 2</option>
                            <option value="PCV 3">PCV 3</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_pcv_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_pcv_date" name="pcv_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Vitamin A Modal -->
<div class="modal fade" id="editVitaminModal" tabindex="-1" aria-labelledby="editVitaminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVitaminModalLabel">Edit Vitamin A</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editVitaminForm">
                    <input type="hidden" id="edit_vitamin_id" name="vitamin_a_infant_id">
                    <input type="hidden" id="edit_vitamin_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_vitamin_type" class="form-label">Vitamin Type</label>
                        <select class="form-select" id="edit_vitamin_type" name="vitamin_type" required>
                            <option value="">Select Type</option>
                            <option value="Vitamin A (6-11 Months)">Vitamin A (6-11 Months)</option>
                            <option value="Vitamin A (12-59 Months) Dose 1">Vitamin A (12-59 Months) Dose 1</option>
                            <option value="Vitamin A (12-59 Months) Dose 2">Vitamin A (12-59 Months) Dose 2</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_vitamin_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_vitamin_date" name="vitamin_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Iron Infant Modal -->
<div class="modal fade" id="editIronInfantModal" tabindex="-1" aria-labelledby="editIronInfantModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIronInfantModalLabel">Edit Iron</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editIronInfantForm">
                    <input type="hidden" id="edit_iron_id" name="iron_infant_id">
                    <input type="hidden" id="edit_iron_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_iron_type" class="form-label">Iron Type</label>
                        <select class="form-select" id="edit_iron_type" name="iron_type" required>
                            <option value="">Select Type</option>
                            <option value="Iron (6-11 Month)">Iron (6-11 Month)</option>
                            <option value="Iron (12-23 Month)">Iron (12-23 Month)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_iron_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_iron_date" name="iron_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit MNP Modal -->
<div class="modal fade" id="editMnpModal" tabindex="-1" aria-labelledby="editMnpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMnpModalLabel">Edit MNP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMnpForm">
                    <input type="hidden" id="edit_mnp_id" name="mnp_id">
                    <input type="hidden" id="edit_mnp_patient_id" name="patient_id">
                    
                    <div class="mb-3">
                        <label for="edit_mnp_type" class="form-label">MNP Type</label>
                        <select class="form-select" id="edit_mnp_type" name="mnp_type" required>
                            <option value="">Select Type</option>
                            <option value="MNP (6-11 Months)">MNP (6-11 Months)</option>
                            <option value="MNP (12-23 Months)">MNP (12-23 Months)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_mnp_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_mnp_date" name="mnp_date" required>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

      <!--========================================EDIT INFANT MODALs =================================-->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/target_quota.js"></script>
    <script src="../assets/js/filter_function.js"></script>
    <script src="../assets/js/filter_infant.js"></script>
    <script src="../assets/js/filter_postpartum.js"></script>
    <script src="../assets/js/maternal_script.js"></script>
    <script src="../assets/js/view_maternal_script.js"></script>
    <script src="../assets/js/edit_maternal_script.js"></script>
    <script src="../assets/js/delete_btn_maternal.js"></script>
    <script src="../assets/js/pretanal_followup_mat.js"></script>
    <script src="../assets/js/immunization_followup_mat.js"></script>
    <script src="../assets/js/iron_supp_mat.js"></script>
    <script src="../assets/js/calcium_supp_mat.js"></script>
    <script src="../assets/js/iodine_supp_mat.js"></script>
    <script src="../assets/js/fim_stat.js"></script>
    <script src="../assets/js/post_checkup.js"></script>
    <script src="../assets/js/vitamin_mat.js"></script>
    <script src="../assets/js/post_iron.js"></script>
    <script src="../assets/js/preg_outcome.js"></script>
    <script src="../assets/js/delivery_birth.js"></script>
    <script src="../assets/js/place_birth.js"></script>
    <script src="../assets/js/place_non_health.js"></script>
    <script src="../assets/js/get_bmi.js"></script>
    <script src="../assets/js/disease_mat.js"></script>
    <script src="../assets/js/laboratory_mat.js"></script>
    <script src="../assets/js/cbc_lab.js"></script>
    <script src="../assets/js/given_iron.js"></script>
    <script src="../assets/js/post_details.js"></script>
    <script src="../assets/js/hiv_mat.js"></script>
    <script src="../assets/js/view_infant_script.js"></script>
    <script src="../assets/js/exclusive_feeding_dynamic.js"></script>
    <script src="../assets/js/infant_dynamic.js"></script>
    <script src="../assets/js/referral_infant.js"></script>
    <script src="../assets/js/date_Done.js"></script>
    <script src="../assets/js/tt_status.js"></script>
    <script src="../assets/js/date_assessed.js"></script>
    <script src="../assets/js/breastfeed1.js"></script>
    <script src="../assets/js/breastfeed2.js"></script>
    <script src="../assets/js/breastfeed_checkbox.js"></script>
    <script src="../assets/js/bcg.js"></script>
    <script src="../assets/js/hepaB.js"></script>
    <script src="../assets/js/pentavalent.js"></script>
    <script src="../assets/js/opv_infant.js"></script>
    <script src="../assets/js/ipv_infant.js"></script>
    <script src="../assets/js/mcv_infant.js"></script>
    <script src="../assets/js/fic_infant.js"></script>
    <script src="../assets/js/rvv_infant.js"></script>
    <script src="../assets/js/pcv_infant.js"></script>
    <script src="../assets/js/vitamin_infant.js"></script>
    <script src="../assets/js/iron_infant.js"></script>
    <script src="../assets/js/mnp_infant.js"></script>
    <script src="../assets/js/deworming_infant.js"></script>
    <script src="../assets/js/infant_assessment.js"></script>
    <script src="../assets/js/view_postpartum_script.js"></script>
    <script src="../assets/js/postpartum_checkup.js"></script>
    <script src="../assets/js/postpartum_details2.js"></script>
    <script src="../assets/js/postpartum_iron.js"></script>
    <script src="../assets/js/postpartum_vitamin.js"></script>
    <script src="../assets/js/infant_from_maternal.js"></script>
    <script src="../assets/js/appointment_page.js"></script>
   <script src="../assets/js/reportScript.js"></script> 
   <script src="../assets/js/add_appointment_form.js"></script> 
   <script src="../assets/js/validation_error_maternal.js"></script> 
   <script src="../assets/js/edit_postpartum.js"></script> 
   <script src="../assets/js/date_validation.js"></script> 
   <script src="../assets/js/date_validation_infant.js"></script> 
   <script src="../assets/js/date_validation_modals.js"></script> 
   <script src="../assets/js/edit_infant.js"></script> 
   <script src="../assets/js/edit_prenatal_checkup.js"></script> 
   
    <script>
        function loadPage(page, clickedElement) {
            const mainContent = document.getElementById("main-content");
            mainContent.style.opacity = "0.5";
            document.querySelectorAll('.sidebar-nav-link').forEach(link => {
                link.classList.remove('active');
            });

            // Add active class to the clicked link (kung provided)
            if (clickedElement) {
                clickedElement.classList.add('active');
            }

            fetch(page)
                .then(response => {
                    // Error handling kung hindi ok ang response (e.g., 404 o server error)
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    // Convert response body to text (HTML)
                    return response.text();
                })
                .then(data => {
                    // Palitan ang innerHTML ng main content area ng bagong na-fetch na HTML
                    mainContent.innerHTML = data;
                    mainContent.style.opacity = "1";
                    mainContent.classList.add("fade-in");
                    setTimeout(() => setupFormNavigation(), 50);

                    // Search function for maternal patients
                    if (page.includes("view_maternal_patient.php")) {
                        if (typeof fetchData === 'function') {
                            initialMaternalSearch();
                            fetchData();
                        }
                    }

                    // Search function for infant patients
                    if (page.includes("view_infant_patient.php")) {
                        if (typeof fetchInfantData === 'function') {
                            initialInfantSearch();
                            fetchInfantData();
                        }
                    }

                    // Search function for postpartum records
                    if (page.includes("view_postpartum_record.php")) {
                        if (typeof fetchPostpartumData === 'function') {
                            initialPostpartumSearch();
                            fetchPostpartumData();
                        }
                    }

                    // Appointment Management page
                    if (page.includes("appointment_page.php")) {
                        if (typeof loadAppointments === 'function') {
                            console.log("Loading Appointments page...");
                            loadAppointments();
                        } else {
                            console.warn("loadAppointments() not found");
                        }
                    }

                    if (page.includes("home.php")) {
                    // Wait a bit for DOM to be ready, then load chart
                    setTimeout(() => {
                        if (typeof loadImmunizationChart === 'function') {
                            loadImmunizationChart();
                        } else {
                            console.warn("loadImmunizationChart() not found");
                        }
                    }, 100);
                }

                })
                .catch(error => {
                    // Kung may error, ipakita ang alert card sa main content area
                    mainContent.innerHTML = `
                        <div class="content-card text-center">
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Error loading page. Please try again.
                            </div>
                        </div>`;
                    // Bawiin dimming kahit may error para bumalik sa normal state
                    mainContent.style.opacity = "1";
                    // Log sa console para sa debugging
                    console.error("Error:", error);
                });
        }

        function loadHomePage() {
            const homeLink = document.querySelector('.sidebar-nav-link[onclick*="loadHomePage"]');
            loadPage('home.php', homeLink);
        }
 
        document.addEventListener('DOMContentLoaded', function() {
            loadHomePage();
        });

        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to logout.php
                    window.location.href = '../module/logout.php';
                }
            });
        }
    </script>

    <!--Sweet alert for adding Patient-->
    <?php
    if (isset($_SESSION['statusMessage']) && $_SESSION['statusMessage'] != '') {
        echo $_SESSION['statusMessage'];
    ?>
        <script>
            Swal.fire({
                title: '<?php echo $_SESSION['statusMessage']; ?>',
                icon: '<?php echo $_SESSION['statusMessageCode']; ?>',
                confirmButtonText: "Okay",
                confirmButtonColor: "#3085d6"
            });
        </script>
    <?php
        unset($_SESSION['statusMessage']);
    }
    ?>
    <!--Sweet alert for adding Patient-->

</body>

</html>