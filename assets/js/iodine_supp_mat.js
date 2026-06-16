function refreshIodineInfo(pregId) {
    $.ajax({
      
        url: "patient/maternal/get_iodine_supp.php",
        method: "POST",
        data: { pregnancy_id: pregId },
        success: function (data) {
          
            $("#iodine-info-" + pregId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating checkup info:", error);
        }
    });
}

$(document).on("click", ".add_iodine_btn", function () {
    let pregId = $(this).data("preg-id");
    let currentIodineStat = $(this).attr("data-iodine-status");
    $("#iodine_pregnancy_id").val(pregId); 

    $('#iodine_checkbox').prop('checked', currentIodineStat == 1 || currentIodineStat === '1');
    
   
    $('#viewPregnancyRecord').modal('hide');
    //$('#myModal').modal('hide');
    
    
    $("#addIodineModal").modal("show");
});


$("#addIodineForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let pregId = $('#iodine_pregnancy_id').val();

    $.ajax({
        url: "patient/maternal/add_iodine_supp.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addIodineModal").modal("hide");
                $('#addIodineForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "Iodine Updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });           
                refreshIodineInfo(pregId); 
            }else{
                Swal.fire("Error 🚨", "Server reported an issue saving the data. Please check PHP code.", "error");
                console.error("Server Response (not 'success'):", response);
            }
        },
        error: function (xhr, status, error) {
             console.error("Error Saving iodine data:", error);
             Swal.fire("Error", "There was an issue saving the iodine data.", "error");
        }
    });
});


$('#addIodineModal').on('hidden.bs.modal', function () {
 
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