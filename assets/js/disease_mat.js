function refreshDiseaseInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_disease.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#syphilis_date_' + pregId).text(data.syphilis_date);
        $('#syphilis_screening_' + pregId).text(data.syphilis_screening);
        $('#syphilis_remarks_' + pregId).text(data.syphilis_remarks);

        $('#hepatitisB_date_' + pregId).text(data.hepatitisB_date);
        $('#hepatitis_b_screening_' + pregId).text(data.hepatitis_b_screening);
        $('#hepatitis_b_remarks_' + pregId).text(data.hepatitis_b_remarks);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Infectious Disease Screening info:", error);
    },
  });
}

let pendingDiseasePregId = null;
$(document).on("click", ".add_disease_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#disease_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addDiseaseModal").modal("show");
  }, 300);
});

$("#addDiseaseForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#disease_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_disease.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
         pendingDiseasePregId = pregId;  
        $("#addDiseaseModal").modal("hide");
        $("#addDiseaseForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Infectious Disease Screening Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshDiseaseInfo(pregId);
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
      console.error("Error Saving Infectious Disease Screening data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Infectious Disease Screening data.",
        "error"
      );
    },
  });
});

$("#addDiseaseModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingDiseasePregId) {
    setTimeout(function () {
      refreshDiseaseInfo(pendingDiseasePregId);
      pendingDiseasePregId = null;
    }, 300);
  }
});