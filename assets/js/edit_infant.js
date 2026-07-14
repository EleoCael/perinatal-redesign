$(document).ready(function () {

  // 🟦 1. When user clicks the Edit button
  $(document).on("click", ".edit_infant_btn", function () {
    let id = $(this).data("id");

    loadPage(
      'patient/infant/edit_infant_patient.php?patient_id=' + id
    );
  });

  $("#editInfantForm").on("submit", function (e) {
    e.preventDefault();

    const formData = $(this).serialize();

    $.ajax({
      url: "patient/infant/edit_btn_infant.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Updated Successfully",
            text: response.message,
            showConfirmButton: false,
            timer: 1500
          });

          $("#editInfantModal").modal("hide");

          refreshInfantTable();
        } else {
          Swal.fire({
            icon: "error",
            title: "Update Failed",
            text: response.message
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
         console.log('Response:', xhr.responseText); 
        Swal.fire({
          icon: "error",
          title: "Server Error",
          text: "Something went wrong while saving changes."
        });
      }
    });
  });


  function refreshInfantTable() {
    $.ajax({
      url: "patient/infant/fetch_infant_record.php",
      type: "GET",
      success: function (data) {
        $("#infant_record_list").html(data); 
      }
    });
  }

});
