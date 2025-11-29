<?php
include 'includes/config.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h1>Adding Sample Taxis...</h1>";
    
    // Sample taxi data
    $sampleTaxis = [
        ['UAB 123A', 'Toyota Hiace', 'Kampala Road', 14],
        ['UAE 456B', 'Nissan Urvan', 'City Square', 14],
        ['UAD 789C', 'Toyota Hiace', 'Nakawa', 14],
        ['UAF 012D', 'Nissan Urvan', 'Ntinda', 14],
        ['UAG 345E', 'Toyota Hiace', 'Bweyogerere', 14]
    ];
    
    $added = 0;
    foreach ($sampleTaxis as $taxi) {
        // Check if taxi already exists
        $stmt = $conn->prepare("SELECT id FROM taxis WHERE taxi_number = ?");
        $stmt->execute([$taxi[0]]);
        
        if (!$stmt->fetch()) {
            $stmt = $conn->prepare("INSERT INTO taxis (taxi_number, model, current_location, capacity) VALUES (?, ?, ?, ?)");
            $stmt->execute([$taxi[0], $taxi[1], $taxi[2], $taxi[3]]);
            echo "<p>✅ Added taxi: {$taxi[0]} - {$taxi[1]} at {$taxi[2]}</p>";
            $added++;
        } else {
            echo "<p>⚠️ Taxi already exists: {$taxi[0]}</p>";
        }
    }
    
    echo "<h2 style='color: green;'>✅ Added $added sample taxis!</h2>";
    echo "<p><a href='book.php'>Start Booking Taxis</a></p>";
    
} catch(PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error: " . $e->getMessage() . "</h2>";
}
?>