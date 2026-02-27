<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | SafeHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include 'includes/header-styles.php'; ?>
    <style>
        /* PAGE-SPECIFIC STYLES FOR ABOUT PAGE */

        /* About Hero */
        .about-hero {

            --text-dark: #333333;
            --text-light: #f4f4f4;
            --white: #ffffff;
            --light-gray: #f4f5f4;
        }

        /* --- HERO SECTION --- */
        .about-hero {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)), url('https://images.unsplash.com/photo-1521791055366-0d553872125f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center 30%;
            color: white;
            padding: 120px 0;
            text-align: center;
        }

        .about-hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .about-hero p {
            max-width: 800px;
            margin: 0 auto;
            font-size: 1.3rem;
            color: var(--accent-color);
            font-style: italic;
        }

        /* --- MAIN NARRATIVE SECTION --- */
        .story-section {
            padding: 80px 0;
            max-width: 1000px;
            margin: 0 auto;
        }

        .story-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 40px;
            padding: 0 20px;
        }

        .story-text {
            font-size: 1.1rem;
            color: #444;
        }

        .story-text p {
            margin-bottom: 25px;
        }

        .highlight-text {
            border-left: 4px solid var(--accent-color);
            padding-left: 20px;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.2rem;
            background-color: var(--light-gray);
            padding: 20px;
            border-radius: 0 8px 8px 0;
        }

        /* --- MISSION & VISION SECTION (GLASS DESIGN) --- */
        .vm-section {
            background-color: var(--primary-color);
            background-image: radial-gradient(circle at 20% 20%, rgba(0, 102, 204, 0.1) 0%, transparent 20%), radial-gradient(circle at 80% 80%, rgba(0, 102, 204, 0.1) 0%, transparent 20%);
            color: white;
            padding: 100px 0;
        }

        .vm-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            padding: 0 20px;
        }

        .vm-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 12px;
            transition: transform 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .vm-card:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--accent-color);
            transform: translateY(-5px);
        }

        .vm-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: block;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .vm-card h2 {
            color: var(--accent-color);
            font-size: 1.8rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .vm-card p {
            font-size: 1.05rem;
            color: #e0e0e0;
            line-height: 1.8;
        }

        /* --- TEAM SECTION --- */
        .team-section {
            padding: 100px 0;
            text-align: center;
            background-color: var(--light-gray);
        }

        .team-grid {
            max-width: 1200px;
            margin: 50px auto 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }

        .team-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            border-bottom: 4px solid transparent;
        }

        .team-card:hover {
            transform: translateY(-5px);
            border-bottom: 4px solid var(--accent-color);
        }

        .team-photo {
            height: 300px;
            background-color: #ddd;
            background-image: url('https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80');
            background-size: cover;
            background-position: center;
        }

        .team-info {
            padding: 25px;
        }

        .team-info h4 {
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .team-info span {
            color: var(--accent-color);
            font-size: 0.9rem;
            font-weight: bold;
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
                transition: all 0.3s ease;
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

            .btn-nav {
                margin-top: 20px;
                width: 80%;
                max-width: 250px;
            }
        }

        @media (max-width: 768px) {
            .about-hero h1 {
                font-size: 2.2rem;
            }

            .story-section,
            .vm-section,
            .team-section {
                padding: 50px 0;
            }

            .vm-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .story-text {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <header class="about-hero">
        <div class="container">
            <h1>We Don’t Just List Properties;<br>We Illuminate Them.</h1>
            <p>"We pull every plot of land and every luxury structure out of the shadows."</p>
        </div>
    </header>

    <section class="story-section">
        <div class="story-container">
            <div class="story-text">
                <p>
                    In the fast-paced, often opaque world of Abuja real estate, <strong>SafeHaven</strong> was born out of a necessity for absolute transparency. We don’t just list properties; we secure them. Our name is our mandate: we pull every plot of land and every luxury structure out of the shadows of uncertainty and into the clarity of verified safety.
                </p>

                <div class="highlight-text">
                    "Our founder’s journey is one of rising above the noise and thriving in the face of bias and systemic challenges."
                </div>
                <br>

                <p>
                    This hard-won resilience is the engine behind our firm. We possess a sixth sense for the market, a deep intuitive understanding of Abuja’s urban pulse, and we use it to protect our clients from the scams and paper-thin deals that plague the industry.
                </p>

                <p>
                    We aren't just brokers; we are <strong>curators of peace of mind</strong>. Having seen firsthand the devastation of housing insecurity, we treat every client’s search as a mission to secure a legacy.
                </p>

                <p>
                    When we put our "SafeHaven" standard on a property, it means the titles are clean, the value is real, and the future is secure. At SafeHaven, we don’t do "maybe." We do verified.
                </p>
            </div>
        </div>
    </section>

    <section class="vm-section">
        <div class="vm-grid">
            <div class="vm-card">
                <span class="vm-icon">👁️</span>
                <h2>Our Vision</h2>
                <p>
                    To be the most trusted lens through which Africa views real estate, where every transaction is defined by absolute clarity and every client finds a place to truly belong.
                </p>
            </div>

            <div class="vm-card">
                <span class="vm-icon">🛡️</span>
                <h2>Our Mission</h2>
                <p>
                    To eliminate the shadows of doubt in the Abuja property market by providing rigorously verified real estate solutions. We leverage deep market insights and a culture of resilience to secure safe, dignified homes for families and high-yield assets for investors.
                </p>
            </div>
        </div>
    </section>

    <section class="team-section">
        <h2 style="font-size: 2.2rem; color: var(--primary-color); margin-bottom: 10px;">The Curators</h2>
        <p style="color: #666; margin-bottom: 40px;">The team dedicated to your peace of mind.</p>

        <div class="team-grid">
            <div class="team-card">
                <div class="team-photo"></div>
                <div class="team-info">
                    <h4>[Founder Name]</h4>
                    <span>Lead Consultant</span>
                </div>
            </div>
            <div class="team-card">
                <div class="team-photo" style="background-image: url('https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80');"></div>
                <div class="team-info">
                    <h4>[Name]</h4>
                    <span>Head of Verification</span>
                </div>
            </div>
            <div class="team-card">
                <div class="team-photo" style="background-image: url('https://images.unsplash.com/photo-1556157382-97eda2d62296?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80');"></div>
                <div class="team-info">
                    <h4>[Name]</h4>
                    <span>Client Success</span>
                </div>
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