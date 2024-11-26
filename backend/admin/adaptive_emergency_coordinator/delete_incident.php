    <?php
    session_start();
    require '../../../database/connection.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $incidentId = $_POST['id'] ?? null;

        if (!$incidentId) {
            echo json_encode(['message' => 'Invalid input.']);
            exit;
        }

        try {
            // Fetch media information for the incident
            $stmt = $conn->prepare("SELECT media_path FROM incident_logs WHERE id = :id");
            $stmt->execute([':id' => $incidentId]);
            $incident = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($incident) {
                // Delete media file from the server
                if (!empty($incident['media_path']) && file_exists('../../../' . $incident['media_path'])) {
                    unlink('../../../' . $incident['media_path']);
                }

                // Delete the incident record from the database
                $stmt = $conn->prepare("DELETE FROM incident_logs WHERE id = :id");
                $stmt->execute([':id' => $incidentId]);

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
