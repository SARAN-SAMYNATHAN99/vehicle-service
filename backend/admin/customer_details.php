<?php
/**
 * Admin Get Customer Details with Vehicles and Bookings
 * GET /backend/admin/customer_details.php?id=X
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    sendResponse(false, 'Valid customer ID is required.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Fetch customer info
    $uStmt = $pdo->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = :id AND role = 'customer' LIMIT 1");
    $uStmt->execute([':id' => $id]);
    $customer = $uStmt->fetch();

    if (!$customer) {
        sendResponse(false, 'Customer record not found.', null, 404);
    }

    // Fetch customer's vehicles
    $vStmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = :uid ORDER BY created_at DESC");
    $vStmt->execute([':uid' => $id]);
    $vehicles = $vStmt->fetchAll();

    // Fetch customer's bookings
    $bStmt = $pdo->prepare("SELECT 
                                b.*,
                                v.vehicle_number, v.brand as vehicle_brand, v.model as vehicle_model,
                                s.service_name, s.estimated_duration,
                                m.name as mechanic_name
                            FROM bookings b
                            JOIN vehicles v ON b.vehicle_id = v.id
                            JOIN services s ON b.service_id = s.id
                            LEFT JOIN mechanics m ON b.mechanic_id = m.id
                            WHERE b.user_id = :uid
                            ORDER BY b.booking_date DESC");
    $bStmt->execute([':uid' => $id]);
    $bookings = $bStmt->fetchAll();

    sendResponse(true, 'Customer details retrieved.', [
        'customer' => $customer,
        'vehicles' => $vehicles,
        'bookings' => $bookings
    ]);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
