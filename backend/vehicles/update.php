<?php
/**
 * Update Vehicle Endpoint
 * POST /backend/vehicles/update.php
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

$id            = (int)($input['id'] ?? 0);
$vehicleNumber = cleanInput($input['vehicle_number'] ?? '');
$vehicleType   = cleanInput($input['vehicle_type'] ?? 'Car');
$brand         = cleanInput($input['brand'] ?? '');
$model         = cleanInput($input['model'] ?? '');
$year          = (int)($input['manufacturing_year'] ?? 0);
$fuelType      = cleanInput($input['fuel_type'] ?? 'Petrol');

if ($id <= 0) {
    sendResponse(false, 'Valid vehicle ID is required.', null, 400);
}

if (empty($vehicleNumber) || empty($brand) || empty($model)) {
    sendResponse(false, 'Vehicle number, brand, and model are required.', null, 400);
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

    // Verify ownership if not admin
    if ($currentUser['role'] !== 'admin') {
        $check = $pdo->prepare("SELECT id FROM vehicles WHERE id = :id AND user_id = :uid LIMIT 1");
        $check->execute([':id' => $id, ':uid' => $currentUser['id']]);
        if (!$check->fetch()) {
            sendResponse(false, 'Vehicle not found or you do not have permission to edit it.', null, 403);
        }
    }

    $stmt = $pdo->prepare("UPDATE vehicles 
                           SET vehicle_number = :num, 
                               vehicle_type = :vtype, 
                               brand = :brand, 
                               model = :model, 
                               manufacturing_year = :yr, 
                               fuel_type = :fuel 
                           WHERE id = :id");
    $stmt->execute([
        ':num'   => strtoupper($vehicleNumber),
        ':vtype' => $vehicleType,
        ':brand' => $brand,
        ':model' => $model,
        ':yr'    => $year,
        ':fuel'  => $fuelType,
        ':id'    => $id
    ]);

    sendResponse(true, 'Vehicle updated successfully!');

} catch (PDOException $e) {
    sendResponse(false, 'Database error while updating vehicle: ' . $e->getMessage(), null, 500);
}
