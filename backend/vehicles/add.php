<?php
/**
 * Add Vehicle Endpoint
 * POST /backend/vehicles/add.php
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

$vehicleNumber = cleanInput($input['vehicle_number'] ?? '');
$vehicleType   = cleanInput($input['vehicle_type'] ?? 'Car');
$brand         = cleanInput($input['brand'] ?? '');
$model         = cleanInput($input['model'] ?? '');
$year          = (int)($input['manufacturing_year'] ?? 0);
$fuelType      = cleanInput($input['fuel_type'] ?? 'Petrol');

// Optional user_id assignment if admin
$targetUserId  = ($currentUser['role'] === 'admin' && !empty($input['user_id'])) ? (int)$input['user_id'] : $currentUser['id'];

// Validation
if (empty($vehicleNumber) || strlen($vehicleNumber) < 3) {
    sendResponse(false, 'Vehicle number/registration plate is required (min 3 characters).', null, 400);
}

if (empty($brand) || empty($model)) {
    sendResponse(false, 'Vehicle brand and model are required.', null, 400);
}

$currentYear = (int)date('Y') + 1;
if ($year < 1950 || $year > $currentYear) {
    sendResponse(false, "Manufacturing year must be between 1950 and {$currentYear}.", null, 400);
}

$allowedTypes = ['Car', 'Bike', 'SUV', 'Truck', 'Other'];
if (!in_array($vehicleType, $allowedTypes)) {
    $vehicleType = 'Car';
}

$allowedFuels = ['Petrol', 'Diesel', 'Electric', 'Hybrid', 'CNG'];
if (!in_array($fuelType, $allowedFuels)) {
    $fuelType = 'Petrol';
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("INSERT INTO vehicles (user_id, vehicle_number, vehicle_type, brand, model, manufacturing_year, fuel_type) 
                           VALUES (:uid, :num, :vtype, :brand, :model, :yr, :fuel)");
    $stmt->execute([
        ':uid'   => $targetUserId,
        ':num'   => strtoupper($vehicleNumber),
        ':vtype' => $vehicleType,
        ':brand' => $brand,
        ':model' => $model,
        ':yr'    => $year,
        ':fuel'  => $fuelType
    ]);

    $newId = (int)$pdo->lastInsertId();

    sendResponse(true, 'Vehicle added successfully!', [
        'id'                 => $newId,
        'user_id'            => $targetUserId,
        'vehicle_number'     => strtoupper($vehicleNumber),
        'vehicle_type'       => $vehicleType,
        'brand'              => $brand,
        'model'              => $model,
        'manufacturing_year' => $year,
        'fuel_type'          => $fuelType
    ], 201);

} catch (PDOException $e) {
    sendResponse(false, 'Database error while saving vehicle: ' . $e->getMessage(), null, 500);
}
