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
    $userAge = isset($_POST['user_age']) ? intval($_POST['user_age']) : null;
    $userId = $_SESSION['user_id'];

    if (empty($caseTitle) || empty($caseType) || empty($userAge)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Age verification
    if ($userAge < 18) {
        echo json_encode(['success' => false, 'message' => 'You must be at least 18 years old.']);
        exit;
    }

    // Handle file upload if a file is provided
    $uploadedFileName = null;
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
        // Update query with optional file
        $sql = "
            UPDATE cases 
            SET case_title = :case_title, 
                case_type = :case_type, 
                notes = :notes, 
                user_age = :user_age
        ";
        $params = [
            'case_title' => $caseTitle,
            'case_type' => $caseType,
            'notes' => $notes,
            'user_age' => $userAge,
            'id' => $caseId,
            'user_id' => $userId,
        ];

        if ($uploadedFileName) {
            $sql .= ", user_id_file = :user_id_file";
            $params['user_id_file'] = $uploadedFileName;
        }

        $sql .= " WHERE id = :id AND created_by = :user_id";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Case updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or unauthorized action.']);
        }
    } catch (PDOException $e) {
        error_log("Database error in edit_case.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to update case.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
