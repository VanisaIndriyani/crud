<?php 
require_once 'includes/db.php';
include 'includes/header.php'; 

$project_id = $_GET['id'] ?? 0;
$project = null;
$stages = [];
$progress = 0;

if ($project_id) {
    try {
        // Fetch Project
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute(['id' => $project_id]);
        $project = $stmt->fetch();

        if ($project) {
            // Fetch Stages
            $stmt = $pdo->prepare("SELECT * FROM project_stages WHERE project_id = :id ORDER BY id ASC");
            $stmt->execute(['id' => $project_id]);
            $stages = $stmt->fetchAll();

            // Calculate Progress
            $completedStages = 0;
            foreach ($stages as $stage) {
                if ($stage['status'] === 'Completed') {
                    $completedStages++;
                }
            }
            $progress = $completedStages;
        }
    } catch (Exception $e) {
        $error = "Error fetching project details: " . $e->getMessage();
    }
}
?>

<div class="container fade-in">
    <a href="dashboard.php" class="back-link">
        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to Projects
    </a>

    <?php if ($project): ?>
        <div class="project-detail-card">
            <div class="project-detail-header">
                <div class="project-info">
                    <h1 class="stage-title"><?php echo htmlspecialchars($project['name']); ?></h1>
                    <div style="display: flex; gap: 10px; margin-top: 5px;">
                        <span class="badge badge-primary">
                            <i class="fas fa-code-branch"></i> <?php echo htmlspecialchars($project['version']); ?>
                        </span>
                        <span class="badge badge-secondary">
                            <i class="fas fa-laptop-code"></i> <?php echo htmlspecialchars($project['software']); ?>
                        </span>
                    </div>
                </div>

                <?php 
                $percent = count($stages) > 0 ? round(($progress / count($stages)) * 100) : 0;
                ?>
                <div class="progress-section">
                    <div class="progress-info-text">
                        <span class="progress-label">Overall Progress</span>
                        <span class="progress-subtext"><?php echo $progress; ?> of <?php echo count($stages); ?> stages completed</span>
                    </div>
                    <div class="circular-progress" style="background: conic-gradient(var(--primary-color) <?php echo $percent * 3.6; ?>deg, #e0e0e0 0deg);">
                        <div class="circular-value"><?php echo $percent; ?>%</div>
                    </div>
                </div>
            </div>

            <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">Validation Stages</h3>

            <div class="stages-list">
                <?php foreach ($stages as $index => $stage): ?>
                <?php 
                    $statusClass = match($stage['status']) {
                        'Completed' => 'completed',
                        'In Progress' => 'in-progress',
                        default => 'not-started'
                    };
                    
                    $statusColor = match($stage['status']) {
                        'Completed' => 'var(--success-color)',
                        'In Progress' => 'var(--warning-color)',
                        default => 'var(--text-light)'
                    };
                ?>
                <div class="stage-wrapper" style="display: flex; gap: 10px;">
                    <a href="stage_details.php?id=<?php echo $stage['id']; ?>" class="stage-item <?php echo $statusClass; ?>" style="flex-grow: 1;">
                        <div class="stage-number"><?php echo $index + 1; ?></div>
                        <div class="stage-info">
                            <div class="stage-title"><?php echo htmlspecialchars($stage['name']); ?></div>
                            <div class="stage-status" style="color: <?php echo $statusColor; ?>">
                                <?php echo $stage['status']; ?>
                            </div>
                        </div>
                        <div class="stage-check">
                            <?php if ($stage['status'] == 'Completed'): ?>
                                <i class="fas fa-check-circle" style="color: var(--success-color); font-size: 24px;"></i>
                            <?php elseif ($stage['status'] == 'In Progress'): ?>
                                <i class="fas fa-spinner fa-spin" style="color: var(--warning-color); font-size: 24px;"></i>
                            <?php else: ?>
                                <i class="far fa-circle" style="color: #ddd; font-size: 24px;"></i>
                            <?php endif; ?>
                        </div>
                        <div style="margin-left: 1rem; color: var(--text-light);">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                    
                    <a href="export_stage_excel.php?id=<?php echo $stage['id']; ?>" 
                       style="display: flex; align-items: center; justify-content: center; width: 60px; background: var(--white); border: 1px solid var(--border-color); border-radius: 12px; color: var(--success-color); font-size: 1.5rem; transition: all 0.3s; text-decoration: none;"
                       onmouseover="this.style.borderColor='var(--success-color)'; this.style.backgroundColor='#e8f5e9'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';"
                       onmouseout="this.style.borderColor='var(--border-color)'; this.style.backgroundColor='var(--white)'; this.style.transform='none'; this.style.boxShadow='none';"
                       title="Export to Excel">
                        <i class="fas fa-file-excel"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="project-detail-card" style="text-align: center; padding: 4rem;">
            <div style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;">
                <i class="fas fa-folder-open"></i>
            </div>
            <h2>Project not found</h2>
            <p style="color: var(--text-light);">The project you are looking for does not exist or has been deleted.</p>
            <a href="dashboard.php" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-home"></i> Return to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
