<?php
// Select vehicle for service page
require_once '../config/session_config.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Vehicle.php';

$message = '';
$customer = null;
$vehicles = [];

if (isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);

    if (!empty($customer_id)) {
        $customerClass = new Customer();
        $vehicleClass = new Vehicle();

        $customer = $customerClass->getById($customer_id);

        if ($customer) {
            $vehicles = $vehicleClass->getByCustomerId($customer['id']);
        } else {
            $message = 'Customer not found.';
        }
    }
} else {
    $message = 'Invalid access. Please search for a customer first.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Vehicle for Service - Car Wash Pro</title>
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
                <h2>Select Vehicle for Service</h2>
                <?php if ($customer): ?>
                    <p>Select a vehicle for <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?> to book a service.</p>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($customer && !empty($vehicles)): ?>
                    <!-- Vehicles Selection -->
                    <div class="vehicles-section">
                        <h3>Your Vehicles</h3>
                        <div class="vehicles-grid">
                            <?php foreach ($vehicles as $vehicle): ?>
                                <div class="vehicle-card selectable" onclick="window.location.href='book_service.php?customer_id=<?php echo $customer['id']; ?>&vehicle_id=<?php echo $vehicle['id']; ?>'">
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
                                    <div class="select-indicator">Click to select</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="form-actions">
                        <a href="search_customer.php?phone=<?php echo urlencode($customer['phone']); ?>" class="btn btn-secondary">Back to Customer</a>
                    </div>
                <?php elseif ($customer && empty($vehicles)): ?>
                    <div class="info-message">No vehicles registered. Please add a vehicle first.</div>
                    <div class="form-actions">
                        <a href="add_vehicle.php?customer_id=<?php echo $customer['id']; ?>" class="btn btn-primary">Add Vehicle</a>
                        <a href="search_customer.php?phone=<?php echo urlencode($customer['phone']); ?>" class="btn btn-secondary">Back to Customer</a>
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

    .vehicles-section {
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
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .vehicle-card:hover {
        border-color: #2196f3;
        box-shadow: 0 4px 15px rgba(33, 150, 243, 0.2);
        transform: translateY(-2px);
    }

    .vehicle-card h4 {
        color: #2196f3;
        margin-bottom: 0.5rem;
    }

    .select-indicator {
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #666;
        font-style: italic;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 1rem;
    }

    .btn-primary {
        background-color: #2196f3;
        color: white;
    }

    .btn-primary:hover {
        background-color: #1976d2;
    }

    .btn-secondary {
        background-color: #757575;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #616161;
    }

    @media (max-width: 768px) {
        .vehicles-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }
    }
</style>
