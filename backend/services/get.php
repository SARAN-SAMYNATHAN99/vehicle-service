<?php
/**
 * Get Service Details Endpoint
 * GET /backend/services/get.php?id=X
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';

setApiHeaders();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    sendResponse(false, 'Invalid service ID specified.', null, 400);
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $service = $stmt->fetch();

    if (!$service) {
        sendResponse(false, 'Service not found.', null, 404);
    }

    sendResponse(true, 'Service details retrieved.', $service);
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
