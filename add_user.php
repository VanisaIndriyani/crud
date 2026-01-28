<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect non-admins to dashboard
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                
                $message = "User created successfully!";
            }
        } catch (PDOException $e) {
            $error = "Error creating user: " . $e->getMessage();
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<div class="fade-in">
    <div class="dashboard-header">
        <div>
            <h2>Add New User</h2>
            <p>Create a new account for the system</p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div style="display: flex; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; padding: 2rem; background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <?php if ($message): ?>
                <div style="background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div style="background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Username</label>
                    <div style="position: relative;">
                        <i class="fas fa-user" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        <input type="text" name="username" class="form-control" placeholder="Enter username" required style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid var(--border-color); border-radius: 8px;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password</label>
                    <div style="position: relative;">
                        <i class="fas fa-lock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid var(--border-color); border-radius: 8px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Role</label>
                    <div style="position: relative;">
                        <i class="fas fa-shield-alt" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-light);"></i>
                        <select name="role" class="form-control" style="width: 100%; padding: 12px 12px 12px 45px; border: 1px solid var(--border-color); border-radius: 8px; background: white;">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                    <i class="fas fa-user-plus"></i> Create User
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>