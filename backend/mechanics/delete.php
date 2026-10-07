<?php
/**
 * Delete Mechanic Endpoint (Admin Only)
 * POST /backend/mechanics/delete.php
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
    sendResponse(false, 'Valid mechanic ID is required.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Mechanics assigned to bookings will have mechanic_id SET NULL via database foreign key constraint
    $stmt = $pdo->prepare("DELETE FROM mechanics WHERE id = :id");
    $stmt->execute([':id' => $id]);

    sendResponse(true, 'Mechanic deleted successfully.');
} catch (PDOException $e) {
    sendResponse(false, 'Database error while deleting mechanic: ' . $e->getMessage(), null, 500);
}
