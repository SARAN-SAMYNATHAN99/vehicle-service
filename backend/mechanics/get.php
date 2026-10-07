<?php
/**
 * Get Mechanic Details Endpoint
 * GET /backend/mechanics/get.php?id=X
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';

setApiHeaders();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    sendResponse(false, 'Invalid mechanic ID specified.', null, 400);
}

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM mechanics WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $mechanic = $stmt->fetch();

    if (!$mechanic) {
        sendResponse(false, 'Mechanic not found.', null, 404);
    }

    sendResponse(true, 'Mechanic details retrieved.', $mechanic);
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
