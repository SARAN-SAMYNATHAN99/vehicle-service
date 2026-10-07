<?php
/**
 * Check Current User Session State
 * GET /backend/auth/me.php
 */

require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();

if (isLoggedIn()) {
    $user = getCurrentUser();
    sendResponse(true, 'User is authenticated.', [
        'authenticated' => true,
        'user' => [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role'  => $user['role']
        ]
    ]);
} else {
    sendResponse(false, 'User is not authenticated.', [
        'authenticated' => false,
        'user' => null
    ], 200); // 200 so clients can inspect status smoothly without catching 401 exceptions
}
