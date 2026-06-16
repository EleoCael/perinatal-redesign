function refreshIronInfantInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_iron_infant.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#iron-infant-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating iron info:", error);
    },
  });
}

let pendingIronPatientId = null;

$(document).on("click", ".add_iron_infant_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#iron_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addIronInfantModal").modal("show");
});

$("#addIronInfantForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#iron_patient_id").val();

  $.ajax({
    url: "patient/infant/add_iron_infant.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingIronPatientId = patientId;
        $("#addIronInfantModal").modal("hide");
        $("#addIronInfantForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Iron Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshIronInfantInfo(patientId);
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
      console.error("Error Saving Iron data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Iron data.",
        "error"
      );
    },
  });
});

$("#addIronInfantModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingIronPatientId) {
    setTimeout(function () {
      refreshIronInfantInfo(pendingIronPatientId);
      pendingIronPatientId = null;
    }, 300);
  }
});

// edit modal
$(document).on("click", ".edit_iron_infant_btn", function () {
    let ironId = $(this).data("iron-id");
    let patientId = $(this).data("patient-id");
    let ironType = $(this).data("iron-type");
    let ironDate = $(this).data("iron-date");
    
    $("#edit_iron_id").val(ironId);
    $("#edit_iron_patient_id").val(patientId);
    $("#edit_iron_type").val(ironType);
    $("#edit_iron_date").val(ironDate);
    
    // Store original values in data attributes
    $("#editIronInfantForm")
        .data("original-iron-type", ironType)
        .data("original-iron-date", ironDate);

    $("#editIronInfantModal").modal("show");
});

$("#editIronInfantForm").on("submit", function (e) {
    e.preventDefault();

    let currentType = $("#edit_iron_type").val();
    let currentDate = $("#edit_iron_date").val();
    let originalType = $(this).data("original-iron-type");
    let originalDate = $(this).data("original-iron-date");
    
    if (currentType === originalType && currentDate === originalDate) {
        $("#editIronInfantModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_iron_patient_id").val();

    $.ajax({
        url: "patient/infant/update_iron_infant.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editIronInfantModal").modal("hide");
                $("#editIronInfantForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "Iron updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshIronInfantInfo(patientId);
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
            console.error("Error updating Iron data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the Iron data.",
                "error"
            );
        },
    });
});

$("#editIronInfantModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});