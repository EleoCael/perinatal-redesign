function refreshPlaceInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_place_birth.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#health_facility_type_' + pregId).text(data.facility_type);
        $('#health_facility_name_' + pregId).text(data.facility_name);
        $('#bemonc_cemonc_capable_' + pregId).text(data.bemonc_cemonc_capable);
        $('#ownership_' + pregId).text(data.ownership);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Place of Delivery info:", error);
    },
  });
}

let pendingPlacePregId = null;
$(document).on("click", ".add_place_birth_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#place_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addPlaceBirthModal").modal("show");
  }, 300);
});

$("#addPlaceBirthForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#place_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_place_birth.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingPlacePregId = pregId;  
        $("#addPlaceBirthModal").modal("hide");
        $("#addPlaceBirthForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Place of Delivery Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshPlaceInfo(pregId);
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

$("#addPlaceBirthModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingPlacePregId) {
    setTimeout(function () {
      refreshPlaceInfo(pendingPlacePregId);
      pendingPlacePregId = null;
    }, 300);
  }
});