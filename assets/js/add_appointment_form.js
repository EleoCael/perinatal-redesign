
// Enhanced patient search functionality
if (typeof searchTimeout === "undefined") {
  var searchTimeout = null;
}



// Patient search with debouncing
$('#patientSearch').on('input', function() {
    const searchTerm = $(this).val().trim();
    
    // Clear previous timeout
    clearTimeout(searchTimeout);
    
    // Only search if term is 2+ characters
    if (searchTerm.length < 2) {
        $('#patientSearchResults').hide().empty();
        return;
    }
    
    // Debounce search - wait 300ms after user stops typing
    searchTimeout = setTimeout(() => {
        searchPatients(searchTerm);
    }, 300);
});

// Function to search patients
function searchPatients(searchTerm, patientType = '') {
    $.ajax({
        url: "appointments/search_patients.php",
        method: "POST",
        data: { 
            search_term: searchTerm,
            patient_type: patientType
        },
        dataType: "json",
        success: function(response) {
            const resultsContainer = $('#patientSearchResults');
            resultsContainer.empty();
            
            if (response.patients && response.patients.length > 0) {
                response.patients.forEach(patient => {
                    const patientItem = `
                        <div class="patient-item p-2 border-bottom cursor-pointer" 
                             onclick="selectPatient(${patient.patient_id}, '${patient.first_name} ${patient.last_name}', '${patient.patient_type}')">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>${patient.first_name} ${patient.last_name}</strong>
                                    <span class="badge bg-${getPatientTypeBadgeColor(patient.patient_type)} ms-2">
                                        ${formatPatientType(patient.patient_type)}
                                    </span>
                                </div>
                                <small class="text-muted">${patient.contact_number || 'No contact'}</small>
                            </div>
                            ${patient.name_of_mother ? `<small class="text-muted">Mother: ${patient.name_of_mother}</small>` : ''}
                        </div>
                    `;
                    resultsContainer.append(patientItem);
                });
                resultsContainer.show();
            } else {
                resultsContainer.html('<div class="p-2 text-muted">No patients found</div>').show();
            }
        },
        error: function(xhr, status, error) {
            console.error("Patient search error:", error);
            $('#patientSearchResults').html('<div class="p-2 text-danger">Error searching patients</div>').show();
        }
    });
}

// Quick filter functions
function filterPatientsByType(patientType) {
    $('#patientSearch').val('');
    const typeNames = {
        'mother': 'Mothers',
        'infant': 'Infants', 
        'postpartum_mother': 'Postpartum Mothers'
    };
    $('#patientSearch').attr('placeholder', `Searching ${typeNames[patientType]}...`);
    searchPatients('', patientType);
}

// Select patient function
function selectPatient(patientId, patientName, patientType) {
    $('#selectedPatientId').val(patientId);
    $('#selectedPatientName').text(`${patientName} (${formatPatientType(patientType)})`);
    $('#selectedPatient').show();
    $('#patientSearchResults').hide().empty();
    $('#patientSearch').val('');
    
    // Auto-set appropriate appointment type based on patient type
    autoSetAppointmentType(patientType);
}

// Clear selected patient
function clearSelectedPatient() {
    $('#selectedPatientId').val('');
    $('#selectedPatient').hide();
    $('#patientSearch').val('').attr('placeholder', 'Type patient name to search...');
}

// Helper functions
function getPatientTypeBadgeColor(patientType) {
    const colors = {
        'mother': 'primary',
        'infant': 'success', 
        'postpartum_mother': 'info'
    };
    return colors[patientType] || 'secondary';
}

function formatPatientType(patientType) {
    const types = {
        'mother': 'Mother',
        'infant': 'Infant',
        'postpartum_mother': 'Postpartum'
    };
    return types[patientType] || patientType;
}

function autoSetAppointmentType(patientType) {
    const typeMapping = {
        'mother': 'Prenatal',
        'infant': 'Infant Checkup',
        'postpartum_mother': 'Postpartum'
    };
    
    const suggestedType = typeMapping[patientType];
    if (suggestedType) {
        $('select[name="appointment_type"]').val(suggestedType);
    }
}

// Close search results when clicking outside
$(document).on('click', function(e) {
    if (!$(e.target).closest('#patientSearch, #patientSearchResults').length) {
        $('#patientSearchResults').hide();
    }
});
