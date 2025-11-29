<?php
/**
 * Database Setup Script
 * Run this once to create all tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'e_taxi_booking';
$username = 'root';
$password = '';

try {
    // Connect to MySQL (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) UNIQUE NOT NULL,
        email VARCHAR(100),
        user_type ENUM('passenger', 'driver', 'admin') DEFAULT 'passenger',
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create taxis table
    $pdo->exec("CREATE TABLE IF NOT EXISTS taxis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        driver_id INT,
        taxi_number VARCHAR(20) UNIQUE NOT NULL,
        model VARCHAR(50),
        capacity INT DEFAULT 14,
        current_location VARCHAR(255),
        status ENUM('available', 'booked', 'offline') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create bookings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        passenger_id INT,
        taxi_id INT,
        pickup_location VARCHAR(255) NOT NULL,
        destination VARCHAR(255) NOT NULL,
        passengers_count INT DEFAULT 1,
        booking_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
        fare DECIMAL(10,2),
        notes TEXT
    )");

    // Insert admin user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();

    if ($adminCount == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, phone, email, user_type, password) 
                      VALUES (?, ?, ?, ?, ?)")
            ->execute(['Admin User', '256700000000', 'admin@etaxi.com', 'admin', $hashedPassword]);
    }

    echo "<h1>✅ Database Setup Complete!</h1>";
    echo "<p>All tables created successfully.</p>";
    echo "<p>Admin login: <strong>Phone: 256700000000 | Password: admin123</strong></p>";
    echo "<a href='index.php'>Go to Website</a>";

} catch(PDOException $e) {
    echo "<h1>❌ Database Setup Failed</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>