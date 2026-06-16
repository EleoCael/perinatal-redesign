<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php");
    exit;
}

require "../module/db.config.php";


$all_barangays = [
    'Bagumbayan','Balubad','Bambang','Matungao','Maysantol','Perez','Pitpitan',
    'San Francisco','San Jose (Pob.)','San Nicolas','Santa Ana','Santa Ines','Taliptip','Tibig'
];


foreach ($all_barangays as $barangay) {
    $check_sql = "SELECT health_center_id FROM health_center WHERE barangay_name = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $barangay);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt); 

    if (mysqli_stmt_num_rows($stmt) == 0) {
        mysqli_stmt_close($stmt);
        $insert_sql = "INSERT INTO health_center (barangay_name, municipality, province)
                       VALUES (?, 'Bulakan', 'Bulacan')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "s", $barangay);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
}


$health_centers = [];
$health_center_sql = "SELECT hc.health_center_id, hc.barangay_name, 
                      u.user_id as midwife_assigned,
                      CONCAT(u.first_name, ' ', u.last_name) as midwife_name
                      FROM health_center hc
                      LEFT JOIN user u ON hc.health_center_id = u.health_center_id AND u.role = 'Midwife'
                      WHERE hc.barangay_name IN ('" . implode("','", $all_barangays) . "')
                      ORDER BY hc.barangay_name";
$result = mysqli_query($conn, $health_center_sql);
if ($result) {
    $health_centers = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>

<div class="container-fluid">
    <div class="page-header">
        <h1>Create Midwife Account</h1>
    </div>

    <div class="content-card">
        <div id="messageArea"></div>

      
        <form id="midwifeForm" action="process_create_midwife.php" method="POST" autocomplete="off">
           
            <input type="hidden" id="ajax_submit" name="ajax_submit" value="1">

            <div class="mb-5">
                <h3 class="mb-4" style="color: var(--primary-color);">
                    <i class="bi bi-person-vcard me-2"></i>Personal Information
                </h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lastName" name="lastName" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="form-text">This will be used for login</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="health_center_id" class="form-label">Assigned Barangay <span class="text-danger">*</span></label>
                        <select class="form-select" id="health_center_id" name="health_center_id" required>
                            <option value="">Select Barangay</option>
                            <?php foreach ($health_centers as $center): ?>
                                <?php $is_occupied = !empty($center['midwife_assigned']); ?>
                                <option value="<?php echo $center['health_center_id']; ?>" <?php echo $is_occupied ? 'disabled' : ''; ?>>
                                    <?php echo htmlspecialchars($center['barangay_name']); ?>
                                    <?php if ($is_occupied): ?>(Occupied)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h3 class="mb-4" style="color: var(--primary-color);">
                    <i class="bi bi-shield-lock me-2"></i>Account Credentials
                </h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <button type="button" id="backBtn" class="btn btn-secondary" onclick="if(typeof loadPage === 'function') loadPage('midwife/manage_midwives.php', this);" name="backBtn">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="reset" id="resetBtn" class="btn btn-outline-secondary me-2" name="resetBtn">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                    </button>
                    <button type="submit" id="submitBtn" class="btn btn-primary" name="submitBtn">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

