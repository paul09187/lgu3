<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../../database/connection.php';
require '../../../database/utils.php'; // Corrected file path

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caseId = intval($_POST['id']);
    $caseTitle = htmlspecialchars(strip_tags($_POST['case_title']));
    $caseType = htmlspecialchars(strip_tags($_POST['case_type']));
    $guardianName = htmlspecialchars(strip_tags($_POST['guardian_name']));
    $guardianContact = htmlspecialchars(strip_tags($_POST['guardian_contact']));
    $notes = htmlspecialchars(strip_tags($_POST['notes']));
    $status = htmlspecialchars(strip_tags($_POST['status']));
    $userAge = intval($_POST['user_age']);
    $uploadedFileName = null;

    // Validate and handle optional file upload
    if (isset($_FILES['user_id_file']) && $_FILES['user_id_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../uploads/user_ids/';
        $fileName = uniqid() . '_' . basename($_FILES['user_id_file']['name']);
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['user_id_file']['tmp_name'], $filePath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload ID file.']);
            exit;
        }

        $uploadedFileName = $fileName;
    }

    try {
        $sql = "
            UPDATE cases 
            SET case_title = :case_title, case_type = :case_type, guardian_name = :guardian_name, 
                guardian_contact = :guardian_contact, notes = :notes, status = :status, user_age = :user_age
        ";
        $params = [
            'case_title' => $caseTitle,
            'case_type' => $caseType,
            'guardian_name' => $guardianName,
            'guardian_contact' => $guardianContact,
            'notes' => $notes,
            'status' => $status,
            'user_age' => $userAge,
            'id' => $caseId,
        ];

        if ($uploadedFileName) {
            $sql .= ", user_id_file = :user_id_file";
            $params['user_id_file'] = $uploadedFileName;
        }

        $sql .= " WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        logAudit($_SESSION['user_id'], "Edited case", json_encode(['case_id' => $caseId]));

        echo json_encode(['success' => true, 'message' => 'Case updated successfully.']);
    } catch (PDOException $e) {
        error_log("Edit case error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update case.']);
    }
}
