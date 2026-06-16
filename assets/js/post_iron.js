function refreshPostIronInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_post_iron.php",
        method: "POST",
        data: { pregnancy_id: pregId },
        success: function (data) {
          
            $("#post-iron-info-" + pregId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating checkup info:", error);
        }
    });
}

$(document).on("click", ".add_post_iron_btn", function () {
    let pregId = $(this).data("preg-id");
    $("#post_iron_pregnancy_id").val(pregId); 

   $('#viewPregnancyRecord').modal('hide');

    $("#addPostIronModal").modal("show");
});


$("#addPostIronForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#post_iron_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_post_iron.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addPostIronModal").modal("hide");
                $('#addPostIronForm')[0].reset();

            Swal.fire({
                title: "Success!",
                text: "Iron Supplement added successfully.",
                icon: "success",
                showConfirmButton: true,
            });         
            refreshImmunizationInfo(pregId); 
            }else{
                Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);
            }
        },
        error: function (xhr, status, error) {
             console.error("Error Saving checkup data:", error);
             Swal.fire("Error", "There was an issue saving the Iron Supplement data.", "error");
        }
    });
});

$('#addPostIronModal').on('hidden.bs.modal', function () {
 
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
$(document).on("click", ".edit_post_iron_btn", function () {
    let postSuppId = $(this).data("post-supp-id");
    let pregId = $(this).data("preg-id");
    let monthGiven = $(this).data("iron-folic-month-given");
    let dateGiven = $(this).data("iron-folic-date-given");
    let tabletsGiven = $(this).data("tablets-given");
    
    openEditPostIronModal(postSuppId, pregId, monthGiven, dateGiven, tabletsGiven);
});

function openEditPostIronModal(postSuppId, pregId, monthGiven, dateGiven, tabletsGiven) {

    $("#edit_post_supp_id").val(postSuppId);
    $("#edit_post_iron_pregnancy_id").val(pregId);
    $("#edit_iron_folic_month_given").val(monthGiven);
    $("#edit_iron_folic_date_given").val(dateGiven);
    $("#edit_post_tablets_given").val(tabletsGiven);
    
    
    $("#editPostIronModal").modal("show");
}

$("#saveEditPostIronBtn").on("click", function() {
    savePostIronChanges();
});

function savePostIronChanges() {
    const formData = new FormData(document.getElementById('editPostIronForm'));
    

    const monthGiven = $("#edit_iron_folic_month_given").val();
    const dateGiven = $("#edit_iron_folic_date_given").val();
    const tabletsGiven = $("#edit_post_tablets_given").val();
    const pregnancyId = $("#edit_post_iron_pregnancy_id").val();
    

    if (!monthGiven || !dateGiven || !tabletsGiven) {
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

    $.ajax({
        url: "patient/maternal/update_post_iron.php",
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
                    text: 'Postpartum iron supplement updated successfully!',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: true
                }).then(() => {
                 
                    $("#editPostIronModal").modal("hide");
                    
                 
                    refreshPostIronInfo(pregnancyId);
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


