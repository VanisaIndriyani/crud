<?php 
require_once 'includes/db.php';
include 'includes/header.php'; 

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Check if username is taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
    $stmt->execute(['username' => $username, 'id' => $userId]);
    if ($stmt->fetch()) {
        $error = "Username already taken.";
    } else {
        // Password Validation
        $hashedPassword = null;
        if (!empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $error = "Passwords do not match.";
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            }
        }

        if (!$error) {
            // Handle Profile Picture Upload
            $profilePicture = $user['profile_picture']; // Keep existing if no new upload
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'assets/uploads/profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExt = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($fileExt, $allowedExts)) {
                    $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExt;
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
                        // Delete old profile picture if exists
                        if ($user['profile_picture'] && file_exists($user['profile_picture'])) {
                            unlink($user['profile_picture']);
                        }
                        $profilePicture = $uploadPath;
                    } else {
                        $error = "Failed to upload image.";
                    }
                } else {
                    $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                }
            }
        }

        if (!$error) {
            if ($hashedPassword) {
                $stmt = $pdo->prepare("UPDATE users SET username = :username, password = :password, profile_picture = :profile_picture WHERE id = :id");
                $params = ['username' => $username, 'password' => $hashedPassword, 'profile_picture' => $profilePicture, 'id' => $userId];
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = :username, profile_picture = :profile_picture WHERE id = :id");
                $params = ['username' => $username, 'profile_picture' => $profilePicture, 'id' => $userId];
            }

            if ($stmt->execute($params)) {
                $_SESSION['username'] = $username; // Update session
                $message = "Profile updated successfully!";
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute(['id' => $userId]);
                $user = $stmt->fetch();
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}
?>

<div class="container fade-in">
    <div class="project-detail-card" style="max-width: 600px; margin: 0 auto;">
        <div class="project-detail-header">
            <h1 class="stage-title">Edit Profile</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: #f0f0f0; margin: 0 auto; overflow: hidden; position: relative; border: 3px solid var(--primary-color);">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user" style="font-size: 60px; color: #ccc; line-height: 120px;"></i>
                    <?php endif; ?>
                </div>
                <div style="margin-top: 1rem;">
                    <label for="profile_picture" class="btn btn-secondary btn-sm" style="cursor: pointer;">
                        <i class="fas fa-camera"></i> Change Photo
                    </label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;" onchange="document.querySelector('.file-name').textContent = this.files[0].name">
                    <div class="file-name" style="font-size: 0.8rem; color: var(--text-light); margin-top: 5px;"></div>
                </div>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div style="margin: 2rem 0; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text-light);">Change Password <span style="font-size: 0.8rem; font-weight: normal;">(Leave blank to keep current)</span></h3>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="Enter new password">
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 2rem;">
                <a href="dashboard.php" class="btn btn-secondary" style="flex: 1; justify-content: center;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>