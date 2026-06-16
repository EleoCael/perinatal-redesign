
function refreshCheckupInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_prenatal_checkup.php",
        method: "POST",
        data: { pregnancy_id: pregId },
        success: function (data) {
          
            $("#checkup-info-" + pregId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating checkup info:", error);
        }
    });
}

$(document).on("click", ".add_checkup_btn", function () {
    let pregId = $(this).data("preg-id");
    $("#checkup_pregnancy_id").val(pregId); 
    
   
    $('#viewPregnancyRecord').modal('hide');
    $('#myModal').modal('hide');
    
    
    $("#addCheckupModal").modal("show");
});


$("#addCheckupForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#checkup_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_checkup.php",
        method: "POST",
        data: formData,
        success: function (response) {
            $('#addCheckupForm')[0].reset();
            $("#addCheckupModal").modal("hide");

            Swal.fire({
                title: "Success!",
                text: "Check-up added successfully.",
                icon: "success",
                showConfirmButton: true,
            });

           
            refreshCheckupInfo(pregId); 
        },
        error: function (xhr, status, error) {
             console.error("Error Saving checkup data:", error);
             Swal.fire("Error", "There was an issue saving the check-up data.", "error");
        }
    });
});


$('#addCheckupModal').on('hidden.bs.modal', function () {
 
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

