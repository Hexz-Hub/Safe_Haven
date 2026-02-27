<?php
session_start();

$form_success = '';
$form_error = '';

if (isset($_SESSION['form_success'])) {
    $form_success = $_SESSION['form_success'];
    unset($_SESSION['form_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once 'config/database.php';
    include_once 'config/email.php';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $request_type = isset($_POST['request_type']) ? trim($_POST['request_type']) : '';
    $concern = isset($_POST['concern']) ? trim($_POST['concern']) : '';
    $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';

    $db = new \Database();
    $conn = $db->getConnection();

    // Create verifications table if it doesn't exist (includes optional user_id and concern)
    $create_sql = "CREATE TABLE IF NOT EXISTS verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50),
    request_type VARCHAR(100),
    concern TEXT,
    message TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($create_sql);

    // Ensure concern column exists
    try {
        $colStmt = $conn->prepare("SHOW COLUMNS FROM verifications LIKE 'concern'");
        $colStmt->execute();
        if ($colStmt->rowCount() === 0) {
            $conn->exec("ALTER TABLE verifications ADD COLUMN concern TEXT NULL AFTER request_type");
        }
    } catch (Exception $e) {
        // ignore - best-effort schema update
    }

    $insert_sql = "INSERT INTO verifications (user_id, name, email, phone, request_type, concern, message, status) VALUES (:user_id, :name, :email, :phone, :request_type, :concern, :message, 'pending')";
    $stmt = $conn->prepare($insert_sql);
    $user_id_param = null;
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['user_id'])) {
        $user_id_param = $_SESSION['user_id'];
    }
    $stmt->bindParam(':user_id', $user_id_param);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':request_type', $request_type);
    $stmt->bindParam(':concern', $concern);
    $stmt->bindParam(':message', $message_text);

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
        $email_subject = "New Contact Form Submission - " . $request_type;
        $email_body = "You have received a new contact form submission:\n\n";
        $email_body .= "Name: $name\n";
        $email_body .= "Email: $email\n";
        $email_body .= "Phone: $phone\n";
        $email_body .= "Request Type: $request_type\n";
        if (!empty($concern)) {
            $email_body .= "Concern: $concern\n";
        }
        $email_body .= "\nMessage:\n$message_text\n\n";
        $email_body .= "---\n";
        $email_body .= "Submitted at: " . date('Y-m-d H:i:s') . "\n";
        $email_body .= "View all verifications: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/admin-verifications.php";

        sendEmail($admin_email, $email_subject, $email_body, 'Admin');

        $_SESSION['form_success'] = 'Thanks — your request has been submitted. Our verification team will contact you.';
        header('Location: contact.php?submitted=1');
        exit();
    } else {
        $form_error = 'There was an error submitting your request. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Spotlight Listings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR CONTACT PAGE */

        /* Contact Hero */
        .contact-hero {
            --white: #ffffff;
            --light-bg: #f4f5f4;
            --input-bg: #ffffff;
        }

        /* --- TOP BAR --- */
        .contact-hero {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)), url('https://images.unsplash.com/photo-1423666639041-f56000c27a9a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0 120px;
            /* Extra padding for overlap */
            text-align: center;
        }

        .contact-hero h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
        }

        .contact-hero p {
            color: var(--accent-color);
            max-width: 600px;
            margin: 0 auto;
            font-style: italic;
        }

        /* --- CONTACT CONTAINER --- */
        .contact-wrapper {
            max-width: 1100px;
            margin: -80px auto 50px;
            /* Pull up into hero */
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
        }

        /* LEFT COLUMN: INFO */
        .contact-info {
            background-color: var(--primary-color);
            color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-top: 4px solid var(--accent-color);
        }

        .info-item {
            margin-bottom: 30px;
        }

        .info-item h3 {
            color: var(--accent-color);
            font-size: 1.1rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item p {
            color: #ddd;
            font-size: 0.95rem;
        }

        .info-item a {
            color: white;
            text-decoration: none;
            border-bottom: 1px dotted var(--accent-color);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 40px;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
            font-weight: bold;
            text-decoration: none;
            transition: 0.3s;
            border: 1px solid transparent;
        }

        .social-icon:hover {
            background: transparent;
            color: var(--white);
            border-color: var(--accent-color);
        }

        /* RIGHT COLUMN: FORM */
        .contact-form-card {
            background-color: var(--white);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .form-header {
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: var(--primary-color);
        }

        .form-header p {
            font-size: 0.9rem;
            color: #666;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 1rem;
            transition: 0.3s;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        /* Fear Dropdown */
        .fear-group label {
            color: var(--primary-color);
        }

        .fear-group select {
            border-left: 4px solid var(--accent-color);
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background-color: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            text-transform: uppercase;
            transition: 0.3s;
            border: 2px solid var(--accent-color);
        }

        .btn-submit:hover {
            background-color: transparent;
            color: var(--accent-color);
            color: var(--primary-color);
        }

        /* --- MAP SECTION --- */
        .map-section {
            height: 500px;
            background-color: var(--light-bg);
            position: relative;
            margin: 50px 0;
            padding: 0;
        }

        .map-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .map-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .map-header h2 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .map-header p {
            color: #666;
            font-size: 1rem;
        }

        .map-wrapper {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: 3px solid var(--accent-color);
            background: white;
        }

        .map-wrapper iframe {
            display: block;
            filter: grayscale(0%) contrast(1.1) brightness(1.05);
        }

        .map-overlay {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(47, 62, 46, 0.95);
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            border-left: 4px solid var(--accent-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 10;
        }

        .map-overlay h3 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--accent-color);
        }

        .map-overlay p {
            font-size: 0.85rem;
            margin: 0;
            color: #ddd;
        }

        /* --- FOOTER --- */
        footer {
            background-color: var(--primary-dark);
            color: #a3bfa3;
            padding: 40px 0;
            text-align: center;
        }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media (max-width: 768px) {

            /* Mobile Nav Logic */
            .hamburger {
                display: block;
            }

            .hamburger.active .bar:nth-child(2) {
                opacity: 0;
            }

            .hamburger.active .bar:nth-child(1) {
                transform: translateY(8px) rotate(45deg);
            }

            .hamburger.active .bar:nth-child(3) {
                transform: translateY(-8px) rotate(-45deg);
            }

            .nav-links {
                position: fixed;
                left: -100%;
                top: 70px;
                gap: 0;
                flex-direction: column;
                background-color: var(--primary-color);
                width: 100%;
                text-align: center;
                transition: 0.3s;
                padding-bottom: 20px;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
            }

            .nav-links.active {
                left: 0;
            }

            .nav-links li {
                margin: 16px 0;
                width: 100%;
            }

            .btn-nav {
                display: inline-block;
                margin-top: 10px;
            }

            /* Content Adjustments */
            .contact-wrapper {
                grid-template-columns: 1fr;
                margin-top: -30px;
                gap: 30px;
            }

            .contact-hero h1 {
                font-size: 2.2rem;
            }

            .contact-hero {
                padding-bottom: 60px;
            }

            .contact-info,
            .contact-form-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <header class="contact-hero">
        <div class="container">
            <h1>Start Your Verified Journey</h1>
            <p>Reach out to the only agency in Delta State that prioritizes your peace of mind over the sale.</p>
        </div>
    </header>

    <div class="contact-wrapper">

        <div class="contact-info">
            <div class="info-item">
                <h3>📍 Visit Our Office</h3>
                <p>Suite 001, The Spotlight Hub<br>Delta State: Asaba, Agbor, Kwale, Abraka, Oghara, Ozoro</p>
            </div>

            <div class="info-item">
                <h3>📞 Call Us</h3>
                <p>Mon - Sat: 8am - 6pm</p>
                <p><a href="tel:+2348140097917">+234 814 009 7917</a></p>
            </div>

            <div class="info-item">
                <h3>✉️ Email Us</h3>
                <p>General: <a href="mailto:info@spotlightlistings.ng">info@spotlightlistings.ng</a></p>
                <p>Verification: <a href="mailto:verify@spotlightlistings.ng">verify@spotlightlistings.ng</a></p>
            </div>

            <div class="info-item">
                <h3>💬 WhatsApp</h3>
                <p>Need a quick response?</p>
                <a href="#">Chat with a Verifier &rarr;</a>
            </div>

            <div class="social-links">
                <a href="#" class="social-icon">IG</a>
                <a href="#" class="social-icon">X</a>
                <a href="#" class="social-icon">LI</a>
            </div>
        </div>

        <div class="contact-form-card">
            <div class="form-header">
                <h2>Send Us a Message</h2>
                <p>Tell us what you are looking for, and we will verify the reality.</p>
            </div>

            <form method="POST">
                <?php if (!empty($form_success)): ?>
                    <div style="margin-bottom:20px;padding:12px;background:#d4edda;color:#155724;border-left:4px solid #28a745;border-radius:6px;">
                        <?php echo htmlspecialchars($form_success); ?>
                    </div>
                <?php elseif (!empty($form_error)): ?>
                    <div style="margin-bottom:20px;padding:12px;background:#f8d7da;color:#721c24;border-left:4px solid #dc3545;border-radius:6px;">
                        <?php echo htmlspecialchars($form_error); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Amina Bello" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-input" placeholder="name@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-input" placeholder="+234..." value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label>I am looking to:</label>
                    <select name="request_type" class="form-select">
                        <option value="buy" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] == 'buy') ? 'selected' : ''; ?>>Buy a Home</option>
                        <option value="rent" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] == 'rent') ? 'selected' : ''; ?>>Rent a Home</option>
                        <option value="invest" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] == 'invest') ? 'selected' : ''; ?>>Invest in Land</option>
                        <option value="verification" <?php echo (isset($_POST['request_type']) && $_POST['request_type'] == 'verification') ? 'selected' : ''; ?>>Verify a Property (3rd Party)</option>
                    </select>
                </div>

                <div class="form-group fear-group">
                    <label>What is your biggest concern regarding Delta State real estate?</label>
                    <select name="concern" class="form-select">
                        <option value="" disabled selected>Select an option...</option>
                        <option value="⚠️ Fake documents/scams" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '⚠️ Fake documents/scams') ? 'selected' : ''; ?>>⚠️ I'm afraid of fake documents/scams</option>
                        <option value="💸 Overpaying/Valuation" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '💸 Overpaying/Valuation') ? 'selected' : ''; ?>>💸 I don't want to overpay (Valuation)</option>
                        <option value="🏚️ Hidden structural issues" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '🏚️ Hidden structural issues') ? 'selected' : ''; ?>>🏚️ I'm worried about hidden structural issues</option>
                        <option value="⚖️ Legal disputes/litigation" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '⚖️ Legal disputes/litigation') ? 'selected' : ''; ?>>⚖️ I'm worried about legal disputes/litigation</option>
                        <option value="😊 None - just want home" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '😊 None - just want home') ? 'selected' : ''; ?>>😊 None - I just want a great home!</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Your Message</label>
                    <textarea class="form-textarea" rows="4" placeholder="Tell us more about your preferences (Location, Budget, etc.)..."></textarea>
                </div>

                <button type="submit" class="btn-submit">Start Conversation</button>
            </form>
        </div>
    </div>

    <section class="map-section">
        <div class="map-container">
            <div class="map-header">
                <h2>Visit Us in Delta State</h2>
                <p>📍 Serving the entire Delta State with verified real estate solutions</p>
            </div>

            <div class="map-wrapper">
                <div class="map-overlay">
                    <h3>📍 Our Locations</h3>
                    <p>Asaba, Agbor, Kwale, Abraka, Oghara, Ozoro</p>
                </div>

                <!-- Map for Delta State (replace with actual map if needed) -->
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126431.019!2d6.6986!3d6.2016!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1043f2e7b6e7b6e7%3A0x7b6e7b6e7b6e7b6e!2sDelta%20State%2C%20Nigeria!5e0!3m2!1sen!2sng!4v1706363847291!5m2!1sen!2sng&zoom=9"
                    width="100%"
                    height="500"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <footer style="background-color:var(--primary-dark); color:#a3bfa3; padding:40px 0; text-align:center;">
        <p>© 2026 Spotlight Listings. The Truth is Our Only Inventory.</p>
    </footer>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/2348140097917" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
    </a>

    <script>
        const hamburger = document.querySelector(".hamburger");
        const navLinks = document.querySelector(".nav-links");

        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navLinks.classList.toggle("active");
        });

        document.querySelectorAll(".nav-links li a").forEach(n => n.addEventListener("click", () => {
            hamburger.classList.remove("active");
            navLinks.classList.remove("active");
        }));
    </script>

</body>

</html>