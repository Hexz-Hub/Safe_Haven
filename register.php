<?php
session_start();
include_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();
$message = '';
$error = '';

// Create users table if needed
$create = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->exec($create);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $check->bindParam(':email', $email);
        $check->execute();

        if ($check->fetch()) {
            $error = 'This email is already registered. Please sign in or use another email.';
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = $conn->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
                $ins->bindParam(':name', $name);
                $ins->bindParam(':email', $email);
                $ins->bindParam(':password', $hash);

                if ($ins->execute()) {
                    $_SESSION['user_id'] = $conn->lastInsertId();
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    header('Location: user-dashboard.php');
                    exit();
                } else {
                    $error = 'Failed to create account.';
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $error = 'This email is already registered. Please sign in or use another email.';
                } else {
                    $error = 'Failed to create account. Please try again.';
                }
            }
        }
    } else {
        $error = 'Please provide a valid email and a password (min 6 chars).';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include_once 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR REGISTER PAGE */
        :root {
            --gradient-gold: linear-gradient(135deg, var(--accent-color), var(--accent-light));
            --radius: 16px;
            --radius-sm: 10px;
            --text-light: #ffffff;
            --gold-light: var(--accent-light);
            --success: #28a745;
            --error: #dc3545;
        }

        .auth-container {
            width: 100%;
            max-width: 500px;
            margin: 60px auto;
            padding: 0 20px;
            z-index: 1;
            position: relative;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
            animation: fadeInDown 0.8s ease;
        }

        .auth-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .auth-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .auth-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 40px;
            border: 1px solid rgba(75, 44, 107, 0.15);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-gold);
        }

        .auth-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(75, 44, 107, 0.08) 0%, transparent 70%);
            z-index: 0;
        }

        .auth-form {
            position: relative;
            z-index: 1;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-color);
            font-size: 1.1rem;
            z-index: 2;
        }

        .form-input {
            width: 100%;
            padding: 16px 16px 16px 50px;
            background: var(--white);
            border: 2px solid rgba(75, 44, 107, 0.2);
            border-radius: var(--radius-sm);
            color: var(--text-dark);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-color);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.12);
        }

        .form-input::placeholder {
            color: rgba(45, 27, 71, 0.5);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-size: 1.1rem;
            transition: var(--transition);
            z-index: 2;
        }

        .toggle-password:hover {
            color: var(--accent-color);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .checkbox-custom {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(0, 102, 204, 0.5);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .checkbox-custom.checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .checkbox-custom.checked::after {
            content: '✓';
            color: var(--primary-dark);
            font-weight: bold;
            font-size: 0.8rem;
        }

        input[type="checkbox"] {
            display: none;
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .forgot-link:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: var(--transition);
            gap: 10px;
        }

        .btn-primary {
            background: var(--accent-color);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(75, 44, 107, 0.2);
        }

        .btn-primary:hover {
            background: var(--accent-light);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(75, 44, 107, 0.25);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-icon {
            font-size: 1.2rem;
        }

        .auth-footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 0.95rem;
        }

        .auth-footer a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .auth-footer a:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        .error-message {
            background: rgba(220, 53, 69, 0.15);
            border-left: 4px solid var(--error);
            color: #ff8a8a;
            padding: 15px;
            border-radius: var(--radius-sm);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease;
        }

        .success-message {
            background: rgba(40, 167, 69, 0.15);
            border-left: 4px solid var(--success);
            color: #90ee90;
            padding: 15px;
            border-radius: var(--radius-sm);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 700;
            transition: var(--transition);
        }

        .brand-link:hover {
            color: var(--accent-color);
        }

        .brand-logo {
            width: 44px;
            height: auto;
            display: block;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        /* Responsive */
        @media (max-width: 576px) {
            .auth-card {
                padding: 30px 25px;
            }

            .auth-header h1 {
                font-size: 2rem;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 400px) {
            .auth-card {
                padding: 25px 20px;
            }

            body {
                padding: 15px;
            }
        }

        /* Additional decorative elements */
        .decoration {
            position: absolute;
            z-index: 0;
            opacity: 0.1;
        }

        .decoration-1 {
            top: 10%;
            left: 5%;
            width: 100px;
            height: 100px;
            border: 2px solid var(--accent-color);
            border-radius: 50%;
        }

        .decoration-2 {
            bottom: 15%;
            right: 8%;
            width: 150px;
            height: 150px;
            border: 2px solid var(--accent-color);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
        }

        /* Password strength indicator */
        .password-strength {
            height: 4px;
            background: rgba(75, 44, 107, 0.1);
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .form-help {
            display: block;
            margin-top: 8px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .strength-bar {
            height: 100%;
            width: 0;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }
    </style>
</head>

<body>
    <!-- Decorative elements -->
    <div class="decoration decoration-1"></div>
    <div class="decoration decoration-2"></div>

    <div class="auth-container">
        <a href="index.php" class="brand-link">
            <img src="uploads/logo.png" alt="SafeHaven Logo" class="brand-logo" onerror="this.style.display='none'">
            <span>SAFEHAVEN</span>
        </a>

        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join SafeHaven to track your verification requests and listings</p>
        </div>

        <div class="auth-card">
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            placeholder="Enter your full name"
                            required
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                            autocomplete="name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="Enter your email address"
                            required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            autocomplete="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Create a password (min 6 chars)"
                            required
                            minlength="6"
                            autocomplete="new-password">
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <small class="form-help">Password must be at least 6 characters.</small>
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus btn-icon"></i>
                    <span>Create Account</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="user-login.php">Sign in</a></p>
                <p style="margin-top: 10px;">
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle eye icon
            const eyeIcon = this.querySelector('i');
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });

        // Password strength indicator (basic example)
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;

            strengthBar.style.width = strength + '%';

            // Change color based on strength
            if (strength < 50) {
                strengthBar.style.background = '#dc3545'; // red
            } else if (strength < 75) {
                strengthBar.style.background = '#ffc107'; // yellow
            } else {
                strengthBar.style.background = '#28a745'; // green
            }
        });

        // Auto focus on name field
        document.getElementById('name').focus();

        // Form validation enhancement
        const form = document.querySelector('.auth-form');
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!name || !email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Show loading state on button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Creating...</span>';
            submitBtn.disabled = true;

            return true;
        });
    </script>
</body>

</html>