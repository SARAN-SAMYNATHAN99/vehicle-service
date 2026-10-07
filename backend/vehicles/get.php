<?php
/**
 * Get Single Vehicle Details
 * GET /backend/vehicles/get.php?id=X
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();
$currentUser = requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    sendResponse(false, 'Invalid vehicle ID specified.', null, 400);
}

try {
    $pdo = Database::getConnection();

    if ($currentUser['role'] === 'admin') {
        $stmt = $pdo->prepare("SELECT v.*, u.name as owner_name FROM vehicles v JOIN users u ON v.user_id = u.id WHERE v.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = :id AND user_id = :uid LIMIT 1");
        $stmt->execute([':id' => $id, ':uid' => $currentUser['id']]);
    }

    $vehicle = $stmt->fetch();
    if (!$vehicle) {
        sendResponse(false, 'Vehicle not found or you do not have permission to view it.', null, 404);
    }

    sendResponse(true, 'Vehicle details retrieved.', $vehicle);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
