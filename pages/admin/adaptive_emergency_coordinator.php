<?php
session_start();

// Redirect non-admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Fetch emergency alerts
$stmt = $conn->prepare("SELECT emergency_alerts.title, emergency_alerts.message, users.name AS created_by, emergency_alerts.created_at 
                        FROM emergency_alerts 
                        JOIN users ON emergency_alerts.created_by = users.id 
                        ORDER BY emergency_alerts.created_at DESC");
$stmt->execute();
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch incident logs
$stmt = $conn->prepare("SELECT il.*, u.name AS reported_by_name FROM incident_logs il 
                        JOIN users u ON il.reported_by = u.id 
                        ORDER BY il.created_at DESC");
$stmt->execute();
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Function to fetch incident counts for Highcharts
function fetchCount($conn, $query)
{
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_COLUMN) ?? 0;
}
?>

<?php include '../../include/header.php'; ?>
<?php include '../../include/sidebar.php'; ?>
<?php include '../../include/topbar.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Emergency Coordinator</h1>

    <div class="row">
        <!-- Create Alert -->
        <div class="col-lg-6 col-md-12 mb-4">
            <h3>Create Emergency Alert</h3>
            <form id="alertForm">
                <input type="text" name="title" placeholder="Title" required class="form-control mb-2">
                <textarea name="message" placeholder="Message" required class="form-control mb-2"></textarea>
                <button type="submit" class="btn btn-primary btn-block">Send Alert</button>
            </form>
        </div>

        <!-- Log an Incident -->
        <div class="col-lg-6 col-md-12 mb-4">
            <h3>Log an Incident</h3>
            <form id="incidentForm">
                <input type="text" name="incident_type" placeholder="Incident Type (e.g., Fire)" required class="form-control mb-2">
                <textarea name="description" placeholder="Description" required class="form-control mb-2"></textarea>
                <input type="text" name="location" placeholder="Location" required class="form-control mb-2">
                <button type="submit" class="btn btn-primary btn-block">Log Incident</button>
            </form>
        </div>
    </div>

    <!-- Emergency Alerts -->
    <div class="mt-4">
        <h3>Emergency Alerts</h3>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped custom-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alerts as $alert) : ?>
                        <tr>
                            <td><?= htmlspecialchars($alert['title']); ?></td>
                            <td><?= htmlspecialchars($alert['message']); ?></td>
                            <td><?= htmlspecialchars($alert['created_by']); ?></td>
                            <td><?= htmlspecialchars($alert['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Incident Logs -->
    <div class="mt-4">
        <h3>Incident Logs</h3>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-striped custom-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Reported By</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidents as $incident) : ?>
                        <tr>
                            <td><?= htmlspecialchars($incident['incident_type']); ?></td>
                            <td><?= htmlspecialchars($incident['description']); ?></td>
                            <td><?= htmlspecialchars($incident['location']); ?></td>
                            <td><?= htmlspecialchars($incident['reported_by_name']); ?></td>
                            <td>
                                <select name="status" class="form-control status-dropdown" data-id="<?= $incident['id']; ?>">
                                    <option value="pending" <?= $incident['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="resolved" <?= $incident['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="closed" <?= $incident['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </td>
                            <td><?= htmlspecialchars($incident['incident_date']); ?></td>
                            <td>
                                <button class="btn btn-danger btn-sm delete-incident" data-id="<?= $incident['id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<div id="incidentChart"></div>

<?php include '../../include/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Handle Create Alert Form Submission
    $('#alertForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../../../backend/admin/adaptive_emergency_coordinator/alerts.php', $(this).serialize(), function(response) {
            try {
                const data = JSON.parse(response);
                alert(data.message);
                location.reload();
            } catch (error) {
                alert('An error occurred.');
            }
        });
    });

    // Handle Incident Logging Form Submission
    $('#incidentForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../../../backend/admin/adaptive_emergency_coordinator/incidents.php', $(this).serialize(), function(response) {
            try {
                const data = JSON.parse(response);
                alert(data.message);
                location.reload();
            } catch (error) {
                alert('An error occurred.');
            }
        });
    });

    // Handle Status Update
    $('.status-dropdown').on('change', function() {
        const incidentId = $(this).data('id');
        const status = $(this).val();

        $.post('../../../backend/admin/adaptive_emergency_coordinator/update_status.php', {
            id: incidentId,
            status: status
        }, function(response) {
            try {
                const data = JSON.parse(response);
                alert(data.message);
                location.reload();
            } catch (error) {
                alert('An error occurred.');
            }
        });
    });

    // Chart for Incidents
    document.addEventListener('DOMContentLoaded', function() {
        Highcharts.chart('incidentChart', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Incident Status Summary'
            },
            xAxis: {
                categories: ['Pending', 'Resolved', 'Closed']
            },
            yAxis: {
                title: {
                    text: 'Count'
                }
            },
            series: [{
                name: 'Incidents',
                data: [
                    <?= fetchCount($conn, "SELECT COUNT(*) AS count FROM incident_logs WHERE status = 'pending'"); ?>,
                    <?= fetchCount($conn, "SELECT COUNT(*) AS count FROM incident_logs WHERE status = 'resolved'"); ?>,
                    <?= fetchCount($conn, "SELECT COUNT(*) AS count FROM incident_logs WHERE status = 'closed'"); ?>
                ]
            }]
        });
    });

    // Handle Delete Incident
    $('.delete-incident').on('click', function() {
        const incidentId = $(this).data('id');
        if (confirm('Are you sure you want to delete this incident?')) {
            $.post(
                '../../../backend/admin/adaptive_emergency_coordinator/delete_incident.php', {
                    id: incidentId
                },
                function(response) {
                    try {
                        const data = JSON.parse(response);
                        alert(data.message);
                        if (data.message.includes('successfully')) {
                            location.reload();
                        }
                    } catch (error) {
                        alert('An error occurred while processing the request.');
                    }
                }
            );
        }
    });
</script>