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
    $userAge = intval($_POST['user_age']);
    $createdBy = $_SESSION['user_id'];

    if (empty($caseTitle) || empty($caseType) || empty($userAge)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Age verification
    if ($userAge < 18) {
        echo json_encode(['success' => false, 'message' => 'You must be at least 18 years old to submit a case.']);
        exit;
    }

    // Handle file upload
    if (isset($_FILES['user_id_file']) && $_FILES['user_id_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../uploads/user_ids/';
        $fileName = uniqid() . '_' . basename($_FILES['user_id_file']['name']);
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['user_id_file']['tmp_name'], $filePath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload ID file.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID upload is required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO cases (case_title, case_type, notes, created_by, user_id_file, user_age) 
            VALUES (:case_title, :case_type, :notes, :created_by, :user_id_file, :user_age)
        ");
        $stmt->execute([
            'case_title' => $caseTitle,
            'case_type' => $caseType,
            'notes' => $notes,
            'created_by' => $createdBy,
            'user_id_file' => $fileName,
            'user_age' => $userAge,
        ]);

        echo json_encode(['success' => true, 'message' => 'Case submitted successfully.']);
    } catch (PDOException $e) {
        error_log("Database error in submit_user_case.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to submit case.']);
    }
}
