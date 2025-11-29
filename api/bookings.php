<?php
include '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'create':
        createBooking();
        break;
    case 'get_available_taxis':
        getAvailableTaxisAPI();
        break;
    case 'calculate_fare':
        calculateFareAPI();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function createBooking() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $passenger_id = $_SESSION['user_id'];
    $pickup_location = trim($_POST['pickup_location']);
    $destination = trim($_POST['destination']);
    $passengers_count = intval($_POST['passengers_count']);
    $notes = trim($_POST['notes'] ?? '');
    
    // Validation
    if (empty($pickup_location) || empty($destination)) {
        $_SESSION['error'] = 'Please select both pickup and destination locations';
        header('Location: ../book.php');
        exit();
    }
    
    if ($pickup_location === $destination) {
        $_SESSION['error'] = 'Pickup and destination cannot be the same';
        header('Location: ../book.php');
        exit();
    }
    
    if ($passengers_count < 1 || $passengers_count > 4) {
        $_SESSION['error'] = 'Please select 1-4 passengers';
        header('Location: ../book.php');
        exit();
    }
    
    // Calculate fare (simplified calculation)
    $fare = calculateFareAmount($pickup_location, $destination, $passengers_count);
    
    // Find available taxi
    $taxi = findAvailableTaxi($pickup_location);
    
    if (!$taxi) {
        $_SESSION['error'] = 'No taxis available at your selected location. Please try another location.';
        header('Location: ../book.php');
        exit();
    }
    
    try {
        // Create booking
        $stmt = $conn->prepare("INSERT INTO bookings (passenger_id, taxi_id, pickup_location, destination, passengers_count, fare, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$passenger_id, $taxi['id'], $pickup_location, $destination, $passengers_count, $fare, $notes]);
        
        // Update taxi status
        $stmt = $conn->prepare("UPDATE taxis SET status = 'booked' WHERE id = ?");
        $stmt->execute([$taxi['id']]);
        
        $booking_id = $conn->lastInsertId();
        
        $_SESSION['success'] = "Booking confirmed! Your taxi {$taxi['taxi_number']} is on the way. Booking ID: #$booking_id";
        header('Location: ../bookings.php');
        exit();
        
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Booking failed. Please try again.';
        header('Location: ../book.php');
        exit();
    }
}

// CHANGED FUNCTION NAME: getAvailableTaxis → getAvailableTaxisAPI
function getAvailableTaxisAPI() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $location = $_GET['location'] ?? '';
    
    if (empty($location)) {
        echo json_encode(['success' => false, 'message' => 'Location required']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT * FROM taxis WHERE current_location LIKE ? AND status = 'available'");
    $stmt->execute(["%$location%"]);
    $taxis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'taxis' => $taxis]);
}

// CHANGED FUNCTION NAME: calculateFare → calculateFareAPI
function calculateFareAPI() {
    $pickup = $_GET['pickup'] ?? '';
    $destination = $_GET['destination'] ?? '';
    $passengers = intval($_GET['passengers'] ?? 1);
    
    if (empty($pickup) || empty($destination)) {
        echo json_encode(['success' => false, 'fare' => 0]);
        exit();
    }
    
    $fare = calculateFareAmount($pickup, $destination, $passengers);
    echo json_encode(['success' => true, 'fare' => $fare]);
}

// Helper function to calculate fare (unique name)
function calculateFareAmount($pickup, $destination, $passengers) {
    // Simplified fare calculation based on route distance
    $routeDistances = [
        'City Square-Nakawa' => 5,
        'City Square-Ntinda' => 7,
        'City Square-Bweyogerere' => 12,
        'City Square-Kira' => 10,
        'City Square-Najjera' => 8,
        'Nakawa-Ntinda' => 4,
        'Nakawa-Bweyogerere' => 8
    ];
    
    $routeKey1 = "$pickup-$destination";
    $routeKey2 = "$destination-$pickup";
    
    if (isset($routeDistances[$routeKey1])) {
        $distance = $routeDistances[$routeKey1];
    } elseif (isset($routeDistances[$routeKey2])) {
        $distance = $routeDistances[$routeKey2];
    } else {
        $distance = 6; // Default distance
    }
    
    $base_fare = 3000;
    $distance_rate = 500;
    $passenger_surcharge = 500;
    
    $fare = $base_fare + ($distance * $distance_rate);
    if ($passengers > 1) {
        $fare += ($passengers - 1) * $passenger_surcharge;
    }
    
    return $fare;
}

function findAvailableTaxi($location) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM taxis WHERE current_location LIKE ? AND status = 'available' LIMIT 1");
    $stmt->execute(["%$location%"]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>