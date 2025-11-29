<?php
include 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's bookings
$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT b.*, t.taxi_number, t.model 
    FROM bookings b 
    LEFT JOIN taxis t ON b.taxi_id = t.id 
    WHERE b.passenger_id = ? 
    ORDER BY b.booking_time DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .booking-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #8B0000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .booking-id {
            font-weight: bold;
            color: #8B0000;
        }
        .booking-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-in_progress { background: #d4edda; color: #155724; }
        .status-completed { background: #e2e3e5; color: #383d41; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .no-bookings {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <header>
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
            <h2>📋 My Bookings</h2>
            
            <?php
            if (isset($_SESSION['success'])) {
                echo displayMessage($_SESSION['success'], 'success');
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo displayMessage($_SESSION['error'], 'error');
                unset($_SESSION['error']);
            }
            ?>

            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    <h3>No bookings yet</h3>
                    <p>You haven't made any taxi bookings yet.</p>
                    <a href="book.php" class="cta-button">Book Your First Taxi</a>
                </div>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <span class="booking-id">Booking #<?php echo $booking['id']; ?></span>
                                <span class="booking-status status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                </span>
                            </div>
                            
                            <div class="booking-details">
                                <div>
                                    <p><strong>🚗 Taxi:</strong> <?php echo $booking['taxi_number'] ?? 'Not assigned'; ?></p>
                                    <p><strong>📍 From:</strong> <?php echo htmlspecialchars($booking['pickup_location']); ?></p>
                                    <p><strong>🎯 To:</strong> <?php echo htmlspecialchars($booking['destination']); ?></p>
                                </div>
                                <div>
                                    <p><strong>👥 Passengers:</strong> <?php echo $booking['passengers_count']; ?></p>
                                    <p><strong>💰 Fare:</strong> <?php echo number_format($booking['fare']); ?> UGX</p>
                                    <p><strong>🕐 Booked:</strong> <?php echo date('M d, Y H:i', strtotime($booking['booking_time'])); ?></p>
                                </div>
                            </div>
                            
                            <?php if (!empty($booking['notes'])): ?>
                                <p><strong>📝 Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="book.php" class="btn">📅 Book Another Taxi</a>
                <a href="dashboard.php" class="btn btn-secondary">🏠 Dashboard</a>
            </div>
        </div>
    </main>
</body>
</html>