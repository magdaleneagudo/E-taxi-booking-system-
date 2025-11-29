<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

function isDriver() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'driver';
}

function isPassenger() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'passenger';
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function checkAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
        header('Location: login.php');
        exit();
    }
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function displayMessage($message, $type = 'info') {
    $class = '';
    switch($type) {
        case 'success': $class = 'alert-success'; break;
        case 'error': $class = 'alert-error'; break;
        case 'warning': $class = 'alert-warning'; break;
        default: $class = 'alert-info';
    }
    
    return '<div class="alert ' . $class . '">' . htmlspecialchars($message) . '</div>';
}

// CHANGED FUNCTION NAME: getAvailableTaxis → getTaxisByLocation
function getTaxisByLocation($route = null) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT t.*, u.name as driver_name 
            FROM taxis t 
            LEFT JOIN users u ON t.driver_id = u.id 
            WHERE t.status = 'available'";
    
    if ($route) {
        $sql .= " AND t.current_location LIKE :route";
    }
    
    $stmt = $conn->prepare($sql);
    if ($route) {
        $stmt->bindValue(':route', '%' . $route . '%');
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function calculateFare($distance, $passengers) {
    $base_fare = 3000; // UGX
    $distance_rate = 500; // UGX per km
    $passenger_surcharge = 500; // UGX per additional passenger
    
    $fare = $base_fare + ($distance * $distance_rate);
    if ($passengers > 1) {
        $fare += ($passengers - 1) * $passenger_surcharge;
    }
    
    return $fare;
}
?>