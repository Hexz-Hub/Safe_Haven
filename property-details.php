<?php
session_start();
include_once 'config/database.php';

// Get property ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listing.php");
    exit();
}

$property_id = (int)$_GET['id'];
$db = new \Database();
$conn = $db->getConnection();

// Fetch property details
$query = "SELECT p.*, a.full_name as created_by_name 
          FROM properties p 
          LEFT JOIN admin_users a ON p.created_by = a.id 
          WHERE p.id = :id LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $property_id);
$stmt->execute();
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header("Location: listing.php");
    exit();
}

// Fetch property images
$img_query = "SELECT * FROM property_images WHERE property_id = :id ORDER BY display_order ASC, id ASC";
$img_stmt = $conn->prepare($img_query);
$img_stmt->bindParam(':id', $property_id);
$img_stmt->execute();
$images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch similar properties
$similar_query = "SELECT * FROM properties 
                  WHERE property_type = :type 
                  AND id != :id 
                  AND status = 'available' 
                  LIMIT 3";
$similar_stmt = $conn->prepare($similar_query);
$similar_stmt->bindParam(':type', $property['property_type']);
$similar_stmt->bindParam(':id', $property_id);
$similar_stmt->execute();
$similar_properties = $similar_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle inquiry submission
$inquiry_message = '';
$inquiry_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['login_required'] = true;
        header("Location: user-login.php");
        exit();
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if ($name && $email && $message) {
        $ins_query = "INSERT INTO inquiries (name, email, phone, property_id, message, status) 
                      VALUES (:name, :email, :phone, :property_id, :message, 'new')";
        $ins_stmt = $conn->prepare($ins_query);
        $ins_stmt->bindParam(':name', $name);
        $ins_stmt->bindParam(':email', $email);
        $ins_stmt->bindParam(':phone', $phone);
        $ins_stmt->bindParam(':property_id', $property_id);
        $ins_stmt->bindParam(':message', $message);

        if ($ins_stmt->execute()) {
            $inquiry_message = "Thank you! Your inquiry has been submitted. We'll contact you soon.";
        } else {
            $inquiry_error = "Failed to submit inquiry. Please try again.";
        }
    } else {
        $inquiry_error = "Please fill in all required fields.";
    }
}

// Format price
function formatPrice($price)
{
    if ($price >= 1000000000) {
        return '₦' . number_format($price / 1000000000, 2) . 'B';
    } elseif ($price >= 1000000) {
        return '₦' . number_format($price / 1000000, 0) . 'M';
    } else {
        return '₦' . number_format($price, 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR PROPERTY DETAILS PAGE */

        /* Breadcrumb */
        .breadcrumb {
            background: white;
            padding: 15px 0;
            font-size: 0.9rem;
        }

        .breadcrumb a {
            color: var(--accent-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb span {
            color: #666;
        }

        /* Main Content */
        .property-detail {
            padding: 30px 0;
        }

        .property-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        /* Image Gallery */
        .gallery-section {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            padding: 15px;
            background: #f9f9f9;
        }

        .thumbnail {
            width: 100%;
            height: 90px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .thumbnail:hover,
        .thumbnail.active {
            border-color: var(--accent-color);
        }

        /* Property Info Card */
        .info-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 80px;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 15px;
        }

        .property-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .location {
            color: #666;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-sold {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .property-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
            padding: 20px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-item i {
            color: var(--accent-color);
            font-size: 1.2rem;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #666;
        }

        .stat-value {
            font-weight: bold;
            color: var(--text-dark);
        }

        .cta-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--accent-color);
            color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--gold-light);
        }

        .btn-secondary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--primary-dark);
        }

        /* Description Section */
        .description-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            border-bottom: 3px solid var(--accent-color);
            padding-bottom: 10px;
        }

        .description-text {
            line-height: 1.8;
            color: #444;
            white-space: pre-line;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .feature-item i {
            color: var(--accent-color);
        }

        /* Contact Form */
        .contact-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--accent-color);
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Similar Properties */
        .similar-properties {
            padding: 40px 0;
        }

        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .property-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .property-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .property-card-body {
            padding: 20px;
        }

        .property-card-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .property-card-price {
            font-size: 1.3rem;
            color: var(--accent-color);
            font-weight: bold;
            margin-bottom: 10px;
        }

        .property-card-location {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .property-card-features {
            display: flex;
            gap: 15px;
            font-size: 0.9rem;
            color: #666;
        }

        /* Footer */
        footer {
            background-color: var(--primary-dark);
            color: #ddd;
            padding: 30px 0;
            text-align: center;
        }

        @media (max-width: 968px) {
            .property-grid {
                grid-template-columns: 1fr;
            }

            .info-card {
                position: static;
            }

            .main-image {
                height: 350px;
            }
        }

        @media (max-width: 768px) {
            .property-stats {
                grid-template-columns: 1fr;
            }

            .properties-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a> <span>/</span>
            <a href="listing.php">Listings</a> <span>/</span>
            <span><?php echo htmlspecialchars($property['title']); ?></span>
        </div>
    </div>

    <!-- Property Detail -->
    <section class="property-detail">
        <div class="container">
            <div class="property-grid">
                <!-- Left Column: Gallery -->
                <div>
                    <div class="gallery-section">
                        <?php if ($property['main_image']): ?>
                            <img src="uploads/properties/<?php echo htmlspecialchars($property['main_image']); ?>"
                                alt="<?php echo htmlspecialchars($property['title']); ?>"
                                class="main-image" id="mainImage">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/800x500?text=No+Image"
                                alt="No image" class="main-image" id="mainImage">
                        <?php endif; ?>

                        <?php if (count($images) > 0): ?>
                            <div class="thumbnail-grid">
                                <?php if ($property['main_image']): ?>
                                    <img src="uploads/properties/<?php echo htmlspecialchars($property['main_image']); ?>"
                                        class="thumbnail active"
                                        onclick="changeMainImage(this.src)">
                                <?php endif; ?>

                                <?php foreach ($images as $img): ?>
                                    <img src="uploads/properties/<?php echo htmlspecialchars($img['image_path']); ?>"
                                        class="thumbnail"
                                        onclick="changeMainImage(this.src)">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="description-section">
                        <h2 class="section-title">Description</h2>
                        <p class="description-text"><?php echo htmlspecialchars($property['description']); ?></p>

                        <?php if ($property['features']): ?>
                            <h3 style="margin-top: 30px; color: var(--primary-color);">Features</h3>
                            <div class="features-grid">
                                <?php
                                $features = explode(',', $property['features']);
                                foreach ($features as $feature):
                                    $feature = trim($feature);
                                    if ($feature):
                                ?>
                                        <div class="feature-item">
                                            <i class="fas fa-check-circle"></i>
                                            <span><?php echo htmlspecialchars($feature); ?></span>
                                        </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Contact Form -->
                    <div class="contact-section">
                        <h2 class="section-title">Interested in This Property?</h2>

                        <?php if ($inquiry_message): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $inquiry_message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($inquiry_error): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $inquiry_error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="name" class="form-input" required>
                            </div>

                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" class="form-input" required>
                            </div>

                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" class="form-input">
                            </div>

                            <div class="form-group">
                                <label>Message *</label>
                                <textarea name="message" class="form-textarea" required>I am interested in <?php echo htmlspecialchars($property['title']); ?>. Please provide more details.</textarea>
                            </div>

                            <button type="submit" name="submit_inquiry" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-paper-plane"></i> Send Inquiry
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Info Card -->
                <div>
                    <div class="info-card">
                        <div class="price"><?php echo formatPrice($property['price']); ?></div>
                        <h1 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h1>
                        <div class="location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($property['location']); ?>
                        </div>

                        <?php
                        $status_class = 'status-' . strtolower($property['status']);
                        ?>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo ucfirst($property['status']); ?>
                        </span>

                        <div class="property-stats">
                            <div class="stat-item">
                                <i class="fas fa-home"></i>
                                <div>
                                    <div class="stat-label">Type</div>
                                    <div class="stat-value"><?php echo htmlspecialchars($property['property_type']); ?></div>
                                </div>
                            </div>

                            <div class="stat-item">
                                <i class="fas fa-ruler-combined"></i>
                                <div>
                                    <div class="stat-label">Area</div>
                                    <div class="stat-value"><?php echo number_format($property['area_sqm']); ?> sqm</div>
                                </div>
                            </div>

                            <?php if ($property['bedrooms'] > 0): ?>
                                <div class="stat-item">
                                    <i class="fas fa-bed"></i>
                                    <div>
                                        <div class="stat-label">Bedrooms</div>
                                        <div class="stat-value"><?php echo $property['bedrooms']; ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($property['bathrooms'] > 0): ?>
                                <div class="stat-item">
                                    <i class="fas fa-bath"></i>
                                    <div>
                                        <div class="stat-label">Bathrooms</div>
                                        <div class="stat-value"><?php echo $property['bathrooms']; ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="cta-buttons">
                            <a href="tel:+2348140097917" class="btn btn-primary">
                                <i class="fas fa-phone"></i> Call Us Now
                            </a>
                            <a href="https://wa.me/2348140097917?text=I'm interested in <?php echo urlencode($property['title']); ?>"
                                class="btn btn-secondary" target="_blank">
                                <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                            </a>
                            <a href="contact.php" class="btn btn-secondary">
                                <i class="fas fa-check-circle"></i> Request Verification
                            </a>
                        </div>

                        <?php if ($property['verification_status'] === 'verified'): ?>
                            <div style="margin-top: 20px; padding: 15px; background: #d4edda; border-radius: 4px; text-align: center;">
                                <i class="fas fa-shield-alt" style="color: #155724; font-size: 1.5rem;"></i>
                                <div style="color: #155724; font-weight: 600; margin-top: 8px;">Verified Property</div>
                                <div style="font-size: 0.85rem; color: #155724;">All documents authenticated</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Properties -->
    <?php if (count($similar_properties) > 0): ?>
        <section class="similar-properties">
            <div class="container">
                <h2 class="section-title">Similar Properties</h2>
                <div class="properties-grid">
                    <?php foreach ($similar_properties as $prop): ?>
                        <a href="property-details.php?id=<?php echo $prop['id']; ?>" class="property-card">
                            <?php if ($prop['main_image']): ?>
                                <img src="uploads/properties/<?php echo htmlspecialchars($prop['main_image']); ?>"
                                    alt="<?php echo htmlspecialchars($prop['title']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/300x220?text=No+Image" alt="No image">
                            <?php endif; ?>

                            <div class="property-card-body">
                                <div class="property-card-title"><?php echo htmlspecialchars($prop['title']); ?></div>
                                <div class="property-card-price"><?php echo formatPrice($prop['price']); ?></div>
                                <div class="property-card-location">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($prop['location']); ?>
                                </div>
                                <?php if ($prop['bedrooms'] > 0 || $prop['bathrooms'] > 0): ?>
                                    <div class="property-card-features">
                                        <?php if ($prop['bedrooms'] > 0): ?>
                                            <span><i class="fas fa-bed"></i> <?php echo $prop['bedrooms']; ?> Bed</span>
                                        <?php endif; ?>
                                        <?php if ($prop['bathrooms'] > 0): ?>
                                            <span><i class="fas fa-bath"></i> <?php echo $prop['bathrooms']; ?> Bath</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2026 SafeHaven. The Truth is Our Only Inventory.</p>
        </div>
    </footer>

    <script>
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;

            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
                if (thumb.src === src) {
                    thumb.classList.add('active');
                }
            });
        }
    </script>
</body>

</html>