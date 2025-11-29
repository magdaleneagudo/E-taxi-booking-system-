<?php
// Simple admin fix script
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'e_taxi_booking';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🛠️ Admin Account Setup</h1>";
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ Admin User Already Exists</h3>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($admin['name']) . "</p>";
        echo "<p><strong>Phone:</strong> " . htmlspecialchars($admin['phone']) . "</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "</div>";
    } else {
        // Create admin user
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, phone, email, user_type, password) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute(['System Admin', '256700000000', 'admin@etaxi.com', 'admin', $hashed_password])) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>✅ Admin User Created Successfully!</h3>";
            echo "<p><strong>Phone:</strong> 256700000000</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3>❌ Failed to create admin user</h3>";
            echo "</div>";
        }
    }
    
    // Show all admin users
    echo "<h3>📋 All Admin Users in System:</h3>";
    $stmt = $pdo->query("SELECT id, name, phone, user_type FROM users WHERE user_type = 'admin'");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "<p>No admin users found.</p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Name</th><th>Phone</th><th>Type</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td>{$admin['id']}</td>";
            echo "<td>{$admin['name']}</td>";
            echo "<td>{$admin['phone']}</td>";
            echo "<td><strong style='color: #dc3545;'>{$admin['user_type']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<div style="margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;">
    <h3>🚀 Next Steps:</h3>
    <ol>
        <li><a href="login.php" style="color: #8B0000; font-weight: bold;">Go to Login Page</a> and use:
            <ul>
                <li><strong>Phone:</strong> 256700000000</li>
                <li><strong>Password:</strong> admin123</li>
            </ul>
        </li>
        <li>After login, you should see "Admin" link in navigation</li>
        <li>Click "Admin" to access the admin panel</li>
    </ol>
</div>

<div style="margin-top: 20px;">
    <h3>🔧 Alternative Access Methods:</h3>
    <p><a href="admin_access.php" style="color: #8B0000;">Force Admin Access</a> - Bypass login (for testing)</p>
    <p><a href="test_users.php" style="color: #8B0000;">View All Users</a> - Check all users in system</p>
</div>