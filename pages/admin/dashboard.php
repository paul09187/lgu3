<?php
session_start();
// Redirect non-admin users to the login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

require '../../database/connection.php';

// Set the page title
$title = "Admin Dashboard";

// Corrected include paths
include '../../include/header.php';
include '../../include/sidebar.php';
include '../../include/topbar.php';

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
$totalResidents = fetchCount($conn, "SELECT COUNT(*) AS count FROM users WHERE role = 'user'");
$maleResidents = fetchCount($conn, "SELECT COUNT(*) AS count FROM users WHERE gender = 'male' AND role = 'user'");
$femaleResidents = fetchCount($conn, "SELECT COUNT(*) AS count FROM users WHERE gender = 'female' AND role = 'user'");
$singleResidents = fetchCount($conn, "SELECT COUNT(*) AS count FROM users WHERE civil_status = 'single' AND role = 'user'");

// Fetch data for cases
$totalCases = fetchCount($conn, "SELECT COUNT(*) AS count FROM cases");
$openCases = fetchCount($conn, "SELECT COUNT(*) AS count FROM cases WHERE status = 'open'");
$resolvedCases = fetchCount($conn, "SELECT COUNT(*) AS count FROM cases WHERE status = 'resolved'");
$inProgressCases = fetchCount($conn, "SELECT COUNT(*) AS count FROM cases WHERE status = 'in_progress'");
?>

<!-- HTML Content -->
<div id="page-content-wrapper">
    <div class="container mt-4">
        <h1 class="mb-4">Welcome to Barangay Admin Dashboard</h1>

        <!-- Dashboard Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-info text-center shadow-sm">
                    <div class="card-body">
                        <h5>Total Residents</h5>
                        <h3 class="card-title"><?php echo htmlspecialchars($totalResidents); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-info text-center shadow-sm">
                    <div class="card-body">
                        <h5>Male Residents</h5>
                        <h3 class="card-title"><?php echo htmlspecialchars($maleResidents); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-info text-center shadow-sm">
                    <div class="card-body">
                        <h5>Female Residents</h5>
                        <h3 class="card-title"><?php echo htmlspecialchars($femaleResidents); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card card-info text-center shadow-sm">
                    <div class="card-body">
                        <h5>Single Residents</h5>
                        <h3 class="card-title"><?php echo htmlspecialchars($singleResidents); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cases Section -->
        <h3 class="mt-4">Cases Overview</h3>
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


        <!-- Analytics Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Analytics: Residents by Gender</h5>
                        <div id="analyticsChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../include/footer.php'; ?>

<!-- Highcharts Script -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pie Chart for Gender Analytics
        Highcharts.chart('analyticsChart', {
            chart: {
                type: 'pie',
            },
            title: {
                text: 'Residents by Gender',
            },
            series: [{
                name: 'Residents',
                colorByPoint: true,
                data: [{
                        name: 'Male',
                        y: <?php echo $maleResidents; ?>
                    },
                    {
                        name: 'Female',
                        y: <?php echo $femaleResidents; ?>
                    },
                ]
            }]
        });
    });
</script>