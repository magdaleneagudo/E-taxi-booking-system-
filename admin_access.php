<?php
session_start();

// Force admin session
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin User';
$_SESSION['user_type'] = 'admin';
$_SESSION['user_phone'] = '256700000000';

echo "<h1>🔓 Admin Access Granted</h1>";
echo "<p>You have been logged in as admin.</p>";
echo "<p><a href='admin/index.php'>Go to Admin Panel</a></p>";
echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
?>