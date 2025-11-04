<?php
// Admin display settings page
require_once '../config/session_config.php';
require_once '../classes/Service.php';

requireRole(['admin', 'main_admin']);

$service = new Service();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_display') {
        $title = trim($_POST['public_display_title']);
        $refreshInterval = (int)$_POST['auto_refresh_interval'];

        if (empty($title)) {
            $error = 'Display title cannot be empty.';
        } elseif ($refreshInterval < 10 || $refreshInterval > 300) {
            $error = 'Auto-refresh interval must be between 10 and 300 seconds.';
        } else {
            try {
                $pdo = getDB();
                $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'public_display_title'")->execute([$title]);
                $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'auto_refresh_interval'")->execute([$refreshInterval]);
                $message = 'Display settings updated successfully.';
            } catch (Exception $e) {
                $error = 'Failed to update display settings: ' . $e->getMessage();
            }
        }
    }
}

// Get current settings
$pdo = getDB();
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('public_display_title', 'auto_refresh_interval')");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Display Settings - Car Wash Pro</title>
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
                <h2>Public Display Settings</h2>

                <?php if ($message): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="form-container">
                    <h3>Configure Public Queue Display</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_display">
                        <div class="form-group">
                            <label for="public_display_title">Display Title *</label>
                            <input type="text" id="public_display_title" name="public_display_title"
                                   value="<?php echo htmlspecialchars($settings['public_display_title'] ?? 'Car Wash Pro Queue'); ?>" required>
                            <small>This title will be shown on the public queue display page.</small>
                        </div>
                        <div class="form-group">
                            <label for="auto_refresh_interval">Auto-Refresh Interval (seconds) *</label>
                            <input type="number" id="auto_refresh_interval" name="auto_refresh_interval"
                                   value="<?php echo htmlspecialchars($settings['auto_refresh_interval'] ?? '30'); ?>"
                                   min="10" max="300" required>
                            <small>How often the public queue display should refresh (10-300 seconds).</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Display Settings</button>
                    </form>
                </div>

                <div class="preview-section">
                    <h3>Preview</h3>
                    <div class="display-preview">
                        <div class="preview-header">
                            <h4><?php echo htmlspecialchars($settings['public_display_title'] ?? 'Car Wash Pro Queue'); ?></h4>
                        </div>
                        <div class="preview-content">
                            <p>Current queue will be displayed here with auto-refresh every <?php echo htmlspecialchars($settings['auto_refresh_interval'] ?? '30'); ?> seconds.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        .preview-section {
            margin-top: 3rem;
        }

        .display-preview {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            max-width: 600px;
            margin: 0 auto;
        }

        .preview-header {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        .preview-header h4 {
            margin: 0;
            font-size: 1.5rem;
        }

        .preview-content {
            padding: 2rem;
            background: white;
            text-align: center;
            color: #666;
        }
    </style>
</body>
</html>
