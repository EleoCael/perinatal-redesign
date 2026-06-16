function refreshReferralInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_referral.php",
    method: "POST",
    data: { patient_id: patientId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#referral_' + patientId).text(data.referral_date);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Referral Date info:", error);
    },
  });
}

let pendingReferralPatientId = null;
$(document).on("click", ".add_referral_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#referral_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addReferralModal").modal("show");
  }, 300);
});

$("#addReferralForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#referral_patient_id").val();

  $.ajax({
    url: "patient/infant/add_referral.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingReferralPatientId = patientId;  
        $("#addReferralModal").modal("hide");
        $("#addReferralForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Referral Date Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshReferralInfo(patientId);
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
      console.error("Error Saving Referral Date data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Referral Date data.",
        "error"
      );
    },
  });
});

$("#addReferralModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$("#myModal").modal("show");
  }, 200);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingReferralPatientId) {
    setTimeout(function () {
      refreshReferralInfo(pendingReferralPatientId);
      pendingReferralPatientId = null;
    }, 300);
  }
});