<?php
require_once 'includes/db.php';

// Validate ID
$fileId = $_GET['id'] ?? 0;
if (!$fileId) {
    die("Invalid file ID.");
}

// Fetch file info from DB
$stmt = $pdo->prepare("SELECT * FROM stage_documents WHERE id = :id");
$stmt->execute(['id' => $fileId]);
$file = $stmt->fetch();

if (!$file) {
    die("File not found in database.");
}

$filepath = $file['file_path'];

// Security check: Ensure file is within uploads directory
$realBase = realpath('uploads');
$realFile = realpath($filepath);

if ($realFile === false || strpos($realFile, $realBase) !== 0 || !file_exists($realFile)) {
    die("File not found on server.");
}

// Get MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $realFile);
finfo_close($finfo);

// Set headers
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($realFile));

// Clear output buffer
ob_clean();
flush();

// Read file
readfile($realFile);
exit;
?>