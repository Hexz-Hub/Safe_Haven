<?php
session_start();
include_once 'config/database.php';
$db = new \Database();
$conn = $db->getConnection();
$news_items = [];
// determine user link label
$user_link = 'user-login.php';
$user_label = 'Login';
if (isset($_SESSION['user_id'])) {
    $user_link = 'user-dashboard.php';
    $user_label = 'Dashboard';
}
try {
    $limit = 6;
    $stmt = $conn->prepare("SELECT * FROM news WHERE status = 'published' ORDER BY published_at DESC, created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    $news_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $news_items = [];
}

// Fetch virtual tours
$virtual_tours = [];
try {
    $stmt = $conn->prepare("SELECT * FROM virtual_tours WHERE status = 'active' ORDER BY display_order ASC");
    $stmt->execute();
    $virtual_tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $virtual_tours = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | SafeHaven Estate Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR HOME PAGE */

        .section-title {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        .section-title h2 {
            font-size: 2.8rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 700;
            line-height: 1.2;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        /* HAMBURGER MENU (Mobile) */
        .hamburger {
            display: block;
            cursor: pointer;
            background: none;
            border: none;
            width: 30px;
            height: 20px;
            position: relative;
            z-index: 1001;
        }

        @media (min-width: 992px) {
            .hamburger {
                display: none;
            }
        }

        .bar {
            display: block;
            width: 100%;
            height: 3px;
            background-color: var(--accent-color);
            transition: var(--transition);
            position: absolute;
            left: 0;
        }

        .bar:nth-child(1) {
            top: 0;
        }

        .bar:nth-child(2) {
            top: 8px;
        }

        .bar:nth-child(3) {
            top: 16px;
        }

        .hamburger.active .bar:nth-child(1) {
            top: 8px;
            transform: rotate(45deg);
        }

        .hamburger.active .bar:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active .bar:nth-child(3) {
            top: 8px;
            transform: rotate(-45deg);
        }

        @media (max-width: 991px) {
            .nav-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                padding: 40px 20px;
                gap: 0;
                transition: var(--transition);
                overflow-y: auto;
                z-index: 999;
            }

            .nav-links.active {
                left: 0;
            }

            .nav-links li {
                width: 100%;
                text-align: center;
                margin: 10px 0;
            }

            .nav-links a {
                display: block;
                padding: 15px;
                font-size: 1.2rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .nav-links a::after {
                display: none;
            }
        }

        /* --- HERO SECTION (Enhanced) --- */
        .hero {
            min-height: 90vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(rgba(31, 43, 31, 0.9), rgba(47, 62, 46, 0.8)),
                url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .hero-content {
            text-align: center;
            color: var(--white);
            position: relative;
            z-index: 1;
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 20px;
        }

        .hero-content h1 {
            font-size: 3.2rem;
            margin-bottom: 25px;
            line-height: 1.1;
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            color: rgba(255, 255, 255, 0.9);
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-btns {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.2rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .hero-btns {
                flex-direction: column;
                align-items: center;
            }

            .hero-btns .btn {
                width: 100%;
                max-width: 300px;
            }
        }

        /* --- INTRO SECTION (Glass) --- */
        .intro {
            padding: 100px 0;
            background: linear-gradient(135deg, rgba(47, 62, 46, 0.95), rgba(31, 43, 31, 0.9)),
                url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80');
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .intro-glass-card {
            max-width: 1000px;
            margin: 0 auto;
            padding: 70px 50px;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 102, 204, 0.3);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .intro-glass-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(0, 102, 204, 0.1) 0%, transparent 70%);
            z-index: 0;
        }

        .intro-glass-card>* {
            position: relative;
            z-index: 1;
        }

        .intro-glass-card h2 {
            color: var(--accent-color);
            font-size: 2.8rem;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .intro-glass-card blockquote {
            font-size: 1.5rem;
            font-style: italic;
            color: var(--white);
            line-height: 1.6;
            position: relative;
            padding-left: 30px;
            border-left: 4px solid var(--accent-color);
        }

        @media (max-width: 768px) {
            .intro {
                padding: 60px 0;
            }

            .intro-glass-card {
                padding: 40px 25px;
            }

            .intro-glass-card h2 {
                font-size: 2rem;
            }

            .intro-glass-card blockquote {
                font-size: 1.2rem;
                padding-left: 20px;
            }
        }

        /* --- WHY CHOOSE US --- */
        .why-us {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            position: relative;
            overflow: hidden;
        }

        .why-us::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(0, 102, 204, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(0, 102, 204, 0.1) 0%, transparent 20%);
            z-index: 0;
        }

        .why-us .container {
            position: relative;
            z-index: 1;
        }

        .why-us .section-title h2 {
            color: var(--white);
        }

        .why-us .section-title p {
            color: rgba(255, 255, 255, 0.8);
        }

        .why-us-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        @media (min-width: 992px) {
            .why-us-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .why-card {
            background: rgba(255, 255, 255, 0.07);
            padding: 35px 25px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .why-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-color);
            box-shadow: var(--shadow-lg);
            background: rgba(255, 255, 255, 0.12);
        }

        .why-card .why-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
            color: var(--accent-color);
        }

        .why-card h3 {
            margin-bottom: 15px;
            color: var(--accent-color);
            font-size: 1.4rem;
            font-weight: 600;
        }

        .why-card p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.6;
            flex-grow: 1;
        }

        /* --- LISTINGS --- */
        .listings {
            padding: 100px 0;
            background-color: var(--light-gray);
        }

        .listing-filters {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 50px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 24px;
            border: 1px solid #ddd;
            background: transparent;
            cursor: pointer;
            border-radius: 30px;
            font-weight: 500;
            transition: var(--transition);
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            border-color: var(--primary-color);
        }

        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        @media (min-width: 1200px) {
            .listings-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .listing-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
        }

        .listing-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .listing-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--accent-color);
            color: var(--primary-dark);
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .listing-img {
            height: 250px;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
            position: relative;
            transition: transform 0.5s ease;
        }

        .listing-card:hover .listing-img {
            transform: scale(1.05);
        }

        .listing-details {
            padding: 25px;
        }

        .listing-price {
            color: var(--accent-color);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .listing-loc {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* --- VIDEO SLIDER (Enhanced) --- */
        .video-section {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            position: relative;
            overflow: hidden;
        }

        .video-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--accent-color), var(--gold-light));
        }

        .slider-container {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }

        .slider-wrapper {
            overflow: hidden;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
        }

        .slider-track {
            display: flex;
            transition: transform 0.5s ease-in-out;
            gap: 0;
        }

        .video-slide {
            flex: 0 0 100%;
            padding: 10px;
        }

        @media (min-width: 768px) {
            .video-slide {
                flex: 0 0 50%;
            }
        }

        @media (min-width: 992px) {
            .video-slide {
                flex: 0 0 33.333%;
            }
        }

        .video-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--border-radius);
            overflow: hidden;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .video-item:hover {
            border-color: var(--accent-color);
            transform: translateY(-5px);
        }

        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
        }

        .video-wrapper iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .video-info {
            padding: 20px;
        }

        .video-info h3 {
            color: var(--accent-color);
            margin-bottom: 8px;
            font-size: 1.3rem;
        }

        .slider-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 40px;
        }

        .slider-btn {
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .slider-btn:hover {
            background: var(--accent-color);
            color: var(--primary-color);
            border-color: var(--accent-color);
        }

        .slider-dots {
            display: flex;
            gap: 10px;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: var(--transition);
        }

        .slider-dot.active {
            background: var(--accent-color);
            transform: scale(1.2);
        }

        /* --- NEWS SECTION --- */
        .news-section {
            padding: 100px 0;
            background-color: var(--white);
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        @media (min-width: 1200px) {
            .news-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .news-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        /* Carousel helpers (used when news-grid contains a dynamic carousel) */
        .news-carousel {
            position: relative;
            overflow: hidden;
        }

        .news-track {
            display: flex;
            transition: transform 0.5s ease;
        }

        .news-card {
            min-width: 100%;
            box-sizing: border-box;
            padding: 18px;
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            border: none;
            padding: 12px 14px;
            border-radius: 6px;
            cursor: pointer;
            z-index: 5;
        }

        .carousel-btn.prev {
            left: 12px;
        }

        .carousel-btn.next {
            right: 12px;
        }

        .news-img-wrapper {
            position: relative;
            overflow: hidden;
            height: 220px;
        }

        .news-img {
            height: 220px;
            background-color: #ddd;
            background-size: cover;
            background-position: center;
            position: relative;
            transition: transform 0.5s ease;
        }

        .news-card:hover .news-img {
            transform: scale(1.05);
        }

        /* Image slider styles for news cards with multiple images */
        .image-slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 220px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 5;
        }

        .news-card:hover .image-slider {
            pointer-events: auto;
            opacity: 1;
        }

        .slider-images {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .slider-image {
            position: absolute;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .slider-image.active {
            opacity: 1;
        }

        .slider-dots {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 10;
        }

        .slider-dots .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s;
        }

        .slider-dots .dot.active {
            background: var(--accent-color);
            transform: scale(1.3);
        }

        .slider-dots .dot:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        .news-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .news-date {
            color: var(--accent-color);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .news-content h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.4rem;
            line-height: 1.3;
        }

        .news-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .read-more {
            color: var(--accent-color);
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .read-more:hover {
            color: var(--primary-color);
            gap: 10px;
        }

        /* --- FOOTER (Enhanced) --- */
        footer {
            background: linear-gradient(135deg, var(--primary-dark), #152015);
            color: #a3bfa3;
            padding: 80px 0 30px;
            position: relative;
            overflow: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--accent-color), var(--gold-light));
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 50px;
            margin-bottom: 50px;
        }

        @media (min-width: 768px) {
            .footer-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .footer-col h4 {
            color: var(--accent-color);
            font-size: 1.2rem;
            margin-bottom: 25px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--accent-color);
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col ul li a:hover {
            color: var(--accent-color);
            padding-left: 5px;
        }

        .footer-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--accent-color);
            margin-bottom: 20px;
            display: block;
        }

        .footer-about p {
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .footer-contact li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 15px;
        }

        .footer-contact i {
            color: var(--accent-color);
            margin-top: 3px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }

        /* WhatsApp styles are now in header-styles.php */

        /* --- LOADING ANIMATION --- */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-contact">
                <span><i class="fas fa-phone"></i> +234 814 009 7917</span>
                <span><i class="fas fa-envelope"></i> info@spotlightlistings.ng</span>
                <span><i class="fas fa-map-marker-alt"></i> Delta State: Asaba, Agbor, Kwale, Abraka, Oghara, Ozoro</span>
            </div>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
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
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="listing.php">Listings</a></li>
                <li><a href="verification.php">Verification</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="verification.php" class="btn btn-primary btn-nav">Verify a Property</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li style="display:flex;align-items:center;padding-left:12px;color:var(--white);font-weight:600;">
                        Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </li>
                <?php endif; ?>
                <li><a href="<?php echo htmlspecialchars($user_link); ?>" class="btn btn-primary btn-nav"><?php echo htmlspecialchars($user_label); ?></a></li>
            </ul>

            <button class="hamburger" aria-label="Toggle menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <div class="container">
            <div class="hero-content animate">
                <h1>The Most Trusted Lens Through Which Africa Views Real Estate.</h1>
                <p>We don't just sell potential. We sell verified reality. Secure safe, dignified homes and high-yield assets in Delta State: Asaba, Agbor, Kwale, Abraka, Oghara, Ozoro.</p>
                <div class="hero-btns">
                    <a href="listing.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Find Your Home
                    </a>
                    <a href="verification.php" class="btn btn-secondary">
                        <i class="fas fa-shield-alt"></i> See How We Verify
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Intro Section -->
    <section class="intro">
        <div class="container">
            <div class="intro-glass-card animate">
                <h2>The Truth is Our Only Inventory</h2>
                <blockquote>
                    "Unlike traditional brokers who sell potential, we sell verified reality. We combine deep Delta State market knowledge with a proprietary verification process. If it isn't safe enough for our own family, it doesn't make it into our spotlight."
                </blockquote>
            </div>
        </div>
    </section>

    <script>
        // News carousel logic
        (function() {
            const track = document.getElementById('newsTrack');
            if (!track) return;
            const slides = track.children;
            let index = 0;

            function show(i) {
                if (i < 0) i = slides.length - 1;
                if (i >= slides.length) i = 0;
                index = i;
                track.style.transform = 'translateX(' + (-index * 100) + '%)';
            }

            var prevBtn = document.getElementById('newsPrev');
            var nextBtn = document.getElementById('newsNext');
            if (prevBtn) prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                show(index - 1);
            });
            if (nextBtn) nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                show(index + 1);
            });

            // initialize and auto rotate every 5s
            show(0);
            if (slides.length > 1) {
                setInterval(function() {
                    show(index + 1);
                }, 5000);
            }
        })();
    </script>

    <!-- Why Choose Us -->
    <section class="why-us">
        <div class="container">
            <div class="section-title animate">
                <h2>Why Choose Us?</h2>
                <p>We sell safety, disguised as luxury real estate.</p>
            </div>
            <div class="why-us-grid">
                <div class="why-card animate">
                    <span class="why-icon">🤝</span>
                    <h3>Radical Integrity</h3>
                    <p>We are humane, direct, and refreshingly honest. We would rather lose a commission than see a client lose their life savings.</p>
                </div>
                <div class="why-card animate">
                    <span class="why-icon">🔦</span>
                    <h3>The Spotlight Standard</h3>
                    <p>Our "Spotlight" isn't a metaphor—it is a glare of certainty, a process that ensures any land or building we recommend is legally bulletproof.</p>
                </div>
                <div class="why-card animate">
                    <span class="why-icon">🧠</span>
                    <h3>The Insider Intelligence</h3>
                    <p>Delta State is a region of opportunity. We have our ears to the ground and our eyes on the future to give you a distinct advantage.</p>
                </div>
                <div class="why-card animate">
                    <span class="why-icon">🔥</span>
                    <h3>The Resilience Factor</h3>
                    <p>Our brand was forged in the fire. We are the "tough-as-nails" partner you want in your corner when navigating the FCT.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Listings Section -->
    <section class="listings" id="listings">
        <div class="container">
            <div class="section-title animate">
                <h2>Curated. Verified. Ready.</h2>
                <p>Explore our carefully selected properties that have passed the Spotlight verification process.</p>
            </div>

            <div class="listing-filters">
                <button class="filter-btn active">All Properties</button>
                <button class="filter-btn">Residential</button>
                <button class="filter-btn">Commercial</button>
                <button class="filter-btn">Luxury</button>
                <button class="filter-btn">Verified</button>
            </div>

            <div class="listings-grid">
                <!-- PHP content would go here -->
                <div class="listing-card animate">
                    <div class="listing-badge">Verified</div>
                    <div class="listing-img" style="background-image: url('https://images.unsplash.com/photo-1613977257363-707ba9348227?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="listing-details">
                        <div class="listing-price">₦ 350,000,000</div>
                        <h4>5 Bedroom Luxury Duplex</h4>
                        <div class="listing-loc"><i class="fas fa-map-marker-alt"></i> Asaba, Delta State</div>
                        <p>Modern duplex with panoramic views, smart home features, and premium finishes.</p>
                        <a href="<?php echo isset($_SESSION['user_id']) ? 'property-details.php?id=1' : 'user-login.php'; ?>" class="btn btn-primary" style="width:100%; margin-top:15px;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>

                <div class="listing-card animate">
                    <div class="listing-badge">Featured</div>
                    <div class="listing-img" style="background-image: url('https://images.unsplash.com/photo-1518780664697-55e3ad937233?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="listing-details">
                        <div class="listing-price">₦ 180,000,000</div>
                        <h4>3 Bedroom Terrace</h4>
                        <div class="listing-loc"><i class="fas fa-map-marker-alt"></i> Agbor, Delta State</div>
                        <p>Spacious terrace with private garden, modern kitchen, and security features.</p>
                        <a href="<?php echo isset($_SESSION['user_id']) ? 'property-details.php?id=2' : 'user-login.php'; ?>" class="btn btn-primary" style="width:100%; margin-top:15px;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>

                <div class="listing-card animate">
                    <div class="listing-badge">Verified</div>
                    <div class="listing-img" style="background-image: url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');"></div>
                    <div class="listing-details">
                        <div class="listing-price">₦ 650,000,000</div>
                        <h4>Commercial Plaza</h4>
                        <div class="listing-loc"><i class="fas fa-map-marker-alt"></i> Kwale, Delta State</div>
                        <p>Prime commercial space with 20 shops, offices, and ample parking.</p>
                        <a href="<?php echo isset($_SESSION['user_id']) ? 'property-details.php?id=3' : 'user-login.php'; ?>" class="btn btn-primary" style="width:100%; margin-top:15px;">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="<?php echo isset($_SESSION['user_id']) ? 'listing.php' : 'user-login.php'; ?>" class="btn btn-outline">
                    <i class="fas fa-list"></i> View Full Inventory
                </a>
            </div>
        </div>
    </section>

    <!-- Video Section -->
    <section class="video-section">
        <div class="container">
            <div class="section-title animate">
                <h2 style="color:white;">Virtual Property Tours</h2>
                <p style="color:#ccc;">Experience our verified listings from the comfort of your screen.</p>
            </div>

            <?php if (!empty($virtual_tours)): ?>
                <div class="slider-container">
                    <div class="slider-wrapper">
                        <div class="slider-track" id="track">
                            <?php foreach ($virtual_tours as $tour): ?>
                                <div class="video-slide">
                                    <div class="video-item">
                                        <div class="video-wrapper">
                                            <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($tour['youtube_video_id']); ?>?controls=1"
                                                allowfullscreen
                                                loading="lazy"></iframe>
                                        </div>
                                        <div class="video-info">
                                            <h3><?php echo htmlspecialchars($tour['title']); ?></h3>
                                            <p><?php echo htmlspecialchars($tour['description']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="slider-controls">
                        <button class="slider-btn prev" id="prevBtn" aria-label="Previous slide">
                            <i class="fas fa-chevron-left"></i>
                        </button>

                        <div class="slider-dots" id="dotsContainer">
                            <?php foreach ($virtual_tours as $index => $tour): ?>
                                <span class="slider-dot <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-slide="<?php echo $index; ?>"></span>
                            <?php endforeach; ?>
                        </div>

                        <button class="slider-btn next" id="nextBtn" aria-label="Next slide">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; color: #ccc;">
                    <i class="fas fa-video" style="font-size: 64px; margin-bottom: 20px; opacity: 0.5;"></i>
                    <p>No virtual tours available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- News Section -->
    <section class="news-section">
        <div class="container">
            <div class="section-title animate">
                <h2>Latest News & Insights</h2>
                <p>Stay updated with the latest real estate trends and market insights</p>
            </div>

            <div class="news-grid">
                <?php if (count($news_items) > 0): ?>
                    <div class="news-carousel">
                        <button class="carousel-btn prev" id="newsPrev"><i class="fas fa-chevron-left"></i></button>
                        <div class="news-track" id="newsTrack">
                            <?php foreach ($news_items as $n): ?>
                                <?php
                                // Fetch all images for this news article
                                $img_list_query = "SELECT image_path FROM news_images WHERE news_id = :id ORDER BY display_order ASC";
                                $img_list_stmt = $conn->prepare($img_list_query);
                                $img_list_stmt->bindParam(':id', $n['id']);
                                $img_list_stmt->execute();
                                $all_images = $img_list_stmt->fetchAll(PDO::FETCH_ASSOC);

                                // Get first image or fallback
                                $img = '';
                                if (count($all_images) > 0 && file_exists(__DIR__ . '/uploads/news/' . $all_images[0]['image_path'])) {
                                    $img = 'uploads/news/' . $all_images[0]['image_path'];
                                } elseif (!empty($n['image']) && file_exists(__DIR__ . '/uploads/news/' . $n['image'])) {
                                    $img = 'uploads/news/' . $n['image'];
                                } else {
                                    $img = 'https://images.unsplash.com/photo-1505691723518-36a4b6a29e1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80';
                                }
                                $date = !empty($n['published_at']) ? date('M d, Y', strtotime($n['published_at'])) : date('M d, Y', strtotime($n['created_at']));
                                ?>
                                <div class="news-card animate">
                                    <div class="news-img-wrapper">
                                        <div class="news-img" style="background-image: url('<?php echo htmlspecialchars($img); ?>');"></div>
                                        <?php if (count($all_images) > 1): ?>
                                            <div class="image-slider" data-article-id="<?php echo $n['id']; ?>">
                                                <div class="slider-images">
                                                    <?php foreach ($all_images as $idx => $img_item): ?>
                                                        <div class="slider-image <?php echo $idx == 0 ? 'active' : ''; ?>" style="background-image: url('uploads/news/<?php echo htmlspecialchars($img_item['image_path']); ?>');"></div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="slider-dots">
                                                    <?php for ($i = 0; $i < count($all_images); $i++): ?>
                                                        <span class="dot <?php echo $i == 0 ? 'active' : ''; ?>" onclick="goToImageSlide(this)"></span>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="news-content">
                                        <div class="news-date"><i class="far fa-calendar"></i> <?php echo $date; ?></div>
                                        <h3><?php echo htmlspecialchars($n['title']); ?></h3>
                                        <p><?php echo htmlspecialchars(mb_strimwidth($n['excerpt'] ?: $n['content'], 0, 160, '...')); ?></p>
                                        <a href="news.php?slug=<?php echo urlencode($n['slug']); ?>" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-btn next" id="newsNext"><i class="fas fa-chevron-right"></i></button>
                    </div>
                <?php else: ?>
                    <p style="color:#666;">No news articles found.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="#" class="footer-logo">SPOTLIGHT LISTINGS</a>
                    <p class="footer-about">"Eliminating the shadows of doubt in the Delta State property market through radical transparency and verified reality."</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="services.php">Our Services</a></li>
                        <li><a href="listing.php">Property Listings</a></li>
                        <li><a href="verification.php">Verification Process</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Property Types</h4>
                    <ul>
                        <li><a href="listing.php?type=residential">Residential Properties</a></li>
                        <li><a href="listing.php?type=commercial">Commercial Properties</a></li>
                        <li><a href="listing.php?type=luxury">Luxury Homes</a></li>
                        <li><a href="listing.php?type=land">Land & Plots</a></li>
                        <li><a href="listing.php?type=verified">Verified Properties</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Contact Info</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Delta State: Asaba, Agbor, Kwale, Abraka, Oghara, Ozoro</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>+234 814 009 7917</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>info@spotlightlistings.ng</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Mon - Fri: 9am - 6pm<br>Sat: 10am - 4pm</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 SafeHaven. All Rights Reserved. | Designed for Delta State's Real Estate Community</p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/2348140097917" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i> Chat on WhatsApp
    </a>

    <script>
        // HAMBURGER MENU
        const hamburger = document.querySelector(".hamburger");
        const navLinks = document.querySelector(".nav-links");

        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navLinks.classList.toggle("active");
            document.body.style.overflow = navLinks.classList.contains("active") ? "hidden" : "";
        });

        document.querySelectorAll(".nav-links a").forEach(link => {
            link.addEventListener("click", () => {
                hamburger.classList.remove("active");
                navLinks.classList.remove("active");
                document.body.style.overflow = "";
            });
        });

        // SLIDER FUNCTIONALITY
        const track = document.getElementById('track');
        const slides = document.querySelectorAll('.video-slide');
        const dots = document.querySelectorAll('.slider-dot');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        let currentSlide = 0;
        let slidesPerView = 1;
        let autoPlayInterval;
        const autoPlayDelay = 5000; // 5 seconds

        function updateSlidesPerView() {
            if (window.innerWidth >= 992) {
                slidesPerView = 3;
            } else if (window.innerWidth >= 768) {
                slidesPerView = 2;
            } else {
                slidesPerView = 1;
            }
            updateSliderPosition();
        }

        function updateSliderPosition() {
            if (slides.length === 0) return;

            const slideWidth = slides[0].offsetWidth;
            track.style.transform = `translateX(-${currentSlide * slideWidth}px)`;

            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentSlide);
            });
        }

        function moveSlide(direction) {
            const maxSlide = slides.length - slidesPerView;
            currentSlide += direction;

            if (currentSlide < 0) {
                currentSlide = maxSlide; // Loop to end
            } else if (currentSlide > maxSlide) {
                currentSlide = 0; // Loop to start
            }

            updateSliderPosition();
            resetAutoPlay();
        }

        function goToSlide(index) {
            const maxSlide = slides.length - slidesPerView;
            if (index >= 0 && index <= maxSlide) {
                currentSlide = index;
                updateSliderPosition();
                resetAutoPlay();
            }
        }

        function startAutoPlay() {
            if (slides.length > 0) {
                autoPlayInterval = setInterval(() => {
                    moveSlide(1);
                }, autoPlayDelay);
            }
        }

        function stopAutoPlay() {
            if (autoPlayInterval) {
                clearInterval(autoPlayInterval);
            }
        }

        function resetAutoPlay() {
            stopAutoPlay();
            startAutoPlay();
        }

        // Event Listeners
        if (prevBtn) prevBtn.addEventListener('click', () => moveSlide(-1));
        if (nextBtn) nextBtn.addEventListener('click', () => moveSlide(1));

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });

        // Pause autoplay on hover
        const sliderContainer = document.querySelector('.slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoPlay);
            sliderContainer.addEventListener('mouseleave', startAutoPlay);
        }

        // Initialize
        if (slides.length > 0) {
            updateSlidesPerView();
            startAutoPlay();
            window.addEventListener('resize', updateSlidesPerView);
        }

        // SCROLL ANIMATIONS
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                }
            });
        }, observerOptions);

        // Observe all animate elements
        document.querySelectorAll('.animate').forEach(el => {
            observer.observe(el);
        });

        // FILTER BUTTONS
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Here you would filter listings based on the button clicked
                // For now, just log the filter
                console.log('Filtering by:', this.textContent);
            });
        });

        // NEWS CARD IMAGE SLIDER
        function goToImageSlide(dotElement) {
            try {
                const slider = dotElement.closest('.image-slider');
                if (!slider) return;

                const dots = Array.from(slider.querySelectorAll('.dot'));
                const images = Array.from(slider.querySelectorAll('.slider-image'));
                const index = dots.indexOf(dotElement);

                if (index === -1) return;

                // Remove active class from all
                images.forEach(img => img.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));

                // Add active class to selected
                if (images[index]) images[index].classList.add('active');
                dotElement.classList.add('active');
            } catch (e) {
                console.error('Error in goToImageSlide:', e);
            }
        }

        // Auto-rotate images on card hover
        setTimeout(() => {
            document.querySelectorAll('.image-slider').forEach(slider => {
                let currentIndex = 0;
                const dots = Array.from(slider.querySelectorAll('.dot'));
                let autoRotate;

                if (dots.length < 2) return; // Skip if less than 2 images

                const card = slider.closest('.news-card');
                if (!card) return;

                card.addEventListener('mouseenter', () => {
                    autoRotate = setInterval(() => {
                        currentIndex = (currentIndex + 1) % dots.length;
                        goToImageSlide(dots[currentIndex]);
                    }, 2000); // Rotate every 2 seconds
                });

                card.addEventListener('mouseleave', () => {
                    if (autoRotate) clearInterval(autoRotate);
                    currentIndex = 0;
                    goToImageSlide(dots[0]);
                });
            });
        }, 100);
    </script>
</body>

</html>