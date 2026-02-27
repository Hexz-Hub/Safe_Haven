<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Insights: Guzape vs. Maitama | SafeHaven</title>
    <style>
        /* --- CORE STYLES --- */
        :root {
            --primary-navy: #4B2C6B;
            --accent-color: #D4AF37;
            --text-dark: #2D1B47;
            --light-bg: #F5F3FF;
            --white: #ffffff;
            --vs-red: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Nav (Simplified) */
        nav {
            background-color: var(--primary-navy);
            padding: 15px 0;
            color: white;
        }

        nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--accent-color);
            text-decoration: none;
        }

        /* --- HERO SECTION --- */
        .insight-hero {
            background: linear-gradient(rgba(75, 44, 107, 0.6), rgba(45, 27, 71, 0.55)),
                url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 60px 0 100px;
            /* Extra padding bottom for overlap */
            text-align: center;
        }

        .insight-hero h1 {
            font-size: 2.8rem;
            margin-bottom: 10px;
        }

        .insight-tag {
            background-color: var(--accent-color);
            color: var(--primary-navy);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* --- THE BATTLE CONTAINER --- */
        .battle-container {
            max-width: 1000px;
            margin: -60px auto 50px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            position: relative;
        }

        /* VS Badge */
        .vs-badge {
            position: absolute;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--white);
            border: 4px solid var(--primary-navy);
            color: var(--primary-navy);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-style: italic;
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Neighborhood Cards */
        .hood-card {
            background: white;
            padding: 0;
            border-radius: 8px;
            /* Rounded outer corners only handled via overflow */
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .hood-card.left {
            border-radius: 8px 0 0 8px;
            border-right: 1px solid #eee;
        }

        .hood-card.right {
            border-radius: 0 8px 8px 0;
        }

        .hood-img {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .hood-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            color: white;
            padding: 20px;
        }

        .hood-title h2 {
            font-size: 2rem;
        }

        .hood-title span {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .hood-body {
            padding: 30px;
        }

        /* Stats & Bars */
        .stat-row {
            margin-bottom: 20px;
        }

        .stat-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .progress-bg {
            width: 100%;
            background-color: #eee;
            height: 8px;
            border-radius: 4px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background-color: var(--primary-navy);
        }

        .fill-gold {
            background-color: var(--accent-color);
        }

        /* Pricing Box */
        .price-box {
            background-color: var(--light-bg);
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin-top: 20px;
        }

        .price-box small {
            color: #666;
            display: block;
            margin-bottom: 5px;
        }

        .price-box strong {
            color: var(--primary-navy);
            font-size: 1.1rem;
        }

        /* --- THE VERDICT SECTION --- */
        .verdict-section {
            max-width: 800px;
            margin: 0 auto 80px;
            background: white;
            padding: 40px;
            border-radius: 8px;
            border-top: 4px solid var(--accent-color);
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .verdict-section h3 {
            font-size: 1.8rem;
            color: var(--primary-navy);
            margin-bottom: 20px;
        }

        .btn-group {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-navy {
            background-color: var(--primary-navy);
            color: white;
        }

        .btn-outline {
            border: 2px solid var(--primary-navy);
            color: var(--primary-navy);
        }

        .btn:hover {
            transform: translateY(-3px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .battle-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .hood-card.left {
                border-radius: 8px;
                border-right: none;
            }

            .hood-card.right {
                border-radius: 8px;
            }

            .vs-badge {
                top: auto;
                bottom: -30px;
                display: none;
            }

            /* Hide VS on mobile or adjust */
            .insight-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>

    <nav>
        <div class="container">
            <a href="index.php" class="logo">SAFEHAVEN</a>
            <span>Market Insights</span>
        </div>
    </nav>

    <header class="insight-hero">
        <span class="insight-tag">Abuja Neighborhood Series</span>
        <h1>The Battle of the Peaks</h1>
        <p style="opacity: 0.8;">Maitama vs. Guzape: Where should your capital go in 2026?</p>
    </header>

    <div class="battle-container">
        <div class="vs-badge">VS</div>

        <div class="hood-card left">
            <div class="hood-img" style="background-image: url('https://images.unsplash.com/photo-1599557348981-d24f0c765956?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');">
                <div class="hood-title">
                    <h2>Maitama</h2>
                    <span>"The Seat of Power"</span>
                </div>
            </div>
            <div class="hood-body">
                <p style="margin-bottom: 20px; font-size: 0.95rem;">
                    The undisputed heavyweight of Abuja luxury. Home to embassies, power brokers, and old money. It is quiet, heavily secured, and flat.
                </p>

                <div class="stat-row">
                    <div class="stat-label"><span>Prestige Factor</span><span>10/10</span></div>
                    <div class="progress-bg">
                        <div class="progress-fill" style="width: 100%;"></div>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-label"><span>Infrastructure Age</span><span>Aging (Reliable)</span></div>
                    <div class="progress-bg">
                        <div class="progress-fill" style="width: 70%;"></div>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-label"><span>Topography</span><span>Flat Terrain</span></div>
                    <div class="progress-bg">
                        <div class="progress-fill" style="width: 40%;"></div>
                    </div>
                </div>

                <div class="price-box">
                    <small>Avg. 5-Bed Detached Duplex</small>
                    <strong>₦850M - ₦2.5B+</strong>
                </div>
            </div>
        </div>

        <div class="hood-card right">
            <div class="hood-img" style="background-image: url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');">
                <div class="hood-title">
                    <h2>Guzape</h2>
                    <span>"The Beverly Hills of Abuja"</span>
                </div>
            </div>
            <div class="hood-body">
                <p style="margin-bottom: 20px; font-size: 0.95rem;">
                    The new frontier for modern luxury. Perched on hills with breathtaking views, it attracts tech entrepreneurs, young families, and expats.
                </p>

                <div class="stat-row">
                    <div class="stat-label"><span>Prestige Factor</span><span>8.5/10</span></div>
                    <div class="progress-bg">
                        <div class="progress-fill fill-gold" style="width: 85%;"></div>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-label"><span>Infrastructure Age</span><span>Brand New</span></div>
                    <div class="progress-bg">
                        <div class="progress-fill fill-gold" style="width: 95%;"></div>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-label"><span>Topography</span><span>Hilly / Views</span></div>
                    <div class="progress-bg">
                        <div class="progress-fill fill-gold" style="width: 90%;"></div>
                    </div>
                </div>

                <div class="price-box">
                    <small>Avg. 5-Bed Detached Duplex</small>
                    <strong>₦450M - ₦1.2B</strong>
                </div>
            </div>
        </div>
    </div>

    <section class="verdict-section">
        <h3>The SafeHaven Verdict</h3>
        <p>
            <strong>Choose Maitama if:</strong> You value proximity to the CBD, absolute privacy, and being surrounded by established diplomatic security. It is the safe, blue-chip asset that never loses value.
        </p>
        <br>
        <p>
            <strong>Choose Guzape if:</strong> You want a modern architectural masterpiece with a view. You prefer a "lifestyle" neighborhood with cooler air and don't mind the ongoing construction as the district expands.
        </p>

        <div class="btn-group">
            <a href="#" class="btn btn-navy">See Maitama Listings</a>
            <a href="#" class="btn btn-outline">See Guzape Listings</a>
        </div>
    </section>

    <footer style="text-align: center; padding: 20px; color: #777; font-size: 0.9rem;">
        <p>Data based on Q4 2025 SafeHaven Market Analysis.</p>
    </footer>

</body>

</html>