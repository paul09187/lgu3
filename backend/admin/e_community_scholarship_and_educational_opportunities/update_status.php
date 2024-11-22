<?php
session_start();
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $status = $_POST['status'];

    try {
        $stmt = $conn->prepare("UPDATE users SET scholarship_status = :status WHERE id = :userId");
        $stmt->execute([':status' => $status, ':userId' => $userId]);

        // Optionally, send notification here
        echo json_encode(['success' => true, 'message' => 'Application status updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
