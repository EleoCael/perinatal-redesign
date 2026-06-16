function refreshFimInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_fim_stat.php",
        method: "POST",
        data: { pregnancy_id: pregId },
        success: function (data) {
          
            $("#fim-info-" + pregId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating FIM status info:", error);
        }
    });
}

$(document).on("click", ".add_fim_btn", function () {
    let pregId = $(this).data("preg-id");
    let currentFimStat = $(this).attr("data-fim-status");
    $("#fim_pregnancy_id").val(pregId); 

    $('#fim_status_checkbox').prop('checked', currentFimStat == 1 || currentFimStat === '1');
    
   
    $('#viewPregnancyRecord').modal('hide');
   // $('#myModal').modal('hide');
    
    
    $("#addFimModal").modal("show");
});


$("#addFimForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#fim_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_fim.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addFimModal").modal("hide");
                $('#addFimForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "FIM Status updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshFimInfo(pregId); 
            } else{
                 Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);

            }      
        },
        error: function (xhr, status, error) {
             console.error("Error Saving FIM status:", error);
             Swal.fire("Error", "There was an issue saving the FIM status.", "error");
        }
    });
});


$('#addFimModal').on('hidden.bs.modal', function () {
 
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