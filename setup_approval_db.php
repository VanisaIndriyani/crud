<?php
require_once 'includes/db.php';

try {
    // 1. Alter stage_validation_report_details table
    // Check if columns exist first
    $stmt = $pdo->query("SHOW COLUMNS FROM stage_validation_report_details LIKE 'prepared_by'");
    if (!$stmt->fetch()) {
        $sql = "ALTER TABLE stage_validation_report_details 
                ADD COLUMN prepared_by INT DEFAULT NULL,
                ADD COLUMN prepared_date DATETIME DEFAULT NULL,
                ADD COLUMN reviewed_by INT DEFAULT NULL,
                ADD COLUMN reviewed_date DATETIME DEFAULT NULL,
                ADD COLUMN approved_by INT DEFAULT NULL,
                ADD COLUMN approved_date DATETIME DEFAULT NULL,
                ADD FOREIGN KEY (prepared_by) REFERENCES users(id) ON DELETE SET NULL,
                ADD FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
                ADD FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL";
        
        $pdo->exec($sql);
        echo "Table stage_validation_report_details updated successfully.<br>";
    } else {
        echo "Table stage_validation_report_details already has approval columns.<br>";
    }

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>