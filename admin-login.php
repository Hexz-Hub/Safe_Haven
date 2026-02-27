<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin-dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once __DIR__ . '/config/database.php';

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $db = new \Database();
        $conn = $db->getConnection();

        $query = "SELECT * FROM admin_users WHERE username = :username LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['full_name'];

                // Update last login
                $update_query = "UPDATE admin_users SET last_login = NOW() WHERE id = :id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(':id', $user['id']);
                $update_stmt->execute();

                header("Location: admin-dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please enter both username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SafeHaven</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #4B2C6B;
            --primary-dark: #2D1B47;
            --primary-light: #6B4E8C;
            --accent-color: #D4AF37;
            --accent-light: #E8C766;
            --text-dark: #333333;
            --white: #ffffff;
            --light-gray: #f5f3ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2D1B47 0%, #4B2C6B 45%, #6B4E8C 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.96);
            padding: 50px 40px;
            border-radius: 16px;
            border: 1px solid rgba(75, 44, 107, 0.15);
            box-shadow: 0 20px 45px rgba(45, 27, 71, 0.24);
            max-width: 420px;
            width: 100%;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-section h1 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .logo-section p {
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e4dcf2;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .error-message {
            background: #ff4444;
            color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: var(--accent-color);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 35px 25px;
            }

            .logo-section h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo-section">
            <h1>SAFEHAVEN</h1>
            <p>Admin Dashboard Login</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="back-link">
            <a href="index.php">← Back to Website</a>
        </div>
    </div>
</body>

</html>