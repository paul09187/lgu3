<?php
session_start();
require_once '../database/connection.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$method = $_SERVER['REQUEST_METHOD'];

// Helper function to generate tokens
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

// Helper function to send emails
function sendEmail($to, $subject, $body)
{
    $mail = new PHPMailer(true);
    try {
        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ecojohnpaul01@gmail.com';
        $mail->Password   = 'vpio swhm cxrf usbw';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email settings
        $mail->setFrom('ecojohnpaul01@gmail.com', 'LGU 3');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to send email.']);
        exit;
    }
}

// Login
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['barangay_id'] = $user['barangay_id'];

        echo json_encode(['message' => 'Login successful', 'role' => $user['role']]);
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid email or password']);
    }
}

// Register
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $birth_date = trim($_POST['birth_date']);
    $gender = trim($_POST['gender']);
    $civil_status = trim($_POST['civil_status']);
    $occupation = trim($_POST['occupation']);
    $household_number = trim($_POST['household_number']);
    $barangay_id = trim($_POST['barangay_id']);
    $is_resident = 1;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid email address']);
        exit;
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/', $password)) {
        http_response_code(400);
        echo json_encode(['message' => 'Password must meet security criteria (min 8 chars, include uppercase, lowercase, number, special character).']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Email already registered']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (
        name, email, password, role, contact_number, address, 
        birth_date, gender, civil_status, occupation, 
        household_number, barangay_id, is_resident, created_at
    ) VALUES (
        :name, :email, :password, 'user', :contact_number, :address, 
        :birth_date, :gender, :civil_status, :occupation, 
        :household_number, :barangay_id, :is_resident, NOW()
    )");

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':contact_number' => $contact_number,
            ':address' => $address,
            ':birth_date' => $birth_date,
            ':gender' => $gender,
            ':civil_status' => $civil_status,
            ':occupation' => $occupation,
            ':household_number' => $household_number,
            ':barangay_id' => $barangay_id,
            ':is_resident' => $is_resident
        ]);
    } catch (PDOException $e) {
        // Log the error message
        http_response_code(500);
        echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }


    $loginLink = "http://ecopaul//login.php";
    sendEmail($email, 'Welcome to LGU 3', "Dear $name,\n\nWelcome to LGU 3! You can log in using the following link: $loginLink\n\nBest regards,\nLGU 3 Team");

    echo json_encode(['message' => 'Registration successful. A welcome email has been sent.']);
}

// Forgot Password
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'forgot_password') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['message' => 'If this email exists, a reset link has been sent.']);
        exit;
    }

    $token = generateToken();

    $stmt = $conn->prepare("UPDATE users SET reset_token = :token, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email");
    $stmt->execute([':token' => $token, ':email' => $email]);

    $resetLink = "http://ecopaul/reset_password.php?token=$token";
    sendEmail($email, 'Password Reset Request', "Click the link to reset your password: $resetLink\n\nThis link will expire in 1 hour.");

    echo json_encode(['message' => 'If this email exists, a reset link has been sent.']);
}

// Reset Password
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    $token = trim($_POST['token']);
    $newPassword = trim($_POST['new_password']);

    // Password validation allows all characters
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*\W).{8,}$/', $newPassword)) {
        http_response_code(400);
        echo json_encode(['message' => 'Password must meet security criteria (min 8 chars, include uppercase, lowercase, number, special character).']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expires > NOW()");
    $stmt->execute([':token' => $token]);
    if ($stmt->rowCount() === 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid or expired reset token']);
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE reset_token = :token");
    $stmt->execute([':password' => $hashedPassword, ':token' => $token]);

    echo json_encode(['message' => 'Password reset successful. Please log in with your new password.']);
}


// Logout
if ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    echo json_encode(['message' => 'Logout successful']);
}

// Check Session
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check_session') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'authenticated' => true,
            'user_id' => $_SESSION['user_id'],
            'role' => $_SESSION['role'],
            'name' => $_SESSION['name']
        ]);
    } else {
        echo json_encode(['authenticated' => false]);
    }
}
