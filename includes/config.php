<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'acd1f4ftwarecom_root');
define('DB_PASS', 'qM0sD5tW3rG7hY9jL');
define('DB_NAME', 'acd1f4ftwarecom_readhub');

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Application paths
define('BASE_PATH', dirname(__DIR__));
define('CATEGORIES_PATH', BASE_PATH . '/categories');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('METADATA_PATH', BASE_PATH . '/metadata');

// Allowed file types
define('ALLOWED_TYPES', ['application/pdf']);
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // 20MB

// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session configuration - These must be set before session_start()
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    session_start();
}
