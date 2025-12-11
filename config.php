<?php
// Load environment variables
require_once __DIR__ . '/env.php';

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'luminelle');

// Application Settings
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost/luminelle');
define('UPLOAD_PATH', __DIR__ . '/assets/images/uploads/');
define('MAX_FILE_SIZE', (int)(getenv('MAX_FILE_SIZE') ?: 5242880));
define('DEBUG', getenv('DEBUG') === 'true');

// Error reporting based on environment
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

