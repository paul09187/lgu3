<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $caseId = intval($_GET['id']);
    $userId = $_SESSION['user_id']; // Ensure the case belongs to the logged-in user

    try {
        $stmt = $conn->prepare("
        SELECT id, case_title, case_type, status, notes, user_id_file, user_age, created_at 
        FROM cases 
        WHERE id = :id AND created_by = :user_id
    ");

        $stmt->execute(['id' => $caseId, 'user_id' => $userId]);
        $case = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($case) {
            echo json_encode(['success' => true, 'case' => $case]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Case not found or you are not authorized to view it.']);
        }
    } catch (PDOException $e) {
        error_log("Database error in view_case.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to fetch case details.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
