<?php

session_start();
require_once "../module/db.config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("FORM SUBMITTED - Year: " . $_POST['year']);
    error_log("Form data: " . print_r($_POST['month'], true));
}

if (!isset($_SESSION['health_center_id']) || empty($_SESSION['health_center_id'])) {
    die("Access denied: No health center assigned");
}

$health_center_id = $_SESSION['health_center_id'];
$current_year = date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $year = $_POST['year'];
    $months = $_POST['month'];

    foreach ($months as $monthNum => $targetValue) {
        if (!empty($targetValue)) {

            $check_sql = "SELECT target_id FROM monthly_targets 
        WHERE target_year = ? AND target_month = ? AND health_center_id = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("iii", $year, $monthNum, $health_center_id); 
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
          
            $update_sql = "UPDATE monthly_targets SET target_value = ? 
            WHERE target_year = ? AND target_month = ? AND health_center_id = ?";
            $stmt = $conn->prepare($update_sql);  
            $stmt->bind_param("iiii", $targetValue, $year, $monthNum, $health_center_id);
            
            } else {
                
                $insert_sql = "INSERT INTO monthly_targets (target_year, target_month, target_value, health_center_id) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("iiii", $year, $monthNum, $targetValue, $health_center_id);
            }
            $stmt->execute();
            $stmt->close();
        }
    }

    echo "<script>
            alert('Monthly targets updated successfully!'); 
            if (typeof loadPage === 'function') {
                loadPage('home.php');
            } else {
                window.location.href = 'midwife_dashboard.php';
            }
        </script>";
    exit();
}


$existing_targets = array_fill(1, 12, '');
$sql = "SELECT target_month, target_value FROM monthly_targets 
        WHERE target_year = ? AND health_center_id = ? ORDER BY target_month"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $current_year, $health_center_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $existing_targets[$row['target_month']] = $row['target_value'];
}
$stmt->close();
?>


<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h4 class="m-0 fw-bold text-primary">
                        <i class="fas fa-bullseye me-2"></i>Set Monthly Immunization Targets
                    </h4>
                    <button class="btn btn-secondary" onclick="loadPage('home.php')">
                        <i class="fas fa-arrow-left text-white me-1"></i>Back to Dashboard
                    </button>
                </div>
                <div class="card-body">
                    <form method="POST" action="set_targets.php">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="year" class="form-label fw-bold">Target Year</label>
                                <select class="form-select" id="year" name="year" required>
                                    <?php
                                    $current_year = date('Y');
                                    $start_year = 2024;
                                    $end_year = $current_year + 5;

                                    for ($year = $start_year; $year <= $end_year; $year++) {
                                        $selected = ($year == $current_year) ? 'selected' : '';
                                        echo "<option value='$year' $selected>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-8 d-flex align-items-end">
                                <div class="alert alert-info mb-0 w-100">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Set your monthly targets for fully immunized children. These will be shown as the target line on the dashboard chart.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <?php
                            $months = [
                                1 => 'January',
                                2 => 'February',
                                3 => 'March',
                                4 => 'April',
                                5 => 'May',
                                6 => 'June',
                                7 => 'July',
                                8 => 'August',
                                9 => 'September',
                                10 => 'October',
                                11 => 'November',
                                12 => 'December'
                            ];

                            foreach ($months as $monthNum => $monthName): ?>
                                <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
                                    <div class="card h-100 border-primary">
                                        <div class="card-body">
                                            <label for="month_<?= $monthNum ?>" class="form-label fw-bold text-dark">
                                                <?= $monthName ?>
                                            </label>
                                            <input type="number" class="form-control form-control-lg"
                                                id="month_<?= $monthNum ?>"
                                                name="month[<?= $monthNum ?>]"
                                                value="<?= htmlspecialchars($existing_targets[$monthNum]) ?>"
                                                min="0"
                                                placeholder="Enter target">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3" id="saveTargetsBtn">
                                    <i class="fas fa-save text-white me-2"></i>Save All Monthly Targets
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>