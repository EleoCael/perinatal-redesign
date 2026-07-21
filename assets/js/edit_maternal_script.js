$(document).ready(function () {

  // 🟦 1. When user clicks the Edit button
  $(document).on("click", ".edit_btn", function () {
    console.log('MY HANDLER IS RUNNING!')
    let id = $(this).data("id");

    loadPage(
        "patient/maternal/edit_maternal_patient.php?patient_id=" + id
    );
  });

  // 🟩 2. When the edit form is submitted
  $("#editMaternalForm").on("submit", function (e) {
    e.preventDefault();
    console.log('Is this what I need to change? 2')

    const formData = $(this).serialize();

    $.ajax({
      url: "patient/maternal/edit_btn_maternal.php",
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
          $("#editModal").modal("hide");

          // Refresh the maternal table dynamically
          refreshMaternalTable();
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

  // 🟨 3. Optional: function to refresh your table (customize the selector)
  function refreshMaternalTable() {
    console.log('Is this what I need to change?')

    $.ajax({
      url: "patient/maternal/fetch_maternal_list.php",
      type: "GET",
      success: function (data) {
        $("#maternal_record_list").html(data); 
      }
    });
  }

});