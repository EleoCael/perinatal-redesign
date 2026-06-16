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
    <form action="/rhusystem/midwife/patient/postpartum/postpartum_process.php" method="POST">
        <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#patient-details-tab">Patient Details</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#postpartum-care-tab">Postpartum Care</a>
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
                            <label class="form-label">Full Name <span class="text-danger">*</span></label><br>
                            <div class="row g-3 pd-10">
                                <div class="col">
                                    <input type="text" name="first_name" class="form-control" placeholder="First name" aria-label="First name">
                                    <span id="error_first_name" class="text-danger"></span>
                                </div>
                                <div class="col">
                                    <input type="text" name="middle_name" class="form-control" placeholder="Middle Initial" aria-label="Last name">
                                </div>
                                <div class="col">
                                    <input type="text" name="last_name" class="form-control" placeholder="Last name" aria-label="Last name">
                                    <span id="error_last_name" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col mt-4">
                                    <label class="form-label">Date of Registration <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_of_registration"
                                     max="<?php echo date('Y-m-d'); ?>" id="date_of_registration">
                                    <span id="error_date_of_registration" class="text-danger"></span>
                                </div>
                                <div class="col mt-4">
                                    <label class="form-label">Family Serial No. <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="family_serial_number">
                                    <span id="error_family_serial_number" class="text-danger"></span>
                                </div>
                                <div class="col dropdown mt-4">
                                    <label class="form-label">Socio-Economic Status <span class="text-danger">*</span></label>
                                    <select class="form-select" name="socio_economic_status">
                                        <option value="" disabled selected>Select Status</option>
                                        <option value="1 - NHTS">1-NHTS</option>
                                        <option value="2 - Non-NHTS">2-Non-NHTS</option>
                                    </select>
                                    <span id="error_socio_economic_status" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="mb-3 mt-2">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address">
                                <span id="error_address" class="text-danger"></span>
                            </div>
                            <div class="row g-3">
                                <div class="col mt-4">
                                    <label class="form-label">Date of birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="birth_date"
                                    max="<?php echo date('Y-m-d'); ?>" id="birth_date">
                                    <span id="error_birth_date" class="text-danger"></span>
                                </div>
                                <div class="col mt-4">
                                    <label class="form-label">Age Bracket <span class="text-danger">*</span></label>
                                    <div class="age-bracket-container mt-3">
                                        <input type="radio" name="age_bracket" value="10-14">
                                        <label>10-14 y/o</label>

                                        <input type="radio" name="age_bracket" value="15-19">
                                        <label>15-19 y/o</label>

                                        <input type="radio" name="age_bracket" value="20-49">
                                        <label>20-49 y/o</label>
                                    </div>
                                    <span id="error_age_bracket" class="text-danger"></span>
                                </div>
                                <div class="col mt-4 ">
                                    <label class="form-label">Age <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" min="0" name="age" id="age">
                                    <span id="error_age" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col mb-3">
                                    <label class="form-label">Email(optional)</label><br>
                                    <input type="email" class="form-control" placeholder="ex. maritesmadrigal@gmail.com" name="email">
                                </div>
                                <div class="col">
                                    <label class="form-label">Contact Number <span class="text-danger">*</span></label><br>
                                    <input type="tel" class="form-control" placeholder="ex. 09123456789" id="contact_number" name="contact_number">
                                    <span id="error_contact_number" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mb-3">
                                <button class="btn btn-secondary col-md-2" class="back_btn" type="button" onclick="
                                loadPage('addPatient_LandingPg.php');
                             ">Back</button>
                                <button class="btn btn-primary js-next_btn col-md-2" name="next_btn" type="button">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Patient Details-->

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
                                    <input type="date" class="form-control" name="post_delivery_date"
                                     max="<?php echo date('Y-m-d'); ?>" id="post_delivery_date">                               
                                    <span id="error_post_delivery_date" class="text-danger"></span>
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
                                        <option value="Within 24hrs after delivery">Within 24hrs after delivery</option>
                                        <option value="Within 1 week after delivery">Within 1 week after delivery</option>
                                        
                                    </select>
                                </div>
                                <div class="col">
                                    <label class="form-label">Checkup Date</label>
                                    <input type="date" class="form-control" name="post_checkup_date"
                                     max="<?php echo date('Y-m-d'); ?>" id="post_checkup_date">
                                     <span id="error_post_checkup_date" class="text-danger"></span>
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
                                    <input type="date" class="form-control" name="breastfeeding_date"
                                     max="<?php echo date('Y-m-d'); ?>" id="breastfeeding_date">
                                      <span id="error_breastfeeding_date" class="text-danger"></span>
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
                                    <input type="date" class="form-control" name="iron_folic_date_given"
                                    max="<?php echo date('Y-m-d'); ?>" id="iron_folic_date_given">
                                      <span id="error_iron_folic_date_given" class="text-danger"></span>
                                </div>
                                <div class="col">
                                    <label class="form-label">No. Tablets Givem</label>
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
                                    <input type="date" class="form-control" name="vitamin_a_date"
                                    max="<?php echo date('Y-m-d'); ?>" id="vitamin_a_date">
                                    <span id="error_vitamin_a_date" class="text-danger"></span>
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
                    <button class="btn btn-secondary js-back_btn col-md-2" type="button">Back</button>
                    <button class="btn btn-primary js-submit_btn col-md-2" name="submit_btn" type="submit">Submit</button>
                </div>
            </div>
            <!--Postpartum Care-->
        </div>
    </form>
</div>