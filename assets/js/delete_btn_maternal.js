//delete function for pregnancy details
$(document).on("click", " .delete_preg_btn", function () {
  let id = $(this).data("preg-id");

  Swal.fire({
    title: "Are you sure?",
    text: "You won't be able to revert this!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "patient/maternal/delete_btn_maternal.php",
        type: "POST",
        data: { action: "delete_preg_record", pregnancy_id: id },
        success: function (data) {
          Swal.fire({
            title: "Deleted!",
            text: "Your file has been deleted.",
            data,
            icon: "success",
          }).then(()=>{
            fetchData();
          });
        },
        error: function () {
          Swal.fire("Error!", "Something went wrong while deleting.", "error");
        },
      });
    }
  });
});
//delete function for pregnancy details