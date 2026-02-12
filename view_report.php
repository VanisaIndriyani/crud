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
    } elseif (in_array($ext, ['xlsx', 'xls', 'csv'])) {
        // Handle Excel Files with SheetJS (Client-side rendering)
        $fileContent = base64_encode(file_get_contents($file));
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>View Excel - <?php echo htmlspecialchars($filename); ?></title>
            <!-- SheetJS for reading Excel files -->
            <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f4f6f8; color: #333; }
                .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 15px 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
                h3 { margin: 0; font-size: 1.1rem; color: #2c3e50; }
                .close-btn { background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 0.9rem; text-decoration: none; }
                .close-btn:hover { background: #5a6268; }
                
                /* Tabs */
                .tabs { display: flex; gap: 2px; overflow-x: auto; margin-bottom: 0; padding-bottom: 5px; border-bottom: 1px solid #dee2e6; }
                .tab { padding: 8px 20px; background: #e9ecef; border: 1px solid #dee2e6; border-bottom: none; cursor: pointer; border-radius: 6px 6px 0 0; font-size: 0.9rem; color: #495057; white-space: nowrap; }
                .tab.active { background: #fff; color: #1a73e8; font-weight: 600; border-bottom: 1px solid #fff; margin-bottom: -1px; }
                
                /* Table Container */
                #excel-container { background: white; padding: 20px; border-radius: 0 0 8px 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: auto; min-height: 500px; border: 1px solid #dee2e6; border-top: none; }
                
                /* Generated Table Styles */
                table { border-collapse: collapse; width: 100%; min-width: 600px; }
                td, th { border: 1px solid #e0e0e0; padding: 8px 12px; font-size: 14px; text-align: left; }
                tr:nth-child(even) { background-color: #f8f9fa; }
                tr:hover { background-color: #f1f3f5; }
            </style>
        </head>
        <body>
            <div class="header-bar">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; background: #2e7d32; color: white; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">XLS</div>
                    <h3><?php echo htmlspecialchars($filename); ?></h3>
                </div>
                <!-- Since window.close() only works for windows opened by script, we rely on user closing tab or providing back link if needed. 
                     But usually this opens in new tab. -->
                <button onclick="window.close()" class="close-btn">Close Preview</button>
            </div>

            <div id="tabs" class="tabs"></div>
            <div id="excel-container">
                <div style="text-align: center; padding: 50px; color: #666;">
                    Loading Excel file...
                </div>
            </div>

            <script>
                try {
                    const base64File = "<?php echo $fileContent; ?>";
                    const workbook = XLSX.read(base64File, {type: 'base64'});
                    
                    const tabsContainer = document.getElementById('tabs');
                    const container = document.getElementById('excel-container');
                    
                    // Render Sheet Function
                    function renderSheet(sheetName) {
                        const worksheet = workbook.Sheets[sheetName];
                        // Convert to HTML
                        const html = XLSX.utils.sheet_to_html(worksheet, {id: "excel-table", editable: false});
                        container.innerHTML = html;
                        
                        // Update active tab
                        document.querySelectorAll('.tab').forEach(btn => {
                            btn.classList.toggle('active', btn.textContent === sheetName);
                        });
                    }

                    // Clear loading message
                    container.innerHTML = '';
                    tabsContainer.innerHTML = '';

                    // Generate Tabs
                    if (workbook.SheetNames.length > 0) {
                        workbook.SheetNames.forEach((name, index) => {
                            const btn = document.createElement('div');
                            btn.className = 'tab ' + (index === 0 ? 'active' : '');
                            btn.textContent = name;
                            btn.onclick = () => renderSheet(name);
                            tabsContainer.appendChild(btn);
                        });

                        // Render first sheet
                        renderSheet(workbook.SheetNames[0]);
                    } else {
                        container.innerHTML = '<p style="padding: 20px; text-align: center;">No sheets found in this file.</p>';
                    }

                } catch (error) {
                    console.error("Error parsing Excel:", error);
                    document.getElementById('excel-container').innerHTML = 
                        '<div style="text-align: center; padding: 40px; color: #d32f2f;">' +
                        '<h3>Error Loading File</h3>' +
                        '<p>Sorry, we could not process this Excel file for preview.</p>' +
                        '<p style="font-size: 0.9em; color: #666;">Details: ' + error.message + '</p>' +
                        '</div>';
                }
            </script>
        </body>
        </html>
        <?php
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
