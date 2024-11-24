<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}
require '../../database/connection.php';

// Function to log audit actions
function logAudit($userId, $action, $details = null)
{
    global $conn; // Use the existing database connection
    $stmt = $conn->prepare("
        INSERT INTO audit_logs (user_id, action, details) 
        VALUES (:user_id, :action, :details)
    ");
    $stmt->execute([
        'user_id' => $userId,
        'action' => $action,
        'details' => $details
    ]);
}
?>

<?php include '../../include/header.php'; ?>
<?php include '../../include/sidebar.php'; ?>
<?php include '../../include/topbar.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Case Management</h1>
    <button class="btn btn-primary mb-3" onclick="openCaseModal()">Add New Case</button>

    <!-- Responsive Table -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped custom-table">
            <thead>
                <tr>
                    <th>Case Title</th>
                    <th>Case Type</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="caseTableBody">
                <!-- Data will be populated dynamically -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add Case Modal -->
<div class="modal fade" id="caseModal" tabindex="-1" aria-labelledby="caseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="caseModalLabel">Add New Case</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="caseForm">
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
                    <div class="mb-3">
                        <label for="guardianName" class="form-label">Guardian Name</label>
                        <input type="text" id="guardianName" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="guardianContact" class="form-label">Guardian Contact</label>
                        <input type="text" id="guardianContact" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" class="form-control"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="submitCaseForm()">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Audit Logs Section -->
<div class="container mt-5">
    <h1 class="mb-4">Audit Logs</h1>
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped custom-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody id="auditLogsTableBody">
                <!-- Data will be populated dynamically -->
            </tbody>
        </table>
    </div>
</div>

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
                <p><strong>Guardian Name:</strong> <span id="guardianNameDetail"></span></p>
                <p><strong>Guardian Contact:</strong> <span id="guardianContactDetail"></span></p>
                <p><strong>Status:</strong> <span id="caseStatusDetail"></span></p>
                <p><strong>Notes:</strong> <span id="caseNotesDetail"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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
                    <div class="mb-3">
                        <label for="editGuardianName" class="form-label">Guardian Name</label>
                        <input type="text" id="editGuardianName" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="editGuardianContact" class="form-label">Guardian Contact</label>
                        <input type="text" id="editGuardianContact" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="editCaseStatus" class="form-label">Status</label>
                        <select id="editCaseStatus" class="form-select">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editCaseNotes" class="form-label">Notes</label>
                        <textarea id="editCaseNotes" class="form-control"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="submitEditCaseForm()">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>



<?php include '../../include/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function fetchCases() {
        $.get('../../../backend/admin/child_youth_services_case/case_management.php')
            .done(response => {
                console.log("Case Management Response:", response); // Log the response for debugging
                const data = JSON.parse(response);
                const tableBody = $('#caseTableBody');
                tableBody.empty();

                if (data.success && data.cases.length > 0) {
                    data.cases.forEach(c => {
                        const row = `
                        <tr>
                            <td>${c.case_title}</td>
                            <td>${c.case_type}</td>
                            <td>${c.status}</td>
                            <td>${c.created_at}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-info btn-sm" onclick="viewCase(${c.id})">View</button>
                                    <button class="btn btn-warning btn-sm" onclick="editCase(${c.id})">Edit</button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteCase(${c.id})">Delete</button>
                                    ${c.status !== 'closed' ? `
                                        <button class="btn btn-primary btn-sm" onclick="updateCaseStatus(${c.id}, 'in_progress')">In Progress</button>
                                        <button class="btn btn-success btn-sm" onclick="updateCaseStatus(${c.id}, 'closed')">Close</button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>`;
                        tableBody.append(row);
                    });
                } else {
                    tableBody.append('<tr><td colspan="5">No cases found.</td></tr>');
                }
            })
            .fail(() => alert('Failed to fetch cases.'));
    }


    function fetchAuditLogs() {
        $.get('../../../backend/admin/child_youth_services_case/fetch_audit_logs.php')
            .done(response => {
                const data = JSON.parse(response);
                const tableBody = $('#auditLogsTableBody');
                tableBody.empty();

                if (data.success) {
                    data.logs.forEach(log => {
                        tableBody.append(`
                    <tr>
                        <td>${log.user_name}</td>
                        <td>${log.action}</td>
                        <td>${log.created_at}</td>
                    </tr>
                `);
                    });
                } else {
                    tableBody.append('<tr><td colspan="3">No logs available.</td></tr>');
                }
            })
            .fail(() => alert('Failed to fetch audit logs.'));
    }




    $(document).ready(() => {
        fetchCases();
        fetchAuditLogs();
    });


    // Open the Add Case Modal
    function openCaseModal() {
        $('#caseModal').modal('show');
    }

    function submitCaseForm() {
        const caseForm = {
            case_title: $('#caseTitle').val(),
            case_type: $('#caseType').val(),
            guardian_name: $('#guardianName').val(),
            guardian_contact: $('#guardianContact').val(),
            notes: $('#notes').val(),
        };

        $.post('../../../backend/admin/child_youth_services_case/add_case.php', caseForm)
            .done(response => {
                console.log(response); // Debug the response
                const data = JSON.parse(response);
                alert(data.message);
                if (data.success) {
                    $('#caseModal').modal('hide');
                    fetchCases(); // Refresh the table after adding a case
                }
            })
            .fail(() => alert('Failed to add case.'));
    }


    // View case details
    function viewCase(caseId) {
        $.get('../../../backend/admin/child_youth_services_case/view_case.php', {
                id: caseId
            })
            .done(response => {
                const data = JSON.parse(response);
                if (data.success) {
                    $('#caseTitleDetail').text(data.case.case_title);
                    $('#caseTypeDetail').text(data.case.case_type);
                    $('#guardianNameDetail').text(data.case.guardian_name || 'N/A');
                    $('#guardianContactDetail').text(data.case.guardian_contact || 'N/A');
                    $('#caseStatusDetail').text(data.case.status);
                    $('#caseNotesDetail').text(data.case.notes || 'No notes provided.');

                    $('#viewCaseModal').modal('show');
                } else {
                    alert(data.message);
                }
            })
            .fail(() => alert('Failed to fetch case details.'));
    }

    // Edit case
    function editCase(caseId) {
        $.get('../../../backend/admin/child_youth_services_case/view_case.php', {
                id: caseId
            })
            .done(response => {
                const data = JSON.parse(response);
                if (data.success) {
                    $('#editCaseId').val(data.case.id);
                    $('#editCaseTitle').val(data.case.case_title);
                    $('#editCaseType').val(data.case.case_type);
                    $('#editGuardianName').val(data.case.guardian_name || '');
                    $('#editGuardianContact').val(data.case.guardian_contact || '');
                    $('#editCaseStatus').val(data.case.status);
                    $('#editCaseNotes').val(data.case.notes || '');

                    $('#editCaseModal').modal('show');
                } else {
                    alert(data.message);
                }
            })
            .fail(() => alert('Failed to fetch case details.'));
    }

    // Submit edits to a case
    function submitEditCaseForm() {
    const caseForm = {
        id: $('#editCaseId').val(),
        case_title: $('#editCaseTitle').val(),
        case_type: $('#editCaseType').val(),
        guardian_name: $('#editGuardianName').val(),
        guardian_contact: $('#editGuardianContact').val(),
        notes: $('#editCaseNotes').val(),
        status: $('#editCaseStatus').val(),
    };

    $.post('../../../backend/admin/child_youth_services_case/edit_case.php', caseForm)
        .done(response => {
            console.log("Edit Case Response:", response); // Log response
            const data = JSON.parse(response);
            alert(data.message);
            if (data.success) {
                $('#editCaseModal').modal('hide');
                fetchCases();
            }
        })
        .fail(error => {
            console.error("Edit Case Error:", error);
            alert('Failed to update case.');
        });
    }

    // Delete case
    function deleteCase(caseId) {
        if (!confirm('Are you sure you want to delete this case?')) return;
    
        $.post('../../../backend/admin/child_youth_services_case/delete_case.php', { id: caseId })
            .done(response => {
                console.log("Raw Response:", response); // Log raw response
    
                try {
                    const data = JSON.parse(response); // Parse JSON response
                    console.log("Parsed Response:", data);
    
                    if (data.success) {
                        alert(data.message);
                        fetchCases(); // Refresh the case list
                    } else {
                        alert(data.message || 'Failed to delete case.');
                    }
                } catch (error) {
                    console.error("JSON Parse Error:", error);
                    alert('Unexpected server response. Failed to delete case.');
                }
            })
            .fail(error => {
                console.error("AJAX Error:", error); // Log any AJAX errors
                alert('Failed to delete case.');
            });
    }




    // Update case status
    function updateCaseStatus(caseId, status) {
        if (!confirm(`Are you sure you want to update the status to '${status}'?`)) return;

        $.post('../../../backend/admin/child_youth_services_case/update_case_status.php', {
                id: caseId,
                status
            })
            .done(response => {
                const data = JSON.parse(response);
                alert(data.message);
                if (data.success) {
                    fetchCases();
                }
            })
            .fail(() => alert('Failed to update case status.'));
    }

    $(document).ready(() => {
        fetchCases();
        fetchAuditLogs();
    });
</script>