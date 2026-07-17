<?php
// generate_excel.php (midwife)
// Same data source as generate_pdf.php — written out as .xlsx using PhpSpreadsheet.

require_once '../../vendor/autoload.php';

if (!file_exists('../../module/db.config.php')) {
    die('Database config not found');
}
require_once '../../module/db.config.php';
require_once 'report_functions.php';

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
$health_center_id = $_SESSION['health_center_id'] ?? 1;

$action = $_GET['action'] ?? '';
$period = $_GET['period'] ?? 'monthly';
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$quarter = $_GET['quarter'] ?? 1;

// Build the period text the same way reportScript.js does client-side
$months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$quarterNames = ['', '1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];

if ($period === 'monthly') {
    $periodText = $months[(int) $month] . ' ' . $year;
} elseif ($period === 'quarterly') {
    $periodText = $quarterNames[(int) $quarter] . ' ' . $year;
} else {
    $periodText = 'Year ' . $year;
}

if ($action === 'prenatal') {
    $data = getPrenatalReport($conn, $health_center_id, $period, $month, $year, $quarter);
    $reportTitle = "Prenatal Care Report - {$periodText}";
    $columns = ['10–14 years old', '15–19 years old', '20–49 years old', 'Total'];
    $rowKeys = ['age_10_14', 'age_15_19', 'age_20_49', 'total'];
} elseif ($action === 'child') {
    $data = getChildReport($conn, $health_center_id, $period, $month, $year, $quarter);
    $reportTitle = "Infant Care & Immunization Report - {$periodText}";
    $columns = ['Male', 'Female', 'Total'];
    $rowKeys = ['male', 'female', 'total'];
} elseif ($action === 'nutrition') {
    $data = getNutritionReport($conn, $health_center_id, $period, $month, $year, $quarter);
    $reportTitle = "Nutrition Services Report - {$periodText}";
    $columns = ['Male', 'Female', 'Total'];
    $rowKeys = ['male', 'female', 'total'];
} else {
    die('Invalid report type requested.');
}

// ── Build the spreadsheet ──
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Report');

$lastCol = chr(ord('A') + count($columns));

$sheet->setCellValue('A1', $reportTitle);
$sheet->mergeCells("A1:{$lastCol}1");
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$generatedBy = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? ''));
if ($generatedBy === '') {
    $generatedBy = 'Unknown User';
}
$sheet->setCellValue('A2', 'Generated ' . date('F j, Y g:i A') . ' by ' . $generatedBy);
$sheet->mergeCells("A2:{$lastCol}2");
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9);
$sheet->getStyle('A2')->getFont()->getColor()->setRGB('666666');

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

$rowNum = $headerRow + 1;
if (empty($data)) {
    $sheet->setCellValue('A' . $rowNum, 'No data available');
    $sheet->mergeCells("A{$rowNum}:{$lastCol}{$rowNum}");
    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $rowNum++;
} else {
    foreach ($data as $row) {
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

$tableRange = "A{$headerRow}:{$lastCol}" . ($rowNum - 1);
$sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

$sheet->getColumnDimension('A')->setWidth(55);
foreach (range('B', $lastCol) as $c) {
    $sheet->getColumnDimension($c)->setWidth(18);
}

$filenameSafe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $reportTitle) . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filenameSafe . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
