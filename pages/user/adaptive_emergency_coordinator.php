<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Fetch emergency alerts
$stmt = $conn->prepare("SELECT title, message, created_at FROM emergency_alerts ORDER BY created_at DESC");
$stmt->execute();
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch incidents reported by the user
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM incident_logs WHERE reported_by = :userId ORDER BY created_at DESC");
$stmt->execute(['userId' => $userId]);
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../../include/header.php'; ?>
<?php include '../../include/sidebar.php'; ?>
<?php include '../../include/topbar.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Emergency Coordinator</h1>

    <!-- Emergency Alerts -->
    <div class="mb-4">
        <h3>Emergency Alerts</h3>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped custom-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alerts as $alert) : ?>
                        <tr>
                            <td><?= htmlspecialchars($alert['title']); ?></td>
                            <td><?= htmlspecialchars($alert['message']); ?></td>
                            <td><?= htmlspecialchars($alert['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Report an Incident -->
    <div class="mb-4">
        <h3>Report an Incident</h3>
        <form id="incidentForm">
            <div class="mb-3">
                <label for="incident_type" class="form-label">Incident Type</label>
                <input type="text" id="incident_type" name="incident_type" class="form-control" placeholder="e.g., Fire, Flood" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" placeholder="Provide details of the incident" required></textarea>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" id="location" name="location" class="form-control" placeholder="Enter the location of the incident" required>
            </div>
            <button type="submit" class="btn btn-primary">Report Incident</button>
        </form>
    </div>

    <!-- Incident Logs -->
    <div class="mb-4">
        <h3>Your Incident Logs</h3>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped custom-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Reported At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidents as $incident) : ?>
                        <tr>
                            <td><?= htmlspecialchars($incident['incident_type']); ?></td>
                            <td><?= htmlspecialchars($incident['description']); ?></td>
                            <td><?= htmlspecialchars($incident['location']); ?></td>
                            <td><?= ucfirst($incident['status']); ?></td>
                            <td><?= htmlspecialchars($incident['incident_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Handle Incident Reporting Form Submission
    $('#incidentForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../../../backend/user/adaptive_emergency_coordinator/incidents.php', $(this).serialize(), function(response) {
            try {
                const data = JSON.parse(response);
                alert(data.message);
                if (data.message.includes('successfully')) {
                    location.reload(); // Reload to update the logs
                }
            } catch (error) {
                alert('An error occurred while processing your request.');
            }
        });
    });
</script>