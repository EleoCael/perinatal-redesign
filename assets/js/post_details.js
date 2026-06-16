function refreshPostDetailsInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_post_details.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#post_delivery_date_' + pregId).text(data.post_delivery_date);
        $('#post_delivery_time_' + pregId).text(data.post_delivery_time);
        $('#breastfeeding_date_' + pregId).text(data.breastfeeding_date);
        $('#breastfeeding_time_' + pregId).text(data.breastfeeding_time);
  
       
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Postpartum Details info:", error);
       console.log('Response:', xhr.responseText);
    },
  });
}

let pendingPostDetailsPregId = null;
$(document).on("click", ".add_postpartum_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#postpartum_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addPostpartumModal").modal("show");
  }, 300);
});

$("#addPostpartumForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#postpartum_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_post_details.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
         pendingPostDetailsPregId = pregId;  
        $("#addPostpartumModal").modal("hide");
        $("#addPostpartumForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Postpartum Details Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshPostDetailsInfo(pregId);
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
      console.error("Error Saving Infectious Disease Screening data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Infectious Disease Screening data.",
        "error"
      );
    },
  });
});

$("#addPostpartumModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingPostDetailsPregId) {
    setTimeout(function () {
      refreshPostDetailsInfo(pendingPostDetailsPregId);
      pendingPostDetailsPregId = null;
    }, 300);
  }
});