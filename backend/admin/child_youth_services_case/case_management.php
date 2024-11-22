<?php
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("SELECT id, case_title, case_type, status, created_at FROM cases ORDER BY created_at DESC");
        $stmt->execute();
        $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'cases' => $cases]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch cases.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
