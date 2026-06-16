function refreshBreastFeedCheckInfo(patientId) {
    $.ajax({
      
        url: "patient/infant/get_breastfeed_checkbox.php",
        method: "POST",
        data: { patient_id: patientId },
        success: function (data) {
          
            $("#breastfeed-info-" + patientId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating Breastfeeding status info:", error);
        }
    });
}

$(document).on("click", ".add_breastfeed_btn", function () {
    let patientId = $(this).data("patient-id");
    let currentBreastfeedStat = $(this).attr("data-breastfeed-status");
    $("#breastfeed_patient_id").val(patientId); 

    $('#breastfeed_checkbox').prop('checked', currentBreastfeedStat == 1 || currentBreastfeedStat === '1');
    
   
    $('#myInfantModal').modal('hide');
   // $('#myModal').modal('hide');
    
    
    $("#addBreastfeedModal").modal("show");
});


$("#addBreastfeedForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $('#breastfeed_patient_id').val();

    $.ajax({
        url: "patient/infant/breastfeed_check.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addBreastfeedModal").modal("hide");
                $('#addBreastfeedForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "Breastfeed Status updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshBreastFeedCheckInfo(patientId); 
            } else{
                 Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);

            }      
        },
        error: function (xhr, status, error) {
             console.error("Error Saving Breastfeed status:", error);
             Swal.fire("Error", "There was an issue saving the FIM status.", "error");
        }
    });
});


$('#addBreastfeedModal').on('hidden.bs.modal', function () {
 
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