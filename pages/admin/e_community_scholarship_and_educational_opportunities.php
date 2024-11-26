<?php
session_start();

// Ensure the admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Fetch scholarship applications
$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.contact_number, u.scholarship_status, u.application_date, u.document_path
    FROM users u
    WHERE u.scholarship_status != 'not_applied'
    ORDER BY u.application_date DESC
");
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available slots
$slotStmt = $conn->prepare("SELECT total_slots, available_slots FROM interview_slots LIMIT 1");
$slotStmt->execute();
$slotData = $slotStmt->fetch(PDO::FETCH_ASSOC);
$totalSlots = $slotData['total_slots'] ?? 0;
$availableSlots = $slotData['available_slots'] ?? 0;
?>

<?php include '../../include/header.php'; ?>
<?php include '../../include/sidebar.php'; ?>
<?php include '../../include/topbar.php'; ?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Scholarship Applications</h1>

    <!-- Display Slot Information -->
    <div class="alert alert-info">
        <strong>Interview Slots:</strong> <?= $availableSlots ?> / <?= $totalSlots ?> available.
    </div>

    <!-- Responsive Table -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped custom-table">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Application Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $application) : ?>
                    <tr>
                        <td><?= htmlspecialchars($application['name']); ?></td>
                        <td><?= htmlspecialchars($application['email']); ?></td>
                        <td><?= htmlspecialchars($application['contact_number']); ?></td>
                        <td><?= ucfirst($application['scholarship_status']); ?></td>
                        <td><?= htmlspecialchars($application['application_date']); ?></td>
                        <td>
                            <div class="d-flex flex-wrap gap-1 justify-content-center">
                                <button class="btn btn-info btn-sm" onclick="viewApplication(<?= $application['id']; ?>)">View</button>
                                <?php if ($application['scholarship_status'] === 'applied') : ?>
                                    <button class="btn btn-success btn-sm" onclick="updateStatus(<?= $application['id']; ?>, 'approved')">Approve</button>
                                    <button class="btn btn-danger btn-sm" onclick="updateStatus(<?= $application['id']; ?>, 'rejected')">Reject</button>
                                    <?php if ($availableSlots > 0): ?>
                                        <button class="btn btn-primary btn-sm" onclick="openScheduleModal(<?= $application['id']; ?>)">Schedule Interview</button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>No Slots Available</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Viewing Application Details -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Name:</strong> <span id="viewName"></span></p>
                <p><strong>Email:</strong> <span id="viewEmail"></span></p>
                <p><strong>Contact Number:</strong> <span id="viewContact"></span></p>
                <p><strong>Status:</strong> <span id="viewStatus"></span></p>
                <p><strong>Application Date:</strong> <span id="viewDate"></span></p>
                <p>
                    <strong>Uploaded Document:</strong>
                    <a id="viewDocument" href="#" target="_blank">View Document</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Schedule Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" id="scheduleUserId" name="user_id">
                    <div class="mb-3">
                        <label for="scheduleDate" class="form-label">Select Date and Time</label>
                        <input type="datetime-local" id="scheduleDate" name="date" class="form-control" required>
                    </div>
                    <button type="button" class="btn btn-primary w-100" onclick="submitScheduleForm()">Schedule</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function updateStatus(userId, status) {
        if (!confirm(`Are you sure you want to mark this application as ${status}?`)) return;

        $.post('../../../backend/admin/e_community_scholarship_and_educational_opportunities/update_status.php', {
                user_id: userId,
                status: status
            })
            .done(function(response) {
                const data = JSON.parse(response);
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .fail(function() {
                alert('Failed to update status.');
            });
    }

    function openScheduleModal(userId) {
        document.getElementById('scheduleUserId').value = userId;
        $('#scheduleModal').modal('show');
    }

    function submitScheduleForm() {
        const dateInput = document.getElementById('scheduleDate');
        const localDate = new Date(dateInput.value);

        const utcDate = new Date(localDate.getTime() - localDate.getTimezoneOffset() * 60000)
            .toISOString()
            .slice(0, 19)
            .replace('T', ' ');

        const formData = {
            user_id: document.getElementById('scheduleUserId').value,
            date: utcDate,
        };

        $.post('../../../backend/admin/e_community_scholarship_and_educational_opportunities/schedule_interview.php', formData)
            .done(function(response) {
                const data = JSON.parse(response);
                alert(data.message);
                if (data.success) {
                    $('#scheduleModal').modal('hide');
                    location.reload();
                }
            })
            .fail(function() {
                alert('Failed to schedule interview.');
            });
    }

    function viewApplication(userId) {
        $.get('../../../backend/admin/e_community_scholarship_and_educational_opportunities/view_application.php', {
                id: userId
            })
            .done(function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    $('#viewName').text(data.application.name);
                    $('#viewEmail').text(data.application.email);
                    $('#viewContact').text(data.application.contact_number);
                    $('#viewStatus').text(data.application.scholarship_status);
                    $('#viewDate').text(data.application.application_date);
                    $('#viewDocument').attr('href', data.application.document_path);
                    $('#viewModal').modal('show');
                } else {
                    alert(data.message);
                }
            })
            .fail(function() {
                alert('Error fetching application details.');
            });
    }
</script>