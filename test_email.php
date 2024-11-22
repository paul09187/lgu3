<?php
require 'vendor/autoload.php'; // Ensure the correct path to your Composer autoload file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include database connection if needed for testing reset token
require_once 'database/connection.php';

// Helper function to generate a token
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

try {
    // Generate a token for testing
    $token = generateToken();
    echo "Generated token: $token<br>";

    // OPTIONAL: Insert the token into the database for testing
    $email = 'test@example.com'; // Replace with your email
    $stmt = $conn->prepare("UPDATE users SET reset_token = :token, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email");
    $stmt->execute([':token' => $token, ':email' => $email]);
    echo "Token inserted into database.<br>";

    // Prepare the reset link
    $resetLink = "http://yourdomain/reset_password.php?token=$token";

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'seancatian9@gmail.com'; // Your Gmail address
    $mail->Password   = 'xetc asgl hjsd siqi';   // App password (NOT your Gmail password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('seancatian9@gmail.com', 'LGU 3');
    $mail->addAddress($email);

    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "Click the following link to reset your password: $resetLink\n\nThis link will expire in 1 hour.";
    $mail->send();

    echo "Email sent successfully! Check your inbox.";
} catch (Exception $e) {
    echo "Failed to send email: " . $mail->ErrorInfo;
}
