<!-- Top Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-contact">
            <span><i class="fas fa-phone"></i> +234 814 009 7917</span>
            <span><i class="fas fa-envelope"></i> info@safehaven.ng</span>
            <span><i class="fas fa-map-marker-alt"></i> Delta State: Asaba, Agbor, Kwale, Abraka, Oghara, Ozoro</span>
        </div>
        <div class="top-socials">
            <a href="#facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#twitter" title="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#linkedin" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </div>
</div>

<!-- Navigation -->
<nav>
    <div class="container">
        <div class="nav-logo">
            <img src="uploads/logo.png" alt="SafeHaven Logo" onerror="this.style.display='none'">
            <a href="index.php"><span>SAFEHAVEN</span></a>
        </div>

        <ul class="nav-menu">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            $active_home = ($current_page == 'index.php') ? 'active' : '';
            $active_about = ($current_page == 'about.php') ? 'active' : '';
            $active_services = (in_array($current_page, ['services.php', 'request-media-services.php', 'book-consultation.php', 'schedule-inspection.php'])) ? 'active' : '';
            $active_listings = ($current_page == 'listing.php') ? 'active' : '';
            $active_verification = ($current_page == 'verification.php') ? 'active' : '';
            $active_contact = ($current_page == 'contact.php') ? 'active' : '';
            ?>
            <li><a href="index.php" class="<?php echo $active_home; ?>">Home</a></li>
            <li><a href="about.php" class="<?php echo $active_about; ?>">About</a></li>
            <li><a href="services.php" class="<?php echo $active_services; ?>">Services</a></li>
            <li><a href="listing.php" class="<?php echo $active_listings; ?>">Listings</a></li>
            <li><a href="verification.php" class="<?php echo $active_verification; ?>">Verification</a></li>
            <li><a href="contact.php" class="<?php echo $active_contact; ?>">Contact</a></li>
            <li><a href="verification.php" class="btn btn-primary btn-nav">Verify a Property</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li style="padding-left:12px;color:var(--white);font-weight:600;">
                    Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </li>
                <li><a href="user-dashboard.php" class="btn btn-primary btn-nav">Dashboard</a></li>
                <li><a href="user-logout.php" class="btn btn-secondary btn-nav">Logout</a></li>
            <?php else: ?>
                <li><a href="user-login.php" class="btn btn-primary btn-nav">Login</a></li>
            <?php endif; ?>
        </ul>

        <button class="hamburger" aria-label="Toggle menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');

        if (hamburger) {
            hamburger.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                this.classList.toggle('active');
            });
        }

        // Close menu when a link is clicked
        const navLinks = document.querySelectorAll('.nav-menu a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                if (hamburger) hamburger.classList.remove('active');
            });
        });
    });
</script>