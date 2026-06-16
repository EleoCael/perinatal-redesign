function refreshExlusiveFeedInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_breastfeed1.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#exclusive-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating checkup info:", error);
    },
  });
}

let pendingExlusivePatientId = null;

$(document).on("click", ".add_exclusive_breastfeed", function () {
  let patientId = $(this).data("patient-id");
  $("#exlusive_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addExlusiveFeedModal").modal("show");
});

$("#addExlusiveFeedForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#exlusive_patient_id").val();

  $.ajax({
    url: "patient/infant/add_breastfeed1.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingExlusivePatientId = patientId;
        $("#addExlusiveFeedModal").modal("hide");
        $("#addExlusiveFeedForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Exclusive Feeding Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshExlusiveFeedInfo(patientId);
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
      console.error("Error Saving Exclusive Feeding data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Exclusive Feeding data.",
        "error"
      );
    },
  });
});

$("#addExlusiveFeedModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$('#myModal').modal('show');
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingExlusivePatientId) {
    setTimeout(function () {
      refreshExlusiveFeedInfo(pendingExlusivePatientId);
      pendingExlusivePatientId = null;
    }, 300);
  }
});

//edit modal
// Store original values when opening the modal
$(document).on("click", ".edit_exclusive_breastfeeding_btn", function () {
    let breastfeedId = $(this).data("exclusive-breastfeed-id");
    let patientId = $(this).data("patient-id");
    let monthCheck = $(this).data("month-check");
    let monthDate = $(this).data("month-date");

    $("#edit_exclusive_breastfeed_id").val(breastfeedId);
    $("#edit_exlusive_patient_id").val(patientId);
    $("#edit_month_check").val(monthCheck);
    $("#edit_month_date").val(monthDate);

    // Store original values in data attributes
    $("#editExlusiveFeedForm")
        .data("original-month-check", monthCheck)
        .data("original-month-date", monthDate);

    $("#editExlusiveFeedModal").modal("show");
});

$("#editExlusiveFeedForm").on("submit", function (e) {
    e.preventDefault();
    
    // Check if any values actually changed
    let currentMonthCheck = $("#edit_month_check").val();
    let currentMonthDate = $("#edit_month_date").val();
    let originalMonthCheck = $(this).data("original-month-check");
    let originalMonthDate = $(this).data("original-month-date");
    
    if (currentMonthCheck === originalMonthCheck && currentMonthDate === originalMonthDate) {
        // No changes made, just close the modal
        $("#editExlusiveFeedModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_exlusive_patient_id").val();

    $.ajax({
        url: "patient/infant/update_breastfeed1.php", 
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editExlusiveFeedModal").modal("hide");
                $("#editExlusiveFeedForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "Exclusive Feeding updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshExlusiveFeedInfo(patientId);
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
            console.error("Error updating Exclusive Feeding data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the Exclusive Feeding data.",
                "error"
            );
        },
    });
});

$("#editExlusiveFeedModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});