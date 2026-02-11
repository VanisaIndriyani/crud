<?php
require_once 'includes/db.php';
include 'includes/header.php';

$project_id = $_GET['project_id'] ?? 0;
$project = null;
$risks = [];
$message = '';
$error = '';

if ($project_id) {
    try {
        // Fetch Project
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute(['id' => $project_id]);
        $project = $stmt->fetch();

        if ($project) {
            // Fetch Risk Assessments
            $stmtRisks = $pdo->prepare("SELECT * FROM risk_assessments WHERE project_id = :id ORDER BY created_at ASC");
            $stmtRisks->execute(['id' => $project_id]);
            $risks = $stmtRisks->fetchAll();
        }
    } catch (PDOException $e) {
        $error = "Error fetching data: " . $e->getMessage();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $project) {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_risk') {
        $process_name = $_POST['process_name'] ?? '';
        $failure_mode = $_POST['failure_mode'] ?? '';
        $effect_of_failure = $_POST['effect_of_failure'] ?? '';
        $cause_of_failure = $_POST['cause_of_failure'] ?? '';
        $severity = (int)($_POST['severity'] ?? 1);
        $occurrence = (int)($_POST['occurrence'] ?? 1);
        $detection = (int)($_POST['detection'] ?? 1);
        $corrective_actions = $_POST['corrective_actions'] ?? '';

        $rpn = $severity * $occurrence * $detection;

        try {
            $stmt = $pdo->prepare("INSERT INTO risk_assessments (project_id, process_name, failure_mode, effect_of_failure, cause_of_failure, severity, occurrence, detection, rpn, corrective_actions) VALUES (:pid, :pn, :fm, :ef, :cf, :sev, :occ, :det, :rpn, :ca)");
            $stmt->execute([
                'pid' => $project_id,
                'pn' => $process_name,
                'fm' => $failure_mode,
                'ef' => $effect_of_failure,
                'cf' => $cause_of_failure,
                'sev' => $severity,
                'occ' => $occurrence,
                'det' => $detection,
                'rpn' => $rpn,
                'ca' => $corrective_actions
            ]);

            // Recalculate Project Risk Level
            updateProjectRiskLevel($pdo, $project_id);

            echo "<script>window.location.href = 'risk_assessment.php?project_id=$project_id&msg=added';</script>";
            exit;

        } catch (PDOException $e) {
            $error = "Error adding risk: " . $e->getMessage();
        }
    } elseif ($action === 'delete_risk') {
        $risk_id = $_POST['risk_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM risk_assessments WHERE id = :id");
            $stmt->execute(['id' => $risk_id]);
            
            updateProjectRiskLevel($pdo, $project_id);
            
            echo "<script>window.location.href = 'risk_assessment.php?project_id=$project_id&msg=deleted';</script>";
            exit;
        } catch (PDOException $e) {
            $error = "Error deleting risk: " . $e->getMessage();
        }
    }
}

// Function to update project risk level
function updateProjectRiskLevel($pdo, $projectId) {
    // Get Max RPN
    $stmt = $pdo->prepare("SELECT MAX(rpn) FROM risk_assessments WHERE project_id = :id");
    $stmt->execute(['id' => $projectId]);
    $maxRPN = (int)$stmt->fetchColumn();

    $riskLevel = 'Non-GxP';
    $revalYears = 0;

    if ($maxRPN >= 50) {
        $riskLevel = 'High';
        $revalYears = 1;
    } elseif ($maxRPN >= 20) {
        $riskLevel = 'Medium';
        $revalYears = 2;
    } elseif ($maxRPN > 0) {
        $riskLevel = 'Low';
        $revalYears = 3;
    }

    $nextRevalDate = null;
    if ($revalYears > 0) {
        $nextRevalDate = date('Y-m-d', strtotime("+$revalYears years"));
    }

    $stmtUpdate = $pdo->prepare("UPDATE projects SET max_rpn = :max_rpn, risk_level = :level, next_revalidation_date = :date WHERE id = :id");
    $stmtUpdate->execute([
        'max_rpn' => $maxRPN,
        'level' => $riskLevel,
        'date' => $nextRevalDate,
        'id' => $projectId
    ]);
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $message = "Risk assessment added successfully.";
    if ($_GET['msg'] === 'deleted') $message = "Risk assessment deleted successfully.";
}
?>

<style>
    /* Custom Styles for Risk Assessment */
    .risk-header-card {
        background: white;
        border-radius: var(--radius);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 2rem;
        align-items: center;
        margin-bottom: 2rem;
    }

    .risk-stat {
        text-align: center;
        padding: 1rem;
        border-radius: var(--radius);
        background: var(--background-color);
    }

    .risk-stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary-dark);
        line-height: 1.2;
    }

    .risk-stat-label {
        font-size: 0.85rem;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .risk-badge-lg {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1rem;
    }

    .risk-high { background-color: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
    .risk-medium { background-color: #fff3e0; color: #ef6c00; border: 1px solid #ffcc80; }
    .risk-low { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    .risk-none { background-color: #f5f5f5; color: #757575; border: 1px solid #e0e0e0; }

    .form-section {
        background: white;
        border-radius: var(--radius);
        padding: 2rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        color: var(--text-color);
    }

    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-dark);
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--background-color);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .rpn-calculator {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 1.5rem;
        border-radius: var(--radius);
        text-align: center;
        margin-top: 1rem;
        border: 1px solid #fff;
        box-shadow: var(--shadow-sm);
    }

    .rpn-display {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-color);
        margin: 0.5rem 0;
    }

    .table-container {
        background: white;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .risk-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .risk-table th {
        background: #f1f5f9;
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        color: var(--primary-dark);
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    .risk-table td {
        padding: 1.25rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        background: white;
    }

    .risk-table tr:last-child td {
        border-bottom: none;
    }

    .risk-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    .score-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        font-weight: 700;
        font-size: 0.9rem;
        transition: transform 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .score-s { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .score-o { background-color: #fff3e0; color: #ef6c00; border: 1px solid #ffe0b2; }
    .score-d { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }

    .rpn-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 800;
        min-width: 50px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>

<div class="container fade-in">
    <a href="dashboard.php" class="back-link" style="margin-bottom: 1.5rem; display: inline-block;">
        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to Dashboard
    </a>

    <?php if ($project): ?>
        <?php 
            $currentRisk = $project['risk_level'] ?? 'Non-GxP';
            $riskClass = match($currentRisk) {
                'High' => 'risk-high',
                'Medium' => 'risk-medium',
                'Low' => 'risk-low',
                default => 'risk-none'
            };
            $riskIcon = match($currentRisk) {
                'High' => 'exclamation-triangle',
                'Medium' => 'exclamation-circle',
                'Low' => 'check-circle',
                default => 'info-circle'
            };
        ?>

        <!-- Header Summary Card -->
        <div class="risk-header-card">
            <div>
                <h1 style="font-size: 1.8rem; margin-bottom: 0.5rem; color: var(--primary-dark);">Risk Assessment</h1>
                <p style="margin: 0; color: var(--text-light); font-size: 1.1rem;">
                    <?php echo htmlspecialchars($project['name']); ?>
                    <span style="font-size: 0.9rem; opacity: 0.8; margin-left: 8px;">(<?php echo htmlspecialchars($project['software']); ?>)</span>
                </p>
            </div>
            
            <div class="risk-stat">
                <div class="risk-stat-label">Current Risk Level</div>
                <div style="margin-top: 8px;">
                    <span class="risk-badge-lg <?php echo $riskClass; ?>">
                        <i class="fas fa-<?php echo $riskIcon; ?>"></i> <?php echo $currentRisk; ?>
                    </span>
                </div>
            </div>

            <div class="risk-stat">
                <div class="risk-stat-label">Next Revalidation</div>
                <div class="risk-stat-value" style="font-size: 1.2rem; margin-top: 8px;">
                    <?php echo $project['next_revalidation_date'] ? date('d M Y', strtotime($project['next_revalidation_date'])) : '-'; ?>
                </div>
                <?php if ($project['risk_level'] && $project['risk_level'] !== 'Non-GxP'): ?>
                    <small style="color: var(--text-light);">
                        (<?php echo match($project['risk_level']) { 'High' => '1 Year', 'Medium' => '2 Years', 'Low' => '3 Years', default => '-' }; ?>)
                    </small>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success" style="margin-bottom: 2rem;"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 2rem;"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
            
            <!-- Left Column: Table & Form -->
            <div>
                <!-- Add Risk Form -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="fas fa-plus-circle"></i> Add New Risk Item
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_risk">
                        
                        <div class="form-grid" style="margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label>Process Name <span style="color: var(--danger-color)">*</span></label>
                                <input type="text" name="process_name" required placeholder="e.g., User Input Form" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label>Failure Mode <span style="color: var(--danger-color)">*</span></label>
                                <input type="text" name="failure_mode" required placeholder="e.g., Validation fails" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px;">
                            </div>
                        </div>

                        <div class="form-grid" style="margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label>Potential Effect</label>
                                <input type="text" name="effect_of_failure" placeholder="e.g., Data corruption" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px;">
                            </div>
                            <div class="form-group">
                                <label>Potential Cause</label>
                                <input type="text" name="cause_of_failure" placeholder="e.g., Logic error" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px;">
                            </div>
                        </div>

                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; color: var(--text-color);">
                            <label style="font-weight: 700; margin-bottom: 1rem; display: block; color: var(--primary-dark);">Risk Scoring (1-10)</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                                <div>
                                    <label style="font-size: 0.85rem; color: var(--text-light); font-weight: 600;">Severity (S)</label>
                                    <select name="severity" id="severity" onchange="calculateRPN()" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ddd;">
                                        <?php for($i=1; $i<=10; $i++) echo "<option value='$i'>$i</option>"; ?>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.85rem; color: var(--text-light); font-weight: 600;">Occurrence (O)</label>
                                    <select name="occurrence" id="occurrence" onchange="calculateRPN()" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ddd;">
                                        <?php for($i=1; $i<=10; $i++) echo "<option value='$i'>$i</option>"; ?>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.85rem; color: var(--text-light); font-weight: 600;">Detection (D)</label>
                                    <select name="detection" id="detection" onchange="calculateRPN()" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ddd;">
                                        <?php for($i=1; $i<=10; $i++) echo "<option value='$i'>$i</option>"; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="rpn-calculator">
                                <div style="font-size: 0.9rem; color: var(--text-light);">Calculated RPN Score</div>
                                <div id="rpn_preview" class="rpn-display">1</div>
                                <div id="rpn_label" style="font-weight: 600; font-size: 0.9rem; color: var(--success-color);">Low Risk</div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label>Corrective / Preventive Actions</label>
                            <textarea name="corrective_actions" rows="3" placeholder="Describe mitigation steps..." style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-plus"></i> Add Risk Item
                        </button>
                    </form>
                </div>

                <!-- Risks Table -->
                <div class="table-container">
                    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; font-size: 1.2rem;">FMEA Risk Register</h3>
                        <span class="badge badge-secondary"><?php echo count($risks); ?> Items</span>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="risk-table">
                            <thead>
                                <tr>
                                    <th>Process / Failure Mode</th>
                                    <th style="text-align: center; width: 60px;">S</th>
                                    <th style="text-align: center; width: 60px;">O</th>
                                    <th style="text-align: center; width: 60px;">D</th>
                                    <th style="text-align: center; width: 80px;">RPN</th>
                                    <th>Actions</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($risks)): ?>
                                    <tr>
                                        <td colspan="7" style="padding: 3rem; text-align: center; color: var(--text-light);">
                                            <i class="fas fa-clipboard-check" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                            <p>No risk assessments recorded yet.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($risks as $risk): ?>
                                        <?php 
                                            $rpnClass = '';
                                            $rpnLabel = 'Low';
                                            if ($risk['rpn'] >= 50) { $rpnClass = 'background-color: #ffebee; color: #c62828;'; $rpnLabel = 'High'; }
                                            elseif ($risk['rpn'] >= 20) { $rpnClass = 'background-color: #fff3e0; color: #ef6c00;'; $rpnLabel = 'Medium'; }
                                            else { $rpnClass = 'background-color: #e8f5e9; color: #2e7d32;'; }
                                        ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 700; color: var(--primary-dark); font-size: 0.95rem;"><?php echo htmlspecialchars($risk['process_name']); ?></div>
                                                <div style="font-size: 0.85rem; color: #64748b; margin-top: 4px; font-weight: 500;">
                                                    <i class="fas fa-exclamation-circle" style="font-size: 0.75rem; color: #94a3b8; margin-right: 4px;"></i>
                                                    <?php echo htmlspecialchars($risk['failure_mode']); ?>
                                                </div>
                                                <?php if($risk['cause_of_failure']): ?>
                                                    <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px; font-style: italic;">
                                                        <i class="fas fa-search" style="font-size: 0.7rem; margin-right: 4px;"></i> <?php echo htmlspecialchars($risk['cause_of_failure']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center;"><span class="score-badge score-s" title="Severity"><?php echo $risk['severity']; ?></span></td>
                                            <td style="text-align: center;"><span class="score-badge score-o" title="Occurrence"><?php echo $risk['occurrence']; ?></span></td>
                                            <td style="text-align: center;"><span class="score-badge score-d" title="Detection"><?php echo $risk['detection']; ?></span></td>
                                            <td style="text-align: center;">
                                                <div class="rpn-badge" style="<?php echo $rpnClass; ?>">
                                                    <?php echo $risk['rpn']; ?>
                                                </div>
                                            </td>
                                            <td style="font-size: 0.9rem; color: #555;">
                                                <?php echo $risk['corrective_actions'] ? nl2br(htmlspecialchars($risk['corrective_actions'])) : '<span style="color:#cbd5e1; font-style:italic;">No actions defined</span>'; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this risk item?');">
                                                    <input type="hidden" name="action" value="delete_risk">
                                                    <input type="hidden" name="risk_id" value="<?php echo $risk['id']; ?>">
                                                    <button type="submit" style="background: none; border: none; color: #ff5252; cursor: pointer; padding: 5px; opacity: 0.7; transition: opacity 0.2s;">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Guidelines -->
            <div>
                <div style="background: white; padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow-sm); position: sticky; top: 100px;">
                    <h3 style="font-size: 1.1rem; color: var(--primary-dark); margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">
                        <i class="fas fa-book-open"></i> Risk Guidelines
                    </h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">RPN Categories</h4>
                        
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 8px; border-radius: 6px; background: #ffebee;">
                            <div style="width: 10px; height: 10px; background: #c62828; border-radius: 50%;"></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #c62828;">High (≥ 50)</div>
                                <div style="font-size: 0.8rem; color: #c62828;">Revalidation: 1 Year</div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 8px; border-radius: 6px; background: #fff3e0;">
                            <div style="width: 10px; height: 10px; background: #ef6c00; border-radius: 50%;"></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #ef6c00;">Medium (20-49)</div>
                                <div style="font-size: 0.8rem; color: #ef6c00;">Revalidation: 2 Years</div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 8px; border-radius: 6px; background: #e8f5e9;">
                            <div style="width: 10px; height: 10px; background: #2e7d32; border-radius: 50%;"></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; font-size: 0.9rem; color: #2e7d32;">Low (< 20)</div>
                                <div style="font-size: 0.8rem; color: #2e7d32;">Revalidation: 3 Years</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Formula</h4>
                        <div style="background: #f8f9fa; padding: 10px; border-radius: 6px; text-align: center; font-family: monospace; font-weight: 600; color: var(--primary-color);">
                            RPN = S × O × D
                        </div>
                        <ul style="font-size: 0.8rem; color: var(--text-light); margin-top: 10px; padding-left: 1.2rem;">
                            <li><strong>S</strong>everity (Keparahan)</li>
                            <li><strong>O</strong>ccurrence (Frekuensi)</li>
                            <li><strong>D</strong>etection (Kemudahan Deteksi)</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    <?php else: ?>
        <p>Project not found.</p>
    <?php endif; ?>
</div>

<script>
function calculateRPN() {
    const s = parseInt(document.getElementById('severity').value) || 0;
    const o = parseInt(document.getElementById('occurrence').value) || 0;
    const d = parseInt(document.getElementById('detection').value) || 0;
    const rpn = s * o * d;
    
    const preview = document.getElementById('rpn_preview');
    const label = document.getElementById('rpn_label');
    
    preview.innerText = rpn;
    
    if (rpn >= 50) {
        preview.style.color = '#c62828';
        label.innerText = 'High Risk - Reval 1 Year';
        label.style.color = '#c62828';
    } else if (rpn >= 20) {
        preview.style.color = '#ef6c00';
        label.innerText = 'Medium Risk - Reval 2 Years';
        label.style.color = '#ef6c00';
    } else {
        preview.style.color = '#2e7d32';
        label.innerText = 'Low Risk - Reval 3 Years';
        label.style.color = '#2e7d32';
    }
}

// Init on load
document.addEventListener('DOMContentLoaded', calculateRPN);
</script>

<?php include 'includes/footer.php'; ?>