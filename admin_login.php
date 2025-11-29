<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'e_taxi_booking';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Check if we have admin users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
    $admin_count = $stmt->fetchColumn();
    
    if ($admin_count == 0) {
        // Create admin user if none exists
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, phone, email, user_type, password) VALUES (?, ?, ?, ?, ?)")
            ->execute(['System Administrator', '256700000000', 'admin@etaxi.com', 'admin', $hashed_password]);
    }
    
    // Get an admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_type = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Set session as this admin
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_name'] = $admin['name'];
        $_SESSION['user_type'] = $admin['user_type'];
        $_SESSION['user_phone'] = $admin['phone'];
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Admin Login Success</title>
            <style>
                body { font-family: Arial; margin: 40px; background: #f5f5f5; }
                .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 15px 0; }
                .btn { display: inline-block; padding: 10px 20px; background: #8B0000; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
            </style>
        </head>
        <body>
            <div class='success'>
                <h1>✅ Admin Login Successful!</h1>
                <p>You have been logged in as: <strong>{$admin['name']}</strong></p>
                <p>User Type: <strong>{$admin['user_type']}</strong></p>
            </div>
            
            <div class='info'>
                <h3>🎯 What you can do now:</h3>
                <p>As an administrator, you can manage the entire system including users, taxis, and bookings.</p>
            </div>
            
            <div>
                <a href='admin/index.php' class='btn'>🚀 Go to Admin Panel</a>
                <a href='dashboard.php' class='btn'>📊 User Dashboard</a>
                <a href='index.php' class='btn'>🏠 Homepage</a>
            </div>
            
            <div style='margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 5px;'>
                <h3>🔐 Regular Login:</h3>
                <p>To login normally in the future, use:</p>
                <ul>
                    <li><strong>Phone:</strong> 256700000000</li>
                    <li><strong>Password:</strong> admin123</li>
                </ul>
                <p><a href='login.php'>Go to Regular Login Page</a></p>
            </div>
        </body>
        </html>";
    }
    
} catch(PDOException $e) {
    echo "<h1>❌ Database Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><a href='login.php'>Try Regular Login</a></p>";
}
?>