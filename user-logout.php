<?php
session_start();
// Only remove user session keys to avoid interfering with admin session
unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
header('Location: user-login.php');
exit();

