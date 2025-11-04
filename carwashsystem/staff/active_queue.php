<?php
// Staff active queue management
require_once '../config/session_config.php';
require_once '../classes/Queue.php';

requireRole('staff');

$queue = new Queue();
$currentQueue = $queue->getCurrentQueue();

// Calculate statistics
$totalWaiting = count(array_filter($currentQueue, fn($item) => $item['status'] === 'waiting'));
$totalInProgress = count(array_filter($currentQueue, fn($item) => $item['status'] === 'in_progress'));
$totalRevenue = array_sum(array_column($currentQueue, 'price'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $queueId = (int)($_POST['queue_id'] ?? 0);

    if ($action === 'start' && $queueId > 0) {
        try {
            $queue->startService($queueId, $_SESSION['user_id']);
            header('Location: active_queue.php?success=started');
            exit();
        } catch (Exception $e) {
            $error = 'Failed to start service: ' . $e->getMessage();
        }
    } elseif ($action === 'complete' && $queueId > 0) {
        try {
            $queue->completeService($queueId);
            header('Location: active_queue.php?success=completed');
            exit();
        } catch (Exception $e) {
            $error = 'Failed to complete service: ' . $e->getMessage();
        }
    } elseif ($action === 'cancel' && $queueId > 0) {
        try {
            $queue->cancelService($queueId);
            header('Location: active_queue.php?success=cancelled');
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
    <title>Active Queue - Car Wash Pro Staff</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="staff-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="container">
                <div class="modern-form-container">
                <h2 class="form-title">Active Queue Management</h2>

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

                <!-- Statistics Overview -->
                <div class="stats-overview">
                    <div class="stat-card">
                        <h4>Total Waiting</h4>
                        <div class="stat-number"><?php echo $totalWaiting; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>In Progress</h4>
                        <div class="stat-number"><?php echo $totalInProgress; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Revenue</h4>
                        <div class="stat-number">₱<?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                </div>

                <?php if (empty($currentQueue)): ?>
                    <div class="no-history">
                        <div class="empty-icon">📋</div>
                        <h3>No Active Queue</h3>
                        <p>No customers are currently in the queue.</p>
                    </div>
                <?php else: ?>
                    <div class="queue-actions">
                        <button type="button" class="btn btn-primary" onclick="callNextCustomer()">Call Next Customer</button>
                    </div>

                    <!-- Active Queue Table -->
                    <div class="history-section">
                        <h3>Current Queue</h3>
                        <div class="table-container">
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Est. Wait</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($currentQueue as $item): ?>
                                        <tr>
                                            <td><?php echo $item['position']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?><br>
                                                <small><?php echo htmlspecialchars($item['phone']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['plate_number']); ?></td>
                                            <td><?php echo htmlspecialchars($item['service_name']); ?> (₱<?php echo number_format($item['price'], 2); ?>)</td>
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
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

   
    <style>
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
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
            font-size: 1.8rem;
            font-weight: bold;
            color: #2196f3;
        }

        .history-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th,
        .history-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .history-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .history-table td {
            vertical-align: top;
        }

        .history-table td small {
            color: #666;
            font-size: 0.8rem;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-waiting {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-in_progress {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .no-history {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }

            .history-table {
                font-size: 0.9rem;
            }

            .history-table th,
            .history-table td {
                padding: 0.5rem;
            }
        }
    </style>

    <script>
        function callNextCustomer() {
            // Find the first waiting customer
            const waitingItems = document.querySelectorAll('tr:has(.status-waiting)');
            if (waitingItems.length > 0) {
                const firstItem = waitingItems[0];
                const position = firstItem.cells[0].textContent;
                alert(`Calling customer in position ${position}`);
                // You could add audio notification here
            } else {
                alert('No waiting customers in queue.');
            }
        }
    </script>
</body>
</html>
