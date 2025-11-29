<?php include 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .login-type-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #8B0000;
        }
        .login-type-icon {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        .test-accounts {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
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
            <h2>Login to Your Account</h2>
            
            <?php
            if (isset($_SESSION['error'])) {
                echo displayMessage($_SESSION['error'], 'error');
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo displayMessage($_SESSION['success'], 'success');
                unset($_SESSION['success']);
            }
            ?>

            <!-- User Type Information -->
            <div class="login-types">
                <div class="login-type-card">
                    <div class="login-type-icon">🚗</div>
                    <div><strong>Passenger</strong></div>
                    <small>Book taxis and ride</small>
                </div>
                <div class="login-type-card">
                    <div class="login-type-icon">🚕</div>
                    <div><strong>Driver</strong></div>
                    <small>Drive and earn money</small>
                </div>
                <div class="login-type-card">
                    <div class="login-type-icon">👨‍💼</div>
                    <div><strong>Admin</strong></div>
                    <small>Manage system</small>
                </div>
            </div>

            <!-- Test Accounts Information -->
            <div class="test-accounts">
                <h4>🧪 Test Accounts:</h4>
                <p><strong>Admin:</strong> 256700000000 / admin123</p>
                <p><strong>Passenger:</strong> Use any registered number</p>
                <p><strong>Driver:</strong> Register as driver first</p>
            </div>

            <form action="api/users.php" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label for="phone">📞 Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="256700000000" required>
                </div>

                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="cta-button" style="width: 100%;">Login</button>
            </form>

            <p style="text-align: center; margin-top: 1rem;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>

            <div style="text-align: center; margin-top: 1rem;">
                <a href="create_test_accounts.php" class="btn btn-secondary">Create Test Accounts</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 E-Taxi Booking System. Group 6 - Uganda Christian University</p>
    </footer>
</body>
</html>