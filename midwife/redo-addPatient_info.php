<div class="main-container-landingPg">
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

    <form id="patientInfoForm" action="/rhusystem/midwife/patient/maternal/maternal_process.php" method="POST">

        <div class="tab-content content-main-div">

            <!--Patient Details-->
            <div class="tab-pane fade show active" id="patient-details-tab">
                <div class="card mb-3 shadow-sm">
                    <div class="card-header text-center">
                        PATIENT INFORMATION
                    </div>
                    <div class="card-body ">
                        <div class="form-group group-form">

                            <!-- Patient Type Selector -->
                            <div class="mb-3">
                                <label class="form-label">Patient Type<span class="text-danger">*</span></label>
                                <select class="form-select" id="patient_type" name="patient_type_select" required>
                                    <option value="" disabled selected>Select Patient Type</option>
                                    <option value="maternal">Maternal</option>
                                    <option value="infant">Infant</option>
                                    <option value="postpartum">Postpartum</option>
                                </select>
                                <span id="error_patient_type" class="text-danger"></span>
                            </div>

                            <!-- Everything below is hidden until a Patient Type is chosen. -->
                            <!-- Shown by validation_error_maternal.js (event delegation) on #patient_type change. -->
                            <div id="patient_fields_container" style="display:none;">

                            <!-- Full Name -->
                            <label class="form-label">Full Name<span class="text-danger">*</span></label><br>
                            <div class="row g-3 pd-10">
                                <div class="col">
                                    <input type="text" id="first_name_input" name="first_name" class="form-control" placeholder="First name" aria-label="First name">
                                    <span id="error_first_name" class="text-danger"></span>
                                </div>
                                <div class="col">
                                    <input type="text" id="middle_name_input" name="middle_name" class="form-control" placeholder="Middle Initial" aria-label="Middle name">
                                </div>
                                <div class="col">
                                    <input type="text" id="last_name_input" name="last_name" class="form-control" placeholder="Last name" aria-label="Last name">
                                    <span id="error_last_name" class="text-danger"></span>
                                </div>
                            </div>

                            <!-- Name of Mother (Infant only) -->
                            <div class="mb-3 mt-2" id="name_of_mother_group" style="display:none;">
                                <label class="form-label">Complete name of Mother<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name_of_mother_input" name="name_of_mother" placeholder="Surname, Firstname Middle Initial." disabled>
                                <span id="error_name_of_mother" class="text-danger"></span>
                            </div>

                            <!-- Address -->
                            <div class="mb-3 mt-2">
                                <label class="form-label">Address<span class="text-danger">*</span></label>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <input type="text" class="form-control" id="house_street_input" maxlength="40" placeholder="House/Unit No. & Street">
                                        <span id="error_house_street" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="purok_sitio_input" maxlength="20" placeholder="Purok/Sitio (optional)">
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-select" id="barangay_input">
                                            <option value="" disabled selected>Select Barangay</option>
                                            <?php
                                            // Hardcoded from the health_center table (all barangays, Bulakan, Bulacan).
                                            // If you add a new health center / barangay, update this list too.
                                            $barangays = [
                                                'Bagumbayan', 'Balubad', 'Bambang', 'Matungao', 'Maysantol',
                                                'Perez', 'Pitpitan', 'San Francisco', 'San Jose', 'San Nicolas',
                                                'Santa Ana', 'Santa Ines', 'Taliptip', 'Tibig', 'San Jose (Pob.)'
                                            ];
                                            foreach ($barangays as $brgy) {
                                                echo '<option value="' . htmlspecialchars($brgy) . '">' . htmlspecialchars($brgy) . '</option>';
                                            }
                                            ?>
                                        </select>
                                        <span id="error_barangay" class="text-danger"></span>
                                    </div>
                                </div>
                                <div class="row g-3 mt-1">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="city_input" value="Bulakan" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="province_input" value="Bulacan" readonly>
                                    </div>
                                </div>
                                <!-- Composed "House/Unit No. & Street, Purok/Sitio, Brgy. X, City, Province" gets written here on submit -->
                                <input type="hidden" name="address" id="address_hidden">
                            </div>

                            <!-- Date of Registration / Family Serial / Socio-Economic Status -->
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

                            <!-- Birth date + Age Bracket + Age (Maternal / Postpartum only) -->
                            <div class="row g-3">
                                <div class="col mt-4">
                                    <label class="form-label">Date of birth<span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="birth_date" id="birth_date_input"
                                     max="<?php echo date('Y-m-d'); ?>">
                                    <span id="error_birth_date" class="text-danger"></span>
                                </div>
                                <div class="col mt-4" id="age_bracket_group">
                                    <label class="form-label">Age Bracket<span class="text-danger">*</span></label>
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
                                <div class="col mt-4" id="age_group">
                                    <label class="form-label">Age<span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" min="0" name="age" id="age">
                                    <span id="error_age" class="text-danger"></span>
                                </div>
                            </div>

                            <!-- Email + Contact Number -->
                            <div class="row mt-4">
                                <div class="col mb-3">
                                    <label class="form-label">Email(optional)</label><br>
                                    <input type="email" class="form-control" placeholder="ex. maritesmadrigal@gmail.com" name="email">
                                </div>
                                <div class="col">
                                    <label class="form-label">Contact Number<span class="text-danger">*</span></label><br>
                                    <input type="tel" class="form-control" placeholder="ex. 09123456789" id="contact_number" name="contact_number">
                                    <span id="error_contact_number" class="text-danger"></span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mb-3">
                                <button class="btn btn-secondary col-md-2" id="back_btn" type="button" onclick="
                                loadPage('home.php');
                             ">Back</button>
                                <button class="btn btn-primary js-submit_patient_info col-md-2" type="button">Submit</button>
                            </div>

                            </div>
                            <!-- /#patient_fields_container -->

                        </div>
                    </div>
                </div>
            </div>
            <!--Patient Details-->

        </div>
    </form>

<!--
    Note: all interactive logic for this form (patient-type switching, field
    name swapping, contact number display) now lives in validation_error_maternal.js,
    which is loaded once by the parent page and uses event delegation on `document`.
    Inline <script> tags here would NOT execute since this fragment is injected
    via loadPage(), so no <script> block is needed in this file anymore.
-->