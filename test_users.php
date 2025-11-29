<?php
include 'includes/config.php';

$db = new Database();
$conn = $db->getConnection();

// Get all users with their types
$stmt = $conn->query("SELECT id, name, phone, user_type FROM users ORDER BY user_type, id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h1>System Users</h1>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Phone</th><th>Type</th></tr>";

foreach ($users as $user) {
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['name']}</td>";
    echo "<td>{$user['phone']}</td>";
    echo "<td><strong>{$user['user_type']}</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='login.php'>Go to Login</a></p>";
?>