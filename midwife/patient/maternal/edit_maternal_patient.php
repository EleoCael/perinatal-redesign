<?php
require_once "../../../module/db.config.php";

$patient = [];

if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];

    $stmt = $conn->prepare(
        "SELECT * FROM patient WHERE patient_id = ?"
    );

    $stmt->bind_param("i", $patient_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();

    $stmt->close();
}
?>
<div class='container-fluid'>
    <div class="row">
        <div class="panel panel-default shadow-lg rounded">
            <div class="panel-heading">
                <h4 class="card-title mt-3">Edit Maternal Record</h4>
            </div>
            <div class="panel-body">
                <form id="editMaternalForm" method="POST">
                    <div class="mb-3">
                        <input
                            type="hidden"
                            name="patient_id"
                            value="<?=$patient['patient_id']?>"
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input 
                            type="text"   
                            class="form-control" 
                            name="first_name" 
                            value= "<?=htmlspecialchars($patient['first_name'])?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Middle Name</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="middle_name"
                            value="<?=htmlspecialchars($patient['middle_name'])?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="last_name" 
                            value="<?=htmlspecialchars($patient['last_name'])?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date of Registration</label>
                        <input 
                            type="date" 
                            class="form-control" 
                            name="date_of_registration"
                            max="<?php echo date('Y-m-d'); ?>" 
                            value="<?=htmlspecialchars($patient['date_of_registration'])?>"
                            id="date_of_registration">
                        <span id="error_date_of_registration" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Family Serial No.</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="family_serial_number"
                            value="<?=htmlspecialchars($patient['family_serial_number'])?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Socio-Economic Status</label>
                        <select class="form-select" name="socio_economic_status">
                            <option value="" disabled selected>Select Status</option>
                            <option 
                                value="1 - NHTS"
                                <?=($patient['socio_economic_status'] ?? '') == '1 - NHTS' ? 'selected' : ''?>
                            >
                                1-NHTS
                            </option>
                            <option 
                                value="2 - Non-NHTS"
                                <?=($patient['socio_economic_status'] ?? '') == '2 - Non-NHTS' ? 'selected' : ''?>
                            >
                                2-Non-NHTS
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="address"
                            value="<?=htmlspecialchars($patient['address'])?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input 
                            type="date" 
                            class="form-control" 
                            name="birth_date"
                            max="<?php echo date('Y-m-d'); ?>"
                            value="<?=($patient['birth_date'])?>"
                            id="birth_date">
                        <span id="error_birth_date" class="text-danger"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Age Bracket</label>
                        <div class="age-bracket-container mt-2">
                            <div class="form-check form-check-inline">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="age_bracket" 
                                    value="10-14" 
                                    id="age_bracket_1"
                                    <?=($patient['age_bracket'] ?? '') == '10-14' ? 'checked' : ''?>
                                >
                                <label class="form-check-label" for="age_bracket_1">10–14 y/o</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="age_bracket" 
                                    value="15-19" 
                                    id="age_bracket_2"
                                    <?=($patient['age_bracket'] ?? '') == '15-19' ? 'checked' : ''?>
                                >
                                <label class="form-check-label" for="age_bracket_2">15–19 y/o</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="age_bracket" 
                                    value="20-49" 
                                    id="age_bracket_3"
                                    <?=($patient['age_bracket'] ?? '') == '20-49' ? 'checked' : ''?>
                                >
                                <label class="form-check-label" for="age_bracket_3">20–49 y/o</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Age <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            class="form-control"
                            min="0" 
                            max="120" 
                            name="age"
                            value="<?=htmlspecialchars($patient['age'])?>"
                            id="age" 
                            required
                            >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email (optional)</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            name="email"
                            value="<?=htmlspecialchars($patient['email'])?>"    
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input 
                            type="tel" 
                            class="form-control" 
                            name="contact_number"
                            value="<?=htmlspecialchars($patient['contact_number'])?>"
                        >
                    </div>
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button 
                            type="button" 
                            class="btn btn-secondary"
                            onclick="loadPage('patient/maternal/view_maternal_patient.php');"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>