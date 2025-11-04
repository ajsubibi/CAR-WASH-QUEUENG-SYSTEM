<?php
// Admin reports page
require_once '../config/session_config.php';
require_once '../classes/Service.php';

requireRole(['admin', 'main_admin']);

$service = new Service();
$message = '';
$reportData = [];

// Get report parameters
$reportType = $_GET['type'] ?? 'daily';
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['report_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
}

try {
    $pdo = getDB();

    if ($reportType === 'daily') {
        // Daily service report
        $stmt = $pdo->prepare("
            SELECT DATE(end_time) as date, COUNT(*) as services_completed,
                   SUM(price) as total_revenue
            FROM service_history
            WHERE DATE(end_time) BETWEEN ? AND ?
            GROUP BY DATE(end_time)
            ORDER BY date DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();

    } elseif ($reportType === 'service_type') {
        // Service type popularity
        $stmt = $pdo->prepare("
            SELECT st.name, COUNT(sh.id) as service_count,
                   SUM(st.price) as total_revenue
            FROM service_history sh
            JOIN service_types st ON sh.service_type_id = st.id
            WHERE DATE(sh.end_time) BETWEEN ? AND ?
            GROUP BY st.id, st.name
            ORDER BY service_count DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();

    } elseif ($reportType === 'staff_performance') {
        // Staff performance
        $stmt = $pdo->prepare("
            SELECT u.first_name, u.last_name, COUNT(sh.id) as services_completed,
                   AVG(TIMESTAMPDIFF(MINUTE, sh.start_time, sh.end_time)) as avg_service_time
            FROM service_history sh
            JOIN users u ON sh.staff_id = u.id
            WHERE DATE(sh.end_time) BETWEEN ? AND ?
            GROUP BY u.id, u.first_name, u.last_name
            ORDER BY services_completed DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();

    } elseif ($reportType === 'customer_stats') {
        // Customer statistics
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT customer_id) as total_customers,
                   COUNT(*) as total_services,
                   AVG(price) as avg_service_price
            FROM service_history
            WHERE DATE(end_time) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();
    }

} catch (Exception $e) {
    $message = 'Error generating report: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Car Wash Pro Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1 class="logo">Car Wash Pro - Admin</h1>
            <nav class="nav">
                <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
                <a href="../login/logout.php" class="btn btn-secondary">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="admin-dashboard">
                <h2>Reports & Analytics</h2>

                <?php if ($message): ?>
                    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <!-- Report Filters -->
                <div class="form-container">
                    <h3>Generate Report</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="report_type">Report Type</label>
                                <select id="report_type" name="report_type">
                                    <option value="daily" <?php echo $reportType === 'daily' ? 'selected' : ''; ?>>Daily Services</option>
                                    <option value="service_type" <?php echo $reportType === 'service_type' ? 'selected' : ''; ?>>Service Types</option>
                                    <option value="staff_performance" <?php echo $reportType === 'staff_performance' ? 'selected' : ''; ?>>Staff Performance</option>
                                    <option value="customer_stats" <?php echo $reportType === 'customer_stats' ? 'selected' : ''; ?>>Customer Statistics</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                </div>

                <!-- Report Results -->
                <?php if (!empty($reportData)): ?>
                    <div class="report-results">
                        <h3>Report Results</h3>
                        <div class="table-container">
                            <?php if ($reportType === 'daily'): ?>
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Services Completed</th>
                                            <th>Total Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($row['date'])); ?></td>
                                                <td><?php echo $row['services_completed']; ?></td>
                                                <td>₱<?php echo number_format($row['total_revenue'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            <?php elseif ($reportType === 'service_type'): ?>
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Service Type</th>
                                            <th>Services Completed</th>
                                            <th>Total Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo $row['service_count']; ?></td>
                                                <td>₱<?php echo number_format($row['total_revenue'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            <?php elseif ($reportType === 'staff_performance'): ?>
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Staff Member</th>
                                            <th>Services Completed</th>
                                            <th>Avg Service Time (min)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                <td><?php echo $row['services_completed']; ?></td>
                                                <td><?php echo $row['avg_service_time'] ? round($row['avg_service_time'], 1) : 'N/A'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                            <?php elseif ($reportType === 'customer_stats'): ?>
                                <div class="stats-summary">
                                    <?php $stats = $reportData[0]; ?>
                                    <div class="stat-card">
                                        <h4>Total Customers</h4>
                                        <span class="stat-number"><?php echo $stats['total_customers']; ?></span>
                                    </div>
                                    <div class="stat-card">
                                        <h4>Total Services</h4>
                                        <span class="stat-number"><?php echo $stats['total_services']; ?></span>
                                    </div>
                                    <div class="stat-card">
                                        <h4>Average Service Price</h4>
                                        <span class="stat-number">₱<?php echo number_format($stats['avg_service_price'], 2); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
        .report-results {
            margin-top: 3rem;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h4 {
            margin: 0 0 0.5rem 0;
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2196f3;
        }

        .table-container {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .admin-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .info-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            background-color: #e3f2fd;
            color: #1976d2;
            border: 1px solid #2196f3;
        }
    </style>
</body>
</html>
