
<?php
require '../../../database/connection.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caseId = intval($_POST['id']);
    logAudit($_SESSION['user_id'], "Deleted case with ID $caseId");

    try {
        $stmt = $conn->prepare("DELETE FROM cases WHERE id = :id");
        $stmt->execute(['id' => $caseId]);

        echo json_encode(['success' => true, 'message' => 'Case deleted successfully.']);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
