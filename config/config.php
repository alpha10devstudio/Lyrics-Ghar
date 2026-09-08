<?php
/**
 * Lyrics Ghar - Main Configuration
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lyricsghar');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'https://lyricsghar.great-site.net');
define('SITE_NAME', 'Lyrics Ghar');
define('SITE_TAGLINE', 'Islamic Lyrics, All in One Place');
define('ADMIN_EMAIL', 'admin@lyricsghar.great-site.net');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('UPLOADS_URL', SITE_URL . '/uploads');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_NAME', 'lyricsghar_session');

// Pagination
define('POSTS_PER_PAGE', 12);

// Start session with custom name
session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require core files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php';
