<?php
// Customer search and history page
require_once '../config/session_config.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Vehicle.php';
require_once __DIR__ . '/Service.php';

$message = '';
$customer = null;
$vehicles = [];
$history = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['phone'])) {
    $phone = trim($_POST['phone'] ?? $_GET['phone'] ?? '');

    if (!empty($phone)) {
        $customerClass = new Customer();
        $vehicleClass = new Vehicle();
        $serviceClass = new Service();

        $customer = $customerClass->getByPhone($phone);

        if ($customer) {
            $vehicles = $vehicleClass->getByCustomerId($customer['id']);
            $history = $serviceClass->getCustomerServiceHistory($customer['id']);
        } else {
            $message = 'Customer not found. Please check the phone number.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Search - Car Wash Pro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="background-blur" style="background-image: url('../assets/images/Mercedes.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; filter: blur(1.5px); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>
    <header class="header header-relative" style="background: transparent;">
        <div class="container">
            <nav class="nav">
                <a href="../index.php" class="btn btn-secondary">← Home</a>
                <a href="../register.php" class="btn btn-primary">Register Now</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="customer-search">
                <h2>Find Your Records</h2>
                <p>Enter your phone number to view your service history and registered vehicles.</p>

                <?php if ($message): ?>
                    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="form-container">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required
                                   pattern="[\+]?[\d\s\-\(\)]+" placeholder="+63 917 123 4567"
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? $_GET['phone'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Search Records</button>
                    </form>
                </div>

                <?php if ($customer): ?>
                    <!-- Customer Information -->
                    <div class="customer-info">
                        <h3>Customer Information</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Name:</span>
                                <span class="value"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Vehicles -->
                    <div class="vehicles-section">
                        <h3>Registered Vehicles</h3>
                        <?php if (empty($vehicles)): ?>
                            <p>No vehicles registered.</p>
                        <?php else: ?>
                            <div class="vehicles-grid">
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <div class="vehicle-card">
                                        <h4><?php echo htmlspecialchars($vehicle['vehicle_type_name']); ?><?php if ($vehicle['brand'] && $vehicle['model']): ?> - <?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?><?php endif; ?></h4>
                                        <div class="vehicle-details">
                                            <?php if ($vehicle['brand'] && !$vehicle['model']): ?>
                                                <p><strong>Brand:</strong> <?php echo htmlspecialchars($vehicle['brand']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($vehicle['model'] && !$vehicle['brand']): ?>
                                                <p><strong>Model:</strong> <?php echo htmlspecialchars($vehicle['model']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($vehicle['color']): ?>
                                                <p><strong>Color:</strong> <?php echo htmlspecialchars($vehicle['color']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Service History -->
                    <div class="history-section">
                        <h3>Service History</h3>
                        <?php if (empty($history)): ?>
                            <p>No service history found.</p>
                        <?php else: ?>
                            <div class="history-table-container">
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Vehicle</th>
                                            <th>Service</th>
                                            <th>Staff</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history as $record): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y g:i A', strtotime($record['end_time'])); ?></td>
                                                <td><?php echo htmlspecialchars($record['plate_number']); ?></td>
                                                <td><?php echo htmlspecialchars($record['service_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['staff_first_name'] . ' ' . $record['staff_last_name']); ?></td>
                                                <td>₱<?php echo number_format($record['price'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="customer-actions">
                        <a href="add_vehicle.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-primary">Add Vehicle</a>
                        <a href="select_vehicle_for_service.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-secondary">New Service Type</a>
                        <a href="../queue.php" class="btn btn-secondary">View Current Queue</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>


</body>
</html>

<style>
    .customer-search {
        max-width: 800px;
        margin: 0 auto;
    }

    .customer-search h2 {
        color: white;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .customer-search p {
        color: white;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }

    .info-message {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        background-color: #e3f2fd;
        color: #1976d2;
        border: 1px solid #2196f3;
    }

    .customer-info {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin: 2rem 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .label {
        font-weight: 500;
        color: #555;
    }

    .value {
        color: #333;
    }

    .vehicles-section,
    .history-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin: 2rem 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .vehicles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .vehicle-card {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 1rem;
        transition: border-color 0.3s ease;
    }

    .vehicle-card:hover {
        border-color: #2196f3;
    }

    .vehicle-card h4 {
        color: #2196f3;
        margin-bottom: 0.5rem;
    }

    .history-table-container {
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
    }

    .customer-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .info-grid,
        .vehicles-grid {
            grid-template-columns: 1fr;
        }

        .customer-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
