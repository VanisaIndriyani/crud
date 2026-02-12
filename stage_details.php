<?php 
require_once 'includes/db.php';
include 'includes/header.php'; 

$stage_id = $_GET['id'] ?? 0;
$stage = null;
$documents = [];
$urs_details = null;
$iq_details = null;
$oq_details = null;
$pq_details = null;
$validation_report_details = null;
$message = '';

if ($stage_id) {
    // Fetch Stage & Project info
    $stmt = $pdo->prepare("
        SELECT ps.*, p.name as project_name, p.id as project_id 
        FROM project_stages ps 
        JOIN projects p ON ps.project_id = p.id 
        WHERE ps.id = :id
    ");
    $stmt->execute(['id' => $stage_id]);
    $stage = $stmt->fetch();

    if ($stage) {
        // Fetch Documents
        $stmtDocs = $pdo->prepare("SELECT * FROM stage_documents WHERE stage_id = :id ORDER BY uploaded_at DESC");
        $stmtDocs->execute(['id' => $stage_id]);
        $documents = $stmtDocs->fetchAll();

        // Fetch Users for Approval
        $stmtUsers = $pdo->query("SELECT id, username FROM users ORDER BY username ASC");
        $all_users = $stmtUsers->fetchAll();

        // Fetch URS Details if stage is "User Request Specification"
        if ($stage['name'] === 'User Request Specification') {
            $stmtURS = $pdo->prepare("SELECT * FROM stage_urs_details WHERE stage_id = :id");
            $stmtURS->execute(['id' => $stage_id]);
            $urs_details = $stmtURS->fetch();
        } elseif ($stage['name'] === 'IQ - Installation Qualification') {
            $stmtIQ = $pdo->prepare("SELECT * FROM stage_iq_details WHERE stage_id = :id");
            $stmtIQ->execute(['id' => $stage_id]);
            $iq_details = $stmtIQ->fetch();
        } elseif ($stage['name'] === 'OQ - Operational Qualification') {
            $stmtOQ = $pdo->prepare("SELECT * FROM stage_oq_details WHERE stage_id = :id");
            $stmtOQ->execute(['id' => $stage_id]);
            $oq_details = $stmtOQ->fetch();
        } elseif ($stage['name'] === 'PQ - Performance Qualification') {
            $stmtPQ = $pdo->prepare("SELECT * FROM stage_pq_details WHERE stage_id = :id");
            $stmtPQ->execute(['id' => $stage_id]);
            $pq_details = $stmtPQ->fetch();
        } elseif ($stage['name'] === 'Laporan Validasi') {
            $stmtVal = $pdo->prepare("SELECT * FROM stage_validation_report_details WHERE stage_id = :id");
            $stmtVal->execute(['id' => $stage_id]);
            $validation_report_details = $stmtVal->fetch();
        }
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $stage) {
    try {
        if (isset($_POST['delete_document_id'])) {
            // Handle Document Deletion
            $docId = $_POST['delete_document_id'];
            
            // Verify document belongs to this stage
            $stmtCheck = $pdo->prepare("SELECT file_path FROM stage_documents WHERE id = :id AND stage_id = :stage_id");
            $stmtCheck->execute(['id' => $docId, 'stage_id' => $stage_id]);
            $docToDelete = $stmtCheck->fetch();

            if ($docToDelete) {
                // Delete file from server
                if (file_exists($docToDelete['file_path'])) {
                    unlink($docToDelete['file_path']);
                }
                
                // Delete record from database
                $stmtDel = $pdo->prepare("DELETE FROM stage_documents WHERE id = :id");
                $stmtDel->execute(['id' => $docId]);
                
                // Refresh documents list
                $stmtDocs->execute(['id' => $stage_id]);
                $documents = $stmtDocs->fetchAll();
                
                $message = "Document deleted successfully.";
            } else {
                $error = "Document not found or invalid.";
            }
        } else {
            $action = $_POST['action'] ?? '';
        
            // Handle URS Details Save
    if ($stage['name'] === 'User Request Specification') {
        $requestor_name = $_POST['requestor_name'] ?? '';
        $requestor_department = $_POST['requestor_department'] ?? '';
        $request_date = $_POST['request_date'] ?? null;
        $software_purpose = $_POST['software_purpose'] ?? '';
        $functional_requirements = $_POST['functional_requirements'] ?? '';
        $technical_requirements = $_POST['technical_requirements'] ?? '';
        $user_requirements = $_POST['user_requirements'] ?? '';
        $acceptance_criteria = $_POST['acceptance_criteria'] ?? '';

        if ($urs_details) {
            $stmt = $pdo->prepare("UPDATE stage_urs_details SET 
                requestor_name = :r_name, 
                requestor_department = :r_dept, 
                request_date = :r_date, 
                software_purpose = :purpose, 
                functional_requirements = :func_req, 
                technical_requirements = :tech_req, 
                user_requirements = :user_req, 
                acceptance_criteria = :criteria 
                WHERE stage_id = :id");
        } else {
            $stmt = $pdo->prepare("INSERT INTO stage_urs_details 
                (stage_id, requestor_name, requestor_department, request_date, software_purpose, functional_requirements, technical_requirements, user_requirements, acceptance_criteria) 
                VALUES (:id, :r_name, :r_dept, :r_date, :purpose, :func_req, :tech_req, :user_req, :criteria)");
        }
        
        $stmt->execute([
            'id' => $stage_id,
            'r_name' => $requestor_name,
            'r_dept' => $requestor_department,
            'r_date' => $request_date ?: null,
            'purpose' => $software_purpose,
            'func_req' => $functional_requirements,
            'tech_req' => $technical_requirements,
            'user_req' => $user_requirements,
            'criteria' => $acceptance_criteria
        ]);

        // Refresh URS details
        $stmtURS = $pdo->prepare("SELECT * FROM stage_urs_details WHERE stage_id = :id");
        $stmtURS->execute(['id' => $stage_id]);
        $urs_details = $stmtURS->fetch();
    } elseif ($stage['name'] === 'IQ - Installation Qualification') {
        $installation_date = $_POST['installation_date'] ?? null;
        $hardware_verification = $_POST['hardware_verification'] ?? '';
        $software_verification = $_POST['software_verification'] ?? '';
        $documentation = $_POST['documentation'] ?? '';
        $iq_result = $_POST['iq_result'] ?? '';

        if ($iq_details) {
            $stmt = $pdo->prepare("UPDATE stage_iq_details SET 
                installation_date = :inst_date, 
                hardware_verification = :hw_ver, 
                software_verification = :sw_ver, 
                documentation = :doc, 
                iq_result = :result 
                WHERE stage_id = :id");
        } else {
            $stmt = $pdo->prepare("INSERT INTO stage_iq_details 
                (stage_id, installation_date, hardware_verification, software_verification, documentation, iq_result) 
                VALUES (:id, :inst_date, :hw_ver, :sw_ver, :doc, :result)");
        }
        
        $stmt->execute([
            'id' => $stage_id,
            'inst_date' => $installation_date ?: null,
            'hw_ver' => $hardware_verification,
            'sw_ver' => $software_verification,
            'doc' => $documentation,
            'result' => $iq_result
        ]);

        // Refresh IQ details
        $stmtIQ = $pdo->prepare("SELECT * FROM stage_iq_details WHERE stage_id = :id");
        $stmtIQ->execute(['id' => $stage_id]);
        $iq_details = $stmtIQ->fetch();
    } elseif ($stage['name'] === 'OQ - Operational Qualification') {
        $test_date = $_POST['test_date'] ?? null;
        $main_function_test = $_POST['main_function_test'] ?? '';
        $interface_test = $_POST['interface_test'] ?? '';
        $security_test = $_POST['security_test'] ?? '';
        $oq_result = $_POST['oq_result'] ?? '';

        if ($oq_details) {
            $stmt = $pdo->prepare("UPDATE stage_oq_details SET 
                test_date = :test_date, 
                main_function_test = :main_func, 
                interface_test = :interface, 
                security_test = :security, 
                oq_result = :result 
                WHERE stage_id = :id");
        } else {
            $stmt = $pdo->prepare("INSERT INTO stage_oq_details 
                (stage_id, test_date, main_function_test, interface_test, security_test, oq_result) 
                VALUES (:id, :test_date, :main_func, :interface, :security, :result)");
        }
        
        $stmt->execute([
            'id' => $stage_id,
            'test_date' => $test_date ?: null,
            'main_func' => $main_function_test,
            'interface' => $interface_test,
            'security' => $security_test,
            'result' => $oq_result
        ]);

        // Refresh OQ details
        $stmtOQ = $pdo->prepare("SELECT * FROM stage_oq_details WHERE stage_id = :id");
        $stmtOQ->execute(['id' => $stage_id]);
        $oq_details = $stmtOQ->fetch();
    } elseif ($stage['name'] === 'PQ - Performance Qualification') {
        $test_date = $_POST['test_date'] ?? null;
        $test_scenario = $_POST['test_scenario'] ?? '';
        $test_data = $_POST['test_data'] ?? '';
        $performance_result = $_POST['performance_result'] ?? '';
        $pq_conclusion = $_POST['pq_conclusion'] ?? '';

        if ($pq_details) {
            $stmt = $pdo->prepare("UPDATE stage_pq_details SET 
                test_date = :test_date, 
                test_scenario = :scenario, 
                test_data = :data, 
                performance_result = :perf_result, 
                pq_conclusion = :conclusion 
                WHERE stage_id = :id");
        } else {
            $stmt = $pdo->prepare("INSERT INTO stage_pq_details 
                (stage_id, test_date, test_scenario, test_data, performance_result, pq_conclusion) 
                VALUES (:id, :test_date, :scenario, :data, :perf_result, :conclusion)");
        }
        
        $stmt->execute([
            'id' => $stage_id,
            'test_date' => $test_date ?: null,
            'scenario' => $test_scenario,
            'data' => $test_data,
            'perf_result' => $performance_result,
            'conclusion' => $pq_conclusion
        ]);

        // Refresh PQ details
        $stmtPQ = $pdo->prepare("SELECT * FROM stage_pq_details WHERE stage_id = :id");
        $stmtPQ->execute(['id' => $stage_id]);
        $pq_details = $stmtPQ->fetch();
    } elseif ($stage['name'] === 'Laporan Validasi') {
        $executive_summary = $_POST['executive_summary'] ?? '';
        $overall_result = $_POST['overall_result'] ?? '';
        $deviation = $_POST['deviation'] ?? '';
        $recommendation = $_POST['recommendation'] ?? '';
        
        $prepared_by = !empty($_POST['prepared_by']) ? $_POST['prepared_by'] : null;
        $reviewed_by = !empty($_POST['reviewed_by']) ? $_POST['reviewed_by'] : null;
        $approved_by = !empty($_POST['approved_by']) ? $_POST['approved_by'] : null;
        
        // Logic for dates: Update date only if ID is provided.
        // Use existing date if available, otherwise use NOW()
        $existing_prep_date = $validation_report_details['prepared_date'] ?? null;
        $prepared_date = $prepared_by ? ($existing_prep_date ?: date('Y-m-d H:i:s')) : null;

        $existing_rev_date = $validation_report_details['reviewed_date'] ?? null;
        $reviewed_date = $reviewed_by ? ($existing_rev_date ?: date('Y-m-d H:i:s')) : null;

        $existing_appr_date = $validation_report_details['approved_date'] ?? null;
        $approved_date = $approved_by ? ($existing_appr_date ?: date('Y-m-d H:i:s')) : null;

        if ($validation_report_details) {
            $stmt = $pdo->prepare("UPDATE stage_validation_report_details SET 
                executive_summary = :summary, 
                overall_result = :result, 
                deviation = :deviation, 
                recommendation = :recommendation, 
                prepared_by = :prep,
                prepared_date = :prep_date,
                reviewed_by = :rev,
                reviewed_date = :rev_date,
                approved_by = :appr,
                approved_date = :appr_date
                WHERE stage_id = :id");
        } else {
            $stmt = $pdo->prepare("INSERT INTO stage_validation_report_details 
                (stage_id, executive_summary, overall_result, deviation, recommendation, prepared_by, prepared_date, reviewed_by, reviewed_date, approved_by, approved_date) 
                VALUES (:id, :summary, :result, :deviation, :recommendation, :prep, :prep_date, :rev, :rev_date, :appr, :appr_date)");
        }
        
        $stmt->execute([
            'id' => $stage_id,
            'summary' => $executive_summary,
            'result' => $overall_result,
            'deviation' => $deviation,
            'recommendation' => $recommendation,
            'prep' => $prepared_by,
            'prep_date' => $prepared_date,
            'rev' => $reviewed_by,
            'rev_date' => $reviewed_date,
            'appr' => $approved_by,
            'appr_date' => $approved_date
        ]);

        // Refresh Validation Report details
        $stmtVal = $pdo->prepare("SELECT * FROM stage_validation_report_details WHERE stage_id = :id");
        $stmtVal->execute(['id' => $stage_id]);
        $validation_report_details = $stmtVal->fetch();
    } else {
        // Handle Generic Stage Date Update
        $completion_date = $_POST['completion_date'] ?? null;
        if ($completion_date) {
            $stmt = $pdo->prepare("UPDATE project_stages SET completion_date = :date WHERE id = :id");
            $stmt->execute(['date' => $completion_date, 'id' => $stage_id]);
            $stage['completion_date'] = $completion_date;
        }
    }

    // Handle File Upload
    if (isset($_FILES['stage_document'])) {
        if ($_FILES['stage_document']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileName = basename($_FILES['stage_document']['name']);
            $targetPath = $uploadDir . time() . '_' . $fileName;
            
            if (move_uploaded_file($_FILES['stage_document']['tmp_name'], $targetPath)) {
                $stmt = $pdo->prepare("INSERT INTO stage_documents (stage_id, file_name, file_path) VALUES (:stage_id, :file_name, :file_path)");
                $stmt->execute([
                    'stage_id' => $stage_id,
                    'file_name' => $fileName,
                    'file_path' => $targetPath
                ]);
                // Refresh documents
                $stmtDocs->execute(['id' => $stage_id]);
                $documents = $stmtDocs->fetchAll();
                $message = "Document uploaded successfully.";
            } else {
                $error = "Failed to upload document (Move failed). Check directory permissions.";
            }
        } elseif ($_FILES['stage_document']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            ];
            $errorCode = $_FILES['stage_document']['error'];
            $error = "Upload Error: " . ($uploadErrors[$errorCode] ?? 'Unknown error code: ' . $errorCode);
        }
    }

    // Handle Status Actions
    if ($action === 'save_draft') {
        $newStatus = ($stage['status'] === 'Not Started') ? 'In Progress' : $stage['status'];
        
        $stmt = $pdo->prepare("UPDATE project_stages SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $stage_id]);
        $stage['status'] = $newStatus;
        if (empty($message)) $message = "Draft saved successfully.";

        // Update Parent Project Status
        updateProjectStatus($pdo, $stage['project_id']);
    } elseif ($action === 'mark_completed') {
        $stmt = $pdo->prepare("UPDATE project_stages SET status = 'Completed', completion_date = CURRENT_DATE() WHERE id = :id");
        $stmt->execute(['id' => $stage_id]);
        $stage['status'] = 'Completed';
        $message = "Stage marked as completed.";

        // Update Parent Project Status
        updateProjectStatus($pdo, $stage['project_id']);
    }
    }
    } catch (Exception $e) {
        $error = "System Error: " . $e->getMessage();
    }
}

// Helper function to update project status based on stages
function updateProjectStatus($pdo, $projectId) {
    $stmt = $pdo->prepare("SELECT status FROM project_stages WHERE project_id = :id");
    $stmt->execute(['id' => $projectId]);
    $stages = $stmt->fetchAll(PDO::FETCH_COLUMN);

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

    $stmtUpdate = $pdo->prepare("UPDATE projects SET status = :status WHERE id = :id");
    $stmtUpdate->execute(['status' => $newStatus, 'id' => $projectId]);
}
?>

<?php if ($stage): ?>
    <div class="container fade-in">
        <a href="project_details.php?id=<?php echo $stage['project_id']; ?>" class="back-link">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to Stages
        </a>

        <div class="project-detail-card">
            
            <div class="project-detail-header">
                <div>
                    <h1 class="stage-title"><?php echo htmlspecialchars($stage['name']); ?></h1>
                    <div class="badge badge-<?php 
                        echo match($stage['status']) {
                            'Completed' => 'success',
                            'In Progress' => 'warning',
                            default => 'secondary'
                        };
                    ?>">
                        <?php echo $stage['status']; ?>
                    </div>
                </div>
                
                <?php 
                $is_approved = false;
                if ($stage['name'] === 'Laporan Validasi' && $validation_report_details) {
                    $is_approved = !empty($validation_report_details['prepared_by']) && 
                                   !empty($validation_report_details['reviewed_by']) && 
                                   !empty($validation_report_details['approved_by']);
                } else {
                    // For other stages, default to true or implement their own logic
                    $is_approved = true; 
                }
                ?>
                
                <?php if ($is_approved): ?>
                <a href="export_stage_excel.php?id=<?php echo $stage['id']; ?>" class="btn" style="background-color: var(--success-color); color: white; box-shadow: 0 4px 6px rgba(46, 125, 50, 0.2);">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <?php else: ?>
                <button type="button" class="btn" disabled style="background-color: #ccc; cursor: not-allowed; opacity: 0.7;" title="Complete all approvals to enable export">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <?php endif; ?>
            </div>

            <!-- Status Notifications are now handled by Modal -->
            <?php /* 
            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            */ ?>

            <form method="POST" enctype="multipart/form-data">
                
                <?php if ($stage['name'] === 'User Request Specification'): ?>
                    <!-- URS Specific Form -->
                    <div class="form-group">
                        <label>Requestor Name <span style="color: var(--danger-color)">*</span></label>
                        <input type="text" name="requestor_name" value="<?php echo htmlspecialchars($urs_details['requestor_name'] ?? ''); ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Requestor Department <span style="color: var(--danger-color)">*</span></label>
                        <input type="text" name="requestor_department" value="<?php echo htmlspecialchars($urs_details['requestor_department'] ?? ''); ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Request Date <span style="color: var(--danger-color)">*</span></label>
                        <input type="date" name="request_date" value="<?php echo htmlspecialchars($urs_details['request_date'] ?? ''); ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Software Purpose <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="software_purpose" rows="4" required class="form-control"><?php echo htmlspecialchars($urs_details['software_purpose'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Functional Requirements <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="functional_requirements" rows="4" required class="form-control"><?php echo htmlspecialchars($urs_details['functional_requirements'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Technical Requirements <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="technical_requirements" rows="4" required class="form-control"><?php echo htmlspecialchars($urs_details['technical_requirements'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>User Requirements <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="user_requirements" rows="4" required class="form-control"><?php echo htmlspecialchars($urs_details['user_requirements'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Acceptance Criteria <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="acceptance_criteria" rows="4" required class="form-control"><?php echo htmlspecialchars($urs_details['acceptance_criteria'] ?? ''); ?></textarea>
                    </div>

                <?php elseif ($stage['name'] === 'IQ - Installation Qualification'): ?>
                    <!-- IQ Specific Form -->
                    <div class="form-group">
                        <label>Tanggal Instalasi <span style="color: var(--danger-color)">*</span></label>
                        <input type="date" name="installation_date" value="<?php echo htmlspecialchars($iq_details['installation_date'] ?? ''); ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Verifikasi Hardware <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="hardware_verification" rows="4" required class="form-control"><?php echo htmlspecialchars($iq_details['hardware_verification'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Verifikasi Software <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="software_verification" rows="4" required class="form-control"><?php echo htmlspecialchars($iq_details['software_verification'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Dokumentasi <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="documentation" rows="4" required class="form-control"><?php echo htmlspecialchars($iq_details['documentation'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Hasil IQ <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="iq_result" rows="4" required class="form-control"><?php echo htmlspecialchars($iq_details['iq_result'] ?? ''); ?></textarea>
                    </div>

                <?php elseif ($stage['name'] === 'OQ - Operational Qualification'): ?>
                    <!-- OQ Specific Form -->
                    <div class="form-group">
                        <label>Tanggal Pengujian <span style="color: var(--danger-color)">*</span></label>
                        <input type="date" name="test_date" value="<?php echo htmlspecialchars($oq_details['test_date'] ?? ''); ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Pengujian Fungsi Utama <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="main_function_test" rows="4" required class="form-control"><?php echo htmlspecialchars($oq_details['main_function_test'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Pengujian Interface <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="interface_test" rows="4" required class="form-control"><?php echo htmlspecialchars($oq_details['interface_test'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Pengujian Keamanan <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="security_test" rows="4" required class="form-control"><?php echo htmlspecialchars($oq_details['security_test'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Hasil OQ <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="oq_result" rows="4" required class="form-control"><?php echo htmlspecialchars($oq_details['oq_result'] ?? ''); ?></textarea>
                    </div>

                <?php elseif ($stage['name'] === 'PQ - Performance Qualification'): ?>
                    <!-- PQ Specific Form -->
                    <div class="form-group">
                        <label>Tanggal Pengujian <span style="color: var(--danger-color)">*</span></label>
                        <input type="date" name="test_date" value="<?php echo htmlspecialchars($pq_details['test_date'] ?? ''); ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Skenario Pengujian <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="test_scenario" rows="4" required class="form-control"><?php echo htmlspecialchars($pq_details['test_scenario'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Data Pengujian <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="test_data" rows="4" required class="form-control"><?php echo htmlspecialchars($pq_details['test_data'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Hasil Kinerja <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="performance_result" rows="4" required class="form-control"><?php echo htmlspecialchars($pq_details['performance_result'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Kesimpulan PQ <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="pq_conclusion" rows="4" required class="form-control"><?php echo htmlspecialchars($pq_details['pq_conclusion'] ?? ''); ?></textarea>
                    </div>

                <?php elseif ($stage['name'] === 'Laporan Validasi'): ?>
                    <!-- Validation Report Specific Form -->
                    <div class="form-group">
                        <label>Ringkasan Eksekutif <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="executive_summary" rows="4" required class="form-control"><?php echo htmlspecialchars($validation_report_details['executive_summary'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Hasil Keseluruhan <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="overall_result" rows="4" required class="form-control"><?php echo htmlspecialchars($validation_report_details['overall_result'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Deviasi (jika ada)</label>
                        <textarea name="deviation" rows="4" class="form-control"><?php echo htmlspecialchars($validation_report_details['deviation'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Rekomendasi <span style="color: var(--danger-color)">*</span></label>
                        <textarea name="recommendation" rows="4" required class="form-control"><?php echo htmlspecialchars($validation_report_details['recommendation'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Approval Status <span style="color: var(--danger-color)">*</span></label>
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
                            
                            <!-- Display Current Status -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                                <div>
                                    <small style="color: var(--text-light); font-weight: 600;">Prepared By</small>
                                    <div style="font-weight: 500;">
                                        <?php 
                                        $prep_user = array_filter($all_users, fn($u) => $u['id'] == ($validation_report_details['prepared_by'] ?? 0));
                                        echo !empty($prep_user) ? htmlspecialchars(reset($prep_user)['username']) : '-';
                                        ?>
                                    </div>
                                </div>
                                <div>
                                    <small style="color: var(--text-light); font-weight: 600;">Acknowledged By</small>
                                    <div style="font-weight: 500;">
                                        <?php 
                                        $rev_user = array_filter($all_users, fn($u) => $u['id'] == ($validation_report_details['reviewed_by'] ?? 0));
                                        echo !empty($rev_user) ? htmlspecialchars(reset($rev_user)['username']) : '-';
                                        ?>
                                    </div>
                                </div>
                                <div>
                                    <small style="color: var(--text-light); font-weight: 600;">Approved By</small>
                                    <div style="font-weight: 500;">
                                        <?php 
                                        $appr_user = array_filter($all_users, fn($u) => $u['id'] == ($validation_report_details['approved_by'] ?? 0));
                                        echo !empty($appr_user) ? htmlspecialchars(reset($appr_user)['username']) : '-';
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('approval-form-section').style.display = document.getElementById('approval-form-section').style.display === 'none' ? 'block' : 'none'">
                                <i class="fas fa-edit"></i> Manage Approvals
                            </button>

                            <!-- Hidden Form -->
                            <div id="approval-form-section" style="display: none; margin-top: 1.5rem; border-top: 1px solid #ddd; padding-top: 1rem;">
                                <div class="form-group">
                                    <label>Prepared By (Dibuat Oleh)</label>
                                    <select name="prepared_by" class="form-control">
                                        <option value="">Select User</option>
                                        <?php foreach ($all_users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo ($validation_report_details['prepared_by'] ?? '') == $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Acknowledged By (Mengetahui)</label>
                                    <select name="reviewed_by" class="form-control">
                                        <option value="">Select User</option>
                                        <?php foreach ($all_users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo ($validation_report_details['reviewed_by'] ?? '') == $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Approved By (Menyetujui)</label>
                                    <select name="approved_by" class="form-control">
                                        <option value="">Select User</option>
                                        <?php foreach ($all_users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo ($validation_report_details['approved_by'] ?? '') == $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['username']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Generic Form for Other Stages -->
                    <div class="form-group" style="max-width: 300px;">
                        <label>Completion Date</label>
                        <input type="date" name="completion_date" value="<?php echo $stage['completion_date']; ?>" class="form-control">
                    </div>
                <?php endif; ?>

                <div class="document-upload-section">
                    <h3 class="document-upload-header">
                        <i class="fas fa-upload"></i> Document Uploads
                    </h3>
                    
                    <div class="upload-area">
                         <input type="file" name="stage_document" id="file-upload" style="display: none;" onchange="window.document.getElementById('file-name').textContent = this.files[0].name">
                        <button type="button" onclick="window.document.getElementById('file-upload').click()" class="btn btn-secondary">
                            <i class="fas fa-upload"></i> Choose File
                        </button>
                        <div id="file-name" style="color: var(--text-light); font-size: 0.9rem;"></div>
                    </div>

                    <div class="documents-list">
                        <?php if (empty($documents)): ?>
                            <p style="color: var(--text-light); font-size: 0.95rem; text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 8px;">No documents uploaded yet</p>
                        <?php else: ?>
                            <?php foreach ($documents as $doc): ?>
                                <div class="document-item">
                                    <i class="fas fa-file-alt document-icon"></i>
                                    <div class="document-info" style="flex-grow: 1;">
                                        <div class="document-name"><?php echo htmlspecialchars($doc['file_name']); ?></div>
                                        <div class="document-meta"><?php echo date('M d, Y H:i', strtotime($doc['uploaded_at'])); ?></div>
                                    </div>
                                    <div class="doc-actions" style="display: flex; gap: 10px;">
                                        <a href="download.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-secondary" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="openDeleteModal(<?php echo $doc['id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" name="action" value="save_draft" formnovalidate class="btn btn-save-draft">
                        <i class="far fa-save"></i> Save Draft
                    </button>
                    <button type="submit" name="action" value="mark_completed" class="btn btn-mark-completed">
                        <i class="fas fa-check-circle"></i> Mark as Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="container" style="text-align: center; padding: 4rem;">
        <h2>Stage not found</h2>
        <a href="dashboard.php" class="btn btn-primary" style="margin-top: 1rem;">Return to Dashboard</a>
    </div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(5px);">
    <div class="modal-content" style="background-color: #fff; margin: 15% auto; padding: 2rem; border: 1px solid #888; width: 90%; max-width: 400px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); text-align: center;">
        <div style="width: 60px; height: 60px; background: #ffebee; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <i class="fas fa-trash-alt" style="font-size: 24px; color: #d32f2f;"></i>
        </div>
        <h3 style="color: #333; margin-bottom: 0.5rem;">Delete Document?</h3>
        <p style="color: #666; margin-bottom: 2rem;">Are you sure you want to delete this document? This action cannot be undone.</p>
        
        <form method="POST" id="deleteForm">
            <input type="hidden" name="delete_document_id" id="modal_delete_id">
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()" style="flex: 1;">Cancel</button>
                <button type="submit" class="btn btn-danger" style="flex: 1;">Delete</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Modal -->
<div id="statusModal" class="modal" style="display: none; position: fixed; z-index: 1001; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(5px);">
    <div class="modal-content" style="background-color: #fff; margin: 15% auto; padding: 2rem; border: 1px solid #888; width: 90%; max-width: 400px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); text-align: center;">
        <div style="width: 60px; height: 60px; background: <?php echo !empty($message) ? '#e8f5e9' : '#ffebee'; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <i class="<?php echo !empty($message) ? 'fas fa-check' : 'fas fa-exclamation'; ?>" style="font-size: 24px; color: <?php echo !empty($message) ? '#2e7d32' : '#d32f2f'; ?>;"></i>
        </div>
        <h3 style="color: #333; margin-bottom: 0.5rem;"><?php echo !empty($message) ? 'Success' : 'Error'; ?></h3>
        <p style="color: #666; margin-bottom: 2rem;"><?php echo !empty($message) ? $message : ($error ?? ''); ?></p>
        
        <button type="button" class="btn btn-primary" onclick="closeStatusModal()" style="width: 100%;">OK</button>
    </div>
</div>

<script>
function openDeleteModal(id) {
    document.getElementById('modal_delete_id').value = id;
    document.getElementById('deleteModal').style.display = 'block';
    
    // Animation
    const modalContent = document.querySelector('#deleteModal .modal-content');
    modalContent.style.opacity = '0';
    modalContent.style.transform = 'scale(0.8)';
    modalContent.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        modalContent.style.opacity = '1';
        modalContent.style.transform = 'scale(1)';
    }, 10);
}

function closeDeleteModal() {
    const modalContent = document.querySelector('#deleteModal .modal-content');
    modalContent.style.opacity = '0';
    modalContent.style.transform = 'scale(0.8)';
    
    setTimeout(() => {
        document.getElementById('deleteModal').style.display = 'none';
    }, 300);
}

function closeStatusModal() {
    const modalContent = document.querySelector('#statusModal .modal-content');
    modalContent.style.opacity = '0';
    modalContent.style.transform = 'scale(0.8)';
    
    setTimeout(() => {
        document.getElementById('statusModal').style.display = 'none';
    }, 300);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const deleteModal = document.getElementById('deleteModal');
    const statusModal = document.getElementById('statusModal');
    if (event.target == deleteModal) {
        closeDeleteModal();
    }
    if (event.target == statusModal) {
        closeStatusModal();
    }
}

<?php if (!empty($message) || isset($error)): ?>
document.addEventListener('DOMContentLoaded', function() {
    const statusModal = document.getElementById('statusModal');
    statusModal.style.display = 'block';
    
    // Animation
    const modalContent = document.querySelector('#statusModal .modal-content');
    modalContent.style.opacity = '0';
    modalContent.style.transform = 'scale(0.8)';
    modalContent.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        modalContent.style.opacity = '1';
        modalContent.style.transform = 'scale(1)';
    }, 10);
});
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
