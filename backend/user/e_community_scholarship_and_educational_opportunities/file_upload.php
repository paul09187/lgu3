<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['documents']) || $_FILES['documents']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['message' => 'File upload failed.']);
        exit;
    }

    $file = $_FILES['documents'];
    $allowedTypes = ['application/pdf', 'application/msword', 'image/jpeg', 'image/png'];

    // Validate file type
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['message' => 'Invalid file type.']);
        exit;
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5242880) {
        echo json_encode(['message' => 'File size exceeds the 5MB limit.']);
        exit;
    }

    // Save file securely
    $uploadDir = '../../../uploads/scholarships/';
    $filePath = $uploadDir . uniqid() . '-' . basename($file['name']);
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['message' => 'Failed to save uploaded file.']);
        exit;
    }

    // Store file path in the database
    $stmt = $conn->prepare("INSERT INTO scholarship_applications (user_id, document_path) VALUES (:user_id, :file_path)");
    $stmt->execute(['user_id' => $_SESSION['user_id'], 'file_path' => $filePath]);

    echo json_encode(['message' => 'Application submitted successfully.']);
}
