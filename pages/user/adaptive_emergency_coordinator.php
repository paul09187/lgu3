<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Fetch emergency alerts with media information
$stmt = $conn->prepare("SELECT title, message, media_type, media_path, created_at FROM emergency_alerts ORDER BY created_at DESC");
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
                        <th>Media</th>
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
        <form id="incidentForm" enctype="multipart/form-data">
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
            <div class="mb-3">
                <label for="incident_media" class="form-label">Upload Picture/Video</label>
                <input type="file" id="incident_media" name="media" class="form-control" accept="image/*,video/*">
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
                        <th>Media</th>
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
                            <td><?= ucfirst($incident['status']); ?></td>
                            <td><?= htmlspecialchars($incident['incident_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Image/Video Preview Modal -->
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
        // Handle Incident Submission
        $('#incidentForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: '../../../backend/user/adaptive_emergency_coordinator/incidents.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        alert(data.message);
                        if (data.message.includes('successfully')) {
                            location.reload();
                        }
                    } catch (error) {
                        alert('An error occurred.');
                    }
                },
                error: function() {
                    alert('An error occurred while uploading the file.');
                }
            });
        });

        // Preview Media Modal
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
    });
</script>