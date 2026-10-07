<?php
/**
 * List Mechanics Endpoint
 * GET /backend/mechanics/list.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';

setApiHeaders();

try {
    $pdo = Database::getConnection();

    if (isset($_GET['available_only']) && $_GET['available_only'] == '1') {
        $stmt = $pdo->query("SELECT * FROM mechanics WHERE availability = 'Available' ORDER BY name ASC");
    } else {
        $stmt = $pdo->query("SELECT * FROM mechanics ORDER BY id ASC");
    }

    $mechanics = $stmt->fetchAll();
    sendResponse(true, 'Mechanics retrieved successfully.', $mechanics);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
