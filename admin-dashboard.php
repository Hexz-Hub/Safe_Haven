<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Get statistics
$db = new \Database();
$conn = $db->getConnection();

// Count properties
$query = "SELECT COUNT(*) as total FROM properties";
$stmt = $conn->prepare($query);
$stmt->execute();
$total_properties = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count news
$query = "SELECT COUNT(*) as total FROM news";
$stmt = $conn->prepare($query);
$stmt->execute();
$total_news = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count available properties
$query = "SELECT COUNT(*) as total FROM properties WHERE status = 'available'";
$stmt = $conn->prepare($query);
$stmt->execute();
$available_properties = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count sold or rented properties
$query = "SELECT COUNT(*) as total FROM properties WHERE status IN ('sold','rented')";
$stmt = $conn->prepare($query);
$stmt->execute();
$sold_rented_properties = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count inquiries
$query = "SELECT COUNT(*) as total FROM inquiries WHERE status = 'new'";
$stmt = $conn->prepare($query);
$stmt->execute();
$new_inquiries = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Count users (if table exists)
$total_users = 0;
try {
    $query = "SELECT COUNT(*) as total FROM users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
    // table may not exist
}

// Recent properties
$query = "SELECT * FROM properties ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->execute();
$recent_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pending verifications
$pending_verifications = 0;
$recent_verifications = [];
try {
    $vquery = "SELECT COUNT(*) as total FROM verifications WHERE status = 'pending'";
    $vstmt = $conn->prepare($vquery);
    $vstmt->execute();
    $pending_verifications = $vstmt->fetch(PDO::FETCH_ASSOC)['total'];

    $vlist = "SELECT * FROM verifications ORDER BY created_at DESC LIMIT 5";
    $vlist_stmt = $conn->prepare($vlist);
    $vlist_stmt->execute();
    $recent_verifications = $vlist_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // table may not exist
}

// Recent inquiries
$recent_inquiries = [];
try {
    $query = "SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $recent_inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // table may not exist
}

// Media requests count
$pending_media_requests = 0;
try {
    $query = "SELECT COUNT(*) as total FROM media_requests WHERE status = 'pending'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $pending_media_requests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
    // table may not exist
}

// Revenue calculation (from properties marked as sold)
$total_revenue = 0;
try {
    $query = "SELECT SUM(sold_price) as revenue FROM properties WHERE status = 'sold' AND sold_price IS NOT NULL";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_revenue = $result['revenue'] ?? 0;
} catch (Exception $e) {
    // field may not exist
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4B2C6B;
            --primary-dark: #2D1B47;
            --darkest-green: #241338;
            --accent-color: #D4AF37;
            --gold-light: #E8C766;
            --gold-dark: #B8941F;
            --white: #ffffff;
            --off-white: #f8f6ff;
            --light-gray: #f5f3ff;
            --text-light: #f8f9fa;
            --text-muted: #c5bbd8;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --gradient-green: linear-gradient(135deg, #4B2C6B 0%, #6B4E8C 100%);
            --gradient-gold: linear-gradient(135deg, #D4AF37 0%, #E8C766 100%);
            --gradient-dark: linear-gradient(135deg, #2D1B47 0%, #4B2C6B 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(145deg, var(--darkest-green), var(--primary-dark));
            color: var(--text-light);
            min-height: 100vh;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: rgba(45, 27, 71, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid rgba(212, 175, 55, 0.18);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: var(--transition);
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.18);
            text-align: center;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: var(--accent-color);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
        }

        .sidebar-logo:hover {
            color: var(--gold-light);
        }

        .sidebar-logo i {
            font-size: 1.8rem;
        }

        .sidebar-logo img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.08);
            padding: 4px;
        }

        .admin-info {
            padding: 20px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            text-align: center;
        }

        .admin-avatar {
            width: 70px;
            height: 70px;
            background: var(--gradient-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.8rem;
            color: var(--primary-dark);
            font-weight: 700;
        }

        .admin-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 5px;
        }

        .admin-role {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-title {
            color: var(--accent-color);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 20px 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.12);
        }

        .nav-links {
            list-style: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
            border-left: 4px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(212, 175, 55, 0.12);
            color: var(--accent-color);
            border-left-color: var(--accent-color);
        }

        .nav-link i {
            width: 20px;
            font-size: 1.1rem;
        }

        .nav-link span {
            flex-grow: 1;
        }

        .nav-badge {
            background: var(--accent-color);
            color: var(--primary-dark);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 0;
            min-height: 100vh;
        }

        .top-bar {
            background: rgba(45, 27, 71, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 20px 30px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-title p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 5px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-btn {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: var(--accent-color);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .notification-btn:hover {
            background: rgba(212, 175, 55, 0.2);
            transform: translateY(-2px);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .logout-btn {
            background: var(--gradient-gold);
            color: var(--primary-dark);
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
        }

        /* Content Area */
        .content-area {
            padding: 30px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 102, 204, 0.2);
            border-radius: var(--radius);
            padding: 25px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-color);
            box-shadow: var(--shadow-lg);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-gold);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(0, 102, 204, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .stat-trend {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: rgba(40, 167, 69, 0.15);
            color: #90ee90;
        }

        .stat-trend.down {
            background: rgba(220, 53, 69, 0.15);
            color: #ff8a8a;
        }

        .stat-content h3 {
            color: var(--text-muted);
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1;
            margin-bottom: 10px;
        }

        .stat-subtext {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Quick Actions */
        .quick-actions-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 102, 204, 0.2);
            border-radius: var(--radius);
            padding: 30px;
            margin-bottom: 40px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .action-btn {
            background: rgba(0, 102, 204, 0.1);
            border: 1px solid rgba(0, 102, 204, 0.3);
            border-radius: var(--radius);
            padding: 20px;
            text-decoration: none;
            color: var(--text-light);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .action-btn:hover {
            background: rgba(0, 102, 204, 0.2);
            border-color: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0, 102, 204, 0.2);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-gold);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--primary-dark);
        }

        .action-content h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--white);
        }

        .action-content p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Tables */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        @media (max-width: 1200px) {
            .tables-grid {
                grid-template-columns: 1fr;
            }
        }

        .table-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 102, 204, 0.2);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(0, 102, 204, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--accent-color);
        }

        .view-all {
            color: var(--accent-color);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .view-all:hover {
            color: var(--gold-light);
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: rgba(31, 43, 31, 0.7);
        }

        .data-table th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: var(--accent-color);
            border-bottom: 1px solid rgba(0, 102, 204, 0.2);
            white-space: nowrap;
        }

        .data-table td {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-light);
        }

        .data-table tbody tr {
            transition: var(--transition);
        }

        .data-table tbody tr:hover {
            background: rgba(0, 102, 204, 0.05);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-available {
            background: rgba(40, 167, 69, 0.2);
            color: #90ee90;
        }

        .status-sold {
            background: rgba(220, 53, 69, 0.2);
            color: #ff8a8a;
        }

        .status-rented {
            background: rgba(255, 193, 7, 0.2);
            color: #ffd700;
        }

        .status-pending {
            background: rgba(23, 162, 184, 0.2);
            color: #87ceeb;
        }

        .status-approved {
            background: rgba(40, 167, 69, 0.2);
            color: #90ee90;
        }

        .status-rejected {
            background: rgba(220, 53, 69, 0.2);
            color: #ff8a8a;
        }

        .status-in_progress {
            background: rgba(255, 193, 7, 0.2);
            color: #ffd700;
        }

        .btn-sm {
            padding: 6px 12px;
            background: rgba(0, 102, 204, 0.1);
            color: var(--accent-color);
            border: 1px solid rgba(0, 102, 204, 0.3);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .btn-sm:hover {
            background: rgba(0, 102, 204, 0.2);
            border-color: var(--accent-color);
        }

        /* Footer */
        .admin-footer {
            padding: 20px 30px;
            border-top: 1px solid rgba(0, 102, 204, 0.2);
            color: var(--text-muted);
            font-size: 0.9rem;
            text-align: center;
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            background: var(--gradient-gold);
            color: var(--primary-dark);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: var(--radius-sm);
            font-size: 1.3rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .mobile-toggle:hover {
            transform: scale(1.05);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1001;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .content-area {
                padding: 20px;
            }

            .tables-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .top-bar {
                padding: 15px 20px;
            }

            .page-title h1 {
                font-size: 1.5rem;
            }
        }

        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="admin-dashboard.php" class="sidebar-logo">
                    <img src="uploads/logo.png" alt="SafeHaven Logo" onerror="this.style.display='none'">
                    <span>SAFEHAVEN</span>
                </a>
            </div>

            <div class="admin-info">
                <div class="admin-avatar">
                    <?php
                    $name = $_SESSION['admin_name'] ?? 'Admin';
                    echo strtoupper(substr($name, 0, 1));
                    ?>
                </div>
                <div class="admin-name"><?php echo htmlspecialchars($name); ?></div>
                <div class="admin-role">Administrator</div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-title">Main Menu</div>
                <ul class="nav-links">
                    <li>
                        <a href="admin-dashboard.php" class="nav-link active">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-properties.php" class="nav-link">
                            <i class="fas fa-home"></i>
                            <span>Properties</span>
                            <span class="nav-badge"><?php echo $total_properties; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-news.php" class="nav-link">
                            <i class="fas fa-newspaper"></i>
                            <span>News & Updates</span>
                            <span class="nav-badge"><?php echo $total_news; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-virtual-tours.php" class="nav-link">
                            <i class="fas fa-video"></i>
                            <span>Virtual Tours</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-verifications.php" class="nav-link">
                            <i class="fas fa-shield-alt"></i>
                            <span>Verifications</span>
                            <?php if ($pending_verifications > 0): ?>
                                <span class="nav-badge" style="background: var(--warning);"><?php echo $pending_verifications; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="admin-inquiries.php" class="nav-link">
                            <i class="fas fa-envelope"></i>
                            <span>Inquiries</span>
                            <?php if ($new_inquiries > 0): ?>
                                <span class="nav-badge" style="background: var(--danger);"><?php echo $new_inquiries; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="admin-consultations.php" class="nav-link">
                            <i class="fas fa-calendar-check"></i>
                            <span>Consultations</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-inspections.php" class="nav-link">
                            <i class="fas fa-binoculars"></i>
                            <span>Inspections</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-media-requests.php" class="nav-link">
                            <i class="fas fa-video"></i>
                            <span>Media Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-users.php" class="nav-link">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                            <span class="nav-badge"><?php echo $total_users; ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-settings.php" class="nav-link">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>

                <div class="nav-title" style="margin-top: 30px;">Quick Links</div>
                <ul class="nav-links">
                    <li>
                        <a href="index.php" target="_blank" class="nav-link">
                            <i class="fas fa-external-link-alt"></i>
                            <span>View Website</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin-profile.php" class="nav-link">
                            <i class="fas fa-user-circle"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Mobile overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1>Dashboard Overview</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>! Here's what's happening.</p>
                </div>

                <div class="top-actions">
                    <button class="mobile-toggle" id="mobileToggle">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <?php if ($new_inquiries > 0 || $pending_verifications > 0): ?>
                            <span class="notification-badge">
                                <?php echo $new_inquiries + $pending_verifications; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <a href="admin-logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card fade-in">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <span class="stat-trend">+12%</span>
                        </div>
                        <div class="stat-content">
                            <h3>Total Properties</h3>
                            <div class="stat-number"><?php echo $total_properties; ?></div>
                            <div class="stat-subtext"><?php echo $available_properties; ?> available</div>
                        </div>
                    </div>

                    <div class="stat-card fade-in delay-1">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <span class="stat-trend">+5%</span>
                        </div>
                        <div class="stat-content">
                            <h3>Revenue Generated</h3>
                            <div class="stat-number">₦<?php echo number_format($total_revenue); ?></div>
                            <div class="stat-subtext">From <?php echo $sold_rented_properties; ?> sales</div>
                        </div>
                    </div>

                    <div class="stat-card fade-in delay-2">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <?php if ($pending_verifications > 0): ?>
                                <span class="stat-trend down"><?php echo $pending_verifications; ?> pending</span>
                            <?php else: ?>
                                <span class="stat-trend">All clear</span>
                            <?php endif; ?>
                        </div>
                        <div class="stat-content">
                            <h3>Verification Requests</h3>
                            <div class="stat-number"><?php echo $pending_verifications; ?></div>
                            <div class="stat-subtext">Pending review</div>
                        </div>
                    </div>

                    <div class="stat-card fade-in delay-3">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="stat-trend">+8%</span>
                        </div>
                        <div class="stat-content">
                            <h3>Active Users</h3>
                            <div class="stat-number"><?php echo $total_users; ?></div>
                            <div class="stat-subtext">Registered users</div>
                        </div>
                    </div>

                    <div class="stat-card fade-in delay-4">
                        <div class="stat-header">
                            <div class="stat-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <?php if ($pending_media_requests > 0): ?>
                                <span class="stat-trend down"><?php echo $pending_media_requests; ?> new</span>
                            <?php else: ?>
                                <span class="stat-trend">No requests</span>
                            <?php endif; ?>
                        </div>
                        <div class="stat-content">
                            <h3>Media Requests</h3>
                            <div class="stat-number"><?php echo $pending_media_requests; ?></div>
                            <div class="stat-subtext">Pending approval</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions-section fade-in delay-5">
                    <div class="section-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="actions-grid">
                        <a href="admin-properties.php?action=add" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-content">
                                <h3>Add New Property</h3>
                                <p>List a new property for sale or rent</p>
                            </div>
                        </a>

                        <a href="admin-news.php?action=add" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-newspaper"></i>
                            </div>
                            <div class="action-content">
                                <h3>Create News Article</h3>
                                <p>Publish news or blog post</p>
                            </div>
                        </a>

                        <a href="admin-virtual-tours.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="action-content">
                                <h3>Manage Virtual Tours</h3>
                                <p>Add or edit property video tours</p>
                            </div>
                        </a>

                        <a href="admin-verifications.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="action-content">
                                <h3>Review Verifications</h3>
                                <p><?php echo $pending_verifications; ?> pending requests</p>
                            </div>
                        </a>

                        <a href="admin-media-requests.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <div class="action-content">
                                <h3>Media Requests</h3>
                                <p><?php echo $pending_media_requests; ?> pending approval</p>
                            </div>
                        </a>

                        <a href="admin-inquiries.php" class="action-btn">
                            <div class="action-icon">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            <div class="action-content">
                                <h3>Manage Inquiries</h3>
                                <p><?php echo $new_inquiries; ?> new messages</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Tables Grid -->
                <div class="tables-grid">
                    <!-- Recent Properties -->
                    <div class="table-section fade-in">
                        <div class="table-header">
                            <h2>Recent Properties</h2>
                            <a href="admin-properties.php" class="view-all">View All →</a>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Location</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_properties) > 0): ?>
                                        <?php foreach ($recent_properties as $property): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars(mb_strimwidth($property['title'], 0, 30, '...')); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars(mb_strimwidth($property['location'], 0, 20, '...')); ?></td>
                                                <td>₦<?php echo number_format($property['price']); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $property['status']; ?>">
                                                        <?php echo ucfirst($property['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="admin-properties.php?action=edit&id=<?php echo $property['id']; ?>" class="btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                                No properties found. <a href="admin-properties.php?action=add" style="color: var(--accent-color);">Add your first property</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Verification Requests -->
                    <div class="table-section fade-in delay-1">
                        <div class="table-header">
                            <h2>Recent Verification Requests</h2>
                            <a href="admin-verifications.php" class="view-all">View All →</a>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_verifications) > 0): ?>
                                        <?php foreach ($recent_verifications as $v): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($v['name']); ?></strong>
                                                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                                                        <?php echo htmlspecialchars($v['email']); ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($v['request_type']); ?></td>
                                                <td>
                                                    <?php $status = $v['status'] ?? 'pending'; ?>
                                                    <span class="status-badge status-<?php echo htmlspecialchars($status); ?>">
                                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $status))); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d', strtotime($v['created_at'])); ?></td>
                                                <td>
                                                    <a href="admin-verification.php?id=<?php echo $v['id']; ?>" class="btn-sm">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                                No verification requests pending.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="admin-footer">
                    <p>© <?php echo date('Y'); ?> SafeHaven. All rights reserved. | System Version 2.1.0</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Mobile sidebar toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });

        // Notification bell animation
        const notificationBtn = document.querySelector('.notification-btn');
        if (notificationBtn.querySelector('.notification-badge')) {
            // Add pulse animation if there are notifications
            notificationBtn.style.animation = 'pulse 2s infinite';

            // Create CSS for pulse animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes pulse {
                    0% { box-shadow: 0 0 0 0 rgba(0, 102, 204, 0.4); }
                    70% { box-shadow: 0 0 0 10px rgba(0, 102, 204, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(0, 102, 204, 0); }
                }
            `;
            document.head.appendChild(style);
        }

        // Auto-refresh page every 5 minutes
        setTimeout(() => {
            window.location.reload();
        }, 300000); // 5 minutes
    </script>
</body>

</html>