<?php
require_once 'includes/db.php';

echo "Starting data seeding...\n";

// Helper function to get stage ID
function getStageId($pdo, $projectId, $stageName) {
    $stmt = $pdo->prepare("SELECT id FROM project_stages WHERE project_id = :pid AND name = :name");
    $stmt->execute(['pid' => $projectId, 'name' => $stageName]);
    return $stmt->fetchColumn();
}

try {
    $pdo->beginTransaction();

    // Project 1: ERP System Validation
    $projectName1 = "Sistem ERP SAP S/4HANA v2.0";
    echo "Creating project: $projectName1\n";
    
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE name = :name");
    $stmt->execute(['name' => $projectName1]);
    $p1Id = $stmt->fetchColumn();

    if (!$p1Id) {
        $stmt = $pdo->prepare("INSERT INTO projects (name, software, version, description, status) VALUES (:name, :software, :version, :description, :status)");
        $stmt->execute([
            'name' => $projectName1,
            'software' => 'SAP S/4HANA',
            'version' => '2.0.5',
            'description' => 'Validation of the new ERP system module for Inventory and Finance.',
            'status' => 'In Progress'
        ]);
        $p1Id = $pdo->lastInsertId();
        
        // Create Stages for P1
        $stages = ['User Request Specification', 'IQ - Installation Qualification', 'OQ - Operational Qualification', 'PQ - Performance Qualification', 'Laporan Validasi'];
        foreach ($stages as $stageName) {
            $status = 'Not Started';
            if ($stageName == 'User Request Specification') $status = 'Completed';
            if ($stageName == 'IQ - Installation Qualification') $status = 'Completed';
            if ($stageName == 'OQ - Operational Qualification') $status = 'In Progress';

            $stmtS = $pdo->prepare("INSERT INTO project_stages (project_id, name, status) VALUES (:pid, :name, :status)");
            $stmtS->execute(['pid' => $p1Id, 'name' => $stageName, 'status' => $status]);
        }
    }

    // Seed Stage Details for P1
    // URS (Completed)
    $ursId = getStageId($pdo, $p1Id, 'User Request Specification');
    if ($ursId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO stage_urs_details (stage_id, requestor_name, requestor_department, request_date, software_purpose, functional_requirements, technical_requirements, user_requirements, acceptance_criteria) VALUES (:id, :rname, :dept, :date, :purpose, :freq, :treq, :ureq, :criteria)");
        $stmt->execute([
            'id' => $ursId,
            'rname' => 'Budi Santoso',
            'dept' => 'IT Department',
            'date' => '2024-01-15',
            'purpose' => 'To manage inventory and finance processes efficiently.',
            'freq' => 'Must handle 10,000 transactions per day.',
            'treq' => 'Server with 32GB RAM, 1TB SSD.',
            'ureq' => 'User friendly interface, Indonesian language support.',
            'criteria' => 'All functional tests passed with 0 critical defects.'
        ]);
    }

    // IQ (Completed)
    $iqId = getStageId($pdo, $p1Id, 'IQ - Installation Qualification');
    if ($iqId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO stage_iq_details (stage_id, installation_date, hardware_verification, software_verification, documentation, iq_result) VALUES (:id, :date, :hw, :sw, :doc, :result)");
        $stmt->execute([
            'id' => $iqId,
            'date' => '2024-02-01',
            'hw' => 'Verified server specifications match requirements.',
            'sw' => 'Software installed successfully on production server.',
            'doc' => 'Installation manual v1.0, Configuration guide.',
            'result' => 'Pass'
        ]);
    }

    // OQ (In Progress)
    $oqId = getStageId($pdo, $p1Id, 'OQ - Operational Qualification');
    if ($oqId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO stage_oq_details (stage_id, test_date, main_function_test, interface_test, security_test, oq_result) VALUES (:id, :date, :main, :int, :sec, :result)");
        $stmt->execute([
            'id' => $oqId,
            'date' => '2024-02-15',
            'main' => 'Testing core modules: Finance, Inventory, Sales.',
            'int' => 'Testing API integration with legacy systems.',
            'sec' => 'Penetration testing and role-based access control.',
            'result' => 'Pending'
        ]);
    }

    // Project 2: Mesin Filling Line A
    $projectName2 = "Mesin Filling Line A";
    echo "Creating project: $projectName2\n";
    
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE name = :name");
    $stmt->execute(['name' => $projectName2]);
    $p2Id = $stmt->fetchColumn();

    if (!$p2Id) {
        $stmt = $pdo->prepare("INSERT INTO projects (name, software, version, description, status) VALUES (:name, :software, :version, :description, :status)");
        $stmt->execute([
            'name' => $projectName2,
            'software' => 'Siemens PLC S7-1500',
            'version' => 'Firmware 2.8',
            'description' => 'Validation of control software for the new filling line.',
            'status' => 'In Progress'
        ]);
        $p2Id = $pdo->lastInsertId();

        // Create Stages for P2
        $stages = ['User Request Specification', 'IQ - Installation Qualification', 'OQ - Operational Qualification', 'PQ - Performance Qualification', 'Laporan Validasi'];
        foreach ($stages as $stageName) {
            $status = 'Not Started';
            if ($stageName == 'User Request Specification') $status = 'Completed';
            if ($stageName == 'IQ - Installation Qualification') $status = 'In Progress';

            $stmtS = $pdo->prepare("INSERT INTO project_stages (project_id, name, status) VALUES (:pid, :name, :status)");
            $stmtS->execute(['pid' => $p2Id, 'name' => $stageName, 'status' => $status]);
        }
    }

    // Seed Stage Details for P2
    // URS (Completed)
    $urs2Id = getStageId($pdo, $p2Id, 'User Request Specification');
    if ($urs2Id) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO stage_urs_details (stage_id, requestor_name, requestor_department, request_date, software_purpose, functional_requirements, technical_requirements, user_requirements, acceptance_criteria) VALUES (:id, :rname, :dept, :date, :purpose, :freq, :treq, :ureq, :criteria)");
        $stmt->execute([
            'id' => $urs2Id,
            'rname' => 'Siti Aminah',
            'dept' => 'Production',
            'date' => '2024-03-01',
            'purpose' => 'Control automated filling process.',
            'freq' => 'Filling accuracy +/- 1ml.',
            'treq' => 'PLC cycle time < 10ms.',
            'ureq' => 'HMI must be touch-screen capable.',
            'criteria' => 'Zero critical alarms during 24h run.'
        ]);
    }

    // IQ (In Progress)
    $iq2Id = getStageId($pdo, $p2Id, 'IQ - Installation Qualification');
    if ($iq2Id) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO stage_iq_details (stage_id, installation_date, hardware_verification, software_verification, documentation, iq_result) VALUES (:id, :date, :hw, :sw, :doc, :result)");
        $stmt->execute([
            'id' => $iq2Id,
            'date' => '2024-03-10',
            'hw' => 'Checking wiring and panel layout.',
            'sw' => 'Uploading program to PLC.',
            'doc' => 'Electrical schematics, IO list.',
            'result' => 'Pending'
        ]);
    }

    $pdo->commit();
    echo "Seeding completed successfully!\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error seeding data: " . $e->getMessage() . "\n";
}
?>