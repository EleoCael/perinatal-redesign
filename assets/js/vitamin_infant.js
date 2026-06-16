function refreshVitaminInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_vitamin.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#vitamin-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating vitamin info:", error);
    },
  });
}

let pendingVitaminPatientId = null;

$(document).on("click", ".add_vit_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#vitamin_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addVitInfantModal").modal("show");
});

$("#addVitInfantForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#vitamin_patient_id").val();

  $.ajax({
    url: "patient/infant/add_vitamin.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingVitaminPatientId = patientId;
        $("#addVitInfantModal").modal("hide");
        $("#addVitInfantForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Vitamin Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshVitaminInfo(patientId);
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
      console.error("Error Saving Vitamin data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Vitamin data.",
        "error"
      );
    },
  });
});

$("#addVitInfantModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingVitaminPatientId) {
    setTimeout(function () {
      refreshVitaminInfo(pendingVitaminPatientId);
      pendingVitaminPatientId = null;
    }, 300);
  }
});

//edit modal 
$(document).on("click", ".edit_vitamin_btn", function () {
    let vitaminId = $(this).data("vitamin-id");
    let patientId = $(this).data("patient-id");
    let vitaminType = $(this).data("vitamin-type");
    let vitaminDate = $(this).data("vitamin-date");

    $("#edit_vitamin_id").val(vitaminId);
    $("#edit_vitamin_patient_id").val(patientId);
    $("#edit_vitamin_type").val(vitaminType);
    $("#edit_vitamin_date").val(vitaminDate);

    $("#editVitaminForm")
        .data("original-vitamin-type", vitaminType)
        .data("original-vitamin-date", vitaminDate);

    $("#editVitaminModal").modal("show");
});

$("#editVitaminForm").on("submit", function (e) {
    e.preventDefault();

    let currentType = $("#edit_vitamin_type").val();
    let currentDate = $("#edit_vitamin_date").val();
    let originalType = $(this).data("original-vitamin-type");
    let originalDate = $(this).data("original-vitamin-date");
    
    if (currentType === originalType && currentDate === originalDate) {
        $("#editVitaminModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_vitamin_patient_id").val();

    $.ajax({
        url: "patient/infant/update_vitamin.php", 
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editVitaminModal").modal("hide");
                $("#editVitaminForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "Vitamin A updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshVitaminInfo(patientId);
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
            console.error("Error updating Vitamin A data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the Vitamin A data.",
                "error"
            );
        },
    });
});

$("#editVitaminModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});