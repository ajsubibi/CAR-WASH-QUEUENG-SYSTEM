<?php
// Staff dashboard
require_once '../config/session_config.php';
require_once '../classes/Queue.php';

requireRole('staff');

$queue = new Queue();
$currentQueue = $queue->getCurrentQueue();

// Get updated stats for dashboard
$pdo = getDB();
$today = date('Y-m-d');

// Total customers served today (completed services)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT sh.customer_id) as customers_served_today
    FROM service_history sh
    WHERE DATE(sh.end_time) = ? AND sh.staff_id = ?
");
$stmt->execute([$today, $_SESSION['user_id']]);
$customersServed = $stmt->fetch()['customers_served_today'];

// Ongoing services (in progress)
$stmt = $pdo->prepare("
    SELECT COUNT(*) as ongoing_services
    FROM queue
    WHERE status = 'in_progress' AND staff_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$ongoingServices = $stmt->fetch()['ongoing_services'];

// Completed services today
$stmt = $pdo->prepare("
    SELECT COUNT(*) as completed_today
    FROM service_history
    WHERE DATE(end_time) = ? AND staff_id = ?
");
$stmt->execute([$today, $_SESSION['user_id']]);
$completedToday = $stmt->fetch()['completed_today'];

// Today's total revenue
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(st.price), 0) as today_revenue
    FROM service_history sh
    JOIN service_types st ON sh.service_type_id = st.id
    WHERE DATE(sh.end_time) = ? AND sh.staff_id = ?
");
$stmt->execute([$today, $_SESSION['user_id']]);
$todayRevenue = $stmt->fetch()['today_revenue'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $queueId = (int)($_POST['queue_id'] ?? 0);

    if ($action === 'start' && $queueId > 0) {
        try {
            $queue->startService($queueId, $_SESSION['user_id']);
            header('Location: dashboard.php?success=started');
            exit();
        } catch (Exception $e) {
            $error = 'Failed to start service: ' . $e->getMessage();
        }
    } elseif ($action === 'complete' && $queueId > 0) {
        try {
            $queue->completeService($queueId);
            header('Location: dashboard.php?success=completed');
            exit();
        } catch (Exception $e) {
            $error = 'Failed to complete service: ' . $e->getMessage();
        }
    } elseif ($action === 'cancel' && $queueId > 0) {
        try {
            $queue->cancelService($queueId);
            header('Location: dashboard.php?success=cancelled');
            exit();
        } catch (Exception $e) {
            $error = 'Failed to cancel service: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Car Wash Pro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</head>
<body>
    <div class="staff-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="container">
                <div class="dashboard">
                    <h2>Staff Dashboard</h2>

                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">
                        <?php
                        switch ($_GET['success']) {
                            case 'started': echo 'Service started successfully.'; break;
                            case 'completed': echo 'Service completed successfully.'; break;
                            case 'cancelled': echo 'Service cancelled.'; break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Queue Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Customers Served Today</h3>
                        <div class="stat-number"><?php echo $customersServed; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Ongoing Services</h3>
                        <div class="stat-number"><?php echo $ongoingServices; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Completed Services Today</h3>
                        <div class="stat-number"><?php echo $completedToday; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Today's Total Revenue</h3>
                        <div class="stat-number">₱<?php echo number_format($todayRevenue, 2); ?></div>
                    </div>
                </div>

                <!-- Current Queue -->
                <div class="queue-section">
                    <h3>Current Queue</h3>

                    <?php if (empty($currentQueue)): ?>
                        <div class="empty-state">
                            <p>No customers in queue.</p>
                        </div>
                    <?php else: ?>
                        <div class="queue-table-container">
                            <table class="queue-table">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Wait Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($currentQueue as $item): ?>
                                        <tr class="<?php echo $item['status'] === 'in_progress' ? 'in-progress' : ''; ?>">
                                            <td><?php echo $item['position']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?><br>
                                                <small><?php echo htmlspecialchars($item['phone']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($item['plate_number']); ?><br>
                                                <small><?php echo htmlspecialchars($item['vehicle_type']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['service_name']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $item['status']; ?>">
                                                    <?php
                                                    switch ($item['status']) {
                                                        case 'waiting': echo 'Waiting'; break;
                                                        case 'in_progress': echo 'In Progress'; break;
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($item['status'] === 'waiting'): ?>
                                                    <?php echo $item['estimated_wait_time']; ?> min
                                                <?php elseif ($item['status'] === 'in_progress'): ?>
                                                    In Service
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="queue_id" value="<?php echo $item['id']; ?>">
                                                    <?php if ($item['status'] === 'waiting'): ?>
                                                        <button type="submit" name="action" value="start" class="btn btn-primary btn-small"
                                                                onclick="return confirm('Start service for this customer?')">Accept</button>
                                                    <?php elseif ($item['status'] === 'in_progress'): ?>
                                                        <button type="submit" name="action" value="complete" class="btn btn-primary btn-small"
                                                                onclick="return confirm('Mark service as completed?')">Complete</button>
                                                        <button type="submit" name="action" value="cancel" class="btn btn-secondary btn-small"
                                                                onclick="return confirm('Cancel this service?')">Cancel</button>
                                                    <?php endif; ?>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>


            </div>
        </div>
    </main>

   
</body>
</html>
