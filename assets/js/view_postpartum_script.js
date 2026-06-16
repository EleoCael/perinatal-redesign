//search record
function initialPostpartumSearch(){

   $('form').submit(function(event) {
        event.preventDefault(); 
    });

  $('#search_postpartum').on('keyup', searchPostpartumRecord);

  $(document).on("click", "#pagination-container .page-link",  function (e) {
    e.preventDefault();
    let pageNumber = $(this).data("page");
    if (pageNumber) {
        fetchPostpartumData(pageNumber);
    }
  });
}

function searchPostpartumRecord(){
  let maternal_post_name = $('#search_postpartum').val();
  if (maternal_post_name.length > 0) {
    $.ajax({
      url: "patient/postpartum/fetch_postpartum_record.php",
      method: "POST",
      data: {action: 'search_record', maternal_post_name: maternal_post_name},
      success: function (data) {
        $('#postpartum_record_list').html(data);
        $("#pagination-container").hide();
      }, 
      error: function (xhr, status, error) {
        console.error("Search AJAX Error:", status, error);
      }

    });
  }else {
    fetchPostpartumData();
    $("#pagination-container").show();

  }
}
//search record

//display records in table
function fetchPostpartumData(page = 1) {
  //using ajax kukuhanin yung list record na galing sa fetch_maternal_record.php
  console.log("Fetching infant data for page:", page);
  $.ajax({
    url: "patient/postpartum/fetch_postpartum_record.php",
    method: "POST",
    dataType: "json",
    data: { action: "fetchData", page: page },
    success: function (response) {
      $("#postpartum_record_list").html(response.table_data);

      $("#pagination-container").html(response.pagination_links);
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", status, error);
      $("#postpartum_record_list").html(
        "<tr><td colspan='7' class='text-center'>Error loading records.</td></tr>"
      );
    },
  });
}

$(document).ready(function () {
  fetchPostpartumData();
});
//display records in table

//pagination
$(document).on("click", "#pagination-container .page-link", function (e) {
  e.preventDefault();
  let pageNumber = $(this).data("page");

  if (pageNumber) {
    fetchPostpartumData(pageNumber);
  }
});
//pagination

//delete function->whole patient record
$(document).on("click", " .delete_postpartum_btn", function () {
  let id = $(this).data("id");

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
        url: "patient/postpartum/fetch_postpartum_record.php",
        type: "POST",
        data: { action: "delete_record", patient_id: id },
        success: function (data) {
          Swal.fire({
            title: "Deleted!",
            text: "Your file has been deleted.",
            data,
            icon: "success",
          });
          fetchPostpartumData();
        },
        error: function () {
          Swal.fire("Error!", "Something went wrong while deleting.", "error");
        },
      });
    }
  });
});
//delete function->whole patient record

//view button function
$(document).on("click", ".view_postpartum_btn", function () {
  let id = $(this).data("id");
  //this is for basic patient info
  $.ajax({
    url: "patient/postpartum/view_btn_postpartum.php",
    method: "POST",
    data: {patient_id : id}, 
    success: function (result) {
    
      $("#postpartumModalContent").html(result);
      $('#myPostpartumModal').modal('show');
      
    },
    error: function(xhr, status, error) {
            console.error("View AJAX Error:", status, error);
          
            $("#postpartumModalContent").html("<p class='text-danger'>Error loading record details.</p>");
            $('#myPostpartumModal').modal('show');
        }
  });
});
//view button function
