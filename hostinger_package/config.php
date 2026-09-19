<?php
/**
 * Single-Tenant Email Accounts Vault - Configuration
 * Clean ASCII / Standard UTF-8 - Strict Apache & Hostinger Compliant
 */

declare(strict_types=1);

// Prevent direct execution outside entry points
if (!defined('VAULT_APP') && php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Direct script access is prohibited.');
}

// Database configuration
define('DB_DIR', __DIR__ . '/db');
define('DB_FILE', DB_DIR . '/vault_data.sqlite');

// Application Credentials
define('ADMIN_USERNAME', 'Hadi');
define('ADMIN_PASSWORD', '91199119');

define('CLIENT_USERNAME', 'rana asim');
define('CLIENT_PASSWORD', 'rana@123');

// Secure Extraction PIN
define('SECURE_EXTRACTION_PIN', '1234');

// Managed Domain Definitions
define('MANAGED_DOMAINS', [
    'basis5.ch',
    'adlover.site'
]);

// App Settings
define('APP_NAME', 'Hadi Digital Account Distribution Portal');
define('SESSION_NAME', 'VAULT_SESS_ID');
define('SESSION_LIFETIME', 86400 * 7); // 7 days

// Timezone
date_default_timezone_set('UTC');
