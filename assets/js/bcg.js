function refreshBcgInfo(patientId) {
    $.ajax({
      
        url: "patient/infant/get_bcg.php",
        method: "POST",
        data: { patient_id: patientId },
        success: function (data) {
          
            $("#bcg-info-" + patientId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating BCG status info:", error);
        }
    });
}

$(document).on("click", ".add_bcg_btn", function () {
    let patientId = $(this).data("patient-id");
    let currentBcgStat = $(this).attr("data-bcg-status");
    $("#bcg_patient_id").val(patientId); 

    $('#bcg_checkbox').prop('checked', currentBcgStat == 1 || currentBcgStat === '1');
    
   
    $('#myInfantModal').modal('hide');
   // $('#myModal').modal('hide');
    
    
    $("#addBCGModal").modal("show");
});


$("#addBCGForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $('#bcg_patient_id').val();

    $.ajax({
        url: "patient/infant/add_bcg.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addBCGModal").modal("hide");
                $('#addBCGForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "BCG Status updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshBcgInfo(patientId); 
            } else{
                 Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);

            }      
        },
        error: function (xhr, status, error) {
             console.error("Error Saving BCG status:", error);
             Swal.fire("Error", "There was an issue saving the BCG status.", "error");
        }
    });
});


$('#addBCGModal').on('hidden.bs.modal', function () {
 
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