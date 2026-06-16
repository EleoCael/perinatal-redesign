<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php");
    exit;
}

$midwife_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($midwife_id <= 0) {
    echo "<div class='alert alert-danger'>Invalid midwife ID</div>";
    exit;
}

require_once "../module/db.config.php";

$midwife_sql = "SELECT u.user_id, u.first_name, u.last_name, u.user_email, u.health_center_id,
                        hc.barangay_name
                 FROM user u
                 LEFT JOIN health_center hc ON u.health_center_id = hc.health_center_id
                 WHERE u.user_id = ? AND u.role = 'Midwife' AND u.is_verified = 1";
$stmt = mysqli_prepare($conn, $midwife_sql);

if (!$stmt) {
    echo "<div class='alert alert-danger'>Prepare failed: " . mysqli_error($conn) . "</div>";
    mysqli_close($conn);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $midwife_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$midwife = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$midwife) {
    echo "<div class='alert alert-danger'>Midwife not found or has been deleted</div>";
    mysqli_close($conn);
    exit;
}

$barangays_sql = "SELECT hc.health_center_id, hc.barangay_name
                   FROM health_center hc
                   ORDER BY hc.barangay_name";
$barangays_result = mysqli_query($conn, $barangays_sql);
$barangays = [];

if ($barangays_result && mysqli_num_rows($barangays_result) > 0) {
    while($row = mysqli_fetch_assoc($barangays_result)) {
        $barangays[] = $row;
    }
}

mysqli_close($conn);
?>

<div class="container-fluid">
    <div class="page-header">
        <h1>Edit Midwife Account</h1>
    </div>

    <div class="content-card">
        <div id="messageArea"></div>

        <div id="editMidwifeForm">
            <input type="hidden" id="user_id" value="<?php echo $midwife['user_id']; ?>">

            <div class="mb-5">
                <h3 class="mb-4" style="color: var(--primary-color);">
                    <i class="bi bi-person-vcard me-2"></i>Personal Information
                </h3>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="firstName" 
                               value="<?php echo htmlspecialchars($midwife['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="lastName" 
                               value="<?php echo htmlspecialchars($midwife['last_name']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" 
                               value="<?php echo htmlspecialchars($midwife['user_email']); ?>" required>
                        <div class="form-text">This will be used for login</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="health_center_id" class="form-label">Assigned Barangay <span class="text-danger">*</span></label>
                        <select class="form-select" id="health_center_id" required>
                            <option value="">Select Barangay</option>
                            <?php foreach ($barangays as $barangay): ?>
                                <?php $is_current = $barangay['health_center_id'] == $midwife['health_center_id']; ?>
                                <option value="<?php echo $barangay['health_center_id']; ?>" 
                                    <?php echo $is_current ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                                    <?php if ($is_current): ?>(Current)<?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <button type="button" class="btn btn-secondary" id="backBtn">
                        <i class="bi bi-arrow-left me-2"></i>Back to List
                    </button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-outline-secondary me-2" id="cancelBtn">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="updateBtn">
                        <i class="bi bi-check-circle me-2"></i>Update Midwife
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

