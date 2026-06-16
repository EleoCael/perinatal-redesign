function refreshPostVitaminInfo(patientId) {
    $.ajax({
      
        url: "patient/postpartum/get_postpartum_vitamin.php",
        method: "POST",
        data: {patient_id : patientId },
        success: function (data) {
          
            $("#vitamin-info-" + patientId).html(data);
        },
        error: function (xhr, status, error) {
            console.error("Error Updating checkup info:", error);
        }
    });
}

$(document).on("click", ".add_post_vitamin_btn", function () {
    let patientId = $(this).data("patient-id");
    let currentVitaminStat = $(this).attr("data-vitamin-status");
    $("#post_vitamin_patient_id").val(patientId); 
    console.log("Vitamin Patient ID set to:", patientId);



    $('#vitamin_checkbox').prop('checked', currentVitaminStat == 1 || currentVitaminStat === '1');
    
   
    $('#myPostpartumModal').modal('hide');
    //$('#myModal').modal('hide');
    
    
    $("#addPostVitaminModal").modal("show");
});


$("#addPostVitaminForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $('#post_vitamin_patient_id').val();
    console.log("Form Data:", formData);
    console.log("Patient ID from hidden field:", patientId);
    console.log("Vitamin Checkbox value:", $('#vitamin_checkbox').is(':checked'));
    

    $.ajax({
        url: "patient/postpartum/add_postpartum_vitamin.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#addPostVitaminModal").modal("hide");
                $('#addPostVitaminForm')[0].reset();
    
                Swal.fire({
                    title: "Success!",
                    text: "Vitamin A Updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });           
                refreshPostVitaminInfo(patientId); 
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


$('#addPostVitaminModal').on('hidden.bs.modal', function () {
 
    setTimeout(() => {
      
        $('#myPostpartumModal').modal('show');
        //$('#myModal').modal('show');
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