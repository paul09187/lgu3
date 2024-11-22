<?php
session_start();
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidentType = $_POST['incident_type'] ?? null;
    $description = $_POST['description'] ?? null;
    $location = $_POST['location'] ?? null;
    $reportedBy = $_SESSION['user_id'] ?? null;

    if (!$incidentType || !$description || !$location || !$reportedBy) {
        echo json_encode(['message' => 'All fields are required.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO incident_logs (incident_type, description, location, reported_by, incident_date, created_at) 
                                VALUES (:incident_type, :description, :location, :reported_by, NOW(), NOW())");
        $stmt->execute([
            ':incident_type' => $incidentType,
            ':description' => $description,
            ':location' => $location,
            ':reported_by' => $reportedBy
        ]);
        echo json_encode(['message' => 'Incident reported successfully.']);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Failed to report incident: ' . $e->getMessage()]);
    }
    exit;
}
