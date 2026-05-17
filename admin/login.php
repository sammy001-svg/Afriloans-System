<?php 
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $user = fetch("SELECT * FROM users WHERE username = ?", [$username]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | ZETA CREDIT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: url('../assets/images/login-bg.png') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Overlay for background */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.7) 100%);
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3.5rem 2.5rem;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-card h2 { 
            color: white; 
            font-size: 2rem; 
            font-weight: 700; 
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .login-card p {
            color: #94a3b8;
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            transition: color 0.3s;
        }

        .input-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            color: white;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
        }

        .input-group input::placeholder {
            color: #64748b;
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
        }

        .input-group input:focus + i {
            color: var(--primary);
        }

        .btn-signin {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-signin:hover {
            background: var(--primary-hover);
            transform: scale(1.02);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.4);
        }

        .alert-error { 
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5; 
            padding: 1rem; 
            border-radius: 15px; 
            margin-bottom: 1.5rem; 
            font-size: 0.9rem; 
            text-align: center;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .footer-links {
            margin-top: 2rem;
            text-align: center;
        }

        .footer-links a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .logo-wrap {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 2.5rem 1.5rem;
            }
            .login-card h2 {
                font-size: 1.5rem;
            }
            .logo-wrap a {
                font-size: 1.4rem !important;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-wrap">
                <a href="../index.php" class="logo" style="color: white; font-size: 1.8rem;">
                    <i class="fas fa-hand-holding-dollar" style="color: var(--primary);"></i>
                    ZETA <span>CREDIT</span>
                </a>
            </div>

            <h2>Welcome Back</h2>
            <p>Please enter your credentials to access the admin dashboard.</p>

            <?php if ($error): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="username" required placeholder="Username">
                    <i class="fas fa-user"></i>
                </div>
                <div class="input-group">
                    <input type="password" name="password" required placeholder="Password">
                    <i class="fas fa-lock"></i>
                </div>
                <button type="submit" class="btn-signin">
                    Sign In <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="footer-links">
                <a href="../index.php"><i class="fas fa-chevron-left"></i> Return to Website</a>
            </div>
        </div>
    </div>

</body>
</html>
