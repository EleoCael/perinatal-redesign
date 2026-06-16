function refreshGivenIronInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_given_iron.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#given_iron_' + pregId).text(data.given_iron);
        $('#given_iron_date_' + pregId).text(data.given_iron_date);
        $('#maternal_screening_remark_' + pregId).text(data.maternal_screening_remark);
       
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Given Iron Screening info:", error);
    },
  });
}


$(document).on("click", ".add_given_iron_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#given_iron_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addGIvenIronModal").modal("show");
  }, 300);
});

let pendingGivenIronPregId = null;
$("#addGIvenIronForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#given_iron_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_given_iron.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
         pendingGivenIronPregId = pregId;  
        $("#addGIvenIronModal").modal("hide");
        $("#addGIvenIronForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Given Iron Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshGivenIronInfo(pregId);
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
      console.error("Error Saving Given Iron Screening data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Given Iron Screening data.",
        "error"
      );
    },
  });
});

$("#addGIvenIronModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingGivenIronPregId) {
    setTimeout(function () {
      refreshGivenIronInfo(pendingGivenIronPregId);
      pendingGivenIronPregId = null;
    }, 300);
  }
});