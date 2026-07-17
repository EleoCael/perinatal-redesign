<?php
// generate_excel.php (admin)
// Same data source as generate_pdf.php — just written out as .xlsx instead
// of a PDF, using PhpSpreadsheet.

require_once '../vendor/autoload.php';

$configFile = '../module/db.config.php';
if (!file_exists($configFile)) {
    die('Database config not found');
}
require_once $configFile;
require_once 'report_functions_admin.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

// ── Build the spreadsheet ──
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Report');

$lastCol = chr(ord('A') + count($columns)); // e.g. 4 data columns -> E

// Title
$sheet->setCellValue('A1', $reportTitle);
$sheet->mergeCells("A1:{$lastCol}1");
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

// Generated-at subtitle
$generatedBy = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
if ($generatedBy === '') {
    $generatedBy = 'Unknown User';
}
$sheet->setCellValue('A2', 'Generated ' . date('F j, Y g:i A') . ' by ' . $generatedBy);
$sheet->mergeCells("A2:{$lastCol}2");
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
$sheet->getStyle('A2')->getFont()->getColor()->setRGB('666666');

// Header row
$headerRow = 4;
$sheet->setCellValue('A' . $headerRow, 'Indicators');
$col = 'B';
foreach ($columns as $colName) {
    $sheet->setCellValue($col . $headerRow, $colName);
    $col++;
}
$headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setRGB('EEEEEE');
$sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Data rows
$rowNum = $headerRow + 1;
if (empty($data)) {
    $sheet->setCellValue('A' . $rowNum, 'No data available');
    $sheet->mergeCells("A{$rowNum}:{$lastCol}{$rowNum}");
    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $rowNum++;
} else {
    foreach ($data as $row) {
        // Strip the &nbsp;/HTML used for on-screen indentation and use a
        // real cell indent instead, since Excel doesn't render HTML.
        $cleanLabel = trim(html_entity_decode(strip_tags($row['indicator']), ENT_QUOTES, 'UTF-8'));
        $sheet->setCellValue('A' . $rowNum, $cleanLabel);
        if (!empty($row['indent'])) {
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setIndent(2);
        }

        $col = 'B';
        foreach ($rowKeys as $key) {
            $isTotal = ($key === 'total' || $key === 'grand_total');
            $sheet->setCellValue($col . $rowNum, $row[$key] ?? 0);
            $sheet->getStyle($col . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if ($isTotal) {
                $sheet->getStyle($col . $rowNum)->getFont()->setBold(true);
            }
            $col++;
        }
        $rowNum++;
    }
}

// Border around the whole table
$tableRange = "A{$headerRow}:{$lastCol}" . ($rowNum - 1);
$sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Column widths
$sheet->getColumnDimension('A')->setWidth(55);
foreach (range('B', $lastCol) as $c) {
    $sheet->getColumnDimension($c)->setWidth(18);
}

// ── Output ──
$filenameSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $reportTitle) . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filenameSafe . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
