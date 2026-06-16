$(document).ready(function () {

  // 🟦 1. When user clicks the Edit button
  $(document).on("click", ".edit_btn", function () {
    let id = $(this).data("id");

    $.ajax({
      url: "patient/postpartum/get_postpartum_record.php",
      method: "POST",
      data: { patient_id: id },
      dataType: "json",
      success: function (data) {
        if (data) {
          // Populate modal fields
          $("input[name='first_name']").val(data.first_name);
          $("input[name='middle_name']").val(data.middle_name);
          $("input[name='last_name']").val(data.last_name);
          $("input[name='date_of_registration']").val(data.date_of_registration);
          $("input[name='family_serial_number']").val(data.family_serial_number);
          $("select[name='socio_economic_status']").val(data.socio_economic_status);
          $("input[name='address']").val(data.address);
          $("input[name='birth_date']").val(data.birth_date);
          $("input[name='age']").val(data.age);
          $("input[name='email']").val(data.email);
          $("input[name='contact_number']").val(data.contact_number);

          // Age bracket radio
          $("input[name='age_bracket'][value='" + data.age_bracket + "']").prop("checked", true);

          // Hidden patient_id input
          $("#edit_post_patient_id").val(id);

          // Show the modal
          $("#editPostpartumModal").modal("show");
        } else {
          Swal.fire({
            icon: "error",
            title: "Fetch Failed",
            text: "Unable to load patient record."
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
         console.log('Response:', xhr.responseText); 
        Swal.fire({
          icon: "error",
          title: "Server Error",
          text: "Unable to fetch patient details."
        });
      }
    });
  });

  // 🟩 2. When the edit form is submitted
  $("#editPostpartumForm").on("submit", function (e) {
    e.preventDefault();

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
    $.ajax({
      url: "patient/postpartum/fetch_postpartum_list.php",
      type: "GET",
      success: function (data) {
        $("#postpartum_record_list").html(data); 
      }
    });
  }

});
