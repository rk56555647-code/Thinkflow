<?php
/**
 * CSRF Protection Functions
 * Generates and validates CSRF tokens for form security
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF token and store in session
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Validate the CSRF token from a form submission
 */
function validateCsrfToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Validate CSRF and die on failure
 */
function requireValidCsrf() {
    if (!validateCsrfToken()) {
        http_response_code(403);
        die("Invalid CSRF token. Please refresh the page and try again.");
    }
}
?>
