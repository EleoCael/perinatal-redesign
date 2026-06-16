function refreshDateAssessedInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_date_assessed.php",
    method: "POST",
    data: { patient_id: patientId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#cpab_tt_date_assessed_' + patientId).text(data.cpab_tt_date_assessed);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Date Assessed info:", error);
    },
  });
}

let pendingAssessedPatientId = null;
$(document).on("click", ".add_date_assessed_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#dane_assessed_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addDateAssessedModal").modal("show");
  }, 300);
});

$("#addDateAssessedForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#dane_assessed_patient_id").val();

  $.ajax({
    url: "patient/infant/add_date_assessed.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingAssessedPatientId = patientId;  
        $("#addDateAssessedModal").modal("hide");
        $("#addDateAssessedForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Date Assessed Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshDateAssessedInfo(patientId);
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
      console.error("Error Saving Date Assessed data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Date Assessed data.",
        "error"
      );
    },
  });
});

$("#addDateAssessedModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$("#myModal").modal("show");
  }, 200);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingAssessedPatientId) {
    setTimeout(function () {
      refreshDateAssessedInfo(pendingAssessedPatientId);
      pendingAssessedPatientId = null;
    }, 300);
  }
});