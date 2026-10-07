<?php
/**
 * Admin Assign Mechanic to Booking Endpoint
 * POST /backend/bookings/assign_mechanic.php
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
$bookingId  = (int)($input['booking_id'] ?? 0);
$mechanicId = (int)($input['mechanic_id'] ?? 0);

if ($bookingId <= 0) {
    sendResponse(false, 'Valid booking ID is required.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Verify booking
    $bStmt = $pdo->prepare("SELECT id, status FROM bookings WHERE id = :id LIMIT 1");
    $bStmt->execute([':id' => $bookingId]);
    $booking = $bStmt->fetch();
    if (!$booking) {
        sendResponse(false, 'Booking record not found.', null, 404);
    }

    if ($mechanicId > 0) {
        // Verify mechanic exists
        $mStmt = $pdo->prepare("SELECT id, name FROM mechanics WHERE id = :id LIMIT 1");
        $mStmt->execute([':id' => $mechanicId]);
        $mechanic = $mStmt->fetch();
        if (!$mechanic) {
            sendResponse(false, 'Selected mechanic does not exist.', null, 404);
        }

        // If status was Pending, auto-promote to Confirmed
        $newStatus = ($booking['status'] === 'Pending') ? 'Confirmed' : $booking['status'];

        $update = $pdo->prepare("UPDATE bookings SET mechanic_id = :mid, status = :status WHERE id = :id");
        $update->execute([':mid' => $mechanicId, ':status' => $newStatus, ':id' => $bookingId]);

        sendResponse(true, "Mechanic '{$mechanic['name']}' assigned to Booking #BKG-" . str_pad($bookingId, 5, '0', STR_PAD_LEFT) . ".");
    } else {
        // Unassign mechanic
        $update = $pdo->prepare("UPDATE bookings SET mechanic_id = NULL WHERE id = :id");
        $update->execute([':id' => $bookingId]);

        sendResponse(true, "Mechanic unassigned from Booking #BKG-" . str_pad($bookingId, 5, '0', STR_PAD_LEFT) . ".");
    }

} catch (PDOException $e) {
    sendResponse(false, 'Database error while assigning mechanic: ' . $e->getMessage(), null, 500);
}
