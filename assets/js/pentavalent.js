function refreshPentavalentInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_pentavalent.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#pentavalent-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating pentavalent info:", error);
    },
  });
}

let pendingPentavalentPatientId = null;

$(document).on("click", ".add_pentavalent_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#pentavalent_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addPentavalentModal").modal("show");
});

$("#addPentavalentForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#pentavalent_patient_id").val();

  $.ajax({
    url: "patient/infant/add_pentavalent.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingPentavalentPatientId = patientId;
        $("#addPentavalentModal").modal("hide");
        $("#addPentavalentForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "Pentavalent Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshPentavalentInfo(patientId);
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
      console.error("Error Saving Pentavalent data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the Pentavalent data.",
        "error"
      );
    },
  });
});

$("#addPentavalentModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$('#myModal').modal('show');
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingPentavalentPatientId) {
    setTimeout(function () {
      refreshPentavalentInfo(pendingPentavalentPatientId);
      pendingPentavalentPatientId = null;
    }, 300);
  }
});

$(document).on("click", ".edit_pentavalent_btn", function () {
    let pentavalentId = $(this).data("pentavalent-id");
    let patientId = $(this).data("patient-id");
    let pentavalentType = $(this).data("pentavalent-type");
    let pentavalentDate = $(this).data("pentavalent-date");
    
    // Populate the edit form with current data
    $("#edit_pentavalent_id").val(pentavalentId);
    $("#edit_pentavalent_patient_id").val(patientId);
    $("#edit_pentavalent_type").val(pentavalentType);
    $("#edit_pentavalent_date").val(pentavalentDate);
    
    // Store original values in data attributes
    $("#editPentavalentForm")
        .data("original-pentavalent-type", pentavalentType)
        .data("original-pentavalent-date", pentavalentDate);

    $("#editPentavalentModal").modal("show");
});

$("#editPentavalentForm").on("submit", function (e) {
    e.preventDefault();
 
    let currentType = $("#edit_pentavalent_type").val();
    let currentDate = $("#edit_pentavalent_date").val();
    let originalType = $(this).data("original-pentavalent-type");
    let originalDate = $(this).data("original-pentavalent-date");
    
    if (currentType === originalType && currentDate === originalDate) {
      
        $("#editPentavalentModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_pentavalent_patient_id").val();

    $.ajax({
        url: "patient/infant/update_pentavalent.php", 
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editPentavalentModal").modal("hide");
                $("#editPentavalentForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "Pentavalent updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshPentavalentInfo(patientId);
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
            console.error("Error updating Pentavalent data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the Pentavalent data.",
                "error"
            );
        },
    });
});

$("#editPentavalentModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});