<?php
session_start();
require '../../../database/connection.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? null;
    $message = $_POST['message'] ?? null;
    $createdBy = $_SESSION['user_id'] ?? null;
    $mediaType = null;
    $mediaPath = null;

    if (!$title || !$message || !$createdBy) {
        echo json_encode(['message' => 'All fields are required.']);
        exit;
    }

    // Handle media upload
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../uploads/emergency_alerts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create upload directory if it doesn't exist
        }

        $fileTmp = $_FILES['media']['tmp_name'];
        $fileName = basename($_FILES['media']['name']);
        $fileType = mime_content_type($fileTmp);
        $filePath = $uploadDir . $fileName;

        if (strpos($fileType, 'image') === 0) {
            $mediaType = 'image';
        } elseif (strpos($fileType, 'video') === 0) {
            $mediaType = 'video';
        } else {
            echo json_encode(['message' => 'Unsupported file type. Only images and videos are allowed.']);
            exit;
        }

        if (move_uploaded_file($fileTmp, $filePath)) {
            $mediaPath = $filePath;
        } else {
            echo json_encode(['message' => 'Failed to upload media.']);
            exit;
        }
    }

    try {
        $stmt = $conn->prepare("INSERT INTO emergency_alerts (title, message, created_by, created_at, media_type, media_path) 
                                VALUES (:title, :message, :created_by, NOW(), :media_type, :media_path)");
        $stmt->execute([
            ':title' => $title,
            ':message' => $message,
            ':created_by' => $createdBy,
            ':media_type' => $mediaType,
            ':media_path' => $mediaPath
        ]);
        echo json_encode(['message' => 'Emergency alert created successfully.']);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Failed to create alert: ' . $e->getMessage()]);
    }
    exit;
}
