<?php
session_start();
include_once 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user-login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get all messages for this user
$messages = [];
try {
    $query = "SELECT m.*, a.full_name as admin_name FROM messages m 
              LEFT JOIN admin_users a ON m.from_user_id = a.id 
              WHERE m.to_user_id = :user_id 
              ORDER BY m.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $messages = [];
}

// Mark message as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $msg_id = $_GET['mark_read'];
    $update_query = "UPDATE messages SET status = 'read' WHERE id = :id AND to_user_id = :user_id";
    $stmt = $conn->prepare($update_query);
    $stmt->bindParam(':id', $msg_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    header("Location: user-messages.php");
    exit();
}

// Get message for detailed view
$message_detail = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $msg_id = $_GET['view'];
    $query = "SELECT m.*, a.full_name as admin_name FROM messages m 
              LEFT JOIN admin_users a ON m.from_user_id = a.id 
              WHERE m.id = :id AND m.to_user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $msg_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $message_detail = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mark as read
    if ($message_detail && $message_detail['status'] === 'unread') {
        $update_query = "UPDATE messages SET status = 'read' WHERE id = :id";
        $stmt = $conn->prepare($update_query);
        $stmt->bindParam(':id', $msg_id);
        $stmt->execute();
        $message_detail['status'] = 'read';
    }
}

// Count unread messages
$unread_count = 0;
$count_query = "SELECT COUNT(*) as count FROM messages WHERE to_user_id = :user_id AND status = 'unread'";
$stmt = $conn->prepare($count_query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$count_result = $stmt->fetch(PDO::FETCH_ASSOC);
$unread_count = $count_result['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: var(--primary-color);
            --primary-dark: var(--primary-dark);
            --accent-color: var(--accent-color);
            --white: #ffffff;
            --light-gray: #f4f5f4;
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
            max-width: 1400px;
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

        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .list-section,
        .detail-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .message-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: 0.3s;
        }

        .message-item:hover {
            background: #f9f9f9;
        }

        .message-item.unread {
            background: #f0f7ff;
            font-weight: 600;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .message-from {
            font-weight: 600;
            color: var(--primary-color);
        }

        .message-date {
            font-size: 0.85rem;
            color: #999;
        }

        .message-subject {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 5px;
        }

        .message-preview {
            font-size: 0.9rem;
            color: #999;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .badge-unread {
            display: inline-block;
            background: var(--accent-color);
            color: var(--primary-color);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 10px;
        }

        .detail-header {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }

        .detail-content {
            padding: 20px;
        }

        .detail-item {
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .detail-value {
            color: #666;
            line-height: 1.6;
            padding: 8px;
            background: #f9f9f9;
            border-left: 3px solid var(--accent-color);
            padding-left: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .message-type-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .message-type-badge.consultation_approval {
            background: #cfe2ff;
            color: #084298;
        }

        .message-type-badge.inspection_confirmation {
            background: #d1e7dd;
            color: #0f5132;
        }

        .message-type-badge.general {
            background: #e2e3e5;
            color: #383d41;
        }

        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-envelope"></i> Messages <?php if ($unread_count > 0): ?><span class="badge-unread"><?php echo $unread_count; ?> New</span><?php endif; ?></h1>
            <a href="user-dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <div class="content-wrapper">
            <!-- Messages List -->
            <div class="list-section">
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item <?php echo $msg['status'] === 'unread' ? 'unread' : ''; ?>" onclick="window.location='?view=<?php echo $msg['id']; ?>'">
                            <div class="message-header">
                                <span class="message-from">
                                    <i class="fas fa-envelope-open"></i>
                                    <?php echo htmlspecialchars($msg['admin_name'] ?? 'Admin Team'); ?>
                                </span>
                                <span class="message-date"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="message-subject">
                                <span class="message-type-badge <?php echo $msg['message_type']; ?>">
                                    <?php
                                    if ($msg['message_type'] === 'consultation_approval') echo 'Consultation';
                                    elseif ($msg['message_type'] === 'inspection_confirmation') echo 'Inspection';
                                    else echo 'Message';
                                    ?>
                                </span>
                                <?php echo htmlspecialchars($msg['subject']); ?>
                            </div>
                            <div class="message-preview">
                                <?php echo htmlspecialchars(mb_strimwidth($msg['message'], 0, 150, '...')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 10px; display: block;"></i>
                        <p>No messages yet</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Message Detail -->
            <div class="detail-section">
                <?php if ($message_detail): ?>
                    <div class="detail-header">
                        <h2><i class="fas fa-envelope-open"></i> Message Details</h2>
                    </div>
                    <div class="detail-content">
                        <div class="detail-item">
                            <div class="detail-label">From</div>
                            <div class="detail-value"><?php echo htmlspecialchars($message_detail['admin_name'] ?? 'Admin Team'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Type</div>
                            <div class="detail-value">
                                <span class="message-type-badge <?php echo $message_detail['message_type']; ?>">
                                    <?php
                                    if ($message_detail['message_type'] === 'consultation_approval') echo 'Consultation Approval';
                                    elseif ($message_detail['message_type'] === 'inspection_confirmation') echo 'Inspection Confirmation';
                                    else echo 'General Message';
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Subject</div>
                            <div class="detail-value"><?php echo htmlspecialchars($message_detail['subject']); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value"><?php echo date('M d, Y H:i', strtotime($message_detail['created_at'])); ?></div>
                        </div>

                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">

                        <div class="detail-item">
                            <div class="detail-label">Message</div>
                            <div class="detail-value">
                                <?php echo nl2br(htmlspecialchars($message_detail['message'])); ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: #999;">
                        <i class="fas fa-arrow-left" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        <p>Select a message to read</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>