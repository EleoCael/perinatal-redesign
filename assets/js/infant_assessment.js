function refreshInfantInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_infant_assessment.php",
    method: "POST",
    data: { patient_id: patientId },
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#birth_weight_' + patientId).text(data.birth_weight);
        $('#birth_height_' + patientId).text(data.birth_height);
        $('#sex_' + patientId).text(data.sex);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Infant Assessment info:", error);
    },
  });
}

let pendingInfantPregId = null;
$(document).on("click", ".add_infant_assessment_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#infant_assess_patient_id").val(patientId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  $("#addInfantScreenModal").modal("show");
});

$("#addInfantScreenForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#infant_assess_patient_id").val();
  console.log("Form data before sending:", formData);
console.log("Patient ID field value:", $("#infant_assess_patient_id").val());


  $.ajax({
    url: "patient/infant/add_infant_assessment.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingInfantPregId = patientId;  
        $("#addInfantScreenModal").modal("hide");
        $("#addInfantScreenForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Newborn Measurement updated successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshInfantInfo(patientId);
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
      console.error("Error Saving Newborn Measurement data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Newborn Measurement data.",
        "error"
      );
    },
  });
});

$("#addInfantScreenModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
   // $("#myModal").modal("show");
  }, 200);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingInfantPregId) {
    setTimeout(function () {
      refreshInfantInfo(pendingInfantPregId);
      pendingInfantPregId = null;
    }, 300);
  }
});