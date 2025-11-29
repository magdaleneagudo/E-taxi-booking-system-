<?php
include '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get statistics
$db = new Database();
$conn = $db->getConnection();

$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_taxis = $conn->query("SELECT COUNT(*) FROM taxis")->fetchColumn();
$total_bookings = $conn->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();

// Recent bookings
$recent_bookings = $conn->query("
    SELECT b.*, u.name as passenger_name, t.taxi_number 
    FROM bookings b 
    LEFT JOIN users u ON b.passenger_id = u.id 
    LEFT JOIN taxis t ON b.taxi_id = t.id 
    ORDER BY b.booking_time DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-header">
        <nav class="admin-nav">
            <div class="logo">
                <h2>E-Taxi Admin Panel</h2>
            <ul class="admin-nav-links">
    <li><a href="index.php" class="active">📊 Dashboard</a></li>
    <li><a href="users.php">👥 Users</a></li>
    <li><a href="taxis.php">🚗 Taxis</a></li>
    <li><a href="bookings.php">📋 Bookings</a></li>
    <li><a href="reports.php">📈 Reports</a></li>
    <li><a href="../dashboard.php">👤 User View</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
</ul>
        </nav>
    </div>

    <div class="admin-container">
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Here's your system overview.</p>

        <!-- Statistics Cards -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_taxis; ?></div>
                <div class="stat-label">Total Taxis</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_bookings; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_bookings; ?></div>
                <div class="stat-label">Pending Bookings</div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="data-table">
            <h3>Recent Bookings</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Passenger</th>
                        <th>Taxi</th>
                        <th>Route</th>
                        <th>Fare</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_bookings)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No bookings found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['passenger_name']); ?></td>
                            <td><?php echo $booking['taxi_number'] ?? 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars($booking['pickup_location']); ?> → <?php echo htmlspecialchars($booking['destination']); ?></td>
                            <td><?php echo number_format($booking['fare']); ?> UGX</td>
                            <td>
                                <span class="badge badge-<?php echo getStatusBadge($booking['status']); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, H:i', strtotime($booking['booking_time'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="form-container">
            <h3>Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="users.php" class="btn">👥 Manage Users</a>
                <a href="taxis.php" class="btn">🚗 Manage Taxis</a>
                <a href="bookings.php" class="btn">📋 View All Bookings</a>
                <a href="reports.php" class="btn">📈 Generate Reports</a>
            </div>
        </div>
    </div>

    <?php
    // Helper function for status badges
    function getStatusBadge($status) {
        switch($status) {
            case 'completed': return 'success';
            case 'confirmed': return 'info';
            case 'in_progress': return 'warning';
            case 'pending': return 'warning';
            case 'cancelled': return 'danger';
            default: return 'secondary';
        }
    }
    ?>
</body>
</html>