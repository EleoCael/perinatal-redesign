<?php
// generate_pdf.php (admin)
// Same approach as the midwife version: build the report as real HTML server-side
// and render it with Dompdf, so there's no browser print dialog / injected
// header-footer involved.

require_once '../vendor/autoload.php';

$configFile = '../module/db.config.php';
if (!file_exists($configFile)) {
    die('Database config not found');
}
require_once $configFile;
require_once 'report_functions_admin.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

session_start();

$action = $_GET['action'] ?? '';
$period = $_GET['period'] ?? 'monthly';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$quarter = $_GET['quarter'] ?? 1;
$barangays = isset($_GET['barangays']) ? explode(',', $_GET['barangays']) : [];

if ($action === 'maternal') {
    $result = getPrenatalReport($conn, $barangays, $period, $month, $year, $quarter);
    $data = $result['data'];
    $reportTitle = $result['title'];
    $columns = ['10–14 years old', '15–19 years old', '20–49 years old', 'Total'];
    $rowKeys = ['age_10_14', 'age_15_19', 'age_20_49', 'grand_total'];
} elseif ($action === 'child') {
    $result = getChildReport($conn, $barangays, $period, $month, $year, $quarter);
    $data = $result['data'];
    $reportTitle = $result['title'];
    $columns = ['Male', 'Female', 'Total'];
    $rowKeys = ['male', 'female', 'total'];
} elseif ($action === 'nutrition') {
    $result = getNutritionReport($conn, $barangays, $period, $month, $year, $quarter);
    $data = $result['data'];
    $reportTitle = $result['title'];
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
            $isTotal = ($key === 'total' || $key === 'grand_total');
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
        font-size: 15px;
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

$canvas = $dompdf->getCanvas();
$canvas->page_text(270, 750, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 8, [0.4, 0.4, 0.4]);

$filenameSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $reportTitle) . '.pdf';

$download = isset($_GET['download']) && $_GET['download'] === '1';
$dompdf->stream($filenameSafe, ['Attachment' => $download]);