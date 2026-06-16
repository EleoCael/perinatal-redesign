// Search function
function initialInfantSearch() {
    
  $("form").submit(function (event) {
    event.preventDefault();
  });

  $("#search_infant").on("keyup", searchInfantRecord);

  $(document).on("click", "#pagination-container .page-link", function (e) {
    e.preventDefault();
    let pageNumber = $(this).data("page");
    if (pageNumber) {
      fetchInfantData(pageNumber);
    }
  });
}

function searchInfantRecord() {
  let infant_name = $("#search_infant").val();
  if (infant_name.length > 0) {
    $.ajax({
      url: "patient/infant/fetch_infant_record.php",
      method: "POST",
      data: { action: "search_record", infant_name: infant_name },
      success: function (data) {
        $("#infant_record_list").html(data);
        $("#pagination-container").hide();
      },
      error: function (xhr, status, error) {
        console.error("Search AJAX Error:", status, error);
      },
    });
  } else {
    fetchInfantData();
    $("#pagination-container").show();
  }
}

// Fetch infant records
function fetchInfantData(page = 1) {
     console.log("Fetching infant data for page:", page);
  $.ajax({
    url: "patient/infant/fetch_infant_record.php",
    method: "POST",
    dataType: "json",
    data: { action: "fetchData", page: page },
    success: function (response) {
      $("#infant_record_list").html(response.table_data);
      $("#pagination-container").html(response.pagination_links);
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", status, error);
      $("#infant_record_list").html(
        "<tr><td colspan='7' class='text-center'>Error loading infant records.</td></tr>"
      );
    },
  });
}

$(document).ready(function () {
  fetchInfantData();
});

$(document).on("click", "#pagination-container .page-link", function (e) {
  e.preventDefault();
  let pageNumber = $(this).data("page");
  if (pageNumber) fetchInfantData(pageNumber);
});

// Delete infant record
$(document).on("click", ".delete_infant_btn", function () {
  let id = $(this).data("id");

  Swal.fire({
    title: "Are you sure?",
    text: "This infant record will be permanently deleted.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "patient/infant/fetch_infant_record.php",
        type: "POST",
        data: { action: "delete_record", patient_id: id },
        success: function () {
          Swal.fire("Deleted!", "The infant record has been deleted.", "success");
          fetchInfantData();
        },
        error: function () {
          Swal.fire("Error!", "Something went wrong while deleting.", "error");
        },
      });
    }
  });
});


//view button function
$(document).on("click", ".view_infant_btn", function () {
  let id = $(this).data("id");
  //this is for basic patient info
  $.ajax({
    url: "patient/infant/view_btn_infant.php",
    method: "POST",
    data: {patient_id : id}, 
    success: function (result) {
    
      $("#infantModalContent").html(result);
      $('#myInfantModal').modal('show');
      
    }
  });
});
//view button function
