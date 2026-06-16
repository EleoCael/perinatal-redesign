<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php");
    exit;
}

require "../module/db.config.php";

function getAdminInfo($conn, $user_id) {
    $adminInfo = [
        'name' => '',
        'title' => 'Administrator',
        'email' => ''
    ];

    $sql = "SELECT first_name, last_name, user_email, role FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $adminInfo['name'] = trim($row['first_name'] . ' ' . $row['last_name']);
        $adminInfo['email'] = $row['user_email'];
        $adminInfo['title'] = $row['role'];
        
        if (empty($adminInfo['name']) || trim($adminInfo['name']) === '') {
            $adminInfo['name'] = 'Administrator';
        }
    }
    $stmt->close();
    
    return $adminInfo;
}

$total_midwives = 0;
$total_barangays = 0;
$occupied_barangays = 0;
$available_barangays = 0;

$adminInfo = getAdminInfo($conn, $_SESSION['user_id']);

if ($conn) {

    $midwife_sql = "SELECT COUNT(*) as total FROM user WHERE role = 'Midwife' AND is_verified = 1";
    $result = mysqli_query($conn, $midwife_sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_midwives = $row['total'];
    }

    $barangay_sql = "SELECT COUNT(*) as total FROM health_center";
    $result = mysqli_query($conn, $barangay_sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $total_barangays = $row['total'];
    }

    $occupied_sql = "SELECT COUNT(DISTINCT hc.health_center_id) as occupied 
                     FROM health_center hc 
                     INNER JOIN user u ON hc.health_center_id = u.health_center_id 
                     WHERE u.role = 'Midwife' AND u.is_verified = 1";
    $result = mysqli_query($conn, $occupied_sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $occupied_barangays = $row['occupied'];
    }

    $available_barangays = $total_barangays - $occupied_barangays;

    mysqli_close($conn);
}
?>

<div class="container-fluid">
     <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-left-warning">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-user-shield fa-lg text-white"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="card-title mb-1 text-dark">
                                <?php echo htmlspecialchars($adminInfo['name']); ?>
                            </h4>
                            <p class="card-text mb-1">
                                <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($adminInfo['title']); ?></span>
                            </p>
                            <p class="card-text text-muted mb-0">
                                <i class="fas fa-envelope me-1"></i>
                                Email: <strong><?php echo htmlspecialchars($adminInfo['email']); ?></strong>
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

    <!-- Total cards-->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Midwives</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_midwives; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fa-2x text-blue-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Barangays</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_barangays; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-geo-alt-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                               Assigned Barangays</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $occupied_barangays; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-house-check-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Unassigned Barangays</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $available_barangays; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-house-door-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <button class="btn btn-outline-primary w-100 h-100 py-3" onclick="loadPage('create_midwife.php')">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-person-plus-fill fa-2x mb-2"></i>
                                        <span class="fw-bold">Add Midwife</span>
                                    </div>
                                </button>     
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                             <button class="btn btn-outline-success w-100 h-100 py-3" onclick="loadPage('manage_midwives.php')">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-people-fill display-4  fa-2x mb-2"></i>
                                    <span class="fw-bold">Manage Midwives</span>
                                </div>
                            </button>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <button class="btn btn-outline-info w-100 h-100 py-3" onclick="loadPage('report_dashboard.php')">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                    <span class="fw-bold">Reports</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

