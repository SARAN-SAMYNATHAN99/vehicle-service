<?php
/**
 * Database Configuration & PDO Connection Handler
 * Vehicle Service Booking System
 */

// Database credentials for XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vehicle_service_db');
define('DB_PORT', '3306');

class Database {
    private static ?PDO $instance = null;

    /**
     * Get or create PDO Database instance
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // If database does not exist yet, allow connecting without DB name to run migrations
                header('Content-Type: application/json; charset=UTF-8');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection failed: ' . $e->getMessage(),
                    'hint' => 'Make sure MySQL is running in XAMPP and run setup.php to initialize the database.'
                ]);
                exit;
            }
        }
        return self::$instance;
    }
}
