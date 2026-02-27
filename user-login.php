<?php
session_start();
include_once 'config/database.php';

$error = '';
$login_required_message = '';

// Check if user was redirected for login requirement
if (isset($_SESSION['login_required']) && $_SESSION['login_required']) {
    $login_required_message = "Please log in to perform this action.";
    unset($_SESSION['login_required']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $db = new Database();
    $conn = $db->getConnection();

    // Ensure users table exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $u['password'])) {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['name'];
            $_SESSION['user_email'] = $u['email'];
            $_SESSION['user_role'] = $u['role'];
            header('Location: user-dashboard.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include_once 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR USER LOGIN PAGE */

        .auth-container {
            width: 100%;
            max-width: 500px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .auth-header p {
            color: #666;
            font-size: 1rem;
        }

        .auth-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(75, 44, 107, 0.15);
            padding: 40px;
            border: 1px solid rgba(75, 44, 107, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.95rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 10px;
        }

        .forgot-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .forgot-link:hover {
            color: var(--accent-color);
        }

        .btn {
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
        }

        .btn.btn-primary span,
        .btn.btn-primary i {
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(75, 44, 107, 0.2);
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
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #ffebee;
            border-left: 4px solid #dc3545;
            color: #c53030;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        .brand-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .brand-logo {
            width: 44px;
            height: auto;
            display: block;
        }

        @media (max-width: 576px) {
            .auth-card {
                padding: 30px 25px;
            }

            .auth-header h1 {
                font-size: 2rem;
            }
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
            <h1>Welcome Back</h1>
            <p>Sign in to access your account and continue your journey</p>
        </div>

        <div class="auth-card">
            <?php if ($login_required_message): ?>
                <div class="info-message" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; padding: 12px 15px; border-radius: 5px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-info-circle"></i>
                    <span><?php echo htmlspecialchars($login_required_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php
            // Display success message if redirected from registration
            if (isset($_GET['registered']) && $_GET['registered'] == 'true'):
            ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span>Registration successful! Please log in with your credentials.</span>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
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
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password">
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <!-- Password strength indicator (can be enhanced with JavaScript) -->
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkbox-custom" id="customCheckbox"></span>
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt btn-icon"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create Account</a></p>
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

        // Custom checkbox
        const rememberCheckbox = document.getElementById('remember');
        const customCheckbox = document.getElementById('customCheckbox');

        rememberCheckbox.addEventListener('change', function() {
            if (this.checked) {
                customCheckbox.classList.add('checked');
            } else {
                customCheckbox.classList.remove('checked');
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

        // Auto focus on email field
        document.getElementById('email').focus();

        // Form validation enhancement
        const form = document.querySelector('.auth-form');
        form.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Show loading state on button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Signing In...</span>';
            submitBtn.disabled = true;

            return true;
        });

        // Auto-check remember if previously checked
        if (localStorage.getItem('rememberEmail')) {
            rememberCheckbox.checked = true;
            customCheckbox.classList.add('checked');
            document.getElementById('email').value = localStorage.getItem('rememberEmail');
        }

        // Save email if remember is checked
        rememberCheckbox.addEventListener('change', function() {
            const email = document.getElementById('email').value;
            if (this.checked && email) {
                localStorage.setItem('rememberEmail', email);
            } else {
                localStorage.removeItem('rememberEmail');
            }
        });
    </script>
</body>

</html>