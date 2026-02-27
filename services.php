<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* Page-specific styles */

        /* --- HERO SECTION --- */
        .services-hero {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)), url('https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .services-hero h1 {
            font-size: 3rem;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .services-hero p {
            max-width: 700px;
            margin: 0 auto;
            color: var(--accent-color);
            font-size: 1.2rem;
            font-style: italic;
        }

        /* --- REAL ESTATE SERVICES --- */
        .re-services {
            padding: 80px 0;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            color: var(--primary-color);
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .section-header p {
            color: #666;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            /* Adjusted for mobile */
            gap: 40px;
            padding: 0 20px;
        }

        .service-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border-top: 5px solid var(--accent-color);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            padding: 40px;
            flex-grow: 1;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: inline-block;
            background: var(--light-bg);
            width: 70px;
            height: 70px;
            line-height: 70px;
            text-align: center;
            border-radius: 50%;
        }

        .service-card h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .service-card p {
            color: #555;
            margin-bottom: 20px;
        }

        .feature-list {
            list-style: none;
            margin-bottom: 30px;
        }

        .feature-list li {
            margin-bottom: 10px;
            padding-left: 25px;
            position: relative;
            color: #444;
        }

        .feature-list li::before {
            content: '✓';
            color: var(--accent-color);
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .btn-outline {
            display: inline-block;
            padding: 12px 30px;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            transition: 0.3s;
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* --- MEDIA SERVICES SECTION --- */
        .media-section {
            background-color: var(--primary-color);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        /* Decorative Gold Circle */
        .media-section::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            border: 20px solid rgba(0, 102, 204, 0.05);
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .media-intro {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 50px;
        }

        .media-intro h2 {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 15px;
        }

        .media-intro p {
            max-width: 700px;
            margin: 0 auto;
            color: #ccc;
        }

        .media-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s;
        }

        .media-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-color);
        }

        .media-card h4 {
            font-size: 1.2rem;
            color: white;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .media-card p {
            font-size: 0.9rem;
            color: #aaa;
        }

        .media-icon {
            color: var(--accent-color);
            font-size: 1.2rem;
        }

        .btn-gold-full {
            background-color: var(--accent-color);
            color: var(--primary-color);
            padding: 15px 40px;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-top: 40px;
            text-transform: uppercase;
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

            /* Content Adjustments */
            .services-hero h1 {
                font-size: 2.2rem;
            }

            .service-grid,
            .media-grid {
                grid-template-columns: 1fr;
            }

            .section-header h2,
            .media-intro h2 {
                font-size: 1.8rem;
            }

            .re-services,
            .media-section {
                padding: 50px 0;
            }

            .card-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <header class="services-hero">
        <div class="container">
            <h1>Services That Illuminate Value</h1>
            <p>From securing your assets to amplifying your brand, we bring everything into the SafeHaven standard.</p>
        </div>
    </header>

    <section class="re-services">
        <div class="section-header">
            <h2>Real Estate Solutions</h2>
            <p>Expert guidance and rigorous checks for your peace of mind.</p>
        </div>

        <div class="service-grid">

            <div class="service-card">
                <div class="card-body">
                    <span class="card-icon">🏠</span>
                    <h3>Book an Inspection</h3>
                    <p>Don't rely on pictures alone. Schedule a physical or virtual tour of our verified listings. We walk you through the property, pointing out both the features and the flaws.</p>
                    <ul class="feature-list">
                        <li>Physical Tours (Abuja)</li>
                        <li>Virtual/Video Tours (Diaspora Clients)</li>
                        <li>Neighborhood Assessment</li>
                        <li>Structural Walkthrough</li>
                    </ul>
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'schedule-inspection.php' : 'user-login.php'; ?>" class="btn-outline">Schedule Inspection</a>
                </div>
            </div>

            <div class="service-card">
                <div class="card-body">
                    <span class="card-icon">🤝</span>
                    <h3>Book a Consultation</h3>
                    <p>New to the Abuja market? Need investment strategy? Book a session with our Lead Consultants. We provide data-backed advice to help you avoid scams and maximize ROI.</p>
                    <ul class="feature-list">
                        <li>Investment Strategy Sessions</li>
                        <li>Legal & Title Advisory</li>
                        <li>Market Valuation Insights</li>
                        <li>"Safe Purchase" Roadmap</li>
                    </ul>
                    <a href="<?php echo isset($_SESSION['user_id']) ? 'book-consultation.php' : 'user-login.php'; ?>" class="btn-outline">Book Consultation</a>
                </div>
            </div>

        </div>
    </section>

    <section class="media-section">
        <div class="media-grid">

            <div class="media-intro">
                <h2>SafeHaven Media Hub</h2>
                <p>We don't just sell real estate; we are experts in showcasing it. Leverage our media team to illuminate your brand, events, and products.</p>
            </div>

            <div class="media-card">
                <h4><span class="media-icon">📸</span> Event Coverage</h4>
                <p>Professional photography and videography for real estate launches, corporate events, and open houses. We capture the essence of luxury.</p>
            </div>

            <div class="media-card">
                <h4><span class="media-icon">🎬</span> Content Creation</h4>
                <p>High-end reels, property tours, and brand storytelling. We create visual assets that stop the scroll and drive engagement.</p>
            </div>

            <div class="media-card">
                <h4><span class="media-icon">📱</span> Social Media Management</h4>
                <p>Let us handle your digital presence. We manage community engagement, posting schedules, and brand voice consistency.</p>
            </div>

            <div class="media-card">
                <h4><span class="media-icon">🚀</span> Digital Marketing Services</h4>
                <p>Strategic campaigns designed to generate leads. We don't just post; we position your brand for growth.</p>
            </div>

            <div class="media-card">
                <h4><span class="media-icon">📈</span> Google & Social Ads</h4>
                <p>Targeted PPC and Social Media advertising campaigns. We get your message in front of the exact audience looking to buy.</p>
            </div>

            <div class="media-card">
                <h4><span class="media-icon">📧</span> Email Marketing</h4>
                <p>Direct access to your audience's inbox. We craft newsletters and drip campaigns that nurture leads and drive conversions.</p>
            </div>

            <div style="grid-column: 1 / -1; text-align: center;">
                <a href="request-media-services.php" class="btn-gold-full">REQUEST MEDIA SERVICES</a>
            </div>

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