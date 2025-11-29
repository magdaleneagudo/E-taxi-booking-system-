<?php
include '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get report data
$total_revenue = $conn->query("SELECT SUM(fare) FROM bookings WHERE status = 'completed'")->fetchColumn();
$total_revenue = $total_revenue ? $total_revenue : 0;

$today_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE DATE(booking_time) = CURDATE()")->fetchColumn();
$weekly_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE WEEK(booking_time) = WEEK(NOW())")->fetchColumn();
$monthly_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE MONTH(booking_time) = MONTH(NOW())")->fetchColumn();

// Popular routes
$popular_routes = $conn->query("
    SELECT pickup_location, destination, COUNT(*) as booking_count 
    FROM bookings 
    GROUP BY pickup_location, destination 
    ORDER BY booking_count DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-header">
        <nav class="admin-nav">
            <div class="logo">
                <h2>E-Taxi Admin Panel</h2>
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
    </div>

    <div class="admin-container">
        <h1>📈 System Reports</h1>

        <!-- Revenue Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($total_revenue); ?> UGX</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $today_bookings; ?></div>
                <div class="stat-label">Today's Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $weekly_bookings; ?></div>
                <div class="stat-label">This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $monthly_bookings; ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>

        <!-- Popular Routes -->
        <div class="data-table" style="margin-bottom: 2rem;">
            <h3>🚀 Most Popular Routes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Route</th>
                        <th>Bookings Count</th>
                        <th>Popularity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($popular_routes)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No booking data available</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($popular_routes as $route): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($route['pickup_location']); ?></strong> 
                                → 
                                <strong><?php echo htmlspecialchars($route['destination']); ?></strong>
                            </td>
                            <td><?php echo $route['booking_count']; ?> bookings</td>
                            <td>
                                <?php
                                $width = min($route['booking_count'] * 10, 100);
                                echo "<div style='background: #8B0000; height: 20px; width: {$width}px; border-radius: 10px;'></div>";
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Reports -->
        <div class="form-row">
            <div class="form-container">
                <h3>📊 Quick Reports</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="#" class="btn">📅 Daily Report</a>
                    <a href="#" class="btn">📈 Weekly Report</a>
                    <a href="#" class="btn">📊 Monthly Report</a>
                    <a href="#" class="btn">💰 Revenue Report</a>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="form-container" style="margin-top: 2rem;">
            <h3>ℹ️ System Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Total Users</label>
                    <p><?php echo $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?></p>
                </div>
                <div class="form-group">
                    <label>Total Taxis</label>
                    <p><?php echo $conn->query("SELECT COUNT(*) FROM taxis")->fetchColumn(); ?></p>
                </div>
                <div class="form-group">
                    <label>Total Bookings</label>
                    <p><?php echo $conn->query("SELECT COUNT(*) FROM bookings")->fetchColumn(); ?></p>
                </div>
                <div class="form-group">
                    <label>System Uptime</label>
                    <p>Since <?php echo date('M d, Y'); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>