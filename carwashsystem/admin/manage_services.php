<?php
// Admin manage services
require_once '../config/session_config.php';
require_once '../classes/Service.php';

requireRole('admin');

$service = new Service();
$services = $service->getAllServiceTypes();
$vehicleTypes = $service->getAllVehicleTypes();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_service') {
        try {
            $service->createServiceType([
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'duration_minutes' => (int)$_POST['duration_minutes'],
                'price' => (float)$_POST['price']
            ]);
            $message = 'Service type added successfully.';
            $services = $service->getAllServiceTypes(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to add service type: ' . $e->getMessage();
        }
    } elseif ($action === 'update_service') {
        try {
            $service->updateServiceType((int)$_POST['service_id'], [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'duration_minutes' => (int)$_POST['duration_minutes'],
                'price' => (float)$_POST['price']
            ]);
            $message = 'Service type updated successfully.';
            $services = $service->getAllServiceTypes(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to update service type: ' . $e->getMessage();
        }
    } elseif ($action === 'delete_service') {
        try {
            $service->deleteServiceType((int)$_POST['service_id']);
            $message = 'Service type deleted successfully.';
            $services = $service->getAllServiceTypes(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to delete service type: ' . $e->getMessage();
        }
    } elseif ($action === 'add_vehicle_type') {
        try {
            $service->createVehicleType([
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'])
            ]);
            $message = 'Vehicle type added successfully.';
            $vehicleTypes = $service->getAllVehicleTypes(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to add vehicle type: ' . $e->getMessage();
        }
    } elseif ($action === 'update_vehicle_type') {
        try {
            $service->updateVehicleType((int)$_POST['vehicle_type_id'], [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'])
            ]);
            $message = 'Vehicle type updated successfully.';
            $vehicleTypes = $service->getAllVehicleTypes(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to update vehicle type: ' . $e->getMessage();
        }
    } elseif ($action === 'delete_vehicle_type') {
        try {
            $service->deleteVehicleType((int)$_POST['vehicle_type_id']);
            $message = 'Vehicle type deleted successfully.';
            $vehicleTypes = $service->getAllVehicleTypes(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to delete vehicle type: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Car Wash Pro Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1 class="logo">Car Wash Pro - Admin</h1>
            <nav class="nav">
                <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
                <a href="../login/logout.php" class="btn btn-secondary">Logout</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="admin-dashboard">
                <h2>Manage Services & Vehicle Types</h2>

                <?php if ($message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Service Types Management -->
                <div class="management-section">
                    <h3>Service Types</h3>

                    <!-- Add Service Type Form -->
                    <div class="form-container">
                        <h4>Add New Service Type</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_service">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="service_name">Name *</label>
                                    <input type="text" id="service_name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="service_duration">Duration (minutes) *</label>
                                    <input type="number" id="service_duration" name="duration_minutes" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label for="service_price">Price (₱) *</label>
                                    <input type="number" id="service_price" name="price" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="service_description">Description</label>
                                <textarea id="service_description" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Service Type</button>
                        </form>
                    </div>

                    <!-- Service Types List -->
                    <div class="items-list">
                        <h4>Existing Service Types</h4>
                        <?php if (empty($services)): ?>
                            <p>No service types found.</p>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Duration</th>
                                            <th>Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($services as $svc): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($svc['name']); ?></td>
                                                <td><?php echo htmlspecialchars($svc['description'] ?? 'N/A'); ?></td>
                                                <td><?php echo $svc['duration_minutes']; ?> min</td>
                                                <td>₱<?php echo number_format($svc['price'], 2); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-secondary btn-small"
                                                            onclick="editService(<?php echo $svc['id']; ?>, '<?php echo addslashes($svc['name']); ?>', '<?php echo addslashes($svc['description'] ?? ''); ?>', <?php echo $svc['duration_minutes']; ?>, <?php echo $svc['price']; ?>)">Edit</button>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_service">
                                                        <input type="hidden" name="service_id" value="<?php echo $svc['id']; ?>">
                                                        <button type="submit" class="btn btn-secondary btn-small"
                                                                onclick="return confirm('Delete this service type?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Vehicle Types Management -->
                <div class="management-section">
                    <h3>Vehicle Types</h3>

                    <!-- Add Vehicle Type Form -->
                    <div class="form-container">
                        <h4>Add New Vehicle Type</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_vehicle_type">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="vehicle_name">Name *</label>
                                    <input type="text" id="vehicle_name" name="name" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vehicle_description">Description</label>
                                <textarea id="vehicle_description" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Vehicle Type</button>
                        </form>
                    </div>

                    <!-- Vehicle Types List -->
                    <div class="items-list">
                        <h4>Existing Vehicle Types</h4>
                        <?php if (empty($vehicleTypes)): ?>
                            <p>No vehicle types found.</p>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vehicleTypes as $vt): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($vt['name']); ?></td>
                                                <td><?php echo htmlspecialchars($vt['description'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-secondary btn-small"
                                                            onclick="editVehicleType(<?php echo $vt['id']; ?>, '<?php echo addslashes($vt['name']); ?>', '<?php echo addslashes($vt['description'] ?? ''); ?>')">Edit</button>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_vehicle_type">
                                                        <input type="hidden" name="vehicle_type_id" value="<?php echo $vt['id']; ?>">
                                                        <button type="submit" class="btn btn-secondary btn-small"
                                                                onclick="return confirm('Delete this vehicle type?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Modals (Hidden by default) -->
    <div id="editServiceModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Edit Service Type</h4>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_service">
                <input type="hidden" id="edit_service_id" name="service_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_service_name">Name *</label>
                        <input type="text" id="edit_service_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_service_duration">Duration (minutes) *</label>
                        <input type="number" id="edit_service_duration" name="duration_minutes" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_service_price">Price (₱) *</label>
                        <input type="number" id="edit_service_price" name="price" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_service_description">Description</label>
                    <textarea id="edit_service_description" name="description" rows="3"></textarea>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editServiceModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Service Type</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editVehicleModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Edit Vehicle Type</h4>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_vehicle_type">
                <input type="hidden" id="edit_vehicle_type_id" name="vehicle_type_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_vehicle_name">Name *</label>
                        <input type="text" id="edit_vehicle_name" name="name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_vehicle_description">Description</label>
                    <textarea id="edit_vehicle_description" name="description" rows="3"></textarea>
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editVehicleModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Vehicle Type</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editService(id, name, description, duration, price) {
            document.getElementById('edit_service_id').value = id;
            document.getElementById('edit_service_name').value = name;
            document.getElementById('edit_service_description').value = description;
            document.getElementById('edit_service_duration').value = duration;
            document.getElementById('edit_service_price').value = price;
            document.getElementById('editServiceModal').style.display = 'block';
        }

        function editVehicleType(id, name, description) {
            document.getElementById('edit_vehicle_type_id').value = id;
            document.getElementById('edit_vehicle_name').value = name;
            document.getElementById('edit_vehicle_description').value = description;
            document.getElementById('editVehicleModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
    </script>

    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
        }

        .management-section {
            margin-bottom: 3rem;
        }

        .items-list {
            margin-top: 2rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .admin-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
</body>
</html>
