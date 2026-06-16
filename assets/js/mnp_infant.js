function refreshMnpInfo(patientId) {
  $.ajax({
    url: "patient/infant/get_mnp.php",
    method: "POST",
    data: { patient_id: patientId },
    success: function (data) {
      $("#mnp-info-" + patientId).html(data);
    },
    error: function (xhr, status, error) {
      console.error("Error Updating mnp info:", error);
    },
  });
}

let pendingMnpPatientId = null;

$(document).on("click", ".add_mnp_btn", function () {
  let patientId = $(this).data("patient-id");
  $("#mnp_patient_id").val(patientId);

  $("#myInfantModal").modal("hide");

  $("#addMnpModal").modal("show");
});

$("#addMnpForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let patientId = $("#mnp_patient_id").val();

  $.ajax({
    url: "patient/infant/add_mnp.php",
    method: "POST",
    data: formData,
    success: function (response) {
      if (response.trim() === "success") {
        pendingMnpPatientId = patientId;
        $("#addMnpModal").modal("hide");
        $("#addMnpForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "MNP Added successfully.",
          icon: "success",
          showConfirmButton: true,
        });
        refreshMnpInfo(patientId);
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
      console.error("Error Saving MNP data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the MNP data.",
        "error"
      );
    },
  });
});

$("#addMnpModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#myInfantModal").modal("show");
  }, 300);
});

$("#myInfantModal").on("shown.bs.modal", function () {
  if (pendingMnpPatientId) {
    setTimeout(function () {
      refreshMnpInfo(pendingMnpPatientId);
      pendingMnpPatientId = null;
    }, 300);
  }
});

$(document).on("click", ".edit_mnp_btn", function () {
    let mnpId = $(this).data("mnp-id");
    let patientId = $(this).data("patient-id");
    let mnpType = $(this).data("mnp-type");
    let mnpDate = $(this).data("mnp-date");
  
    $("#edit_mnp_id").val(mnpId);
    $("#edit_mnp_patient_id").val(patientId);
    $("#edit_mnp_type").val(mnpType);
    $("#edit_mnp_date").val(mnpDate);

    $("#editMnpForm")
        .data("original-mnp-type", mnpType)
        .data("original-mnp-date", mnpDate);

    $("#editMnpModal").modal("show");
});

$("#editMnpForm").on("submit", function (e) {
    e.preventDefault();

    let currentType = $("#edit_mnp_type").val();
    let currentDate = $("#edit_mnp_date").val();
    let originalType = $(this).data("original-mnp-type");
    let originalDate = $(this).data("original-mnp-date");
    
    if (currentType === originalType && currentDate === originalDate) {
        $("#editMnpModal").modal("hide");
        Swal.fire({
            title: "Info",
            text: "No changes were made.",
            icon: "info",
            showConfirmButton: true,
        });
        return;
    }
    
    let formData = $(this).serialize();
    let patientId = $("#edit_mnp_patient_id").val();

    $.ajax({
        url: "patient/infant/update_mnp.php", 
        method: "POST",
        data: formData,
        success: function (response) {
            if (response.trim() === "success") {
                $("#editMnpModal").modal("hide");
                $("#editMnpForm")[0].reset();

                Swal.fire({
                    title: "Success!",
                    text: "MNP updated successfully.",
                    icon: "success",
                    showConfirmButton: true,
                });
                refreshMnpInfo(patientId);
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
            console.error("Error updating MNP data:", error);
            Swal.fire(
                "Error",
                "There was an issue updating the MNP data.",
                "error"
            );
        },
    });
});

$("#editMnpModal").on("hidden.bs.modal", function () {
    setTimeout(() => {
        $("#myInfantModal").modal("show");
    }, 300);
});