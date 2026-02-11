<?php
session_start();
require_once 'includes/db.php';

// Log logout if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        $stmtLog = $pdo->prepare("INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES (:uid, 'Logout', :ip, :ua)");
        $stmtLog->execute([
            'uid' => $_SESSION['user_id'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    } catch (Exception $e) {
        // Ignore logging errors
    }
}

session_destroy();
header('Location: index.php');
exit;
