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

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'save_company_settings') {
        $settings_to_save = [
            'company_name' => $_POST['company_name'] ?? '',
            'company_email' => $_POST['company_email'] ?? '',
            'company_phone' => $_POST['company_phone'] ?? '',
            'company_address' => $_POST['company_address'] ?? '',
            'whatsapp_number' => $_POST['whatsapp_number'] ?? '',
            'instagram_url' => $_POST['instagram_url'] ?? '',
            'facebook_url' => $_POST['facebook_url'] ?? '',
            'linkedin_url' => $_POST['linkedin_url'] ?? ''
        ];

        try {
            foreach ($settings_to_save as $key => $value) {
                $query = "INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value)
                          ON DUPLICATE KEY UPDATE setting_value = :value";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':key', $key);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            }
            $message = "Company settings updated successfully!";
        } catch (Exception $e) {
            $message = "Error updating settings: " . $e->getMessage();
        }
    }
}

// Get current company settings
$company_settings = [];
try {
    $query = "SELECT setting_key, setting_value FROM settings WHERE setting_key IN 
              ('company_name', 'company_email', 'company_phone', 'company_address', 
               'whatsapp_number', 'instagram_url', 'facebook_url', 'linkedin_url')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($results as $row) {
        $company_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    $company_settings = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Admin Dashboard</title>
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
            max-width: 1000px;
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

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .settings-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .settings-card h2 {
            margin-bottom: 10px;
            color: var(--primary-color);
            font-size: 22px;
        }

        .settings-card p {
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        textarea {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 5px rgba(0, 102, 204, 0.3);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        button {
            background: var(--accent-color);
            color: var(--primary-color);
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: opacity 0.3s;
        }

        button:hover {
            opacity: 0.9;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-cog"></i> Settings</h1>
            <a href="admin-dashboard.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="settings-card">
            <h2>Company & Contact Settings</h2>
            <p>Manage your company information and contact details.</p>

            <form method="POST">
                <input type="hidden" name="action" value="save_company_settings">

                <!-- Basic Company Info -->
                <div class="section-title"><i class="fas fa-building"></i> Company Information</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company_settings['company_name'] ?? ''); ?>" placeholder="e.g., SafeHaven">
                    </div>
                    <div class="form-group">
                        <label for="company_email">Company Email</label>
                        <input type="email" id="company_email" name="company_email" value="<?php echo htmlspecialchars($company_settings['company_email'] ?? ''); ?>" placeholder="e.g., info@safehaven.ng">
                    </div>
                </div>

                <!-- Contact Details -->
                <div class="section-title" style="margin-top: 25px;"><i class="fas fa-phone"></i> Contact Details</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="company_phone">Company Phone</label>
                        <input type="tel" id="company_phone" name="company_phone" value="<?php echo htmlspecialchars($company_settings['company_phone'] ?? ''); ?>" placeholder="e.g., +234 (0) 123 456 7890">
                    </div>
                    <div class="form-group">
                        <label for="whatsapp_number">WhatsApp Number</label>
                        <input type="tel" id="whatsapp_number" name="whatsapp_number" value="<?php echo htmlspecialchars($company_settings['whatsapp_number'] ?? ''); ?>" placeholder="e.g., +234 (0) 123 456 7890">
                    </div>
                </div>

                <div class="form-group full">
                    <label for="company_address">Physical Address</label>
                    <textarea id="company_address" name="company_address" placeholder="Enter your company's physical address"><?php echo htmlspecialchars($company_settings['company_address'] ?? ''); ?></textarea>
                </div>

                <!-- Social Media -->
                <div class="section-title" style="margin-top: 25px;"><i class="fas fa-share-alt"></i> Social Media Links</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="facebook_url">Facebook URL</label>
                        <input type="text" id="facebook_url" name="facebook_url" value="<?php echo htmlspecialchars($company_settings['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/your-page">
                    </div>
                    <div class="form-group">
                        <label for="instagram_url">Instagram URL</label>
                        <input type="text" id="instagram_url" name="instagram_url" value="<?php echo htmlspecialchars($company_settings['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/your-handle">
                    </div>
                    <div class="form-group">
                        <label for="linkedin_url">LinkedIn URL</label>
                        <input type="text" id="linkedin_url" name="linkedin_url" value="<?php echo htmlspecialchars($company_settings['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/company/your-company">
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit"><i class="fas fa-save"></i> Save All Settings</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>