<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caseTitle = htmlspecialchars(strip_tags($_POST['case_title']));
    $caseType = htmlspecialchars(strip_tags($_POST['case_type']));
    $notes = htmlspecialchars(strip_tags($_POST['notes']));
    $createdBy = $_SESSION['user_id'];

    if (empty($caseTitle) || empty($caseType)) {
        echo json_encode(['success' => false, 'message' => 'Case title and type are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO cases (case_title, case_type, notes, created_by) 
            VALUES (:case_title, :case_type, :notes, :created_by)
        ");
        $stmt->execute([
            'case_title' => $caseTitle,
            'case_type' => $caseType,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);

        echo json_encode(['success' => true, 'message' => 'Case submitted successfully.']);
    } catch (PDOException $e) {
        error_log("Database error in submit_user_case.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to submit case.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
