<?php
session_start();
require '../../../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidentType = $_POST['incident_type'] ?? null;
    $description = $_POST['description'] ?? null;
    $location = $_POST['location'] ?? null;
    $reportedBy = $_SESSION['user_id'] ?? null;

    // Validate required fields
    if (!$incidentType || !$description || !$location || !$reportedBy) {
        echo json_encode(['message' => 'All fields are required.']);
        exit;
    }

    // Handle media upload
    $mediaPath = null;
    $mediaType = null;

    if (!empty($_FILES['media']['name'])) {
        $file = $_FILES['media'];
        $uploadDir = '../../../uploads/incidents/';
        $fileName = time() . '_' . basename($file['name']);
        $targetFilePath = $uploadDir . $fileName;

        // Create directory if not exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            $mediaPath = 'uploads/incidents/' . $fileName;
            $fileType = mime_content_type($targetFilePath);

            if (strpos($fileType, 'image') !== false) {
                $mediaType = 'image';
            } elseif (strpos($fileType, 'video') !== false) {
                $mediaType = 'video';
            } else {
                unlink($targetFilePath);
                echo json_encode(['message' => 'Invalid file type. Only images/videos are allowed.']);
                exit;
            }
        } else {
            echo json_encode(['message' => 'Media upload failed.']);
            exit;
        }
    }

    try {
        // Insert incident log
        $stmt = $conn->prepare("INSERT INTO incident_logs (incident_type, description, location, media_type, media_path, reported_by, incident_date, created_at) 
                                VALUES (:incident_type, :description, :location, :media_type, :media_path, :reported_by, NOW(), NOW())");
        $stmt->execute([
            ':incident_type' => $incidentType,
            ':description' => $description,
            ':location' => $location,
            ':media_type' => $mediaType,
            ':media_path' => $mediaPath,
            ':reported_by' => $reportedBy
        ]);

        echo json_encode(['message' => 'Incident reported successfully.']);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Failed to report incident: ' . $e->getMessage()]);
    }
    exit;
}
