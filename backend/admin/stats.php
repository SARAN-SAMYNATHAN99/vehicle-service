<?php
/**
 * Admin Dashboard Statistics Endpoint
 * GET /backend/admin/stats.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();
requireAdmin();

try {
    $pdo = Database::getConnection();

    // Key Performance Indicators (KPIs)
    $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $totalVehicles  = (int)$pdo->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
    $totalBookings  = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
    $pendingBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
    $confirmedBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'")->fetchColumn();
    $inServiceBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'In Service'")->fetchColumn();
    $completedServices = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Completed'")->fetchColumn();
    $cancelledBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Cancelled'")->fetchColumn();
    $totalServices  = (int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
    $totalMechanics = (int)$pdo->query("SELECT COUNT(*) FROM mechanics")->fetchColumn();
    $totalRevenue   = (float)$pdo->query("SELECT IFNULL(SUM(estimated_price), 0) FROM bookings WHERE status = 'Completed'")->fetchColumn();

    // Recent 5 bookings
    $recentStmt = $pdo->query("SELECT 
                                    b.id, b.booking_date, b.booking_time, b.status, b.estimated_price,
                                    u.name as customer_name,
                                    v.brand as vehicle_brand, v.model as vehicle_model, v.vehicle_number,
                                    s.service_name,
                                    m.name as mechanic_name
                               FROM bookings b
                               JOIN users u ON b.user_id = u.id
                               JOIN vehicles v ON b.vehicle_id = v.id
                               JOIN services s ON b.service_id = s.id
                               LEFT JOIN mechanics m ON b.mechanic_id = m.id
                               ORDER BY b.id DESC LIMIT 5");
    $recentBookings = $recentStmt->fetchAll();

    sendResponse(true, 'Admin metrics retrieved successfully.', [
        'stats' => [
            'total_customers'    => $totalCustomers,
            'total_vehicles'     => $totalVehicles,
            'total_bookings'     => $totalBookings,
            'pending_bookings'   => $pendingBookings,
            'confirmed_bookings' => $confirmedBookings,
            'in_service_bookings'=> $inServiceBookings,
            'completed_services' => $completedServices,
            'cancelled_bookings' => $cancelledBookings,
            'total_services'     => $totalServices,
            'total_mechanics'    => $totalMechanics,
            'total_revenue'      => $totalRevenue
        ],
        'recent_bookings' => $recentBookings
    ]);

} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
