function selectData(filterValue){
    console.log('Filter selected:', filterValue);
    
    // Store current filter
    window.currentFilter = filterValue;
    
    // Always call the filter directly
    loadFilteredMaternalRecords(filterValue);
}

window.loadFilteredMaternalRecords = function(filterType) {
    console.log('Loading records with filter:', filterType);
    
    const tbody = $('#postpartum_record_list');
    tbody.html('<tr><td colspan="7" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading...</td></tr>');
    
    $.ajax({
        url: 'patient/postpartum/fetch_filter_postpartum.php',
        method: 'POST',
        dataType: 'html',
        data: { filter_type: filterType },
        success: function(response) {
            console.log('Filter response received');
            tbody.html(response);
            
            // Hide pagination when filtering
            $('#pagination-container').hide();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Response:', xhr.responseText);
            tbody.html('<tr><td colspan="7" class="text-center text-danger">Error loading records. Please try again.</td></tr>');
        }
    });
};