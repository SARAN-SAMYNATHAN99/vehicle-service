<?php
/**
 * User Login Endpoint
 * POST /backend/auth/login.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed. Use POST.', null, 405);
}

$input = getRequestData();

$email    = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $input['password'] ?? '';

if (!$email || empty($password)) {
    sendResponse(false, 'Please provide both valid email and password.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT id, name, email, phone, password, role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        sendResponse(false, 'Invalid email or password credentials.', null, 401);
    }

    // Set authenticated session
    setSessionUser($user);

    $redirectUrl = ($user['role'] === 'admin') ? 'admin/dashboard.html' : 'dashboard.html';

    sendResponse(true, 'Login successful! Redirecting...', [
        'user' => [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
            'role'  => $user['role']
        ],
        'redirect' => $redirectUrl
    ]);

} catch (PDOException $e) {
    sendResponse(false, 'Database error during authentication: ' . $e->getMessage(), null, 500);
}
