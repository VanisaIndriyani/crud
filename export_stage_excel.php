<?php
require 'vendor/autoload.php';
require_once 'includes/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

$stage_id = $_GET['id'] ?? 0;

if (!$stage_id) {
    die("Stage ID not provided.");
}

// Fetch Stage Info
$stmt = $pdo->prepare("SELECT ps.*, p.name as project_name, u.username as creator_name FROM project_stages ps JOIN projects p ON ps.project_id = p.id LEFT JOIN users u ON p.created_by = u.id WHERE ps.id = :id");
$stmt->execute(['id' => $stage_id]);
$stage = $stmt->fetch();

if (!$stage) {
    die("Stage not found.");
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Styling Variables
$headerStyle = [
    'font' => [
        'bold' => true,
        'size' => 14,
        'color' => ['argb' => 'FFFFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF0044CC'], // Dark Blue
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$subHeaderStyle = [
    'font' => [
        'bold' => true,
        'size' => 12,
        'color' => ['argb' => 'FFFFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF00AA00'], // Green
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$labelStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFEFEFEF'], // Light Gray
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
    ],
    'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
];

$valueStyle = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_TOP,
        'wrapText' => true
    ],
];

// Set Title
$sheet->setCellValue('A1', $stage['project_name']);
$sheet->setCellValue('A2', $stage['name']);
$sheet->setCellValue('A3', 'Created By: ' . ($stage['creator_name'] ?? 'Unknown'));
$sheet->mergeCells('A1:B1');
$sheet->mergeCells('A2:B2');
$sheet->mergeCells('A3:B3');

$sheet->getStyle('A1')->applyFromArray($headerStyle);
$sheet->getStyle('A2')->applyFromArray($subHeaderStyle);
$sheet->getStyle('A3')->applyFromArray($subHeaderStyle);

$sheet->getRowDimension(1)->setRowHeight(30);
$sheet->getRowDimension(2)->setRowHeight(25);
$sheet->getRowDimension(3)->setRowHeight(25);

$row = 5;
$data = null;
$fields = [];

// Fetch Details based on Stage Name
if ($stage['name'] === 'User Request Specification') {
    $stmt = $pdo->prepare("SELECT * FROM stage_urs_details WHERE stage_id = :id");
    $stmt->execute(['id' => $stage_id]);
    $data = $stmt->fetch();
    
    $fields = [
        'Requestor Name' => 'requestor_name',
        'Requestor Department' => 'requestor_department',
        'Request Date' => 'request_date',
        'Software Purpose' => 'software_purpose',
        'Functional Requirements' => 'functional_requirements',
        'Technical Requirements' => 'technical_requirements',
        'User Requirements' => 'user_requirements',
        'Acceptance Criteria' => 'acceptance_criteria'
    ];
} elseif ($stage['name'] === 'IQ - Installation Qualification') {
    $stmt = $pdo->prepare("SELECT * FROM stage_iq_details WHERE stage_id = :id");
    $stmt->execute(['id' => $stage_id]);
    $data = $stmt->fetch();

    $fields = [
        'Installation Date' => 'installation_date',
        'Hardware Verification' => 'hardware_verification',
        'Software Verification' => 'software_verification',
        'Documentation' => 'documentation',
        'IQ Result' => 'iq_result'
    ];
} elseif ($stage['name'] === 'OQ - Operational Qualification') {
    $stmt = $pdo->prepare("SELECT * FROM stage_oq_details WHERE stage_id = :id");
    $stmt->execute(['id' => $stage_id]);
    $data = $stmt->fetch();

    $fields = [
        'Test Date' => 'test_date',
        'Main Function Test' => 'main_function_test',
        'Interface Test' => 'interface_test',
        'Security Test' => 'security_test',
        'OQ Result' => 'oq_result'
    ];
} elseif ($stage['name'] === 'PQ - Performance Qualification') {
    $stmt = $pdo->prepare("SELECT * FROM stage_pq_details WHERE stage_id = :id");
    $stmt->execute(['id' => $stage_id]);
    $data = $stmt->fetch();

    $fields = [
        'Test Date' => 'test_date',
        'Test Scenario' => 'test_scenario',
        'Test Data' => 'test_data',
        'Performance Result' => 'performance_result',
        'PQ Conclusion' => 'pq_conclusion'
    ];
} elseif ($stage['name'] === 'Laporan Validasi') {
    $stmt = $pdo->prepare("SELECT * FROM stage_validation_report_details WHERE stage_id = :id");
    $stmt->execute(['id' => $stage_id]);
    $data = $stmt->fetch();

    $fields = [
        'Executive Summary' => 'executive_summary',
        'Overall Result' => 'overall_result',
        'Deviation' => 'deviation',
        'Recommendation' => 'recommendation',
        'Approval' => 'approval'
    ];
} else {
    $sheet->setCellValue('A' . $row, "Generic Stage Data");
    $fields = ['Completion Date' => 'completion_date'];
    $data = $stage;
}

if ($data || ($stage['name'] === 'Generic')) {
    foreach ($fields as $label => $key) {
        $sheet->setCellValue('A' . $row, $label);
        $sheet->setCellValue('B' . $row, $data[$key] ?? '-');
        
        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->getStyle('B' . $row)->applyFromArray($valueStyle);
        
        $row++;
    }
} else {
    $sheet->setCellValue('A' . $row, "No data available for this stage.");
    $sheet->mergeCells("A$row:B$row");
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;
}

// Documents Section
$row += 2;
$sheet->setCellValue('A' . $row, 'Uploaded Documents');
$sheet->mergeCells("A$row:B$row");
$sheet->getStyle('A' . $row)->applyFromArray($subHeaderStyle);
$row++;

$stmtDocs = $pdo->prepare("SELECT * FROM stage_documents WHERE stage_id = :id ORDER BY uploaded_at DESC");
$stmtDocs->execute(['id' => $stage_id]);
$documents = $stmtDocs->fetchAll();

if (count($documents) > 0) {
    $sheet->setCellValue('A' . $row, 'File Name');
    $sheet->setCellValue('B' . $row, 'Upload Date');
    $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
    $sheet->getStyle('B' . $row)->applyFromArray($labelStyle);
    $row++;

    foreach ($documents as $doc) {
        $sheet->setCellValue('A' . $row, $doc['file_name']);
        $sheet->setCellValue('B' . $row, $doc['uploaded_at']);
        $sheet->getStyle('A' . $row)->applyFromArray($valueStyle);
        $sheet->getStyle('B' . $row)->applyFromArray($valueStyle);
        $row++;
    }
} else {
    $sheet->setCellValue('A' . $row, 'No documents uploaded.');
    $sheet->mergeCells("A$row:B$row");
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Auto width
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(70);

// Download
$filename = preg_replace('/[^a-zA-Z0-9]/', '_', $stage['name']) . '_Report.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
