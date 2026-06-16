// Load appointments when page is ready
$(document).ready(function () {
  console.log("Current page URL:", window.location.href);
  console.log("Current page pathname:", window.location.pathname);
  loadAppointments();

  // Set up filter event listeners
  $("#filterDate, #filterStatus, #filterType").on("change", function () {
    loadAppointments();
  });

  // Set up "Add Appointment" button
  $("#addAppointmentBtn").on("click", function () {
    loadAddAppointmentForm();
  });
  $("#viewUpcomingBtn").on("click", function () {
    switchToUpcomingView();
  });

  $("#viewCompletedBtn").on("click", function () {
    switchToCompletedView();
  });
});

// Function to load appointments with filters
function loadAppointments(page = 1) {
  // Show loading state
  $("#loadingMessage").removeClass("d-none");
  $("#noAppointmentsMessage").addClass("d-none");
  $("#appointments_table").html("");

  // Get filter values
  const filterDate = $("#filterDate").val();
  const filterStatus = $("#filterStatus").val();
  const filterType = $("#filterType").val();

  $.ajax({
    url: "appointments/fetch_appointments.php",
    method: "POST",
    dataType: "json",
    data: {
      action: "fetchAppointments",
      page: page,
      filter_date: filterDate,
      filter_status: filterStatus,
      filter_type: filterType,
    },
    success: function (response) {
      $("#loadingMessage").addClass("d-none");

      if (response.table_data) {
        $("#appointments_table").html(response.table_data);
        setupTableButtonListeners();

        // Show pagination if available
        if (response.pagination_links) {
          // We'll add pagination container later if needed
        }
      } else {
        $("#noAppointmentsMessage").removeClass("d-none");
      }
    },
    error: function (xhr, status, error) {
      $("#loadingMessage").addClass("d-none");
      console.error("Appointments AJAX Error:", status, error);
      $("#appointments_table").html(
        '<tr><td colspan="7" class="text-center text-danger">Error loading appointments.</td></tr>'
      );
    },
  });
}

$(document).on("click", "#addAppointmentBtn", function () {
  console.log("🟢 delegated click detected");
  loadAddAppointmentForm();
});

// Function to load the add appointment form
function loadAddAppointmentForm() {
  $.ajax({
    url: "appointments/add_appointment_form.php",
    method: "GET",
    success: function (data) {
      console.log("✅ add_appointment_form.php loaded");
      $(".main-container").html(data);

      // now load its JS once, safely
      $.getScript("../assets/js/add_appointment_form.js");
    },
    error: function (xhr, status, error) {
      console.error("Form AJAX Error:", status, error);
      alert("Error loading appointment form");
    },
  });
}

// Function to handle appointment actions (complete, cancel, etc.)
function handleAppointmentAction(appointmentId, action) {
  // We'll implement this in later steps
  console.log("Appointment action:", appointmentId, action);
}

// Add this after your existing functions in appointment_page.js

// Function to set up event listeners for table buttons (Complete, Set Next)
function setupTableButtonListeners() {
  console.log("Setting up table button listeners");

  // Complete Appointment button - using event delegation
  // In setupTableButtonListeners, update the Complete button handler:
  $(document).on("click", ".complete-appointment-btn", function () {
    const appointmentId = $(this).data("id");
    const patientId = $(this).data("patient-id");
    const patientName = $(this).data("patient-name");
    console.log("Complete appointment button clicked:", {
      appointmentId,
      patientId,
      patientName,
    });
    completeAppointment(appointmentId, patientId, patientName);
  });

  // Set Next Appointment button - using event delegation
  $(document).on("click", ".set-next-btn", function () {
    const patientId = $(this).data("id");
    const patientName = $(this).data("name");
    console.log("Set next appointment for:", patientId, patientName);
    setNextAppointment(patientId, patientName);
  });
}

// Function to load appointment form with patient pre-selected
function loadAddAppointmentFormWithPatient(patientId, patientName) {
  $.ajax({
    url: "appointments/add_appointment_form.php",
    method: "GET",
    success: function (data) {
      console.log("✅ add_appointment_form.php loaded");
      $(".main-container").html(data);

      // Wait a moment for the form to render, then pre-select the patient
      setTimeout(() => {
        if (typeof selectPatient === "function") {
          selectPatient(patientId, patientName, "");
        } else {
          console.error("selectPatient function not found");
          // Fallback: show alert and let user select manually
          Swal.fire({
            title: "Patient Ready",
            text: `Please select ${patientName} from the patient search`,
            icon: "info",
          });
        }
      }, 500);
    },
    error: function (xhr, status, error) {
      console.error("Form AJAX Error:", status, error);
      alert("Error loading appointment form");
    },
  });
}
// Function to complete an appointment with option to set next appointment
function completeAppointment(appointmentId, patientId, patientName) {
  console.log("completeAppointment function called with ID:", appointmentId);

  Swal.fire({
    title: "Complete Appointment?",
    html: `Are you sure you want to mark <strong>${patientName}'s</strong> appointment as completed?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, complete it!",
    cancelButtonText: "Cancel",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      console.log("User confirmed, making AJAX call...");

      $.ajax({
        url: "appointments/complete_appointment.php",
        method: "POST",
        data: { appointment_id: appointmentId },
        dataType: "json",
        success: function (response) {
          console.log("AJAX Success Response:", response);
          if (response.success) {
            Swal.fire({
              title: "Completed!",
              html: `Appointment marked as completed!<br><br>Would you like to set the next appointment for <strong>${patientName}</strong>?`,
              icon: "success",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#6c757d",
              confirmButtonText: "Yes, set next appointment",
              cancelButtonText: "No, back to list",
            }).then((result) => {
              if (result.isConfirmed) {
                // Open the add appointment form pre-filled with this patient
                loadAddAppointmentFormWithPatient(patientId, patientName);
              } else {
                loadAppointments(); // Refresh the list
              }
            });
          } else {
            Swal.fire({
              title: "Error!",
              text: response.message,
              icon: "error",
              confirmButtonColor: "#3085d6",
            });
          }
        },
        error: function (xhr, status, error) {
          console.log("AJAX Error Details:", {
            status: status,
            error: error,
            responseText: xhr.responseText,
          });
          Swal.fire({
            title: "Error!",
            text: "Failed to complete appointment. Please try again.",
            icon: "error",
            confirmButtonColor: "#3085d6",
          });
        },
      });
    } else {
      console.log("User cancelled the operation");
    }
  });
}

// Function to set next appointment
function setNextAppointment(patientId, patientName) {
  console.log("Setting next appointment for:", patientId, patientName);

  Swal.fire({
    title: "Set Next Appointment",
    html: `Set next appointment for <strong>${patientName}</strong>?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, set next appointment",
    cancelButtonText: "Cancel",
    reverseButtons: true,
  }).then((result) => {
    if (result.isConfirmed) {
      loadAddAppointmentFormWithPatient(patientId, patientName);
    }
  });
}

// Function to switch to upcoming appointments view
function switchToUpcomingView() {
    $('#viewUpcomingBtn').removeClass('btn-outline-primary').addClass('btn-primary');
    $('#viewCompletedBtn').removeClass('btn-primary').addClass('btn-outline-secondary');
    $('#appointmentsTable thead th:eq(4)').text('Status'); // Reset status column header
    loadAppointments();
}

// Function to switch to completed appointments view
function switchToCompletedView() {
    $('#viewCompletedBtn').removeClass('btn-outline-secondary').addClass('btn-primary');
    $('#viewUpcomingBtn').removeClass('btn-primary').addClass('btn-outline-primary');
    $('#appointmentsTable thead th:eq(4)').text('Status'); // Keep status column header
    loadCompletedAppointments();
}

// Function to load completed appointments
function loadCompletedAppointments() {
    $('#loadingMessage').removeClass('d-none');
    $('#noAppointmentsMessage').addClass('d-none');
    $('#appointmentsTableBody').html('');
    
    // Get filter values
    const filterDate = $('#filterDate').val();
    const filterType = $('#filterType').val();
    
    $.ajax({
        url: "appointments/fetch_appointments.php",
        method: "POST",
        dataType: "json",
        data: { 
            action: "fetchCompletedAppointments",
            filter_date: filterDate,
            filter_type: filterType
        },
        success: function(response) {
            $('#loadingMessage').addClass('d-none');
            if (response.table_data) {
                $('#appointmentsTableBody').html(response.table_data);
                setupTableButtonListeners();
            } else {
                $('#noAppointmentsMessage').removeClass('d-none');
            }
        },
        error: function(xhr, status, error) {
            $('#loadingMessage').addClass('d-none');
            console.error("Completed appointments AJAX Error:", status, error);
            $('#appointmentsTableBody').html(
                '<tr><td colspan="7" class="text-center text-danger">Error loading completed appointments.</td></tr>'
            );
        }
    });
}

//set appointment
$(document).on('submit', '#addAppointmentForm', function(e) {

    e.preventDefault();
    
    const formData = $(this).serialize();
    console.log('Form data being sent:', formData);
    
    // Validate that a patient is selected
    const patientId = $('#selectedPatientId').val();
    if (!patientId) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a patient first.',
            icon: 'error'
        });
        return;
    }
    
    $.ajax({
        url: "appointments/save_appointment.php",
        method: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            console.log('Save appointment response:', response);
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success'
                }).then(() => {
                    // Go back to appointments list
                    loadAppointmentsPage();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message || 'Failed to save appointment',
                    icon: 'error'
                });
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', {status, error, responseText: xhr.responseText});
            Swal.fire({
                title: 'Connection Error!',
                text: 'Failed to connect to server. Please try again.',
                icon: 'error'
            });
        }
    });
});

// Function to load the main appointments page
function loadAppointmentsPage() {
  $.ajax({
    url: "appointments/appointment_page.php",
    method: "GET",
    success: function (data) {
      console.log("✅ appointment_page.php loaded");
      $(".main-container").html(data);
      
      // Reinitialize the appointments list
      loadAppointments();
      
      // Reattach event listeners
      $("#filterDate, #filterStatus, #filterType").on("change", function () {
        loadAppointments();
      });
      
      $("#viewUpcomingBtn").on("click", function () {
        switchToUpcomingView();
      });
      
      $("#viewCompletedBtn").on("click", function () {
        switchToCompletedView();
      });
    },
    error: function (xhr, status, error) {
      console.error("Error loading appointments page:", status, error);
      Swal.fire({
        title: "Error!",
        text: "Could not load appointments page.",
        icon: "error",
      });
    },
  });
}