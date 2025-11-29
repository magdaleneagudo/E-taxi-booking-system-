<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'e_taxi_booking');  // Make sure this matches your database name
define('DB_USER', 'root');
define('DB_PASS', '');  // Leave empty for XAMPP default

// Application settings
define('BASE_URL', 'http://localhost/e-taxi-booking/');
define('SITE_NAME', 'E-Taxi Booking System');

// Start session
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple database connection test
function testDatabaseConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

// Check if database exists, if not create it
function initializeSystem() {
    try {
        // First try to connect without database
        $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE 'e_taxi_booking'");
        if ($stmt->rowCount() == 0) {
            // Create database
            $pdo->exec("CREATE DATABASE e_taxi_booking");
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; margin: 10px; border-radius: 5px;'>
                    ✅ Database 'e_taxi_booking' created successfully!
                  </div>";
        }
        
    } catch(PDOException $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border-radius: 5px;'>
                ❌ Database Error: " . $e->getMessage() . "
              </div>";
    }
}

// Initialize system
initializeSystem();

// Include other required files
require_once 'database.php';
require_once 'functions.php';
?>