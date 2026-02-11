<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Fetch Logs with Pagination
$limit = 15;
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

<div class="fade-in container" style="max-width: 1200px; padding-top: 2rem;">
    <div class="dashboard-header">
        <div>
            <h2><i class="fas fa-history" style="margin-right: 10px; color: var(--accent-color);"></i>User Activity Logs</h2>
            <p style="color: rgba(255,255,255,0.8);">Monitor user logins and system security events</p>
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
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; text-align: left;">
                        <th style="padding: 1.2rem; color: var(--text-light); font-weight: 600; font-size: 0.9rem; border-bottom: 2px solid var(--border-color); white-space: nowrap;">
                            <i class="far fa-clock" style="margin-right: 8px;"></i>Time
                        </th>
                        <th style="padding: 1.2rem; color: var(--text-light); font-weight: 600; font-size: 0.9rem; border-bottom: 2px solid var(--border-color);">
                            <i class="far fa-user" style="margin-right: 8px;"></i>User
                        </th>
                        <th style="padding: 1.2rem; color: var(--text-light); font-weight: 600; font-size: 0.9rem; border-bottom: 2px solid var(--border-color);">
                            <i class="fas fa-bolt" style="margin-right: 8px;"></i>Action
                        </th>
                        <th style="padding: 1.2rem; color: var(--text-light); font-weight: 600; font-size: 0.9rem; border-bottom: 2px solid var(--border-color);">
                            <i class="fas fa-network-wired" style="margin-right: 8px;"></i>IP Address
                        </th>
                        <th style="padding: 1.2rem; color: var(--text-light); font-weight: 600; font-size: 0.9rem; border-bottom: 2px solid var(--border-color);">
                            <i class="fas fa-desktop" style="margin-right: 8px;"></i>Device Info
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="5" style="padding: 3rem; text-align: center; color: var(--text-light);">
                                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No activity logs found yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.2s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1.2rem; color: var(--text-color); font-family: monospace; font-size: 0.95rem;">
                                    <?php echo date('M d, Y H:i:s', strtotime($log['created_at'])) . ' WIB'; ?>
                                </td>
                                <td style="padding: 1.2rem;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 32px; height: 32px; background: var(--primary-light); color: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                            <?php echo strtoupper(substr($log['username'] ?? 'U', 0, 1)); ?>
                                        </div>
                                        <span style="font-weight: 600; color: var(--primary-dark);"><?php echo htmlspecialchars($log['username'] ?? 'Unknown'); ?></span>
                                    </div>
                                </td>
                                <td style="padding: 1.2rem;">
                                    <?php 
                                        $actionColor = match($log['action']) {
                                            'Login' => '#e8f5e9',
                                            'Logout' => '#fff3e0',
                                            'Failed Login' => '#ffebee',
                                            default => '#f5f5f5'
                                        };
                                        $textColor = match($log['action']) {
                                            'Login' => '#2e7d32',
                                            'Logout' => '#ef6c00',
                                            'Failed Login' => '#c62828',
                                            default => '#616161'
                                        };
                                        $icon = match($log['action']) {
                                            'Login' => 'fa-sign-in-alt',
                                            'Logout' => 'fa-sign-out-alt',
                                            'Failed Login' => 'fa-exclamation-triangle',
                                            default => 'fa-info-circle'
                                        };
                                    ?>
                                    <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: <?php echo $actionColor; ?>; color: <?php echo $textColor; ?>; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fas <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td style="padding: 1.2rem; color: var(--text-color); font-family: monospace;">
                                    <?php echo htmlspecialchars($log['ip_address']); ?>
                                </td>
                                <td style="padding: 1.2rem; font-size: 0.85rem; color: var(--text-light); max-width: 300px;">
                                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                        <?php 
                                            // Simple parser for User Agent
                                            $ua = $log['user_agent'];
                                            $browser = 'Unknown';
                                            if (strpos($ua, 'Chrome') !== false) $browser = 'Chrome';
                                            elseif (strpos($ua, 'Firefox') !== false) $browser = 'Firefox';
                                            elseif (strpos($ua, 'Safari') !== false) $browser = 'Safari';
                                            elseif (strpos($ua, 'Edge') !== false) $browser = 'Edge';
                                            
                                            $os = 'Unknown OS';
                                            if (strpos($ua, 'Windows') !== false) $os = 'Windows';
                                            elseif (strpos($ua, 'Mac') !== false) $os = 'MacOS';
                                            elseif (strpos($ua, 'Linux') !== false) $os = 'Linux';
                                            elseif (strpos($ua, 'Android') !== false) $os = 'Android';
                                            elseif (strpos($ua, 'iPhone') !== false) $os = 'iOS';

                                            echo "<i class='fab fa-" . strtolower($browser) . "'></i> $browser on $os";
                                        ?>
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
            <div style="padding: 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #fafafa;">
                <div style="color: var(--text-light); font-size: 0.9rem;">
                    Showing page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </div>
                <div class="pagination" style="margin: 0; gap: 8px;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="page-link" style="width: auto; padding: 0 1rem;"><i class="fas fa-chevron-left"></i> Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="page-link" style="width: auto; padding: 0 1rem;">Next <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
