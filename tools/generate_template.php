<?php
/**
 * Generates the businesses import template XLSX.
 * Run once: php tools/generate_template.php
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Font, Alignment, Border};

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Businesses');

$headers = [
    'A' => 'Company Name',
    'B' => 'Contact Name',
    'C' => 'Email',
    'D' => 'Phone',
    'E' => 'Website',
    'F' => 'Address',
    'G' => 'City',
    'H' => 'Country',
    'I' => 'Category',
    'J' => 'Notes',
];

// Header row styling
$headerStyle = [
    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
];

foreach ($headers as $col => $label) {
    $sheet->setCellValue($col . '1', $label);
    $sheet->getColumnDimension($col)->setWidth(22);
}

$sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(24);

// Sample rows
$samples = [
    ['Acme Corporation', 'John Smith', 'john@acme.com', '+30 210 1234567', 'https://acme.com', 'Main St 1', 'Athens', 'Greece', 'Technology', 'Interested in ERP'],
    ['Beta Industries', 'Maria Papadaki', 'maria@beta.gr', '+30 2310 987654', 'https://beta.gr', 'Egnatia 25', 'Thessaloniki', 'Greece', 'Manufacturing', ''],
    ['Gamma Services', 'Nikos Georgiou', 'nikos@gamma.gr', '+30 2610 555444', '', 'Riga Feraiou 10', 'Patras', 'Greece', 'Services', 'Follow up next month'],
];

$dataStyle = [
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
];

foreach ($samples as $i => $row) {
    $rowNum = $i + 2;
    foreach (array_values($headers) as $j => $_) {
        $col = chr(65 + $j);
        $sheet->setCellValue($col . $rowNum, $row[$j] ?? '');
    }
    if ($i % 2 === 0) {
        $sheet->getStyle('A' . $rowNum . ':J' . $rowNum)->applyFromArray($dataStyle);
    }
}

// Freeze header row
$sheet->freezePane('A2');

// Auto-filter
$sheet->setAutoFilter('A1:J1');

$outPath = dirname(__DIR__) . '/public/assets/templates/businesses_template.xlsx';
$writer  = new Xlsx($spreadsheet);
$writer->save($outPath);

echo "Template saved to: $outPath" . PHP_EOL;
