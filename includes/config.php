<?php
// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        die('.env file not found. Please copy .env.example to .env and configure it.');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!empty($name)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env file
loadEnv(dirname(__DIR__) . '/.env');

// Database configuration
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));

// Application paths
define('BASE_PATH', dirname(__DIR__));
define('CATEGORIES_PATH', BASE_PATH . '/' . getenv('CATEGORIES_PATH'));
define('UPLOADS_PATH', BASE_PATH . '/' . getenv('UPLOADS_PATH'));
define('METADATA_PATH', BASE_PATH . '/' . getenv('METADATA_PATH'));

// Allowed file types
define('ALLOWED_TYPES', ['application/pdf']);
define('MAX_FILE_SIZE', (int)getenv('MAX_UPLOAD_SIZE')); // From .env

// Error reporting
ini_set('display_errors', getenv('APP_DEBUG'));
error_reporting(E_ALL);

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
    die("Database connection failed. Please check your .env configuration.");
}

// Session configuration - These must be set before session_start()
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', getenv('SESSION_HTTP_ONLY'));
    ini_set('session.use_only_cookies', getenv('SESSION_USE_ONLY_COOKIES'));
    ini_set('session.cookie_secure', getenv('SESSION_SECURE'));
    session_start();
}
