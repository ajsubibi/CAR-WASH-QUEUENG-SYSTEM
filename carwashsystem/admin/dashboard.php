<?php
// Admin dashboard
require_once '../config/session_config.php';
require_once '../classes/Queue.php';
require_once '../classes/Service.php';
require_once '../classes/User.php';

requireRole('admin');

$queue = new Queue();
$service = new Service();
$user = new User();

$stats = $queue->getStats();
$serviceStats = $service->getServiceStats();
$users = $user->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Car Wash Pro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1 class="logo">Car Wash Pro - Admin</h1>
            <nav class="nav">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle">Menu ▼</button>
                    <div class="dropdown-menu">
                        <a href="settings.php">Settings</a>
                        <a href="../login/logout.php">Logout</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="admin-dashboard">
                <h2>Admin Dashboard</h2>

                <!-- Quick Actions -->
                <div class="admin-actions">
                    <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
                    <a href="manage_services.php" class="btn btn-secondary">Service</a>
                    <a href="manage_staff.php" class="btn btn-secondary">Staff</a>
                    <a href="manage_customers.php" class="btn btn-secondary">Customers</a>
                    <a href="reports.php" class="btn btn-secondary">Reports</a>
                    <a href="queue_management.php" class="btn btn-secondary">Queue</a>
                    <a href="admin_display.php" class="btn btn-secondary">Public View</a>
                    <a href="settings.php" class="btn btn-secondary">Settings</a>
                    <?php if ($_SESSION['role'] === 'main_admin'): ?>
                        <a href="add_admin.php" class="btn btn-primary">Add Admin</a>
                    <?php endif; ?>
                </div>

                <!-- Queue Statistics -->
                <div class="stats-section">
                    <h3>Today's Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h4>Waiting</h4>
                            <div class="stat-number"><?php echo $stats['waiting']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h4>In Progress</h4>
                            <div class="stat-number"><?php echo $stats['in_progress']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h4>Completed</h4>
                            <div class="stat-number"><?php echo $stats['completed_today']; ?></div>
                        </div>
                        <div class="stat-card">
                            <h4>Avg Wait Time</h4>
                            <div class="stat-number"><?php echo $stats['avg_wait_time'] ? round($stats['avg_wait_time']) : 0; ?> min</div>
                        </div>
                    </div>
                </div>

                <!-- Service Statistics -->
                <?php if (!empty($serviceStats)): ?>
                    <div class="stats-section">
                        <h3>Service Performance</h3>
                        <div class="service-stats">
                            <?php foreach ($serviceStats as $stat): ?>
                                <div class="service-stat-card">
                                    <h4><?php echo htmlspecialchars($stat['name']); ?></h4>
                                    <div class="stat-details">
                                        <span>Total: <?php echo $stat['total_services']; ?></span>
                                        <span>Avg Duration: <?php echo round($stat['avg_duration']); ?> min</span>
                                        <span>Revenue: $<?php echo number_format($stat['total_revenue'], 2); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Users -->
                <div class="recent-section">
                    <h3>System Users</h3>
                    <div class="users-table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($users, 0, 10) as $userData): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></td>
                                        <td><?php echo ucfirst($userData['role']); ?></td>
                                        <td><?php echo htmlspecialchars($userData['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($userData['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Car Wash Pro. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
