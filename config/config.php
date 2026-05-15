<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->safeLoad();
}

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3307'); // MySQL default port
define('DB_NAME', $_ENV['DB_NAME'] ?? 'attendance_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');   // XAMPP default user
define('DB_PASS', $_ENV['DB_PASS'] ?? '');       // XAMPP default password (empty)

// SMTP Configuration (Email)
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? 'your-email@gmail.com');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? 'your-app-password');
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? 'tls');

// Application Settings
define('APP_NAME', $_ENV['APP_NAME'] ?? 'College Attendance Portal');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost/attendance-email-system/public');
define('TIMEZONE', $_ENV['TIMEZONE'] ?? 'Asia/Kolkata');
define('COLLEGE_NAME', $_ENV['COLLEGE_NAME'] ?? 'My College');

// Error Reporting — NEVER display errors in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/php_errors.log');

// Set Timezone
date_default_timezone_set(TIMEZONE);
?>