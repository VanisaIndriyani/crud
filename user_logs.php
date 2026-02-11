<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Fetch Logs with Pagination
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$totalLogs = 0;
$totalPages = 0;
$logs = [];

try {
    // Get total count
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM user_logs");
    $totalLogs = $stmtCount->fetchColumn();
    $totalPages = ceil($totalLogs / $limit);

    // Get paginated logs
    $stmt = $pdo->prepare("
        SELECT l.*, u.username 
        FROM user_logs l 
        LEFT JOIN users u ON l.user_id = u.id 
        ORDER BY l.created_at DESC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error fetching logs: " . $e->getMessage();
}
?>

<div class="fade-in">
    <div class="dashboard-header">
        <div>
            <h2><i class="fas fa-history"></i> User Activity Logs</h2>
            <p>Monitor user logins and system activities</p>
        </div>
        <div>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <?php if (isset($error)): ?>
            <p style="color: red; padding: 15px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--background-color); text-align: left;">
                        <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">Time</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">User</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">Action</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">IP Address</th>
                        <th style="padding: 12px; border-bottom: 2px solid var(--border-color);">User Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-light);">No logs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 12px;"><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                <td style="padding: 12px; font-weight: 500;"><?php echo htmlspecialchars($log['username'] ?? 'Unknown'); ?></td>
                                <td style="padding: 12px;">
                                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; background: #e3f2fd; color: #1565c0;">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; font-family: monospace;"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td style="padding: 12px; font-size: 0.85rem; color: var(--text-light); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                    <?php echo htmlspecialchars($log['user_agent']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 5px;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 5px 10px;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
