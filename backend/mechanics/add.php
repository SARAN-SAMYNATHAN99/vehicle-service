<?php
/**
 * Add Mechanic Endpoint (Admin Only)
 * POST /backend/mechanics/add.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed. Use POST.', null, 405);
}

requireAdmin();

$input = getRequestData();
$name           = cleanInput($input['name'] ?? '');
$phone          = cleanInput($input['phone'] ?? '');
$specialization = cleanInput($input['specialization'] ?? '');
$availability   = cleanInput($input['availability'] ?? 'Available');

if (empty($name) || strlen($name) < 2) {
    sendResponse(false, 'Mechanic name is required.', null, 400);
}

if (empty($phone) || strlen($phone) < 7) {
    sendResponse(false, 'Valid contact phone number is required.', null, 400);
}

if (empty($specialization)) {
    sendResponse(false, 'Mechanic specialization is required.', null, 400);
}

$validAvailabilities = ['Available', 'Busy', 'On Leave'];
if (!in_array($availability, $validAvailabilities)) {
    $availability = 'Available';
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("INSERT INTO mechanics (name, phone, specialization, availability) 
                           VALUES (:name, :phone, :spec, :avail)");
    $stmt->execute([
        ':name'  => $name,
        ':phone' => $phone,
        ':spec'  => $specialization,
        ':avail' => $availability
    ]);

    $newId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Mechanic added successfully!', [
        'id'             => $newId,
        'name'           => $name,
        'phone'          => $phone,
        'specialization' => $specialization,
        'availability'   => $availability
    ], 201);
} catch (PDOException $e) {
    sendResponse(false, 'Database error while saving mechanic: ' . $e->getMessage(), null, 500);
}
