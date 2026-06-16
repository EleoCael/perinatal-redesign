function selectInfantData(filterValue){
    console.log('Filter selected:', filterValue);
    
    // Store current filter
    window.currentInfantFilter = filterValue;
    
    // Load filtered records
    if (filterValue === 'all') {
        // Use the existing fetchData function for "All Records"
        if (typeof fetchInfantData === 'function') {
            fetchInfantData(1);
        }
    } else {
        // Use filter function for specific filters
        if (typeof loadFilteredInfantRecords === 'function') {
            loadFilteredInfantRecords(filterValue);
        }
    }
}

// Make this function globally available
window.loadFilteredInfantRecords = function(filterType) {
    console.log('Loading infant records with filter:', filterType);
    
    const tbody = $('#infant_record_list');
    tbody.html('<tr><td colspan="7" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading...</td></tr>');
    
    $.ajax({
        url: 'patient/infant/fetch_filter_infant.php',
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

