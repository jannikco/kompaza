<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

// Application configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Kompaza');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('PLATFORM_DOMAIN', $_ENV['PLATFORM_DOMAIN'] ?? 'kompaza.com');

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'kompaza');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

// Email configuration (Brevo - platform default)
define('BREVO_API_KEY', $_ENV['BREVO_API_KEY'] ?? '');
define('BREVO_LIST_ID', $_ENV['BREVO_LIST_ID'] ?? '');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'info@kompaza.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Kompaza');

// Stripe (platform billing)
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('STRIPE_WEBHOOK_SECRET', $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');
define('STRIPE_CONNECT_WEBHOOK_SECRET', $_ENV['STRIPE_CONNECT_WEBHOOK_SECRET'] ?? '');
define('APP_SECRET', $_ENV['APP_SECRET'] ?? 'change-me-in-production');

// S3 / Linode Object Storage
define('S3_ENDPOINT', $_ENV['S3_ENDPOINT'] ?? 'https://de-fra-1.linodeobjects.com');
define('S3_REGION', $_ENV['S3_REGION'] ?? 'de-fra-1');
define('S3_ACCESS_KEY_ID', $_ENV['S3_ACCESS_KEY_ID'] ?? '');
define('S3_SECRET_ACCESS_KEY', $_ENV['S3_SECRET_ACCESS_KEY'] ?? '');
define('S3_BUCKET_NAME', $_ENV['S3_BUCKET_NAME'] ?? 'kompaza');
define('S3_PUBLIC_DOMAIN', $_ENV['S3_PUBLIC_DOMAIN'] ?? 'kompaza.de-fra-1.linodeobjects.com');

// Security configuration
define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');

// Superadmin
define('SUPERADMIN_EMAIL', $_ENV['SUPERADMIN_EMAIL'] ?? 'admin@kompaza.com');

// Paths
define('BASE_PATH', dirname(__DIR__, 2));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEWS_PATH', BASE_PATH . '/src/Views');
define('CONTROLLERS_PATH', BASE_PATH . '/src/Controllers');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Europe/Copenhagen');

// Load helper functions
require_once __DIR__ . '/functions.php';

// Load database connection
require_once __DIR__ . '/../Database/Database.php';
