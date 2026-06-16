function refreshComplementaryFeedInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_breastfeed2.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#complementary-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating Complementary Feeding info:", error);
    },
  });
}

let pendingComplementaryPatientId = null;

$(document).on("click", ".add_complementary_feed", function () {
  let patientId = $(this).data("patient-id");
  $("#complementary_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addComplimentaryModal").modal("show");
});

$("#addComplimentaryForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#complementary_patient_id").val();

  $.ajax({
    url: "patient/infant/add_breastfeed2.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingComplementaryPatientId = patientId;
        $("#addComplimentaryModal").modal("hide");
        $("#addComplimentaryForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Complementary Feeding Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshComplementaryFeedInfo(patientId);
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
      console.error("Error Saving Complementary Feeding data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Complementary Feeding data.",
        "error"
      );
    },
  });
});

$("#addComplimentaryModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$('#myModal').modal('show');
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingComplementaryPatientId) {
    setTimeout(function () {
      refreshComplementaryFeedInfo(pendingComplementaryPatientId);
      pendingComplementaryPatientId = null;
    }, 300);
  }
});

//edit modal
$(document).on("click", ".edit_complementary_btn", function () {
    let complementaryId = $(this).data("complementary-id");
    let patientId = $(this).data("patient-id");
    let monthCheck = $(this).data("complementary-month-check");
    let monthDate = $(this).data("complementary-month-date");

    $("#edit_complementary_feeding_id").val(complementaryId);
    $("#edit_complementary_patient_id").val(patientId);
    $("#edit_complementary_month_check").val(monthCheck);
    $("#edit_complementary_month_date").val(monthDate);

    $("#editComplementaryForm")
        .data("original-complementary-month-check", monthCheck)
        .data("original-complementary-month-date", monthDate);

    $("#editComplementaryModal").modal("show");
});

$("#editComplementaryForm").on("submit", function (e) {
    e.preventDefault();

    let currentMonthCheck = $("#edit_complementary_month_check").val();
    let currentMonthDate = $("#edit_complementary_month_date").val();
    let originalMonthCheck = $(this).data("original-complementary-month-check");
    let originalMonthDate = $(this).data("original-complementary-month-date");
    
    if (currentMonthCheck === originalMonthCheck && currentMonthDate === originalMonthDate) {
        $("#editComplementaryModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_complementary_patient_id").val();

    $.ajax({
        url: "patient/infant/update_breastfeed2.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editComplementaryModal").modal("hide");
                $("#editComplementaryForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "Complementary Feeding updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshComplementaryFeedInfo(patientId);
            } else {
                Swal.fire(
                    "Error 🚨",
                    "Server reported an issue updating the data. Please check PHP code.",
                    "error"
                );
                console.error("Server Response (not 'success'):", response);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error updating Complementary Feeding data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the Complementary Feeding data.",
                "error"
            );
        },
    });
});

$("#editComplementaryModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});