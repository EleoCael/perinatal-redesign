function refreshBmiInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_bmi.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#bmi_class_' + pregId).text(data.bmi_class);
        $('#bmi_' + pregId).text(data.bmi);
        $('#deworming_stat_' + pregId).text(data.deworming_stat);
        $('#deworming_date_' + pregId).text(data.deworming_date);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Prenatal Details info:", error);
    },
  });
}

let pendingBmiPregId = null;
$(document).on("click", ".add_bmi_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#bmi_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addPrenatalBmiModal").modal("show");
  }, 300);
});

$("#addPrenatalBmiForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#bmi_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_bmi.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingBmiPregId = pregId;  
        $("#addPrenatalBmiModal").modal("hide");
        //$("#addPrenatalBmiForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Prenatal Details Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshBmiInfo(pregId);
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
      console.error("Error Saving Prenatal Details data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Prenatal Details data.",
        "error"
      );
    },
  });
});

$("#addPrenatalBmiModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingBmiPregId) {
    setTimeout(function () {
      refreshBmiInfo(pendingBmiPregId);
      pendingBmiPregId = null;
    }, 300);
  }
});