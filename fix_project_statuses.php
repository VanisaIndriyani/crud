<?php
require_once 'includes/db.php';

echo "<h1>Fixing Project Statuses...</h1>";

try {
    // Get all projects
    $stmt = $pdo->query("SELECT id, name, status FROM projects");
    $projects = $stmt->fetchAll();

    $updatedCount = 0;

    foreach ($projects as $project) {
        $projectId = $project['id'];
        
        // Get stages for this project
        $stmtStages = $pdo->prepare("SELECT status FROM project_stages WHERE project_id = :id");
        $stmtStages->execute(['id' => $projectId]);
        $stages = $stmtStages->fetchAll(PDO::FETCH_COLUMN);

        $total = count($stages);
        $completed = 0;
        $started = 0;

        foreach ($stages as $status) {
            if ($status === 'Completed') {
                $completed++;
                $started++;
            } elseif ($status === 'In Progress') {
                $started++;
            }
        }

        $newStatus = 'Draft';
        if ($total > 0 && $completed === $total) {
            $newStatus = 'Completed';
        } elseif ($started > 0) {
            $newStatus = 'In Progress';
        }

        // Only update if different
        if ($newStatus !== $project['status']) {
            $stmtUpdate = $pdo->prepare("UPDATE projects SET status = :status WHERE id = :id");
            $stmtUpdate->execute(['status' => $newStatus, 'id' => $projectId]);
            
            echo "<p>Project <strong>" . htmlspecialchars($project['name']) . "</strong> (ID: $projectId) updated from <em>" . $project['status'] . "</em> to <strong>" . $newStatus . "</strong>.</p>";
            $updatedCount++;
        } else {
            echo "<p style='color:gray'>Project " . htmlspecialchars($project['name']) . " (ID: $projectId) is already correct ($newStatus).</p>";
        }
    }

    echo "<h2>Done! Updated $updatedCount projects.</h2>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>