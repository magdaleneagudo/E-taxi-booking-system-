<?php
session_start();

$host = 'localhost';
$dbname = 'e_taxi_booking';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    echo "<h1>👥 System Users Report</h1>";
    
    // Get all users
    $stmt = $pdo->query("SELECT id, name, phone, user_type, created_at FROM users ORDER BY user_type, id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #8B0000; color: white;'>
            <th>ID</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Type</th>
            <th>Created</th>
          </tr>";
    
    foreach ($users as $user) {
        $bgcolor = $user['user_type'] == 'admin' ? '#ffebee' : '#ffffff';
        $color = $user['user_type'] == 'admin' ? '#dc3545' : '#000000';
        
        echo "<tr style='background: $bgcolor;'>";
        echo "<td>{$user['id']}</td>";
        echo "<td><strong style='color: $color;'>{$user['name']}</strong></td>";
        echo "<td>{$user['phone']}</td>";
        echo "<td><strong style='color: $color;'>{$user['user_type']}</strong></td>";
        echo "<td>{$user['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='margin: 20px 0; padding: 15px; background: #e9ecef; border-radius: 5px;'>";
    echo "<h3>Quick Actions:</h3>";
    echo "<p><a href='admin_login.php' style='color: #8B0000; font-weight: bold;'>🔓 Login as Admin</a></p>";
    echo "<p><a href='login.php' style='color: #8B0000;'>🔑 Regular Login</a></p>";
    echo "<p><a href='fix_admin.php' style='color: #8B0000;'>🛠️ Fix Admin Account</a></p>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<h1>❌ Database Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>