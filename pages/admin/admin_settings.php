<?php
session_start();

// Redirect non-admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Default profile picture path
$defaultProfilePicture = 'uploads/profile_pictures/default.png';

// Fetch admin data
$userId = $_SESSION['user_id'];
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id AND role = 'admin'");
    $stmt->execute(['user_id' => $userId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($admin['profile_picture']) || !file_exists('../../' . $admin['profile_picture'])) {
        $admin['profile_picture'] = $defaultProfilePicture; // Assign default profile picture
    }
} catch (PDOException $e) {
    error_log("Error fetching admin details: " . $e->getMessage());
    $admin = null;
}

// Update profile and handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateProfile'])) {
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $email = htmlspecialchars(strip_tags($_POST['email']));
    $contact = htmlspecialchars(strip_tags($_POST['contact_number']));
    $profilePicture = $admin['profile_picture']; // Default to existing picture

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if not exists
        }

        $fileName = uniqid('profile_', true) . '.' . strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $filePath = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($_FILES['profile_picture']['size'] <= 2 * 1024 * 1024 && in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            $fileInfo = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($fileInfo !== false) {
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filePath)) {
                    // Delete old picture if new upload is successful
                    if (!empty($admin['profile_picture']) && $admin['profile_picture'] !== $defaultProfilePicture && file_exists('../../' . $admin['profile_picture'])) {
                        unlink('../../' . $admin['profile_picture']);
                    }
                    $profilePicture = 'uploads/profile_pictures/' . $fileName;
                } else {
                    $updateError = "Failed to upload profile picture.";
                }
            } else {
                $updateError = "Uploaded file is not a valid image.";
            }
        } else {
            $updateError = "Invalid file type or size. Only JPG/PNG files under 2MB are allowed.";
        }
    }

    // Update profile in the database
    if (!isset($updateError)) {
        try {
            $stmt = $conn->prepare("
                UPDATE users 
                SET name = :name, email = :email, contact_number = :contact_number, profile_picture = :profile_picture
                WHERE id = :user_id AND role = 'admin'
            ");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'contact_number' => $contact,
                'profile_picture' => $profilePicture,
                'user_id' => $userId,
            ]);
            $_SESSION['name'] = $name;

            // Reload the page to display updated information
            header("Location: " . $_SERVER['PHP_SELF']);
            exit; // Stop further script execution after the redirect
        } catch (PDOException $e) {
            error_log("Error updating admin profile: " . $e->getMessage());
            $updateError = "An error occurred while updating your profile.";
        }
    }
}


// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePassword'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Verify current password
    if (password_verify($currentPassword, $admin['password'])) {
        if ($newPassword === $confirmPassword) {
            if (strlen($newPassword) >= 8) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                try {
                    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id AND role = 'admin'");
                    $stmt->execute(['password' => $hashedPassword, 'user_id' => $userId]);
                    $passwordSuccess = "Password changed successfully!";
                } catch (PDOException $e) {
                    error_log("Error updating password: " . $e->getMessage());
                    $passwordError = "An error occurred while changing your password.";
                }
            } else {
                $passwordError = "Password must be at least 8 characters long.";
            }
        } else {
            $passwordError = "New password and confirmation do not match.";
        }
    } else {
        $passwordError = "Current password is incorrect.";
    }
}

include '../../include/header.php';
include '../../include/sidebar.php';
include '../../include/topbar.php';
?>

<!-- HTML Form Remains Same -->

<style>
    .profile-picture-box {
        width: 120px;
        /* Box width */
        height: 120px;
        /* Box height */
        border: 1px solid #ddd;
        padding: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f9f9f9;
        border-radius: 50%;
        /* Circular box */
        overflow: hidden;
        /* Ensure the image doesn't overflow */
    }

    .profile-picture {
        width: 100%;
        /* Fit within the box */
        height: 100%;
        /* Fit within the box */
        object-fit: cover;
        /* Crop the image neatly */
    }
</style>

<div id="page-content-wrapper">
    <div class="container mt-4">
        <h1 class="mb-4">Admin Profile & Settings</h1>

        <!-- Success/Error Messages -->
        <?php if (isset($updateSuccess)) : ?>
            <div class="alert alert-success"><?php echo $updateSuccess; ?></div>
        <?php elseif (isset($updateError)) : ?>
            <div class="alert alert-danger"><?php echo $updateError; ?></div>
        <?php endif; ?>

        <!-- Profile Update Form -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5>Profile Information</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($admin['contact_number']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Profile Picture</label><br>
                        <div class="profile-picture-box">
                            <img src="../../<?php echo file_exists('../../' . $admin['profile_picture']) ? htmlspecialchars($admin['profile_picture']) : $defaultProfilePicture; ?>?t=<?php echo time(); ?>"
                                alt="Profile Picture"
                                class="profile-picture">
                        </div>
                        <input type="file" id="profile_picture" name="profile_picture" class="form-control mt-2">
                    </div>
                    <button type="submit" name="updateProfile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Password Change Form -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h5>Change Password</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" name="changePassword" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>