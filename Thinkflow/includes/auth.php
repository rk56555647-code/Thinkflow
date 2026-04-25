<?php
/**
 * Authentication Helper Functions
 * Provides session-based authentication utilities
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login - redirects to login page if not authenticated
 */
function requireLogin($redirect = null) {
    if (!isLoggedIn()) {
        if ($redirect === null) {
            $redirect = '../auth/login.php';
        }
        header("Location: " . $redirect);
        exit();
    }
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require admin role
 */
function requireAdmin($redirect = null) {
    requireLogin($redirect);
    if (!isAdmin()) {
        http_response_code(403);
        die("Access denied. Admin privileges required.");
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Set session data after successful login
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'] ?? 'user';
    $_SESSION['profile_photo'] = $user['profile_photo'] ?? '';
}

/**
 * Destroy session and log out
 */
function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
?>
