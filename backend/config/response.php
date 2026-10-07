<?php
/**
 * Response and Request Helper Utilities
 * Standardizes API responses across all endpoints
 */

// Enable CORS and define JSON headers
function setApiHeaders(): void {
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Output structured JSON response and terminate script
 */
function sendResponse(bool $success, string $message, $data = null, int $statusCode = 200): void {
    setApiHeaders();
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Get input data whether sent via application/json, application/x-www-form-urlencoded, or multipart/form-data
 */
function getRequestData(): array {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
        return array_merge($_REQUEST, $jsonData);
    }

    return $_REQUEST;
}

/**
 * Sanitize string input to prevent XSS
 */
function cleanInput(?string $data): string {
    if ($data === null) return '';
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
