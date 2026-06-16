<?php
require_once "../module/db.config.php";
session_start();

// CRITICAL: Verify session is valid
if (!isset($_SESSION['health_center_id']) || empty($_SESSION['health_center_id'])) {
    die("Access denied: No health center assigned");
}

$health_center_id = $_SESSION['health_center_id'];

function getMidwifeInfo($conn, $health_center_id) {
    $midwifeInfo = [
        'name' => '',
        'title' => 'Midwife',
        'barangay' => '',
        'municipality' => '',
        'province' => ''
    ];
    
    // Query to get midwife details and health center information
    $sql = "SELECT 
                u.first_name, 
                u.last_name,
                hc.barangay_name,
                hc.municipality,
                hc.province
            FROM user u 
            JOIN health_center hc ON u.health_center_id = hc.health_center_id 
            WHERE u.health_center_id = ? 
            AND u.role = 'Midwife'
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $health_center_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $midwifeInfo['name'] = trim($row['first_name'] . ' ' . $row['last_name']);
        $midwifeInfo['barangay'] = $row['barangay_name'];
        $midwifeInfo['municipality'] = $row['municipality'];
        $midwifeInfo['province'] = $row['province'];
        
        // If name is empty, use a default
        if (empty($midwifeInfo['name']) || trim($midwifeInfo['name']) === '') {
            $midwifeInfo['name'] = 'Midwife User';
        }
    } else {
        // Fallback if no midwife found
        $midwifeInfo['name'] = 'Midwife User';
        
        // Get just the health center info
        $sql_hc = "SELECT barangay_name, municipality, province FROM health_center WHERE health_center_id = ?";
        $stmt_hc = $conn->prepare($sql_hc);
        $stmt_hc->bind_param("i", $health_center_id);
        $stmt_hc->execute();
        $result_hc = $stmt_hc->get_result();
        
        if ($row_hc = $result_hc->fetch_assoc()) {
            $midwifeInfo['barangay'] = $row_hc['barangay_name'];
            $midwifeInfo['municipality'] = $row_hc['municipality'];
            $midwifeInfo['province'] = $row_hc['province'];
        }
        $stmt_hc->close();
    }
    $stmt->close();
    
    return $midwifeInfo;
}

function getPatientCounts($conn, $health_center_id) {
    $counts = [
        'maternal' => 0,
        'infant' => 0,
        'postpartum' => 0
    ];
    
    // Count maternal patients
    $sql = "SELECT COUNT(*) as count FROM patient WHERE patient_type = 'mother' AND health_center_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $health_center_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $counts['maternal'] = $row['count'];
    }
    $stmt->close();
    
    // Count infant patients
    $sql = "SELECT COUNT(*) as count FROM patient WHERE patient_type = 'infant' AND health_center_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $health_center_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $counts['infant'] = $row['count'];
    }
    $stmt->close();
    
    // Count postpartum patients
    $sql = "SELECT COUNT(*) as count FROM patient WHERE patient_type = 'postpartum_mother' AND health_center_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $health_center_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $counts['postpartum'] = $row['count'];
    }
    $stmt->close();
    
    return $counts;
}





// Pass the session health_center_id to the functions

$patientCounts = getPatientCounts($conn, $health_center_id);
$midwifeInfo = getMidwifeInfo($conn, $health_center_id);
$patientCounts = getPatientCounts($conn, $health_center_id);



?>


<div class="container-fluid py-4">
    
     <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-left-info">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="rounded-circle bg-info d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-user-md fa-lg text-white"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="card-title mb-1 text-dark">
                                <?php echo htmlspecialchars($midwifeInfo['name']); ?>
                            </h4>
                            <p class="card-text mb-1">
                                <span class="badge bg-primary"><?php echo htmlspecialchars($midwifeInfo['title']); ?></span>
                            </p>
                            <p class="card-text text-muted mb-0">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                Assigned Barangay: <strong><?php echo htmlspecialchars($midwifeInfo['barangay']); ?></strong>
                                <?php if (!empty($midwifeInfo['municipality'])): ?>
                                    <span class="text-muted">• <?php echo htmlspecialchars($midwifeInfo['municipality']); ?>, <?php echo htmlspecialchars($midwifeInfo['province']); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted">Currently logged in</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!--Quick access-->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold  text-danger text-uppercase mb-1">
                                Registered Maternal
                            </div>
                            <div class="h2 mb-0 font-weight-bold  text-gray-800"><?php echo $patientCounts['maternal']; ?></div>
                            <div class="text-success text-sm  font-weight-bold ">
                                <i class="fas fa-arrow-up me-1"></i>
                                Registered patients
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-female fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold  text-primary text-uppercase mb-1">
                                REGISTERED INFANT
                            </div>
                            <div class="h2 mb-0  font-weight-bold  text-gray-800"><?php echo $patientCounts['infant']; ?></div>
                            <div class="text-success text-sm  font-weight-bold ">
                                <i class="fas fa-arrow-up me-1"></i>
                                Registered infants
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-baby fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                POSTPARTUM CARE
                            </div>
                            <div class="h2 mb-0 font-weight-bold text-gray-800"><?php echo $patientCounts['postpartum']; ?></div>
                            <div class="text-success text-sm font-weight-bold">
                                <i class="fas fa-arrow-up me-1"></i>
                                Postpartum patients
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <button class="btn btn-outline-danger w-100 h-100 py-3" onclick="loadPage('viewPatient_LandingPg.php')">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-folder-fill fa-2x mb-2 text-danger"></i>
                                    <span class="fw-bold">View Records</span>
                                </div>
                            </button>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <button class="btn btn-outline-success w-100 h-100 py-3" onclick="loadPage('addPatient_LandingPg.php')">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-person-plus-fill fa-2x mb-2 text-success"></i>
                                    <span class="fw-bold">Add Patient</span>
                                </div>
                            </button>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <button class="btn btn-outline-info w-100 h-100 py-3" onclick="loadPage('reports/report_dashboard.php')">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-chart-bar fa-2x mb-2 text-info"></i>
                                    <span class="fw-bold">Reports</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Immunization Monitoring Chart -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary">Immunization Monitoring Chart</h6>
                <div>
                    <small class="text-muted me-3">Current Year: <?php echo date('Y'); ?></small>
                    <button class="btn btn-sm btn-outline-primary" onclick="loadPage('set_targets.php')">
                        <i class="fas fa-edit me-1"></i>Set Targets
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height: 400px;">
                    <canvas id="immunizationChart"></canvas>
                </div>
                <div class="mt-3 text-center">
                    <div class="d-inline-block me-4">
                        <span class="badge bg-primary me-1">■</span>
                        <small class="text-muted">Target Quota</small>
                    </div>
                    <div class="d-inline-block">
                        <span class="badge bg-success me-1">■</span>
                        <small class="text-muted">Actual Fully Immunized</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<?php $conn->close(); ?>