<?php
session_start();

// Destroy session and redirect to login
session_destroy();
header("Location: admin-login.php");
exit();

