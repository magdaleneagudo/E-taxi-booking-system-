<?php
include '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle user actions
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Prevent admin from deleting themselves
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }
    } else {
        $_SESSION['error'] = 'You cannot delete your own account';
    }
    
    header('Location: users.php');
    exit();
}

// Get all users
$search = $_GET['search'] ?? '';
$user_type = $_GET['user_type'] ?? '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($user_type)) {
    $sql .= " AND user_type = ?";
    $params[] = $user_type;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_users = count($users);
$passengers = $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'passenger'")->fetchColumn();
$drivers = $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'driver'")->fetchColumn();
$admins = $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'admin'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
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
    <li><a href="users.php" class="active">👥 Users</a></li>
    <li><a href="taxis.php">🚗 Taxis</a></li>
    <li><a href="bookings.php">📋 Bookings</a></li>
    <li><a href="reports.php">📈 Reports</a></li>
    <li><a href="../dashboard.php">👤 User View</a></li>
    <li><a href="../logout.php">🚪 Logout</a></li>
</ul>
        </nav>
    </div>

    <div class="admin-container">
        <h1>👥 User Management</h1>

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

        <!-- User Statistics -->
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $passengers; ?></div>
                <div class="stat-label">Passengers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $drivers; ?></div>
                <div class="stat-label">Drivers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $admins; ?></div>
                <div class="stat-label">Admins</div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="search-box">
            <form method="GET" action="users.php">
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, phone, or email" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group">
                        <select name="user_type" class="form-control">
                            <option value="">All User Types</option>
                            <option value="passenger" <?php echo $user_type == 'passenger' ? 'selected' : ''; ?>>Passenger</option>
                            <option value="driver" <?php echo $user_type == 'driver' ? 'selected' : ''; ?>>Driver</option>
                            <option value="admin" <?php echo $user_type == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn">🔍 Search</button>
                <a href="users.php" class="btn btn-secondary">🔄 Reset</a>
            </form>
        </div>

        <!-- Users Table -->
        <div class="data-table">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3>All Users (<?php echo $total_users; ?>)</h3>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($user['name']); ?>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge badge-info">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo getUserTypeBadge($user['user_type']); ?>">
                                    <?php echo ucfirst($user['user_type']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-view">View</a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    // Helper function for user type badges
    function getUserTypeBadge($type) {
        switch($type) {
            case 'admin': return 'danger';
            case 'driver': return 'warning';
            case 'passenger': return 'success';
            default: return 'secondary';
        }
    }
    ?>
</body>
</html>