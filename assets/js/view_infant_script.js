// Search function
function initialInfantSearch() {
  observeInfantInput();

    
  $("form").submit(function (event) {
    event.preventDefault();
  });

  $("#search_infant").on("keyup", searchInfantRecord);

  $(document).on("click", "#infant-pagination .page-link", function (e) {
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
        $("#infant-pagination").hide();
      },
      error: function (xhr, status, error) {
        console.error("Search AJAX Error:", status, error);
      },
    });
  } else {
    fetchInfantData();
    $("#infant-pagination").show();
  }
}

// Dynamic searchbar
function observeInfantInput() {
    const input = document.getElementById('search_infant');

    if (!input) return;

    const observer = new ResizeObserver(entries => {
        const width = entries[0].contentRect.width;

        if (width < 180) {
            input.placeholder = 'Search';
        }
        else if (width < 280) {
            input.placeholder = 'Search Records';
        }
        else {
            input.placeholder = 'Search Infant Records';
        }
    });

    observer.observe(input);
}

// Fetch infant records
function fetchInfantData(page = 1) {
    console.log("Current Filter:", window.currentFilter);

     console.log("Fetching infant data for page:", page);
  $.ajax({
    url: "patient/infant/fetch_infant_record.php",
    method: "POST",
    dataType: "json",
    data: { 
      action: "fetchData", 
      page: page,
      filter_type: window.currentFilter || 'all' },
    success: function (response) {
      $("#infant_record_list").html(response.table_data);
      $("#infant-pagination").html(response.pagination_links);
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

$(document).on("click", "#infant-pagination .page-link", function (e) {
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
  loadPage(
        "patient/infant/view_btn_infant.php?patient_id=" + id
    );
});
//view button function