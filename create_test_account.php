<?php
include 'includes/config.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h1>🧪 Create Test Accounts</h1>";

$test_accounts = [
    // Admin account
    [
        'name' => 'System Administrator',
        'phone' => '256700000000',
        'email' => 'admin@etaxi.com',
        'user_type' => 'admin',
        'password' => 'admin123'
    ],
    // Passenger accounts
    [
        'name' => 'John Passenger',
        'phone' => '256700111111',
        'email' => 'john@example.com',
        'user_type' => 'passenger',
        'password' => 'pass123'
    ],
    [
        'name' => 'Mary Traveler',
        'phone' => '256700222222',
        'email' => 'mary@example.com',
        'user_type' => 'passenger',
        'password' => 'pass123'
    ],
    // Driver accounts
    [
        'name' => 'David Driver',
        'phone' => '256700333333',
        'email' => 'david@example.com',
        'user_type' => 'driver',
        'password' => 'drive123'
    ],
    [
        'name' => 'Sarah Chauffeur',
        'phone' => '256700444444',
        'email' => 'sarah@example.com',
        'user_type' => 'driver',
        'password' => 'drive123'
    ]
];

$created = 0;
$existing = 0;

foreach ($test_accounts as $account) {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$account['phone']]);
    
    if ($stmt->fetch()) {
        echo "<p>⚠️ Account already exists: {$account['phone']} ({$account['user_type']})</p>";
        $existing++;
    } else {
        // Create user
        $hashed_password = password_hash($account['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, phone, email, user_type, password) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$account['name'], $account['phone'], $account['email'], $account['user_type'], $hashed_password])) {
            echo "<p style='color: green;'>✅ Created: {$account['phone']} ({$account['user_type']}) - Password: {$account['password']}</p>";
            $created++;
            
            // If driver, create a taxi for them
            if ($account['user_type'] == 'driver') {
                $driver_id = $conn->lastInsertId();
                $taxi_number = 'UTS' . str_pad($driver_id, 3, '0', STR_PAD_LEFT);
                $locations = ['City Square', 'Kampala Road', 'Nakawa', 'Ntinda', 'Bweyogerere'];
                $location = $locations[array_rand($locations)];
                
                $stmt = $conn->prepare("INSERT INTO taxis (driver_id, taxi_number, model, current_location) VALUES (?, ?, 'Toyota Hiace', ?)");
                $stmt->execute([$driver_id, $taxi_number, $location]);
                echo "<p style='color: blue;'>   🚗 Added taxi: $taxi_number at $location</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to create: {$account['phone']}</p>";
        }
    }
}

echo "<h2>Summary:</h2>";
echo "<p>✅ Created: $created accounts</p>";
echo "<p>⚠️ Existing: $existing accounts</p>";

echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>🧪 Test Login Credentials:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f8f9fa;'><th>Phone</th><th>Password</th><th>Type</th><th>Purpose</th></tr>";
foreach ($test_accounts as $account) {
    echo "<tr>";
    echo "<td>{$account['phone']}</td>";
    echo "<td>{$account['password']}</td>";
    echo "<td>{$account['user_type']}</td>";
    echo "<td>";
    if ($account['user_type'] == 'admin') echo "System management";
    elseif ($account['user_type'] == 'driver') echo "Taxi driving";
    else echo "Booking taxis";
    echo "</td>";
    echo "</tr>";
}
echo "</table>";
echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<a href='login.php' class='btn'>🔑 Go to Login</a> ";
echo "<a href='register.php' class='btn'>📝 Go to Register</a> ";
echo "<a href='index.php' class='btn'>🏠 Go Home</a>";
echo "</div>";
?>