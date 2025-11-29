<?php 
include 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get user-specific data
if ($user_type == 'passenger') {
    $booking_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE passenger_id = $user_id")->fetchColumn();
    $pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE passenger_id = $user_id AND status = 'pending'")->fetchColumn();
} elseif ($user_type == 'driver') {
    // Get driver's taxi info
    $taxi = $conn->query("SELECT * FROM taxis WHERE driver_id = $user_id")->fetch(PDO::FETCH_ASSOC);
    $earning_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE taxi_id = " . ($taxi['id'] ?? 0))->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h2>E-Taxi Uganda</h2>
            </div>
          <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="book.php">Book Taxi</a></li>
    <?php if(isLoggedIn()): ?>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="bookings.php">My Bookings</a></li>
        <?php if(isDriver()): ?>
            <li><a href="driver/">Driver Panel</a></li>
        <?php endif; ?>
        <?php if(isAdmin()): ?>
            <li><a href="admin/">Admin Panel</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
    <?php else: ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    <?php endif; ?>
</ul>
        </nav>
    </header>

    <main>
        <div class="form-container">
            <!-- User Welcome Section -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">
                    <?php 
                    if ($user_type == 'admin') echo '👨‍💼';
                    elseif ($user_type == 'driver') echo '🚕';
                    else echo '🚗';
                    ?>
                </div>
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
                <p>You are logged in as: 
                    <span style="background: <?php echo getUserTypeColor($user_type); ?>; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                        <?php echo ucfirst($user_type); ?>
                    </span>
                </p>
            </div>
            
            <?php
            if (isset($_SESSION['success'])) {
                echo displayMessage($_SESSION['success'], 'success');
                unset($_SESSION['success']);
            }
            ?>

            <!-- Passenger Dashboard -->
            <?php if ($user_type == 'passenger'): ?>
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h3>📊 Your Stats</h3>
                    <p><strong>Total Bookings:</strong> <?php echo $booking_count; ?></p>
                    <p><strong>Pending Rides:</strong> <?php echo $pending_bookings; ?></p>
                </div>

                <div class="dashboard-card">
                    <h3>🚗 Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                        <a href="book.php" class="btn">📅 Book a Taxi</a>
                        <a href="bookings.php" class="btn">📋 My Bookings</a>
                        <a href="profile.php" class="btn">👤 My Profile</a>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings for Passenger -->
            <div style="margin-top: 2rem;">
                <h3>📋 Recent Bookings</h3>
                <?php
                $recent_bookings = $conn->query("
                    SELECT b.*, t.taxi_number 
                    FROM bookings b 
                    LEFT JOIN taxis t ON b.taxi_id = t.id 
                    WHERE b.passenger_id = $user_id 
                    ORDER BY b.booking_time DESC 
                    LIMIT 3
                ")->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($recent_bookings)): ?>
                    <p>No bookings yet. <a href="book.php">Book your first taxi!</a></p>
                <?php else: ?>
                    <?php foreach ($recent_bookings as $booking): ?>
                    <div style="background: white; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #8B0000;">
                        <div style="display: flex; justify-content: between; align-items: center;">
                            <div>
                                <strong>#<?php echo $booking['id']; ?></strong> - 
                                <?php echo htmlspecialchars($booking['pickup_location']); ?> → 
                                <?php echo htmlspecialchars($booking['destination']); ?>
                            </div>
                            <span style="background: <?php echo getStatusColor($booking['status']); ?>; color: white; padding: 3px 10px; border-radius: 15px; font-size: 0.8em;">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="bookings.php" class="btn">View All Bookings</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Driver Dashboard -->
            <?php elseif ($user_type == 'driver'): ?>
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h3>🚕 Driver Dashboard</h3>
                    <?php if ($taxi): ?>
                        <p><strong>Your Taxi:</strong> <?php echo $taxi['taxi_number']; ?></p>
                        <p><strong>Status:</strong> 
                            <span style="color: <?php echo $taxi['status'] == 'available' ? 'green' : 'orange'; ?>">
                                <?php echo ucfirst($taxi['status']); ?>
                            </span>
                        </p>
                        <p><strong>Completed Rides:</strong> <?php echo $earning_count; ?></p>
                    <?php else: ?>
                        <p>No taxi assigned yet.</p>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <h3>💰 Driver Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                        <?php if ($taxi): ?>
                            <a href="driver/bookings.php" class="btn">📋 My Rides</a>
                            <a href="driver/earnings.php" class="btn">💰 Earnings</a>
                            <a href="driver/my_taxi.php" class="btn">🚗 My Taxi</a>
                        <?php else: ?>
                            <p>Contact admin to get a taxi assigned.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Admin Dashboard -->
            <?php elseif ($user_type == 'admin'): ?>
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h3>👨‍💼 Admin Overview</h3>
                    <p>Welcome to the administrator dashboard. You have full access to manage the entire system.</p>
                </div>

                <div class="dashboard-card">
                    <h3>⚡ Quick Admin Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                        <a href="admin/index.php" class="btn">📊 Admin Panel</a>
                        <a href="admin/users.php" class="btn">👥 Manage Users</a>
                        <a href="admin/taxis.php" class="btn">🚗 Manage Taxis</a>
                        <a href="admin/bookings.php" class="btn">📋 All Bookings</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 E-Taxi Booking System. Group 6 - Uganda Christian University</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>

<?php
// Helper functions
function getUserTypeColor($type) {
    switch($type) {
        case 'admin': return '#dc3545';
        case 'driver': return '#ffc107';
        case 'passenger': return '#28a745';
        default: return '#6c757d';
    }
}

function getStatusColor($status) {
    switch($status) {
        case 'completed': return '#28a745';
        case 'confirmed': return '#17a2b8';
        case 'in_progress': return '#ffc107';
        case 'pending': return '#ffc107';
        case 'cancelled': return '#dc3545';
        default: return '#6c757d';
    }
}
?>