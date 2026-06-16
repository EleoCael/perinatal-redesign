function refreshPcvInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_pcv.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#pcv-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating PCV info:", error);
    },
  });
}

let pendingPcvPatientId = null;

$(document).on("click", ".add_pcv_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#pcv_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addPcvModal").modal("show");
});

$("#addPcvForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#pcv_patient_id").val();

  $.ajax({
    url: "patient/infant/add_pcv.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingPcvPatientId = patientId;
        $("#addPcvModal").modal("hide");
        $("#addPcvForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "PCV Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshPcvInfo(patientId);
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
      console.error("Error Saving PCV data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the PCV data.",
        "error"
      );
    },
  });
});

$("#addPcvModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$('#myModal').modal('show');
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingPcvPatientId) {
    setTimeout(function () {
      refreshPcvInfo(pendingPcvPatientId);
      pendingPcvPatientId = null;
    }, 300);
  }
});

// edit modal
$(document).on("click", ".edit_pcv_btn", function () {
    let pcvId = $(this).data("pcv-id");
    let patientId = $(this).data("patient-id");
    let pcvType = $(this).data("pcv-type");
    let pcvDate = $(this).data("pcv-date");
    
    // Populate the edit form with current data
    $("#edit_pcv_id").val(pcvId);
    $("#edit_pcv_patient_id").val(patientId);
    $("#edit_pcv_type").val(pcvType);
    $("#edit_pcv_date").val(pcvDate);
    
    // Show the edit modal
    $("#editPcvModal").modal("show");
});


$("#editPcvForm").on("submit", function (e) {
    e.preventDefault();
    let formData = $(this).serialize();
    let patientId = $("#edit_pcv_patient_id").val();

    $.ajax({
        url: "patient/infant/update_pcv.php", 
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editPcvModal").modal("hide");
                $("#editPcvForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "PCV updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshPcvInfo(patientId);
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
            console.error("Error updating PCV data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the PCV data.",
                "error"
            );
        },
    });
});

$("#editPcvModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});