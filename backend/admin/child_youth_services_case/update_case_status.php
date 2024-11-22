<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caseId = intval($_POST['id']);
    $status = $_POST['status'];

    try {
        $stmt = $conn->prepare("UPDATE cases SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $caseId]);

        // Insert into audit log
        $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, created_at) VALUES (:user_id, :action, NOW())");
        $logStmt->execute([
            'user_id' => $_SESSION['user_id'],
            'action' => "Updated case ID $caseId to status '$status'",
        ]);

        echo json_encode(['success' => true, 'message' => 'Case status updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
