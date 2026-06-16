function refreshPostpartumDetailsInfo(patientId) {
  $.ajax({
    url: "patient/postpartum/get_postpartum_details2.php",
    method: "POST",
    data: { patient_id: patientId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#post_delivery_date_' + patientId).text(data.post_delivery_date);
        $('#post_delivery_time_' + patientId).text(data.post_delivery_time);
        $('#breastfeeding_date_' + patientId).text(data.breastfeeding_date);
        $('#breastfeeding_time_' + patientId).text(data.breastfeeding_time);
  
       
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Postpartum Details info:", error);
       console.log('Response:', xhr.responseText);
    },
  });
}

let pendingPostpartumDetailsPregId = null;
$(document).on("click", ".add_postpartum_details_btn", function () {
  let patientId = $(this).data("preg-id");
  $("#postpartum_details_patient_id").val(patientId);

  $("#myPostpartumModal").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addPostpartumDetailsModal").modal("show");
  }, 300);
});

$("#addPostpartumDetailsForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#postpartum_details_patient_id").val();

  $.ajax({
    url: "patient/postpartum/add_postpartum_details2.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
         pendingPostpartumDetailsPregId = patientId;  
        $("#addPostpartumDetailsModal").modal("hide");
        $("#addPostpartumDetailsForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Postpartum Details Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshPostpartumDetailsInfo(patientId);
      } else {
        Swal.fire(
          "Error 🚨",
          "Server reported an issue saving the data. Please check PHP code.",
          "error"
        );
        console.error("Server Response (not 'success'):", response);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error Saving Postpartum details data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Postpartum details data.",
        "error"
      );
    },
  });
});

$("#addPostpartumDetailsModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myPostpartumModal").modal("show");
    //$("#myModal").modal("show");
  }, 200);
});

$("#myPostpartumModal").on("shown.bs.modal", function () {
  if (pendingPostpartumDetailsPregId) {
    setTimeout(function () {
      refreshPostpartumDetailsInfo(pendingPostpartumDetailsPregId);
      pendingPostpartumDetailsPregId = null;
    }, 300);
  }
});