<?php 
include 'includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Taxi - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .booking-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .available-taxis {
            margin-top: 30px;
        }
        .taxi-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #8B0000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .taxi-card h4 {
            margin: 0 0 5px 0;
            color: #8B0000;
        }
        .taxi-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .taxi-details {
            flex-grow: 1;
        }
        .book-btn {
            background: #8B0000;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .book-btn:hover {
            background: #A52A2A;
        }
        .fare-display {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            text-align: center;
            display: none;
        }
        .route-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .route-btn {
            padding: 10px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .route-btn:hover {
            border-color: #8B0000;
        }
        .route-btn.selected {
            background: #8B0000;
            color: white;
            border-color: #8B0000;
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
        <div class="booking-container">
            <div class="form-container">
                <h2>🚗 Book Your Taxi</h2>
                
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

                <form id="bookingForm" action="api/bookings.php" method="POST">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pickup_location">📍 Pickup Location</label>
                            <select id="pickup_location" name="pickup_location" class="form-control" required>
                                <option value="">Select pickup point</option>
                                <option value="City Square">City Square</option>
                                <option value="Kampala Road">Kampala Road</option>
                                <option value="Nakawa">Nakawa</option>
                                <option value="Ntinda">Ntinda</option>
                                <option value="Bweyogerere">Bweyogerere</option>
                                <option value="Kira">Kira</option>
                                <option value="Najjera">Najjera</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="destination">🎯 Destination</label>
                            <select id="destination" name="destination" class="form-control" required>
                                <option value="">Select destination</option>
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

                    <div class="form-group">
                        <label>🚀 Popular Routes (Click to select):</label>
                        <div class="route-options">
                            <div class="route-btn" data-pickup="City Square" data-dest="Nakawa">City → Nakawa</div>
                            <div class="route-btn" data-pickup="Nakawa" data-dest="City Square">Nakawa → City</div>
                            <div class="route-btn" data-pickup="City Square" data-dest="Ntinda">City → Ntinda</div>
                            <div class="route-btn" data-pickup="Ntinda" data-dest="City Square">Ntinda → City</div>
                            <div class="route-btn" data-pickup="City Square" data-dest="Bweyogerere">City → Bweyogerere</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="passengers_count">👥 Number of Passengers</label>
                        <select id="passengers_count" name="passengers_count" class="form-control" required>
                            <option value="1">1 Passenger</option>
                            <option value="2">2 Passengers</option>
                            <option value="3">3 Passengers</option>
                            <option value="4">4 Passengers</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">📝 Additional Notes (Optional)</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Any special instructions..."></textarea>
                    </div>

                    <div id="fareDisplay" class="fare-display">
                        <h3>Estimated Fare: <span id="fareAmount">0</span> UGX</h3>
                    </div>

                    <button type="submit" class="cta-button" style="width: 100%;">🚖 Book Taxi Now</button>
                </form>
            </div>

            <!-- Available Taxis Section -->
            <div class="available-taxis">
                <div class="form-container">
                    <h3>📍 Available Taxis</h3>
                    <div id="availableTaxisList">
                        <!-- Taxis will be loaded here by JavaScript -->
                        <p>Select pickup location to see available taxis...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 E-Taxi Booking System. Group 6 - Uganda Christian University</p>
    </footer>

    <script src="js/booking.js"></script>
</body>
</html>