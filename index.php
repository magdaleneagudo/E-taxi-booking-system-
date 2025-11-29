<?php include 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - <?php echo SITE_NAME; ?></title>
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
        <section class="hero">
            <h1>Book Your Taxi in Kampala</h1>
            <p>Fast, Safe, and Reliable Taxi Booking Service. Join thousands of satisfied passengers using our digital platform.</p>
            <a href="book.php" class="cta-button">Book Taxi Now</a>
        </section>

        <section class="features">
            <div class="feature-card">
                <h3>🚗 Real-time Booking</h3>
                <p>Book taxis instantly with live availability and real-time tracking</p>
            </div>
            <div class="feature-card">
                <h3>🛡️ Safe & Secure</h3>
                <p>Verified drivers, secure payments, and 24/7 customer support</p>
            </div>
            <div class="feature-card">
                <h3>💰 Affordable</h3>
                <p>Competitive prices for shared rides with transparent pricing</p>
            </div>
            <div class="feature-card">
                <h3>📱 Easy to Use</h3>
                <p>Simple booking process with web and USSD options available</p>
            </div>
        </section>

        <section class="stats">
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h3>Daily Passengers</h3>
                    <div class="number">1,500+</div>
                </div>
                <div class="dashboard-card">
                    <h3>Available Taxis</h3>
                    <div class="number">200+</div>
                </div>
                <div class="dashboard-card">
                    <h3>Routes Covered</h3>
                    <div class="number">15+</div>
                </div>
                <div class="dashboard-card">
                    <h3>Happy Customers</h3>
                    <div class="number">95%</div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 E-Taxi Booking System. Group 6 - Uganda Christian University</p>
        <p>Address: Kampala Metropolitan Area | Phone: +256 700 000 000</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>