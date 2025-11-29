<?php include 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .user-type-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .user-type-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .user-type-card:hover {
            border-color: #8B0000;
            transform: translateY(-2px);
        }
        .user-type-card.selected {
            border-color: #8B0000;
            background: #fff5f5;
        }
        .user-type-card input[type="radio"] {
            display: none;
        }
        .user-type-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .user-type-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .user-type-desc {
            font-size: 0.8rem;
            color: #666;
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
            <h2>Create Your Account</h2>
            
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

            <form action="api/users.php" method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="name">👤 Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="phone">📞 Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="256700000000" required>
                </div>

                <div class="form-group">
                    <label for="email">📧 Email (Optional)</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>

                <div class="form-group">
                    <label>🎯 Account Type</label>
                    <div class="user-type-options">
                        <!-- Passenger -->
                        <label class="user-type-card">
                            <input type="radio" name="user_type" value="passenger" required>
                            <div class="user-type-icon">🚗</div>
                            <div class="user-type-name">Passenger</div>
                            <div class="user-type-desc">Book taxis and ride</div>
                        </label>

                        <!-- Driver -->
                        <label class="user-type-card">
                            <input type="radio" name="user_type" value="driver" required>
                            <div class="user-type-icon">🚕</div>
                            <div class="user-type-name">Taxi Driver</div>
                            <div class="user-type-desc">Drive and earn money</div>
                        </label>

                        <!-- Admin (Hidden for public registration) -->
                        <!-- Admin accounts should be created manually or through special invitation -->
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">🔒 Password</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="At least 6 characters">
                </div>

                <div class="form-group">
                    <label for="confirm_password">✅ Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="cta-button" style="width: 100%;">Create Account</button>
            </form>

            <p style="text-align: center; margin-top: 1rem;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 E-Taxi Booking System. Group 6 - Uganda Christian University</p>
    </footer>

    <script>
        // User type selection styling
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.user-type-card');
            cards.forEach(card => {
                card.addEventListener('click', function() {
                    cards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                });
            });
        });

        // Form validation
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const userType = document.querySelector('input[name="user_type"]:checked');

            if (!userType) {
                alert('Please select an account type');
                return false;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }

            if (password.length < 6) {
                alert('Password must be at least 6 characters long');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>