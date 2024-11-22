<?php
session_start();


// Redirect non-admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Handle Delete User
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $deleteId]);
        $successMessage = "User deleted successfully!";
    } catch (PDOException $e) {
        $errorMessage = "Error deleting user: " . $e->getMessage();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    try {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
        ]);
        $successMessage = "User added successfully!";
    } catch (PDOException $e) {
        $errorMessage = "Error adding user: " . $e->getMessage();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $editUserId = intval($_POST['edit_user_id']);
    $editName = htmlspecialchars($_POST['edit_name']);
    $editEmail = htmlspecialchars($_POST['edit_email']);
    $editRole = $_POST['edit_role'];

    try {
        $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id");
        $stmt->execute([
            'name' => $editName,
            'email' => $editEmail,
            'role' => $editRole,
            'id' => $editUserId,
        ]);
        $successMessage = "User updated successfully!";
    } catch (PDOException $e) {
        $errorMessage = "Error updating user: " . $e->getMessage();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch Users
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
$query = "SELECT * FROM users WHERE name LIKE :search OR email LIKE :search OR role LIKE :search ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute(['search' => "%$search%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../include/header.php';
include '../../include/sidebar.php';
include '../../include/topbar.php';
?>

<div id="page-content-wrapper">
    <div class="container mt-4">
        <h1 class="mb-4">User Management</h1>

        <!-- Success/Error Messages -->
        <?php if (isset($successMessage)) : ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php elseif (isset($errorMessage)) : ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <!-- Search Bar -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo $search; ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        <!-- Add User Modal -->
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>

        <!-- Users Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover table-bordered table-striped custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users) : ?>
                            <?php foreach ($users as $user) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                    <td>
                                        <div class="d-flex flex-column flex-md-row gap-2">
                                            <button class="btn btn-warning btn-sm w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                                Edit
                                            </button>
                                            <a href="?delete_id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure?');" class="btn btn-danger btn-sm w-100 w-md-auto">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit User Modal -->
                                <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editUserModalLabel<?php echo $user['id']; ?>">Edit User</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="edit_user_id" value="<?php echo $user['id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="name_<?php echo $user['id']; ?>" class="form-label">Name</label>
                                                        <input type="text" id="name_<?php echo $user['id']; ?>" name="edit_name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="email_<?php echo $user['id']; ?>" class="form-label">Email</label>
                                                        <input type="email" id="email_<?php echo $user['id']; ?>" name="edit_email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="role_<?php echo $user['id']; ?>" class="form-label">Role</label>
                                                        <select id="role_<?php echo $user['id']; ?>" name="edit_role" class="form-select">
                                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" name="update_user" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>