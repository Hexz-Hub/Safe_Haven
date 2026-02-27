<style>
    /* ============================================
       SAFEHAVEN - PROFESSIONAL BRANDING SYSTEM
       Deep Purple-Blue Primary Color (#4B2C6B)
       Gold Accents (#D4AF37)
       ============================================ */

    /* --- MASTER COLOR PALETTE --- */
    :root {
        /* Primary Branding Colors - from SafeHaven Logo */
        --primary-color: #4B2C6B;
        /* Deep Purple-Blue */
        --primary-dark: #2D1B47;
        /* Darker variant */
        --primary-light: #6B4E8C;
        /* Lighter variant */
        --accent-color: #D4AF37;
        /* Gold */
        --accent-light: #E8C766;
        /* Light Gold */
        --accent-dark: #B8941F;
        /* Dark Gold */

        /* Neutral Colors */
        --text-dark: #2D1B47;
        --text-muted: #666666;
        --white: #ffffff;
        --light-bg: #F5F3FF;
        /* Very light purple-blue */
        --gray-light: #f8f9fa;
        --gray-medium: #e0e0e0;

        /* Effects & Transitions */
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --shadow-sm: 0 2px 4px rgba(75, 44, 107, 0.08);
        --shadow-md: 0 4px 12px rgba(75, 44, 107, 0.12);
        --shadow-lg: 0 8px 24px rgba(75, 44, 107, 0.15);
        --shadow-hover: 0 12px 32px rgba(75, 44, 107, 0.2);

        /* Spacing & Border */
        --border-radius: 8px;
        --border-radius-lg: 12px;
        --border-color: rgba(75, 44, 107, 0.1);
    }

    /* --- GLOBAL RESET --- */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Segoe UI', 'Inter', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', sans-serif;
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

    a:hover {
        color: var(--primary-color);
    }

    ul,
    ol {
        list-style: none;
    }

    img {
        max-width: 100%;
        height: auto;
        display: block;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        color: var(--primary-color);
        font-weight: 700;
        margin-bottom: 15px;
    }

    h1 {
        font-size: 2.5rem;
    }

    h2 {
        font-size: 2rem;
    }

    h3 {
        font-size: 1.5rem;
    }

    h4 {
        font-size: 1.2rem;
    }

    /* --- UTILITIES --- */
    .container {
        width: 100%;
        max-width: 1280px;
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

    /* --- BUTTONS - UNIFIED SYSTEM --- */
    .btn,
    button,
    input[type="submit"],
    input[type="button"],
    a.btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 26px;
        border-radius: var(--border-radius);
        font-weight: 600;
        cursor: pointer;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
        transition: var(--transition);
        border: none;
        gap: 8px;
        white-space: nowrap;
        box-shadow: var(--shadow-sm);
    }

    .btn:active,
    button:active,
    input[type="submit"]:active {
        transform: translateY(0);
    }

    /* Default Button - Gold with Dark Blue Text */
    .btn-primary,
    .btn:not(.btn-secondary):not(.btn-outline):not(.btn-ghost),
    button:not([class*="secondary"]):not([class*="outline"]):not([class*="ghost"]),
    input[type="submit"],
    input[type="button"] {
        background-color: var(--accent-color);
        color: var(--primary-dark);
        font-weight: 700;
    }

    .btn-primary:hover,
    .btn:not(.btn-secondary):not(.btn-outline):not(.btn-ghost):hover,
    button:not([class*="secondary"]):not([class*="outline"]):not([class*="ghost"]):hover,
    input[type="submit"]:hover,
    input[type="button"]:hover {
        background-color: var(--accent-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    /* Secondary Button - Dark Purple-Blue */
    .btn-secondary,
    button.btn-secondary {
        background-color: var(--primary-color);
        color: var(--white);
    }

    .btn-secondary:hover,
    button.btn-secondary:hover {
        background-color: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    /* Outline Button */
    .btn-outline,
    button.btn-outline {
        background-color: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
        box-shadow: none;
    }

    .btn-outline:hover,
    button.btn-outline:hover {
        background-color: var(--primary-color);
        color: var(--white);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    /* Ghost Button (transparent) */
    .btn-ghost {
        background-color: transparent;
        color: var(--primary-color);
        box-shadow: none;
    }

    .btn-ghost:hover {
        background-color: rgba(75, 44, 107, 0.05);
        transform: translateY(-1px);
    }

    /* Navigation Buttons */
    .btn-nav {
        padding: 10px 20px;
        font-size: 0.85rem;
    }

    /* --- TOP BAR --- */
    .top-bar {
        background-color: var(--primary-dark);
        color: #d0d0d0;
        padding: 12px 0;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border-color);
        display: none;
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
        gap: 20px;
    }

    .top-contact {
        display: flex;
        gap: 25px;
        align-items: center;
    }

    .top-contact a {
        color: #d0d0d0;
    }

    .top-contact a:hover {
        color: var(--accent-color);
    }

    .top-socials {
        display: flex;
        gap: 15px;
    }

    .top-socials a {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        color: var(--white);
    }

    .top-socials a:hover {
        background-color: var(--accent-color);
        color: var(--primary-dark);
    }

    /* --- NAVIGATION BAR --- */
    nav {
        background-color: var(--primary-color);
        color: var(--white);
        padding: 0;
        position: sticky;
        top: 0;
        z-index: 999;
        box-shadow: var(--shadow-md);
    }

    nav .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        flex-wrap: wrap;
    }

    @media (min-width: 768px) {
        nav .container {
            padding: 10px 30px;
        }
    }

    /* Logo Section */
    .nav-logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 1.3rem;
        letter-spacing: 1px;
        flex: 0 0 auto;
        margin-right: auto;
    }

    .nav-logo img {
        height: 50px;
        width: auto;
        object-fit: contain;
    }

    .nav-logo span,
    .nav-logo a {
        background: linear-gradient(135deg, var(--accent-color), var(--accent-light));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Navigation Menu */
    .nav-menu {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .nav-menu a,
    .nav-menu li a {
        color: var(--white);
        padding: 8px 16px;
        border-radius: var(--border-radius);
        transition: var(--transition);
        font-weight: 500;
        font-size: 0.95rem;
    }

    .nav-menu a:hover,
    .nav-menu li a:hover,
    .nav-menu a.active,
    .nav-menu li a.active {
        background-color: var(--accent-color);
        color: var(--primary-dark);
    }

    /* Hamburger Menu */
    .hamburger {
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 5px;
        background: none;
        border: none;
        padding: 0;
        box-shadow: none;
    }

    @media (max-width: 768px) {
        .hamburger {
            display: flex;
        }

        .nav-menu {
            display: none;
            position: absolute;
            top: 70px;
            left: 0;
            right: 0;
            background-color: var(--primary-color);
            flex-direction: column;
            padding: 20px;
            gap: 10px;
            width: 100%;
        }

        .nav-menu.active {
            display: flex;
        }

        .nav-menu a,
        .nav-menu li a {
            width: 100%;
            padding: 12px;
        }
    }

    .hamburger span {
        width: 25px;
        height: 3px;
        background-color: var(--accent-color);
        border-radius: 2px;
        transition: var(--transition);
    }

    /* --- FOOTER --- */
    footer {
        background-color: var(--primary-dark);
        color: var(--gray-light);
        padding: 50px 0 20px;
        border-top: 1px solid var(--border-color);
        margin-top: 80px;
    }

    footer a {
        color: var(--accent-color);
        transition: var(--transition);
    }

    footer a:hover {
        color: var(--accent-light);
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 40px;
    }

    .footer-section h3 {
        color: var(--accent-color);
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    .footer-section ul li {
        margin-bottom: 10px;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
        font-size: 0.85rem;
    }

    .footer-bottom a {
        color: var(--accent-color);
    }

    /* --- FORM ELEMENTS --- */
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="tel"],
    input[type="date"],
    input[type="number"],
    select,
    textarea {
        width: 100%;
        padding: 12px 14px;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius);
        font-family: inherit;
        font-size: 0.95rem;
        transition: var(--transition);
        background-color: var(--white);
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    input[type="tel"]:focus,
    input[type="date"]:focus,
    input[type="number"]:focus,
    select:focus,
    textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(75, 44, 107, 0.1);
        background-color: var(--light-bg);
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .form-group {
        margin-bottom: 20px;
    }

    /* --- ALERTS & MESSAGES --- */
    .alert,
    .message,
    .error,
    .success,
    .warning,
    .info {
        padding: 15px 20px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        border-left: 4px solid;
    }

    .alert-success,
    .success {
        background-color: #d4edda;
        border-color: #28a745;
        color: #155724;
    }

    .alert-danger,
    .error {
        background-color: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
    }

    .alert-warning,
    .warning {
        background-color: #fff3cd;
        border-color: #ffc107;
        color: #856404;
    }

    .alert-info,
    .info {
        background-color: #d1ecf1;
        border-color: var(--primary-color);
        color: var(--primary-dark);
    }

    /* --- HERO SECTIONS --- */
    .hero,
    .page-hero,
    .section-hero,
    [class*="hero"] {
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        position: relative;
        color: var(--white);
        padding: 100px 0;
        text-align: center;
        min-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero::before,
    .page-hero::before,
    [class*="hero"]::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(75, 44, 107, 0.85), rgba(45, 27, 71, 0.85));
        z-index: 1;
    }

    .hero>*,
    .page-hero>*,
    [class*="hero"]>* {
        position: relative;
        z-index: 2;
    }

    .hero h1,
    [class*="hero"] h1 {
        font-size: 3rem;
        margin-bottom: 20px;
        font-weight: 700;
        letter-spacing: -1px;
        color: var(--white);
    }

    .hero p,
    [class*="hero"] p {
        font-size: 1.2rem;
        margin-bottom: 30px;
        color: var(--accent-light);
    }

    @media (max-width: 768px) {

        .hero,
        [class*="hero"] {
            padding: 60px 0;
            min-height: 300px;
        }

        .hero h1,
        [class*="hero"] h1 {
            font-size: 2rem;
        }

        .hero p,
        [class*="hero"] p {
            font-size: 1rem;
        }
    }

    /* --- CARDS --- */
    .card {
        background-color: var(--white);
        border-radius: var(--border-radius-lg);
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-color);
    }

    .card-header {
        background-color: var(--primary-color);
        color: var(--white);
        padding: 20px;
    }

    .card-body {
        padding: 25px;
    }

    .card-footer {
        background-color: var(--light-bg);
        padding: 15px 25px;
        border-top: 1px solid var(--border-color);
    }

    /* --- BADGES & TAGS --- */
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: var(--transition);
    }

    .badge-primary {
        background-color: var(--primary-color);
        color: var(--white);
    }

    .badge-accent {
        background-color: var(--accent-color);
        color: var(--primary-dark);
    }

    .badge-light {
        background-color: var(--light-bg);
        color: var(--primary-color);
    }

    /* --- SECTIONS --- */
    section {
        padding: 60px 0;
    }

    section.alt {
        background-color: var(--light-bg);
    }

    /* --- SPACING HELPERS --- */
    .mt-1 {
        margin-top: 10px;
    }

    .mt-2 {
        margin-top: 20px;
    }

    .mt-3 {
        margin-top: 30px;
    }

    .mb-1 {
        margin-bottom: 10px;
    }

    .mb-2 {
        margin-bottom: 20px;
    }

    .mb-3 {
        margin-bottom: 30px;
    }

    .p-1 {
        padding: 10px;
    }

    .p-2 {
        padding: 20px;
    }

    .p-3 {
        padding: 30px;
    }

    /* --- DISPLAY UTILITIES --- */
    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .text-left {
        text-align: left;
    }

    .d-flex {
        display: flex;
    }

    .d-grid {
        display: grid;
    }

    .d-none {
        display: none;
    }

    .d-block {
        display: block;
    }

    .flex-wrap {
        flex-wrap: wrap;
    }

    .gap-2 {
        gap: 20px;
    }

    .gap-3 {
        gap: 30px;
    }

    /* --- RESPONSIVE UTILITIES --- */
    @media (max-width: 768px) {
        .desktop-only {
            display: none !important;
        }

        .container {
            padding: 0 15px;
        }

        h1 {
            font-size: 1.8rem;
        }

        h2 {
            font-size: 1.5rem;
        }

        h3 {
            font-size: 1.2rem;
        }

        section {
            padding: 40px 0;
        }
    }

    .mobile-only {
        display: none;
    }

    @media (max-width: 768px) {
        .mobile-only {
            display: block;
        }
    }

    /* --- ACCESSIBILITY --- */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>