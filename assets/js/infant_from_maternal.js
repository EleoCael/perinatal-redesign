$(document).on('click', '#addInfantBtn', function() {
    let motherId = $(this).data('mother-id');
    $('#myModal').modal('hide');

    $('#main-content').load("patient/maternal/add_new_infant_record.php?mother_id=" + motherId, function(response, status, xhr) {
        if (status === "error") {
            
            $('#main-content').html('<div class="alert alert-danger">Error loading form. Check file path: patient/maternal/add_new_infant_record.php</div>');
        }
    });

   

});
