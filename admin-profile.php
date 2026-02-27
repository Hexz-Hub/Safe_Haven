<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$db = new \Database();
$conn = $db->getConnection();

// Get admin profile
$admin = [];
$message = '';
try {
    $query = "SELECT * FROM admin_users WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $_SESSION['admin_id']);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $admin = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    try {
        $query = "UPDATE admin_users SET full_name = :full_name, email = :email WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $_SESSION['admin_id']);
        $stmt->execute();
        $message = "Profile updated successfully!";
        // Refresh admin data
        $query = "SELECT * FROM admin_users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $_SESSION['admin_id']);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $message = "Error updating profile: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4B2C6B;
            --primary-dark: #2D1B47;
            --accent-color: #D4AF37;
            --white: #ffffff;
            --light-gray: #EEE9F8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', sans-serif;
            background: var(--light-gray);
            color: var(--primary-color);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 28px;
        }

        .back-btn {
            background: var(--accent-color);
            color: var(--primary-color);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
        }

        .back-btn:hover {
            opacity: 0.9;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 5px rgba(0, 102, 204, 0.3);
        }

        button {
            background: var(--accent-color);
            color: var(--primary-color);
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }

        button:hover {
            opacity: 0.9;
        }

        .info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 4px solid var(--accent-color);
        }

        .info p {
            margin: 8px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-circle"></i> Admin Profile</h1>
            <a href="admin-dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($admin): ?>
            <div class="profile-card">
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($admin['username']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                    </div>

                    <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
                </form>

                <div class="info">
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($admin['id']); ?></p>
                    <p><strong>Last Login:</strong> <?php echo isset($admin['last_login']) ? date('M d, Y @ h:i A', strtotime($admin['last_login'])) : 'Never'; ?></p>
                    <p><strong>Joined:</strong> <?php echo date('M d, Y', strtotime($admin['created_at'])); ?></p>
                </div>
            </div>
        <?php else: ?>
            <div style="background: white; padding: 30px; border-radius: 8px; text-align: center;">
                <p>Unable to load profile information.</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>