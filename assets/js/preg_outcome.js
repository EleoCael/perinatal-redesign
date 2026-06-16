function refreshPregOutcomeInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_preg_outcome.php",
    method: "POST",
    data: { pregnancy_id: pregId },
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#date_terminated_' + pregId).text(data.date_terminated);
        $('#outcome_' + pregId).text(data.outcome);
        $('#sex_' + pregId).text(data.sex);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Pregnancy Outcome info:", error);
    },
  });
}

let pendingPregId = null;
$(document).on("click", ".add_preg_outcome_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#outcome_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  $("#addPregOutcomeModal").modal("show");
});

$("#addPregOutcomeForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#outcome_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_preg_outcome.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingPregId = pregId;  
        $("#addPregOutcomeModal").modal("hide");
        $("#addPregOutcomeForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Pregnancy Outcome Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshPregOutcomeInfo(pregId);
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
      console.error("Error Saving Pregnancy Outcome data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Pregnancy Outcome data.",
        "error"
      );
    },
  });
});

$("#addPregOutcomeModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingPregId) {
    setTimeout(function () {
      refreshPregOutcomeInfo(pendingPregId);
      pendingPregId = null;
    }, 300);
  }
});