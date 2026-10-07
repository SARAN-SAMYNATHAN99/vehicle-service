<?php
/**
 * Update Mechanic Endpoint (Admin Only)
 * POST /backend/mechanics/update.php
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
$id             = (int)($input['id'] ?? 0);
$name           = cleanInput($input['name'] ?? '');
$phone          = cleanInput($input['phone'] ?? '');
$specialization = cleanInput($input['specialization'] ?? '');
$availability   = cleanInput($input['availability'] ?? 'Available');

if ($id <= 0) {
    sendResponse(false, 'Valid mechanic ID is required.', null, 400);
}

if (empty($name) || empty($phone) || empty($specialization)) {
    sendResponse(false, 'Name, phone, and specialization are required.', null, 400);
}

$validAvailabilities = ['Available', 'Busy', 'On Leave'];
if (!in_array($availability, $validAvailabilities)) {
    $availability = 'Available';
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("UPDATE mechanics 
                           SET name = :name, 
                               phone = :phone, 
                               specialization = :spec, 
                               availability = :avail 
                           WHERE id = :id");
    $stmt->execute([
        ':name'  => $name,
        ':phone' => $phone,
        ':spec'  => $specialization,
        ':avail' => $availability,
        ':id'    => $id
    ]);

    sendResponse(true, 'Mechanic updated successfully!');
} catch (PDOException $e) {
    sendResponse(false, 'Database error while updating mechanic: ' . $e->getMessage(), null, 500);
}
