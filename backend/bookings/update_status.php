<?php
/**
 * Admin Update Booking Status Endpoint
 * POST /backend/bookings/update_status.php
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
$bookingId = (int)($input['booking_id'] ?? 0);
$status    = cleanInput($input['status'] ?? '');
$mechanicId = isset($input['mechanic_id']) && $input['mechanic_id'] !== '' ? (int)$input['mechanic_id'] : null;

if ($bookingId <= 0) {
    sendResponse(false, 'Valid booking ID is required.', null, 400);
}

$validStatuses = ['Pending', 'Confirmed', 'In Service', 'Completed', 'Cancelled'];
if (!in_array($status, $validStatuses)) {
    sendResponse(false, 'Invalid status value provided.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Check booking exists
    $check = $pdo->prepare("SELECT id FROM bookings WHERE id = :id LIMIT 1");
    $check->execute([':id' => $bookingId]);
    if (!$check->fetch()) {
        sendResponse(false, 'Booking record not found.', null, 404);
    }

    if ($mechanicId !== null && $mechanicId > 0) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = :status, mechanic_id = :mid WHERE id = :id");
        $stmt->execute([':status' => $status, ':mid' => $mechanicId, ':id' => $bookingId]);
    } else {
        $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $bookingId]);
    }

    sendResponse(true, "Booking #BKG-" . str_pad($bookingId, 5, '0', STR_PAD_LEFT) . " status updated to '{$status}'.");

} catch (PDOException $e) {
    sendResponse(false, 'Database error while updating booking status: ' . $e->getMessage(), null, 500);
}
