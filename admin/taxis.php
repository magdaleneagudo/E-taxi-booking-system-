<?php
include '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle taxi actions
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $taxi_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("DELETE FROM taxis WHERE id = ?");
    if ($stmt->execute([$taxi_id])) {
        $_SESSION['success'] = 'Taxi deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete taxi';
    }
    
    header('Location: taxis.php');
    exit();
}

// Add new taxi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_taxi') {
    $taxi_number = trim($_POST['taxi_number']);
    $model = trim($_POST['model']);
    $capacity = intval($_POST['capacity']);
    $current_location = trim($_POST['current_location']);
    
    try {
        $stmt = $conn->prepare("INSERT INTO taxis (taxi_number, model, capacity, current_location) VALUES (?, ?, ?, ?)");
        $stmt->execute([$taxi_number, $model, $capacity, $current_location]);
        $_SESSION['success'] = 'Taxi added successfully';
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Failed to add taxi: ' . $e->getMessage();
    }
    
    header('Location: taxis.php');
    exit();
}

// Get all taxis
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT t.*, u.name as driver_name FROM taxis t LEFT JOIN users u ON t.driver_id = u.id WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (t.taxi_number LIKE ? OR t.model LIKE ? OR t.current_location LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status)) {
    $sql .= " AND t.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$taxis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_taxis = count($taxis);
$available_taxis = $conn->query("SELECT COUNT(*) FROM taxis WHERE status = 'available'")->fetchColumn();
$booked_taxis = $conn->query("SELECT COUNT(*) FROM taxis WHERE status = 'booked'")->fetchColumn();
$offline_taxis = $conn->query("SELECT COUNT(*) FROM taxis WHERE status = 'offline'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Taxis - <?php echo SITE_NAME; ?></title>
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
    <li><a href="taxis.php" class="active">🚗 Taxis</a></li>
    <li><a href="bookings.php">📋 Bookings</a></li>
    <li><a href="reports.php">📈 Reports</a></li>
    <li><a href="../dashboard.php">👤 User View</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
</ul>
        </nav>
    </div>

    <div class="admin-container">
        <h1>🚗 Taxi Management</h1>

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

        <!-- Taxi Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_taxis; ?></div>
                <div class="stat-label">Total Taxis</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $available_taxis; ?></div>
                <div class="stat-label">Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $booked_taxis; ?></div>
                <div class="stat-label">Booked</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $offline_taxis; ?></div>
                <div class="stat-label">Offline</div>
            </div>
        </div>

        <!-- Add Taxi Form -->
        <div class="form-container" style="margin-bottom: 2rem;">
            <h3>Add New Taxi</h3>
            <form method="POST" action="taxis.php">
                <input type="hidden" name="action" value="add_taxi">
                <div class="form-row">
                    <div class="form-group">
                        <label>Taxi Number</label>
                        <input type="text" name="taxi_number" class="form-control" placeholder="e.g., UAB 123A" required>
                    </div>
                    <div class="form-group">
                        <label>Model</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g., Toyota Hiace" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Capacity</label>
                        <select name="capacity" class="form-control" required>
                            <option value="14">14 Seaters</option>
                            <option value="4">4 Seaters</option>
                            <option value="7">7 Seaters</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Current Location</label>
                        <select name="current_location" class="form-control" required>
                            <option value="City Square">City Square</option>
                            <option value="Kampala Road">Kampala Road</option>
                            <option value="Nakawa">Nakawa</option>
                            <option value="Ntinda">Ntinda</option>
                            <option value="Bweyogerere">Bweyogerere</option>
                            <option value="Kira">Kira</option>
                            <option value="Najjera">Najjera</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">➕ Add Taxi</button>
            </form>
        </div>

        <!-- Search and Filter -->
        <div class="search-box">
            <form method="GET" action="taxis.php">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by taxi number, model, or location" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="available" <?php echo $status == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="booked" <?php echo $status == 'booked' ? 'selected' : ''; ?>>Booked</option>
                            <option value="offline" <?php echo $status == 'offline' ? 'selected' : ''; ?>>Offline</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">🔍 Search</button>
                <a href="taxis.php" class="btn btn-secondary">🔄 Reset</a>
            </form>
        </div>

        <!-- Taxis Table -->
        <div class="data-table">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>All Taxis (<?php echo $total_taxis; ?>)</h3>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Taxi Number</th>
                        <th>Model</th>
                        <th>Capacity</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Driver</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($taxis)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No taxis found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($taxis as $taxi): ?>
                        <tr>
                            <td><?php echo $taxi['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($taxi['taxi_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($taxi['model']); ?></td>
                            <td><?php echo $taxi['capacity']; ?> seats</td>
                            <td><?php echo htmlspecialchars($taxi['current_location']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo getTaxiStatusBadge($taxi['status']); ?>">
                                    <?php echo ucfirst($taxi['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $taxi['driver_name'] ?? 'Not assigned'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($taxi['created_at'])); ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-view">View</a>
                                <a href="taxis.php?action=delete&id=<?php echo $taxi['id']; ?>" 
                                   class="btn btn-sm btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this taxi?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Helper function for taxi status badges
    function getTaxiStatusBadge($status) {
        switch($status) {
            case 'available': return 'success';
            case 'booked': return 'warning';
            case 'offline': return 'danger';
            default: return 'secondary';
        }
    }
    ?>
</body>
</html>