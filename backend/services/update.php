<?php
/**
 * Update Service Endpoint (Admin Only)
 * POST /backend/services/update.php
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
$id       = (int)($input['id'] ?? 0);
$name     = cleanInput($input['service_name'] ?? '');
$desc     = cleanInput($input['description'] ?? '');
$price    = floatval($input['price'] ?? 0);
$duration = cleanInput($input['estimated_duration'] ?? '');

if ($id <= 0) {
    sendResponse(false, 'Valid service ID is required.', null, 400);
}

if (empty($name) || empty($desc) || $price <= 0 || empty($duration)) {
    sendResponse(false, 'All fields (name, description, price, duration) are required.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("UPDATE services 
                           SET service_name = :name, 
                               description = :desc, 
                               price = :price, 
                               estimated_duration = :dur 
                           WHERE id = :id");
    $stmt->execute([
        ':name'  => $name,
        ':desc'  => $desc,
        ':price' => $price,
        ':dur'   => $duration,
        ':id'    => $id
    ]);

    sendResponse(true, 'Service updated successfully!');
} catch (PDOException $e) {
    sendResponse(false, 'Database error while updating service: ' . $e->getMessage(), null, 500);
}
