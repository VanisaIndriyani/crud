<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <i class="fas fa-check-double"></i>
            <span>CSV Vision Ease</span>
        </a>
        <div class="user-menu" style="position: relative;">
            <div class="user-dropdown-toggle" onclick="toggleDropdown()" style="cursor: pointer; display: flex; align-items: center; gap: 10px;">
                <?php
                // Get user profile picture
                require_once 'includes/db.php';
                $stmtHeader = $pdo->prepare("SELECT profile_picture FROM users WHERE id = :id");
                $stmtHeader->execute(['id' => $_SESSION['user_id']]);
                $userHeader = $stmtHeader->fetch();
                $profilePic = $userHeader['profile_picture'] ?? '';
                ?>
                
                <span style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #f0f0f0; overflow: hidden; border: 2px solid rgba(255, 255, 255, 0.8);">
                    <?php if ($profilePic && file_exists($profilePic)): ?>
                        <img src="<?php echo $profilePic; ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--primary-color); color: white;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: rgba(255, 255, 255, 0.8);"></i>
            </div>
            
            <div id="userDropdown" class="dropdown-menu">
                <a href="edit_profile.php" class="dropdown-item">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    <script>
    function toggleDropdown() {
        document.getElementById("userDropdown").classList.toggle("show");
    }

    // Close the dropdown if the user clicks outside of it
    window.onclick = function(event) {
        if (!event.target.closest('.user-dropdown-toggle')) {
            var dropdowns = document.getElementsByClassName("dropdown-menu");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
    </script>
    <div class="container">
