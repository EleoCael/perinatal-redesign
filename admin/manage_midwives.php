<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php");
    exit;
}

require "../module/db.config.php";

$midwives_sql = "SELECT u.user_id, u.first_name, u.last_name, u.user_email, 
                         hc.barangay_name, hc.health_center_id
                  FROM user u
                  LEFT JOIN health_center hc ON u.health_center_id = hc.health_center_id
                  WHERE u.role = 'Midwife' AND u.is_verified = 1
                  ORDER BY u.user_id DESC";
$midwives_result = mysqli_query($conn, $midwives_sql);
$midwives = [];

if ($midwives_result && mysqli_num_rows($midwives_result) > 0) {
    while($row = mysqli_fetch_assoc($midwives_result)) {
        $midwives[] = $row;
    }
}

$available_barangays_sql = "SELECT hc.health_center_id, hc.barangay_name
                           FROM health_center hc
                           LEFT JOIN user u ON hc.health_center_id = u.health_center_id AND u.role = 'Midwife' AND u.is_verified = 1
                           WHERE u.user_id IS NULL
                           ORDER BY hc.barangay_name";
$available_barangays_result = mysqli_query($conn, $available_barangays_sql);
$available_barangays = [];

if ($available_barangays_result && mysqli_num_rows($available_barangays_result) > 0) {
    while($row = mysqli_fetch_assoc($available_barangays_result)) {
        $available_barangays[] = $row;
    }
}

mysqli_close($conn);
?>

<div class="container-fluid">
    <div class="page-header">
        <h1>Manage Midwives</h1>
    </div>

    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0" style="color: var(--primary-color);">
                <i class="bi bi-people me-2"></i>Midwife Accounts
            </h3>
            <button type="button" class="btn btn-primary" onclick="loadPage('create_midwife.php', this);">
                <i class="bi bi-person-plus me-2"></i>Add New Midwife
            </button>
        </div>

        <div id="messageArea"></div>

        <?php if (empty($midwives)): ?>
            <div class="text-center py-5">
                <i class="bi bi-people display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">No Midwives Found</h4>
                <p class="text-muted">There are no midwife accounts in the system yet.</p>
                <button type="button" class="btn btn-primary" onclick="loadPage('create_midwife.php', this);">
                    <i class="bi bi-person-plus me-2"></i>Create First Midwife Account
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th style = "color: white;">Name</th>
                            <th style = "color: white;">Email</th>
                            <th style = "color: white;">Assigned Barangay</th>
                            <th style = "color: white;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($midwives as $midwife): ?>
                            <tr id="midwife-row-<?php echo $midwife['user_id']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($midwife['first_name'] . ' ' . $midwife['last_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($midwife['user_email']); ?></td>
                                <td>
                                    <?php if (!empty($midwife['barangay_name'])): ?>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($midwife['barangay_name']); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editMidwife(<?php echo $midwife['user_id']; ?>)"
                                                 data-bs-toggle="tooltip"  title="Edit Midwife">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="reassignMidwife(<?php echo $midwife['user_id']; ?>, '<?php echo htmlspecialchars($midwife['first_name'] . ' ' . $midwife['last_name']); ?>')"
                                                data-bs-toggle="tooltip" title="Reassign Barangay">
                                            <i class="bi bi-geo-alt"></i>
                                        </button>
                                       
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Total Midwives:</strong> <?php echo count($midwives); ?> active accounts
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="reassignModal" tabindex="-1" aria-labelledby="reassignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reassignModalLabel">Reassign Barangay</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Reassigning barangay for: <strong id="reassignMidwifeName"></strong></p>
                <div class="mb-3">
                    <label for="newBarangay" class="form-label">Select New Barangay</label>
                    <select class="form-select" id="newBarangay">
                        <option value="">Select Barangay</option>
                        <?php foreach ($available_barangays as $barangay): ?>
                            <option value="<?php echo $barangay['health_center_id']; ?>">
                                <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmReassign">Reassign</button>
            </div>
        </div>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', function() {

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const confirmReassignBtn = document.getElementById('confirmReassign');
    if (confirmReassignBtn) {
        confirmReassignBtn.addEventListener('click', function() {
            const newBarangayId = document.getElementById('newBarangay').value;
            
            if (!newBarangayId) {
                if (typeof AdminUtils !== 'undefined') {
                    AdminUtils.showError('Please select a barangay');
                } else {
                    alert('Please select a barangay');
                }
                return;
            }

            performReassignMidwife(window.currentMidwifeId, newBarangayId);
        });
    }
});

window.currentMidwifeId = null;

function editMidwife(userId) {
    if (typeof loadPage === 'function') {
        loadPage('edit_midwife.php?id=' + userId, this);
    }
}

function reassignMidwife(userId, midwifeName) {
    window.currentMidwifeId = userId;
    document.getElementById('reassignMidwifeName').textContent = midwifeName;
    
    const modal = new bootstrap.Modal(document.getElementById('reassignModal'));
    modal.show();
}

function deleteMidwife(userId, midwifeName) {
    if (typeof AdminUtils !== 'undefined') {
        AdminUtils.confirmAction(
            `Are you sure you want to delete midwife "${midwifeName}"? This action cannot be undone.`,
            function() {
                performDeleteMidwife(userId);
            }
        );
    } else {
        if (confirm(`Are you sure you want to delete midwife "${midwifeName}"? This action cannot be undone.`)) {
            performDeleteMidwife(userId);
        }
    }
}

function performReassignMidwife(userId, newBarangayId) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('new_barangay_id', newBarangayId);
    formData.append('action', 'reassign_barangay');

    fetch('process_manage_midwife.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showSuccess(data.message);
            } else {
                alert('Success: ' + data.message);
            }
    
            const modal = bootstrap.Modal.getInstance(document.getElementById('reassignModal'));
            if (modal) {
                modal.hide();
            }
            if (typeof loadPage === 'function') {
                loadPage('manage_midwives.php');
            }
        } else {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showError(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showError('Network error occurred');
        } else {
            alert('Network error occurred');
        }
    });
}

function performDeleteMidwife(userId) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('action', 'delete_midwife');

    fetch('process_manage_midwife.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showSuccess(data.message);
            } else {
                alert('Success: ' + data.message);
            }
       
            const row = document.getElementById('midwife-row-' + userId);
            if (row) {
                row.remove();
            }
    
            if (document.querySelector('tbody').children.length === 0) {
                if (typeof loadPage === 'function') {
                    loadPage('manage_midwives.php');
                }
            }
        } else {
            if (typeof AdminUtils !== 'undefined') {
                AdminUtils.showError(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof AdminUtils !== 'undefined') {
            AdminUtils.showError('Network error occurred');
        } else {
            alert('Network error occurred');
        }
    });
}
</script>