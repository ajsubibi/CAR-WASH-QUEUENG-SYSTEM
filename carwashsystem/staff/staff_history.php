<?php
// Staff service history page
require_once '../config/session_config.php';
require_once '../classes/Queue.php';

requireRole('staff');

$queue = new Queue();

// Get service history for this staff member
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT sh.*, c.first_name, c.last_name, c.phone, v.plate_number, st.name as service_name, st.price,
           TIMESTAMPDIFF(MINUTE, sh.start_time, sh.end_time) as duration_minutes
    FROM service_history sh
    JOIN customers c ON sh.customer_id = c.id
    JOIN vehicles v ON sh.vehicle_id = v.id
    JOIN service_types st ON sh.service_type_id = st.id
    WHERE sh.staff_id = ?
    ORDER BY sh.end_time DESC
");
$stmt->execute([$_SESSION['user_id']]);
$serviceHistory = $stmt->fetchAll();

// Calculate statistics
$totalServices = count($serviceHistory);
$totalRevenue = array_sum(array_column($serviceHistory, 'price'));
$avgDuration = $totalServices > 0 ? array_sum(array_column($serviceHistory, 'duration_minutes')) / $totalServices : 0;

// Get today's services
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT COUNT(*) as today_services, SUM(st.price) as today_revenue
    FROM service_history sh
    JOIN service_types st ON sh.service_type_id = st.id
    WHERE sh.staff_id = ? AND DATE(sh.end_time) = ?
");
$stmt->execute([$_SESSION['user_id'], $today]);
$todayStats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service History - Car Wash Pro Staff</title>
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
                <h2 class="form-title">Service History</h2>

                <!-- Statistics Overview -->
                <div class="stats-overview">
                    <div class="stat-card">
                        <h4>Total Services</h4>
                        <div class="stat-number"><?php echo $totalServices; ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Revenue</h4>
                        <div class="stat-number">₱<?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                    <div class="stat-card">
                        <h4>Avg Duration</h4>
                        <div class="stat-number"><?php echo round($avgDuration); ?> min</div>
                    </div>
                    <div class="stat-card">
                        <h4>Today's Revenue</h4>
                        <div class="stat-number">₱<?php echo number_format($todayStats['today_revenue'] ?? 0, 2); ?></div>
                    </div>
                </div>

                <!-- Service History Table -->
                <?php if (!empty($serviceHistory)): ?>
                    <div class="history-section">
                        <h3>Completed Services</h3>
                        <div class="table-container">
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>Service</th>
                                        <th>Duration</th>
                                        <th>Revenue</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($serviceHistory as $service): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y g:i A', strtotime($service['end_time'])); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($service['first_name'] . ' ' . $service['last_name']); ?><br>
                                                <small><?php echo htmlspecialchars($service['phone']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($service['plate_number']); ?></td>
                                            <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                                            <td><?php echo $service['duration_minutes']; ?> min</td>
                                            <td>₱<?php echo number_format($service['price'], 2); ?></td>
                                            <td>
                                                <span class="payment-badge payment-paid">Paid</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-history">
                        <div class="empty-icon">📋</div>
                        <h3>No Service History</h3>
                        <p>You haven't completed any services yet.</p>
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

        .payment-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
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

        .history-navigation {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
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

            .history-navigation {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
