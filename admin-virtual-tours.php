<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$db = new \Database();
$conn = $db->getConnection();

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $youtube_url = trim($_POST['youtube_url']);

                // Extract video ID from YouTube URL
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches);
                $video_id = $matches[1] ?? '';

                if ($video_id) {
                    $stmt = $conn->prepare("INSERT INTO virtual_tours (title, description, youtube_url, youtube_video_id, created_by, display_order) 
                                           SELECT ?, ?, ?, ?, ?, COALESCE(MAX(display_order), 0) + 1 FROM virtual_tours");
                    $stmt->execute([$title, $description, $youtube_url, $video_id, $_SESSION['admin_id']]);
                    $success_message = "Virtual tour added successfully!";
                } else {
                    $error_message = "Invalid YouTube URL. Please check and try again.";
                }
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("DELETE FROM virtual_tours WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = "Virtual tour deleted successfully!";
                break;

            case 'toggle_status':
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE virtual_tours SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = "Status updated successfully!";
                break;

            case 'update_order':
                $id = (int)$_POST['id'];
                $direction = $_POST['direction'];

                $stmt = $conn->prepare("SELECT display_order FROM virtual_tours WHERE id = ?");
                $stmt->execute([$id]);
                $current_order = $stmt->fetchColumn();

                if ($direction === 'up') {
                    $conn->prepare("UPDATE virtual_tours SET display_order = display_order + 1 WHERE display_order = ? - 1")->execute([$current_order]);
                    $conn->prepare("UPDATE virtual_tours SET display_order = display_order - 1 WHERE id = ?")->execute([$id]);
                } else {
                    $conn->prepare("UPDATE virtual_tours SET display_order = display_order - 1 WHERE display_order = ? + 1")->execute([$current_order]);
                    $conn->prepare("UPDATE virtual_tours SET display_order = display_order + 1 WHERE id = ?")->execute([$id]);
                }
                $success_message = "Order updated successfully!";
                break;
        }
    }
}

// Fetch all virtual tours
$stmt = $conn->query("SELECT vt.*, au.username as created_by_name 
                      FROM virtual_tours vt 
                      LEFT JOIN admin_users au ON vt.created_by = au.id 
                      ORDER BY vt.display_order ASC");
$tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Virtual Tours | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4B2C6B;
            --primary-dark: #2D1B47;
            --accent-color: #D4AF37;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #EEE9F8;
        }

        .admin-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .admin-header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .back-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-link:hover {
            color: white;
        }

        .btn-logout {
            background: var(--accent-color);
            color: var(--primary-dark);
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: white;
        }

        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .card h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--accent-color);
            color: var(--primary-dark);
        }

        .btn-primary:hover {
            background: #C9A030;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .tours-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .tours-table th,
        .tours-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .tours-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--primary-color);
        }

        .tours-table tr:hover {
            background: #f8f9fa;
        }

        .video-preview {
            width: 120px;
            height: 68px;
            border-radius: 4px;
            overflow: hidden;
        }

        .video-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            margin-top: 10px;
        }

        .back-link:hover {
            color: var(--accent-color);
        }

        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <h1><i class="fas fa-video"></i> Manage Virtual Property Tours</h1>
        <div class="admin-header-right">
            <a href="admin-dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="admin-logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Add New Tour -->
        <div class="card">
            <h2><i class="fas fa-plus-circle"></i> Add New Virtual Tour</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="title">Tour Title *</label>
                    <input type="text" id="title" name="title" required
                        placeholder="e.g., Maitama Luxury Villa">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"
                        placeholder="Brief description of the property"></textarea>
                </div>

                <div class="form-group">
                    <label for="youtube_url">YouTube URL *</label>
                    <input type="url" id="youtube_url" name="youtube_url" required
                        placeholder="https://www.youtube.com/watch?v=...">
                    <div class="help-text">
                        <i class="fas fa-info-circle"></i> Paste the full YouTube video URL
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Virtual Tour
                </button>
            </form>
        </div>

        <!-- Existing Tours -->
        <div class="card">
            <h2><i class="fas fa-list"></i> Existing Virtual Tours (<?php echo count($tours); ?>)</h2>

            <?php if (empty($tours)): ?>
                <p style="text-align: center; color: #666; padding: 40px 0;">
                    <i class="fas fa-video" style="font-size: 48px; display: block; margin-bottom: 15px;"></i>
                    No virtual tours added yet. Add your first tour above!
                </p>
            <?php else: ?>
                <table class="tours-table">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tours as $index => $tour): ?>
                            <tr>
                                <td>
                                    <div class="video-preview">
                                        <img src="https://img.youtube.com/vi/<?php echo htmlspecialchars($tour['youtube_video_id']); ?>/0.jpg"
                                            alt="<?php echo htmlspecialchars($tour['title']); ?>">
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($tour['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($tour['description'], 0, 80)) . (strlen($tour['description']) > 80 ? '...' : ''); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $tour['status']; ?>">
                                        <?php echo ucfirst($tour['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <?php if ($index > 0): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_order">
                                                <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                                <input type="hidden" name="direction" value="up">
                                                <button type="submit" class="btn btn-secondary btn-sm" title="Move Up">
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($index < count($tours) - 1): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="update_order">
                                                <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                                <input type="hidden" name="direction" value="down">
                                                <button type="submit" class="btn btn-secondary btn-sm" title="Move Down">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($tour['created_by_name'] ?? 'Unknown'); ?></td>
                                <td>
                                    <div class="actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-<?php echo $tour['status'] === 'active' ? 'eye-slash' : 'eye'; ?>"></i>
                                                <?php echo $tour['status'] === 'active' ? 'Hide' : 'Show'; ?>
                                            </button>
                                        </form>

                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this virtual tour?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $tour['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>