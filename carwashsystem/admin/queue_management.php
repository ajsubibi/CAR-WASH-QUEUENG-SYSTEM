<?php

require_once '../config/session_config.php';
require_once '../classes/Queue.php';
require_once '../classes/Customer.php';
require_once '../classes/Vehicle.php';
require_once '../classes/Service.php';

requireRole(['admin', 'main_admin']);

$queue = new Queue();
$customerClass = new Customer();
$vehicleClass = new Vehicle();
$serviceClass = new Service();

$message = '';
$error = '';
$queueItems = $queue->getAllQueueItems();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $queueId = (int)$_POST['queue_id'];
        $newStatus = $_POST['status'];

        try {
            if ($newStatus === 'in_progress') {
                $queue->startService($queueId, $_SESSION['user_id']);
            } elseif ($newStatus === 'completed') {
                $queue->completeService($queueId);
            } elseif ($newStatus === 'cancelled') {
                $queue->cancelService($queueId);
            }
            $message = 'Queue item status updated successfully.';
            $queueItems = $queue->getAllQueueItems(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to update queue status: ' . $e->getMessage();
        }
    } elseif ($action === 'delete_queue_item') {
        $queueId = (int)$_POST['queue_id'];
        try {
            $queue->deleteQueueItem($queueId);
            $message = 'Queue item deleted successfully.';
            $queueItems = $queue->getAllQueueItems(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to delete queue item: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Management - Car Wash Pro Admin</title>
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
                <h2>Queue Management</h2>

                <?php if ($message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (empty($queueItems)): ?>
                    <div class="empty-queue">
                        <div class="empty-queue-icon">🚗</div>
                        <h3>No items in queue</h3>
                        <p>All vehicles have been serviced.</p>
                    </div>
                <?php else: ?>
                    <div class="queue-stats">
                        <div class="stat-card">
                            <h4>Waiting</h4>
                            <span class="stat-number"><?php echo count(array_filter($queueItems, fn($item) => $item['status'] === 'waiting')); ?></span>
                        </div>
                        <div class="stat-card">
                            <h4>In Progress</h4>
                            <span class="stat-number"><?php echo count(array_filter($queueItems, fn($item) => $item['status'] === 'in_progress')); ?></span>
                        </div>
                        <div class="stat-card">
                            <h4>Completed Today</h4>
                            <span class="stat-number"><?php echo count(array_filter($queueItems, fn($item) => $item['status'] === 'completed' && date('Y-m-d', strtotime($item['updated_at'])) === date('Y-m-d'))); ?></span>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Wait Time</th>
                                    <th>Staff</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($queueItems as $item): ?>
                                    <tr class="queue-row status-<?php echo $item['status']; ?>">
                                        <td><?php echo $item['position'] ?? '-'; ?></td>
                                        <td><?php echo htmlspecialchars($item['customer_first_name'] . ' ' . $item['customer_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['plate_number']); ?></td>
                                        <td><?php echo htmlspecialchars($item['service_name']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $item['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            if ($item['status'] === 'waiting') {
                                                echo $item['estimated_wait_time'] ? $item['estimated_wait_time'] . ' min' : 'Calculating...';
                                            } elseif ($item['status'] === 'in_progress') {
                                                echo 'In service';
                                            } elseif ($item['status'] === 'completed') {
                                                echo 'Done';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $item['staff_first_name'] ? htmlspecialchars($item['staff_first_name'] . ' ' . $item['staff_last_name']) : '-'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($item['status'] === 'waiting'): ?>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="queue_id" value="<?php echo $item['id']; ?>">
                                                        <input type="hidden" name="status" value="in_progress">
                                                        <button type="submit" class="btn btn-primary btn-small">Start Service</button>
                                                    </form>
                                                <?php elseif ($item['status'] === 'in_progress'): ?>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="queue_id" value="<?php echo $item['id']; ?>">
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-primary btn-small">Complete</button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="queue_id" value="<?php echo $item['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="btn btn-secondary btn-small"
                                                            onclick="return confirm('Cancel this service?')">Cancel</button>
                                                </form>

                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_queue_item">
                                                    <input type="hidden" name="queue_id" value="<?php echo $item['id']; ?>">
                                                    <button type="submit" class="btn btn-secondary btn-small"
                                                            onclick="return confirm('Delete this queue item permanently?')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
        .empty-queue {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .empty-queue-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .queue-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
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

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-waiting {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-in_progress {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .queue-row.status-completed {
            opacity: 0.7;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
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

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .queue-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</body>
</html>
