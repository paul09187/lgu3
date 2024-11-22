<?php
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $caseId = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("SELECT * FROM cases WHERE id = :id");
        $stmt->execute(['id' => $caseId]);
        $case = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($case) {
            echo json_encode(['success' => true, 'case' => $case]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Case not found.']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
