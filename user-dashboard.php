<?php
session_start();
include_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: user-login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

$message = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Get current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password && strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
            $update->bindParam(':password', $hashed);
            $update->bindParam(':id', $user_id);

            if ($update->execute()) {
                $message = "Password changed successfully!";
            } else {
                $error = "Failed to update password.";
            }
        } else {
            $error = "New passwords don't match or too short (min 6 characters).";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if ($name) {
        $update = $conn->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
        $update->bindParam(':name', $name);
        $update->bindParam(':phone', $phone);
        $update->bindParam(':id', $user_id);

        if ($update->execute()) {
            $_SESSION['user_name'] = $name;
            $message = "Profile updated successfully!";
        } else {
            $error = "Failed to update profile.";
        }
    }
}

// Get user data
$user_query = $conn->prepare("SELECT * FROM users WHERE id = :id");
$user_query->bindParam(':id', $user_id);
$user_query->execute();
$user_data = $user_query->fetch(PDO::FETCH_ASSOC);

// Get verification requests
$verif_query = $conn->prepare('SELECT * FROM verifications WHERE user_id = :uid ORDER BY created_at DESC');
$verif_query->bindParam(':uid', $user_id);
$verif_query->execute();
$requests = $verif_query->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$total_requests = count($requests);
$pending_requests = count(array_filter($requests, fn($r) => $r['status'] === 'pending'));
$approved_requests = count(array_filter($requests, fn($r) => $r['status'] === 'approved'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* Page-specific dashboard styles */

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-banner h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--white);
        }

        .welcome-banner p {
            color: var(--accent-color);
            font-size: 1.1rem;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-left: 4px solid;
        }

        .stat-card.primary {
            border-color: var(--primary-color);
        }

        .stat-card.warning {
            border-color: var(--warning);
        }

        .stat-card.success {
            border-color: var(--success);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card.primary .stat-icon {
            background: rgba(37, 211, 102, 0.1);
            color: var(--primary-color);
        }

        .stat-card.warning .stat-icon {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .stat-card.success .stat-icon {
            background: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 2px solid #ddd;
        }

        .tab {
            padding: 12px 24px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab.active {
            color: var(--primary-color);
            border-bottom-color: var(--accent-color);
        }

        .tab:hover {
            color: var(--accent-color);
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Card */
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .card-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--gold-light);
        }

        .btn-secondary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--primary-dark);
        }

        /* Alert */
        .alert {
            padding: 15px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table thead {
            background: var(--light-bg);
        }

        .table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 2px solid #ddd;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .table tbody tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-in_progress {
            background: #d1ecf1;
            color: #0c5460;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 0.9rem;
            }

            .welcome-banner h1 {
                font-size: 1.5rem;
            }

            .tabs {
                overflow-x: auto;
                flex-wrap: nowrap;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', $user_data['name'])[0]); ?>! 👋</h1>
            <p>Manage your verification requests and account settings</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-number"><?php echo $total_requests; ?></div>
                        <div class="stat-label">Total Requests</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-number"><?php echo $pending_requests; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-number"><?php echo $approved_requests; ?></div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('requests')">
                <i class="fas fa-list"></i> My Requests
            </button>
            <button class="tab" onclick="switchTab('profile')">
                <i class="fas fa-user"></i> Profile
            </button>
            <button class="tab" onclick="switchTab('security')">
                <i class="fas fa-lock"></i> Security
            </button>
        </div>

        <!-- Tab Content: Requests -->
        <div id="requests" class="tab-content active">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 class="card-title" style="margin: 0;">Verification Requests</h2>
                    <a href="contact.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Request
                    </a>
                </div>

                <?php if (count($requests) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Request Type</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Admin Feedback</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $req): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($req['request_type']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($req['message'], 0, 100, '...')); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($req['status']); ?>">
                                            <?php echo ucfirst($req['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($req['admin_feedback'])): ?>
                                            <button type="button" class="feedback-btn" onclick="showFeedback(<?php echo $req['id']; ?>, '<?php echo addslashes(htmlspecialchars($req['admin_feedback'])); ?>')" style="background: var(--accent-color); color: var(--primary-color); border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 12px;">
                                                View Feedback
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-inbox"></i>
                        <h3>No Requests Yet</h3>
                        <p>Submit your first verification request to get started</p>
                        <a href="contact.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-plus"></i> Submit Request
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab Content: Profile -->
        <div id="profile" class="tab-content">
            <div class="card">
                <h2 class="card-title">Profile Information</h2>

                <?php if ($message && !isset($_POST['change_password'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error && !isset($_POST['change_password'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-input"
                                value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-input"
                                value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-input"
                                value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-input"
                                value="<?php echo date('F j, Y', strtotime($user_data['created_at'])); ?>" disabled>
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Tab Content: Security -->
        <div id="security" class="tab-content">
            <div class="card">
                <h2 class="card-title">Change Password</h2>

                <?php if ($message && isset($_POST['change_password'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error && isset($_POST['change_password'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-input"
                                minlength="6" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-input"
                                minlength="6" required>
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Deactivate all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Activate selected tab
            event.target.closest('.tab').classList.add('active');
        }

        // Feedback Modal
        function showFeedback(requestId, feedback) {
            const modal = document.getElementById('feedbackModal');
            document.getElementById('feedbackContent').innerText = feedback;
            modal.style.display = 'block';
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('feedbackModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <!-- Feedback Modal -->
    <div id="feedbackModal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);overflow:auto;">
        <div style="background-color:var(--white);margin:10% auto;padding:30px;border-radius:8px;width:90%;max-width:600px;box-shadow:0 6px 20px rgba(0,0,0,0.3);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h2 style="margin:0;color:var(--primary-color);">Admin Feedback</h2>
                <span onclick="closeFeedbackModal()" style="cursor:pointer;font-size:28px;font-weight:bold;color:#999;">&times;</span>
            </div>
            <div id="feedbackContent" style="background:#f9f9f9;padding:20px;border-radius:6px;border-left:4px solid var(--accent-color);white-space:pre-wrap;word-wrap:break-word;color:#333;line-height:1.6;">
                <!-- Feedback will be inserted here -->
            </div>
            <button onclick="closeFeedbackModal()" style="margin-top:20px;background:var(--primary-color);color:white;padding:10px 20px;border:none;border-radius:5px;cursor:pointer;font-weight:600;">Close</button>
        </div>
    </div>

</body>

</html>

