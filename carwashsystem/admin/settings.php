<?php
// Admin settings page
require_once '../config/session_config.php';

requireRole(['admin', 'main_admin']);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } else {
            try {
                $user = new User();
                if ($user->verifyPassword($_SESSION['user_id'], $currentPassword)) {
                    $user->update($_SESSION['user_id'], ['password' => $newPassword]);
                    $message = 'Password changed successfully.';
                } else {
                    $error = 'Current password is incorrect.';
                }
            } catch (Exception $e) {
                $error = 'Failed to change password: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update_profile') {
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($firstName) || empty($lastName)) {
            $error = 'First name and last name are required.';
        } else {
            try {
                $user = new User();
                $user->update($_SESSION['user_id'], [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone
                ]);
                $_SESSION['first_name'] = $firstName;
                $_SESSION['last_name'] = $lastName;
                $message = 'Profile updated successfully.';
            } catch (Exception $e) {
                $error = 'Failed to update profile: ' . $e->getMessage();
            }
        }
    }
}

// Get current user info
$user = new User();
$currentUser = $user->getById($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Car Wash Pro Admin</title>
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
                <h2>Account Settings</h2>

                <?php if ($message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Profile Settings -->
                <div class="form-container">
                    <h3>Profile Information</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name"
                                       value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name"
                                       value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email"
                                       value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($currentUser['username']); ?>" readonly>
                            <small>Username cannot be changed.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>

                <!-- Password Change -->
                <div class="form-container">
                    <h3>Change Password</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_password">
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password">New Password *</label>
                                <input type="password" id="new_password" name="new_password" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="info-section">
                    <h3>Account Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Role:</span>
                            <span class="value"><?php echo ucfirst($currentUser['role']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Account Created:</span>
                            <span class="value"><?php echo date('M j, Y', strtotime($currentUser['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Last Updated:</span>
                            <span class="value"><?php echo date('M j, Y', strtotime($currentUser['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .info-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
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
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .form-container h3 {
            margin-top: 0;
            color: #2196f3;
        }
    </style>
</body>
</html>
