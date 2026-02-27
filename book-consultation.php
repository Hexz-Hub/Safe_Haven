<?php
session_start();
include_once 'config/database.php';
include_once 'config/email.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user-login.php");
    exit();
}

$db = new Database();
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
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $preferred_time = isset($_POST['preferred_time']) ? $_POST['preferred_time'] : '';

    if ($name && $email && $subject && $message) {
        try {
            $query = "INSERT INTO consultations (user_id, name, email, phone, subject, message, preferred_time, status, created_at) 
                      VALUES (:user_id, :name, :email, :phone, :subject, :message, :preferred_time, 'pending', NOW())";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':preferred_time', $preferred_time);

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

                // Send notification email to admin
                $email_subject = "New Consultation Request - " . $subject;
                $email_body = "You have received a new consultation request:\n\n";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Phone: $phone\n";
                $email_body .= "Subject: $subject\n";
                if (!empty($preferred_time)) {
                    $email_body .= "Preferred Time: $preferred_time\n";
                }
                $email_body .= "\nMessage:\n$message\n\n";
                $email_body .= "---\n";
                $email_body .= "Submitted at: " . date('Y-m-d H:i:s') . "\n";
                $email_body .= "View all consultations: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/admin-consultations.php";

                sendEmail($admin_email, $email_subject, $email_body, 'Admin');

                $_SESSION['form_success'] = "Your consultation request has been submitted successfully! We'll contact you soon.";
                header('Location: book-consultation.php?submitted=1');
                exit();
            } else {
                $form_error = "Failed to submit your consultation request. Please try again.";
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Consultation | Spotlight Listings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* --- PAGE HERO --- */
        .page-hero {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)),
                url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
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
    <?php include 'includes/header.php'; ?>

    <!-- Page Hero -->
    <section class="page-hero">
        <div class="container">
            <h1>Book a Consultation</h1>
            <p>Connect with our real estate experts for personalized guidance</p>
        </div>
    </section>

    <!-- Consultation Form -->
    <div class="form-container">
        <div class="form-header">
            <h2>Schedule Your Consultation</h2>
            <p>Fill out the form below and we'll get back to you within 24 hours</p>
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
                <label>Consultation Subject *</label>
                <select name="subject" class="form-select" required>
                    <option value="">Select a subject...</option>
                    <option value="property_inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'property_inquiry') ? 'selected' : ''; ?>>Property Inquiry</option>
                    <option value="investment_advice" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'investment_advice') ? 'selected' : ''; ?>>Investment Advice</option>
                    <option value="verification_process" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'verification_process') ? 'selected' : ''; ?>>Verification Process</option>
                    <option value="buying_guidance" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'buying_guidance') ? 'selected' : ''; ?>>Buying Guidance</option>
                    <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label>Preferred Time for Consultation</label>
                <select name="preferred_time" class="form-select">
                    <option value="">No preference</option>
                    <option value="morning" <?php echo (isset($_POST['preferred_time']) && $_POST['preferred_time'] == 'morning') ? 'selected' : ''; ?>>Morning (9 AM - 12 PM)</option>
                    <option value="afternoon" <?php echo (isset($_POST['preferred_time']) && $_POST['preferred_time'] == 'afternoon') ? 'selected' : ''; ?>>Afternoon (12 PM - 3 PM)</option>
                    <option value="evening" <?php echo (isset($_POST['preferred_time']) && $_POST['preferred_time'] == 'evening') ? 'selected' : ''; ?>>Evening (3 PM - 6 PM)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Message *</label>
                <textarea name="message" class="form-textarea" placeholder="Tell us more about what you'd like to discuss..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Book Consultation</button>
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