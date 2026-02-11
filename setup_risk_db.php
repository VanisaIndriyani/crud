<?php
require_once 'includes/db.php';

echo "<h1>Setup Risk Assessment Database</h1>";

try {
    // 1. Create risk_assessments table
    $sql = "CREATE TABLE IF NOT EXISTS risk_assessments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        process_name VARCHAR(255) NOT NULL,
        failure_mode VARCHAR(255),
        effect_of_failure VARCHAR(255),
        cause_of_failure VARCHAR(255),
        severity INT NOT NULL DEFAULT 1,
        occurrence INT NOT NULL DEFAULT 1,
        detection INT NOT NULL DEFAULT 1,
        rpn INT NOT NULL DEFAULT 1,
        corrective_actions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "<p>Tabel <code>risk_assessments</code> berhasil dibuat/diupdate.</p>";

    // 2. Add columns to projects table for caching risk status (optional but good for performance)
    // Check if columns exist first to avoid errors
    $columns = [
        'max_rpn' => 'INT DEFAULT 0',
        'risk_level' => "ENUM('Low', 'Medium', 'High', 'Non-GxP') DEFAULT NULL",
        'next_revalidation_date' => 'DATE DEFAULT NULL'
    ];

    foreach ($columns as $col => $def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM projects LIKE '$col'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE projects ADD COLUMN $col $def");
            echo "<p>Kolom <code>$col</code> berhasil ditambahkan ke tabel <code>projects</code>.</p>";
        } else {
            echo "<p>Kolom <code>$col</code> sudah ada di tabel <code>projects</code>.</p>";
        }
    }

    echo "<h2>Setup Selesai</h2>";
    echo "<a href='dashboard.php'>Kembali ke Dashboard</a>";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>