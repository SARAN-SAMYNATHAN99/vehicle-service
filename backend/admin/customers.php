<?php
/**
 * Admin List Customers Endpoint
 * GET /backend/admin/customers.php
 * Supports ?search=keyword
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();
requireAdmin();

$search = cleanInput($_GET['search'] ?? '');

try {
    $pdo = Database::getConnection();

    $sql = "SELECT 
                u.id,
                u.name,
                u.email,
                u.phone,
                u.created_at,
                COUNT(DISTINCT v.id) as vehicle_count,
                COUNT(DISTINCT b.id) as booking_count,
                MAX(b.booking_date) as last_booking_date
            FROM users u
            LEFT JOIN vehicles v ON u.id = v.user_id
            LEFT JOIN bookings b ON u.id = b.user_id
            WHERE u.role = 'customer'";

    $params = [];
    if (!empty($search)) {
        $sql .= " AND (u.name LIKE :s OR u.email LIKE :s OR u.phone LIKE :s)";
        $params[':s'] = "%{$search}%";
    }

    $sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();

    sendResponse(true, 'Customers list retrieved.', $customers);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
