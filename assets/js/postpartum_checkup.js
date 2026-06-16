function refreshPostpartumCheckupInfo(patientId) {
    $.ajax({
        url: "patient/postpartum/get_postpartum_checkup.php",
        method: "POST",
        data: { patient_id: patientId },
        success: function (data) {
            $("#post-checkup-info-" + patientId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating checkup info:", error);
        }
    });
}

$(document).on("click", ".add_postpartum_checkup_btn", function () {
    let patientId = $(this).data("patient-id");
    $("#postpartum_checkup_patient_id").val(patientId); 
    
    $('#myPostpartumModal').modal('hide');
    
    $("#addPostCheckupModal").modal("show");
});

$("#addPostpartumCheckupForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $('#postpartum_checkup_patient_id').val();

    $.ajax({
        url: "patient/postpartum/add_postpartum_checkup.php",
        method: "POST",
        data: formData,
        success: function (response) {
            $('#addPostpartumCheckupForm')[0].reset();
            $("#addPostCheckupModal").modal("hide");

            Swal.fire({
                title: "Success!",
                text: "Check-up added successfully.",
                icon: "success",
                showConfirmButton: true,
            });

            refreshPostpartumCheckupInfo(patientId); 
        },
        error: function (xhr, status, error) {
             console.error("Error Saving checkup data:", error);
             Swal.fire("Error", "There was an issue saving the check-up data.", "error");
        }
    });
});

$('#addPostCheckupModal').on('hidden.bs.modal', function () {
    setTimeout(() => {
        $('#myPostpartumModal').modal('show');
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

$(document).on("click", ".edit_postpartum_checkup_btn", function () {
    let checkupId = $(this).data("checkup-id");
    let patientId = $(this).data("patient-id");
    let checkupVisit = $(this).data("checkup-visit");
    let postCheckupDate = $(this).data("post-checkup-date");
    
    // Store original values in data attributes
    $("#editPostpartumCheckupForm")
        .data("original-checkup-visit", checkupVisit)
        .data("original-post-checkup-date", postCheckupDate);
    
    openEditPostpartumCheckupModal(checkupId, patientId, checkupVisit, postCheckupDate);
});

function openEditPostpartumCheckupModal(checkupId, patientId, checkupVisit, postCheckupDate) {
    $("#edit_postpartum_checkup_id").val(checkupId);
    $("#edit_postpartum_patient_id").val(patientId);
    $("#edit_postpartum_checkup_visit").val(checkupVisit);
    $("#edit_postpartum_checkup_date").val(postCheckupDate);
    
    $("#editPostpartumCheckupModal").modal("show");
}

$("#saveEditPostpartumCheckupBtn").on("click", function() {
    savePostpartumCheckupChanges();
});

function savePostpartumCheckupChanges() {
    const formData = new FormData(document.getElementById('editPostpartumCheckupForm'));
 
    const checkupVisit = $("#edit_postpartum_checkup_visit").val();
    const postCheckupDate = $("#edit_postpartum_checkup_date").val();
    const patientId = $("#edit_postpartum_patient_id").val();

    // Check if any values actually changed
    let originalVisit = $("#editPostpartumCheckupForm").data("original-checkup-visit");
    let originalDate = $("#editPostpartumCheckupForm").data("original-post-checkup-date");
    
    if (checkupVisit === originalVisit && postCheckupDate === originalDate) {
        // No changes made, just close the modal
        $("#editPostpartumCheckupModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }

    if (!checkupVisit || !postCheckupDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    const saveBtn = $("#saveEditPostpartumCheckupBtn");
    saveBtn.prop('disabled', true);
    saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

    $.ajax({
        url: "patient/postpartum/update_postpartum_checkup.php",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(data) {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Postpartum checkup updated successfully!',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: true
                }).then(() => {
                    $("#editPostpartumCheckupModal").modal("hide");
                    refreshPostpartumCheckupInfo(patientId);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonColor: '#d33'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while updating the postpartum checkup',
                confirmButtonColor: '#d33'
            });
        },
        complete: function() {
            saveBtn.prop('disabled', false);
            saveBtn.html('Save Changes');
        }
    });
}