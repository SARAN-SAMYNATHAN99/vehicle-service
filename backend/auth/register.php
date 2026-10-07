<?php
/**
 * User Registration Endpoint
 * POST /backend/auth/register.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed. Use POST.', null, 405);
}

$input = getRequestData();

$name     = cleanInput($input['name'] ?? '');
$email    = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone    = cleanInput($input['phone'] ?? '');
$password = $input['password'] ?? '';
$confirm  = $input['confirm_password'] ?? '';

// Validation
if (empty($name) || strlen($name) < 2) {
    sendResponse(false, 'Please enter a valid full name (minimum 2 characters).', null, 400);
}

if (!$email) {
    sendResponse(false, 'Please provide a valid email address.', null, 400);
}

if (empty($phone) || strlen($phone) < 7) {
    sendResponse(false, 'Please provide a valid phone number (minimum 7 digits).', null, 400);
}

if (empty($password) || strlen($password) < 6) {
    sendResponse(false, 'Password must be at least 6 characters long.', null, 400);
}

if ($password !== $confirm) {
    sendResponse(false, 'Password and confirmation password do not match.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Check if email already registered
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        sendResponse(false, 'An account with this email address already exists. Please login instead.', null, 409);
    }

    // Securely hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user
    $insert = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (:name, :email, :phone, :password, 'customer')");
    $insert->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':phone'    => $phone,
        ':password' => $hashedPassword
    ]);

    $userId = (int)$pdo->lastInsertId();

    $userData = [
        'id'    => $userId,
        'name'  => $name,
        'email' => $email,
        'phone' => $phone,
        'role'  => 'customer'
    ];

    // Auto-login newly registered user
    setSessionUser($userData);

    sendResponse(true, 'Registration successful! Welcome to GearShift Services.', [
        'user'     => $userData,
        'redirect' => 'dashboard.html'
    ], 201);

} catch (PDOException $e) {
    sendResponse(false, 'Database error during registration: ' . $e->getMessage(), null, 500);
}
