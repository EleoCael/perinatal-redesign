function refreshRvvInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_rvv.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#rvv-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating RVV info:", error);
    },
  });
}

let pendingRvvPatientId = null;

$(document).on("click", ".add_rvv_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#rvv_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addRvvModal").modal("show");
});

$("#addRvvForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#rvv_patient_id").val();

  $.ajax({
    url: "patient/infant/add_rvv.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingRvvPatientId = patientId;
        $("#addRvvModal").modal("hide");
        $("#addRvvForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "RVV Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshRvvInfo(patientId);
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
      console.error("Error Saving RVV data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the RVV data.",
        "error"
      );
    },
  });
});

$("#addRvvModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
    //$('#myModal').modal('show');
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingRvvPatientId) {
    setTimeout(function () {
      refreshRvvInfo(pendingRvvPatientId);
      pendingRvvPatientId = null;
    }, 300);
  }
});

// edit modal
$(document).on("click", ".edit_rvv_btn", function () {
    let rvvId = $(this).data("rvv-id");
    let patientId = $(this).data("patient-id");
    let rvvType = $(this).data("rvv-type");
    let rvvDate = $(this).data("rvv-date");
    
    // Populate the edit form with current data
    $("#edit_rvv_id").val(rvvId);
    $("#edit_rvv_patient_id").val(patientId);
    $("#edit_rvv_type").val(rvvType);
    $("#edit_rvv_date").val(rvvDate);
    
    // Store original values in data attributes
    $("#editRvvForm")
        .data("original-rvv-type", rvvType)
        .data("original-rvv-date", rvvDate);

    // Show the edit modal
    $("#editRvvModal").modal("show");
});

$("#editRvvForm").on("submit", function (e) {
    e.preventDefault();

    let currentType = $("#edit_rvv_type").val();
    let currentDate = $("#edit_rvv_date").val();
    let originalType = $(this).data("original-rvv-type");
    let originalDate = $(this).data("original-rvv-date");
    
    if (currentType === originalType && currentDate === originalDate) {
        $("#editRvvModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_rvv_patient_id").val();

    $.ajax({
        url: "patient/infant/update_rvv.php", 
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editRvvModal").modal("hide");
                $("#editRvvForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "RVV updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshRvvInfo(patientId);
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
            console.error("Error updating RVV data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the RVV data.",
                "error"
            );
        },
    });
});

$("#editRvvModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});