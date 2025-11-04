<?php
require_once '../config/session_config.php';
require_once '../classes/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'main_admin':
        case 'admin':
            header('Location: ../admin/dashboard.php');
            break;
        case 'staff':
            header('Location: ../staff/dashboard.php');
            break;
    }
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $user = new User();
        $authUser = $user->authenticate($username, $password);

        if ($authUser) {
            $_SESSION['user_id'] = $authUser['id'];
            $_SESSION['username'] = $authUser['username'];
            $_SESSION['role'] = $authUser['role'];
            $_SESSION['first_name'] = $authUser['first_name'];
            $_SESSION['last_name'] = $authUser['last_name'];

            // Redirect based on role
            switch ($authUser['role']) {
                case 'main_admin':
                case 'admin':
                    header('Location: ../admin/dashboard.php');
                    break;
                case 'staff':
                    header('Location: ../staff/dashboard.php');
                    break;
            }
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Wash Pro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="login_styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Car Wash Pro</h1>
            <p>Staff & Admin Login</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" class="login-form">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <div class="login-footer">
            <a href="../index.php">← Back to Public Site</a>
        </div>
    </div>
</body>
</html>
