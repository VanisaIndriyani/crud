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

$docPrefix = 'VEA-FR-MIT';
$docCodeByStageName = [
    'User Request Specification' => '03',
    'IQ - Installation Qualification' => '10',
    'Validation Report' => '12',
    'Laporan Validasi' => '12',
    'OQ - Operational Qualification' => '23',
    'PQ - Performance Qualification' => '25',
];
$docHeaderTitleByStageName = [
    'User Request Specification' => 'User Request Report',
    'IQ - Installation Qualification' => 'Installation Qualification Report',
    'Validation Report' => 'Validation Report',
    'Laporan Validasi' => 'Validation Report',
    'OQ - Operational Qualification' => 'Operational Qualification Report',
    'PQ - Performance Qualification' => 'Performance Qualification Report',
];
$docDisplayStageNameByStageName = [
    'User Request Specification' => 'User Request Specification',
    'IQ - Installation Qualification' => 'Installation Qualification',
    'Validation Report' => 'Validation Report',
    'Laporan Validasi' => 'Validation Report',
    'OQ - Operational Qualification' => 'Operational Qualification',
    'PQ - Performance Qualification' => 'Performance Qualification',
];
$docRevision = '0a';
$effectiveDateText = '17 March 2026';

$docCode = $docCodeByStageName[$stage['name']] ?? '00';
$docHeaderTitle = $docHeaderTitleByStageName[$stage['name']] ?? ($stage['name'] . ' Report');
$docDisplayStageName = $docDisplayStageNameByStageName[$stage['name']] ?? $stage['name'];

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
} elseif (in_array($stage['name'], ['Validation Report', 'Laporan Validasi'], true)) {
    $stmt = $pdo->prepare("
        SELECT d.*, 
               u1.username as prep_name, 
               u2.username as rev_name, 
               u3.username as app_name 
        FROM stage_validation_report_details d
        LEFT JOIN users u1 ON d.prepared_by = u1.id
        LEFT JOIN users u2 ON d.reviewed_by = u2.id
        LEFT JOIN users u3 ON d.approved_by = u3.id
        WHERE d.stage_id = :id
    ");
    $stmt->execute(['id' => $stage_id]);
    $data = $stmt->fetch();

    // Format approval strings
    if ($data) {
        $data['prepared_info'] = ($data['prep_name'] ?? '-') . ($data['prepared_date'] ? ' (' . $data['prepared_date'] . ')' : '');
        $data['reviewed_info'] = ($data['rev_name'] ?? '-') . ($data['reviewed_date'] ? ' (' . $data['reviewed_date'] . ')' : '');
        $data['approved_info'] = ($data['app_name'] ?? '-') . ($data['approved_date'] ? ' (' . $data['approved_date'] . ')' : '');
    }

    $fields = [
        'Executive Summary' => 'executive_summary',
        'Overall Result' => 'overall_result',
        'Deviation' => 'deviation',
        'Recommendation' => 'recommendation',
        'Prepared By' => 'prepared_info',
        'Acknowledged By' => 'reviewed_info',
        'Approved By' => 'approved_info'
    ];
} else {
    $sheet->setCellValue('A' . $row, "Generic Stage Data");
    $fields = ['Completion Date' => 'completion_date'];
    $data = $stage;
}

$topLineStyle = [
    'font' => ['size' => 10],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true
    ],
];

$blueHeaderStyle = [
    'font' => [
        'bold' => true,
        'size' => 16,
        'color' => ['argb' => 'FFFFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF1F4E79'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$greenBarStyle = [
    'font' => [
        'bold' => true,
        'size' => 12,
        'color' => ['argb' => 'FFFFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF00B050'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$noteStyle = [
    'font' => ['size' => 8],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_TOP,
        'wrapText' => true
    ],
];

$sheet->setCellValue('A1', 'PT Vision Ease Asia, ' . $docPrefix . '-' . $docCode . ' ' . $docHeaderTitle . ', Rev : ' . $docRevision . ', Tgl Efektif : ' . $effectiveDateText);
$sheet->mergeCells('A1:B1');
$sheet->getStyle('A1')->applyFromArray($topLineStyle);
$sheet->getRowDimension(1)->setRowHeight(15);

$sheet->setCellValue('A2', 'Software Validation System');
$sheet->mergeCells('A2:B2');
$sheet->getStyle('A2')->applyFromArray($blueHeaderStyle);
$sheet->getRowDimension(2)->setRowHeight(30);

$sheet->setCellValue('A3', $stage['name']);
$sheet->mergeCells('A3:B3');
$sheet->getStyle('A3')->applyFromArray($greenBarStyle);
$sheet->getRowDimension(3)->setRowHeight(25);

$sheet->setCellValue('A4', 'Created By: ' . ($stage['creator_name'] ?? 'Unknown'));
$sheet->mergeCells('A4:B4');
$sheet->getStyle('A4')->applyFromArray($greenBarStyle);
$sheet->getRowDimension(4)->setRowHeight(25);

$sheet->getRowDimension(5)->setRowHeight(8);
$row = 6;

$largeTextKeys = [
    'software_purpose',
    'functional_requirements',
    'technical_requirements',
    'user_requirements',
    'acceptance_criteria',
    'hardware_verification',
    'software_verification',
    'documentation',
    'main_function_test',
    'interface_test',
    'security_test',
    'test_scenario',
    'test_data',
    'performance_result',
    'pq_conclusion',
    'executive_summary',
    'overall_result',
    'deviation',
    'recommendation',
    'iq_result',
    'oq_result'
];

$dateKeys = [
    'request_date',
    'installation_date',
    'test_date',
    'completion_date'
];

if ($data || ($stage['name'] === 'Generic')) {
    foreach ($fields as $label => $key) {
        $value = $data[$key] ?? '-';
        if (in_array($key, $dateKeys, true) && !empty($value) && $value !== '-') {
            $ts = strtotime((string)$value);
            if ($ts !== false) {
                $value = date('d-m-Y', $ts);
            }
        }

        $sheet->setCellValue('A' . $row, $label);
        $sheet->setCellValue('B' . $row, $value);

        $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
        $sheet->getStyle('B' . $row)->applyFromArray($valueStyle);

        if (in_array($key, $largeTextKeys, true) || (is_string($value) && mb_strlen($value) > 120)) {
            $sheet->getRowDimension($row)->setRowHeight(80);
        } else {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        $row++;
    }
} else {
    $sheet->setCellValue('A' . $row, "No data available for this stage.");
    $sheet->mergeCells("A$row:B$row");
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($row)->setRowHeight(18);
    $row++;
}

$row += 2;

$sheet->setCellValue('A' . $row, 'Uploaded Documents');
$sheet->mergeCells("A$row:B$row");
$sheet->getStyle('A' . $row)->applyFromArray($greenBarStyle);
$sheet->getRowDimension($row)->setRowHeight(25);
$row++;

$stmtDocs = $pdo->prepare("SELECT * FROM stage_documents WHERE stage_id = :id ORDER BY uploaded_at DESC");
$stmtDocs->execute(['id' => $stage_id]);
$documents = $stmtDocs->fetchAll();

if (count($documents) > 0) {
    $sheet->setCellValue('A' . $row, 'File Name');
    $sheet->setCellValue('B' . $row, 'Upload Date');
    $sheet->getStyle('A' . $row)->applyFromArray($labelStyle);
    $sheet->getStyle('B' . $row)->applyFromArray($labelStyle);
    $sheet->getRowDimension($row)->setRowHeight(20);
    $row++;

    foreach ($documents as $doc) {
        $sheet->setCellValue('A' . $row, $doc['file_name']);
        $sheet->setCellValue('B' . $row, $doc['uploaded_at']);
        $sheet->getStyle('A' . $row)->applyFromArray($valueStyle);
        $sheet->getStyle('B' . $row)->applyFromArray($valueStyle);
        $sheet->getRowDimension($row)->setRowHeight(18);
        $row++;
    }
} else {
    $sheet->setCellValue('A' . $row, 'No documents uploaded.');
    $sheet->mergeCells("A$row:B$row");
    $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($row)->setRowHeight(18);
    $row++;
}

$row += 2;
$sheet->setCellValue(
    'A' . $row,
    'Catatan : Setiap copy dokumen internal tanpa tanda "Controlled Copy" dianggap sebagai dokumen tidak terkendali. Adalah tanggung jawab setiap pengguna untuk memastikan isi dokumen yang digunakan sebagai yang terbaru dan berlaku serta memusnahkan dokumen lama yang sudah tidak berlaku.'
);
$sheet->mergeCells("A$row:B$row");
$sheet->getStyle('A' . $row)->applyFromArray($noteStyle);
$sheet->getRowDimension($row)->setRowHeight(35);

$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(80);

// Download
$filenameBase = $docPrefix . '-' . $docCode . ' ' . $docDisplayStageName . ' Report';
$filename = preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]+/', '', $filenameBase) . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
