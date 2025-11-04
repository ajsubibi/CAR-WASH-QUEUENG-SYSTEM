<?php
// Admin manage staff
require_once '../config/session_config.php';
require_once '../classes/User.php';

requireRole('admin');

$user = new User();
$staff = $user->getByRole('staff');
$admins = $user->getByRole('admin');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_user') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        // Validation
        if (empty($username) || empty($password) || empty($firstName) || empty($lastName)) {
            $error = 'Please fill in all required fields.';
        } elseif ($user->usernameExists($username)) {
            $error = 'Username already exists.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            try {
                $user->create([
                    'username' => $username,
                    'password' => $password,
                    'role' => $role,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone
                ]);
                $message = ucfirst($role) . ' added successfully.';
                // Refresh lists
                $staff = $user->getByRole('staff');
                $admins = $user->getByRole('admin');
            } catch (Exception $e) {
                $error = 'Failed to add user: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update_user') {
        $userId = (int)$_POST['user_id'];
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($firstName) || empty($lastName)) {
            $error = 'First name and last name are required.';
        } else {
            try {
                $updateData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone
                ];

                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $error = 'Password must be at least 6 characters long.';
                    } else {
                        $updateData['password'] = $password;
                    }
                }

                if (!$error) {
                    $user->update($userId, $updateData);
                    $message = 'User updated successfully.';
                    // Refresh lists
                    $staff = $user->getByRole('staff');
                    $admins = $user->getByRole('admin');
                }
            } catch (Exception $e) {
                $error = 'Failed to update user: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_user') {
        $userId = (int)$_POST['user_id'];

        // Prevent deleting yourself
        if ($userId == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            try {
                $user->delete($userId);
                $message = 'User deleted successfully.';
                // Refresh lists
                $staff = $user->getByRole('staff');
                $admins = $user->getByRole('admin');
            } catch (Exception $e) {
                $error = 'Failed to delete user: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Car Wash Pro Admin</title>
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
                <h2>Manage Staff & Admins</h2>

                <?php if ($message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Add New User Form -->
                <div class="form-container">
                    <h3>Add New User</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_user">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" id="password" name="password" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label for="role">Role *</label>
                                <select id="role" name="role" required>
                                    <option value="staff">Staff</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </form>
                </div>

                <!-- Staff List -->
                <div class="management-section">
                    <h3>Staff Members</h3>
                    <?php if (empty($staff)): ?>
                        <p>No staff members found.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staff as $member): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($member['username']); ?></td>
                                            <td><?php echo htmlspecialchars($member['email'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($member['phone'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($member['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-secondary btn-small"
                                                        onclick="editUser(<?php echo $member['id']; ?>, '<?php echo addslashes($member['first_name']); ?>', '<?php echo addslashes($member['last_name']); ?>', '<?php echo addslashes($member['email'] ?? ''); ?>', '<?php echo addslashes($member['phone'] ?? ''); ?>')">Edit</button>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                                    <button type="submit" class="btn btn-secondary btn-small"
                                                            onclick="return confirm('Delete this staff member?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Admin List -->
                <div class="management-section">
                    <h3>Admin Users</h3>
                    <?php if (empty($admins)): ?>
                        <p>No admin users found.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                            <td><?php echo htmlspecialchars($admin['email'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($admin['phone'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-secondary btn-small"
                                                        onclick="editUser(<?php echo $admin['id']; ?>, '<?php echo addslashes($admin['first_name']); ?>', '<?php echo addslashes($admin['last_name']); ?>', '<?php echo addslashes($admin['email'] ?? ''); ?>', '<?php echo addslashes($admin['phone'] ?? ''); ?>')">Edit</button>
                                                <?php if ($admin['id'] != $_SESSION['user_id']): ?>
                                                    <form method="POST" action="" style="display: inline;">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_id" value="<?php echo $admin['id']; ?>">
                                                        <button type="submit" class="btn btn-secondary btn-small"
                                                                onclick="return confirm('Delete this admin?')">Delete</button>
                                                    </form>
                                                <?php endif; ?>
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
    </main>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Edit User</h4>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_first_name">First Name *</label>
                        <input type="text" id="edit_first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name *</label>
                        <input type="text" id="edit_last_name" name="last_name" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="tel" id="edit_phone" name="phone">
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="edit_password" name="password" minlength="6">
                </div>
                <div class="form-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editUser(id, firstName, lastName, email, phone) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('editUserModal').style.display = 'block';
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
