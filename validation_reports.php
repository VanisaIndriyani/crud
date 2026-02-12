<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Fetch Completed Validation Reports
// Logic: Get projects where 'Laporan Validasi' stage is Completed and has a document
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;
$reports = [];
$totalPages = 1;

try {
    // Count total reports for pagination
    $countStmt = $pdo->query("
        SELECT COUNT(*) 
        FROM projects p
        JOIN project_stages ps ON p.id = ps.project_id
        JOIN stage_documents sd ON ps.id = sd.stage_id
        WHERE ps.name = 'Laporan Validasi' 
          AND ps.status = 'Completed'
          AND sd.uploaded_at = (
              SELECT MAX(uploaded_at) 
              FROM stage_documents 
              WHERE stage_id = ps.id
          )
    ");
    $totalReports = $countStmt->fetchColumn();
    $totalPages = ceil($totalReports / $limit);

    // Fetch reports with limit and offset
    $stmt = $pdo->prepare("
        SELECT 
            p.name as project_name, 
            p.software, 
            p.version, 
            ps.completion_date, 
            sd.id as doc_id, 
            sd.file_name,
            sd.file_path
        FROM projects p
        JOIN project_stages ps ON p.id = ps.project_id
        JOIN stage_documents sd ON ps.id = sd.stage_id
        WHERE ps.name = 'Laporan Validasi' 
          AND ps.status = 'Completed'
          AND sd.uploaded_at = (
              SELECT MAX(uploaded_at) 
              FROM stage_documents 
              WHERE stage_id = ps.id
          )
        ORDER BY ps.completion_date DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error fetching reports: " . $e->getMessage();
}
?>

<div class="fade-in container" style="max-width: 1200px; padding-top: 2rem;">
    <div class="dashboard-header">
        <div>
            <h2><i class="fas fa-file-signature" style="margin-right: 10px; color: var(--accent-color);"></i>Validation Reports</h2>
            <p style="color: rgba(255,255,255,0.8);">Centralized repository of approved validation reports</p>
        </div>
        <div>
            <a href="dashboard.php" class="btn btn-secondary" style="border-radius: 20px;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card" style="background: var(--white); border-radius: 16px; padding: 0; overflow: hidden; box-shadow: var(--shadow-lg);">
        <?php if (isset($error)): ?>
            <div class="alert alert-error" style="margin: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="background: #f8f9fa; text-align: left;">
                        <th style="padding: 1.5rem 1.2rem; color: var(--text-light); font-weight: 700; font-size: 0.9rem; border-bottom: 2px solid var(--border-color); text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-project-diagram" style="margin-right: 8px; color: var(--primary-color);"></i>Project
                        </th>
                        <th style="padding: 1.5rem 1.2rem; color: var(--text-light); font-weight: 700; font-size: 0.9rem; border-bottom: 2px solid var(--border-color); text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-cube" style="margin-right: 8px; color: var(--primary-color);"></i>Software
                        </th>
                        <th style="padding: 1.5rem 1.2rem; color: var(--text-light); font-weight: 700; font-size: 0.9rem; border-bottom: 2px solid var(--border-color); text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="far fa-calendar-check" style="margin-right: 8px; color: var(--primary-color);"></i>Completion Date
                        </th>
                        <th style="padding: 1.5rem 1.2rem; color: var(--text-light); font-weight: 700; font-size: 0.9rem; border-bottom: 2px solid var(--border-color); text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fas fa-file-alt" style="margin-right: 8px; color: var(--primary-color);"></i>Report File
                        </th>
                        <th style="padding: 1.5rem 1.2rem; color: var(--text-light); font-weight: 700; font-size: 0.9rem; border-bottom: 2px solid var(--border-color); text-align: center; text-transform: uppercase; letter-spacing: 0.5px;">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="5" style="padding: 4rem 2rem; text-align: center; color: var(--text-light);">
                                <div style="width: 80px; height: 80px; background: #f5f5f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                    <i class="fas fa-folder-open" style="font-size: 2.5rem; color: #bdbdbd;"></i>
                                </div>
                                <h3 style="margin-bottom: 0.5rem; color: var(--text-color);">No Reports Found</h3>
                                <p style="font-size: 0.9rem;">There are no completed validation reports available at the moment.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $report): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#fcfcfc'; this.style.transform='scale(1.002)';" onmouseout="this.style.backgroundColor='transparent'; this.style.transform='scale(1)';">
                                <td style="padding: 1.5rem 1.2rem; font-weight: 600; color: var(--primary-dark); font-size: 1rem;">
                                    <?php echo htmlspecialchars($report['project_name']); ?>
                                </td>
                                <td style="padding: 1.5rem 1.2rem;">
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 500; color: var(--text-color); font-size: 1.05rem;"><?php echo htmlspecialchars($report['software']); ?></span>
                                        <span style="display: inline-block; background: #e3f2fd; color: #1565c0; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: 600; margin-top: 6px; width: fit-content;">
                                            v<?php echo htmlspecialchars($report['version']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td style="padding: 1.5rem 1.2rem; font-family: 'Poppins', sans-serif; color: var(--text-color);">
                                    <?php echo date('d M Y', strtotime($report['completion_date'])); ?>
                                </td>
                                <td style="padding: 1.5rem 1.2rem;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="width: 40px; height: 40px; background: #ffebee; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-file-pdf" style="color: #e53935; font-size: 1.4rem;"></i>
                                        </div>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;" title="<?php echo htmlspecialchars($report['file_name']); ?>">
                                                <?php echo htmlspecialchars($report['file_name']); ?>
                                            </span>
                                            <span style="font-size: 0.75rem; color: var(--text-light);">PDF Document</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.5rem 1.2rem; text-align: center;">
                                    <div style="display: flex; gap: 8px; justify-content: center;">
                                        <a href="view_report.php?id=<?php echo $report['doc_id']; ?>" target="_blank" class="btn btn-accent" style="padding: 8px 15px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s; white-space: nowrap;">
                                            <i class="far fa-eye" style="margin-right: 4px;"></i> View
                                        </a>
                                        <button onclick="sendEmail('<?php echo htmlspecialchars(addslashes($report['project_name'])); ?>', '<?php echo htmlspecialchars(addslashes($report['software'])); ?>', '<?php echo htmlspecialchars(addslashes($report['version'])); ?>', '<?php echo htmlspecialchars(addslashes($report['file_path'])); ?>', '<?php echo htmlspecialchars(addslashes($report['file_name'])); ?>')" class="btn" style="padding: 8px 15px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; background: #ffa000; color: white; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s; cursor: pointer; display: flex; align-items: center; white-space: nowrap;">
                                            <i class="far fa-envelope" style="margin-right: 4px;"></i> Email
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="padding: 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: center; gap: 5px;">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="page-link" style="padding: 8px 16px; border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-color); text-decoration: none; transition: all 0.2s;"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>" style="padding: 8px 16px; border: 1px solid <?php echo $i === $page ? 'var(--primary-color)' : 'var(--border-color)'; ?>; background: <?php echo $i === $page ? 'var(--primary-color)' : 'transparent'; ?>; color: <?php echo $i === $page ? '#fff' : 'var(--text-color)'; ?>; border-radius: 6px; text-decoration: none; transition: all 0.2s;">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="page-link" style="padding: 8px 16px; border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-color); text-decoration: none; transition: all 0.2s;"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function sendEmail(projectName, software, version, filePath, fileName) {
    const subject = `Laporan Validasi: ${projectName}`;
    const body = `Halo,\n\nBerikut adalah informasi bahwa Laporan Validasi telah tersedia untuk:\nProyek: ${projectName}\nSoftware: ${software} v${version}\n\nTerima kasih.`;
    
    // Open Gmail Compose in new tab
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.open(gmailUrl, '_blank');
}
</script>

<?php include 'includes/footer.php'; ?>
