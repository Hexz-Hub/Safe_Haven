<?php
session_start();
include_once 'config/database.php';

$message = '';
$error = '';
$step = 'request'; // request, verify, reset

// Handle password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $db = new Database();
    $conn = $db->getConnection();

    if ($_POST['action'] === 'request_reset') {
        $email = trim($_POST['email']);

        // Check if email exists
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token in database
            $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
            $insert->bindParam(':email', $email);
            $insert->bindParam(':token', $token);
            $insert->bindParam(':expires', $expires_at);
            $insert->execute();

            // In a real application, send email with reset link
            // For now, we'll display the token (NOT SECURE - for development only)
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/forgot-password.php?token=" . $token;

            $message = "Password reset instructions have been sent to your email. <br><br><strong>Development Mode:</strong> Click this link: <a href='$reset_link'>Reset Password</a>";
            // In production, use PHPMailer or similar to send email
        } else {
            $error = "No account found with that email address.";
        }
    } elseif ($_POST['action'] === 'reset_password') {
        $token = $_POST['token'];
        $new_password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Verify token
            $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW() AND used = 0 LIMIT 1");
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $reset = $stmt->fetch(PDO::FETCH_ASSOC);
                $email = $reset['email'];

                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
                $update->bindParam(':password', $hashed_password);
                $update->bindParam(':email', $email);

                if ($update->execute()) {
                    // Mark token as used
                    $mark_used = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = :token");
                    $mark_used->bindParam(':token', $token);
                    $mark_used->execute();

                    header("Location: user-login.php?reset=success");
                    exit();
                } else {
                    $error = "Failed to update password. Please try again.";
                }
            } else {
                $error = "Invalid or expired reset token.";
            }
        }
    }
}

// Check if token is provided in URL
if (isset($_GET['token'])) {
    $db = new Database();
    $conn = $db->getConnection();

    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW() AND used = 0 LIMIT 1");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $step = 'reset';
    } else {
        $error = "Invalid or expired reset token.";
        $step = 'request';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include_once 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR FORGOT PASSWORD PAGE */

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

        .success-message {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
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

    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    }

    .brand-link {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 30px;
    text-decoration: none;
    color: var(--text-light);
    transition: var(--transition);
    }

    .brand-link:hover {
    color: var(--accent-color);
    }

    .brand-icon {
    font-size: 2rem;
    color: var(--accent-color);
    }

    .brand-text {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.5px;
    }

    .auth-header {
    text-align: center;
    margin-bottom: 30px;
    }

    .auth-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 10px;
    }

    .auth-header p {
    color: var(--text-muted);
    font-size: 0.95rem;
    }

    .auth-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border-radius: var(--radius);
    padding: 40px;
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .error-message,
    .success-message {
    padding: 15px 20px;
    border-radius: var(--radius-sm);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.9rem;
    }

    .error-message {
    background: rgba(220, 53, 69, 0.15);
    border: 1px solid rgba(220, 53, 69, 0.3);
    color: #ff6b7a;
    }

    .success-message {
    background: rgba(40, 167, 69, 0.15);
    border: 1px solid rgba(40, 167, 69, 0.3);
    color: #6fd99e;
    }

    .success-message a {
    color: var(--accent-color);
    text-decoration: underline;
    }

    .form-group {
    margin-bottom: 25px;
    }

    .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-light);
    }

    .input-wrapper {
    position: relative;
    }

    .input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 1rem;
    }

    .form-input {
    width: 100%;
    padding: 14px 16px 14px 48px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: var(--radius-sm);
    color: var(--white);
    font-size: 0.95rem;
    transition: var(--transition);
    }

    .form-input::placeholder {
    color: var(--text-muted);
    }

    .form-input:focus {
    outline: none;
    border-color: var(--accent-color);
    background: rgba(255, 255, 255, 0.12);
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }

    .toggle-password {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 5px;
    transition: var(--transition);
    }

    .toggle-password:hover {
    color: var(--accent-color);
    }

    .btn {
    width: 100%;
    padding: 14px 24px;
    font-size: 1rem;
    font-weight: 600;
    border: none;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    }

    .btn-primary {
    background: var(--gradient-gold);
    color: var(--primary-dark);
    }

    .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 102, 204, 0.4);
    }

    .btn-icon {
    font-size: 1rem;
    }

    .auth-footer {
    margin-top: 30px;
    text-align: center;
    }

    .auth-footer a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    }

    .auth-footer a:hover {
    color: var(--accent-color);
    }

    .info-box {
    background: rgba(0, 102, 204, 0.12);
    border: 1px solid rgba(0, 102, 204, 0.3);
    padding: 15px;
    border-radius: var(--radius-sm);
    margin-bottom: 20px;
    font-size: 0.9rem;
    color: var(--text-light);
    }

    @media (max-width: 480px) {
    .auth-card {
    padding: 30px 20px;
    }

    .auth-header h1 {
    font-size: 1.6rem;
    }
    }
    </style>
</head>

<body>
    <div class="auth-container">
        <a href="index.php" class="brand-link">
            <i class="fas fa-search-location brand-icon"></i>
            <span class="brand-text">SAFEHAVEN</span>
        </a>

        <div class="auth-header">
            <h1><?php echo $step === 'reset' ? 'Reset Password' : 'Forgot Password'; ?></h1>
            <p>
                <?php
                if ($step === 'reset') {
                    echo 'Enter your new password below';
                } else {
                    echo 'Enter your email to receive a password reset link';
                }
                ?>
            </p>
        </div>

        <div class="auth-card">
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($step === 'request'): ?>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="action" value="request_reset">

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
                                autocomplete="email">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane btn-icon"></i>
                        <span>Send Reset Link</span>
                    </button>
                </form>
            <?php else: ?>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    Password must be at least 6 characters long.
                </div>

                <form method="POST" class="auth-form">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">

                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter new password"
                                required
                                minlength="6">
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Confirm new password"
                                required
                                minlength="6">
                            <button type="button" class="toggle-password" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check btn-icon"></i>
                        <span>Reset Password</span>
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                <div style="margin-bottom: 15px;">
                    <a href="user-login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
                <div>
                    <a href="index.php"><i class="fas fa-home"></i> Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');

        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                const eyeIcon = this.querySelector('i');
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        }

        if (toggleConfirmPassword) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);

                const eyeIcon = this.querySelector('i');
                if (type === 'text') {
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        }

        // Password match validation
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });

            passwordInput.addEventListener('input', function() {
                if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            });
        }
    </script>
</body>

</html>