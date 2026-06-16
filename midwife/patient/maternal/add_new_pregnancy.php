<?php
    require_once "../../../module/db.config.php";

    $patient_id = isset($_GET['patient_id']) && $_GET['patient_id'] !== '' ? intval($_GET['patient_id']) : 0;

    if ($patient_id <=0) {
        header("Location: add_maternal.php");
        exit;
    }

    $stmt_patient = $conn->prepare("SELECT patient_id, first_name,
     last_name FROM patient WHERE
     patient_id = ?");
    $stmt_patient->bind_param("i", $patient_id);
    $stmt_patient->execute();
    $patient = $stmt_patient->get_result()->fetch_assoc();
    $stmt_patient->close();
?>

<div class="main-container">
    <form action="/rhusystem/midwife/patient/maternal/save_new_pregnancy.php" method="POST">
        <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pregnancy-delivery-tab">Pregnancy & Delivery</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#health-wellness-tab">Health Screening & Wellness</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#immunization-supplement-tab">Immunization & Supplements</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#postpartum-care-tab">Postpartum Care</a>
            </li>
        </ul>

        <div class="tab-content content-main-div">

            <!--Patient Details-->
            <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
            <div class="alert alert-info">
                Adding new pregnancy for: <strong><?= htmlspecialchars($patient['last_name']. ', '. $patient['first_name']) ?></strong>
            </div>
            <!--Patient Details-->

            <!--Pregnancy & Delivery-->
            <div class="tab-pane fade show active" id="pregnancy-delivery-tab">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        PREGNANCY METRICS
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row mt-2">
                                <div class="col ">
                                    <label class="form-label">Last Menstrual Period (LMP)</label>
                                    <input type="date" class="form-control" name="lmp">
                                </div>
                                <div class="col">
                                    <label class="form-label">Estimated Date of Confinement(EDC) </label>
                                    <input type="date" class="form-control" name="edc">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label class="form-label">Gravidity</label>
                                    <input type="number" name="gravidity" min="0" class="form-control">
                                </div>
                                <div class="col">
                                    <label class="form-label">Parity</label>
                                    <input type="number" name="parity" min="0" class="form-control">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        PREGNANCY OUTCOME
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">

                            <div class="col">
                                <label class="form-label">Date Terminated</label>
                                <input type="date" class="form-control" name="date_terminated">
                            </div>
                            <div class="col">
                                <label class="form-label">Outcome</label>
                                <select class="form-select" name="outcome">
                                    <option value="" disabled selected>Select Outcome</option>
                                    <option value="FT">FT-Full Term</option>
                                    <option value="PT">PT-Pre Term</option>
                                    <option value="FD">FD-Fetal Death</option>
                                    <option value="AB">AB-Abortion/Miscarriage</option>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label">Sex</label>
                                <div class="age-bracket-container">
                                    <input type="radio" name="sex" value="M">
                                    <label>Male</label>
                                    <input type="radio" name="sex" value="F">
                                    <label>Female</label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        BIRTH INFORMATION
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col">
                                    <label class="form-label">Type of Delivery</label>
                                    <select class="form-select" name="delivery_type">
                                        <option value="" disabled selected>Select delivery type</option>
                                        <option value="CS">CS-Caesarian Section</option>
                                        <option value="VD">VD-Vaginal Delivery</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Weight Classification</label>
                                    <select class="form-select" name="birth_weight_classification">
                                        <option value="" disabled selected>Select classification</option>
                                        <option value="Low">Low(< 2,500g) </option>
                                        <option value="Normal">Normal(≥ 2,500g)</option>
                                        <option value="Unknown">Unknown</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label class="form-label">Birth Weight(in grams)</label>
                                    <input type="number" name="birth_weight" min="0" step="0.01" placeholder="leave blank if unknown" class="form-control">
                                </div>
                                <div class="col">
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
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        PLACE OF DELIVERY
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col">
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
                                <div class="col">
                                    <label class="form-label">Health Facility Name</label>
                                    <input type="text" class="form-control" name="health_facility_name">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label class="form-label">Ownership</label>
                                    <select class="form-select" name="ownership">
                                        <option value="" disabled selected>Select ownership</option>
                                        <option value="Public">Public</option>
                                        <option value="Private">Private</option>
                                    </select>
                                </div>
                                <div class="col mt-5">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="bemonc_cemonc_capable" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            place a check if BEmONC/CEmONC capable
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col">
                                    <label class="form-label">Non-Health Facility</label>
                                    <select class="form-select" name="non_health_facility_type">
                                        <option value="" disabled selected>Select non-health facility </option>
                                        <option value="Home">1-Home</option>
                                        <option value="Others">2-Others(including emergency)</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Non-Health Facility Name</label>
                                    <input type="text" class="form-control" name="non_health_facility_name" placeholder="leave blank if not applicable">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control " name="remarks" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="d-flex justify-content-end gap-2 mb-3 ">
                    <button class="btn btn-secondary js-back_button col-md-2" type="button" onclick="
                                loadPage('addPatient_LandingPg.php');">Back</button>
                    <button class="btn btn-primary js-next_button col-md-2" name="next_btn" type="button" >Next</button>
                </div>
            </div>
            <!--Pregnancy & Delivery-->

            <!--Health & Wellness-->
            <div class="tab-pane fade" id="health-wellness-tab">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        PRENATAL CHECK-UP
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mt-2 align-items-start">
                                <div class="col">
                                    <label class="form-label">Trimester</label>
                                    <select class="form-select" name="trimester">
                                        <option value="" disabled selected>Select trimester</option>
                                        <option value="1st">1st Tri</option>
                                        <option value="2nd">2nd Tri</option>
                                        <option value="3rd">3rd Tri</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Check-up Date</label>
                                    <input type="date" class="form-control" name="checkup_date">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        NUTRITIONAL ASSESSMENT
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-4 mt-2 align-items-start">
                                <div class="col">
                                    <label class="form-label">BMI Classification</label>
                                    <select class="form-select" name="bmi_class">
                                        <option value="" disabled selected>Select bmi classification</option>
                                        <option value="low_bmi">Low</option>
                                        <option value="normal_bmi">Normal</option>
                                        <option value="high_bmi">High</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">BMI</label>
                                    <input type="number" class="form-control" min="0" step="0.01" name="bmi">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label class="form-label">Deworming</label>
                                    <select class="form-select" name="deworming_status">
                                        <option value="" disabled selected>Select trimester</option>
                                        <option value="2nd Tri">2nd Tri</option>
                                        <option value="3rd Tri">3rd Tri</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Deworming Date</label>
                                    <input type="date" class="form-control" name="deworming_date_given">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        INFECTIOUS DISEASE SURVEILLANCE
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col-md-3">
                                    <label class="form-label">Syphilis Screening</label>
                                    <input type="date" class="form-control" name="syphilis_date">
                                </div>
                                <div class="col-md-3 mt-5">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="syphilis_screening" value="positive">
                                        <label class="form-check-label">Positive</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="syphilis_screening" value="negative">
                                        <label class="form-check-label">Negative</label>
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <label class="form-label">Note:</label>
                                    <input type="text" class="form-control" name="syphilis_screening_remarks">
                                </div>
                            </div>
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col-md-3">
                                    <label class="form-label">Hepatitis B Screening</label>
                                    <input type="date" class="form-control" name="hepatitisB_date">
                                </div>
                                <div class="col-md-3 mt-5">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="hepatitis_b_screening" value="positive">
                                        <label class="form-check-label">Positive</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="hepatitis_b_screening" value="negative">
                                        <label class="form-check-label">Negative</label>
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <label class="form-label">Note:</label>
                                    <input type="text" class="form-control" name="hepatitis_b_screening_remarks">
                                </div>
                            </div>
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col-md-3">
                                    <label class="form-label">HIV Screening</label>
                                    <input type="date" class="form-control" name="hiv_date">
                                </div>
                                <div class="col-md-3 mt-5">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="hiv_screening" value="positive">
                                        <label class="form-check-label">Positive</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="hiv_screening" value="negative">
                                        <label class="form-check-label">Negative</label>
                                    </div>
                                </div>
                                <div class="col-md-6 ">
                                    <label class="form-label">Note:</label>
                                    <input type="text" class="form-control" name="hiv_screening_remarks">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        LABORATORY SCREENING
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col-md-3">
                                    <label class="form-label">Gestational Diabetes </label>
                                    <input type="date" class="form-control" name="gestational_diabetes_date">
                                </div>
                                <div class="col-md-3 mt-5">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gestational_diabetes_screening" value="positive">
                                        <label class="form-check-label">Positive</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gestational_diabetes_screening" value="negative">
                                        <label class="form-check-label">Negative</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Note:</label>
                                    <input type="text" class="form-control" name="diabetes_remarks">
                                </div>
                            </div>
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col-md-3">
                                    <label class="form-label">CBC/Hgb&Hct Date Screened</label>
                                    <input type="date" class="form-control" name="cbc_hgb_hct_date">
                                </div>
                                <div class="col-md-3 mt-5">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="anemia_status" value="with anemia">
                                        <label class="form-check-label">w/anemia</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="anemia_status" value="w/o anemia">
                                        <label class="form-check-label">w/o anemia</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CBC/Hgb&Hct Count</label>
                                    <input type="number" step="0.01" class="form-control" name="cbc_hgb_hct_count">
                                </div>
                    
                                <div class="col-md-3 ">
                                    <label class="form-label">Note:</label>
                                    <input type="text" class="form-control" name="anemia_status_remarks">
                                </div>
                            </div>
                            <div class="row g-3 mb-3 mt-2 align-items-start">
                                <div class="col">
                                        <label class="form-label">Given Iron Date</label>
                                        <input type="date" class="form-control" name="given_iron_date">
                                    </div>
                                 <div class="col-md-3 mt-5">
                                        <input class="form-check-input" type="checkbox" value="1" name="given_iron" id="checkDefault">
                                        <label class="form-check-label" for="checkDefault">
                                            place a check if Given Iron
                                        </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Note:</label>
                                    <input type="text" class="form-control" name="maternal_screening_remark">
                                </div>
                                    
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" rows="3" name="maternal_screening_remark"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3 ">
                     <button class="btn btn-secondary js-back_button col-md-2" type="button">Back</button>
                    <button class="btn btn-primary js-next_button col-md-2" name="next_btn" type="button" >Next</button>
                </div>
            </div>
            <!--Health & Wellness-->

            <!--Immunization & Supplements-->
            <div class="tab-pane fade" id="immunization-supplement-tab">

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        IMMUNIZATION STATUS
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="dynamic_immunization">
                                <div class="row g-3 mb-3 mt-2 align-items-center dynamic-row-immunization">
                                    <div class="col-md-5 dropdown">
                                        <label class="form-label">Immunization Type</label>
                                        <select class="form-select" name="immunization_type[]">
                                            <option value="" disabled selected>Select type</option>
                                            <option value="Td1/TT1">Td1/TT1</option>
                                            <option value="Td2/TT2">Td2/TT2</option>
                                            <option value="Td3/TT3">Td3/TT3</option>
                                            <option value="Td4/TT4">Td4/TT4</option>
                                            <option value="Td5/TT5">Td5/TT5</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Date Given</label>
                                        <input type="date" class="form-control" name="immunization_date[]">
                                    </div>
                                    <div class="col d-flex align-items-center" style="padding-top: 10px;">
                                        <button class="btn btn-primary " type="button" id="add_fim_field">
                                            <i class="bi bi-plus-lg text-white"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        MICRONUTRIENT SUPPLEMENTATION
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="dynamic_supp">
                                <div class="row g-3 mt-2 mb-4 align-items-center dynamic-row">
                                    <div class="col-md-3 dropdown ">
                                        <label class="form-label">Supplement</label>
                                        <select class="form-select" name="supplement_type[]">
                                            <option value="" disabled selected>Select supplement type</option>
                                            <option value="Iron Sulfate w/Folic Acid">Iron Sulfate w/Folic Acid</option>
                                            <option value="Calcium Carbonate">Calcium Carbonate</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 dropdown">
                                        <label class="form-label">Trimester</label>
                                        <select class="form-select" name="supp_trimester[]">
                                            <option value="" disabled selected>Select trimester</option>
                                            <option value="1st visit (1st Tri)">1st visit(1st tri)</option>
                                            <option value="2nd visit (2nd tri)">2nd visit (2nd tri)</option>
                                            <option value="3rd visit (3rd tri)">3rd visit (3rd tri)</option>
                                            <option value="4th visit (3rd tri)">4th visit (3rd tri)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 ">
                                        <label class="form-label">Date Given</label>
                                        <input type="date" class="form-control" name="date_supp[]">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Tablets Given</label>
                                        <input type="number" class="form-control" min="0" name="supp_tablets_given[]" >
                                    </div>
                                    <div class="col d-flex align-items-center" style="padding-top: 10px;">
                                        <button class="btn btn-primary" type="button" id="add_supp_field">
                                            <i class="bi bi-plus-lg text-white"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <hr>

                            <div class="row g-3 mb-3 mt-2 align-items-center">
                                <div class="col-md-4 ">
                                    <label class="form-label">Iodine Capsule</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="iodine_capsule_given">
                                        <label class="form-check-label">
                                            check if 2 Capsules were given
                                        </label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">Date Given(1st visit/tri)</label>
                                    <input type="date" class="form-control" name="date_iodine">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mb-3 ">
                      <button class="btn btn-secondary js-back_button col-md-2" type="button">Back</button>
                      <button class="btn btn-primary js-next_button col-md-2" name="next_btn" type="button" >Next</button>
                </div>
            </div>

            <!--Immunization & Supplements-->

            <!--Postpartum Care-->
            <div class="tab-pane fade" id="postpartum-care-tab">

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        DATE AND TIME OF DELIVERY
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-center">
                                <div class="col">
                                    <label class="form-label">Date of Delivery</label>
                                    <input type="date" class="form-control" name="post_delivery_date">
                                </div>
                                <div class="col">
                                    <label class="form-label">Time of Delivery</label>
                                    <input type="time" class="form-control" name="post_delivery_time">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        POSTPARTUM CHECK-UPS
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-4 align-items-center">
                                <div class="col">
                                    <label class="form-label">Post-Partum Visits</label>
                                    <select class="form-select" name="checkup_visit">
                                        <option disabled selected>Select visit</option>
                                        <option value="Within 24hrs after delivery">Within 24hrs</option>
                                        <option value="3rd day">3rd day</option>
                                        <option value="7 to 14 days">7-14 days</option>
                                        <option value="6 weeks">6 weeks</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Checkup Date</label>
                                    <input type="date" class="form-control" name="post_checkup_date">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        DATE & TIME INITIATED BREASTFEEDING
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-4  align-items-center">
                                <div class="col">
                                    <label class="form-label">Date Breastfed</label>
                                    <input type="date" class="form-control" name="breastfeeding_date">
                                </div>
                                <div class="col">
                                    <label class="form-label">Time Breastfed</label>
                                    <input type="time" class="form-control" name="breastfeeding_time">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        MICRONUTRIENT SUPPLEMENTATION(postpartum)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-center">
                                <div class="col">
                                    <label class="form-label">Iron w/Folic Acid Month Given</label>
                                    <select class="form-select" name="iron_folic_month_given">
                                        <option disabled selected>Select month</option>
                                        <option value="1st month postpartum">1st month postpartum</option>
                                        <option value="2nd month postpartum">2nd month postpartum</option>
                                        <option value="3rd month postpartum">3rd month postpartum</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Date Given</label>
                                    <input type="date" class="form-control" name="iron_folic_date_given">
                                </div>
                                <div class="col">
                                    <label class="form-label">No. Tablets Given</label>
                                    <input type="number" min="0" class="form-control" name="tablets_given">
                                </div>
                            </div>
                            <hr>
                            <div class="row g-3 mb-3 mt-2 align-items-center">
                                <div class="col-md-4 mt-4">
                                    <label class="form-label">Vitamin A</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="vitamin_a">
                                        <label class="form-check-label">
                                            check if Vitamin A was given
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date Given</label>
                                    <input type="date" class="form-control" name="vitamin_a_date">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="mb-3">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" name="remarks" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3 ">
                    <button class="btn btn-secondary js-back_button col-md-2" type="button">Back</button>
                    <button class="btn btn-primary col-md-2" name="submit_pregnancy" type="submit">Submit</button>
                </div>
            </div>
            <!--Postpartum Care-->
        </div>
    </form>
</div>



