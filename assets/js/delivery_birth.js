function refreshBirthInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_delivery_birth.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    dataType: "json",
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#delivery_type_' + pregId).text(data.delivery_type);
        $('#birth_weight_classification_' + pregId).text(data.weight_class);
        $('#birth_weight_' + pregId).text(data.birth_weight);
        $('#birth_attendant_' + pregId).text(data.birth_attendant);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Birth info:", error);
    },
  });
}

let pendingBirthPregId = null;
$(document).on("click", ".add_birth_info_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#delivery_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addBirthInfoModal").modal("show");
  }, 300);
});

$("#addBirthInfoForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#delivery_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_delivery_birth.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
         pendingBirthPregId = pregId;  
        $("#addBirthInfoModal").modal("hide");
        $("#addBirthInfoForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Birth Information Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshBirthInfo(pregId);
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
      console.error("Error Saving Birth Information data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Birth Information data.",
        "error"
      );
    },
  });
});

$("#addBirthInfoModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingBirthPregId) {
    setTimeout(function () {
      refreshBirthInfo(pendingBirthPregId);
      pendingBirthPregId = null;
    }, 300);
  }
});