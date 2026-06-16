function refreshPlaceNonHealthInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_place_non_health.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#non_health_facility_type_' + pregId).text(data.non_facility_type);
        $('#non_health_facility_name_' + pregId).text(data.non_facility_name);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Place of Delivery info:", error);
    },
  });
}

let pendingNonHealthPregId = null;
$(document).on("click", ".add_non_health_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#place_non_health_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addPlaceNonHealthModal").modal("show");
  }, 300);
});

$("#addPlaceNonHealthForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#place_non_health_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_place_non_health.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingNonHealthPregId = pregId;  
        $("#addPlaceNonHealthModal").modal("hide");
        $("#addPlaceNonHealthForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Place of Delivery Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshPlaceNonHealthInfo(pregId);
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
      console.error("Error Saving Place of Delivery data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Place of Delivery data.",
        "error"
      );
    },
  });
});

$("#addPlaceNonHealthModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingNonHealthPregId) {
    setTimeout(function () {
      refreshPlaceNonHealthInfo(pendingNonHealthPregId);
      pendingNonHealthPregId = null;
    }, 300);
  }
});