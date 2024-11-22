<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require '../../../database/connection.php';

try {
    // Corrected column name from 'timestamp' to 'created_at'
    $stmt = $conn->prepare("
    SELECT al.*, u.name AS user_name 
    FROM audit_logs al 
    JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC
");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'logs' => $logs]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
