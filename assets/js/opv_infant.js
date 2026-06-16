function refreshOpvInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_opv.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#opv-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating OPV info:", error);
    },
  });
}

let pendingOpvPatientId = null;

$(document).on("click", ".add_opv_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#opv_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addOpvModal").modal("show");
});

$("#addOpvForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#opv_patient_id").val();

  $.ajax({
    url: "patient/infant/add_opv.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingOpvPatientId = patientId;
        $("#addOpvModal").modal("hide");
        $("#addOpvForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "OPV Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshOpvInfo(patientId);
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
      console.error("Error Saving OPV data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the OPV data.",
        "error"
      );
    },
  });
});

$("#addOpvModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$('#myModal').modal('show');
  }, 300);
});

$("#addOpvModal").on("shown.bs.modal", function () {
  if (pendingOpvPatientId) {
    setTimeout(function () {
      refreshOpvInfo(pendingOpvPatientId);
      pendingOpvPatientId = null;
    }, 300);
  }
});


// edit modal
$(document).on("click", ".edit_opv_btn", function () {
    let opvId = $(this).data("opv-id");
    let patientId = $(this).data("patient-id");
    let opvType = $(this).data("opv-type");
    let opvDate = $(this).data("opv-date");
    
    // Populate the edit form with current data
    $("#edit_opv_id").val(opvId);
    $("#edit_opv_patient_id").val(patientId);
    $("#edit_opv_type").val(opvType);
    $("#edit_opv_date").val(opvDate);
    
    // Store original values in data attributes
    $("#editOpvForm")
        .data("original-opv-type", opvType)
        .data("original-opv-date", opvDate);

    // Show the edit modal
    $("#editOpvModal").modal("show");
});

$("#editOpvForm").on("submit", function (e) {
    e.preventDefault();

    let currentType = $("#edit_opv_type").val();
    let currentDate = $("#edit_opv_date").val();
    let originalType = $(this).data("original-opv-type");
    let originalDate = $(this).data("original-opv-date");
    
    if (currentType === originalType && currentDate === originalDate) {
      
        $("#editOpvModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_opv_patient_id").val();

    $.ajax({
        url: "patient/infant/update_opv.php", 
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editOpvModal").modal("hide");
                $("#editOpvForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "OPV updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshOpvInfo(patientId);
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
            console.error("Error updating OPV data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the OPV data.",
                "error"
            );
        },
    });
});

$("#editOpvModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});