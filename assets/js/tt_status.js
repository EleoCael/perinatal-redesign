function refreshTtStatusInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_tt_status.php",
    method: "POST",
    data: { patient_id: patientId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#cpab_tt_status_' + patientId).text(data.cpab_tt_status);
        $('#cpab_tt_date_' + patientId).text(data.cpab_tt_date);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating TT Status info:", error);
    },
  });
}

let pendingTTStatusPatientId = null;
$(document).on("click", ".add_ttstatus_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#tt_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addTTStatusModal").modal("show");
  }, 300);
});

$("#addTTStatusForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#tt_patient_id").val();

  $.ajax({
    url: "patient/infant/add_tt_status.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingTTStatusPatientId = patientId;  
        $("#addTTStatusModal").modal("hide");
        $("#addTTStatusForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "TT Status Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshTtStatusInfo(patientId);
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
      console.error("Error Saving TT Status data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the TT Status data.",
        "error"
      );
    },
  });
});

$("#addTTStatusModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$("#myModal").modal("show");
  }, 200);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingTTStatusPatientId) {
    setTimeout(function () {
      refreshTtStatusInfo(pendingTTStatusPatientId);
      pendingTTStatusPatientId = null;
    }, 300);
  }
});