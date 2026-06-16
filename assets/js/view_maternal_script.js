//search record
function initialMaternalSearch(){

 // $('form').submit(function(event) {
   //     event.preventDefault(); 
    //});

  $('#search_maternal').on('keyup', searchRecord);

  $(document).on("click", "#pagination-container .page-link",  function (e) {
    e.preventDefault();
    let pageNumber = $(this).data("page");
    if (pageNumber) {
        fetchData(pageNumber);
    }
  });
}

function searchRecord(){
  let maternal_name = $('#search_maternal').val();
  if (maternal_name.length > 0) {
    // Reset filter dropdown when searching
    $('select.form-select').val('all');
    window.currentFilter = 'all';
    
    $.ajax({
      url: "patient/maternal/fetch_maternal_record.php",
      method: "POST",
      data: {action: 'search_record', maternal_name: maternal_name},
      success: function (data) {
        $('#maternal_record_list').html(data);
        $("#pagination-container").hide();
      }, 
      error: function (xhr, status, error) {
        console.error("Search AJAX Error:", status, error);
      }
    });
  }else {
    // When search is cleared, check if there's an active filter
    if (window.currentFilter && window.currentFilter !== 'all') {
      loadFilteredMaternalRecords(window.currentFilter);
    } else {
      fetchData();
      $("#pagination-container").show();
    }
  }
}
//search record

//display records in table
function fetchData(page = 1) {
  //using ajax kukuhanin yung list record na galing sa fetch_maternal_record.php
  $.ajax({
    url: "patient/maternal/fetch_maternal_record.php",
    method: "POST",
    dataType: "json",
    data: { action: "fetchData", page: page },
    success: function (response) {
      $("#maternal_record_list").html(response.table_data);

      $("#pagination-container").html(response.pagination_links);
    },
    error: function (xhr, status, error) {
      console.error("AJAX Error:", status, error);
      $("#maternal_record_list").html(
        "<tr><td colspan='7' class='text-center'>Error loading records.</td></tr>"
      );
    },
  });
}

$(document).ready(function () {
  fetchData();
});
//display records in table

//pagination
$(document).on("click", "#pagination-container .page-link", function (e) {
  e.preventDefault();
  let pageNumber = $(this).data("page");

  if (pageNumber) {
    fetchData(pageNumber);
  }
});
//pagination

//delete function->whole patient record
$(document).on("click", " .delete_btn", function () {
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
        url: "patient/maternal/fetch_maternal_record.php",
        type: "POST",
        data: { action: "delete_record", patient_id: id },
        success: function (data) {
          Swal.fire({
            title: "Deleted!",
            text: "Your file has been deleted.",
            data,
            icon: "success",
          });
          fetchData();
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
$(document).on("click", ".view_btn", function () {
  let id = $(this).data("id");
  //this is for basic patient info
  $.ajax({
    url: "patient/maternal/view_btn_maternal.php",
    method: "POST",
    data: {patient_id : id}, 
    success: function (result) {
    
      $("#modalContent").html(result);
      $('#myModal').modal('show');
      
    }
  });
});
//view button function

//view button function for pregnancy details
$(document).on("click", ".view_preg_btn", function () {
  let pregId = $(this).data("preg-id");
  let pregNum = $(this). data("preg-num");
  let dateCreated = $(this). data("date-created");

  let title = 'Pregnancy # ' + pregNum + '( Date Created: ' + dateCreated + ')';
  $('#pregnancyModalTitle').text(title);

   $('#pregDetails').html('<div class="text-center">Loading details...</div>');

  $.ajax({
      url: "patient/maternal/view_preg_details.php",
      method: "POST",
      data: {pregnancy_id: pregId},
      success: function (result) {
        
        $('#pregDetails').html(result);
        $('#viewPregnancyRecord').modal('show');  
      }
  });
});

//view button function for pregnancy details

//add pregnancy button function
$(document).on("click", "#addPregnancyBtn", function () {
 
  let patientId = $(this).data("patient-id");

  $('#myModal').modal('hide');
  //maglalagay loading
  $('#main-content').load("patient/maternal/add_new_pregnancy.php?patient_id=" + patientId, function(response, status, xhr) {
        if (status === "error") {
            
            $('#main-content').html('<div class="alert alert-danger">Error loading form. Check file path: patient/maternal/add_new_pregnancy.php</div>');
        }
    });

});
//add pregnancy button function

//next and back button for add new pregnancy file
$(document).on('click', '.js-next_button', function() {
  let currentTab = $('.tab-pane.active');
  let nextTab = currentTab.next('.tab-pane');

  if (nextTab.length) {
    currentTab.removeClass('show active');
    nextTab.addClass('show active');

    const nextTabLink = $('a[href="#' + nextTab.attr('id') + '"]');
    $('#myTabs a').removeClass('active');
    nextTabLink.addClass('active');

      $('html, body').animate({ scrollTop: 0 }, 'fast');
  }
});

$(document).on('click', '.js-back_button', function() {
  let currentTab = $('.tab-pane.active');
  let prevTab = currentTab.prev('.tab-pane');

  if (prevTab.length) {
    currentTab.removeClass('show active');
    prevTab.addClass('show active');
    
    const prevTabLink = $('a[href="#' + prevTab.attr('id') + '"]');
    $('#myTabs a').removeClass('active');
    prevTabLink.addClass('active');

     $('html, body').animate({ scrollTop: 0 }, 'fast');
  }
});

//next and back button for add new pregnancy file