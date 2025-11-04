<?php
// Staff control panel - separate queue management for staff
require_once '../config/session_config.php';
require_once '../classes/Queue.php';

requireRole('staff');

$queue = new Queue();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'call_next') {
        // Call next customer in queue
        try {
            $nextCustomer = $queue->getNextWaitingCustomer();
            if ($nextCustomer) {
                $queue->updateStatus($nextCustomer['id'], 'in_progress', $_SESSION['user_id']);
                $message = "Called next customer: {$nextCustomer['first_name']} {$nextCustomer['last_name']} - {$nextCustomer['plate_number']}";
            } else {
                $message = "No customers waiting in queue.";
            }
        } catch (Exception $e) {
            $message = 'Error calling next customer: ' . $e->getMessage();
        }
    } elseif ($action === 'complete_service') {
        $queueId = $_POST['queue_id'];
        $paymentStatus = $_POST['payment_status'] ?? 'pending';

        try {
            $queue->completeService($queueId, $_SESSION['user_id'], $paymentStatus);
            $message = "Service completed successfully.";
        } catch (Exception $e) {
            $message = 'Error completing service: ' . $e->getMessage();
        }
    } elseif ($action === 'mark_paid') {
        $queueId = $_POST['queue_id'];

        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("UPDATE queue SET payment_status = 'paid', status = 'claimed' WHERE id = ?");
            $stmt->execute([$queueId]);
            $message = "Payment marked as received and service claimed.";
        } catch (Exception $e) {
            $message = 'Error updating payment status: ' . $e->getMessage();
        }
    }
}

// Get current queue items assigned to this staff member
$currentServices = $queue->getStaffCurrentServices($_SESSION['user_id']);

// Get all queue items for overview
$allQueueItems = $queue->getAllQueueItems();
$waitingCount = count(array_filter($allQueueItems, fn($item) => $item['status'] === 'waiting'));
$inProgressCount = count($currentServices);
$totalRevenue = array_sum(array_column($currentServices, 'price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel - Car Wash Pro Staff</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <div class="staff-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="container">
                <div class="modern-form-container">
                <h2 class="form-title">Staff Control Panel</h2>

                <?php if ($message): ?>
                    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <!-- Statistics Overview -->
                <div class="stats-overview">
                    <div class="stat-card">
                        <h4>Total Waiting</h4>
                        <div class="stat-number"><?php echo $waitingCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>In Progress</h4>
                        <div class="stat-number"><?php echo $inProgressCount; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Revenue</h4>
                        <div class="stat-number">₱<?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                </div>

                <!-- Call Next Customer -->
                <div class="action-section">
                    <h3>Call Next Customer</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="call_next">
                        <button type="submit" class="btn btn-primary btn-large">Call Next Customer</button>
                    </form>
                </div>

                <!-- Current Services -->
                <?php if (!empty($currentServices)): ?>
                    <div class="history-section">
                        <h3>Current Services</h3>
                        <div class="table-container">
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Service</th>
                                        <th>Started</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($currentServices as $service): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($service['first_name'] . ' ' . $service['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($service['plate_number']); ?></td>
                                            <td><?php echo htmlspecialchars($service['service_name']); ?> (₱<?php echo number_format($service['price'], 2); ?>)</td>
                                            <td><?php echo date('g:i A', strtotime($service['started_at'])); ?></td>
                                            <td>
                                                <span class="payment-status payment-<?php echo $service['payment_status'] ?? 'pending'; ?>">
                                                    <?php echo ucfirst($service['payment_status'] ?? 'pending'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (($service['payment_status'] ?? 'pending') === 'pending'): ?>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="mark_paid">
                                                        <input type="hidden" name="queue_id" value="<?php echo $service['id']; ?>">
                                                        <button type="submit" class="btn btn-secondary btn-small">Mark Paid</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="complete_service">
                                                    <input type="hidden" name="queue_id" value="<?php echo $service['id']; ?>">
                                                    <input type="hidden" name="payment_status" value="<?php echo $service['payment_status'] ?? 'pending'; ?>">
                                                    <button type="submit" class="btn btn-primary btn-small"
                                                            onclick="return confirm('Mark this service as completed?')">Complete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-history">
                        <div class="empty-icon">🚗</div>
                        <h3>No Active Services</h3>
                        <p>You don't have any services in progress.</p>
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

        .action-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }

        .btn-large {
            padding: 1rem 2rem;
            font-size: 1.2rem;
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

        .payment-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .payment-pending {
            background: #fff3cd;
            color: #856404;
        }

        .payment-paid {
            background: #d4edda;
            color: #155724;
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
</body>
</html>
