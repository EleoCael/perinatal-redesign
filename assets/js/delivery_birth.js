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

$(document).on("click", ".add_birth_info_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#delivery_pregnancy_id").val(pregId);
  $("#addBirthInfoModal").modal("show");
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
        $("#addBirthInfoForm")[0].reset();

        $("#addBirthInfoModal").one("hidden.bs.modal", function () {
          Swal.fire({
            title: "Success!",
            text: "Birth Information Added successfully.",
            icon: "success",
            showConfirmButton: true
          });
          refreshBirthInfo(pregId);
        });
        $("#addBirthInfoModal").modal("hide");
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