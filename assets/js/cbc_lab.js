function refreshCbcInfo(pregId) {
  $.ajax({
    url: "patient/maternal/get_cbc.php",
    method: "POST",
    data: { pregnancy_id: pregId},
    success: function (data) {
      if (data.error) {
        console.error(data.error);
        return;
      }
        $('#cbc_hgb_hct_date_' + pregId).text(data.cbc_hgb_hct_date);
        $('#anemia_status_' + pregId).text(data.anemia_status);
        $('#cbc_hgb_hct_count_' + pregId).text(data.cbc_hgb_hct_count);
        $('#anemia_remarks_' + pregId).text(data.anemia_remarks);
       
    },
    error: function (xhr, status, error) {
      console.error("Error Updating CBC/Hgb&Hct Screening info:", error);
    },
  });
}


$(document).on("click", ".add_cbc_btn", function () {
  let pregId = $(this).data("preg-id");
  $("#cbc_pregnancy_id").val(pregId);

  $("#viewPregnancyRecord").modal("hide");
  //$('#myModal').modal('hide');
  setTimeout(() => {
    $("#addCbcModal").modal("show");
  }, 300);
});

let pendingCbcPregId = null;
$("#addCbcForm").on("submit", function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  let pregId = $("#cbc_pregnancy_id").val();

  $.ajax({
    url: "patient/maternal/add_cbc.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
         pendingCbcPregId = pregId;  
        $("#addCbcModal").modal("hide");
        $("#addCbcForm")[0].reset();

        Swal.fire({
          title: "Success!",
          text: "CBC/Hgb&Hct Screening Added successfully.",
          icon: "success",
          showConfirmButton: true
        });
        refreshCbcInfo(pregId);
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
      console.error("Error Saving CBC/Hgb&Hct Screening data:", error);
      Swal.fire(
        "Error",
        "There was an issue saving the CBC/Hgb&Hct Screening data.",
        "error"
      );
    },
  });
});

$("#addCbcModal").on("hidden.bs.modal", function () {
  setTimeout(() => {
    $("#viewPregnancyRecord").modal("show");
    $("#myModal").modal("show");
  }, 200);
});

$("#viewPregnancyRecord").on("shown.bs.modal", function () {
  if (pendingCbcPregId) {
    setTimeout(function () {
      refreshCbcInfo(pendingCbcPregId);
      pendingCbcPregId = null;
    }, 300);
  }
});