$(document).ready(function () {

  // 🟦 1. When user clicks the Edit button
  $(document).on("click", ".edit_infant_btn", function () {
    let id = $(this).data("id");

    loadPage(
      'patient/infant/edit_infant_patient.php?patient_id=' + id
    );
  });

  $(document).on("submit", "#editInfantForm", function (e) {
    e.preventDefault();

    const formData = $(this).serialize();
    const $submitBtn = $(this).find('button[type="submit"]');
    $submitBtn.prop("disabled", true);

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
          }).then(function () {
            loadPage('patient/infant/view_infant_patient.php');
          });
        } else {
          $submitBtn.prop("disabled", false);
          Swal.fire({
            icon: "error",
            title: "Update Failed",
            text: response.message
          });
        }
      },
      error: function (xhr, status, error) {
        $submitBtn.prop("disabled", false);
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

});