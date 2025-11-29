<?php
include '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get all bookings
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT b.*, u.name as passenger_name, t.taxi_number 
        FROM bookings b 
        LEFT JOIN users u ON b.passenger_id = u.id 
        LEFT JOIN taxis t ON b.taxi_id = t.id 
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR b.pickup_location LIKE ? OR b.destination LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status)) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY b.booking_time DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_bookings = count($bookings);
$pending_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$confirmed_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed'")->fetchColumn();
$completed_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-header">
        <nav class="admin-nav">
            <div class="logo">
                <h2>E-Taxi Admin Panel</h2>
            </div>
            <ul class="admin-nav-links">
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="taxis.php">🚗 Taxis</a></li>
                <li><a href="bookings.php" class="active">📋 Bookings</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
                <li><a href="../dashboard.php">👤 User View</a></li>
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="admin-container">
        <h1>📋 Booking Management</h1>

        <?php
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <!-- Booking Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_bookings; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_bookings; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $confirmed_bookings; ?></div>
                <div class="stat-label">Confirmed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $completed_bookings; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-box">
            <form method="GET" action="bookings.php">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by passenger name, pickup, or destination" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="in_progress" <?php echo $status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">🔍 Search</button>
                <a href="bookings.php" class="btn btn-secondary">🔄 Reset</a>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="data-table">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>All Bookings (<?php echo $total_bookings; ?>)</h3>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Passenger</th>
                        <th>Taxi</th>
                        <th>Route</th>
                        <th>Passengers</th>
                        <th>Fare</th>
                        <th>Status</th>
                        <th>Booked</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No bookings found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>
                            <td><?php echo htmlspecialchars($booking['passenger_name']); ?></td>
                            <td><?php echo $booking['taxi_number'] ?? 'N/A'; ?></td>
                            <td>
                                <?php echo htmlspecialchars($booking['pickup_location']); ?> 
                                → 
                                <?php echo htmlspecialchars($booking['destination']); ?>
                            </td>
                            <td><?php echo $booking['passengers_count']; ?></td>
                            <td><?php echo number_format($booking['fare']); ?> UGX</td>
                            <td>
                                <span class="badge badge-<?php echo getBookingStatusBadge($booking['status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, H:i', strtotime($booking['booking_time'])); ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-view">View</a>
                                <a href="#" class="btn btn-sm btn-edit">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Helper function for booking status badges
    function getBookingStatusBadge($status) {
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