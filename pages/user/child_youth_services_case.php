<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}
require '../../database/connection.php';
?>

<?php include '../../include/header.php'; ?>
<?php include '../../include/sidebar.php'; ?>
<?php include '../../include/topbar.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">My Cases</h1>
    <button class="btn btn-primary mb-3" onclick="openCaseModal()">Submit New Case</button>

    <!-- Responsive Table -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped custom-table">
            <thead>
                <tr>
                    <th>Case Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userCaseTable">
                <!-- Cases will be populated dynamically -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add Case Modal -->
<div class="modal fade" id="caseModal" tabindex="-1" aria-labelledby="caseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="caseModalLabel">Submit New Case</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="submitCaseForm">
                    <div class="mb-3">
                        <label for="caseTitle" class="form-label">Case Title</label>
                        <input type="text" id="caseTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="caseType" class="form-label">Case Type</label>
                        <select id="caseType" class="form-select">
                            <option value="abuse">Abuse</option>
                            <option value="neglect">Neglect</option>
                            <option value="support">Support</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <!-- ID Upload Field -->
                    <div class="mb-3">
                        <label for="userIdFile" class="form-label">Upload ID (JPEG/PNG)</label>
                        <input type="file" id="userIdFile" class="form-control" accept="image/jpeg,image/png" required>
                    </div>

                    <!-- Age Input Field -->
                    <div class="mb-3">
                        <label for="userAge" class="form-label">Your Age</label>
                        <input type="number" id="userAge" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Details</label>
                        <textarea id="notes" class="form-control"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="submitCase()">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View case modal -->
<div class="modal fade" id="viewCaseModal" tabindex="-1" aria-labelledby="viewCaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCaseModalLabel">View Case Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Title:</strong> <span id="caseTitleDetail"></span></p>
                <p><strong>Type:</strong> <span id="caseTypeDetail"></span></p>
                <p><strong>Status:</strong> <span id="caseStatusDetail"></span></p>
                <p><strong>ID File:</strong> <a id="caseIdFileLink" href="#" target="_blank">View Uploaded ID</a></p>
                <p><strong>Age:</strong> <span id="caseAgeDetail"></span></p>
                <p><strong>Details:</strong> <span id="caseNotesDetail"></span></p>
                <p><strong>Submitted At:</strong> <span id="caseSubmittedAt"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit case modal -->
<div class="modal fade" id="editCaseModal" tabindex="-1" aria-labelledby="editCaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCaseModalLabel">Edit Case</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCaseForm">
                    <input type="hidden" id="editCaseId">
                    <div class="mb-3">
                        <label for="editCaseTitle" class="form-label">Case Title</label>
                        <input type="text" id="editCaseTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCaseType" class="form-label">Case Type</label>
                        <select id="editCaseType" class="form-select">
                            <option value="abuse">Abuse</option>
                            <option value="neglect">Neglect</option>
                            <option value="support">Support</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <!-- Edit ID Upload Field -->
                    <div class="mb-3">
                        <label for="editUserIdFile" class="form-label">Update ID (JPEG/PNG)</label>
                        <input type="file" id="editUserIdFile" class="form-control" accept="image/jpeg,image/png">
                        <small class="text-muted">Leave empty if you do not want to update the ID file.</small>
                    </div>

                    <!-- Edit Age Input Field -->
                    <div class="mb-3">
                        <label for="editUserAge" class="form-label">Update Age</label>
                        <input type="number" id="editUserAge" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="editNotes" class="form-label">Details</label>
                        <textarea id="editNotes" class="form-control"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="submitEditCase()">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>



<?php include '../../include/footer.php'; ?>

<script>
    function fetchUserCases() {
        $.get('../../../backend/user/child_youth_services_case/fetch_user_cases.php')
            .done(response => {
                console.log("Response from fetchUserCases:", response); // Debugging response
                const data = JSON.parse(response);
                const tableBody = $('#userCaseTable');
                tableBody.empty();

                if (data.success && data.cases.length > 0) {
                    data.cases.forEach(caseData => {
                        tableBody.append(`
                        <tr>
                            <td>${caseData.case_title}</td>
                            <td>${caseData.case_type}</td>
                            <td>${caseData.status}</td>
                            <td>${caseData.created_at}</td>
                            <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-info btn-sm" onclick="viewCase(${caseData.id})">View</button>
                                <button class="btn btn-warning btn-sm" onclick="editCase(${caseData.id})">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteCase(${caseData.id})">Delete</button>
                            </div>
                            </td>
                        </tr>
                    `);
                    });
                } else {
                    tableBody.append('<tr><td colspan="5">No cases found.</td></tr>');
                }
            })
            .fail(err => {
                console.error("Failed to fetch user cases:", err); // Debugging errors
                alert('Failed to fetch cases.');
            });
    }

    function viewCase(caseId) {
        $.get(`../../../backend/user/child_youth_services_case/view_case.php`, {
                id: caseId
            })
            .done(response => {
                console.log("View case response:", response);
                const data = JSON.parse(response);
                if (data.success) {
                    $('#caseTitleDetail').text(data.case.case_title);
                    $('#caseTypeDetail').text(data.case.case_type);
                    $('#caseStatusDetail').text(data.case.status);
                    $('#caseNotesDetail').text(data.case.notes || 'No details provided.');
                    $('#caseSubmittedAt').text(data.case.created_at);
                    $('#caseAgeDetail').text(data.case.user_age || 'N/A');
                    $('#caseIdFileLink').attr('href', '../../../uploads/user_ids/' + data.case.user_id_file).text("View ID File");
                    $('#viewCaseModal').modal('show');
                } else {
                    alert(data.message);
                }
            })
            .fail(err => {
                console.error("Failed to fetch case details:", err);
                alert('Failed to fetch case details.');
            });
    }

    function editCase(caseId) {
        $.get(`../../../backend/user/child_youth_services_case/view_case.php`, {
                id: caseId
            })
            .done(response => {
                console.log("Edit case response:", response);
                const data = JSON.parse(response);
                if (data.success) {
                    $('#editCaseId').val(data.case.id);
                    $('#editCaseTitle').val(data.case.case_title);
                    $('#editCaseType').val(data.case.case_type);
                    $('#editNotes').val(data.case.notes || '');
                    $('#editUserAge').val(data.case.user_age || ''); // Populate age
                    $('#editCaseModal').modal('show');
                } else {
                    alert(data.message);
                }
            })
            .fail(err => {
                console.error("Failed to fetch case details for editing:", err);
                alert('Failed to fetch case details.');
            });
    }

    function submitEditCase() {
        const formData = new FormData();
        formData.append('id', $('#editCaseId').val());
        formData.append('case_title', $('#editCaseTitle').val());
        formData.append('case_type', $('#editCaseType').val());
        formData.append('notes', $('#editNotes').val());
        formData.append('user_age', $('#editUserAge').val());
        if ($('#editUserIdFile')[0].files.length > 0) {
            formData.append('user_id_file', $('#editUserIdFile')[0].files[0]); // Include file if selected
        }

        $.ajax({
            url: '../../../backend/user/child_youth_services_case/edit_case.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                console.log("Edit case submit response:", response);
                const data = JSON.parse(response);
                alert(data.message);
                if (data.success) {
                    $('#editCaseModal').modal('hide');
                    fetchUserCases();
                }
            },
            error: (err) => {
                console.error("Failed to edit case:", err);
                alert('Failed to edit case.');
            },
        });
    }

    function deleteCase(caseId) {
        if (!confirm('Are you sure you want to delete this case?')) return;

        $.post(`../../../backend/user/child_youth_services_case/delete_case.php`, {
                id: caseId
            })
            .done(response => {
                console.log("Delete case response:", response);
                const data = JSON.parse(response);
                alert(data.message);
                if (data.success) {
                    fetchUserCases();
                }
            })
            .fail(err => {
                console.error("Failed to delete case:", err);
                alert('Failed to delete case.');
            });
    }

    function openCaseModal() {
        $('#caseModal').modal('show');
    }

    function submitCase() {
        const formData = new FormData();
        formData.append('case_title', $('#caseTitle').val());
        formData.append('case_type', $('#caseType').val());
        formData.append('notes', $('#notes').val());
        formData.append('user_age', $('#userAge').val());
        formData.append('user_id_file', $('#userIdFile')[0].files[0]);

        $.ajax({
            url: '../../../backend/user/child_youth_services_case/submit_user_case.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                console.log("Submit case response:", response);
                const data = JSON.parse(response);
                alert(data.message);
                if (data.success) {
                    $('#caseModal').modal('hide');
                    fetchUserCases();
                }
            },
            error: (err) => {
                console.error("Failed to submit case:", err);
                alert('Failed to submit case.');
            },
        });
    }

    // Initial data fetch on page load
    $(document).ready(() => {
        fetchUserCases();
    });
</script>