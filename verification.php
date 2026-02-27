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
    $property_location = isset($_POST['property_location']) ? trim($_POST['property_location']) : '';
    $property_link = isset($_POST['property_link']) ? trim($_POST['property_link']) : '';
    $concern = isset($_POST['concern']) ? trim($_POST['concern']) : '';
    $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($name && $email) {
        $db = new \Database();
        $conn = $db->getConnection();

        // Create verifications table if it doesn't exist (includes optional user_id and extra fields)
        $create_sql = "CREATE TABLE IF NOT EXISTS verifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(50),
            request_type VARCHAR(100),
            concern TEXT,
            property_location VARCHAR(255),
            property_link VARCHAR(255),
            message TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->exec($create_sql);

        // Ensure extra columns exist
        $columns = [
            'concern' => "ALTER TABLE verifications ADD COLUMN concern TEXT NULL AFTER request_type",
            'property_location' => "ALTER TABLE verifications ADD COLUMN property_location VARCHAR(255) NULL AFTER concern",
            'property_link' => "ALTER TABLE verifications ADD COLUMN property_link VARCHAR(255) NULL AFTER property_location"
        ];

        foreach ($columns as $col => $sql) {
            try {
                $colStmt = $conn->prepare("SHOW COLUMNS FROM verifications LIKE :col");
                $colStmt->bindParam(':col', $col);
                $colStmt->execute();
                if ($colStmt->rowCount() === 0) {
                    $conn->exec($sql);
                }
            } catch (Exception $e) {
                // ignore - best-effort schema update
            }
        }

        $insert_sql = "INSERT INTO verifications (user_id, name, email, phone, request_type, concern, property_location, property_link, message, status)
            VALUES (:user_id, :name, :email, :phone, :request_type, :concern, :property_location, :property_link, :message, 'pending')";
        $stmt = $conn->prepare($insert_sql);
        $user_id_param = null;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['user_id'])) {
            $user_id_param = $_SESSION['user_id'];
        }
        $request_type = 'verification_report';
        $stmt->bindParam(':user_id', $user_id_param);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':request_type', $request_type);
        $stmt->bindParam(':concern', $concern);
        $stmt->bindParam(':property_location', $property_location);
        $stmt->bindParam(':property_link', $property_link);
        $stmt->bindParam(':message', $message_text);

        if ($stmt->execute()) {
            // Get admin email from settings
            $admin_email = 'support@safehaven.ng'; // Default admin email
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

            // Send notification email to admin with HTML template
            $email_subject = "New Verification Report Request - SafeHaven";

            // Build HTML email
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
            $dashboard_link = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/admin-verifications.php";

            $html_body = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Segoe UI, Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .intro {
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-left: 3px solid #667eea;
            padding-left: 10px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .detail-label {
            font-weight: 600;
            color: #667eea;
            min-width: 140px;
            padding-right: 20px;
        }
        .detail-value {
            color: #555;
            flex: 1;
            word-wrap: break-word;
        }
        .message-box {
            background-color: #f9f9f9;
            border-left: 3px solid #667eea;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.5;
            color: #555;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #999;
            border-top: 1px solid #eee;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            margin: 15px 0;
            font-weight: 600;
            font-size: 14px;
            transition: opacity 0.3s;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .timestamp {
            color: #999;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Verification Report Request</h1>
        </div>
        
        <div class="content">
            <p class="intro">A new verification report request has been submitted. Please review the details below:</p>
            
            <div class="section">
                <div class="section-title">Requester Information</div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">' . htmlspecialchars($name) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><a href="mailto:' . htmlspecialchars($email) . '" style="color: #667eea; text-decoration: none;">' . htmlspecialchars($email) . '</a></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">' . htmlspecialchars($phone) . '</span>
                </div>
            </div>';

            if (!empty($concern) || !empty($property_location) || !empty($property_link)) {
                $html_body .= '<div class="section">
                    <div class="section-title">Property Details</div>';

                if (!empty($concern)) {
                    $html_body .= '<div class="detail-row">
                        <span class="detail-label">Concern:</span>
                        <span class="detail-value">' . htmlspecialchars($concern) . '</span>
                    </div>';
                }

                if (!empty($property_location)) {
                    $html_body .= '<div class="detail-row">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value">' . htmlspecialchars($property_location) . '</span>
                    </div>';
                }

                if (!empty($property_link)) {
                    $html_body .= '<div class="detail-row">
                        <span class="detail-label">Property Link:</span>
                        <span class="detail-value"><a href="' . htmlspecialchars($property_link) . '" style="color: #667eea; text-decoration: none; word-break: break-all;">' . htmlspecialchars($property_link) . '</a></span>
                    </div>';
                }

                $html_body .= '</div>';
            }

            $html_body .= '<div class="section">
                <div class="section-title">Message</div>
                <div class="message-box">' . htmlspecialchars($message_text) . '</div>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . $dashboard_link . '" class="cta-button">Review in Dashboard</a>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Submitted:</span>
                <span class="detail-value timestamp">' . date('F d, Y \a\t g:i A') . '</span>
            </div>
        </div>
        
        <div class="footer">
            <p>SafeHaven Admin Notification</p>
            <p style="margin: 5px 0 0 0;">This is an automated notification. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>';

            // Plain text version for fallback
            $plain_body = "New Verification Report Request\n\n";
            $plain_body .= "Name: $name\n";
            $plain_body .= "Email: $email\n";
            $plain_body .= "Phone: $phone\n";
            if (!empty($concern)) {
                $plain_body .= "Concern: $concern\n";
            }
            if (!empty($property_location)) {
                $plain_body .= "Property Location: $property_location\n";
            }
            if (!empty($property_link)) {
                $plain_body .= "Property Link: $property_link\n";
            }
            $plain_body .= "\nMessage:\n$message_text\n\n";
            $plain_body .= "---\n";
            $plain_body .= "Submitted at: " . date('Y-m-d H:i:s') . "\n";
            $plain_body .= "View all verifications: $dashboard_link";

            sendHtmlEmail($admin_email, $email_subject, $html_body, $plain_body, 'Admin');

            $_SESSION['form_success'] = 'Thanks — your verification report request has been submitted. Our team will contact you shortly.';
            header('Location: verification.php?submitted=1#verification-form');
            exit();
        } else {
            $form_error = 'There was an error submitting your request. Please try again.';
        }
    } else {
        $form_error = 'Please provide your name and email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The SafeHaven Standard | Verification Process</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* Page-specific styles */

        /* --- PAGE HEADER --- */
        .page-header {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)), url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .page-header h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--white);
        }

        .page-header p {
            max-width: 700px;
            margin: 0 auto;
            color: var(--accent-color);
            font-size: 1.2rem;
            font-style: italic;
        }

        /* --- TIMELINE SECTION --- */
        .process-section {
            padding: 80px 0;
            background-color: var(--white);
        }

        .timeline {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* The Vertical Line */
        .timeline::after {
            content: '';
            position: absolute;
            width: 4px;
            background-color: rgba(0, 102, 204, 0.3);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -2px;
        }

        /* Timeline Containers */
        .t-container {
            padding: 10px 40px;
            position: relative;
            background-color: inherit;
            width: 50%;
        }

        .left {
            left: 0;
        }

        .right {
            left: 50%;
        }

        /* The Circle on the Line */
        .t-container::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            right: -12px;
            background-color: var(--primary-color);
            border: 4px solid var(--accent-color);
            top: 30px;
            border-radius: 50%;
            z-index: 1;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .right::after {
            left: -12px;
        }

        /* The Content Box */
        .content {
            padding: 35px;
            background-color: var(--light-gray);
            border-radius: 8px;
            border-left: 5px solid var(--primary-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .content:hover {
            transform: translateY(-5px);
            background-color: white;
            border-left-color: var(--accent-color);
        }

        .content h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 10px;
        }

        .content p {
            font-size: 1rem;
            color: #555;
            margin-bottom: 15px;
            font-style: italic;
        }

        .content ul {
            list-style: none;
        }

        .content li {
            margin-bottom: 12px;
            padding-left: 25px;
            position: relative;
            color: #333;
        }

        .content li::before {
            content: '🛡️';
            font-size: 0.8rem;
            position: absolute;
            left: 0;
            top: 3px;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 100px 20px;
            background-color: var(--primary-color);
            color: white;
            background-image: radial-gradient(circle at center, rgba(0, 102, 204, 0.15) 0%, transparent 50%);
        }

        .cta-section h2 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--white);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .btn-gold {
            background-color: var(--accent-color);
            color: var(--primary-color);
            padding: 15px 35px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            display: inline-block;
            margin-top: 30px;
            text-transform: uppercase;
            transition: 0.3s;
            border: 2px solid var(--accent-color);
        }

        .btn-gold:hover {
            background-color: transparent;
            color: var(--accent-color);
        }

        /* Verification Form */
        .verification-form-section {
            background: var(--light-gray);
            padding: 80px 20px;
        }

        .verification-form-card {
            max-width: 900px;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border-top: 4px solid var(--accent-color);
        }

        .verification-form-card h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .verification-form-card p {
            color: #666;
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 16px;
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

        .form-submit {
            margin-top: 10px;
            width: 100%;
            padding: 14px;
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

        .form-submit:hover {
            background-color: transparent;
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .verification-form-card {
                padding: 30px 20px;
            }
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

            /* Timeline Mobile Logic */
            .timeline::after {
                left: 31px;
            }

            .t-container {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }

            .t-container::after {
                left: 19px;
            }

            .left::after,
            .right::after {
                left: 19px;
            }

            .right {
                left: 0%;
            }

            /* Move right side boxes to match left */

            .page-header h1 {
                font-size: 2.2rem;
            }

            .cta-section {
                padding: 60px 20px;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <header class="page-header">
        <div class="container">
            <h1>The SafeHaven Stress Test</h1>
            <p>We don't just view properties; we investigate them. Our 3-Phase Protocol ensures "Verified" isn't just a stamp—it's a guarantee.</p>
        </div>
    </header>

    <section class="process-section">
        <div class="timeline">
            <div class="t-container left">
                <div class="content">
                    <h2>Phase 1: The Legal Audit</h2>
                    <p>"We hit the books before we take a photo."</p>
                    <ul>
                        <li><strong>AGIS Authentication:</strong> We cross-reference the C of O/R of O directly with the Abuja Geographic Information Systems database.</li>
                        <li><strong>Encumbrance Check:</strong> We investigate for undisclosed mortgages, court injunctions, or hidden family disputes.</li>
                        <li><strong>FCDA Double-Check:</strong> For developing districts (e.g., Katampe, Idu), we verify allocation papers to prevent "double-allocation" fraud.</li>
                    </ul>
                </div>
            </div>

            <div class="t-container right">
                <div class="content">
                    <h2>Phase 2: Physical Reality Check</h2>
                    <p>"We look past the fresh paint."</p>
                    <ul>
                        <li><strong>The "Rain Test":</strong> We analyze the location's topography and history for flood risks, even during the dry season.</li>
                        <li><strong>Structural Integrity:</strong> We check for foundation dampness, wall cracks, and plumbing pressure.</li>
                        <li><strong>Boundary Precision:</strong> Our surveyors confirm that physical walls align strictly with the survey plan.</li>
                    </ul>
                </div>
            </div>

            <div class="t-container left">
                <div class="content">
                    <h2>Phase 3: Value & Vibe</h2>
                    <p>"A legal home isn't always a good investment."</p>
                    <ul>
                        <li><strong>Infrastructure Audit:</strong> We confirm functional connection to the AEDC power grid and Water Board.</li>
                        <li><strong>Security Assessment:</strong> We evaluate proximity to security flashpoints or unmanaged shanties.</li>
                        <li><strong>Data-Driven Valuation:</strong> We use recent comparative market analysis to ensure you don't overpay based on hype.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <h2>Have a property you want us to check?</h2>
        <p style="color: #e0e0e0; font-size: 1.1rem;">We offer independent verification services even if you aren't buying through us.</p>
        <a href="#verification-form" class="btn-gold">Request a Verification Report</a>
    </section>

    <section id="verification-form" class="verification-form-section">
        <div class="verification-form-card">
            <h3>Verification Report Request</h3>
            <p>Share the property details so our team can begin the verification process.</p>

            <?php if (!empty($form_success)): ?>
                <div style="margin-bottom:20px;padding:12px;background:#d4edda;color:#155724;border-left:4px solid #28a745;border-radius:6px;">
                    <?php echo htmlspecialchars($form_success); ?>
                </div>
            <?php elseif (!empty($form_error)): ?>
                <div style="margin-bottom:20px;padding:12px;background:#f8d7da;color:#721c24;border-left:4px solid #dc3545;border-radius:6px;">
                    <?php echo htmlspecialchars($form_error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
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
                        <label>Property Location</label>
                        <input type="text" name="property_location" class="form-input" placeholder="Area, district, or full address" value="<?php echo isset($_POST['property_location']) ? htmlspecialchars($_POST['property_location']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Listing Link (optional)</label>
                        <input type="url" name="property_link" class="form-input" placeholder="https://..." value="<?php echo isset($_POST['property_link']) ? htmlspecialchars($_POST['property_link']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Biggest Concern</label>
                        <select name="concern" class="form-select">
                            <option value="" disabled selected>Select an option...</option>
                            <option value="⚠️ Fake documents/scams" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '⚠️ Fake documents/scams') ? 'selected' : ''; ?>>⚠️ Fake documents/scams</option>
                            <option value="💸 Overpaying/Valuation" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '💸 Overpaying/Valuation') ? 'selected' : ''; ?>>💸 Overpaying/Valuation</option>
                            <option value="🏚️ Hidden structural issues" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '🏚️ Hidden structural issues') ? 'selected' : ''; ?>>🏚️ Hidden structural issues</option>
                            <option value="⚖️ Legal disputes/litigation" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '⚖️ Legal disputes/litigation') ? 'selected' : ''; ?>>⚖️ Legal disputes/litigation</option>
                            <option value="😊 None - just want home" <?php echo (isset($_POST['concern']) && $_POST['concern'] == '😊 None - just want home') ? 'selected' : ''; ?>>😊 None - just want a great home</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Property Details</label>
                    <textarea name="message" class="form-textarea" rows="4" placeholder="Tell us about the property, owner claims, documents available, or any specific requests..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                </div>

                <button type="submit" class="form-submit">Submit Request</button>
            </form>
        </div>
    </section>

    <footer style="background-color:var(--primary-dark); color:#a3bfa3; padding:40px 0; text-align:center;">
        <p>© 2026 SafeHaven. The Truth is Our Only Inventory.</p>
    </footer>

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