function refreshMcvInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_mcv.php",
    method: "POST",
    data: { patient_id: patientId},
    success: function (data) {
      $("#mcv-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating MCV info:", error);
    },
  });
}

let pendingMcvPatientId = null;

$(document).on("click", ".add_mcv_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#mcv_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addMcvModal").modal("show");
});

$("#addMcvForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#mcv_patient_id").val();

  $.ajax({
    url: "patient/infant/add_mcv.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingMcvPatientId = patientId;
        $("#addMcvModal").modal("hide");
        $("#addMcvForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "MCV Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshMcvInfo(patientId);
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
      console.error("Error Saving MCV data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the MCV data.",
        "error"
      );
    },
  });
});

$("#addMcvModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$('#myModal').modal('show');
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingMcvPatientId) {
    setTimeout(function () {
      refreshMcvInfo(pendingMcvPatientId);
      pendingMcvPatientId = null;
    }, 300);
  }
});


// Edit modal
$(document).on("click", ".edit_mcv_btn", function () {
    let mcvId = $(this).data("mcv-id");
    let patientId = $(this).data("patient-id");
    let mcvType = $(this).data("mcv-type");
    let mcvDate = $(this).data("mcv-date");
    
    // Populate the edit form with current data
    $("#edit_mcv_id").val(mcvId);
    $("#edit_mcv_patient_id").val(patientId);
    $("#edit_mcv_type").val(mcvType);
    $("#edit_mcv_date").val(mcvDate);
    
    // Store original values in data attributes
    $("#editMcvForm")
        .data("original-mcv-type", mcvType)
        .data("original-mcv-date", mcvDate);

    // Show the edit modal
    $("#editMcvModal").modal("show");
});

$("#editMcvForm").on("submit", function (e) {
    e.preventDefault();

    let currentType = $("#edit_mcv_type").val();
    let currentDate = $("#edit_mcv_date").val();
    let originalType = $(this).data("original-mcv-type");
    let originalDate = $(this).data("original-mcv-date");
    
    if (currentType === originalType && currentDate === originalDate) {
      
        $("#editMcvModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_mcv_patient_id").val();

    $.ajax({
        url: "patient/infant/update_mcv.php",
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editMcvModal").modal("hide");
                $("#editMcvForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "MCV updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshMcvInfo(patientId);
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
            console.error("Error updating MCV data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the MCV data.",
                "error"
            );
        },
    });
});

$("#editMcvModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});