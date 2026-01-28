<?php
session_start();
require_once 'includes/db.php';

// Login logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // Store role in session
            $_SESSION['welcome_message'] = "Welcome back, " . htmlspecialchars($user['username']) . "!";
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please enter username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Validation System</title>
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
      :root {
    --primary-color: #0d47a1; /* Stronger Blue */
    --primary-dark: #2c60d9ff;
    --accent-color: #00c853; /* Vibrant Green */
    --text-color: #333333;
    --white: #ffffff;
    --background-gradient: linear-gradient(-45deg, #1565C0, #0d47a1, #0277bd, #01579b);
}

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--background-gradient);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-wrapper {
            display: flex;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            min-height: 550px;
            animation: fadeIn 1s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-left {
            flex: 1;
            background: #e3f2fd; /* Light Blue BG */
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Decorative Circle */
        .login-left::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(21, 101, 192, 0.1); /* Light Blue Circle */
            border-radius: 50%;
            top: -50px;
            left: -50px;
            animation: pulse 10s infinite alternate;
        }
        
        .login-left::after {
             content: '';
             position: absolute;
             width: 200px;
             height: 200px;
             background: rgba(13, 71, 161, 0.1); /* Light Blue Circle */
             border-radius: 50%;
             bottom: 50px;
             right: 50px;
             animation: pulse 8s infinite alternate-reverse;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        .laptop-icon-container {
            position: relative;
            z-index: 2;
            animation: floatIcon 3s ease-in-out infinite;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .login-illustration-img {
            width: 85%;
            max-width: 400px;
            height: auto;
            filter: drop-shadow(0 15px 30px rgba(0,0,0,0.15));
            transition: transform 0.5s ease;
        }
        
        .login-illustration-img:hover {
            transform: scale(1.05) rotate(2deg);
        }

        .login-right {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: var(--white);
            position: relative;
        }

        .login-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
            animation: slideInDown 0.8s ease-out;
        }
        
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-subtitle {
             font-size: 0.9rem;
             color: #666;
             margin-bottom: 2.5rem;
        }

        .login-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 5px;
            background: var(--primary-color);
            margin: 10px auto 0;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .login-title:hover::after {
            width: 100px;
        }

        .login-form {
            width: 100%;
            max-width: 320px;
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            animation: fadeInUp 1s ease-out 0.3s backwards;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-input-group {
            position: relative;
        }
        
        .login-input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            transition: color 0.3s;
        }

        .login-input {
            width: 100%;
            padding: 16px 20px 16px 45px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9f9f9;
            color: var(--text-color);
        }

        .login-input:focus {
            border-color: var(--primary-color);
            background: var(--white);
            outline: none;
            box-shadow: 0 0 0 4px rgba(13, 71, 161, 0.1);
        }
        
        .login-input:focus + .login-input-icon {
            color: var(--primary-color);
        }
        
        /* Password Field Container */
        .password-container {
            position: relative;
            width: 100%;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
            transition: color 0.3s;
        }
        
        .toggle-password:hover {
            color: var(--primary-color);
        }

        .login-btn {
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(to right, #2e7d32, #00c853);
            color: var(--white);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1rem;
            box-shadow: 0 10px 20px rgba(0, 200, 83, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .login-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .login-btn:hover::after {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 200, 83, 0.3);
            background: linear-gradient(to right, #00c853, #2e7d32);
        }

        .default-user-info {
            margin-top: 2rem;
            padding: 12px 20px;
            background: #f0f4f8;
            border-radius: 10px;
            font-size: 0.85rem;
            color: #555;
            border: 1px solid #e1e8ed;
            text-align: center;
            width: 100%;
            max-width: 320px;
            animation: fadeIn 1s ease-out 0.6s backwards;
        }

        /* Responsive Login */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                width: 100%;
                max-width: 400px;
                min-height: auto;
            }

            .login-left {
                display: none;
            }

            .login-right {
                padding: 2.5rem 2rem;
            }
        }
    </style>
</head>
<body class="login-body">
    <div class="login-wrapper">
        <!-- Left Side: Laptop Illustration -->
        <div class="login-left">
            <div class="login-illustration">
                <div class="laptop-icon-container">
                    <img src="assets/img/home-banner-7.svg" alt="Login Illustration" class="login-illustration-img">
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="login-right">
            <div class="login-title">WELCOME</div>
            
            <?php if (isset($error)): ?>
                <p style="color: #ffcdd2; margin-bottom: 15px; font-size: 0.9rem; background: rgba(255,0,0,0.1); padding: 5px 10px; border-radius: 5px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="login-input-group">
                    <input type="text" id="username" name="username" class="login-input" placeholder="Username" required autocomplete="off">
                    <i class="fas fa-user login-input-icon"></i>
                </div>
                
                <div class="password-container login-input-group">
                    <input type="password" id="password" name="password" class="login-input" placeholder="••••••••" required>
                    <i class="fas fa-lock login-input-icon"></i>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
                
                <button type="submit" class="login-btn">SUBMIT</button>
            </form>

         
        </div>
    </div>
    
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            // toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            // toggle the eye slash icon
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
