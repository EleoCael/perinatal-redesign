<?php
require_once "../module/db.config.php";
session_start();

if (isset($_SESSION['status_message'])) {
    $status = $_SESSION['status_message'];
    echo '<div class="alert ' . ($status['type'] === 'success' ? 'alert-success' : 'alert-danger') . '">' . htmlspecialchars($status['text']) . '</div>';
    unset($_SESSION['status_message']);
}

$report_type = $_GET['report_type'] ?? 'prenatal';

$stmt = $conn->prepare("SELECT * FROM report_indicators WHERE report_type = ? ORDER BY display_order ASC");
$stmt->bind_param("s", $report_type);
$stmt->execute();
$indicators = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<div class="container-fluid">
    <div class="filter-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Manage Report Indicators</h5>
    <button class="btn btn-outline-secondary btn-sm" type="button"
            onclick="loadPage('report_dashboard.php')">
        <i class="bi bi-arrow-left"></i> Back to Reports
    </button>
</div>
        <p class="text-muted">
            Edit the wording, order, active state, and thresholds used in your generated reports.
            Changing these here updates the report immediately — no code changes needed.
        </p>

        <div class="mb-3">
            <a href="#" class="btn btn-sm btn-outline-primary <?php echo $report_type === 'prenatal' ? 'active' : ''; ?>"
               onclick="loadPage('manage_indicators.php?report_type=prenatal'); return false;">Prenatal Care</a>
            <a href="#" class="btn btn-sm btn-outline-primary <?php echo $report_type === 'child' ? 'active' : ''; ?>"
             onclick="loadPage('manage_indicators.php?report_type=child'); return false;">Infant Care & Immunization</a>
            <a href="#" class="btn btn-sm btn-outline-primary <?php echo $report_type === 'nutrition' ? 'active' : ''; ?>"
                onclick="loadPage('manage_indicators.php?report_type=nutrition'); return false;">Nutrition Services</a>
        </div>

        <form id="manageIndicatorsForm" action="/rhusystem/admin/manage_indicators_process.php" method="POST">
            <input type="hidden" name="report_type" value="<?php echo htmlspecialchars($report_type); ?>">

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 8%; font-size: 0.75rem;" class="text-muted"><i class="bi bi-arrow-down-up text-muted"></i>Reorder</th>
                            <th style="width: 57%;">Label</th>
                            <th style="width: 20%;">Threshold</th>
                            <th style="width: 15%;" class="text-center">Active</th>
                            
                        </tr>
                    </thead>
                    <tbody id="indicatorRows">
                       <?php foreach ($indicators as $ind): ?>
                        <tr data-indicator-id="<?php echo $ind['indicator_id']; ?>">
                            <td class="text-center" style="cursor: grab;">
                                <i class="bi bi-grip-vertical text-muted drag-handle"></i>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                    name="indicator[<?php echo $ind['indicator_id']; ?>][label_template]"
                                    value="<?php echo htmlspecialchars($ind['label_template']); ?>">
                                <?php if (strpos($ind['label_template'], '{threshold}') !== false): ?>
                                    <small class="text-muted">Uses <code>{threshold}</code> as a placeholder for the number on the right.</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($ind['has_threshold']): ?>
                                    <input type="number" min="1" class="form-control form-control-sm"
                                           name="indicator[<?php echo $ind['indicator_id']; ?>][threshold_value]"
                                           value="<?php echo htmlspecialchars($ind['threshold_value']); ?>">
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input"
                                       name="indicator[<?php echo $ind['indicator_id']; ?>][is_active]"
                                       value="1" <?php echo $ind['is_active'] ? 'checked' : ''; ?>>
                            </td>
                            <td class="d-none">
                                <input type="hidden" class="display-order-input"
                                       name="indicator[<?php echo $ind['indicator_id']; ?>][display_order]"
                                       value="<?php echo (int) $ind['display_order']; ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
        
    </div>
</div>

<img src="x" alt="" style="display:none" onerror="window.initIndicatorSorting && window.initIndicatorSorting()">