<section id="topbar">
    <!--topbar-->
    <nav>
        <i class='bx bx-menu toggle-sidebar'></i>
        <form action="#">
            <div class="form-group">
                <input type="text" placeholder="Search...">
                <i class='bx bx-search-alt icon'></i>
            </div>
        </form>
        <span class="divider"></span>
        <div class="profile">
            <?php
            // Fetch the current user's role and profile picture dynamically
            $userId = $_SESSION['user_id'];
            $role = $_SESSION['role']; // Ensure 'role' is set in the session
            try {
                $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = :user_id");
                $stmt->execute(['user_id' => $userId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Determine the profile picture path or use the default one
                $profilePicturePath = !empty($result['profile_picture']) && file_exists("../../" . $result['profile_picture'])
                    ? "../../" . htmlspecialchars($result['profile_picture'])
                    : "../../assets/img/profile.png"; // Default picture if none uploaded
            } catch (PDOException $e) {
                error_log("Error fetching profile picture: " . $e->getMessage());
                $profilePicturePath = "../../assets/img/profile.png"; // Fallback to default
            }
            ?>
            <img src="<?php echo $profilePicturePath; ?>?t=<?php echo time(); ?>" alt="Profile Picture" class="profile-picture">
            <ul class="profile-link">
                <?php if ($role === 'admin') : ?>
                    <!-- Admin-specific navbar links -->
                    <li><a href="/pages/admin/admin_settings.php"><i class='bx bxs-user-circle icon'></i>Profile Setting</a></li>
                    <li><a href="../../logout.php"><i class='bx bx-log-out icon'></i>Logout</a></li>
                <?php else : ?>
                    <!-- User-specific navbar links -->
                    <li><a href="/pages/user/user_settings.php"><i class='bx bxs-user-circle icon'></i>Profile Setting</a></li>
                    <li><a href="../../logout.php"><i class='bx bx-log-out icon'></i>Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <!--end topbar-->
    <!--end navbar-->