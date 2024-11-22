<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $caseId = intval($_POST['id']);
    $userId = $_SESSION['user_id'];

    try {
        $stmt = $conn->prepare("
            DELETE FROM cases 
            WHERE id = :id AND created_by = :user_id
        ");
        $stmt->execute(['id' => $caseId, 'user_id' => $userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Case deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Case not found or you are not authorized to delete it.']);
        }
    } catch (PDOException $e) {
        error_log("Database error in delete_case.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete case.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
