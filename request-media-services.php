<?php
session_start();
include_once 'config/database.php';
include_once 'config/email.php';

$success = '';
$error = '';

if (isset($_SESSION['form_success'])) {
    $success = $_SESSION['form_success'];
    unset($_SESSION['form_success']);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_required'] = true;
    $_SESSION['redirect_after_login'] = 'request-media-services.php';
    header('Location: user-login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $service_type = $_POST['service_type'];
    $preferred_date = $_POST['preferred_date'];
    $preferred_time = $_POST['preferred_time'];
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && !empty($service_type) && !empty($preferred_date) && !empty($preferred_time)) {
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("INSERT INTO media_requests (user_id, name, email, phone, service_type, preferred_date, preferred_time, message) 
                                VALUES (:user_id, :name, :email, :phone, :service_type, :preferred_date, :preferred_time, :message)");

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':service_type', $service_type);
        $stmt->bindParam(':preferred_date', $preferred_date);
        $stmt->bindParam(':preferred_time', $preferred_time);
        $stmt->bindParam(':message', $message);

        if ($stmt->execute()) {
            $_SESSION['form_success'] = 'Your media service request has been submitted successfully! Our team will review it and get back to you soon.';

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
            $admin_subject = "New Media Service Request - " . str_replace('_', ' ', ucwords($service_type, '_'));
            $admin_message = "You have received a new media service request:\n\n";
            $admin_message .= "Name: $name\n";
            $admin_message .= "Email: $email\n";
            $admin_message .= "Phone: $phone\n";
            $admin_message .= "Service Type: " . str_replace('_', ' ', ucwords($service_type, '_')) . "\n";
            $admin_message .= "Preferred Date: $preferred_date\n";
            $admin_message .= "Preferred Time: $preferred_time\n";
            $admin_message .= "\nMessage:\n$message\n\n";
            $admin_message .= "---\n";
            $admin_message .= "Submitted at: " . date('Y-m-d H:i:s') . "\n";
            $admin_message .= "View all requests: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/admin-media-requests.php";

            sendEmail($admin_email, $admin_subject, $admin_message, 'Admin');

            // Send confirmation email to user
            $to = $email;
            $subject = "Media Service Request Confirmation - Spotlight Listings";
            $email_message = "Dear $name,\n\n";
            $email_message .= "Thank you for your media service request. We have received the following details:\n\n";
            $email_message .= "Service: " . str_replace('_', ' ', ucwords($service_type, '_')) . "\n";
            $email_message .= "Preferred Date: $preferred_date\n";
            $email_message .= "Preferred Time: $preferred_time\n";
            $email_message .= "Message: $message\n\n";
            $email_message .= "Our team will review your request and respond within 24-48 hours.\n\n";
            $email_message .= "Best regards,\nSpotlight Listings Team";

            sendEmail($to, $subject, $email_message, $name);

            header('Location: request-media-services.php?submitted=1');
            exit();
        } else {
            $error = 'Failed to submit request. Please try again.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// Get user details for pre-filling
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request Media Services | Spotlight Listings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        .page-hero {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)),
                url('https://images.unsplash.com/photo-1519183071298-a2962be90b8e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
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

        /* --- ALERTS --- */
        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            font-size: 0.95rem;
        }

        label .required {
            color: #e74c3c;
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

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .datetime-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* --- SUBMIT BUTTON --- */
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            background: #003366;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .btn-back:hover {
            color: var(--accent-color);
        }

        /* --- FOOTER --- */
        footer {
            background-color: var(--primary-dark);
            color: #a3bfa3;
            padding: 40px 0;
            text-align: center;
            margin-top: 80px;
        }

        @media (max-width: 600px) {
            .page-hero h1 {
                font-size: 1.8rem;
            }

            .form-container {
                padding: 25px;
                margin: 30px auto;
            }

            .datetime-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <h1><i class="fas fa-video"></i> Request Media Services</h1>
            <p>Professional media services to elevate your brand</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h2>Choose Your Service</h2>
                <p>Fill out the form below to request our media services</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="service_type">
                        Service Type <span class="required">*</span>
                    </label>
                    <select id="service_type" name="service_type" class="form-select" required>
                        <option value="">-- Select a Service --</option>
                        <option value="event_coverage">📸 Event Coverage</option>
                        <option value="content_creation">🎬 Content Creation</option>
                        <option value="social_media">📱 Social Media Management</option>
                        <option value="digital_marketing">🚀 Digital Marketing Services</option>
                        <option value="google_ads">📊 Google & Social Ads</option>
                        <option value="email_marketing">📧 Email Marketing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">
                        Full Name <span class="required">*</span>
                    </label>
                    <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">
                        Email Address <span class="required">*</span>
                    </label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">
                        Phone Number
                    </label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+234 xxx xxx xxxx">
                </div>

                <div class="datetime-group">
                    <div class="form-group">
                        <label for="preferred_date">
                            Preferred Date <span class="required">*</span>
                        </label>
                        <input type="date" id="preferred_date" name="preferred_date" class="form-input" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="preferred_time">
                            Preferred Time <span class="required">*</span>
                        </label>
                        <input type="time" id="preferred_time" name="preferred_time" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">
                        Additional Details / Requirements
                    </label>
                    <textarea id="message" name="message" class="form-textarea" placeholder="Please provide any specific details about your project, location, or special requirements..."></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Set minimum date to today
        document.getElementById('preferred_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>

</html>