<?php
// Session configuration and security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Function to check user role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login/login.php');
        exit();
    }
}

// Function to require specific role
function requireRole($role) {
    requireLogin();
    if (is_array($role)) {
        $hasAccess = false;
        foreach ($role as $r) {
            if (hasRole($r)) {
                $hasAccess = true;
                break;
            }
        }
        if (!$hasAccess) {
            header('Location: ../login/login.php?error=access_denied');
            exit();
        }
    } elseif (!hasRole($role)) {
        header('Location: ../login/login.php?error=access_denied');
        exit();
    }
}

// Function to get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name']
    ];
}

// Function to logout
function logout() {
    session_unset();
    session_destroy();
    header('Location: ../login/login.php');
    exit();
}
?>
