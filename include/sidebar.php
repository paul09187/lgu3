<?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <!-- Admin Sidebar -->
    <section id="sidebar">
        <a href="/pages/admin/dashboard.php" class="logo">
            <img src="../../assets/img/Unified-LGU-3-LOGO-preview.png" alt="LGU Logo">
            <span>LGU: 3</span>
        </a>
        <ul class="side-menu">
            <li class="divider" data-text="main">Main</li>
            <li><a href="/pages/admin/dashboard.php" class="active"><i class='bx bxs-dashboard icon'></i>Dashboard</a></li>

            <li class="divider" data-text="emergency">Emergency Coordinator</li>
            <li><a href="/pages/admin/adaptive_emergency_coordinator.php"><i class='bx bxs-bell icon'></i>Emergency System</a></li>

            <li class="divider" data-text="scholarship">Scholarship Management</li>
            <li><a href="/pages/admin/e_community_scholarship_and_educational_opportunities.php"><i class='bx bxs-edit icon'></i>Application System</a></li>


            <li class="divider" data-text="Child & Youth Services">Child & Youth Services</li>
            <li><a href="/pages/admin/child_youth_services_case.php"><i class='bx bxs-user-plus icon'></i>Cases</a></li>


            <li class="divider" data-text="User Management">User Management</li>
            <li><a href="/pages/admin/user_management.php"><i class='bx bxs-user icon'></i>Users</a></li>

            <li class="divider" data-text="Chats">Chats</li>
            <li><a href="/pages/chat_system/admin_chats.php"><i class='bx bxs-chat icon'></i>Messages</a></li>
        </ul>
        <div class="ads">
            <div class="wrapper">
                <a href="#" class="btn-upgrade">INFO</a>
                <p>Local Government Unit (LGU) tasks include maintaining order and enhancing community life. When citizens vote, they empower officials to achieve municipal goals effectively.</p>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- User Sidebar -->
    <section id="sidebar">
        <a href="/pages/user/dashboard.php" class="logo">
            <img src="../../assets/img/Unified-LGU-3-LOGO-preview.png" alt="LGU Logo">
            <span>LGU: 3</span>
        </a>
        <ul class="side-menu">
            <li class="divider" data-text="main">Main</li>
            <li><a href="/pages/user/dashboard.php" class="active"><i class='bx bxs-dashboard icon'></i>Dashboard</a></li>

            <li class="divider" data-text="services">Services</li>
            <li><a href="/pages/user/adaptive_emergency_coordinator.php"><i class='bx bxs-bell icon'></i></i>Emergency System</a></li>
            <li><a href="/pages/user/e_community_scholarship_and_educational_opportunities.php"><i class='bx bxs-edit icon'></i>Apply for Scholarship</a></li>
            <li><a href="/pages/user/child_youth_services_case.php"><i class='bx bxs-book-content icon'></i>Cases</a></li>

            <li class="divider" data-text="Chats">Chats</li>
            <li><a href="/pages/chat_system/user_chats.php"><i class='bx bxs-chat icon'></i>Messages</a></li>
        </ul>
        <div class="ads">
            <div class="wrapper">
                <a href="#" class="btn-upgrade">INFO</a>
                <p>Local Government Unit (LGU) tasks include maintaining order and enhancing community life. When citizens vote, they empower officials to achieve municipal goals effectively.</p>
            </div>
        </div>
    </section>
<?php endif; ?>