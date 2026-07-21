$(document).ready(function () {

  // 🟦 1. When user clicks the Edit button
 $(document).on("click", ".edit_postpartum_btn", function () {
    let id = $(this).data("id");
    loadPage("patient/postpartum/edit_postpartum_patient.php?patient_id=" + id);
});

  // 🟩 2. When the edit form is submitted
  $("#editPostpartumForm").on("submit", function (e) {
    e.preventDefault();

    console.log('Maybe this!')

    const formData = $(this).serialize();

    $.ajax({
      url: "patient/postpartum/edit_btn_postpartum.php",
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

          // Close modal
          $("#editPostpartumModal").modal("hide");

          // Refresh the maternal table dynamically
          refreshPostpartumTable();
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


  function refreshPostpartumTable() {

    console.log('Or this!')
    $.ajax({
      url: "patient/postpartum/fetch_postpartum_list.php",
      type: "GET",
      success: function (data) {
        $("#postpartum_record_list").html(data); 
      }
    });
  }

});