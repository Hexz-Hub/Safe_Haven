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

// Get all inquiries
$inquiries = [];
$error = '';
$inquiry_detail = null;

try {
    $query = "SELECT i.id, i.name, i.email, i.phone, i.property_id, i.message, i.status, i.created_at, p.title as property_title 
              FROM inquiries i 
              LEFT JOIN properties p ON i.property_id = p.id 
              ORDER BY i.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
    $inquiries = [];
}

// Get inquiry detail if viewing
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $inquiry_id = $_GET['view'];
    $detail_query = "SELECT i.id, i.name, i.email, i.phone, i.property_id, i.message, i.status, i.created_at, p.title as property_title 
                     FROM inquiries i 
                     LEFT JOIN properties p ON i.property_id = p.id 
                     WHERE i.id = :id";
    $detail_stmt = $conn->prepare($detail_query);
    $detail_stmt->bindParam(':id', $inquiry_id);
    $detail_stmt->execute();
    $inquiry_detail = $detail_stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries | Admin Dashboard</title>
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
            max-width: 1200px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

        .status.new {
            background: #d4edda;
            color: #155724;
        }

        .status.read {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status.responded {
            background: #e2e3e5;
            color: #383d41;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        /* Detail Card Styles */
        .layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .detail-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .detail-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .detail-content {
            padding: 25px;
        }

        .detail-item {
            margin-bottom: 20px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 6px;
            font-size: 0.95rem;
        }

        .detail-value {
            color: #666;
            padding: 10px 12px;
            background: #f9f9f9;
            border-left: 3px solid var(--accent-color);
            border-radius: 4px;
            line-height: 1.5;
        }

        .detail-value.message {
            min-height: 100px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .back-link {
            display: inline-block;
            color: var(--primary-color);
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .back-link:hover {
            color: var(--accent-color);
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 1024px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-envelope"></i> Inquiries</h1>
            <a href="admin-dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($inquiry_detail): ?>
            <!-- Detail View -->
            <a href="admin-inquiries.php" class="back-link">← Back to All Inquiries</a>

            <div class="layout">
                <!-- Left: Inquiry Details -->
                <div class="detail-card">
                    <div class="detail-header">
                        <i class="fas fa-info-circle"></i> Inquiry Details
                    </div>
                    <div class="detail-content">
                        <div class="detail-item">
                            <div class="detail-label">Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($inquiry_detail['name']); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?php echo htmlspecialchars($inquiry_detail['email']); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value"><?php echo htmlspecialchars($inquiry_detail['phone'] ?? 'Not provided'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Property</div>
                            <div class="detail-value"><?php echo htmlspecialchars($inquiry_detail['property_title'] ?? 'Not specified'); ?></div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Status</div>
                            <div class="detail-value">
                                <span class="status <?php echo strtolower($inquiry_detail['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($inquiry_detail['status'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Date</div>
                            <div class="detail-value"><?php echo date('M d, Y H:i', strtotime($inquiry_detail['created_at'])); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Right: Message -->
                <div class="detail-card">
                    <div class="detail-header">
                        <i class="fas fa-message"></i> Message
                    </div>
                    <div class="detail-content">
                        <div class="detail-item">
                            <div class="detail-value message"><?php echo nl2br(htmlspecialchars($inquiry_detail['message'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif (count($inquiries) > 0): ?>
            <!-- List View -->
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Property</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inq): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inq['name']); ?></td>
                            <td><?php echo htmlspecialchars($inq['email']); ?></td>
                            <td><?php echo htmlspecialchars($inq['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($inq['property_title'] ?? 'Not specified'); ?></td>
                            <td>
                                <span class="status <?php echo strtolower($inq['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($inq['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($inq['created_at'])); ?></td>
                            <td>
                                <a href="?view=<?php echo $inq['id']; ?>" style="color: var(--primary-color); font-weight: 600; text-decoration: none; padding: 5px 10px; background: var(--accent-color); border-radius: 4px; font-size: 12px;">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-message">
                <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 10px; display: block;"></i>
                <p>No inquiries found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>