<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

require '../../../database/connection.php';

// Function to log actions into audit logs
function logAudit($userId, $action, $details = null)
{
    global $conn;
    try {
        $stmt = $conn->prepare("
            INSERT INTO audit_logs (user_id, action, details) 
            VALUES (:user_id, :action, :details)
        ");
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details
        ]);
    } catch (PDOException $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate the input
    $caseTitle = htmlspecialchars(strip_tags($_POST['case_title'] ?? ''));
    $caseType = htmlspecialchars(strip_tags($_POST['case_type'] ?? ''));
    $guardianName = htmlspecialchars(strip_tags($_POST['guardian_name'] ?? ''));
    $guardianContact = htmlspecialchars(strip_tags($_POST['guardian_contact'] ?? ''));
    $notes = htmlspecialchars(strip_tags($_POST['notes'] ?? ''));
    $createdBy = $_SESSION['user_id'] ?? null;

    // Check for missing required fields
    if (empty($caseTitle) || empty($caseType) || !$createdBy) {
        echo json_encode(['success' => false, 'message' => 'Case title, type, and user session are required.']);
        exit;
    }

    try {
        // Insert the new case into the database
        $stmt = $conn->prepare("
            INSERT INTO cases (case_title, case_type, guardian_name, guardian_contact, notes, created_by)
            VALUES (:case_title, :case_type, :guardian_name, :guardian_contact, :notes, :created_by)
        ");
        $stmt->execute([
            'case_title' => $caseTitle,
            'case_type' => $caseType,
            'guardian_name' => $guardianName,
            'guardian_contact' => $guardianContact,
            'notes' => $notes,
            'created_by' => $createdBy
        ]);

        // Log the action in audit logs
        $caseId = $conn->lastInsertId();
        logAudit($createdBy, "Added new case", json_encode(['case_id' => $caseId, 'title' => $caseTitle]));

        echo json_encode(['success' => true, 'message' => 'Case added successfully.']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to add case.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
