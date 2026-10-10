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

// Email configuration (SMTP2GO is the platform default; Brevo kept for tenants/back-compat)
define('SMTP2GO_API_KEY', $_ENV['SMTP2GO_API_KEY'] ?? '');
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

// Cron authentication (required for /api/cron/* endpoints; empty = endpoints fail closed)
define('CRON_SECRET', $_ENV['CRON_SECRET'] ?? '');

// Outreach — LinkedIn agent platform.
// OUTREACH_ENCRYPTION_KEY encrypts LinkedIn session cookies at rest (sodium secretbox).
// It must be 32 random bytes, base64-encoded. Generate one with:
//   php -r "echo base64_encode(sodium_crypto_secretbox_keygen()), PHP_EOL;"
// When it is unset, storing a LinkedIn session fails closed rather than falling
// back to plaintext. OUTREACH_ENCRYPTION_KEY_PREVIOUS lets an old key keep decrypting
// existing rows during a rotation.
define('OUTREACH_ENCRYPTION_KEY', $_ENV['OUTREACH_ENCRYPTION_KEY'] ?? '');
define('OUTREACH_ENCRYPTION_KEY_PREVIOUS', $_ENV['OUTREACH_ENCRYPTION_KEY_PREVIOUS'] ?? '');
// How long a synchronous relay call waits for the browser extension to answer.
define('OUTREACH_RELAY_TIMEOUT_MS', (int)($_ENV['OUTREACH_RELAY_TIMEOUT_MS'] ?? 8000));

// S3 / Linode Object Storage
define('S3_ENDPOINT', $_ENV['S3_ENDPOINT'] ?? 'https://de-fra-1.linodeobjects.com');
define('S3_REGION', $_ENV['S3_REGION'] ?? 'de-fra-1');
define('S3_ACCESS_KEY_ID', $_ENV['S3_ACCESS_KEY_ID'] ?? '');
define('S3_SECRET_ACCESS_KEY', $_ENV['S3_SECRET_ACCESS_KEY'] ?? '');
define('S3_BUCKET_NAME', $_ENV['S3_BUCKET_NAME'] ?? 'kompaza');
define('S3_PUBLIC_DOMAIN', $_ENV['S3_PUBLIC_DOMAIN'] ?? 'kompaza.de-fra-1.linodeobjects.com');

// Security configuration
define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');

// OpenAI (legacy lead-magnet generation)
define('OPENAI_API_KEY', $_ENV['OPENAI_API_KEY'] ?? '');

// SpaceXAI / xAI — preferred for new LLM features (suggest_automation_settings).
define('XAI_API_KEY', $_ENV['XAI_API_KEY'] ?? '');

// Superadmin
define('SUPERADMIN_EMAIL', $_ENV['SUPERADMIN_EMAIL'] ?? 'jh@profectify.com');

// Auth / session cookies last 180 days (remember token + PHP session)
define('SESSION_LIFETIME_DAYS', 180);
define('SESSION_LIFETIME', SESSION_LIFETIME_DAYS * 24 * 60 * 60);

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
require_once __DIR__ . '/jh-prices.php';

// Load database connection
require_once __DIR__ . '/../Database/Database.php';
