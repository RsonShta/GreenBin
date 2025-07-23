<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Duration of session timeout in seconds
 */
define('SESSION_TIMEOUT', 1800); // 30 minutes

/**
 * Handles role-based access control and session timeout
 *
 * @param array $allowedRoles List of roles allowed to access the page
 * @param string $redirectPath Path to redirect if unauthorized
 */
function requireRole(array $allowedRoles, string $redirectPath = '/GreenBin/login')
{
    // Not logged in at all
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header("Location: $redirectPath");
        exit();
    }

    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header("Location: $redirectPath?timeout=1");
        exit();
    }

    $_SESSION['last_activity'] = time(); // Update last activity time

    // Role check
    if (!in_array($_SESSION['user_role'], $allowedRoles)) {
        header("Location: $redirectPath");
        exit();
    }
}
