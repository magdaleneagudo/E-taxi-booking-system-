<?php
include 'includes/config.php';

$db = new Database();
$conn = $db->getConnection();

// Check if admin exists
$stmt = $conn->prepare("SELECT * FROM users WHERE user_type = 'admin'");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "<h2>✅ Admin User Already Exists</h2>";
    echo "<p><strong>Phone:</strong> " . $admin['phone'] . "</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
} else {
    // Create admin user
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, user_type, password) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute(['Admin User', '256700000000', 'admin@etaxi.com', 'admin', $hashed_password])) {
        echo "<h2 style='color: green;'>✅ Admin User Created Successfully!</h2>";
        echo "<p><strong>Phone:</strong> 256700000000</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
    } else {
        echo "<h2 style='color: red;'>❌ Failed to create admin user</h2>";
    }
}

echo "<p><a href='login.php'>Go to Login Page</a></p>";
?>