<?php
/**
 * Add Service Endpoint (Admin Only)
 * POST /backend/services/add.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed. Use POST.', null, 405);
}

// Ensure Admin
requireAdmin();

$input = getRequestData();
$name     = cleanInput($input['service_name'] ?? '');
$desc     = cleanInput($input['description'] ?? '');
$price    = floatval($input['price'] ?? 0);
$duration = cleanInput($input['estimated_duration'] ?? '');

if (empty($name) || strlen($name) < 2) {
    sendResponse(false, 'Service name is required (min 2 characters).', null, 400);
}

if (empty($desc)) {
    sendResponse(false, 'Service description is required.', null, 400);
}

if ($price <= 0) {
    sendResponse(false, 'Valid service price is required (must be greater than 0).', null, 400);
}

if (empty($duration)) {
    sendResponse(false, 'Estimated duration is required (e.g., 2 Hours).', null, 400);
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("INSERT INTO services (service_name, description, price, estimated_duration) 
                           VALUES (:name, :desc, :price, :dur)");
    $stmt->execute([
        ':name'  => $name,
        ':desc'  => $desc,
        ':price' => $price,
        ':dur'   => $duration
    ]);

    $newId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Service added successfully!', [
        'id'                 => $newId,
        'service_name'       => $name,
        'description'        => $desc,
        'price'              => $price,
        'estimated_duration' => $duration
    ], 201);
} catch (PDOException $e) {
    sendResponse(false, 'Database error while saving service: ' . $e->getMessage(), null, 500);
}
