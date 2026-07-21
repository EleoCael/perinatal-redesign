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
                <h5 class="modal-title">Edit Infant Record</h5>
            </div>
            <div class="panel-body">
                <form id="editInfantForm" method="POST">
                    <input type="hidden" name="patient_id" id="edit_infant_patient_id">

                    <div class="mb-3">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="infant_first_name" 
                            value="<?=htmlspecialchars($patient['first_name'])?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Middle Name</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="infant_middle_name"
                            value="<?=htmlspecialchars($patient['middle_name'])?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="infant_last_name"
                            value="<?=htmlspecialchars($patient['last_name'])?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date of Registration</label>
                        <input 
                            type="date" 
                            class="form-control" 
                            name="date_of_registration"
                            value="<?=htmlspecialchars($patient['date_of_registration'])?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Family Serial No.</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="family_serial_number"
                            value="<?=htmlspecialchars($patient['family_serial_number'])?>"
                        >
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
                            value="<?=htmlspecialchars($patient['address'])?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input 
                            type="date" 
                            class="form-control" 
                            name="infant_birth_date"
                            value="<?=htmlspecialchars($patient['birth_date'])?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Complete name of Mother<span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control" 
                            name="name_of_mother" 
                            placeholder="Surname, Firstname Middle Initial."
                            value="<?=htmlspecialchars($patient['name_of_mother'])?>"
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
                    
                    <div class="d-flex justify-content-end mr-3 mb-3 gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button 
                            type="button" 
                            class="btn btn-secondary"
                            onclick="loadPage('patient/infant/view_infant_patient.php');"
                        >
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Modal for editing/updating infant record -->
    <!--=========================================MATERNAL MODAL ============================-->
