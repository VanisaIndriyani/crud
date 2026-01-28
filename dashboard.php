<?php 
require_once 'includes/db.php';
include 'includes/header.php'; 

// Handle Create Project
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_project') {
        $name = $_POST['name'] ?? '';
        $software = $_POST['software'] ?? '';
        $version = $_POST['version'] ?? '';
        $description = $_POST['description'] ?? '';

        if ($name && $software) {
            try {
                $pdo->beginTransaction();

                // Insert Project
                $stmt = $pdo->prepare("INSERT INTO projects (name, software, version, description, status, created_by) VALUES (:name, :software, :version, :description, 'Draft', :created_by)");
                $stmt->execute([
                    'name' => $name,
                    'software' => $software,
                    'version' => $version,
                    'description' => $description,
                    'created_by' => $_SESSION['user_id']
                ]);
                $projectId = $pdo->lastInsertId();

                // Insert Default Stages
                $stages = [
                    'User Request Specification',
                    'IQ - Installation Qualification',
                    'OQ - Operational Qualification',
                    'PQ - Performance Qualification',
                    'Laporan Validasi'
                ];

                $stmtStage = $pdo->prepare("INSERT INTO project_stages (project_id, name, status) VALUES (:project_id, :name, 'Not Started')");
                foreach ($stages as $stageName) {
                    $stmtStage->execute([
                        'project_id' => $projectId,
                        'name' => $stageName
                    ]);
                }

                $pdo->commit();
                // Refresh to show new project
                echo "<script>window.location.href = 'dashboard.php';</script>";
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Failed to create project: " . $e->getMessage();
            }
        } else {
            $error = "Project Name and Software Name are required.";
        }
    } elseif ($_POST['action'] === 'add_user' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if ($username && $password) {
            try {
                // Check if username exists
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
                $stmt->execute(['username' => $username]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Username already exists.";
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
                    $stmt->execute([
                        'username' => $username,
                        'password' => $hashed_password,
                        'role' => $role
                    ]);
                    
                    echo "<script>window.location.href = 'dashboard.php?msg=user_added';</script>";
                    exit;
                }
            } catch (PDOException $e) {
                $error = "Error creating user: " . $e->getMessage();
            }
        } else {
            $error = "All fields are required.";
        }
    } elseif ($_POST['action'] === 'delete_user' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $userId = $_POST['user_id'] ?? '';
        
        if ($userId) {
            try {
                // Prevent deleting yourself
                if ($userId == $_SESSION['user_id']) {
                    $error = "You cannot delete your own account.";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                    $stmt->execute(['id' => $userId]);
                    echo "<script>window.location.href = 'dashboard.php?msg=user_deleted';</script>";
                    exit;
                }
            } catch (PDOException $e) {
                $error = "Error deleting user: " . $e->getMessage();
            }
        }
    }
}

// Fetch Stats
$stats = [
    'Draft' => 0,
    'In Progress' => 0,
    'Completed' => 0,
    'Total' => 0
];

try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM projects GROUP BY status");
    while ($row = $stmt->fetch()) {
        $stats[$row['status']] = $row['count'];
    }
    $stats['Total'] = array_sum($stats);
} catch (Exception $e) {
    // Handle error silently or log
}

// Fetch Users (if admin)
$users = [];
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    try {
        $stmtUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = $stmtUsers->fetchAll();
    } catch (Exception $e) {
        // Handle error
    }
}

// Fetch Projects with Pagination
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$projects = [];
$totalProjects = 0;
$totalPages = 0;

try {
    // Get total count
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM projects");
    $totalProjects = $stmtCount->fetchColumn();
    $totalPages = ceil($totalProjects / $limit);

    // Get paginated projects
    $stmt = $pdo->prepare("SELECT * FROM projects ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll();
} catch (Exception $e) {
    echo "Error fetching projects: " . $e->getMessage();
}
?>

<div class="fade-in">
    <div class="dashboard-header">
        <div>
            <h2>Validation Projects</h2>
            <p>Manage your computer software validation workflows</p>
            <?php if (isset($error)): ?>
                <p style="color: #ffcccb; margin-top: 10px; font-weight: bold;"><?php echo $error; ?></p>
            <?php endif; ?>
        </div>
        <div style="display: flex; gap: 10px;">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <button onclick="openModal('userManagementModal')" class="btn btn-secondary" style="display: flex; align-items: center; gap: 8px; color: var(--primary-color);">
                <i class="fas fa-users-cog"></i> User Management
            </button>
            <?php endif; ?>
            <button onclick="openModal('newProjectModal')" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Project
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card fade-in-up">
            <div>
                <h3>Registered</h3>
                <div class="value"><?php echo $stats['Draft']; ?></div>
            </div>
            <div class="icon"><i class="fas fa-file-alt"></i></div>
        </div>
        <div class="stat-card fade-in-up">
            <div>
                <h3>In Progress</h3>
                <div class="value"><?php echo $stats['In Progress']; ?></div>
            </div>
            <div class="icon"><i class="fas fa-spinner fa-spin"></i></div>
        </div>
        <div class="stat-card fade-in-up">
            <div>
                <h3>Completed</h3>
                <div class="value"><?php echo $stats['Completed']; ?></div>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card fade-in-up">
            <div>
                <h3>Total Projects</h3>
                <div class="value"><?php echo $stats['Total']; ?></div>
            </div>
            <div class="icon"><i class="fas fa-folder"></i></div>
        </div>
    </div>

    <!-- Projects List -->
    <div class="projects-grid">
        <?php if (empty($projects)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-sm);">
                <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--border-color); margin-bottom: 1rem;"></i>
                <h3>No projects yet</h3>
                <p style="color: var(--text-light);">Create your first validation project to get started.</p>
                <button onclick="openModal('newProjectModal')" class="btn btn-primary" style="margin-top: 1rem;">Create Project</button>
            </div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <div>
                        <div class="project-header">
                            <div class="project-title"><?php echo htmlspecialchars($project['name']); ?></div>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $project['status'])); ?>">
                                <?php echo $project['status']; ?>
                            </span>
                        </div>
                        <div class="project-info">
                            <p style="font-weight: 600; color: var(--primary-dark);"><i class="fas fa-cube" style="margin-right: 5px; opacity: 0.7;"></i> <?php echo htmlspecialchars($project['software']); ?> <span style="font-weight: 400; color: var(--text-light);"><?php echo htmlspecialchars($project['version']); ?></span></p>
                            <?php if ($project['description']): ?>
                                <p style="font-size: 0.85rem; margin-top: 0.5rem; line-height: 1.4;"><?php echo htmlspecialchars(substr($project['description'], 0, 80)) . (strlen($project['description']) > 80 ? '...' : ''); ?></p>
                            <?php endif; ?>
                            <p style="margin-top: 1rem; font-size: 0.75rem; color: var(--text-light);"><i class="far fa-calendar-alt"></i> Created <?php echo date('M d, Y', strtotime($project['created_at'])); ?></p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: auto;">
                        <a href="project_details.php?id=<?php echo $project['id']; ?>" class="btn btn-secondary" style="flex: 1; justify-content: center;">
                            Open Project
                        </a>
                        <a href="project_report.php?id=<?php echo $project['id']; ?>" class="btn btn-secondary" style="padding: 0 1.2rem;" title="View Report">
                            <i class="far fa-file-alt"></i>
                        </a>
                        <a href="#" class="btn btn-danger" style="padding: 0 1.2rem;" title="Delete Project" onclick="confirmDelete(<?php echo $project['id']; ?>); return false;">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="page-link"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="page-link"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- New Project Modal -->
<div id="newProjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Validation Project</h3>
            <span class="close-modal" onclick="closeModal('newProjectModal')">&times;</span>
        </div>
        <p style="color: var(--text-light); margin-bottom: 1.5rem;">Start a new software validation workflow by filling out the details below.</p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="create_project">
            <div class="form-group">
                <label>Project Name <span style="color: var(--danger-color)">*</span></label>
                <input type="text" name="name" placeholder="e.g., ERP System Validation 2024" required>
            </div>
            <div class="form-group">
                <label>Software Name <span style="color: var(--danger-color)">*</span></label>
                <input type="text" name="software" placeholder="e.g., SAP ERP" required>
            </div>
            <div class="form-group">
                <label>Version</label>
                <input type="text" name="version" placeholder="e.g., 1.0.0">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Brief description of the validation scope..."></textarea>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 1rem;">
                <button type="button" class="btn btn-accent" style="flex: 1; color: var(--danger-color); font-weight: 700;" onclick="closeModal('newProjectModal')">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Create Project</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 2rem;">
        <div style="margin-bottom: 1.5rem;">
            <div style="width: 80px; height: 80px; background: #ffebee; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="fas fa-trash-alt" style="font-size: 2.5rem; color: var(--danger-color);"></i>
            </div>
        </div>
        <h3 style="margin-bottom: 0.5rem; color: var(--danger-color);">Delete Project?</h3>
        <p style="color: var(--text-light); margin-bottom: 2rem;">Are you sure you want to delete this project? This action cannot be undone and all associated data will be lost.</p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="closeModal('deleteModal')" class="btn btn-secondary" style="min-width: 100px;">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="btn btn-danger" style="min-width: 100px;">Delete</a>
        </div>
    </div>
</div>

<!-- User Management Modal -->
<div id="userManagementModal" class="modal">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3>User Management</h3>
            <span class="close-modal" onclick="closeModal('userManagementModal')">&times;</span>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
            <!-- Add User Form -->
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--primary-dark); border-bottom: 2px solid var(--accent-color); padding-bottom: 0.5rem; display: inline-block;">Add New User</h4>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_user">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Username <span style="color: var(--danger-color)">*</span></label>
                            <div style="position: relative;">
                                <i class="fas fa-user" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                                <input type="text" name="username" class="form-control" placeholder="Username" required style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid var(--border-color); border-radius: 8px;">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Password <span style="color: var(--danger-color)">*</span></label>
                            <div style="position: relative;">
                                <i class="fas fa-lock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                                <input type="password" name="password" id="newUserPassword" class="form-control" placeholder="Password" required style="width: 100%; padding: 10px 35px 10px 35px; border: 1px solid var(--border-color); border-radius: 8px;">
                                <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light); cursor: pointer;"></i>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Role</label>
                            <div style="display: flex; gap: 10px;">
                                <div style="position: relative; flex: 1;">
                                    <i class="fas fa-shield-alt" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                                    <select name="role" class="form-control" style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid var(--border-color); border-radius: 8px; background: white;">
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary" style="padding: 0 1.5rem;">Add</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- User List -->
            <div>
                <h4 style="margin-bottom: 1rem; color: var(--primary-dark); border-bottom: 2px solid var(--accent-color); padding-bottom: 0.5rem; display: inline-block;">Existing Users</h4>
                <div style="background: var(--white); border-radius: 8px; border: 1px solid var(--border-color); overflow: hidden;">
                    <div style="overflow-x: auto; max-height: 300px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                                <tr style="text-align: left;">
                                    <th style="padding: 1rem; font-weight: 600; color: var(--text-light);">Username</th>
                                    <th style="padding: 1rem; font-weight: 600; color: var(--text-light);">Role</th>
                                    <th style="padding: 1rem; font-weight: 600; color: var(--text-light);">Created At</th>
                                    <th style="padding: 1rem; font-weight: 600; color: var(--text-light); text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="4" style="padding: 2rem; text-align: center; color: var(--text-light);">No users found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                        <td style="padding: 0.75rem 1rem;">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width: 28px; height: 28px; background: #e3f2fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 0.8rem;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <span style="font-weight: 500;"><?php echo htmlspecialchars($user['username']); ?></span>
                                            </div>
                                        </td>
                                        <td style="padding: 0.75rem 1rem;">
                                            <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-primary' : 'badge-secondary'; ?>" style="font-size: 0.75rem;">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; color: var(--text-light); font-size: 0.9rem;">
                                            <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                        </td>
                                        <td style="padding: 0.75rem 1rem; text-align: right;">
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button onclick="confirmDeleteUser(<?php echo $user['id']; ?>)" class="btn btn-danger" style="padding: 0.4rem; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div id="deleteUserModal" class="modal">
    <div class="modal-content" style="max-width: 400px; text-align: center; padding: 2rem;">
        <div style="margin-bottom: 1.5rem;">
            <div style="width: 80px; height: 80px; background: #ffebee; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                <i class="fas fa-user-times" style="font-size: 2.5rem; color: var(--danger-color);"></i>
            </div>
        </div>
        <h3 style="margin-bottom: 0.5rem; color: var(--danger-color);">Delete User?</h3>
        <p style="color: var(--text-light); margin-bottom: 2rem;">Are you sure you want to delete this user? This action cannot be undone.</p>
        <form method="POST" action="">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" id="deleteUserId">
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" onclick="closeModal('deleteUserModal')" class="btn btn-secondary" style="min-width: 100px;">Cancel</button>
                <button type="submit" class="btn btn-danger" style="min-width: 100px;">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Move modals to body to ensure they're above everything (fixes z-index stacking issues)
    document.body.appendChild(document.getElementById('newProjectModal'));
    document.body.appendChild(document.getElementById('deleteModal'));
    const userManagementModal = document.getElementById('userManagementModal');
    if (userManagementModal) document.body.appendChild(userManagementModal);
    const deleteUserModal = document.getElementById('deleteUserModal');
    if (deleteUserModal) document.body.appendChild(deleteUserModal);

    function confirmDelete(projectId) {
        document.getElementById('confirmDeleteBtn').href = 'delete_project.php?id=' + projectId;
        openModal('deleteModal');
    }

    function confirmDeleteUser(userId) {
        document.getElementById('deleteUserId').value = userId;
        openModal('deleteUserModal');
    }

    // Password Visibility Toggle
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordInput = document.getElementById('newUserPassword');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // Check for URL parameters to show toast
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        
        if (msg === 'deleted') {
            showToast('Project deleted successfully', 'success');
        } else if (msg === 'user_added') {
            showToast('User created successfully', 'success');
        } else if (msg === 'user_deleted') {
            showToast('User deleted successfully', 'success');
        }

        if (msg) {
            // Clean URL
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toastNotification');
        const toastMessage = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');

        toastMessage.textContent = message;
        toast.className = 'toast-notification ' + type;
        
        if (type === 'success') {
            toastIcon.className = 'fas fa-check-circle';
        } else {
            toastIcon.className = 'fas fa-info-circle';
        }

        toast.classList.add('show');

        setTimeout(function() {
            toast.classList.remove('show');
        }, 3000);
    }
</script>

<!-- Toast Notification Container -->
<div id="toastNotification" class="toast-notification">
    <i id="toastIcon" class="fas fa-check-circle"></i>
    <span id="toastMessage">Notification message</span>
</div>

<?php include 'includes/footer.php'; ?>
