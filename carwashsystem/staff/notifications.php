<?php
// Staff notifications page
require_once '../config/session_config.php';
require_once '../classes/Queue.php';

requireRole('staff');

$queue = new Queue();

// Get notifications (recent queue updates, completed services, etc.)
$notifications = [];

// Get recent completed services by this staff member
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT sh.*, c.first_name, c.last_name, c.phone, v.plate_number, st.name as service_name
    FROM service_history sh
    JOIN customers c ON sh.customer_id = c.id
    JOIN vehicles v ON sh.vehicle_id = v.id
    JOIN service_types st ON sh.service_type_id = st.id
    WHERE sh.staff_id = ? AND sh.end_time >= DATE_SUB(NOW(), INTERVAL 1 DAY)
    ORDER BY sh.end_time DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$recentServices = $stmt->fetchAll();

// Get current queue status
$currentQueue = $queue->getAllQueueItems();
$waitingCount = count(array_filter($currentQueue, fn($item) => $item['status'] === 'waiting'));
$inProgressCount = count(array_filter($currentQueue, fn($item) => $item['status'] === 'in_progress'));

// Create notifications based on activity
if ($waitingCount > 0) {
    $notifications[] = [
        'type' => 'info',
        'message' => "There are {$waitingCount} customers waiting in queue.",
        'time' => 'Now'
    ];
}

if ($inProgressCount > 0) {
    $notifications[] = [
        'type' => 'warning',
        'message' => "You have {$inProgressCount} service(s) in progress.",
        'time' => 'Now'
    ];
}

foreach ($recentServices as $service) {
    $notifications[] = [
        'type' => 'success',
        'message' => "Completed service for {$service['first_name']} {$service['last_name']} - {$service['plate_number']} ({$service['service_name']})",
        'time' => date('g:i A', strtotime($service['end_time']))
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Car Wash Pro Staff</title>
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
                <h2 class="form-title">Notifications</h2>

                <?php if (empty($notifications)): ?>
                    <div class="empty-notifications">
                        <div class="empty-icon">🔔</div>
                        <h3>No new notifications</h3>
                        <p>You're all caught up!</p>
                    </div>
                <?php else: ?>
                    <div class="notifications-list">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item notification-<?php echo $notification['type']; ?>">
                                <div class="notification-content">
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <span class="notification-time"><?php echo $notification['time']; ?></span>
                                </div>
                                <div class="notification-icon">
                                    <?php
                                    switch ($notification['type']) {
                                        case 'success':
                                            echo '✅';
                                            break;
                                        case 'warning':
                                            echo '⚠️';
                                            break;
                                        case 'info':
                                            echo 'ℹ️';
                                            break;
                                        default:
                                            echo '🔔';
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
        .empty-notifications {
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

        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .notification-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-left: 4px solid;
        }

        .notification-success {
            border-left-color: #4caf50;
        }

        .notification-warning {
            border-left-color: #ff9800;
        }

        .notification-info {
            border-left-color: #2196f3;
        }

        .notification-content {
            flex: 1;
        }

        .notification-content p {
            margin: 0 0 0.5rem 0;
            font-weight: 500;
        }

        .notification-time {
            font-size: 0.9rem;
            color: #666;
        }

        .notification-icon {
            font-size: 1.5rem;
            margin-left: 1rem;
        }

        .quick-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .quick-actions {
                flex-direction: column;
            }

            .notification-item {
                flex-direction: column;
                text-align: center;
            }

            .notification-icon {
                margin-left: 0;
                margin-top: 1rem;
            }
        }
    </style>
</body>
</html>
