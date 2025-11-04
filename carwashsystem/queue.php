<?php
// Public queue display
require_once 'config/session_config.php';
require_once 'classes/Queue.php';

$queue = new Queue();
$currentQueue = $queue->getCurrentQueue();
$joined = isset($_GET['joined']) && $_GET['joined'] == '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Queue - Car Wash Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta http-equiv="refresh" content="30"> <!-- Auto-refresh every 30 seconds -->
</head>
<body>
    <div class="background-blur" style="background-image: url('assets/images/Mercedes.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; filter: blur(2px); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>
    <header class="header header-relative" style="background: transparent;">
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="btn btn-secondary">← Home</a>
                <a href="register.php" class="btn btn-primary">Register Now</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="form-container" style="margin-top: -10px;">
                <h2>Current Queue Status</h2>
                <p style="color: #666; margin-bottom: 2rem;">Real-time queue updates every 30 seconds</p>

                <?php if ($joined): ?>
                    <div class="success-message" style="margin-bottom: 2rem;">
                        ✅ Successfully joined the queue! Your position will be updated shortly.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message" style="margin-bottom: 2rem;">
                        <?php if ($_GET['success'] === 'registration'): ?>
                            Service registered successfully! Your queue number is: <strong><?php echo htmlspecialchars($_GET['queue'] ?? ''); ?></strong>
                        <?php elseif ($_GET['success'] === 'replacement'): ?>
                            Vehicle replaced successfully! Your queue number is: <strong><?php echo htmlspecialchars($_GET['queue'] ?? ''); ?></strong>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($currentQueue)): ?>
                    <!-- Empty Queue State -->
                    <div class="empty-queue" style="text-align: center; padding: 3rem;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🚗</div>
                        <h3 style="color: #333; margin-bottom: 1rem;">No vehicles in queue</h3>
                        <p style="color: #666; margin-bottom: 2rem;">The queue is currently empty. Be the first to book your service!</p>
                        <a href="register.php" class="btn btn-primary">Join the Queue</a>
                    </div>
                <?php else: ?>
                    <!-- Queue Table -->
                    <div class="queue-table-container" style="margin-bottom: 2rem;">
                        <table class="queue-table">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Est. Wait</th>
                                    <th>Staff</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($currentQueue as $item): ?>
                                    <tr class="<?php echo $item['status'] === 'in_progress' ? 'in-progress' : ''; ?>">
                                        <td class="position"><?php echo $item['position']; ?></td>
                                        <td class="customer">
                                            <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?><br>
                                            <small><?php echo htmlspecialchars($item['phone']); ?></small>
                                        </td>
                                        <td class="vehicle">
                                            <?php echo htmlspecialchars($item['plate_number']); ?><br>
                                            <small><?php echo htmlspecialchars($item['vehicle_type']); ?></small>
                                        </td>
                                        <td class="service">
                                            <?php echo htmlspecialchars($item['service_name']); ?><br>
                                            <small>₱<?php echo number_format($item['price'], 2); ?></small>
                                        </td>
                                        <td class="status">
                                            <span class="status-badge status-<?php echo $item['status']; ?>">
                                                <?php
                                                switch ($item['status']) {
                                                    case 'waiting':
                                                        echo 'Waiting';
                                                        break;
                                                    case 'in_progress':
                                                        echo 'In Progress';
                                                        break;
                                                    case 'completed':
                                                        echo 'Completed';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Cancelled';
                                                        break;
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td class="wait-time">
                                            <?php if ($item['status'] === 'waiting'): ?>
                                                <?php echo $item['estimated_wait_time']; ?> min
                                            <?php elseif ($item['status'] === 'in_progress'): ?>
                                                In Service
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="staff">
                                            <?php if ($item['staff_first_name']): ?>
                                                <?php echo htmlspecialchars($item['staff_first_name'] . ' ' . $item['staff_last_name']); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-buttons">
                        <a href="register.php" class="btn btn-primary">Join the Queue</a>
                        <a href="classes/search_customer.php" class="btn btn-secondary">My Record</a>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['registration_data']) && isset($_SESSION['in_review_process'])): ?>
                    <div class="form-buttons" style="margin-top: 1rem;">
                        <a href="review.php" class="btn btn-secondary">← Back to Review</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
