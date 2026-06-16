$(document).ready(function () {

  // 🟦 1. When user clicks the Edit button
  $(document).on("click", ".edit_infant_btn", function () {
    let id = $(this).data("id");

    $.ajax({
      url: "patient/infant/get_infant_record.php",
      method: "POST",
      data: { patient_id: id },
      dataType: "json",
      success: function (data) {
        if (data) {
          // Populate modal fields
          $("input[name='infant_first_name']").val(data.first_name);
          $("input[name='infant_middle_name']").val(data.middle_name);
          $("input[name='infant_last_name']").val(data.last_name);
          $("input[name='date_of_registration']").val(data.date_of_registration);
          $("input[name='family_serial_number']").val(data.family_serial_number);
          $("select[name='socio_economic_status']").val(data.socio_economic_status);
          $("input[name='address']").val(data.address);
          $("input[name='infant_birth_date']").val(data.birth_date);
          $("input[name='name_of_mother']").val(data.name_of_mother);
          $("input[name='email']").val(data.email);
          $("input[name='contact_number']").val(data.contact_number);

          $("#edit_infant_patient_id").val(id);

          $("#editInfantModal").modal("show");
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
