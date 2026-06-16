function refreshPostCheckupInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_post_checkup.php",
        method: "POST",
        data: { pregnancy_id: pregId },
        success: function (data) {
          
            $("#post-checkup-info-" + pregId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating checkup info:", error);
        }
    });
}

$(document).on("click", ".add_post_checkup_btn", function () {
    let pregId = $(this).data("preg-id");
    $("#post_checkup_pregnancy_id").val(pregId); 
    
   
    $('#viewPregnancyRecord').modal('hide');
    $('#myModal').modal('hide');
    
    
    $("#addPostpartumCheckupModal").modal("show");
});


$("#addPostCheckupForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#post_checkup_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_post_checkup.php",
        method: "POST",
        data: formData,
        success: function (response) {
            $('#addPostCheckupForm')[0].reset();
            $("#addPostpartumCheckupModal").modal("hide");

            Swal.fire({
                title: "Success!",
                text: "Check-up added successfully.",
                icon: "success",
                showConfirmButton: true,
            });

           
            refreshPostCheckupInfo(pregId); 
        },
        error: function (xhr, status, error) {
             console.error("Error Saving checkup data:", error);
             Swal.fire("Error", "There was an issue saving the check-up data.", "error");
        }
    });
});


$('#addPostpartumCheckupModal').on('hidden.bs.modal', function () {
 
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

//edit modal
$(document).on("click", ".edit_post_checkup_btn", function () {
    let checkupId = $(this).data("post-checkup-id");
    let pregId = $(this).data("preg-id");
    let checkupVisit = $(this).data("checkup-visit");
    let postCheckupDate = $(this).data("post-checkup-date");
    
    openEditPostCheckupModal(checkupId, pregId, checkupVisit, postCheckupDate);
});

function openEditPostCheckupModal(checkupId, pregId, checkupVisit, postCheckupDate) {
    
    $("#edit_post_checkup_id").val(checkupId);
    $("#edit_post_pregnancy_id").val(pregId);
    $("#edit_checkup_visit").val(checkupVisit);
    $("#edit_post_checkup_date").val(postCheckupDate);
 
    $("#editPostCheckupModal").modal("show");
}

$("#saveEditPostCheckupBtn").on("click", function() {
    savePostCheckupChanges();
});

function savePostCheckupChanges() {
    const formData = new FormData(document.getElementById('editPostCheckupForm'));
    
    const checkupVisit = $("#edit_checkup_visit").val();
    const postCheckupDate = $("#edit_post_checkup_date").val();
    const pregnancyId = $("#edit_post_pregnancy_id").val();
    

    if (!checkupVisit || !postCheckupDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    const saveBtn = $("#saveEditPostCheckupBtn");
    saveBtn.prop('disabled', true);
    saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

    $.ajax({
        url: "patient/maternal/update_postpartum_checkup.php",
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
         
                    $("#editPostCheckupModal").modal("hide");

                    refreshPostpartumCheckupInfo(pregnancyId);
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