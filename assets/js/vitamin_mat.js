function refreshVitaminInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_vitamin.php",
        method: "POST",
        data: { pregnancy_id: pregId },
        success: function (data) {
          
            $("#vitamin-info-" + pregId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating checkup info:", error);
        }
    });
}

$(document).on("click", ".add_vitamin_btn", function () {
    let pregId = $(this).data("preg-id");
    let currentVitaminStat = $(this).attr("data-vitamin-status");
    $("#vitamin_pregnancy_id").val(pregId); 

    $('#vitamin_checkbox').prop('checked', currentVitaminStat == 1 || currentVitaminStat === '1');
    
   
    $('#viewPregnancyRecord').modal('hide');
    //$('#myModal').modal('hide');
    
    
    $("#addVitaminModal").modal("show");
});


$("#addVitaminForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#vitamin_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_vitamin.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addVitaminModal").modal("hide");
                $('#addVitaminForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "Vitamin A Updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });           
                refreshVitaminInfo(pregId); 
            }else{
                Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);
            }
        },
        error: function (xhr, status, error) {
             console.error("Error Saving iodine data:", error);
             Swal.fire("Error", "There was an issue saving the Vitamin A data.", "error");
        }
    });
});


$('#addVitaminModal').on('hidden.bs.modal', function () {
 
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