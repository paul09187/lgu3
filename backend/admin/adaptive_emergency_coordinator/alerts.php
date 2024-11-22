<?php
session_start();
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? null;
    $message = $_POST['message'] ?? null;
    $createdBy = $_SESSION['user_id'] ?? null;

    if (!$title || !$message || !$createdBy) {
        echo json_encode(['message' => 'All fields are required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO emergency_alerts (title, message, created_by, created_at) VALUES (:title, :message, :created_by, NOW())");
    $stmt->execute([
        ':title' => $title,
        ':message' => $message,
        ':created_by' => $createdBy
    ]);

    if (!$title || !$message || !$createdBy) {
        echo json_encode(['message' => 'All fields are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO emergency_alerts (title, message, created_by, created_at) VALUES (:title, :message, :created_by, NOW())");
        $stmt->execute([
            ':title' => $title,
            ':message' => $message,
            ':created_by' => $createdBy
        ]);
        echo json_encode(['message' => 'Emergency alert created successfully.']);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Failed to create alert: ' . $e->getMessage()]);
    }
    exit;
}
