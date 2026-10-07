<?php
/**
 * Delete Service Endpoint (Admin Only)
 * POST /backend/services/delete.php
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
$id = (int)($input['id'] ?? 0);

if ($id <= 0) {
    sendResponse(false, 'Valid service ID is required.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("DELETE FROM services WHERE id = :id");
    $stmt->execute([':id' => $id]);

    sendResponse(true, 'Service deleted successfully.');
} catch (PDOException $e) {
    sendResponse(false, 'Database error while deleting service: ' . $e->getMessage(), null, 500);
}
