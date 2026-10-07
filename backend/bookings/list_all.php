<?php
/**
 * Admin List All Bookings Endpoint
 * GET /backend/bookings/list_all.php
 * Supports ?status=Pending|Confirmed|In Service|Completed|Cancelled
 * Supports ?search=keyword
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();
requireAdmin();

$statusFilter = cleanInput($_GET['status'] ?? '');
$search       = cleanInput($_GET['search'] ?? '');

try {
    $pdo = Database::getConnection();

    $sql = "SELECT 
                b.id,
                b.user_id,
                b.vehicle_id,
                b.service_id,
                b.mechanic_id,
                b.booking_date,
                b.booking_time,
                b.notes,
                b.estimated_price,
                b.status,
                b.created_at,
                u.name as customer_name,
                u.email as customer_email,
                u.phone as customer_phone,
                v.vehicle_number,
                v.vehicle_type,
                v.brand as vehicle_brand,
                v.model as vehicle_model,
                s.service_name,
                s.estimated_duration,
                m.name as mechanic_name,
                m.phone as mechanic_phone,
                m.specialization as mechanic_specialization
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN mechanics m ON b.mechanic_id = m.id
            WHERE 1=1";

    $params = [];

    // Filter by status
    $validStatuses = ['Pending', 'Confirmed', 'In Service', 'Completed', 'Cancelled'];
    if (!empty($statusFilter) && in_array($statusFilter, $validStatuses)) {
        $sql .= " AND b.status = :status";
        $params[':status'] = $statusFilter;
    }

    // Search across customer name, email, vehicle number, booking id
    if (!empty($search)) {
        $sql .= " AND (u.name LIKE :search 
                     OR u.email LIKE :search 
                     OR u.phone LIKE :search 
                     OR v.vehicle_number LIKE :search 
                     OR s.service_name LIKE :search 
                     OR b.id = :search_exact)";
        $params[':search'] = "%{$search}%";
        $params[':search_exact'] = is_numeric($search) ? (int)$search : 0;
    }

    $sql .= " ORDER BY b.booking_date DESC, b.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll();

    sendResponse(true, 'Bookings retrieved successfully.', $bookings);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
