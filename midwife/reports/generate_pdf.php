<?php
// generate_pdf.php
// Renders the same reports as generateAPI.php, but as a real server-generated PDF
// via Dompdf instead of relying on window.print() + the browser's print dialog.
// This is why the old export had "Midwife Dashboard" and a URL/page-number stamped
// on it — those were browser-injected print header/footers, not our own HTML.

require_once '../../vendor/autoload.php';
require_once '../../module/db.config.php';
require_once 'report_functions.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
    die('Database connection failed');
}

session_start();
$health_center_id = $_SESSION['health_center_id'] ?? 1;

$action = $_GET['action'] ?? '';
$period = $_GET['period'] ?? 'monthly';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$quarter = $_GET['quarter'] ?? 1;

$months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$quarterNames = ['', 'Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];

if ($period === 'monthly') {
    $periodLabel = ($months[intval($month)] ?? '') . ' ' . $year;
} elseif ($period === 'quarterly') {
    $periodLabel = ($quarterNames[intval($quarter)] ?? '') . ' ' . $year;
} else {
    $periodLabel = "Annual Report $year";
}

if ($action === 'prenatal') {
    $reportTitle = "Prenatal Care Report - $periodLabel";
    $data = getPrenatalReport($conn, $health_center_id, $period, $month, $year, $quarter);
    $columns = ['10–14 years old', '15–19 years old', '20–49 years old', 'Total'];
    $rowKeys = ['age_10_14', 'age_15_19', 'age_20_49', 'total'];
} elseif ($action === 'child') {
    $reportTitle = "Infant Care & Immunization Report - $periodLabel";
    $data = getChildReport($conn, $health_center_id, $period, $month, $year, $quarter);
    $columns = ['Male', 'Female', 'Total'];
    $rowKeys = ['male', 'female', 'total'];
} elseif ($action === 'nutrition') {
    $reportTitle = "Nutrition Services Report - $periodLabel";
    $data = getNutritionReport($conn, $health_center_id, $period, $month, $year, $quarter);
    $columns = ['Male', 'Female', 'Total'];
    $rowKeys = ['male', 'female', 'total'];
} else {
    die('Invalid report type requested.');
}

// ── Build the HTML that Dompdf will render ──
$rowsHtml = '';
if (empty($data)) {
    $colspan = count($columns) + 1;
    $rowsHtml = "<tr><td colspan=\"{$colspan}\" style=\"text-align:center; color:#888;\">No data available</td></tr>";
} else {
    foreach ($data as $row) {
        $indentStyle = !empty($row['indent']) ? 'padding-left: 24px;' : '';
        $rowsHtml .= '<tr><td style="' . $indentStyle . '">' . $row['indicator'] . '</td>';
        foreach ($rowKeys as $key) {
            $isTotal = $key === 'total';
            $cellStyle = $isTotal ? 'text-align:center; font-weight:bold;' : 'text-align:center;';
            $rowsHtml .= '<td style="' . $cellStyle . '">' . ($row[$key] ?? 0) . '</td>';
        }
        $rowsHtml .= '</tr>';
    }
}

$columnHeadersHtml = '';
foreach ($columns as $col) {
    $columnHeadersHtml .= '<th style="text-align:center;">' . htmlspecialchars($col) . '</th>';
}

$generatedAt = date('F j, Y g:i A');
$generatedBy = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
if ($generatedBy === '') {
    $generatedBy = 'Unknown User';
}

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 60px 50px 60px 50px;
    }
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #1a1a1a;
        font-size: 11px;
    }
    h1 {
        font-size: 16px;
        margin-bottom: 4px;
    }
    .meta {
        color: #666;
        font-size: 9px;
        margin-bottom: 16px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #999;
        padding: 6px 8px;
        vertical-align: top;
    }
    th {
        background-color: #eee;
    }
</style>
</head>
<body>
    <h1>{$reportTitle}</h1>
    <div class="meta">Generated {$generatedAt} by {$generatedBy}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 55%; text-align:left;">Indicators</th>
                {$columnHeadersHtml}
            </tr>
        </thead>
        <tbody>
            {$rowsHtml}
        </tbody>
    </table>
</body>
</html>
HTML;

// ── Render via Dompdf ──
$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

// Add real page numbers (this is OUR footer, not the browser's)
$canvas = $dompdf->getCanvas();
$canvas->page_text(270, 750, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 8, [0.4, 0.4, 0.4]);

$filenameSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $reportTitle) . '.pdf';

$download = isset($_GET['download']) && $_GET['download'] === '1';
$dompdf->stream($filenameSafe, ['Attachment' => $download]);