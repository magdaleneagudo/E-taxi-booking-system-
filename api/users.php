<?php
include '../includes/config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch($action) {
    case 'register':
        handleRegistration();
        break;
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        // FIXED: Added missing comma in the JSON string
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function handleRegistration() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email'] ?? '');
    $user_type = $_POST['user_type'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($phone) || empty($password)) {
        $_SESSION['error'] = 'All fields are required';
        header('Location: ../register.php');
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header('Location: ../register.php');
        exit();
    }
    
    if (strlen($password) < 6) {
        $_SESSION['error'] = 'Password must be at least 6 characters long';
        header('Location: ../register.php');
        exit();
    }
    
    // Check if phone already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Phone number already registered';
        header('Location: ../register.php');
        exit();
    }
    
    // Create user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, user_type, password) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $phone, $email, $user_type, $hashed_password])) {
        $_SESSION['success'] = 'Account created successfully! Please login.';
        header('Location: ../login.php');
        exit();
    } else {
        $_SESSION['error'] = 'Registration failed. Please try again.';
        header('Location: ../register.php');
        exit();
    }
}

function handleLogin() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_phone'] = $user['phone'];
        
        $_SESSION['success'] = 'Login successful!';
        header('Location: ../dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = 'Invalid phone number or password';
        header('Location: ../login.php');
        exit();
    }
}

function handleLogout() {
    session_destroy();
    header('Location: ../index.php');
    exit();
}
?>