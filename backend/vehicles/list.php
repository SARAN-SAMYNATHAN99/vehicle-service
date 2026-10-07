<?php
/**
 * List Vehicles Endpoint
 * GET /backend/vehicles/list.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();
$currentUser = requireAuth();

try {
    $pdo = Database::getConnection();

    // If admin requests a specific user's vehicles, or all vehicles
    if ($currentUser['role'] === 'admin' && isset($_GET['user_id'])) {
        $targetUserId = (int)$_GET['user_id'];
        $stmt = $pdo->prepare("SELECT v.*, u.name as owner_name, u.email as owner_email 
                               FROM vehicles v 
                               JOIN users u ON v.user_id = u.id 
                               WHERE v.user_id = :uid 
                               ORDER BY v.created_at DESC");
        $stmt->execute([':uid' => $targetUserId]);
    } elseif ($currentUser['role'] === 'admin' && isset($_GET['all'])) {
        $stmt = $pdo->query("SELECT v.*, u.name as owner_name, u.email as owner_email 
                             FROM vehicles v 
                             JOIN users u ON v.user_id = u.id 
                             ORDER BY v.created_at DESC");
    } else {
        // Customer viewing their own vehicles
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = :uid ORDER BY created_at DESC");
        $stmt->execute([':uid' => $currentUser['id']]);
    }

    $vehicles = $stmt->fetchAll();
    sendResponse(true, 'Vehicles retrieved successfully.', $vehicles);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
