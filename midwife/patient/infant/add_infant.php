<?php
if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])) {
    echo '<div class="alert alert-danger">';
    foreach ($_SESSION['form_errors'] as $error) {
        echo '<div>• ' . htmlspecialchars($error) . '</div>';
    }
    echo '</div>';
    
    // Clear errors after displaying
    unset($_SESSION['form_errors']);
}
?>
<div class="main-container">
    <form action="/rhusystem/midwife/patient/infant/infant_process.php" method="POST">
        <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#patient-details-tab">Patient Details</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#newborn-screening-tab">Screening</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#infant-feeding-tab">Infant Feeding</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#immunization-received-tab">Immunization Received</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#postpartum-care-tab">Micronutrient Supplementation</a>
            </li>
        </ul>

        <div class="tab-content content-main-div">

            <!--Patient Details-->
            <div class="tab-pane fade show active" id="patient-details-tab">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        PATIENT INFORMATION
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <label class="form-label">Full Name<span class="text-danger">*</span></label><br>
                            <div class="row g-3 pd-10">
                                <div class="col">
                                    <input type="text" name="infant_first_name" class="form-control" placeholder="First name" aria-label="First name">
                                    <span id="error_first_name" class="text-danger"></span>
                                </div>
                                <div class="col">
                                    <input type="text" name="infant_middle_name" class="form-control" placeholder="Middle Initial" aria-label="Last name">
                                </div>
                                <div class="col">
                                    <input type="text" name="infant_last_name" class="form-control" placeholder="Last name" aria-label="Last name">
                                    <span id="error_last_name" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="mb-3 mt-2">
                                <label class="form-label">Address<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address">
                                <span id="error_address" class="text-danger"></span>
                            </div>
                            <div class="row g-3">
                                <div class="col mt-4">
                                    <label class="form-label">Date of Registration<span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_of_registration"
                                     max="<?php echo date('Y-m-d'); ?>" id="date_of_registration">
                                    <span id="error_date_of_registration" class="text-danger"></span>
                                </div>
                                <div class="col mt-4">
                                    <label class="form-label">Family Serial No.<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="family_serial_number">
                                    <span id="error_family_serial_number" class="text-danger"></span>
                                </div>
                                <div class="col dropdown mt-4">
                                    <label class="form-label">Socio-Economic Status<span class="text-danger">*</span></label>
                                    <select class="form-select" name="socio_economic_status">
                                        <option value="" disabled selected>Select Status</option>
                                        <option value="1 - NHTS">1-NHTS</option>
                                        <option value="2 - Non-NHTS">2-Non-NHTS</option>
                                    </select>
                                    <span id="error_socio_economic_status" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="mb-3 mt-2">
                                <label class="form-label">Complete name of Mother<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name_of_mother" placeholder="Surname, Firstname Middle Initial.">
                                <span id="error_name_of_mother" class="text-danger"></span>
                            </div>
                            <div class="row g-3">
                                <div class="col">
                                    <label class="form-label">Date of birth<span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="infant_birth_date"
                                     max="<?php echo date('Y-m-d'); ?>" id="birth_date">
                                    <span id="error_birth_date" class="text-danger"></span>
                                </div>  
                                
                                <div class="col">
                                    <label class="form-label">Email(optional)</label><br>
                                    <input type="email" class="form-control" placeholder="ex. maritesmadrigal@gmail.com" name="email">
                                </div>
                                <div class="col">
                                    <label class="form-label">Contact Number<span class="text-danger">*</span></label><br>
                                    <input type="tel" class="form-control" placeholder="ex. 09123456789"  id="contact_number" name="contact_number">
                                    <span id="error_contact_number" class="text-danger"></span>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end gap-2 mb-3">
                                <button class="btn btn-secondary col-md-2" id="back_btn" type="button" onclick="
                                loadPage('addPatient_LandingPg.php');
                             ">Back</button>
                                <button class="btn btn-primary js-next_btn col-md-2" type="button">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Patient Details-->

            <!--Newborn Screening-->
            <div class="tab-pane fade" id="newborn-screening-tab">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        NEWBORN MEASUREMENT
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="col ">
                                <label class="form-label">Weight in grams:</label>
                                <input type="number" min="0" step="0.01"  class="form-control" name="birth_weight">
                            </div>
                            <div class="col">
                                <label class="form-label">Length/Height in cm: </label>
                                <input type="number" min="0" step="0.01"  class="form-control" name="birth_height">
                            </div>
                            <div class="col">
                                <label class="form-label">Sex:</label>
                                <div class="age-bracket-container">
                                    <input type="radio" name="sex" value="male">
                                    <label>Male</label>
                                    <input type="radio" name="sex" value="female">
                                    <label>Female</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        NEWBORN SCREENING
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="col">
                                <label class="form-label">Referral Date:</label>
                                <input type="date" class="form-control" name="newborn_screening_referral"
                                 max="<?php echo date('Y-m-d'); ?>" id="newborn_screening_referral">
                                <span id="error_newborn_screening_referral" class="text-danger"></span>
                            </div>
                            <div class="col">
                                <label class="form-label">Date Done:</label>
                                <input type="date" class="form-control" name="newborn_screening_done"
                                max="<?php echo date('Y-m-d'); ?>" id="newborn_screening_done">
                                <span id="error_newborn_screening_done" class="text-danger"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        CHILD PROTECTED AT BIRTH (CPAB)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-center">
                                <div class="col dropdown">
                                    <label class="form-label">TT Status (Tetanus Toxoid):</label>
                                    <select class="form-select" name="cpab_tt_status">
                                        <option value="" disabled selected>Select type</option>
                                        <option value="td">Td (Tetanus and diphtheria)</option>
                                        <option value="td1">Td1 (Tetanus and diphtheria 1)</option>
                                        <option value="td2">Td2 (Tetanus and diphtheria 2)</option>
                                        <option value="td3">Td3 (Tetanus and diphtheria 3)</option>
                                        <option value="td4">Td4 (Tetanus and diphtheria 4 )</option>
                                        <option value="td5">Td5 (Tetanus and diphtheria 5)</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" name="cpab_tt_date"
                                    max="<?php echo date('Y-m-d'); ?>" id="cpab_tt_date">
                                    <span id="error_cpab_tt_date" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label class="form-label">Date Assessed:</label>
                                    <input type="date" class="form-control" name="cpab_tt_date_assessed"
                                    max="<?php echo date('Y-m-d'); ?>" id="cpab_tt_date_assessed">
                                <span id="error_cpab_tt_date_assessed" class="text-danger"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3 ">
                    <button class="btn btn-secondary js-back_btn col-md-2" type="button">Back</button>
                    <button class="btn btn-primary js-next_btn col-md-2" type="button">Next</button>
                </div>
            </div>
            <!--Newborn Screening-->

            <!--Infant Feeding-->
            <div class="tab-pane fade" id="infant-feeding-tab">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        CHILD WAS EXCLUSIVELY BREASTFED
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="dynamic_exclusive_feeding">
                                <div class="row g-3 mb-3 mt-2 align-items-center dynamic-row-exclusive-feeding">
                                    <div class="col-md-5 dropdown">
                                        <label class="form-label">Month Child was exclusively breastfed</label>
                                        <select class="form-select" name="month_check[]">
                                            <option value="" disabled selected>Select Month</option>
                                            <option value="1st Month">1st Month</option>
                                            <option value="2nd Month">2nd Month</option>
                                            <option value="3rd Month">3rd Month</option>
                                            <option value="4th Month">4th Month</option>
                                            <option value="5th Month">5th Month</option>
                                            <option value="6th Month">6th Month</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="month_date[]"
                                         max="<?php echo date('Y-m-d'); ?>" id="month_date">
                                        <span id="error_month_date" class="text-danger"></span>
                                    </div>
                                    <div class="col d-flex align-items-center" style="padding-top: 10px;">
                                        <button class="btn btn-primary " type="button" id="add_feed_field">
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
                        COMPLEMENTARY FEEDING
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="dynamic_complementary_feeding">
                                <div class="row g-3 mb-3 mt-2 align-items-center dynamic-row-complementary-feeding">
                                    <div class="col-md-5 dropdown">
                                        <label class="form-label">Complementary Feeding</label>
                                        <select class="form-select" name="complementary_month_check[]">
                                            <option value="" disabled selected>Select Month</option>
                                            <option value="6th Month">6th Month</option>
                                            <option value="7th Month">7th Month</option>
                                            <option value="8th Month">8th Month</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="complementary_month_date[]"
                                        max="<?php echo date('Y-m-d'); ?>" id="complementary_month_date">
                                        <span id="error_complementary_month_date" class="text-danger"></span>
                                    </div>
                                    <div class="col d-flex align-items-center" style="padding-top: 10px;">
                                        <button class="btn btn-primary " type="button" id="add_comple_field">
                                            <i class="bi bi-plus-lg text-white"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3 ">
                    <button class="btn btn-secondary js-back_btn col-md-2" type="button">Back</button>
                    <button class="btn btn-primary js-next_btn col-md-2" type="button">Next</button>
                </div>
            </div>
            <!--Infant Feeding-->

            <!--Immunization Received-->
            <div class="tab-pane fade" id="immunization-received-tab">

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        BCG (Bacillus Calmette–Guérin)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-center">
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" value="1" name="bcg_check">
                                        <label class="form-check-label">
                                            place a check if BCG was received
                                        </label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">Date Received:</label>
                                    <input type="date" class="form-control" name="bcg_date"
                                        max="<?php echo date('Y-m-d'); ?>" id="bcg_date">
                                    <span id="error_bcg_date" class="text-danger"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        Hepatitis B1
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">       
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Hepatitis B1</label>
                                        <select class="form-select" name="hepaB_day">
                                            <option value="" disabled selected>Select </option>
                                            <option value="w/in 24 hours">w/in 24 hours</option>
                                            <option value="More than 24 hours">More than 24 hours</option>
                                            
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="hepaB_date"
                                        max="<?php echo date('Y-m-d'); ?>" id="hepaB_date">
                                        <span id="error_hepaB_date" class="text-danger"></span>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        PENTAVALENT
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Pentavalent</label>
                                        <select class="form-select" name="pentavalent_type">
                                            <option value="" disabled selected>Select</option>
                                            <option value="Pentavalent 1">Pentavalent 1</option>
                                            <option value="Pentavalent 2">Pentavalent 2</option>
                                            <option value="Pentavalent 3">Pentavalent 3</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="pentavalent_date"
                                            max="<?php echo date('Y-m-d'); ?>" id="pentavalent_date">
                                        <span id="error_pentavalent_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        Oral Polio Vaccine (OPV)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Oral Polio Vaccine (OPV)</label>
                                        <select class="form-select" name="opv_type">
                                            <option value="" disabled selected>Select</option>
                                            <option value="Opv 1">Opv 1</option>
                                            <option value="Opv 2">Opv 2</option>
                                            <option value="Opv 3">Opv 3</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="opv_date"
                                            max="<?php echo date('Y-m-d'); ?>" id="opv_date">
                                        <span id="error_opv_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        Inactivated Polio Vaccine (IPV)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col-md-4 dropdown">
                                        <input class="form-check-input" type="checkbox" value="1" name="ipv_1">
                                        <label class="form-check-label">
                                            place a check if IPV was received
                                        </label>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="ipv_date"
                                        max="<?php echo date('Y-m-d'); ?>" id="ipv_date">
                                        <span id="error_ipv_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        Measles-Containing Vaccine (MCV)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Measles-Containing Vaccine (MCV)</label>
                                        <select class="form-select" name="mcv_type">
                                            <option value="" disabled selected>Select</option>
                                            <option value="MCV1 (AMV)">MCV1 Anti-Measles Vaccine(AMV)</option>
                                            <option value="MCV2 (MMR)">MCV2 Measles, Mumps, and Rubella(MMR)</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="mcv_date"
                                         max="<?php echo date('Y-m-d'); ?>" id="mcv_date">
                                        <span id="error_mcv_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        FULLY IMMUNIZED CHILD (FIC)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-center">
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" value="1" name="fic_check">
                                        <label class="form-check-label">
                                            place a check if child was fully immunized
                                        </label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" name="fic_date"
                                        max="<?php echo date('Y-m-d'); ?>" id="fic_date">
                                    <span id="error_fic_date" class="text-danger"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        ROTA VIRUS VACCINE (RVV)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Rota Virus Vaccine (RVV)</label>
                                        <select class="form-select" name="rvv_type">
                                            <option value="" disabled selected>Select</option>
                                            <option value="Rota Virus Vaccine 1">Rota Virus Vaccine 1</option>
                                            <option value="Rota Virus Vaccine 2">Rota Virus Vaccine 2</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="rvv_date"
                                             max="<?php echo date('Y-m-d'); ?>" id="rvv_date">
                                        <span id="error_rvv_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        PNEUMOCOCCAL CONJUGATE VACCINES (PCV)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Pneumococcal Conjugate Vaccine (PCV)</label>
                                        <select class="form-select" name="pcv_type">
                                            <option value="" disabled selected>Select</option>
                                            <option value="PCV 1">PCV 1</option>
                                            <option value="PCV 2">PCV 2</option>
                                            <option value="PCV 3">PCV 3</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="pcv_date"
                                            max="<?php echo date('Y-m-d'); ?>" id="pcv_date">
                                        <span id="error_pcv_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3 ">
                    <button class="btn btn-secondary js-back_btn col-md-2" type="button">Back</button>
                    <button class="btn btn-primary js-next_btn col-md-2" type="button">Next</button>
                </div>
            </div>

            <!--Immunization & Supplements-->

            <!--Micronutrient Supplementation-->
            <div class="tab-pane fade" id="postpartum-care-tab">

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        VITAMIN A
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Vitamin A</label>
                                        <select class="form-select" name="vitamin_type">
                                            <option value="" disabled selected>Select</option>
                                            <option value="Vitamin A (6-11 Months)">6-11 Months</option>
                                            <option value="Vitamin A (12-59 Months) Dose 1">(12-59 Months) Dose 1</option>
                                            <option value="Vitamin A (12-59 Months) Dose 2">(12-59 Months) Dose 2</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="vitamin_date"
                                          max="<?php echo date('Y-m-d'); ?>" id="vitamin_date">
                                        <span id="error_vitamin_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        IRON
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Iron</label>
                                        <select class="form-select" name="iron_type">
                                            <option value="" disabled selected>Select</option>
                                            <option value="Iron (6-11 Month)">6-11 Months</option>
                                            <option value="Iron (12-23 Month)">12-59 Months</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="iron_date"
                                         max="<?php echo date('Y-m-d'); ?>" id="iron_date">
                                        <span id="error_iron_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                       Micronutrient Powder (MNP)
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                                <div class="row g-3 mb-3 mt-2 align-items-center">
                                    <div class="col dropdown">
                                        <label class="form-label">Micronutrient Powder (MNP)</label>
                                        <select class="form-select" name="mnp_type">
                                            <option value="" disabled selected>Select</option>
                                            <option value="MNP (6-11 Months)">6-11 Months</option>
                                            <option value="MNP (12-23 Months)">12-23 Months</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Date:</label>
                                        <input type="date" class="form-control" name="mnp_date"
                                         max="<?php echo date('Y-m-d'); ?>" id="mnp_date">
                                        <span id="error_mnp_date" class="text-danger"></span>
                                    </div>
                            </div>                     
                        </div>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        DEWORMING
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">
                            <div class="row g-3 mb-3 mt-2 align-items-center">
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" value="1" name="deworming_check">
                                        <label class="form-check-label">
                                            Deworming (12-59 months)
                                        </label>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">Date</label>
                                    <input type="date" class="form-control" name="deworming_date"
                                    max="<?php echo date('Y-m-d'); ?>" id="deworming_date">
                                    <span id="error_deworming_date" class="text-danger"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-3 ">
                    <button class="btn btn-secondary js-back_btn col-md-2" type="button">Back</button>
                    <button class="btn btn-primary js-submit_btn col-md-2" id="submit_btn" name="submit_btn" type="submit">Submit</button>
                </div>
            </div>
            <!--Postpartum Care-->
        </div>
    </form>
</div>

<script>
// Update the contact number display when user types
document.getElementById('contact_number').addEventListener('input', function() {
    const contactNumber = this.value;
    document.getElementById('display_contact').textContent = contactNumber || 'the patient\'s contact number';
});

// Also update on page load if there's already a value
document.addEventListener('DOMContentLoaded', function() {
    const contactInput = document.getElementById('contact_number');
    if (contactInput.value) {
        document.getElementById('display_contact').textContent = contactInput.value;
    }
});
</script>