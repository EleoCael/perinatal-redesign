function refreshFicInfo(patientId) {
    $.ajax({
      
        url: "patient/infant/get_fic.php",
        method: "POST",
        data: { patient_id: patientId },
        success: function (data) {
          
            $("#fic-info-" + patientId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating FIC status info:", error);
        }
    });
}

$(document).on("click", ".add_fic_btn", function () {
    let patientId = $(this).data("patient-id");
    let currentFicStat = $(this).attr("data-fic-status");
    $("#fic_patient_id").val(patientId); 

    $('#fic_checkbox').prop('checked', currentFicStat == 1 || currentFicStat === '1');
    
   
    $('#myInfantModal').modal('hide');
   // $('#myModal').modal('hide');
    
    
    $("#addFicModal").modal("show");
});


$("#addFicForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $('#fic_patient_id').val();

    $.ajax({
        url: "patient/infant/add_fic.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addFicModal").modal("hide");
                $('#addFicForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "FIC Status updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshFicInfo(patientId); 
            } else{
                 Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);

            }      
        },
        error: function (xhr, status, error) {
             console.error("Error Saving FIC status:", error);
             Swal.fire("Error", "There was an issue saving the FIC status.", "error");
        }
    });
});


$('#addFicModal').on('hidden.bs.modal', function () {
 
    setTimeout(() => {
      
        $('#myInfantModal').modal('show');
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