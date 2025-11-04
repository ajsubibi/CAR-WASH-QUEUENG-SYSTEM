<?php
// Staff settings page
require_once '../config/session_config.php';

requireRole('staff');

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
    <title>Settings - Car Wash Pro Staff</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <div class="staff-layout">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <div class="container">
                <div class="staff-dashboard">
                <h2>Account Settings</h2>

                <?php if ($message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Profile Settings -->
                <div class="modern-form-container">
                    <h3 class="form-title">Profile Information</h3>
                    <form method="POST" action="" class="modern-form">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-grid">
                            <div class="input-group">
                                <label for="first_name" class="input-label">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="modern-input"
                                       value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                            </div>
                            <div class="input-group">
                                <label for="last_name" class="input-label">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="modern-input"
                                       value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                            </div>
                            <div class="input-group">
                                <label for="email" class="input-label">Email</label>
                                <input type="email" id="email" name="email" class="modern-input"
                                       value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>">
                            </div>
                            <div class="input-group">
                                <label for="phone" class="input-label">Phone</label>
                                <input type="tel" id="phone" name="phone" class="modern-input"
                                       value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Username</label>
                            <input type="text" class="modern-input readonly" value="<?php echo htmlspecialchars($currentUser['username']); ?>" readonly>
                            <small class="input-hint">Username cannot be changed</small>
                        </div>
                        <button type="submit" class="modern-btn primary">Update Profile</button>
                    </form>
                </div>

                <!-- Password Change -->
                <div class="modern-form-container">
                    <h3 class="form-title">Change Password</h3>
                    <form method="POST" action="" class="modern-form">
                        <input type="hidden" name="action" value="change_password">
                        <div class="input-group">
                            <label for="current_password" class="input-label">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" class="modern-input" required>
                        </div>
                        <div class="form-grid">
                            <div class="input-group">
                                <label for="new_password" class="input-label">New Password *</label>
                                <input type="password" id="new_password" name="new_password" class="modern-input" required minlength="6">
                            </div>
                            <div class="input-group">
                                <label for="confirm_password" class="input-label">Confirm New Password *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="modern-input" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="modern-btn primary">Change Password</button>
                    </form>
                </div>

                <!-- Account Information -->
                <div class="info-section">
                    <h3>Account Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Role:</span>
                            <span class="value">Staff</span>
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
