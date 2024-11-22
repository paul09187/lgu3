<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require '../../../database/connection.php';

$userId = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("
        SELECT id, case_title, case_type, status, created_at 
        FROM cases 
        WHERE created_by = :user_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute(['user_id' => $userId]);
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($cases) {
        echo json_encode(['success' => true, 'cases' => $cases]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No cases found.']);
    }
} catch (PDOException $e) {
    error_log("Database error in fetch_user_cases.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to fetch cases.']);
}
