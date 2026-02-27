<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login with message
    $_SESSION['login_required'] = true;
    $_SESSION['login_message'] = "Please log in to perform this action.";
    header("Location: user-login.php");
    exit();
}

// If we reach here, user is logged in
?>

