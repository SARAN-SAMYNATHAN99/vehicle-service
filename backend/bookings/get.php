<?php
/**
 * Get Booking Details Endpoint
 * GET /backend/bookings/get.php?id=X
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();
$currentUser = requireAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    sendResponse(false, 'Invalid booking ID specified.', null, 400);
}

try {
    $pdo = Database::getConnection();

    $sql = "SELECT 
                b.*,
                u.name as customer_name,
                u.email as customer_email,
                u.phone as customer_phone,
                v.vehicle_number,
                v.vehicle_type,
                v.brand as vehicle_brand,
                v.model as vehicle_model,
                v.fuel_type,
                v.manufacturing_year,
                s.service_name,
                s.description as service_description,
                s.estimated_duration,
                m.name as mechanic_name,
                m.phone as mechanic_phone,
                m.specialization as mechanic_specialization
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN mechanics m ON b.mechanic_id = m.id
            WHERE b.id = :id";

    // Regular customers can only view their own bookings
    if ($currentUser['role'] !== 'admin') {
        $sql .= " AND b.user_id = :uid";
    }

    $sql .= " LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $params = [':id' => $id];
    if ($currentUser['role'] !== 'admin') {
        $params[':uid'] = $currentUser['id'];
    }

    $stmt->execute($params);
    $booking = $stmt->fetch();

    if (!$booking) {
        sendResponse(false, 'Booking record not found or access denied.', null, 404);
    }

    sendResponse(true, 'Booking details retrieved successfully.', $booking);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
