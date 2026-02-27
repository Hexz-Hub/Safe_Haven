<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$db = new \Database();
$conn = $db->getConnection();

$message = '';
$error = '';

// Handle approve/reject actions
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $action = $_POST['action']; // 'approved' or 'rejected'
    $admin_response = trim($_POST['admin_response']);

    $update_query = "UPDATE media_requests 
                     SET status = :status, 
                         admin_response = :admin_response,
                         responded_by = :admin_id,
                         responded_at = NOW(),
                         updated_at = NOW() 
                     WHERE id = :id";
    $stmt = $conn->prepare($update_query);
    $stmt->bindParam(':status', $action);
    $stmt->bindParam(':admin_response', $admin_response);
    $stmt->bindParam(':admin_id', $_SESSION['admin_id']);
    $stmt->bindParam(':id', $request_id);

    if ($stmt->execute()) {
        // Get request details for email
        $details_query = "SELECT * FROM media_requests WHERE id = :id";
        $details_stmt = $conn->prepare($details_query);
        $details_stmt->bindParam(':id', $request_id);
        $details_stmt->execute();
        $request_details = $details_stmt->fetch(PDO::FETCH_ASSOC);

        // Send email notification to user
        $to = $request_details['email'];
        $service_name = str_replace('_', ' ', ucwords($request_details['service_type'], '_'));

        if ($action === 'approved') {
            $subject = "Media Service Request Approved - SafeHaven";
            $email_message = "Dear {$request_details['name']},\n\n";
            $email_message .= "Great news! Your media service request has been APPROVED.\n\n";
            $email_message .= "Service: $service_name\n";
            $email_message .= "Scheduled Date: {$request_details['preferred_date']}\n";
            $email_message .= "Scheduled Time: {$request_details['preferred_time']}\n\n";
            $email_message .= "Response from our team:\n";
            $email_message .= "$admin_response\n\n";
            $email_message .= "We will contact you shortly to finalize the details.\n\n";
        } else {
            $subject = "Media Service Request Update - SafeHaven";
            $email_message = "Dear {$request_details['name']},\n\n";
            $email_message .= "Thank you for your interest in our media services.\n\n";
            $email_message .= "Regarding your request for: $service_name\n";
            $email_message .= "Requested Date: {$request_details['preferred_date']}\n";
            $email_message .= "Requested Time: {$request_details['preferred_time']}\n\n";
            $email_message .= "Response from our team:\n";
            $email_message .= "$admin_response\n\n";
            $email_message .= "Please feel free to submit a new request or contact us directly for alternatives.\n\n";
        }

        $email_message .= "Best regards,\nSafeHaven Team\n";
        $email_message .= "Email: info@safehaven.ng\n";
        $email_message .= "Phone: +234 xxx xxx xxxx";

        // Send email using PHPMailer
        require_once 'config/email.php';
        sendEmail($to, $subject, $email_message, $request_details['name']);

        $message = "Request " . ($action === 'approved' ? 'approved' : 'rejected') . " and email sent to user successfully!";
    } else {
        $error = "Failed to update request status.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $delete_query = "DELETE FROM media_requests WHERE id = :id";
    $stmt = $conn->prepare($delete_query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $message = "Media request deleted successfully!";
    } else {
        $error = "Failed to delete media request.";
    }
}

// Get filter
$status_filter = $_GET['status'] ?? 'all';

// Get all media requests
$media_requests = [];
try {
    if ($status_filter === 'all') {
        $query = "SELECT mr.*, u.name as user_name, a.username as admin_name 
                  FROM media_requests mr 
                  LEFT JOIN users u ON mr.user_id = u.id 
                  LEFT JOIN admin_users a ON mr.responded_by = a.id 
                  ORDER BY mr.created_at DESC";
        $stmt = $conn->prepare($query);
    } else {
        $query = "SELECT mr.*, u.name as user_name, a.username as admin_name 
                  FROM media_requests mr 
                  LEFT JOIN users u ON mr.user_id = u.id 
                  LEFT JOIN admin_users a ON mr.responded_by = a.id 
                  WHERE mr.status = :status
                  ORDER BY mr.created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':status', $status_filter);
    }
    $stmt->execute();
    $media_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
    $media_requests = [];
}

// Get counts for badges
$count_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM media_requests";
$count_stmt = $conn->prepare($count_query);
$count_stmt->execute();
$counts = $count_stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Media Requests | Admin Panel</title>
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

        /* --- HEADER --- */
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

        /* --- CONTAINER --- */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* --- MESSAGES --- */
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* --- FILTERS --- */
        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: var(--primary-color);
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn.active {
            background: var(--accent-color);
            color: var(--primary-color);
            border-color: var(--accent-color);
            font-weight: bold;
        }

        .filter-btn:hover {
            border-color: var(--accent-color);
        }

        .badge {
            background: #e74c3c;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .filter-btn.active .badge {
            background: var(--primary-color);
        }

        /* --- REQUEST CARDS --- */
        .requests-grid {
            display: grid;
            gap: 20px;
        }

        .request-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--accent-color);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .request-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .request-title {
            font-size: 20px;
            color: var(--primary-color);
            font-weight: 600;
        }

        /* --- SERVICE BADGES --- */
        .service-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }

        .service-event_coverage {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .service-content_creation {
            background: #e3f2fd;
            color: #1565c0;
        }

        .service-social_media {
            background: #f3e5f5;
            color: #6a1b9a;
        }

        .service-digital_marketing {
            background: #fff3e0;
            color: #e65100;
        }

        .service-google_ads {
            background: #fce4ec;
            color: #c2185b;
        }

        .service-email_marketing {
            background: #e0f2f1;
            color: #00695c;
        }

        /* --- STATUS BADGES --- */
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
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

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* --- REQUEST DETAILS --- */
        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 15px 0;
            padding: 15px;
            background: var(--light-gray);
            border-radius: 6px;
        }

        .detail-item {
            font-size: 14px;
        }

        .detail-label {
            color: #7f8c8d;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .detail-value {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* --- MESSAGE BOX --- */
        .message-box {
            background: var(--light-gray);
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border-left: 4px solid var(--accent-color);
        }

        .message-box p {
            color: var(--primary-color);
            line-height: 1.6;
            font-size: 14px;
        }

        /* --- ACTION FORM --- */
        .action-form {
            margin-top: 20px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 6px;
        }

        .action-form textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            margin-bottom: 15px;
        }

        .action-form textarea:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* --- BUTTONS --- */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #6c757d;
            color: white;
            padding: 5px 12px;
            font-size: 12px;
        }

        .btn-delete:hover {
            background: #5a6268;
        }

        /* --- ADMIN RESPONSE --- */
        .admin-response {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 2px solid var(--accent-color);
        }

        .admin-response-label {
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-response-text {
            color: var(--primary-color);
            line-height: 1.6;
            font-size: 14px;
        }

        /* --- TIMESTAMP --- */
        .timestamp {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* --- EMPTY STATE --- */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
        }

        .empty-state i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: #7f8c8d;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .request-details {
                grid-template-columns: 1fr;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-btn {
                text-align: center;
                justify-content: center;
            }

            .timestamp {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-video"></i> Media Service Requests</h1>
            <a href="admin-dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>
        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <a href="?status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All
                <span class="badge"><?php echo $counts['total']; ?></span>
            </a>
            <a href="?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pending
                <span class="badge"><?php echo $counts['pending']; ?></span>
            </a>
            <a href="?status=approved" class="filter-btn <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i> Approved
                <span class="badge"><?php echo $counts['approved']; ?></span>
            </a>
            <a href="?status=rejected" class="filter-btn <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">
                <i class="fas fa-times"></i> Rejected
                <span class="badge"><?php echo $counts['rejected']; ?></span>
            </a>
        </div>

        <div class="requests-grid">
            <?php if (empty($media_requests)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No media requests found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($media_requests as $request): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div>
                                <div class="request-title"><?php echo htmlspecialchars($request['name']); ?></div>
                                <div style="margin-top: 8px;">
                                    <span class="service-badge service-<?php echo $request['service_type']; ?>">
                                        <?php echo str_replace('_', ' ', ucwords($request['service_type'], '_')); ?>
                                    </span>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $request['status']; ?>">
                                <?php echo $request['status']; ?>
                            </span>
                        </div>

                        <div class="request-details">
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($request['email']); ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value">
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($request['phone'] ?? 'N/A'); ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Preferred Date</div>
                                <div class="detail-value">
                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($request['preferred_date'])); ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Preferred Time</div>
                                <div class="detail-value">
                                    <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($request['preferred_time'])); ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($request['message']): ?>
                            <div class="message-box">
                                <div class="detail-label" style="margin-bottom: 8px;">
                                    <i class="fas fa-comment-dots"></i> Additional Details
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($request['message'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($request['admin_response']): ?>
                            <div class="admin-response">
                                <div class="admin-response-label">
                                    <i class="fas fa-reply"></i> Admin Response
                                </div>
                                <div class="admin-response-text">
                                    <?php echo nl2br(htmlspecialchars($request['admin_response'])); ?>
                                </div>
                                <?php if ($request['admin_name']): ?>
                                    <div class="timestamp">
                                        Responded by <?php echo htmlspecialchars($request['admin_name']); ?>
                                        on <?php echo date('M d, Y g:i A', strtotime($request['responded_at'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($request['status'] === 'pending'): ?>
                            <div class="action-form">
                                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to perform this action?');">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <textarea name="admin_response" placeholder="Enter your response to the user..." required></textarea>
                                    <div class="action-buttons">
                                        <button type="submit" name="action" value="approved" class="btn btn-approve">
                                            <i class="fas fa-check"></i> Approve & Notify
                                        </button>
                                        <button type="submit" name="action" value="rejected" class="btn btn-reject">
                                            <i class="fas fa-times"></i> Reject & Notify
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="timestamp">
                            <span>
                                <i class="fas fa-clock"></i> Submitted on <?php echo date('M d, Y g:i A', strtotime($request['created_at'])); ?>
                                <?php if ($request['user_name']): ?>
                                    by <?php echo htmlspecialchars($request['user_name']); ?>
                                <?php endif; ?>
                            </span>

                            <a href="?delete=<?php echo $request['id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this request?');"
                                class="btn btn-delete">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>