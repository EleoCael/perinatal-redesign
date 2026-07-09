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
    <form action="/rhusystem/midwife/patient/maternal/maternal_process.php" method="POST">
        <ul class="nav nav-tabs" id="myTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#patient-details-tab">Patient Detail</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#pregnancy-delivery-tab">Pregnancy & Delivery</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#health-wellness-tab">Screening & Wellness</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#immunization-supplement-tab">Immunization & Supplement</a>
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

        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.getElementById('contact_number').addEventListener('input', function() {
    const contactNumber = this.value;
    document.getElementById('display_contact').textContent = contactNumber || 'the patient\'s contact number';
});

document.addEventListener('DOMContentLoaded', function() {
    const contactInput = document.getElementById('contact_number');
    if (contactInput.value) {
        document.getElementById('display_contact').textContent = contactInput.value;
    }
});
</script>