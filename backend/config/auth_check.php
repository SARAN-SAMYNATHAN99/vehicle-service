<?php
/**
 * Authentication and Authorization Guard
 * Vehicle Service Booking System
 */

require_once __DIR__ . '/response.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Get the current authenticated user's session data
 */
function getCurrentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Require any authenticated user (Customer or Admin)
 */
function requireAuth(): array {
    if (!isLoggedIn()) {
        sendResponse(false, 'Unauthorized. Please login to continue.', null, 401);
    }
    return $_SESSION['user'];
}

/**
 * Require an authenticated administrator
 */
function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        sendResponse(false, 'Forbidden. Administrator privileges required.', null, 403);
    }
    return $user;
}

/**
 * Set session user payload upon successful login
 */
function setSessionUser(array $user): void {
    // Avoid storing password hash in session
    unset($user['password']);
    $_SESSION['user'] = $user;
}

/**
 * Clear user session on logout
 */
function clearSession(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
