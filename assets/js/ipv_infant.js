function refreshIpvInfo(patientId) {
    $.ajax({
      
        url: "patient/infant/get_ipv.php",
        method: "POST",
        data: { patient_id: patientId },
        success: function (data) {
          
            $("#ipv-info-" + patientId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating BCG status info:", error);
        }
    });
}

$(document).on("click", ".add_ipv_btn", function () {
    let patientId = $(this).data("patient-id");
    let currentIpvStat = $(this).attr("data-ipv-status");
    $("#ipv_patient_id").val(patientId); 

    $('#ipv_checkbox').prop('checked', currentIpvStat == 1 || currentIpvStat === '1');
    
   
    $('#myInfantModal').modal('hide');
   // $('#myModal').modal('hide');
    
    
    $("#addIpvModal").modal("show");
});


$("#addIpvForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $('#ipv_patient_id').val();

    $.ajax({
        url: "patient/infant/add_ipv.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addIpvModal").modal("hide");
                $('#addIpvForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "IPV Status updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshIpvInfo(patientId); 
            } else{
                 Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);

            }      
        },
        error: function (xhr, status, error) {
             console.error("Error Saving IPV status:", error);
             Swal.fire("Error", "There was an issue saving the IPV status.", "error");
        }
    });
});


$('#addIpvModal').on('hidden.bs.modal', function () {
 
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