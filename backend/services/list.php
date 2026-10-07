<?php
/**
 * List Services Endpoint (Public / Authenticated)
 * GET /backend/services/list.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';

setApiHeaders();

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
    $services = $stmt->fetchAll();

    sendResponse(true, 'Services retrieved successfully.', $services);
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
