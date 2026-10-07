<?php
/**
 * Create Service Booking Endpoint (Customer)
 * POST /backend/bookings/create.php
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

$vehicleId   = (int)($input['vehicle_id'] ?? 0);
$serviceId   = (int)($input['service_id'] ?? 0);
$bookingDate = cleanInput($input['booking_date'] ?? '');
$bookingTime = cleanInput($input['booking_time'] ?? '');
$notes       = cleanInput($input['notes'] ?? '');

// Validation
if ($vehicleId <= 0) {
    sendResponse(false, 'Please select a valid vehicle.', null, 400);
}

if ($serviceId <= 0) {
    sendResponse(false, 'Please select a valid service.', null, 400);
}

if (empty($bookingDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingDate)) {
    sendResponse(false, 'Please choose a valid appointment date (YYYY-MM-DD).', null, 400);
}

$today = date('Y-m-d');
if ($bookingDate < $today) {
    sendResponse(false, 'Appointment date cannot be in the past.', null, 400);
}

if (empty($bookingTime)) {
    sendResponse(false, 'Please select a preferred service time slot.', null, 400);
}

try {
    $pdo = Database::getConnection();

    // Verify vehicle belongs to current user (or admin)
    if ($currentUser['role'] !== 'admin') {
        $vCheck = $pdo->prepare("SELECT id, brand, model, vehicle_number FROM vehicles WHERE id = :vid AND user_id = :uid LIMIT 1");
        $vCheck->execute([':vid' => $vehicleId, ':uid' => $currentUser['id']]);
        $vehicle = $vCheck->fetch();
        if (!$vehicle) {
            sendResponse(false, 'Selected vehicle does not belong to your account.', null, 403);
        }
    }

    // Verify service exists and fetch official price
    $sCheck = $pdo->prepare("SELECT id, service_name, price, estimated_duration FROM services WHERE id = :sid LIMIT 1");
    $sCheck->execute([':sid' => $serviceId]);
    $service = $sCheck->fetch();
    if (!$service) {
        sendResponse(false, 'Selected service is no longer available.', null, 404);
    }

    $estimatedPrice = floatval($service['price']);
    $userId = $currentUser['id'];

    // Insert booking
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, vehicle_id, service_id, mechanic_id, booking_date, booking_time, notes, estimated_price, status) 
                           VALUES (:uid, :vid, :sid, NULL, :bdate, :btime, :notes, :price, 'Pending')");
    $stmt->execute([
        ':uid'   => $userId,
        ':vid'   => $vehicleId,
        ':sid'   => $serviceId,
        ':bdate' => $bookingDate,
        ':btime' => $bookingTime,
        ':notes' => $notes,
        ':price' => $estimatedPrice
    ]);

    $bookingId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Service booking submitted successfully! Our team will review and confirm your slot.', [
        'booking_id'      => $bookingId,
        'booking_date'    => $bookingDate,
        'booking_time'    => $bookingTime,
        'estimated_price' => $estimatedPrice,
        'status'          => 'Pending'
    ], 201);

} catch (PDOException $e) {
    sendResponse(false, 'Database error while saving booking: ' . $e->getMessage(), null, 500);
}
