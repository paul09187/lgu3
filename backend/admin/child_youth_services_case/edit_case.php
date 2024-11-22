<?php
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caseId = intval($_POST['id']);
    $caseTitle = htmlspecialchars(strip_tags($_POST['case_title']));
    $caseType = htmlspecialchars(strip_tags($_POST['case_type']));
    $guardianName = htmlspecialchars(strip_tags($_POST['guardian_name']));
    $guardianContact = htmlspecialchars(strip_tags($_POST['guardian_contact']));
    $notes = htmlspecialchars(strip_tags($_POST['notes']));
    $status = htmlspecialchars(strip_tags($_POST['status']));
    logAudit($_SESSION['user_id'], "Edited case titled '$caseTitle'", "Case ID: $caseId");


    if (empty($caseTitle) || empty($caseType) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            UPDATE cases 
            SET case_title = :case_title, case_type = :case_type, guardian_name = :guardian_name, 
                guardian_contact = :guardian_contact, notes = :notes, status = :status 
            WHERE id = :id
        ");
        $stmt->execute([
            'case_title' => $caseTitle,
            'case_type' => $caseType,
            'guardian_name' => $guardianName,
            'guardian_contact' => $guardianContact,
            'notes' => $notes,
            'status' => $status,
            'id' => $caseId,
        ]);

        echo json_encode(['success' => true, 'message' => 'Case updated successfully.']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
