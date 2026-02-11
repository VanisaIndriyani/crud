<?php
require_once 'includes/db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$doc_id = $_GET['id'] ?? 0;

if (!$doc_id) {
    die("Document ID missing.");
}

try {
    // Fetch document info
    $stmt = $pdo->prepare("SELECT * FROM stage_documents WHERE id = :id");
    $stmt->execute(['id' => $doc_id]);
    $doc = $stmt->fetch();

    if (!$doc || !file_exists($doc['file_path'])) {
        die("Document not found.");
    }

    $file = $doc['file_path'];
    $filename = $doc['file_name'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    // Determine Content Type
    $contentType = 'application/octet-stream';
    if ($ext === 'pdf') $contentType = 'application/pdf';
    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $contentType = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
    elseif ($ext === 'txt') $contentType = 'text/plain';

    // If PDF or Image, show inline
    if ($ext === 'pdf' || in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        // For other files, we can't easily "view" them in browser without downloading.
        // We'll show a message or fallback.
        // But the user insisted on "View Only".
        // Let's try to wrap it in an HTML wrapper if possible, or just default to inline (browser decides).
        // If it's Word/Excel, inline usually triggers download.
        // We will output a simple HTML page saying "Preview not available for this file type".
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>View Document</title>
            <style>
                body { font-family: sans-serif; text-align: center; padding: 50px; background: #f4f6f8; }
                .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: inline-block; }
                .icon { font-size: 48px; color: #ff9800; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="icon">⚠️</div>
                <h2>Preview Not Available</h2>
                <p>The file <strong><?php echo htmlspecialchars($filename); ?></strong> cannot be viewed directly in the browser.</p>
                <p>File type: <?php echo strtoupper($ext); ?></p>
            </div>
        </body>
        </html>
        <?php
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
