function selectMaternalData(filterValue){
    console.log('Filter selected:', filterValue);
    
    // Store current filter
    window.currentFilter = filterValue;
    
    // Load filtered records
    if (filterValue === 'all') {
        // Use the existing fetchData function for "All Records"
        if (typeof fetchData === 'function') {
            fetchData(1);
        }
    } else {
        // Use filter function for specific filters
        if (typeof loadFilteredMaternalRecords_Maternal  === 'function') {
            loadFilteredMaternalRecords_Maternal (filterValue);
        }
    }
}

// Make this function globally available
window.loadFilteredMaternalRecords_Maternal  = function(filterType) {
    console.log('Loading records with filter:', filterType);
    
    const tbody = $('#maternal_record_list');
    tbody.html('<tr><td colspan="7" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading...</td></tr>');
    
    $.ajax({
        url: 'patient/maternal/fetch_filter_function.php',
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