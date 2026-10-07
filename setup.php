<?php
/**
 * Vehicle Service Booking System - Database Setup & Diagnostics Utility
 * Access via: http://localhost/vehicle/setup.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbHost = 'localhost';
$dbPort = '3306';
$dbUser = 'root';
$dbPass = '';
$dbName = 'vehicle_service_db';

$messages = [];
$status = 'idle';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    try {
        // Step 1: Connect to MySQL server without database
        $pdoServer = new PDO("mysql:host={$dbHost};port={$dbPort}", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $messages[] = ['type' => 'success', 'text' => "Connected to MySQL server ({$dbHost}:{$dbPort})."];

        // Step 2: Create database if not exists
        $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $messages[] = ['type' => 'success', 'text' => "Database '{$dbName}' verified/created."];

        // Step 3: Read database.sql file
        $sqlFile = __DIR__ . '/database.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("database.sql file not found at " . $sqlFile);
        }

        $sqlContent = file_get_contents($sqlFile);

        // Step 4: Execute SQL script
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
            $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
        }

        $pdoDb = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, $options);

        // Execute queries
        try {
            $pdoDb->exec($sqlContent);
        } catch (PDOException $ex) {
            // Fallback: Split by semicolons and execute individually
            $queries = explode(";\n", $sqlContent);
            foreach ($queries as $q) {
                $q = trim($q);
                if (!empty($q)) {
                    $pdoDb->exec($q);
                }
            }
        }
        $messages[] = ['type' => 'success', 'text' => "Executed database.sql successfully. Tables & sample data created!"];

        // Step 5: Verify Admin password hash with native password_hash to be 100% sure on any PHP engine
        $adminHash = password_hash('Admin@123', PASSWORD_BCRYPT);
        $userHash = password_hash('Password@123', PASSWORD_BCRYPT);

        $pdoDb->prepare("UPDATE users SET password = :hash WHERE email = 'admin@gearshift.com'")->execute([':hash' => $adminHash]);
        $pdoDb->prepare("UPDATE users SET password = :hash WHERE email IN ('john@example.com', 'emily@example.com', 'michael@example.com')")->execute([':hash' => $userHash]);
        $messages[] = ['type' => 'success', 'text' => "Re-verified secure BCrypt password hashes for Admin and Demo Customer accounts."];

        $status = 'success';
    } catch (Exception $e) {
        $messages[] = ['type' => 'error', 'text' => 'Installation error: ' . $e->getMessage()];
        $status = 'error';
    }
} else {
    // Check current state
    try {
        $testPdo = @new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $tableCount = $testPdo->query("SELECT count(*) FROM information_schema.tables WHERE table_schema = '{$dbName}'")->fetchColumn();
        if ($tableCount > 0) {
            $status = 'already_installed';
            $messages[] = ['type' => 'info', 'text' => "Database '{$dbName}' is already installed with {$tableCount} tables."];
        }
    } catch (Exception $e) {
        $status = 'needs_install';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GearShift - Database Setup & Diagnostic Wizard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .setup-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 1rem;
            max-width: 650px;
            width: 100%;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-success { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid #22c55e; }
        .badge-info { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid #3b82f6; }
        .badge-error { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid #ef4444; }
        .msg-box {
            padding: 0.85rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .msg-success { background: #14532d; color: #bbf7d0; border-left: 4px solid #22c55e; }
        .msg-error { background: #7f1d1d; color: #fecaca; border-left: 4px solid #ef4444; }
        .msg-info { background: #1e3a8a; color: #bfdbfe; border-left: 4px solid #3b82f6; }
        .credentials-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: #0f172a;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .credentials-table th, .credentials-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #334155;
            font-size: 0.9rem;
        }
        .credentials-table th { background: #1e293b; color: #94a3b8; }
        .btn-group { display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap; }
    </style>
</head>
<body>
    <div class="setup-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h1 style="font-size: 1.75rem; font-weight: 700; margin: 0; color: #fff;">⚙️ Database Setup Wizard</h1>
                <p style="color: #94a3b8; margin: 0.25rem 0 0 0; font-size: 0.95rem;">GearShift Vehicle Service Booking System</p>
            </div>
            <span class="badge badge-info">PHP <?= PHP_VERSION ?></span>
        </div>

        <div style="background: #0f172a; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; color: #cbd5e1; border: 1px solid #334155;">
            <p style="margin: 0 0 0.5rem 0;"><strong>System Diagnostics:</strong></p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                <div>PHP Version: <span style="color:#4ade80;">≥ 8.0 (<?= PHP_VERSION ?>)</span></div>
                <div>PDO Extension: <?= extension_loaded('pdo') ? '<span style="color:#4ade80;">✓ Enabled</span>' : '<span style="color:#ef4444;">✗ Disabled</span>' ?></div>
                <div>PDO MySQL: <?= extension_loaded('pdo_mysql') ? '<span style="color:#4ade80;">✓ Enabled</span>' : '<span style="color:#ef4444;">✗ Disabled</span>' ?></div>
                <div>Target DB: <span style="color:#60a5fa;"><?= $dbName ?></span></div>
            </div>
        </div>

        <?php foreach ($messages as $msg): ?>
            <div class="msg-box msg-<?= $msg['type'] ?>">
                <?= $msg['type'] === 'success' ? '✓' : ($msg['type'] === 'error' ? '✗' : 'ℹ') ?>
                <span><?= htmlspecialchars($msg['text']) ?></span>
            </div>
        <?php endforeach; ?>

        <?php if ($status === 'success' || $status === 'already_installed'): ?>
            <div style="margin-top: 1.5rem;">
                <h3 style="color: #f8fafc; font-size: 1.1rem; margin-bottom: 0.5rem;">🔑 Ready-to-Use Demo Credentials</h3>
                <table class="credentials-table">
                    <thead>
                        <tr>
                            <th>Portal</th>
                            <th>Email Address</th>
                            <th>Password</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong style="color:#f97316;">Admin Portal</strong></td>
                            <td><code>admin@gearshift.com</code></td>
                            <td><code>Admin@123</code></td>
                            <td><span class="badge badge-info">Admin</span></td>
                        </tr>
                        <tr>
                            <td><strong style="color:#3b82f6;">Customer Portal</strong></td>
                            <td><code>john@example.com</code></td>
                            <td><code>Password@123</code></td>
                            <td><span class="badge badge-success">Customer</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="btn-group">
                <a href="index.html" class="btn btn-primary" style="background:#2563eb; color:#fff; padding:0.75rem 1.25rem; border-radius:0.5rem; text-decoration:none; font-weight:600;">Go to Home Page</a>
                <a href="login.html" class="btn" style="background:#0284c7; color:#fff; padding:0.75rem 1.25rem; border-radius:0.5rem; text-decoration:none; font-weight:600;">Customer Login</a>
                <a href="admin/dashboard.html" class="btn" style="background:#ea580c; color:#fff; padding:0.75rem 1.25rem; border-radius:0.5rem; text-decoration:none; font-weight:600;">Admin Dashboard</a>
            </div>

            <form method="POST" style="margin-top: 2rem; border-top: 1px solid #334155; padding-top: 1rem;">
                <input type="hidden" name="action" value="install">
                <p style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 0.5rem;">Need to reset or re-initialize sample data?</p>
                <button type="submit" style="background: transparent; color: #94a3b8; border: 1px solid #475569; padding: 0.4rem 0.85rem; border-radius: 0.4rem; cursor: pointer; font-size: 0.85rem;">
                    ↺ Re-run Database Setup & Reset Sample Data
                </button>
            </form>
        <?php else: ?>
            <p style="color: #94a3b8; margin: 1rem 0;">
                Click the button below to initialize the database <code><?= $dbName ?></code>, create all 5 tables (users, vehicles, services, mechanics, bookings), and insert sample data.
            </p>
            <form method="POST">
                <input type="hidden" name="action" value="install">
                <button type="submit" style="background: #2563eb; color: #fff; border: none; padding: 0.85rem 1.75rem; border-radius: 0.5rem; font-weight: 700; cursor: pointer; font-size: 1rem; width: 100%;">
                    🚀 Initialize Database Now
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
