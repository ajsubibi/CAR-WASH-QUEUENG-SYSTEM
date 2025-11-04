<?php
// Customer registration form
require_once 'config/session_config.php';
require_once 'classes/Service.php';
require_once 'classes/Customer.php';
require_once 'classes/Vehicle.php';

$service = new Service();
$vehicleTypes = $service->getAllVehicleTypes();
$services = $service->getAllServiceTypes();

// Sort services by price (cheap to expensive)
usort($services, function($a, $b) {
    return $a['price'] <=> $b['price'];
});

$errors = [];
$formData = [];
$existingCustomer = null;

// Check if customer_id is provided (from My Record page)
if (isset($_GET['customer_id'])) {
    $customerClass = new Customer();
    $existingCustomer = $customerClass->getById((int)$_GET['customer_id']);
    if ($existingCustomer) {
        // Pre-fill customer information
        $formData = [
            'first_name' => $existingCustomer['first_name'],
            'last_name' => $existingCustomer['last_name'],
            'phone' => $existingCustomer['phone'],
            'email' => $existingCustomer['email'] ?? ''
        ];
    }
}

// Pre-fill form with session data if coming from review page
if (isset($_SESSION['registration_data'])) {
    $formData = $_SESSION['registration_data'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['home'])) {
        // Clear all session data and redirect to home
        unset($_SESSION['registration_data']);
        unset($_SESSION['in_review_process']);
        header('Location: index.php');
        exit();
    }

    // Handle add vehicle only (from My Record page)
    if (isset($_POST['add_vehicle_only'])) {
        $customer_id = (int)($_POST['customer_id'] ?? 0);
        $vehicleData = [
            'customer_id' => $customer_id,
            'plate_number' => trim($_POST['plate_number'] ?? ''),
            'vehicle_type_id' => (int)($_POST['vehicle_type_id'] ?? 0),
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'color' => trim($_POST['color'] ?? '')
        ];

        // Validation for vehicle only
        if (empty($vehicleData['plate_number'])) $errors['plate_number'] = 'Plate number is required.';
        if ($vehicleData['vehicle_type_id'] <= 0) $errors['vehicle_type_id'] = 'Please select a vehicle type.';
        if (empty($vehicleData['brand'])) $errors['brand'] = 'Brand is required.';
        if (empty($vehicleData['model'])) $errors['model'] = 'Model is required.';
        if (empty($vehicleData['color'])) $errors['color'] = 'Color is required.';

        if (empty($errors)) {
            $vehicleClass = new Vehicle();
            // Check if vehicle already exists
            $existingVehicle = $vehicleClass->getByPlateNumber($vehicleData['plate_number']);

            if ($existingVehicle && $existingVehicle['customer_id'] == $customer_id) {
                $errors['general'] = 'This vehicle is already registered to this customer.';
            } else {
                // Create the vehicle
                if ($vehicleClass->create($vehicleData)) {
                    header('Location: classes/search_customer.php?phone=' . urlencode($_POST['customer_phone'] ?? ''));
                    exit();
                } else {
                    $errors['general'] = 'Failed to add vehicle. Please try again.';
                }
            }
        }
    } else {
        // Normal registration flow
        // Validate and sanitize input
        $formData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'plate_number' => trim($_POST['plate_number'] ?? ''),
            'vehicle_type_id' => (int)($_POST['vehicle_type_id'] ?? 0),
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'color' => trim($_POST['color'] ?? ''),
            'service_type_id' => (int)($_POST['service_type_id'] ?? 0)
        ];

        // Validation
        if (empty($formData['first_name'])) $errors['first_name'] = 'First name is required.';
        if (empty($formData['last_name'])) $errors['last_name'] = 'Last name is required.';
        if (empty($formData['phone']) || !preg_match('/^\+?[\d\s\-\(\)]+$/', $formData['phone'])) {
            $errors['phone'] = 'Valid phone number is required.';
        }
        if (empty($formData['plate_number'])) $errors['plate_number'] = 'Plate number is required.';
        if ($formData['vehicle_type_id'] <= 0) $errors['vehicle_type_id'] = 'Please select a vehicle type.';
        if (empty($formData['brand'])) $errors['brand'] = 'Brand is required.';
        if (empty($formData['model'])) $errors['model'] = 'Model is required.';
        if (empty($formData['color'])) $errors['color'] = 'Color is required.';
        if ($formData['service_type_id'] <= 0) $errors['service_type_id'] = 'Please select a service.';

        // Check if customer exists by phone
        $customerClass = new Customer();
        $existingCustomer = $customerClass->getByPhone($formData['phone']);

        if ($existingCustomer) {
            // Check if vehicle exists
            $vehicleClass = new Vehicle();
            $existingVehicle = $vehicleClass->getByPlateNumber($formData['plate_number']);

            if ($existingVehicle && $existingVehicle['customer_id'] == $existingCustomer['id']) {
                $errors['general'] = 'This vehicle is already registered. Please check your records.';
            }
        }

        if (empty($errors)) {
            // Store in session and redirect to review
            $_SESSION['registration_data'] = $formData;
            header('Location: review.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Service - Car Wash Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script>
        function confirmHomeNavigation() {
            <?php if (isset($_SESSION['registration_data'])): ?>
                return confirm('Are you sure you want to go back to home? All entered information will be lost.');
            <?php endif; ?>
            return true;
        }
    </script>
</head>
<body style="background-image: url('assets/images/Mercedes.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;">
    <header class="header header-relative" style="background: transparent;">
        <div class="container">
            <nav class="nav">
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" name="home" onclick="return confirmHomeNavigation()" class="btn btn-secondary">← Home</button>
                </form>
                <?php if (!isset($_SESSION['registration_data'])): ?>
                    <a href="queue.php" class="btn btn-secondary">View Queue</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="form-container" style="margin-top: -10px;">
                <h2><?php echo $existingCustomer ? 'Register New Vehicle' : 'Customer Registration'; ?></h2>

                <?php if (isset($errors['general'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($errors['general']); ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="registration-form">
                    <!-- Customer Information -->
                    <div class="form-section">
                        <h3>Customer Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required
                                       value="<?php echo htmlspecialchars($formData['first_name'] ?? ''); ?>">
                                <?php if (isset($errors['first_name'])): ?>
                                    <span class="error"><?php echo $errors['first_name']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required
                                       value="<?php echo htmlspecialchars($formData['last_name'] ?? ''); ?>">
                                <?php if (isset($errors['last_name'])): ?>
                                    <span class="error"><?php echo $errors['last_name']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" required
                                       pattern="[\+]?[\d\s\-\(\)]+" placeholder="+63 917 123 4567"
                                       value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>">
                                <?php if (isset($errors['phone'])): ?>
                                    <span class="error"><?php echo $errors['phone']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="email">Email (Optional)</label>
                                <input type="email" id="email" name="email"
                                       value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Information -->
                    <div class="form-section">
                        <h3>Vehicle Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="plate_number">License Plate Number *</label>
                                <input type="text" id="plate_number" name="plate_number" required
                                       value="<?php echo htmlspecialchars($formData['plate_number'] ?? ''); ?>">
                                <?php if (isset($errors['plate_number'])): ?>
                                    <span class="error"><?php echo $errors['plate_number']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="vehicle_type_id">Vehicle Type *</label>
                            <select id="vehicle_type_id" name="vehicle_type_id" required>
                                <option value="">Select Type</option>
                                <?php
                                $allowedVehicleTypes = ['Motorcycle', 'Sedan', 'SUV', 'Truck', 'Van'];
                                $filteredVehicleTypes = array_filter($vehicleTypes, function($type) use ($allowedVehicleTypes) {
                                    return in_array($type['name'], $allowedVehicleTypes);
                                });
                                $uniqueVehicleTypes = [];
                                foreach ($filteredVehicleTypes as $type) {
                                    $uniqueVehicleTypes[$type['name']] = $type;
                                }
                                foreach ($uniqueVehicleTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>"
                                            <?php echo ($formData['vehicle_type_id'] ?? 0) == $type['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                                <?php if (isset($errors['vehicle_type_id'])): ?>
                                    <span class="error"><?php echo $errors['vehicle_type_id']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="brand">Brand *</label>
                                <input type="text" id="brand" name="brand" required
                                       value="<?php echo htmlspecialchars($formData['brand'] ?? ''); ?>">
                                <?php if (isset($errors['brand'])): ?>
                                    <span class="error"><?php echo $errors['brand']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="model">Model *</label>
                                <input type="text" id="model" name="model" required
                                       value="<?php echo htmlspecialchars($formData['model'] ?? ''); ?>">
                                <?php if (isset($errors['model'])): ?>
                                    <span class="error"><?php echo $errors['model']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="color">Color *</label>
                                <input type="text" id="color" name="color" required
                                       value="<?php echo htmlspecialchars($formData['color'] ?? ''); ?>">
                                <?php if (isset($errors['color'])): ?>
                                    <span class="error"><?php echo $errors['color']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Service Selection -->
                    <div class="form-section">
                        <h3>Service Selection</h3>
                        <div class="form-group">
                            <label for="service_type_id">Select Service *</label>
                            <select id="service_type_id" name="service_type_id" required>
                                <option value="">Choose a service</option>
                                <?php
                                $allowedServices = ['Quick Wash', 'Basic Wash', 'Full Service Wash', 'Deluxe Detail'];
                                $filteredServices = array_filter($services, function($service) use ($allowedServices) {
                                    return in_array($service['name'], $allowedServices);
                                });
                                $uniqueServices = [];
                                foreach ($filteredServices as $service) {
                                    $uniqueServices[$service['name']] = $service;
                                }
                                foreach ($uniqueServices as $service): ?>
                                    <option value="<?php echo $service['id']; ?>"
                                            title="<?php echo htmlspecialchars($service['description']); ?>"
                                            <?php echo ($formData['service_type_id'] ?? 0) == $service['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['name']); ?> -
                                        ₱<?php echo number_format($service['price'], 2); ?> (<?php echo $service['duration_minutes']; ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['service_type_id'])): ?>
                                <span class="error"><?php echo $errors['service_type_id']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Form Buttons -->
                    <div class="form-buttons">
                        <a href="classes/search_customer.php" class="btn btn-secondary">My Record</a>
                        <?php if (!isset($_SESSION['registration_data'])): ?>
                            <a href="queue.php" class="btn btn-secondary">View Queue</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
