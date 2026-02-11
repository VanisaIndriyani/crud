<?php
require_once 'includes/db.php';

try {
    // Check if users exist
    $stmtUsers = $pdo->query("SELECT id FROM users");
    $users = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);

    if (empty($users)) {
        // Create a dummy admin user if none exist
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES ('admin', :pass, 'admin')");
        $stmt->execute(['pass' => $password]);
        $users[] = $pdo->lastInsertId();
        echo "Created default admin user (ID: " . end($users) . ")<br>";
    }

    $actions = ['Login', 'Logout', 'Failed Login'];
    $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'
    ];
    $ips = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '127.0.0.1'];

    // Insert 15 dummy logs
    $stmt = $pdo->prepare("INSERT INTO user_logs (user_id, action, ip_address, user_agent, created_at) VALUES (:uid, :action, :ip, :ua, :created)");

    for ($i = 0; $i < 15; $i++) {
        $uid = $users[array_rand($users)];
        $action = $actions[array_rand($actions)];
        $ip = $ips[array_rand($ips)];
        $ua = $user_agents[array_rand($user_agents)];
        // Random date within last 30 days
        $created = date('Y-m-d H:i:s', strtotime('-' . rand(0, 30) . ' days -' . rand(0, 24) . ' hours -' . rand(0, 60) . ' minutes'));

        $stmt->execute([
            'uid' => $uid,
            'action' => $action,
            'ip' => $ip,
            'ua' => $ua,
            'created' => $created
        ]);
    }

    echo "Successfully seeded 15 user logs!";

} catch (PDOException $e) {
    echo "Error seeding logs: " . $e->getMessage();
}
?>
