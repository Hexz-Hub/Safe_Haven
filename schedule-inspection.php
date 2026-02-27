<?php
session_start();
include_once 'config/database.php';
include_once 'config/email.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user-login.php");
    exit();
}

$db = new \Database();
$conn = $db->getConnection();

$form_success = '';
$form_error = '';

if (isset($_SESSION['form_success'])) {
    $form_success = $_SESSION['form_success'];
    unset($_SESSION['form_success']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $property_id = isset($_POST['property_id']) ? $_POST['property_id'] : '';
    $inspection_date = isset($_POST['inspection_date']) ? $_POST['inspection_date'] : '';
    $message = trim($_POST['message']);

    if ($name && $email && $message) {
        try {
            $query = "INSERT INTO inspections (user_id, name, email, phone, property_id, inspection_date, message, status, created_at) 
                      VALUES (:user_id, :name, :email, :phone, :property_id, :inspection_date, :message, 'pending', NOW())";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->bindParam(':inspection_date', $inspection_date);
            $stmt->bindParam(':message', $message);

            if ($stmt->execute()) {
                // Get admin email from settings
                $admin_email = 'spotlightlisting1@gmail.com'; // Default admin email
                try {
                    $settings_query = "SELECT setting_value FROM settings WHERE setting_key = 'company_email'";
                    $settings_stmt = $conn->prepare($settings_query);
                    $settings_stmt->execute();
                    $setting = $settings_stmt->fetch(PDO::FETCH_ASSOC);
                    if ($setting && !empty($setting['setting_value'])) {
                        $admin_email = $setting['setting_value'];
                    }
                } catch (Exception $e) {
                    // Use default if settings table doesn't exist
                }

                // Get property details if property_id is set
                $property_info = '';
                if (!empty($property_id)) {
                    try {
                        $prop_query = "SELECT title, location FROM properties WHERE id = :property_id";
                        $prop_stmt = $conn->prepare($prop_query);
                        $prop_stmt->bindParam(':property_id', $property_id);
                        $prop_stmt->execute();
                        $property = $prop_stmt->fetch(PDO::FETCH_ASSOC);
                        if ($property) {
                            $property_info = "Property: {$property['title']} ({$property['location']})\n";
                        }
                    } catch (Exception $e) {
                        // Continue without property info
                    }
                }

                // Send notification email to admin
                $email_subject = "New Inspection Request";
                $email_body = "You have received a new inspection request:\n\n";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Phone: $phone\n";
                $email_body .= $property_info;
                if (!empty($inspection_date)) {
                    $email_body .= "Requested Date: $inspection_date\n";
                }
                $email_body .= "\nMessage:\n$message\n\n";
                $email_body .= "---\n";
                $email_body .= "Submitted at: " . date('Y-m-d H:i:s') . "\n";
                $email_body .= "View all inspections: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/admin-inspections.php";

                sendEmail($admin_email, $email_subject, $email_body, 'Admin');

                $_SESSION['form_success'] = "Your inspection request has been submitted successfully! We'll confirm the date and time soon.";
                header('Location: schedule-inspection.php?submitted=1');
                exit();
            } else {
                $form_error = "Failed to submit your inspection request. Please try again.";
            }
        } catch (Exception $e) {
            $form_error = "An error occurred. Please try again later.";
        }
    } else {
        $form_error = "Please fill in all required fields.";
    }
}

// Get user info if logged in
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$user_phone = $_SESSION['user_phone'] ?? '';

// Get all properties for dropdown
$properties_query = "SELECT id, title, location FROM properties WHERE status = 'available' ORDER BY title";
$properties_stmt = $conn->query($properties_query);
$properties = $properties_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule a Property Inspection | Spotlight Listings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--light-bg);
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: 0.3s;
        }

        ul {
            list-style: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* --- TOP BAR --- */
        .top-bar {
            background-color: var(--primary-dark);
            color: #ccc;
            padding: 10px 0;
            font-size: 0.85rem;
            border-bottom: 1px solid rgba(0, 102, 204, 0.2);
        }

        .top-bar .container {
            display: flex;
            justify-content: space-between;
        }

        /* --- NAVIGATION --- */
        nav {
            background-color: var(--primary-color);
            padding: 15px 0;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--accent-color);
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-links a {
            color: var(--white);
            text-decoration: none;
        }

        .nav-links a:hover {
            color: var(--accent-color);
        }

        /* --- PAGE HERO --- */
        .page-hero {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)),
                url('https://images.unsplash.com/photo-1560185127-6ed189bf02f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: var(--white);
            padding: 60px 0;
            text-align: center;
        }

        .page-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .page-hero p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* --- FORM CONTAINER --- */
        .form-container {
            max-width: 700px;
            margin: 60px auto;
            padding: 40px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .form-header p {
            color: #666;
            font-size: 1.05rem;
        }

        /* --- FORM STYLES --- */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1rem;
            font-family: inherit;
            transition: 0.3s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* --- SUBMIT BUTTON --- */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent-color), #003366);
            color: var(--primary-color);
            border: none;
            border-radius: 6px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 102, 204, 0.3);
        }

        /* --- SUCCESS/ERROR MESSAGES --- */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        /* --- FOOTER --- */
        footer {
            background: var(--primary-dark);
            color: var(--text-light);
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        footer a {
            color: var(--accent-color);
        }

        footer a:hover {
            text-decoration: underline;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-col h3 {
            margin-bottom: 15px;
            color: var(--accent-color);
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 10px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(0, 102, 204, 0.2);
            padding-top: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .form-container {
                margin: 40px 20px;
                padding: 25px;
            }

            .page-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div>
                <span><i class="fas fa-phone"></i> +234 814 009 7917</span> |
                <span><i class="fas fa-envelope"></i> info@spotlightlistings.ng</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav>
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-search-location"></i> SPOTLIGHT
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="listing.php">Listings</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="user-dashboard.php">Dashboard</a></li>
                <li><a href="user-logout.php" class="btn" style="background: var(--accent-color); color: var(--primary-color); padding: 8px 16px; border-radius: 4px;">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Hero -->
    <section class="page-hero">
        <div class="container">
            <h1>Schedule Property Inspection</h1>
            <p>Book an on-site inspection of your preferred properties</p>
        </div>
    </section>

    <!-- Inspection Form -->
    <div class="form-container">
        <div class="form-header">
            <h2>Request an Inspection</h2>
            <p>Select a property and choose your preferred inspection date</p>
        </div>

        <form method="POST">
            <?php if (!empty($form_success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($form_success); ?>
                </div>
            <?php elseif (!empty($form_error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($form_error); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" class="form-input" placeholder="Your full name" required
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($user_name); ?>">
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-input" placeholder="your@email.com" required
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user_email); ?>">
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-input" placeholder="+234..."
                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($user_phone); ?>">
            </div>

            <div class="form-group">
                <label>Property *</label>
                <select name="property_id" class="form-select" required>
                    <option value="">Select a property...</option>
                    <?php foreach ($properties as $prop): ?>
                        <option value="<?php echo $prop['id']; ?>" <?php echo (isset($_POST['property_id']) && $_POST['property_id'] == $prop['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prop['title']); ?> - <?php echo htmlspecialchars($prop['location']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Preferred Inspection Date</label>
                <input type="date" name="inspection_date" class="form-input"
                    value="<?php echo isset($_POST['inspection_date']) ? htmlspecialchars($_POST['inspection_date']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Additional Notes or Questions *</label>
                <textarea name="message" class="form-textarea" placeholder="Tell us any specific concerns or questions about the property..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Schedule Inspection</button>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h3>About Spotlight</h3>
                    <p>We are dedicated to bringing transparency and integrity to Abuja's real estate market.</p>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="listing.php">Listings</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="services.php">Property Verification</a></li>
                        <li><a href="services.php">Legal Consultation</a></li>
                        <li><a href="services.php">Market Analysis</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Spotlight Listings. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>