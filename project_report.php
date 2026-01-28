<?php 
require_once 'includes/db.php';
include 'includes/header.php'; 

$project_id = $_GET['id'] ?? 0;
$project = null;
$stages = [];

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
        }
    } catch (Exception $e) {
        $error = "Error fetching project details: " . $e->getMessage();
    }
}
?>

<div class="container fade-in">
    <!-- Header Actions -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to Projects
        </a>
        <a href="export_excel.php?id=<?php echo $project_id; ?>" class="btn btn-primary" style="background: linear-gradient(135deg, #2e7d32, #4caf50); color: white; border: none;">
            <i class="fas fa-file-excel"></i> Export to Excel
        </a>
    </div>

    <?php if ($project): ?>
        <div class="project-detail-card" style="margin-bottom: 2rem; border-top: 4px solid var(--accent-color);">
            <h2 style="margin-bottom: 2rem; display: flex; align-items: center; gap: 10px; color: var(--primary-dark);">
                <i class="far fa-file-alt" style="color: var(--accent-dark);"></i> Project Validation Report
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.3rem;">Project Name</div>
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--primary-dark);"><?php echo htmlspecialchars($project['name']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.3rem;">Status</div>
                    <span class="badge badge-<?php 
                        echo match($project['status']) {
                            'Completed' => 'success',
                            'In Progress' => 'primary',
                            default => 'secondary'
                        };
                    ?>" style="<?php echo $project['status'] === 'Completed' ? 'background-color: var(--accent-color); color: var(--primary-dark);' : ''; ?>">
                        <?php echo $project['status']; ?>
                    </span>
                </div>
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.3rem;">Software Name</div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($project['software']); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.3rem;">Version</div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($project['version']); ?></div>
                </div>
            </div>

            <div style="margin-bottom: 2rem; background: #f9fbe7; padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--accent-color);">
                <div style="font-size: 0.9rem; color: var(--accent-dark); margin-bottom: 0.3rem; font-weight: bold;">Description</div>
                <div style="line-height: 1.6; color: var(--text-color);"><?php echo nl2br(htmlspecialchars($project['description'])); ?></div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.3rem;">Created</div>
                    <div style="font-weight: 500;"><?php echo date('M d, Y, h:i A', strtotime($project['created_at'])); ?></div>
                </div>
                <div>
                    <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 0.3rem;">Last Updated</div>
                    <div style="font-weight: 500;"><?php echo date('M d, Y, h:i A', strtotime($project['updated_at'] ?? $project['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="project-detail-card" style="margin-bottom: 2rem; border-top: 4px solid var(--accent-color);">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Validation Stages Summary</h3>
            
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php foreach ($stages as $stage): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border: 1px solid <?php echo $stage['status'] === 'Completed' ? 'var(--accent-color)' : 'var(--border-color)'; ?>; border-radius: 8px; background-color: <?php echo $stage['status'] === 'Completed' ? '#f1f8e9' : 'var(--white)'; ?>;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <?php if ($stage['status'] === 'Completed'): ?>
                                <i class="fas fa-check-circle" style="color: var(--accent-dark); font-size: 1.2rem;"></i>
                            <?php elseif ($stage['status'] === 'In Progress'): ?>
                                <i class="fas fa-spinner fa-spin" style="color: var(--primary-color); font-size: 1.2rem;"></i>
                            <?php else: ?>
                                <i class="far fa-circle" style="color: var(--text-light); font-size: 1.2rem;"></i>
                            <?php endif; ?>
                            
                            <span style="font-weight: 600; color: <?php echo $stage['status'] === 'Completed' ? 'var(--primary-dark)' : 'var(--text-color)'; ?>;"><?php echo htmlspecialchars($stage['name']); ?></span>
                        </div>
                        
                        <?php if ($stage['status'] === 'Completed'): ?>
                            <span class="badge" style="background-color: var(--accent-color); color: var(--primary-dark);">Completed</span>
                        <?php else: ?>
                            <span style="font-size: 0.9rem; color: var(--text-light);">
                                <?php echo $stage['status']; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="project-detail-card" style="border-top: 4px solid var(--accent-color);">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-dark);">Stage Details</h3>
            
            <div style="display: flex; flex-direction: column; gap: 0;">
                <?php foreach ($stages as $stage): ?>
                    <div style="padding: 1.5rem 0; border-bottom: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.5rem;">
                             <?php if ($stage['status'] === 'Completed'): ?>
                                <i class="fas fa-check-circle" style="color: var(--accent-dark);"></i>
                            <?php else: ?>
                                <i class="far fa-circle" style="color: var(--text-light);"></i>
                            <?php endif; ?>
                            <h4 style="margin: 0; font-size: 1rem; color: <?php echo $stage['status'] === 'Completed' ? 'var(--primary-dark)' : 'var(--text-color)'; ?>;"><?php echo htmlspecialchars($stage['name']); ?></h4>
                        </div>
                        
                        <div style="padding-left: 2rem; color: var(--text-light); font-style: italic; font-size: 0.95rem;">
                            <?php if ($stage['status'] === 'Completed'): ?>
                                Data entered on <?php echo date('M d, Y'); // Placeholder date ?>
                            <?php else: ?>
                                No data entered yet
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else: ?>
        <div class="project-detail-card" style="text-align: center; padding: 4rem;">
            <h2>Project not found</h2>
            <a href="dashboard.php" class="btn btn-primary" style="margin-top: 1rem;">Return to Dashboard</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
