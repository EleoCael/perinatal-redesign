$(document).ready(function () {

  // Submit the postpartum medical info form
  $(document).on("click", ".js-submit_postpartum_medical_info", function (e) {
    e.preventDefault();

    const $form = $("#postpartumMedicalForm");
    const patientId = $form.find('input[name="patient_id"]').val();

    if (!patientId || patientId === "0") {
      Swal.fire({
        icon: "error",
        title: "No Patient Selected",
        text: "Please go back and add the postpartum patient's basic info first."
      });
      return;
    }

    const formData = $form.serialize();

    $.ajax({
      url: "/rhusystem/midwife/patient/postpartum/postpartum_medical_process.php",
      type: "POST",
      data: formData,
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Saved Successfully",
            text: "Postpartum medical info has been recorded.",
            showConfirmButton: false,
            timer: 1500
          }).then(function () {
            loadPage("patient/postpartum/view_postpartum_patient.php");
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Save Failed",
            text: response.message || "Something went wrong."
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        console.log("Response:", xhr.responseText);
        Swal.fire({
          icon: "error",
          title: "Server Error",
          text: "Something went wrong while saving the postpartum medical info."
        });
      }
    });
  });

  // Back button - return to the postpartum list without saving
  $(document).on("click", ".js-back_btn_postpartum", function (e) {
    e.preventDefault();
    loadPage("patient/postpartum/view_postpartum_patient.php");
  });

});
