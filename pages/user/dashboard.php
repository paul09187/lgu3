<?php
session_start();
// Redirect non-logged-in users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Set the page title
$title = "User Dashboard";

// Include common header, sidebar, and topbar
include '../../include/header.php';
include '../../include/sidebar.php';
include '../../include/topbar.php';

// Fetch user's data
$userId = $_SESSION['user_id'];

// Function to fetch counts or specific data with PDO
function fetchCount($conn, $query, $params = [])
{
    try {
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['count'] ?? 0;
    } catch (Exception $e) {
        error_log("Error fetching data: " . $e->getMessage());
        return 0;
    }
}

// Fetch data for dashboard metrics
$totalCases = fetchCount($conn, "SELECT COUNT(*) AS count FROM cases WHERE created_by = :user_id", ['user_id' => $userId]);
$openCases = fetchCount($conn, "SELECT COUNT(*) AS count FROM cases WHERE created_by = :user_id AND status = 'open'", ['user_id' => $userId]);
$resolvedCases = fetchCount($conn, "SELECT COUNT(*) AS count FROM cases WHERE created_by = :user_id AND status = 'resolved'", ['user_id' => $userId]);
$inProgressCases = fetchCount($conn, "SELECT COUNT(*) AS count FROM cases WHERE created_by = :user_id AND status = 'in_progress'", ['user_id' => $userId]);
?>

<!-- HTML Content -->
<div id="page-content-wrapper">
    <div class="container mt-4">
        <h1 class="mb-4">Welcome to Your Dashboard, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>

        <!-- Dashboard Cards -->
        <div class="row">
            <!-- Total Cases -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-info text-center shadow-sm">
                    <div class="card-body">
                        <h5>Total Cases</h5>
                        <h3 class="card-title"><?php echo htmlspecialchars($totalCases); ?></h3>
                    </div>
                </div>
            </div>

            <!-- Open Cases -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-info text-center shadow-sm">
                    <div class="card-body">
                        <h5>Open Cases</h5>
                        <h3 class="card-title"><?php echo htmlspecialchars($openCases); ?></h3>
                    </div>
                </div>
            </div>

            <!-- In Progress Cases -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-info text-center shadow-sm">
                    <div class="card-body">
                        <h5>In Progress Cases</h5>
                        <h3 class="card-title"><?php echo htmlspecialchars($inProgressCases); ?></h3>
                    </div>
                </div>
            </div>

            <!-- Resolved Cases -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-info text-center shadow-sm">
                    <div class="card-body">
                        <h5>Resolved Cases</h5>
                        <h3 class="card-title"><?php echo htmlspecialchars($resolvedCases); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Cases -->
        <h3 class="mt-4">Your Recent Cases</h3>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Case Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $conn->prepare("SELECT case_title, case_type, status, created_at FROM cases WHERE created_by = :user_id ORDER BY created_at DESC LIMIT 5");
                            $stmt->execute(['user_id' => $userId]);
                            $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if ($cases) {
                                foreach ($cases as $case) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($case['case_title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($case['case_type']) . "</td>";
                                    echo "<td>" . htmlspecialchars($case['status']) . "</td>";
                                    echo "<td>" . htmlspecialchars($case['created_at']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No cases found.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            error_log("Error fetching recent cases: " . $e->getMessage());
                            echo "<tr><td colspan='4'>An error occurred.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alerts Section -->
        <h3 class="mt-4">Emergency Alerts</h3>
        <div class="card shadow-sm">
            <div class="card-body">
                <?php
                try {
                    $stmt = $conn->prepare("SELECT title, message, created_at FROM emergency_alerts ORDER BY created_at DESC LIMIT 3");
                    $stmt->execute();
                    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($alerts) {
                        foreach ($alerts as $alert) {
                            echo "<div class='alert alert-warning'>";
                            echo "<h5>" . htmlspecialchars($alert['title']) . "</h5>";
                            echo "<p>" . htmlspecialchars($alert['message']) . "</p>";
                            echo "<small>Issued at: " . htmlspecialchars($alert['created_at']) . "</small>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No emergency alerts found.</p>";
                    }
                } catch (PDOException $e) {
                    error_log("Error fetching emergency alerts: " . $e->getMessage());
                    echo "<p>An error occurred while fetching alerts.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>