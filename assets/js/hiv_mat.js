function refreshHivInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_hiv.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }

        $('#hiv_date_' + pregId).text(data.hiv_date);
        $('#hiv_screening_' + pregId).text(data.hiv_screening);
        $('#hiv_remarks_' + pregId).text(data.hiv_screening_remarks);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating HIV Screening info:", error);
    },
  });
}

let pendingHivPregId = null;
$(document).on("click", ".add_hiv_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#hiv_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addHivModal").modal("show");
  }, 300);
});

$("#addHivForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#hiv_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_hiv.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
         pendingHivPregId = pregId;  
        $("#addHivModal").modal("hide");
        $("#addHivForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "HIV Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshHivInfo(pregId);
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
      console.error("Error Saving HIV data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the HIV data.",
        "error"
      );
    },
  });
});

$("#addHivModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingHivPregId) {
    setTimeout(function () {
      refreshHivInfo(pendingHivPregId);
      pendingHivPregId = null;
    }, 300);
  }
});