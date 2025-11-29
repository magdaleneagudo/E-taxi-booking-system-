<?php
class Database {
    private $host = DB_HOST;
    private $dbname = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Create database and tables if they don't exist
function initializeDatabase() {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Create users table
        $conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) UNIQUE NOT NULL,
            email VARCHAR(100),
            user_type ENUM('passenger', 'driver', 'admin') DEFAULT 'passenger',
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Create taxis table
        $conn->exec("CREATE TABLE IF NOT EXISTS taxis (
            id INT AUTO_INCREMENT PRIMARY KEY,
            driver_id INT,
            taxi_number VARCHAR(20) UNIQUE NOT NULL,
            model VARCHAR(50),
            capacity INT DEFAULT 14,
            current_location VARCHAR(255),
            status ENUM('available', 'booked', 'offline') DEFAULT 'available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Create bookings table
        $conn->exec("CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            passenger_id INT,
            taxi_id INT,
            pickup_location VARCHAR(255) NOT NULL,
            destination VARCHAR(255) NOT NULL,
            passengers_count INT DEFAULT 1,
            booking_time DATETIME DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
            fare DECIMAL(10,2),
            notes TEXT,
            FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (taxi_id) REFERENCES taxis(id) ON DELETE CASCADE
        )");
        
        // Insert sample data
        $checkAdmin = $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'admin'")->fetchColumn();
        if ($checkAdmin == 0) {
            $conn->exec("INSERT INTO users (name, phone, email, user_type, password) VALUES 
                ('Admin User', '256700000000', 'admin@etaxi.com', 'admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "')
            ");
        }
        
    } catch(PDOException $e) {
        echo "Database initialization failed: " . $e->getMessage();
    }
}

// Initialize database when this file is included
initializeDatabase();
?>