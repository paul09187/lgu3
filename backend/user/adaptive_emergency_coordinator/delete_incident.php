<?php
session_start();
require '../../../database/connection.php';

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidentId = $_POST['id'] ?? null;

    // Validate input
    if (!$incidentId) {
        echo json_encode(['message' => 'Invalid input.']);
        exit;
    }

    try {
        // Delete the incident from the database
        $stmt = $conn->prepare("DELETE FROM incident_logs WHERE id = :id");
        $stmt->execute([':id' => $incidentId]);

        // Check if any row was deleted
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Incident deleted successfully.']);
        } else {
            echo json_encode(['message' => 'Incident not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['message' => 'Failed to delete incident: ' . $e->getMessage()]);
    }
    exit;
} else {
    echo json_encode(['message' => 'Invalid request method.']);
    exit;
}
