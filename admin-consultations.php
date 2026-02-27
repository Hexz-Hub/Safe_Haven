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

$message = '';
$error = '';

// Handle status update
if (isset($_GET['update_status']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $new_status = $_GET['update_status'];

    // Get consultation details before updating
    $details_query = "SELECT c.*, u.name as user_name, u.email FROM consultations c 
                      LEFT JOIN users u ON c.user_id = u.id 
                      WHERE c.id = :id";
    $details_stmt = $conn->prepare($details_query);
    $details_stmt->bindParam(':id', $id);
    $details_stmt->execute();
    $consultation_details = $details_stmt->fetch(PDO::FETCH_ASSOC);

    $update_query = "UPDATE consultations SET status = :status, updated_at = NOW() WHERE id = :id";
    $stmt = $conn->prepare($update_query);
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        // Send email notification to user
        require_once 'config/email.php';

        $to = $consultation_details['email'];
        $subject = "Consultation Request Update - SafeHaven";

        $email_message = "Dear {$consultation_details['user_name']},\n\n";
        $email_message .= "Your consultation request status has been updated.\n\n";
        $email_message .= "Status: " . ucfirst($new_status) . "\n";
        $email_message .= "Preferred Date: {$consultation_details['preferred_date']}\n";
        $email_message .= "Preferred Time: {$consultation_details['preferred_time']}\n\n";

        if ($new_status === 'approved') {
            $email_message .= "Great news! Your consultation request has been approved.\n";
            $email_message .= "We will contact you shortly at {$consultation_details['phone']} to confirm the details.\n\n";
        } elseif ($new_status === 'contacted') {
            $email_message .= "We have contacted you regarding your consultation request.\n";
            $email_message .= "Please check your messages or call us if you have any questions.\n\n";
        }

        $email_message .= "Best regards,\nSafeHaven Team\n";
        $email_message .= "Email: info@safehaven.ng\n";
        $email_message .= "Phone: +234 xxx xxx xxxx";

        sendEmail($to, $subject, $email_message, $consultation_details['user_name']);

        $message = "Status updated and email sent successfully!";
    } else {
        $error = "Failed to update status.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $delete_query = "DELETE FROM consultations WHERE id = :id";
    $stmt = $conn->prepare($delete_query);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        $message = "Consultation request deleted successfully!";
    } else {
        $error = "Failed to delete consultation request.";
    }
}

// Get all consultations
$consultations = [];
try {
    $query = "SELECT c.*, u.name as user_name FROM consultations c 
              LEFT JOIN users u ON c.user_id = u.id 
              ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
    $consultations = [];
}

// Get consultation for detailed view
$consultation_detail = null;
if (isset($_GET['view'])) {
    $id = $_GET['view'];
    $query = "SELECT c.*, u.name as user_name, u.email as user_email FROM consultations c 
              LEFT JOIN users u ON c.user_id = u.id 
              WHERE c.id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $consultation_detail = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle adding admin notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consultation_id'])) {
    $consultation_id = $_POST['consultation_id'];
    $admin_notes = trim($_POST['admin_notes']);
    $send_message = isset($_POST['send_message']) ? 1 : 0;
    $message_content = trim($_POST['message_content'] ?? '');

    $notes_query = "UPDATE consultations SET admin_notes = :admin_notes, updated_at = NOW() WHERE id = :id";
    $stmt = $conn->prepare($notes_query);
    $stmt->bindParam(':admin_notes', $admin_notes);
    $stmt->bindParam(':id', $consultation_id);

    if ($stmt->execute()) {
        // If admin wants to send a message to the user
        if ($send_message && $message_content && isset($_POST['to_user_id'])) {
            $to_user_id = $_POST['to_user_id'];
            $message_subject = "Consultation Request Response";
            $message_type = "consultation_approval";

            $msg_query = "INSERT INTO messages (from_user_id, to_user_id, consultation_id, subject, message, message_type, status) 
                          VALUES (:from_user_id, :to_user_id, :consultation_id, :subject, :message, :message_type, 'unread')";
            $msg_stmt = $conn->prepare($msg_query);
            $msg_stmt->bindParam(':from_user_id', $_SESSION['admin_id']);
            $msg_stmt->bindParam(':to_user_id', $to_user_id);
            $msg_stmt->bindParam(':consultation_id', $consultation_id);
            $msg_stmt->bindParam(':subject', $message_subject);
            $msg_stmt->bindParam(':message', $message_content);
            $msg_stmt->bindParam(':message_type', $message_type);

            if ($msg_stmt->execute()) {
                $message = "Notes saved and message sent to user!";
            }
        } else {
            $message = "Notes saved successfully!";
        }
        // Refresh consultation detail
        $query = "SELECT c.*, u.name as user_name, u.email as user_email, u.id as user_id FROM consultations c 
                  LEFT JOIN users u ON c.user_id = u.id 
                  WHERE c.id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $consultation_id);
        $stmt->execute();
        $consultation_detail = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to save notes.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Requests | Admin Dashboard</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.contacted {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status.completed {
            background: #d4edda;
            color: #155724;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-view {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .btn-mark {
            background: #28a745;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
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
            padding: 8px;
            background: #f9f9f9;
            border-left: 3px solid var(--accent-color);
            padding-left: 12px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
        }

        .btn-save {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-save:hover {
            background: var(--primary-dark);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

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
            <h1><i class="fas fa-calendar-check"></i> Consultation Requests</h1>
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

        <div class="content-wrapper">
            <!-- List Section -->
            <div class="list-section">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($consultations) > 0): ?>
                            <?php foreach ($consultations as $consultation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($consultation['name']); ?></td>
                                    <td><?php echo htmlspecialchars($consultation['subject']); ?></td>
                                    <td>
                                        <span class="status <?php echo strtolower($consultation['status']); ?>">
                                            <?php echo ucfirst($consultation['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($consultation['created_at'])); ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="?view=<?php echo $consultation['id']; ?>" class="btn btn-view">View</a>
                                            <?php if ($consultation['status'] !== 'contacted'): ?>
                                                <a href="?update_status=contacted&id=<?php echo $consultation['id']; ?>" class="btn btn-mark">Mark Contacted</a>
                                            <?php endif; ?>
                                            <a href="?delete=<?php echo $consultation['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this request?');">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 10px; display: block;"></i>
                                        No consultation requests yet
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Detail Section -->
            <div class="detail-section">
                <?php if ($consultation_detail): ?>
                    <div class="detail-header">
                        <h2><i class="fas fa-user"></i> Consultation Details</h2>
                    </div>
                    <div class="detail-content">
                        <div class="detail-item">
                            <div class="detail-label">Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($consultation_detail['name']); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?php echo htmlspecialchars($consultation_detail['email']); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value"><?php echo htmlspecialchars($consultation_detail['phone'] ?? 'Not provided'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Subject</div>
                            <div class="detail-value"><?php echo htmlspecialchars($consultation_detail['subject']); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Preferred Time</div>
                            <div class="detail-value"><?php echo htmlspecialchars($consultation_detail['preferred_time'] ?? 'No preference'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="status <?php echo strtolower($consultation_detail['status']); ?>">
                                    <?php echo ucfirst($consultation_detail['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Message</div>
                            <div class="detail-value"><?php echo nl2br(htmlspecialchars($consultation_detail['message'])); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Submitted</div>
                            <div class="detail-value"><?php echo date('M d, Y H:i', strtotime($consultation_detail['created_at'])); ?></div>
                        </div>

                        <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">

                        <form method="POST">
                            <input type="hidden" name="consultation_id" value="<?php echo $consultation_detail['id']; ?>">
                            <input type="hidden" name="to_user_id" value="<?php echo $consultation_detail['user_id']; ?>">
                            <div class="detail-item">
                                <label class="detail-label">Admin Notes</label>
                                <textarea name="admin_notes" placeholder="Add notes about this consultation request..."><?php echo htmlspecialchars($consultation_detail['admin_notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="detail-item" style="margin-top: 20px; padding: 15px; background: #f0f7ff; border-radius: 6px;">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <input type="checkbox" name="send_message" value="1" id="sendMessage" style="width: auto;">
                                    <span style="font-weight: 600; color: var(--primary-color);">Send Response Message to User</span>
                                </label>
                                <div id="messageBox" style="display: none; margin-top: 15px;">
                                    <label class="detail-label">Message to User</label>
                                    <textarea name="message_content" placeholder="Write your response message to the user (e.g., consultation approved, scheduled for...)..."><?php echo htmlspecialchars($_POST['message_content'] ?? ''); ?></textarea>
                                    <small style="color: #666; display: block; margin-top: 5px;">This message will be sent to the user's dashboard</small>
                                </div>
                            </div>

                            <button type="submit" class="btn-save">Save & Send</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: #999;">
                        <i class="fas fa-arrow-left" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                        Select a consultation to view details
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>

<script>
    // Toggle message box when checkbox is clicked
    document.getElementById('sendMessage').addEventListener('change', function() {
        document.getElementById('messageBox').style.display = this.checked ? 'block' : 'none';
    });
</script>