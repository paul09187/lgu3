<?php
session_start();

// Redirect non-admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Fetch emergency alerts
$stmt = $conn->prepare("SELECT emergency_alerts.title, emergency_alerts.message, emergency_alerts.media_type, emergency_alerts.media_path, users.name AS created_by, emergency_alerts.created_at 
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
            <form id="alertForm" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Title" required class="form-control mb-2">
                <textarea name="message" placeholder="Message" required class="form-control mb-2"></textarea>
                <label for="alert_media" class="form-label">Upload Picture/Video</label>
                <input type="file" name="media" id="alert_media" class="form-control mb-2" accept="image/*,video/*">
                <button type="submit" class="btn btn-primary btn-block">Send Alert</button>
            </form>
        </div>

        <!-- Log an Incident -->
        <div class="col-lg-6 col-md-12 mb-4">
            <h3>Log an Incident</h3>
            <form id="incidentForm" enctype="multipart/form-data">
                <input type="text" name="incident_type" placeholder="Incident Type (e.g., Fire)" required class="form-control mb-2">
                <textarea name="description" placeholder="Description" required class="form-control mb-2"></textarea>
                <input type="text" name="location" placeholder="Location" required class="form-control mb-2">
                <label for="incident_media" class="form-label">Upload Picture/Video</label>
                <input type="file" name="media" id="incident_media" class="form-control mb-2" accept="image/*,video/*">
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
                        <th>Media</th>
                        <th>Created By</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alerts as $alert) : ?>
                        <tr>
                            <td><?= htmlspecialchars($alert['title']); ?></td>
                            <td><?= htmlspecialchars($alert['message']); ?></td>
                            <td>
                                <?php if ($alert['media_type'] === 'image') : ?>
                                    <img src="<?= htmlspecialchars($alert['media_path']); ?>" alt="Alert Media"
                                        class="img-thumbnail preview-image" style="max-width: 100px; cursor: pointer;"
                                        data-path="<?= htmlspecialchars($alert['media_path']); ?>">
                                <?php elseif ($alert['media_type'] === 'video') : ?>
                                    <video controls style="max-width: 100px;">
                                        <source src="<?= htmlspecialchars($alert['media_path']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php else : ?>
                                    N/A
                                <?php endif; ?>
                            </td>
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
                        <th>Media</th>
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
                            <td>
                                <?php if ($incident['media_type'] === 'image') : ?>
                                    <img src="../../<?= htmlspecialchars($incident['media_path']); ?>" alt="Incident Media"
                                        class="img-thumbnail preview-image" style="max-width: 100px; cursor: pointer;"
                                        data-path="../../<?= htmlspecialchars($incident['media_path']); ?>">
                                <?php elseif ($incident['media_type'] === 'video') : ?>
                                    <video controls style="max-width: 100px;">
                                        <source src="../../<?= htmlspecialchars($incident['media_path']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php else : ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($incident['reported_by_name']); ?></td>
                            <td>
                                <!-- Dropdown for status -->
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

<!-- Modal for Image/Video Previews -->
<div id="mediaPreviewModal" class="modal fade" tabindex="-1" aria-labelledby="mediaPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPreviewModalLabel">Media Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="#" alt="Preview" class="img-fluid d-none" style="max-width: 100%; max-height: 500px;">
                <video id="modalVideo" controls class="d-none" style="max-width: 100%; max-height: 500px;">
                    <source id="videoSource" src="#" type="video/mp4">
                </video>
            </div>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Handle Form Submissions
        $('#alertForm, #incidentForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: $(this).attr('id') === 'alertForm' ? '../../../backend/admin/adaptive_emergency_coordinator/alerts.php' : '../../../backend/admin/adaptive_emergency_coordinator/incidents.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        alert(data.message);
                        location.reload();
                    } catch (error) {
                        alert('An error occurred.');
                    }
                },
                error: function() {
                    alert('An error occurred while uploading the file.');
                }
            });
        });

        // Preview Modal Logic
        $(document).on('click', '.preview-image', function() {
            const mediaPath = $(this).data('path');
            const isImage = $(this).is('img');

            if (isImage) {
                $('#modalImage').attr('src', mediaPath).removeClass('d-none');
                $('#modalVideo').addClass('d-none');
            } else {
                $('#videoSource').attr('src', mediaPath);
                $('#modalVideo').removeClass('d-none');
                $('#modalImage').addClass('d-none');
            }

            $('#mediaPreviewModal').modal('show');
        });

        // Delete Incident
        $('.delete-incident').on('click', function() {
            const incidentId = $(this).data('id');
            if (confirm('Are you sure you want to delete this incident?')) {
                $.post('../../../backend/admin/adaptive_emergency_coordinator/delete_incident.php', {
                    id: incidentId
                }, function(response) {
                    try {
                        const data = JSON.parse(response);
                        alert(data.message);
                        if (data.message.includes('successfully')) {
                            location.reload();
                        }
                    } catch (error) {
                        alert('An error occurred while processing the request.');
                    }
                });
            }
        });
    });

    $(document).ready(function() {
        // Handle status update
        $('.status-dropdown').on('change', function() {
            const incidentId = $(this).data('id'); // Get the incident ID
            const status = $(this).val(); // Get the selected status

            $.post('../../../backend/admin/adaptive_emergency_coordinator/update_status.php', {
                id: incidentId,
                status: status
            }, function(response) {
                try {
                    const data = JSON.parse(response);
                    alert(data.message);
                    if (data.message.includes('successfully')) {
                        location.reload(); // Optionally reload the page to reflect changes
                    }
                } catch (error) {
                    alert('An error occurred while updating the status.');
                }
            }).fail(function() {
                alert('Failed to send request to the server.');
            });
        });
    });
</script>