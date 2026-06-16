function refreshCalciumInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_calcium_mat.php",
        method: "POST",
        data: { pregnancy_id: pregId, supplement_type : 'Calcium Carbonate'},
        success: function (data) {
          
            $("#calcium-info-" + pregId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating Calcium Supplement info:", error);
        }
    });
}

$(document).on("click", ".add_calcium_btn", function () {
    let pregId = $(this).data("preg-id");
    $("#calcium_pregnancy_id").val(pregId); 

   $('#viewPregnancyRecord').modal('hide');

    $("#addCalciumModal").modal("show");
});


$("#addCalciumForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#calcium_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_calcium_supp.php",
        method: "POST",
        data: formData,
        success: function (response) {
             $('#addCalciumForm')[0].reset();
            $("#addCalciumModal").modal("hide");

            Swal.fire({
                title: "Success!",
                text: "Calcium Supplement added successfully.",
                icon: "success",
                showConfirmButton: true,
            });         
            refreshCalciumInfo(pregId); 
        },
        error: function (xhr, status, error) {
             console.error("Error Saving checkup data:", error);
             Swal.fire("Error", "There was an issue saving the Calcium supplement data.", "error");
        }
    });
});

$('#addCalciumModal').on('hidden.bs.modal', function () {
 
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

// edit modal 
$(document).on("click", ".edit_calcium_btn", function () {
    let supplementId = $(this).data("supplement-id");
    let pregId = $(this).data("preg-id");
    let trimester = $(this).data("trimester");
    let tabletsGiven = $(this).data("tablets-given");
    let dateSupp = $(this).data("date-supp");
    
    openEditCalciumModal(supplementId, pregId, trimester, tabletsGiven, dateSupp);
});

function openEditCalciumModal(supplementId, pregId, trimester, tabletsGiven, dateSupp) {
    
    $("#edit_calcium_supplement_id").val(supplementId);
    $("#edit_calcium_pregnancy_id").val(pregId);
    $("#edit_calcium_trimester").val(trimester);
    $("#edit_calcium_tablets_given").val(tabletsGiven);
    $("#edit_calcium_date_supp").val(dateSupp);
  
    $("#editCalciumModal").modal("show");
}


$("#saveEditCalciumBtn").on("click", function() {
    saveCalciumChanges();
});

function saveCalciumChanges() {
    const formData = new FormData(document.getElementById('editCalciumForm'));

    const trimester = $("#edit_calcium_trimester").val();
    const tabletsGiven = $("#edit_calcium_tablets_given").val();
    const dateSupp = $("#edit_calcium_date_supp").val();
    const pregnancyId = $("#edit_calcium_pregnancy_id").val();

    if (!trimester || !tabletsGiven || !dateSupp) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    const saveBtn = $("#saveEditCalciumBtn");
    saveBtn.prop('disabled', true);
    saveBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

    $.ajax({
        url: "patient/maternal/update_calcium_supplement.php",
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
                    text: 'Calcium supplement updated successfully!',
                    confirmButtonColor: '#3085d6',
                    timer: 2000,
                    showConfirmButton: true
                }).then(() => {
            
                    $("#editCalciumModal").modal("hide");
                    refreshCalciumInfo(pregnancyId);
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
                text: 'An error occurred while updating the calcium supplement',
                confirmButtonColor: '#d33'
            });
        },
        complete: function() {
            
            saveBtn.prop('disabled', false);
            saveBtn.html('Save Changes');
        }
    });
}


