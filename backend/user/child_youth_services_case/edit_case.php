<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caseId = intval($_POST['id']);
    $caseTitle = htmlspecialchars(strip_tags($_POST['case_title']));
    $caseType = htmlspecialchars(strip_tags($_POST['case_type']));
    $notes = htmlspecialchars(strip_tags($_POST['notes']));
    $userId = $_SESSION['user_id'];

    if (empty($caseTitle) || empty($caseType)) {
        echo json_encode(['success' => false, 'message' => 'Case title and type are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            UPDATE cases 
            SET case_title = :case_title, case_type = :case_type, notes = :notes 
            WHERE id = :id AND created_by = :user_id
        ");

        $stmt->execute([
            'case_title' => $caseTitle,
            'case_type' => $caseType,
            'notes' => $notes,
            'id' => $caseId,
            'user_id' => $userId,
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Case updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Case not found or no changes made.']);
        }
    } catch (PDOException $e) {
        error_log("Database error in edit_case.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update case.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
