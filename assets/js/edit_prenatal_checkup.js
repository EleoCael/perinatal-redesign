
document.addEventListener('click', function(e) {
   
    if (e.target.closest('.edit_checkup_btn')) {
        const editBtn = e.target.closest('.edit_checkup_btn');
        openEditCheckupModal(editBtn);
    }
});

function openEditCheckupModal(editBtn) {
   
    const checkupId = editBtn.getAttribute('data-checkup-id');
    const pregnancyId = editBtn.getAttribute('data-preg-id');
    const trimester = editBtn.getAttribute('data-trimester');
    const checkupDate = editBtn.getAttribute('data-checkup-date');
    
  
    document.getElementById('edit_checkup_id').value = checkupId;
    document.getElementById('edit_pregnancy_id').value = pregnancyId;
    document.getElementById('edit_trimester').value = trimester;
    document.getElementById('edit_checkup_date').value = checkupDate;
    
    
    const editModal = new bootstrap.Modal(document.getElementById('editCheckupModal'));
    editModal.show();
}

//submission
document.getElementById('saveEditCheckupBtn').addEventListener('click', function() {
    saveCheckupChanges();
});

function refreshCheckupList(pregnancyId) {
    $.ajax({
        url: "/rhusystem/midwife/patient/maternal/get_prenatal_checkup.php",
        method: "POST", 
        data: { pregnancy_id: pregnancyId },
        success: function(data) {
            $("#checkup-info-" + pregnancyId).html(data);

            initializeEditButtons();
        },
        error: function(xhr, status, error) {
            console.error("Error updating checkup list:", error);
            location.reload();
        }
    });
}

function initializeEditButtons() {
    $(document).off('click', '.edit_checkup_btn');
    
    $(document).on('click', '.edit_checkup_btn', function() {
        const editBtn = this;
        const checkupId = $(editBtn).data('checkup-id');
        const pregnancyId = $(editBtn).data('preg-id');
        const trimester = $(editBtn).data('trimester');
        const checkupDate = $(editBtn).data('checkup-date');
        
        document.getElementById('edit_checkup_id').value = checkupId;
        document.getElementById('edit_pregnancy_id').value = pregnancyId;
        document.getElementById('edit_trimester').value = trimester;
        document.getElementById('edit_checkup_date').value = checkupDate;
        
        const editModal = new bootstrap.Modal(document.getElementById('editCheckupModal'));
        editModal.show();
    });
}

function saveCheckupChanges() {
    const formData = new FormData(document.getElementById('editCheckupForm'));
    const pregnancyId = document.getElementById('edit_pregnancy_id').value;
    
    const trimester = document.getElementById('edit_trimester').value;
    const checkupDate = document.getElementById('edit_checkup_date').value;
    
    if (!trimester || !checkupDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all required fields',
            confirmButtonColor: '#3085d6'
        });
        return;
    }

    const saveBtn = document.getElementById('saveEditCheckupBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

    fetch('/rhusystem/midwife/patient/maternal/update_checkup.php', { 
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Checkup updated successfully!',
                confirmButtonColor: '#3085d6',
                timer: 2000,
                showConfirmButton: true
            }).then(() => {
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editCheckupModal'));
                editModal.hide();

                refreshCheckupList(pregnancyId);
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while updating the checkup',
            confirmButtonColor: '#d33'
        });
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = 'Save Changes';
    });
}

$(document).ready(function() {
    initializeEditButtons();
});

$('#editCheckupModal').on('hidden.bs.modal', function() {
    setTimeout(() => {
        $('#viewPregnancyModal').modal('show');
    }, 300);
});