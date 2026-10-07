<?php
/**
 * Customer Booking History Endpoint
 * GET /backend/bookings/my_bookings.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();
$currentUser = requireAuth();

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
            JOIN vehicles v ON b.vehicle_id = v.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN mechanics m ON b.mechanic_id = m.id
            WHERE b.user_id = :uid
            ORDER BY b.booking_date DESC, b.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':uid' => $currentUser['id']]);
    $bookings = $stmt->fetchAll();

    sendResponse(true, 'Booking history retrieved successfully.', $bookings);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
