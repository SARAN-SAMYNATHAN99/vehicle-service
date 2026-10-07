<?php
/**
 * Cancel Booking Endpoint
 * POST /backend/bookings/cancel.php
 * Note: Cancellation is only permitted when status is 'Pending' or 'Confirmed'.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/response.php';
require_once __DIR__ . '/../config/auth_check.php';

setApiHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed. Use POST.', null, 405);
}

$currentUser = requireAuth();
$input = getRequestData();

$bookingId = (int)($input['booking_id'] ?? 0);
$reason    = cleanInput($input['reason'] ?? '');

if ($bookingId <= 0) {
    sendResponse(false, 'Valid booking ID is required.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Fetch booking
    $query = "SELECT id, user_id, status FROM bookings WHERE id = :id";
    if ($currentUser['role'] !== 'admin') {
        $query .= " AND user_id = :uid";
    }
    $query .= " LIMIT 1";

    $stmt = $pdo->prepare($query);
    $params = [':id' => $bookingId];
    if ($currentUser['role'] !== 'admin') {
        $params[':uid'] = $currentUser['id'];
    }
    $stmt->execute($params);
    $booking = $stmt->fetch();

    if (!$booking) {
        sendResponse(false, 'Booking not found or you do not have permission to modify it.', null, 404);
    }

    // Cancellation constraint check
    $allowedStatuses = ['Pending', 'Confirmed'];
    if (!in_array($booking['status'], $allowedStatuses)) {
        sendResponse(false, "Cannot cancel this booking. Services currently in '{$booking['status']}' status cannot be cancelled.", null, 400);
    }

    // Append cancellation reason to notes if provided
    $updateSql = "UPDATE bookings SET status = 'Cancelled'";
    if (!empty($reason)) {
        $updateSql .= ", notes = CONCAT(IFNULL(notes, ''), '\n[Cancellation Note]: ', :reason)";
    }
    $updateSql .= " WHERE id = :id";

    $updateStmt = $pdo->prepare($updateSql);
    $updateParams = [':id' => $bookingId];
    if (!empty($reason)) {
        $updateParams[':reason'] = $reason;
    }
    $updateStmt->execute($updateParams);

    sendResponse(true, 'Booking #BKG-' . str_pad($bookingId, 5, '0', STR_PAD_LEFT) . ' has been successfully cancelled.');

} catch (PDOException $e) {
    sendResponse(false, 'Database error while cancelling booking: ' . $e->getMessage(), null, 500);
}
