<?php
/**
 * Logout Endpoint
 * GET / POST /backend/auth/logout.php
 */

require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();

clearSession();

sendResponse(true, 'Logged out successfully.', [
    'redirect' => 'login.html'
]);
