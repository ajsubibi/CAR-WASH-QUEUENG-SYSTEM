<?php
// Add vehicle page for existing customers
require_once '../config/session_config.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Vehicle.php';
require_once __DIR__ . '/Service.php';

$message = '';
$customer = null;

if (isset($_GET['customer_id'])) {
    $customer_id = intval($_GET['customer_id']);

    if (!empty($customer_id)) {
        $customerClass = new Customer();

        $customer = $customerClass->getById($customer_id);

        if (!$customer) {
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
    <title>Add Vehicle - Car Wash Pro</title>
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
                <h2>Add Vehicle</h2>
                <?php if ($customer): ?>
                    <p>Add a new vehicle for <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>.</p>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="info-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($customer): ?>
                    <!-- Add Vehicle Form -->
                    <div class="form-container">
                        <form method="POST" action="../register.php">
                            <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                            <input type="hidden" name="add_vehicle_only" value="1">
                            <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>">

                            <div class="form-group">
                                <label for="vehicle_type_id">Vehicle Type *</label>
                                <select id="vehicle_type_id" name="vehicle_type_id" required>
                                    <option value="">Select vehicle type</option>
                                    <?php
                                    $vehicleClass = new Vehicle();
                                    $vehicleTypes = $vehicleClass->getAllVehicleTypes();
                                    foreach ($vehicleTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>">
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="plate_number">Plate Number *</label>
                                <input type="text" id="plate_number" name="plate_number" required
                                       placeholder="ABC 123" style="text-transform: uppercase;">
                            </div>

                            <div class="form-group">
                                <label for="brand">Brand</label>
                                <input type="text" id="brand" name="brand" placeholder="e.g., Toyota">
                            </div>

                            <div class="form-group">
                                <label for="model">Model</label>
                                <input type="text" id="model" name="model" placeholder="e.g., Camry">
                            </div>

                            <div class="form-group">
                                <label for="color">Color</label>
                                <input type="text" id="color" name="color" placeholder="e.g., White">
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Add Vehicle</button>
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

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #2196f3;
        box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
    }

    .readonly-field {
        background-color: #f5f5f5;
        cursor: not-allowed;
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
