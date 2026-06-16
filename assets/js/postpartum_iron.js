function refreshPostpartumIronInfo(patientId) {
    $.ajax({
        url: "patient/postpartum/get_postpartum_iron.php",
        method: "POST",
        data: { patient_id: patientId },
        success: function (data) {
            $("#post-iron-info-" + patientId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating iron info:", error);
        }
    });
}

$(document).on("click", ".add_postpartum_iron_btn", function () {
    let patientId = $(this).data("patient-id");
    $("#postpartum_iron_patient_id").val(patientId); 

    // Check if myPostpartumModal exists before trying to hide it
    if ($('#myPostpartumModal').length) {
        $('#myPostpartumModal').modal('hide');
    }

    $("#addPostpartumIronModal").modal("show");
});

$("#addPostpartumIronForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $('#postpartum_iron_patient_id').val();

    $.ajax({
        url: "patient/postpartum/add_postpartum_iron.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addPostpartumIronModal").modal("hide");
                $('#addPostpartumIronForm')[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "Iron Supplement added successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });         
                refreshPostpartumIronInfo(patientId); 
            } else {
                Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error Saving iron data:", error);
            Swal.fire("Error", "There was an issue saving the Iron Supplement data.", "error");
        }
    });
});

$('#addPostpartumIronModal').on('hidden.bs.modal', function () {
    setTimeout(() => {
        // Check if myPostpartumModal exists before trying to show it
        if ($('#myPostpartumModal').length) {
            $('#myPostpartumModal').modal('show');
        }
    }, 300);
});

$(document).on('hidden.bs.modal', '.modal', function () {
    if ($('.modal.show').length > 0) {
        $('body').addClass('modal-open');
    } else {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    }
});

// Edit modal - UPDATED WITH BETTER DEBUGGING
$(document).on("click", ".edit_postpartum_iron_btn", function () {
    let postSuppId = $(this).data("post-supp-id");
    let patientId = $(this).data("patient-id");
    let monthGiven = $(this).data("iron-folic-month-given");
    let dateGiven = $(this).data("iron-folic-date-given");
    let tabletsGiven = $(this).data("tablets-given");
    
    console.log("🔄 EDIT BUTTON CLICKED - Original values:", {
        monthGiven,
        dateGiven,
        tabletsGiven
    });
    
    // Store original values for change detection
    $("#editPostIronForm")
        .data("original-month-given", monthGiven)
        .data("original-date-given", dateGiven)
        .data("original-tablets-given", tabletsGiven);
    
    openEditPostIronModal(postSuppId, patientId, monthGiven, dateGiven, tabletsGiven);
});

function openEditPostIronModal(postSuppId, patientId, monthGiven, dateGiven, tabletsGiven) {
    $("#edit_post_supp_id").val(postSuppId);
    $("#edit_post_iron_patient_id").val(patientId);
    $("#edit_iron_folic_month_given").val(monthGiven);
    $("#edit_iron_folic_date_given").val(dateGiven);
    $("#edit_post_tablets_given").val(tabletsGiven);
    
    console.log("📝 FORM VALUES SET:", {
        monthGiven: $("#edit_iron_folic_month_given").val(),
        dateGiven: $("#edit_iron_folic_date_given").val(),
        tabletsGiven: $("#edit_post_tablets_given").val()
    });
    
    if ($('#editPostIronModal').length) {
        $("#editPostIronModal").modal("show");
    } else {
        console.error("editPostIronModal not found in DOM");
    }
}

$("#saveEditPostIronBtn").on("click", function() {
    savePostIronChanges();
});

function savePostIronChanges() {
    const monthGiven = $("#edit_iron_folic_month_given").val();
    const dateGiven = $("#edit_iron_folic_date_given").val();
    const tabletsGiven = $("#edit_post_tablets_given").val();
    const patientId = $("#edit_post_iron_patient_id").val();
    
    // Get original values
    let originalMonth = $("#editPostIronForm").data("original-month-given");
    let originalDate = $("#editPostIronForm").data("original-date-given");
    let originalTablets = $("#editPostIronForm").data("original-tablets-given");
    
    console.log("🔍 COMPARING VALUES:");
    console.log("Current:", {monthGiven, dateGiven, tabletsGiven});
    console.log("Original:", {originalMonth, originalDate, originalTablets});
    
    // Convert both tablet values to the same type for comparison
    const currentTablets = String(tabletsGiven).trim();
    const originalTabletsString = String(originalTablets).trim();
    
    console.log("After conversion:");
    console.log("Tablets match:", currentTablets === originalTabletsString);
    console.log("All match:", monthGiven === originalMonth && dateGiven === originalDate && currentTablets === originalTabletsString);
    
    // Check if any values actually changed
    if (monthGiven === originalMonth && dateGiven === originalDate && currentTablets === originalTabletsString) {
        console.log("✅ No changes detected - showing info message");
        // No changes made, just close the modal
        $("#editPostIronModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    } else {
        console.log("🔄 Changes detected - proceeding with update");
    }
    
    // Rest of your existing validation and AJAX code...
    // Validation that checks for empty or "N/A" values
    if (!monthGiven || monthGiven === 'N/A' || !dateGiven || dateGiven === 'N/A' || !tabletsGiven || tabletsGiven === 'N/A') {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    const saveBtn = $("#saveEditPostIronBtn");
    saveBtn.prop('disabled', true);
    saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

    // Use regular form data instead of FormData to ensure consistency
    const formData = {
        post_supp_id: $("#edit_post_supp_id").val(),
        patient_id: patientId,
        iron_folic_month_given: monthGiven,
        iron_folic_date_given: dateGiven,
        tablets_given: tabletsGiven
    };

    console.log("📤 Sending data to server:", formData);

    $.ajax({
        url: "patient/postpartum/update_postpartum_iron.php",
        method: "POST",
        data: formData,
        dataType: "json",
        success: function(data) {
            console.log("📥 Server response:", data);
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Postpartum iron supplement updated successfully!',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: true
                }).then(() => {
                    if ($('#editPostIronModal').length) {
                        $("#editPostIronModal").modal("hide");
                    }
                    refreshPostpartumIronInfo(patientId);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to update supplement',
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
                text: 'An error occurred while updating the postpartum iron supplement',
                confirmButtonColor: '#d33'
            });
        },
        complete: function() {
            saveBtn.prop('disabled', false);
            saveBtn.html('Save Changes');
        }
    });
}