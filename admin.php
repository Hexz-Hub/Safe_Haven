<?php
session_start();

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin-dashboard.php");
    exit();
}

// Redirect to admin login
header("Location: admin-login.php");
exit();
