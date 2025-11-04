<?php
// Review and confirm registration
require_once 'config/session_config.php';
require_once 'classes/Service.php';
require_once 'classes/Customer.php';
require_once 'classes/Vehicle.php';
require_once 'classes/Queue.php';

if (!isset($_SESSION['registration_data'])) {
    header('Location: register.php');
    exit();
}

// Mark that user has reached the review step
$_SESSION['in_review_process'] = true;

$formData = $_SESSION['registration_data'];
$service = new Service();
$vehicleTypes = $service->getAllVehicleTypes();
$services = $service->getAllServiceTypes();

$selectedVehicleType = null;
$selectedService = null;

foreach ($vehicleTypes as $type) {
    if ($type['id'] == $formData['vehicle_type_id']) {
        $selectedVehicleType = $type;
        break;
    }
}

foreach ($services as $svc) {
    if ($svc['id'] == $formData['service_type_id']) {
        $selectedService = $svc;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
    // Clear all session data and redirect to home
    unset($_SESSION['registration_data']);
    unset($_SESSION['in_review_process']);
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $customerClass = new Customer();
        $vehicleClass = new Vehicle();
        $queueClass = new Queue();

        // Check if customer exists
        $existingCustomer = $customerClass->getByPhone($formData['phone']);

        if (!$existingCustomer) {
            // Create new customer
            $customerId = $customerClass->create([
                'first_name' => $formData['first_name'],
                'last_name' => $formData['last_name'],
                'phone' => $formData['phone'],
                'email' => $formData['email']
            ]);
        } else {
            $customerId = $existingCustomer['id'];
        }

        // Check if vehicle exists
        $existingVehicle = $vehicleClass->getByPlateNumber($formData['plate_number']);

        if (!$existingVehicle || $existingVehicle['customer_id'] != $customerId) {
            // Create new vehicle
            $vehicleId = $vehicleClass->create([
                'customer_id' => $customerId,
                'plate_number' => $formData['plate_number'],
                'vehicle_type_id' => $formData['vehicle_type_id'],
                'brand' => $formData['brand'],
                'model' => $formData['model'],
                'color' => $formData['color']
            ]);
        } else {
            $vehicleId = $existingVehicle['id'];
        }

        // Add to queue
        $queueId = $queueClass->addToQueue($customerId, $vehicleId, $formData['service_type_id']);

        // Clear session data
        unset($_SESSION['registration_data']);

        // Redirect to queue view
        header('Location: queue.php?joined=1');
        exit();

    } catch (Exception $e) {
        $error = 'An error occurred while processing your request. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review & Confirm - Car Wash Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body style="background-image: url('assets/images/Mercedes.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;">
    <header class="header header-relative" style="background: transparent;">
        <div class="container">
            <nav class="nav">
                <a href="register.php" class="btn btn-secondary">← Back to Form</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="review-container" style="margin-top: -10px;">
                <h2>Review Your Information</h2>
                <p>Please review your details before confirming your booking.</p>

                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="review-sections">
                    <!-- Customer Information -->
                    <div class="review-section">
                        <h3>Customer Information</h3>
                        <div class="review-item">
                            <span class="label">Name:</span>
                            <span class="value"><?php echo htmlspecialchars($formData['first_name'] . ' ' . $formData['last_name']); ?></span>
                        </div>
                        <div class="review-item">
                            <span class="label">Phone:</span>
                            <span class="value"><?php echo htmlspecialchars($formData['phone']); ?></span>
                        </div>
                        <?php if (!empty($formData['email'])): ?>
                            <div class="review-item">
                                <span class="label">Email:</span>
                                <span class="value"><?php echo htmlspecialchars($formData['email']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Vehicle Information -->
                    <div class="review-section">
                        <h3>Vehicle Information</h3>
                        <div class="review-item">
                            <span class="label">License Plate:</span>
                            <span class="value"><?php echo htmlspecialchars($formData['plate_number']); ?></span>
                        </div>
                        <div class="review-item">
                            <span class="label">Vehicle Type:</span>
                            <span class="value"><?php echo htmlspecialchars($selectedVehicleType['name']); ?></span>
                        </div>
                        <?php if (!empty($formData['brand'])): ?>
                            <div class="review-item">
                                <span class="label">Brand:</span>
                                <span class="value"><?php echo htmlspecialchars($formData['brand']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($formData['model'])): ?>
                            <div class="review-item">
                                <span class="label">Model:</span>
                                <span class="value"><?php echo htmlspecialchars($formData['model']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($formData['color'])): ?>
                            <div class="review-item">
                                <span class="label">Color:</span>
                                <span class="value"><?php echo htmlspecialchars($formData['color']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Service Information -->
                    <div class="review-section">
                        <h3>Service Information</h3>
                        <div class="review-item">
                            <span class="label">Service:</span>
                            <span class="value"><?php echo htmlspecialchars($selectedService['name']); ?></span>
                        </div>
                        <div class="review-item">
                            <span class="label">Duration:</span>
                            <span class="value"><?php echo $selectedService['duration_minutes']; ?> minutes</span>
                        </div>
                        <div class="review-item">
                            <span class="label">Price:</span>
                            <span class="value">₱<?php echo number_format($selectedService['price'], 2); ?></span>
                        </div>
                        <div class="review-item">
                            <span class="label">Description:</span>
                            <span class="value"><?php echo htmlspecialchars($selectedService['description']); ?></span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="" class="confirm-form">
                    <button type="submit" name="confirm" class="btn btn-primary btn-large">Confirm and Join Queue</button>
                </form>

                <div class="review-actions">
                    <a href="register.php" class="btn btn-secondary">Edit Information</a>
                    <a href="queue.php" class="btn btn-secondary">View Current Queue</a>
                    <form method="POST" action="" style="display: inline;">
                        <button type="submit" name="cancel" class="btn btn-secondary" onclick="return confirm('Are you sure you want to cancel? All entered information will be lost.')">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
