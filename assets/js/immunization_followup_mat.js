function refreshImmunizationInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_immunization_followup.php",
        method: "POST",
        data: { pregnancy_id: pregId },
        success: function (data) {
          
            $("#immunization-info-" + pregId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating checkup info:", error);
        }
    });
}

$(document).on("click", ".add_immunization_btn", function () {
    let pregId = $(this).data("preg-id");
    $("#immunization_pregnancy_id").val(pregId); 

   $('#viewPregnancyRecord').modal('hide');

    $("#addImmunizationModal").modal("show");
});


$("#addImmunizationForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#immunization_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_immunization.php",
        method: "POST",
        data: formData,
        success: function (response) {
            $('#addImmunizationForm')[0].reset();
            $("#addImmunizationModal").modal("hide");

            Swal.fire({
                title: "Success!",
                text: "Immunization added successfully.",
                icon: "success",
                showConfirmButton: true,
            });         
            refreshImmunizationInfo(pregId); 
        },
        error: function (xhr, status, error) {
             console.error("Error Saving checkup data:", error);
             Swal.fire("Error", "There was an issue saving the immunization data.", "error");
        }
    });
});

$('#addImmunizationModal').on('hidden.bs.modal', function () {
 
    setTimeout(() => {
      
        $('#viewPregnancyRecord').modal('show');
        $('#myModal').modal('show');
    }, 300);
});

$(document).on('hidden.bs.modal', '.modal', function () {
   
    if ($('.modal.show').length > 0) {
          $('body').addClass('modal-open');
        
    }else {
       
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    }
});


$(document).on("click", ".edit_immunization_btn", function () {
    
    let immunizationId = $(this).data("immunization-id");
    let pregId = $(this).data("preg-id");
    let immunizationType = $(this).data("immunization-type");
    let immunizationDate = $(this).data("immunization-date");
    
    openEditImmunizationModal(immunizationId, pregId, immunizationType, immunizationDate);
});

function openEditImmunizationModal(immunizationId, pregId, immunizationType, immunizationDate) {
  
    $("#edit_immunization_id").val(immunizationId);
    $("#edit_immunization_pregnancy_id").val(pregId);
    $("#edit_immunization_type").val(immunizationType);
    $("#edit_immunization_date").val(immunizationDate);
    $("#editImmunizationModal").modal("show");
}


$("#saveEditImmunizationBtn").on("click", function() {
    saveImmunizationChanges();
});

function saveImmunizationChanges() {
    const formData = new FormData(document.getElementById('editImmunizationForm'));
    
    // Get form values for validation
    const immunizationType = $("#edit_immunization_type").val();
    const immunizationDate = $("#edit_immunization_date").val();
    const pregnancyId = $("#edit_immunization_pregnancy_id").val();
    
    console.log("Form Data:", {
        immunizationType: immunizationType,
        immunizationDate: immunizationDate,
        pregnancyId: pregnancyId
    });
    
    // Validation
    if (!immunizationType || !immunizationDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    // Show loading state
    const saveBtn = $("#saveEditImmunizationBtn");
    saveBtn.prop('disabled', true);
    saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

    // Send AJAX request to update immunization
    $.ajax({
        url: "patient/maternal/update_immunization.php",
        method: "POST",
        data: $( "#editImmunizationForm" ).serialize(), // Use serialize instead of FormData
        dataType: "json", // Expect JSON response
        success: function(data) {
            console.log("Success Response:", data);
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Immunization updated successfully!',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: true
                }).then(() => {
                    // Close the modal
                    $("#editImmunizationModal").modal("hide");
                    
                    // Refresh the immunization list
                    refreshImmunizationInfo(pregnancyId);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Update failed',
                    confirmButtonColor: '#d33'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the immunization',
                confirmButtonColor: '#d33'
            });
        },
        complete: function() {
            // Reset button state
            saveBtn.prop('disabled', false);
            saveBtn.html('Save Changes');
        }
    });
}

