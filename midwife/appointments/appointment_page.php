<div class="main-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Appointment Management</h2>
        <button class="btn btn-primary" id="addAppointmentBtn">
            <i class="bi bi-plus-circle text-white"></i> Set New Appointment
        </button>
    </div>


    <div class="card shadow-sm">
        <div class="card-header  py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 fw-bold text-primary">Upcoming Appointments</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="appointmentsTable">
                    <thead class="table-dark">
                        <tr>
                            <th style="color: #fff;">Patient Name</th>
                            <th style="color: #fff;">Appointment Date</th>
                            <th style="color: #fff;">Time</th>
                            <th style="color: #fff;">Type</th>
                            <th style="color: #fff;">Status</th>
                            <th style="color: #fff;">Contact</th>
                            <th style="color: #fff;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointments_table">
             
                    </tbody>
                </table>
            </div>
            <div id="loadingMessage" class="text-center py-4 d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading appointments...</span>
                </div>
                <p class="mt-2">Loading appointments...</p>
            </div>
            <div id="noAppointmentsMessage" class="text-center py-4 d-none">
                <p class="text-muted">No appointments found.</p>
            </div>
        </div>
    </div>
</div>

