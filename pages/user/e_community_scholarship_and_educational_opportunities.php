<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Fetch user's scholarship application data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.scholarship_status, u.application_date, i.interview_date, u.document_path
    FROM users u
    LEFT JOIN interview_schedule i ON u.id = i.user_id
    WHERE u.id = :userId
");
$stmt->execute(['userId' => $userId]);
$scholarshipData = $stmt->fetch(PDO::FETCH_ASSOC);

$status = ucfirst($scholarshipData['scholarship_status']);
?>

<?php include '../../include/header.php'; ?>
<?php include '../../include/sidebar.php'; ?>
<?php include '../../include/topbar.php'; ?>

<div class="container mt-4">
    <h1 class="text-center mb-4">E-Community Scholarship</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-3">Application Status: <?= htmlspecialchars($status); ?></h3>

            <?php if ($status === 'Not_applied' || $status === 'Rejected'): ?>
                <p><?= $status === 'Rejected' ? "Your previous application was not approved. You may reapply below." : "You have not applied for the scholarship yet. Submit your application below."; ?></p>

                <!-- Scholarship Application Form -->
                <form id="scholarshipForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Enter your full name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" id="contact_number" name="contact_number" class="form-control" placeholder="Enter your contact number" required>
                    </div>

                    <div class="mb-3">
                        <label for="documents" class="form-label">Upload Supporting Documents</label>
                        <input type="file" id="documents" name="documents" class="form-control" required>
                        <small class="text-muted">Accepted formats: PDF, DOCX, JPG, PNG</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                </form>
            <?php elseif ($status === 'Interview_scheduled'): ?>
                <p>Your interview is scheduled on: <strong><?= htmlspecialchars($scholarshipData['interview_date']); ?></strong></p>
            <?php elseif ($status === 'Approved'): ?>
                <p class="text-success">Congratulations! Your scholarship application has been approved.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Handle Scholarship Form Submission
    $('#scholarshipForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: '../../../backend/user/e_community_scholarship_and_educational_opportunities/scholarship.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                } catch (error) {
                    alert('An error occurred while processing your request.');
                }
            },
            error: function() {
                alert('An unexpected error occurred.');
            }
        });
    });
</script>