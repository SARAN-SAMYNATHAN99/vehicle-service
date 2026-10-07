<?php
/**
 * Delete Vehicle Endpoint
 * POST /backend/vehicles/delete.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed. Use POST.', null, 405);
}

$currentUser = requireAuth();
$input = getRequestData();

$id = (int)($input['id'] ?? 0);
if ($id <= 0) {
    sendResponse(false, 'Valid vehicle ID is required.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Verify ownership if not admin
    if ($currentUser['role'] !== 'admin') {
        $check = $pdo->prepare("SELECT id FROM vehicles WHERE id = :id AND user_id = :uid LIMIT 1");
        $check->execute([':id' => $id, ':uid' => $currentUser['id']]);
        if (!$check->fetch()) {
            sendResponse(false, 'Vehicle not found or you do not have permission to delete it.', null, 403);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = :id");
    $stmt->execute([':id' => $id]);

    sendResponse(true, 'Vehicle deleted successfully.');

} catch (PDOException $e) {
    sendResponse(false, 'Database error while deleting vehicle: ' . $e->getMessage(), null, 500);
}
