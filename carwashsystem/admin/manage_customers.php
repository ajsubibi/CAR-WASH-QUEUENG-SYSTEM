<?php
// Admin manage customers page
require_once '../config/session_config.php';
require_once '../classes/Customer.php';
require_once '../classes/Vehicle.php';
require_once '../classes/Service.php';

requireRole(['admin', 'main_admin']);

$customerClass = new Customer();
$vehicleClass = new Vehicle();
$serviceClass = new Service();

$message = '';
$error = '';
$customers = $customerClass->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_customer') {
        $customerId = (int)$_POST['customer_id'];
        try {
            $customerClass->delete($customerId);
            $message = 'Customer deleted successfully.';
            $customers = $customerClass->getAll(); // Refresh list
        } catch (Exception $e) {
            $error = 'Failed to delete customer: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - Car Wash Pro Admin</title>
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
                <h2>Manage Customers</h2>

                <?php if ($message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (empty($customers)): ?>
                    <p>No customers found.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Vehicles</th>
                                    <th>Total Services</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $vehicles = $vehicleClass->getByCustomerId($customer['id']);
                                            echo count($vehicles);
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $history = $serviceClass->getCustomerServiceHistory($customer['id']);
                                            echo count($history);
                                            ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-secondary btn-small"
                                                    onclick="viewCustomerDetails(<?php echo $customer['id']; ?>, '<?php echo addslashes($customer['first_name']); ?>', '<?php echo addslashes($customer['last_name']); ?>', '<?php echo addslashes($customer['phone']); ?>', '<?php echo addslashes($customer['email'] ?? ''); ?>')">View Details</button>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_customer">
                                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                                <button type="submit" class="btn btn-secondary btn-small"
                                                        onclick="return confirm('Delete this customer and all their data?')">Delete</button>
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
    </main>

    <!-- Customer Details Modal -->
    <div id="customerDetailsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Customer Details</h4>
            <div id="customerDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn btn-secondary" onclick="closeModal('customerDetailsModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        function viewCustomerDetails(customerId, firstName, lastName, phone, email) {
            // This would typically fetch detailed customer info via AJAX
            // For now, show basic info
            const content = `
                <div class="customer-detail-info">
                    <p><strong>Name:</strong> ${firstName} ${lastName}</p>
                    <p><strong>Phone:</strong> ${phone}</p>
                    <p><strong>Email:</strong> ${email || 'N/A'}</p>
                    <p><em>Detailed vehicle and service history would be loaded here via AJAX.</em></p>
                </div>
            `;
            document.getElementById('customerDetailsContent').innerHTML = content;
            document.getElementById('customerDetailsModal').style.display = 'block';
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
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-buttons {
            margin-top: 1.5rem;
            text-align: right;
        }

        .customer-detail-info p {
            margin: 0.5rem 0;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .customer-detail-info p:last-child {
            border-bottom: none;
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
