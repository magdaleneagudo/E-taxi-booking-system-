<?php
// Database setup script
$host = 'localhost';
$dbname = 'e_taxi_booking';
$username = 'root';
$password = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");

    echo "<h1>Setting up E-Taxi Database...</h1>";

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
    echo "<p>✅ Users table created</p>";

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
    echo "<p>✅ Taxis table created</p>";

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
    echo "<p>✅ Bookings table created</p>";

    // Insert admin user
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();

    if ($adminCount == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, phone, email, user_type, password) 
                      VALUES (?, ?, ?, ?, ?)")
            ->execute(['Admin User', '256700000000', 'admin@etaxi.com', 'admin', $hashedPassword]);
        echo "<p>✅ Admin user created</p>";
        echo "<p><strong>Admin Login:</strong> Phone: 256700000000 | Password: admin123</p>";
    }

    echo "<h2 style='color: green;'>🎉 Database setup completed successfully!</h2>";
    echo "<p><a href='index.php'>Go to Homepage</a> | <a href='register.php'>Register New User</a></p>";

} catch(PDOException $e) {
    echo "<h2 style='color: red;'>❌ Setup failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running in XAMPP.</p>";
}
?>