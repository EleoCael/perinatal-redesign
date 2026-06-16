function refreshLaboratoryInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_laboratory.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#gestational_date_' + pregId).text(data.gestational_date);
        $('#gestational_screening_' + pregId).text(data.gestational_screening);
        $('#gestational_remarks_' + pregId).text(data.gestational_remarks);   
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Laboratory Screening info:", error);
    },
  });
}


$(document).on("click", ".add_laboratory_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#laboratory_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addLaboratoryModal").modal("show");
  }, 300);
});

let pendingLaboratoryPregId = null;
$("#addLaboratoryForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#laboratory_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_laboratory.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
         pendingLaboratoryPregId = pregId;  
        $("#addLaboratoryModal").modal("hide");
        $("#addLaboratoryForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Laboratory Screening Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshLaboratoryInfo(pregId);
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
      console.error("Error Saving Laboratory Screening data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Laboratory Screening data.",
        "error"
      );
    },
  });
});

$("#addLaboratoryModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingLaboratoryPregId) {
    setTimeout(function () {
      refreshLaboratoryInfo(pendingLaboratoryPregId);
      pendingLaboratoryPregId = null;
    }, 300);
  }
});