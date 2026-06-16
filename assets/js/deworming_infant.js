function refreshDewormingInfantInfo(patientId) {
    $.ajax({
      
        url: "patient/infant/get_deworming.php",
        method: "POST",
        data: { patient_id: patientId },
        success: function (data) {
          
            $("#deworming-info-" + patientId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating Deworming status info:", error);
        }
    });
}

$(document).on("click", ".add_deworming_btn", function () {
    let patientId = $(this).data("patient-id");
    let currentDewormStat = $(this).attr("data-deworming-status");
    $("#deworming_patient_id").val(patientId); 

    $('#deworm_checkbox').prop('checked', currentDewormStat == 1 || currentDewormStat === '1');
    
   
    $('#myInfantModal').modal('hide');
   // $('#myModal').modal('hide');
    
    
    $("#addDewormingInfantModal").modal("show");
});


$("#addDewormingInfantForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $('#deworming_patient_id').val();

    $.ajax({
        url: "patient/infant/add_deworming_infant.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addDewormingInfantModal").modal("hide");
                $('#addDewormingInfantForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "Deworming Status updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshDewormingInfantInfo(patientId); 
            } else{
                 Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);

            }      
        },
        error: function (xhr, status, error) {
             console.error("Error Saving Deworming status:", error);
             Swal.fire("Error", "There was an issue saving the Deworming status.", "error");
        }
    });
});


$('#addDewormingInfantModal').on('hidden.bs.modal', function () {
 
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