<?php
session_start();
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input data
    $incidentType = $_POST['incident_type'] ?? null;
    $description = $_POST['description'] ?? null;
    $location = $_POST['location'] ?? null;
    $reportedBy = $_SESSION['user_id'] ?? null;

    // Validate required fields
    if (!$incidentType || !$description || !$location || !$reportedBy) {
        echo json_encode(['message' => 'All fields are required.']);
        exit;
    }

    try {
        // Insert incident data into the `incident_logs` table
        $stmt = $conn->prepare("INSERT INTO incident_logs (incident_type, description, location, reported_by, incident_date, created_at) 
                                VALUES (:incident_type, :description, :location, :reported_by, NOW(), NOW())");
        $stmt->execute([
            ':incident_type' => $incidentType,
            ':description' => $description,
            ':location' => $location,
            ':reported_by' => $reportedBy
        ]);

        // Success response
        echo json_encode(['message' => 'Incident logged successfully.']);
    } catch (Exception $e) {
        // Error response
        echo json_encode(['message' => 'Failed to log incident: ' . $e->getMessage()]);
    }
    exit;
} else {
    // Invalid request method response
    echo json_encode(['message' => 'Invalid request method.']);
    exit;
}
