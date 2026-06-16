<div class="main-container">
    <div class="card shadow-sm">
        <div class="card-header bg-dark  py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-white">Set New Appointment</h4>
        </div>
        <div class="card-body">
            <form id="addAppointmentForm">
                <div class="row g-3">
                    <!-- Patient Selection -->
                    <!-- Replace the current patient selection section in add_appointment_form.php -->
                    <div class="col-md-12">
                        <label class="form-label">Select Patient <span class="text-danger">*</span></label>

                        <!-- Patient Search Input -->
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="patientSearch"
                                placeholder="Type patient name to search..." autocomplete="off">
                        </div>

                        <!-- Search Results -->
                        <div id="patientSearchResults" class="border rounded p-2" style="max-height: 200px; overflow-y: auto; display: none;">
                            <!-- Search results will appear here -->
                        </div>

                        <!-- Selected Patient Display -->
                        <div id="selectedPatient" class="mt-2 p-2 bg-light rounded" style="display: none;">
                            <strong>Selected Patient:</strong>
                            <span id="selectedPatientName"></span>
                            <input type="hidden" name="patient_id" id="selectedPatientId">
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearSelectedPatient()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        <!-- Quick Filter Buttons -->
                        <div class="mt-2">
                            <small class="text-muted">Quick filter:</small>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="filterPatientsByType('mother')">
                                Mothers
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success me-1" onclick="filterPatientsByType('infant')">
                                Infants
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="filterPatientsByType('postpartum_mother')">
                                Postpartum
                            </button>
                        </div>
                    </div>

                    <!-- Appointment Type -->
                    <div class="col-md-6">
                        <label class="form-label">Appointment Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="appointment_type" required>
                            <option value="" disabled selected>Select type</option>
                            <option value="Prenatal">Prenatal Checkup</option>
                            <option value="Postpartum">Postpartum Checkup</option>
                            <option value="Infant Checkup">Infant Checkup</option>
                            <option value="Immunization">Immunization</option>
                            <option value="General Checkup">General Checkup</option>
                        </select>
                    </div>

                    <!-- Appointment Date -->
                    <div class="col-md-6">
                        <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="appointment_date"
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>

                    <!-- Appointment Time -->
                    <div class="col-md-6">
                        <label class="form-label">Appointment Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" name="appointment_time" value="09:00" required>
                    </div>

                    <!-- Remarks -->
                    <div class="col-12">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" name="remarks" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i>
                     <strong>SMS Reminder:</strong> An automatic SMS reminder will be sent to <strong id="display_contact">the patient's contact number</strong> 24 hours before the appointment.
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-secondary" onclick="loadAppointmentsPage()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Set Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

