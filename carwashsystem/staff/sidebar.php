<div class="sidebar">
    <div class="sidebar-header">
        <h3>Staff Panel</h3>
        <div class="profile-dropdown">
            <img src="../assets/images/car1.jpg" alt="Profile" class="profile-pic">
            <div class="dropdown-menu">
                <a href="settings.php">⚙️ Settings</a>
                <a href="../login/logout.php">Logout</a>
            </div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="icon-dashboard"></i> Dashboard
        </a>
        <a href="active_queue.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'active_queue.php' ? 'active' : ''; ?>">
            <i class="icon-queue"></i> Active Queue
        </a>
        <a href="staff_control_panel.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'staff_control_panel.php' ? 'active' : ''; ?>">
            <i class="icon-control"></i> Control Panel
        </a>
        <a href="staff_history.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'staff_history.php' ? 'active' : ''; ?>">
            <i class="icon-history"></i> Service History
        </a>
        <a href="notifications.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) === 'notifications.php' ? 'active' : ''; ?>">
            <i class="icon-notifications"></i> Notifications
        </a>
        <a href="../login/logout.php" class="sidebar-link">
            <i class="icon-logout"></i> Logout
        </a>
    </nav>
</div>
