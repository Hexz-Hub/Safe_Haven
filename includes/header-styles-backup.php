<style>
    /* --- RESET & VARIABLES --- */
    :root {
        /* BRANDING: SAFEHAVEN BLUE - Professional color palette */
        --primary-green: #0066CC;
        --darker-green: #004999;
        --accent-gold: #0066CC;
        --gold-light: #E6F2FF;
        --gold-dark: #003366;
        --text-dark: #333333;
        --white: #ffffff;
        --light-gray: #f4f5f4;
        --gray-light: #f8f9fa;
        --success-green: #27ae60;
        --transition: all 0.3s ease;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.12);
        --border-radius: 8px;
        --border-radius-lg: 16px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Segoe UI', 'Inter', -apple-system, BlinkMacSystemFont, Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
        background-color: var(--white);
        overflow-x: hidden;
        font-size: 16px;
    }

    a {
        text-decoration: none;
        color: inherit;
        transition: var(--transition);
    }

    ul {
        list-style: none;
    }

    img {
        max-width: 100%;
        height: auto;
    }

    /* --- UTILITIES --- */
    .container {
        width: 100%;
        max-width: 1280px !important;
        margin: 0 auto;
        padding: 0 20px;
    }

    @media (min-width: 768px) {
        .container {
            padding: 0 30px;
        }
    }

    @media (min-width: 1200px) {
        .container {
            padding: 0 40px;
        }
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 24px;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        text-transform: capitalize;
        font-size: 0.9rem;
        letter-spacing: 0.3px;
        transition: var(--transition);
        border: none;
        gap: 6px;
        white-space: nowrap;
    }

    .btn-primary {
        background: var(--accent-gold);
        color: var(--darker-green);
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    }

    .btn-primary:hover {
        background: #C9A030;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-secondary {
        background-color: transparent;
        color: var(--white);
        border: 2px solid rgba(255, 255, 255, 0.8);
    }

    .btn-secondary:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: var(--white);
        transform: translateY(-1px);
    }

    .btn-nav {
        padding: 14px 28px;
        font-size: 0.75rem;
    }

    /* --- TOP BAR --- */
    .top-bar {
        background-color: var(--darker-green);
        color: #ccc;
        padding: 12px 0;
        font-size: 0.9rem;
        border-bottom: 1px solid rgba(0, 102, 204, 0.2);
        display: none;
        /* Hidden on mobile */
    }

    @media (min-width: 768px) {
        .top-bar {
            display: block;
        }
    }

    .top-bar .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .top-contact {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .top-contact span {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .social-icons {
        display: flex;
        gap: 12px;
    }

    .social-icons a {
        color: #ccc;
        font-size: 1rem;
    }

    .social-icons a:hover {
        color: var(--accent-gold);
    }

    /* --- NAVIGATION (Enhanced) --- */
    nav {
        background-color: var(--primary-green);
        color: var(--white);
        padding: 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: var(--shadow-md);
    }

    nav .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        height: 70px;
    }

    @media (min-width: 768px) {
        nav .container {
            padding: 0 30px;
        }
    }

    .logo {
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: 1px;
        color: var(--accent-gold);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .logo i {
        font-size: 1.5rem;
    }

    .nav-links {
        display: flex;
        gap: 30px;
        align-items: center;
    }

    .nav-links a {
        font-weight: 600;
        position: relative;
        padding: 5px 0;
        color: var(--white);
        font-size: 0.95rem;
    }

    .nav-links a:not(.btn):not(.btn-primary)::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background-color: var(--accent-gold);
        transition: var(--transition);
    }

    .nav-links a:not(.btn):not(.btn-primary):hover::after,
    .nav-links a:not(.btn):not(.btn-primary).active::after {
        width: 100%;
    }

    .nav-links a:hover,
    .nav-links a.active {
        color: var(--accent-gold);
    }

    .nav-links .btn,
    .nav-links .btn-primary {
        color: var(--darker-green);
    }

    .nav-links .btn:hover,
    .nav-links .btn-primary:hover {
        color: var(--darker-green);
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
        background-color: var(--accent-gold);
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
            background: linear-gradient(135deg, var(--primary-green), var(--darker-green));
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

        .btn-nav {
            margin-top: 20px;
            width: 80%;
            max-width: 250px;
        }
    }

    /* Footer Styles */
    footer {
        background-color: var(--darker-green);
        color: #ccc;
        padding: 60px 0 30px;
        border-top: 3px solid var(--accent-gold);
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-section h3 {
        color: var(--accent-gold);
        margin-bottom: 20px;
        font-size: 1.2rem;
    }

    .footer-section p,
    .footer-section ul li {
        margin-bottom: 10px;
        line-height: 1.8;
    }

    .footer-section ul li a {
        color: #ccc;
        transition: color 0.3s;
    }

    .footer-section ul li a:hover {
        color: var(--accent-gold);
    }

    .footer-bottom {
        text-align: center;
        padding-top: 30px;
        border-top: 1px solid rgba(0, 102, 204, 0.2);
        font-size: 0.9rem;
    }

    /* WhatsApp Floating Button */
    .whatsapp-float {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #0066CC, #004999);
        color: white;
        padding: 15px 20px;
        border-radius: 50px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 2000;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 0.95rem;
    }

    .whatsapp-float:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
    }

    @media (max-width: 768px) {
        .whatsapp-float {
            bottom: 20px;
            right: 20px;
            padding: 12px 18px;
            font-size: 0.9rem;
        }
    }
</style>