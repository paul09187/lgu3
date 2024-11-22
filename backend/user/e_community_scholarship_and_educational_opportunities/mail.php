<?php

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'seancatian9@gmail.com';
$mail->Password = 'xetc asgl hjsd siqi';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('seancatian9@gmail.com', 'E-Community Scholarship');
$mail->addAddress($userEmail);

$mail->Subject = 'Application Status Update';
$mail->Body = "Dear {$userName},\n\nYour application status has been updated to: {$status}.";

if ($mail->send()) {
    echo "Notification sent successfully.";
} else {
    echo "Failed to send notification.";
}
