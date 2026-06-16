function refreshHepaInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_hepaB.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
        
      }
      $('#hepaB_day_' + patientId).text(data.hepaB_day);
      $('#hepaB_date_' + patientId).text(data.hepaB_date);
      
    },
    error: function (xhr, status, error) {
      console.error("Error Updating checkup info:", error);
    },
  });
}

let pendingHepaPatientId = null;

$(document).on("click", ".add_hepa_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#hepa_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addHepaModal").modal("show");
});

$("#addHepaForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#hepa_patient_id").val();

  $.ajax({
    url: "patient/infant/add_hepaB.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingHepaPatientId = patientId;
        $("#addHepaModal").modal("hide");
        $("#addHepaForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "HepaB Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshHepaInfo(patientId);
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
      console.error("Error Saving Hepa B1 data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Hepa B1 data.",
        "error"
      );
    },
  });
});

$("#addHepaModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$('#myModal').modal('show');
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingHepaPatientId) {
    setTimeout(function () {
      refreshHepaInfo(pendingHepaPatientId);
      pendingHepaPatientId = null;
    }, 300);
  }
});

