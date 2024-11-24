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
    try {
        $caseId = intval($_POST['id']);

        if (!$caseId) {
            echo json_encode(['success' => false, 'message' => 'Invalid Case ID.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM cases WHERE id = :id");
        $stmt->execute(['id' => $caseId]);

        if ($stmt->rowCount() > 0) {
            logAudit($_SESSION['user_id'], "Deleted case with ID $caseId");
            echo json_encode(['success' => true, 'message' => 'Case deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No case found with the given ID.']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
