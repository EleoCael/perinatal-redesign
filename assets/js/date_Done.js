function refreshDateDoneInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_date_done.php",
    method: "POST",
    data: { patient_id: patientId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#date_done_' + patientId).text(data.date_done);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Date Done info:", error);
    },
  });
}

let pendingDateDonePatientId = null;
$(document).on("click", ".add_date_done_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#dane_done_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addDateDoneModal").modal("show");
  }, 300);
});

$("#addDateDoneForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#dane_done_patient_id").val();

  $.ajax({
    url: "patient/infant/add_date_done.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingDateDonePatientId = patientId;  
        $("#addDateDoneModal").modal("hide");
        $("#addDateDoneForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Date Done Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshDateDoneInfo(patientId);
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
      console.error("Error Saving Date Done  data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Date Done  data.",
        "error"
      );
    },
  });
});

$("#addDateDoneModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$("#myModal").modal("show");
  }, 200);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingDateDonePatientId) {
    setTimeout(function () {
      refreshDateDoneInfo(pendingDateDonePatientId);
      pendingDateDonePatientId = null;
    }, 300);
  }
});