<?php
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Invalid application ID.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT name, email, contact_number, scholarship_status, application_date, document_path
            FROM users WHERE id = :id
        ");
        $stmt->execute(['id' => $userId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($application) {
            echo json_encode(['success' => true, 'application' => $application]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Application not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
