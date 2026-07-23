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

<div class="container-fluid py-4">
    <div class="row g-0 border border-secondary-subtle rounded bg-white">
        
        <!-- LEFT COLUMN: Navigation Tabs -->
        <div class="col-md-3 border-end border-secondary-subtle bg-light-subtle p-3">
            <div class="d-flex flex-column h-100">
                <div class="mb-4 ps-2">
                    <span class="text-uppercase tracking-wider text-muted fw-bold small d-block mb-1">Editing Record</span>
                    <h5 class="text-dark fw-bold mb-0">
                        <?=htmlspecialchars(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')) ?: 'Infant Patient'?>
                    </h5>
                    <small class="text-secondary font-monospace">ID: <?=htmlspecialchars($patient['patient_id'] ?? 'N/A')?></small>
                </div>
                
                <!-- Tab Triggers -->
                <div class="nav flex-column nav-pills gap-1" id="infantFormTabs" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start rounded-1 fw-medium py-2.5 small" id="tab-identity" data-bs-toggle="pill" data-bs-target="#panel-identity" type="button" role="tab">
                        1. Infant Identity
                    </button>
                    <button class="nav-link text-start rounded-1 fw-medium py-2.5 small" id="tab-demographics" data-bs-toggle="pill" data-bs-target="#panel-demographics" type="button" role="tab">
                        2. Administrative Details
                    </button>
                    <button class="nav-link text-start rounded-1 fw-medium py-2.5 small" id="tab-contact" data-bs-toggle="pill" data-bs-target="#panel-contact" type="button" role="tab">
                        3. Contact & Location
                    </button>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Form Content Area -->
        <div class="col-md-9 d-flex flex-column">
            <form id="editInfantForm" method="POST" class="d-flex flex-column justify-content-between h-100 mb-0">
                <!-- FIXED: Corrected value binding for the hidden patient_id field -->
                <input type="hidden" name="patient_id" id="edit_infant_patient_id" value="<?=htmlspecialchars($patient['patient_id'] ?? '')?>">
                
                <div class="card-body p-4 tab-content" id="infantFormContent">
                    
                    <!-- PANEL 1: Identity -->
                    <div class="tab-pane fade show active" id="panel-identity" role="tabpanel" aria-labelledby="tab-identity">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-4">Infant Identity & Parentage</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-secondary">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="infant_first_name" value="<?=htmlspecialchars($patient['first_name'] ?? '')?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-secondary">Middle Name</label>
                                <input type="text" class="form-control" name="infant_middle_name" value="<?=htmlspecialchars($patient['middle_name'] ?? '')?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-secondary">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="infant_last_name" value="<?=htmlspecialchars($patient['last_name'] ?? '')?>" required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium text-secondary">Date of Birth</label>
                                <input type="date" class="form-control" name="infant_birth_date" value="<?=htmlspecialchars($patient['birth_date'] ?? '')?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium text-secondary">Complete Name of Mother <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name_of_mother" placeholder="Surname, Firstname Middle Initial." value="<?=htmlspecialchars($patient['name_of_mother'] ?? '')?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL 2: Administrative Details -->
                    <div class="tab-pane fade" id="panel-demographics" role="tabpanel" aria-labelledby="tab-demographics">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-4">Administrative Details</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-secondary">Date of Registration</label>
                                <input type="date" class="form-control" name="date_of_registration" value="<?=htmlspecialchars($patient['date_of_registration'] ?? '')?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-secondary">Family Serial No.</label>
                                <input type="text" class="form-control" name="family_serial_number" value="<?=htmlspecialchars($patient['family_serial_number'] ?? '')?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-medium text-secondary">Socio-Economic Status</label>
                                <select class="form-select" name="socio_economic_status">
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="1 - NHTS" <?=($patient['socio_economic_status'] ?? '') == '1 - NHTS' ? 'selected' : ''?>>1-NHTS</option>
                                    <option value="2 - Non-NHTS" <?=($patient['socio_economic_status'] ?? '') == '2 - Non-NHTS' ? 'selected' : ''?>>2-Non-NHTS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL 3: Contact & Location -->
                    <div class="tab-pane fade" id="panel-contact" role="tabpanel" aria-labelledby="tab-contact">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-4">Contact & Location</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium text-secondary">Contact Number</label>
                                <input type="tel" class="form-control" name="contact_number" value="<?=htmlspecialchars($patient['contact_number'] ?? '')?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium text-secondary">Email Address (optional)</label>
                                <input type="email" class="form-control" name="email" value="<?=htmlspecialchars($patient['email'] ?? '')?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium text-secondary">Residential Address</label>
                            <input type="text" class="form-control" name="address" value="<?=htmlspecialchars($patient['address'] ?? '')?>">
                        </div>
                    </div>
                </div>

                <!-- Shared Footer Action Bar -->
                <div class="d-flex justify-content-end gap-2 p-3 bg-light border-top mt-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-4 fw-medium" onclick="loadPage('patient/infant/view_infant_patient.php');">Back</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4 fw-medium">Save Changes</button>
                </div>
            </form>
        </div>
        
    </div>
</div>

<style>

#infantFormTabs .nav-link {
    color: var(--bs-secondary);
    border: 1px solid transparent;
}
#infantFormTabs .nav-link.active {
    background-color: var(--bs-white);
    color: var(--bs-dark);
    border-color: var(--bs-border-color-translucent);
    font-weight: 600 !important;
}
</style>