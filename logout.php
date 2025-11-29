<?php
include 'includes/config.php';

// Destroy all session data
session_destroy();

// Redirect to login page with success message
$_SESSION['success'] = 'You have been logged out successfully.';
header('Location: login.php');
exit();
?>