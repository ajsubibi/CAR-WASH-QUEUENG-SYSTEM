<?php
// Book service page for existing customers and vehicles
require_once '../config/session_config.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Vehicle.php';
require_once __DIR__ . '/Service.php';

$message = '';
$customer = null;
$vehicle = null;

if (isset($_GET['customer_id']) && isset($_GET['vehicle_id'])) {
    $customer_id = intval($_GET['customer_id']);
    $vehicle_id = intval($_GET['vehicle_id']);

    if (!empty($customer_id) && !empty($vehicle_id)) {
        $customerClass = new Customer();
        $vehicleClass = new Vehicle();

        $customer = $customerClass->getById($customer_id);
        $vehicle = $vehicleClass->getById($vehicle_id);

        if (!$customer) {
            $message = 'Customer not found.';
        } elseif (!$vehicle) {
            $message = 'Vehicle not found.';
        } elseif ($vehicle['customer_id'] != $customer_id) {
            $message = 'Vehicle does not belong to this customer.';
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
    <title>Book Service - Car Wash Pro</title>
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
                <h2>Book Service</h2>
                <?php if ($customer && $vehicle): ?>
                    <p>Book a service for <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>'s vehicle.</p>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($customer && $vehicle): ?>
                    <!-- Customer and Vehicle Information -->
                    <div class="customer-info">
                        <h3>Service Details</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Customer Name:</span>
                                <span class="value"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Vehicle:</span>
                                <span class="value"><?php echo htmlspecialchars($vehicle['plate_number']); ?> (<?php echo htmlspecialchars($vehicle['vehicle_type_name']); ?>)</span>
                            </div>
                            <?php if ($vehicle['brand']): ?>
                                <div class="info-item">
                                    <span class="label">Brand:</span>
                                    <span class="value"><?php echo htmlspecialchars($vehicle['brand']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($vehicle['model']): ?>
                                <div class="info-item">
                                    <span class="label">Model:</span>
                                    <span class="value"><?php echo htmlspecialchars($vehicle['model']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($vehicle['color']): ?>
                                <div class="info-item">
                                    <span class="label">Color:</span>
                                    <span class="value"><?php echo htmlspecialchars($vehicle['color']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Service Booking Form -->
                    <div class="form-container">
                        <form method="POST" action="../register.php">
                            <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                            <input type="hidden" name="service_only" value="1">

                            <div class="form-group">
                                <label for="service_type_id">Select Service *</label>
                                <select id="service_type_id" name="service_type_id" required>
                                    <option value="">Choose a service</option>
                                    <?php
                                    $serviceClass = new Service();
                                    $services = $serviceClass->getAllServiceTypes();
                                    usort($services, function($a, $b) {
                                        return $a['price'] <=> $b['price'];
                                    });
                                    foreach ($services as $service): ?>
                                        <option value="<?php echo $service['id']; ?>"
                                                title="<?php echo htmlspecialchars($service['description']); ?>">
                                            <?php echo htmlspecialchars($service['name']); ?> -
                                            ₱<?php echo number_format($service['price'], 2); ?> (<?php echo $service['duration_minutes']; ?> min)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Book Service</button>
                                <a href="search_customer.php?phone=<?php echo urlencode($customer['phone']); ?>" class="btn btn-secondary">Back to Customer</a>
                            </div>
                        </form>
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

    .form-container {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin: 2rem 0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
    }

    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-group select:focus {
        outline: none;
        border-color: #2196f3;
        box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
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

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }
    }
</style>
