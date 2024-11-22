<?php
session_start();
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidentId = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$incidentId || !$status) {
        echo json_encode(['message' => 'Invalid input.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE incident_logs SET status = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $status,
            ':id' => $incidentId
        ]);
        echo json_encode(['message' => 'Status updated successfully.']);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Failed to update status: ' . $e->getMessage()]);
    }
    exit;
} else {
    echo json_encode(['message' => 'Invalid request method.']);
    exit;
}
