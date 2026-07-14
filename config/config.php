<?php
/**
 * Global configuration & database connection.
 * Edit the DB_* constants below to match your MySQL setup.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---- Database settings ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'budget_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---- App settings ----
define('APP_NAME', 'Budgeting & Expense Management System');
define('BASE_URL', '/budget-system/'); // change if the app lives in a sub-folder, e.g. '/budget-system/'
define('CURRENCY', '₱');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}
