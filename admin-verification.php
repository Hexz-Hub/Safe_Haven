<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Protect page
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-dashboard.php");
    exit();
}

$id = (int) $_GET['id'];
$db = new \Database();
$conn = $db->getConnection();
$message = '';
$error = '';

// Ensure admin_feedback column exists
try {
    $colStmt = $conn->prepare("SHOW COLUMNS FROM verifications LIKE 'admin_feedback'");
    $colStmt->execute();
    if ($colStmt->rowCount() === 0) {
        $conn->exec("ALTER TABLE verifications ADD COLUMN admin_feedback TEXT NULL");
    }
} catch (Exception $e) {
    // ignore - best-effort schema update
}

// Ensure verification status history table exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS verification_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        verification_id INT NOT NULL,
        status VARCHAR(50) NOT NULL,
        admin_feedback TEXT NULL,
        changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (verification_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    // ignore - best-effort schema update
}

// Handle status updates and optional admin feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'approve') {
        $new_status = 'approved';
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
    } elseif ($action === 'in_progress') {
        $new_status = 'in_progress';
    } else {
        $new_status = 'pending';
    }

    $feedback = isset($_POST['feedback']) ? trim($_POST['feedback']) : null;

    $up = $conn->prepare("UPDATE verifications SET status = :status, admin_feedback = :fb WHERE id = :id");
    $up->bindParam(':status', $new_status);
    $up->bindParam(':fb', $feedback);
    $up->bindParam(':id', $id);
    if ($up->execute()) {
        // Send email notification to user
        require_once 'config/email.php';

        // Get verification details
        $details_query = "SELECT * FROM verifications WHERE id = :id LIMIT 1";
        $details_stmt = $conn->prepare($details_query);
        $details_stmt->bindParam(':id', $id);
        $details_stmt->execute();
        $verification_details = $details_stmt->fetch(PDO::FETCH_ASSOC);

        if ($verification_details) {
            $to = $verification_details['email'];
            $subject = "Verification Request Update - SafeHaven";

            $email_message = "Dear {$verification_details['name']},\n\n";
            $email_message .= "Your verification request has been updated.\n\n";
            $email_message .= "Request Type: {$verification_details['request_type']}\n";
            $email_message .= "Status: " . ucfirst($new_status) . "\n\n";

            if (!empty($verification_details['concern'])) {
                $email_message .= "Your Concern: {$verification_details['concern']}\n\n";
            }

            $property_location = $verification_details['property_location'] ?? '';
            $property_link = $verification_details['property_link'] ?? '';

            if (!empty($property_location)) {
                $email_message .= "Property Location: {$property_location}\n\n";
            }

            if (!empty($property_link)) {
                $email_message .= "Listing Link: {$property_link}\n\n";
            }

            if (!empty($verification_details['message'])) {
                $email_message .= "Your Message:\n{$verification_details['message']}\n\n";
            }

            if ($feedback) {
                $email_message .= "Admin Response:\n{$feedback}\n\n";
            }

            if ($new_status === 'approved') {
                $email_message .= "Congratulations! Your verification request has been approved.\n";
                $email_message .= "You can now access premium features on SafeHaven.\n\n";
            } elseif ($new_status === 'rejected') {
                $email_message .= "Unfortunately, your verification request could not be approved at this time.\n";
                $email_message .= "Please review the admin response above for more details.\n\n";
            } elseif ($new_status === 'in_progress') {
                $email_message .= "Your verification request is currently being processed.\n";
                $email_message .= "We will notify you once we have more information.\n\n";
            }

            $email_message .= "Best regards,\nSafeHaven Team\n";
            $email_message .= "Email: info@safehaven.ng\n";
            $email_message .= "Phone: +234 xxx xxx xxxx";

            sendEmail($to, $subject, $email_message, $verification_details['name']);
        }

        // Record status change history
        try {
            $hist = $conn->prepare("INSERT INTO verification_status_history (verification_id, status, admin_feedback) VALUES (:vid, :status, :fb)");
            $hist->bindParam(':vid', $id);
            $hist->bindParam(':status', $new_status);
            $hist->bindParam(':fb', $feedback);
            $hist->execute();
        } catch (Exception $e) {
            // ignore - best-effort history
        }

        $message = 'Request status updated to ' . htmlspecialchars($new_status) . ' and email sent.';
    } else {
        $error = 'Failed to update status.';
    }
    // Refresh the verification data after update
    $q = $conn->prepare("SELECT * FROM verifications WHERE id = :id LIMIT 1");
    $q->bindParam(':id', $id);
    $q->execute();
    $ver = $q->fetch(PDO::FETCH_ASSOC);
}

// Fetch verification
$q = $conn->prepare("SELECT * FROM verifications WHERE id = :id LIMIT 1");
$q->bindParam(':id', $id);
$q->execute();
$ver = $q->fetch(PDO::FETCH_ASSOC);
if (!$ver) {
    header("Location: admin-dashboard.php");
    exit();
}

// Fetch status history
$history = [];
try {
    $h = $conn->prepare("SELECT status, admin_feedback, changed_at FROM verification_status_history WHERE verification_id = :id ORDER BY changed_at DESC");
    $h->bindParam(':id', $id);
    $h->execute();
    $history = $h->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $history = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Request #<?php echo $id; ?> - Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #EEE9F8;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .card {
            padding: 14px;
            border-radius: 6px;
            background: #fafafa;
            border: 1px solid #eee;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 10px 14px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            font-weight: 600;
        }

        .btn-approve {
            background: #28a745;
        }

        .btn-reject {
            background: #dc3545;
        }

        .btn-wip {
            background: #ffc107;
            color: #111;
        }

        .back {
            display: inline-block;
            margin-left: 8px;
            padding: 8px 12px;
            background: #ddd;
            color: #333;
            border-radius: 6px;
            text-decoration: none;
        }

        .note {
            margin-top: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Verification Request — ID #<?php echo $id; ?></h2>
            <div>
                <a href="admin-dashboard.php" class="back">← Back to Dashboard</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div style="padding:12px;background:#d4edda;color:#155724;border-left:4px solid #28a745;border-radius:6px;margin-bottom:12px;"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="padding:12px;background:#f8d7da;color:#721c24;border-left:4px solid #dc3545;border-radius:6px;margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="meta">
            <div class="card">
                <strong>Name</strong>
                <div><?php echo htmlspecialchars($ver['name']); ?></div>
            </div>
            <div class="card">
                <strong>Email</strong>
                <div><?php echo htmlspecialchars($ver['email']); ?></div>
            </div>
            <div class="card">
                <strong>Phone</strong>
                <div><?php echo htmlspecialchars($ver['phone']); ?></div>
            </div>
            <div class="card">
                <strong>Request Type</strong>
                <div><?php echo htmlspecialchars($ver['request_type']); ?></div>
            </div>
        </div>

        <?php if (!empty($ver['concern'])): ?>
            <div style="margin-top:16px;" class="card">
                <strong>Biggest Concern</strong>
                <p class="note"><?php echo nl2br(htmlspecialchars($ver['concern'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($ver['property_location'] ?? '')): ?>
            <div style="margin-top:12px;" class="card">
                <strong>Property Location</strong>
                <p class="note"><?php echo nl2br(htmlspecialchars($ver['property_location'] ?? '')); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($ver['property_link'] ?? '')): ?>
            <div style="margin-top:12px;" class="card">
                <strong>Listing Link</strong>
                <p class="note"><a href="<?php echo htmlspecialchars($ver['property_link'] ?? ''); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($ver['property_link'] ?? ''); ?></a></p>
            </div>
        <?php endif; ?>

        <div style="margin-top:16px;" class="card">
            <strong>Message</strong>
            <p class="note"><?php echo nl2br(htmlspecialchars($ver['message'])); ?></p>
        </div>

        <div style="margin-top:12px;" class="card">
            <strong>Status</strong>
            <div style="margin-top:8px;font-weight:700;"><?php echo htmlspecialchars($ver['status']); ?></div>
            <form method="POST" style="margin-top:12px;">
                <label for="feedback" style="display:block;margin-bottom:8px;font-weight:600;color:#444">Admin feedback (optional)</label>
                <textarea id="feedback" name="feedback" rows="4" style="width:100%;padding:10px;border-radius:6px;border:1px solid #e6e6e6;"><?php echo isset($ver['admin_feedback']) ? htmlspecialchars($ver['admin_feedback']) : ''; ?></textarea>
                <div class="actions" style="margin-top:10px">
                    <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                    <button type="submit" name="action" value="in_progress" class="btn btn-wip">In Progress</button>
                    <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                </div>
            </form>
        </div>

        <div style="margin-top:12px;" class="card">
            <strong>Status History</strong>
            <?php if (!empty($history)): ?>
                <ul style="margin-top:10px;padding-left:18px;">
                    <?php foreach ($history as $h): ?>
                        <li style="margin-bottom:8px;">
                            <strong><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $h['status']))); ?></strong>
                            <span style="color:#666;">— <?php echo date('M d, Y H:i', strtotime($h['changed_at'])); ?></span>
                            <?php if (!empty($h['admin_feedback'])): ?>
                                <div class="note" style="margin-top:4px;"><?php echo nl2br(htmlspecialchars($h['admin_feedback'])); ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="note" style="margin-top:8px;">No history yet.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top:18px;color:#888;font-size:0.9rem;">Submitted: <?php echo date('M d, Y H:i', strtotime($ver['created_at'])); ?></div>
        <?php if (!empty($ver['admin_feedback'])): ?>
            <div style="margin-top:12px;" class="card">
                <strong>Admin Feedback</strong>
                <p class="note"><?php echo nl2br(htmlspecialchars($ver['admin_feedback'])); ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>