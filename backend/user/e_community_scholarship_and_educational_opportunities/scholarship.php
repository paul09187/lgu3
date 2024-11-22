<?php
session_start();
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;

    // Validate file upload
    if (!isset($_FILES['documents']) || $_FILES['documents']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload failed. Please try again.']);
        exit;
    }

    $file = $_FILES['documents'];
    $allowedTypes = ['application/pdf', 'application/msword', 'image/jpeg', 'image/png'];

    // Validate file type
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
        exit;
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5242880) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds the 5MB limit.']);
        exit;
    }

    $uploadDir = '../../../uploads/scholarships/';
    $filePath = $uploadDir . uniqid() . '-' . basename($file['name']);

    // Move file securely
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file.']);
        exit;
    }

    try {
        // Update user scholarship status
        $stmt = $conn->prepare("UPDATE users SET scholarship_status = 'applied', application_date = NOW(), document_path = :filePath WHERE id = :userId");
        $stmt->execute([
            ':filePath' => $filePath,
            ':userId' => $userId
        ]);

        echo json_encode(['success' => true, 'message' => 'Scholarship application submitted successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to process application: ' . $e->getMessage()]);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}
