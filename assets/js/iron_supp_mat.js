function refreshIronInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_iron_mat.php",
        method: "POST",
        data: { pregnancy_id: pregId, supplement_type : 'Iron Sulfate w/Folic Acid'},
        success: function (data) {
          
            $("#iron-info-" + pregId).html(data);
            
        },
        error: function (xhr, status, error) {
            console.error("Error Updating Iron Supplement info:", error);
        }
    });
}

$(document).on("click", ".add_iron_btn", function () {
    let pregId = $(this).data("preg-id");
    $("#iron_pregnancy_id").val(pregId); 

   $('#viewPregnancyRecord').modal('hide');

    $("#addIronModal").modal("show");
});


$("#addIronForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#iron_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_iron_supp.php",
        method: "POST",
        data: formData,
        success: function (response) {
            $('#addIronForm')[0].reset();
            $("#addIronModal").modal("hide");

            Swal.fire({
                title: "Success!",
                text: "Iron Supplement added successfully.",
                icon: "success",
                showConfirmButton: true,
            });         
            refreshIronInfo(pregId); 
        },
        error: function (xhr, status, error) {
             console.error("Error Saving checkup data:", error);
             Swal.fire("Error", "There was an issue saving the Iron supplement data.", "error");
        }
    });
});

$('#addIronModal').on('hidden.bs.modal', function () {
 
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


$(document).on("click", ".edit_iron_btn", function () {
    let supplementId = $(this).data("supplement-id");
    let pregId = $(this).data("preg-id");
    let trimester = $(this).data("trimester");
    let tabletsGiven = $(this).data("tablets-given");
    let dateSupp = $(this).data("date-supp");
    
    openEditIronModal(supplementId, pregId, trimester, tabletsGiven, dateSupp);
});

function openEditIronModal(supplementId, pregId, trimester, tabletsGiven, dateSupp) {

    $("#edit_iron_supplement_id").val(supplementId);
    $("#edit_iron_pregnancy_id").val(pregId);
    $("#edit_iron_trimester").val(trimester);
    $("#edit_iron_tablets_given").val(tabletsGiven);
    $("#edit_iron_date_supp").val(dateSupp);

    $("#editIronModal").modal("show");
}

// Handle save changes button click for iron supplements
$("#saveEditIronBtn").on("click", function() {
    saveIronChanges();
});

function saveIronChanges() {
    const formData = new FormData(document.getElementById('editIronForm'));
    
    const trimester = $("#edit_iron_trimester").val();
    const tabletsGiven = $("#edit_iron_tablets_given").val();
    const dateSupp = $("#edit_iron_date_supp").val();
    const pregnancyId = $("#edit_iron_pregnancy_id").val();
    
    if (!trimester || !tabletsGiven || !dateSupp) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    const saveBtn = $("#saveEditIronBtn");
    saveBtn.prop('disabled', true);
    saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

    $.ajax({
        url: "patient/maternal/update_iron_supplement.php",
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
                    text: 'Iron supplement updated successfully!',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: true
                }).then(() => {
                    $("#editIronModal").modal("hide");

                    refreshIronInfo(pregnancyId);
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
                text: 'An error occurred while updating the iron supplement',
                confirmButtonColor: '#d33'
            });
        },
        complete: function() {

            saveBtn.prop('disabled', false);
            saveBtn.html('Save Changes');
        }
    });
}

