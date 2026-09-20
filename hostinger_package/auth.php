<?php
/**
 * Single-Tenant Email Accounts Vault - Authentication & Session Security
 */

declare(strict_types=1);

if (!defined('VAULT_APP') && php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Direct script access is prohibited.');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class Auth {
    /**
     * Start hardened session
     */
    public static function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');
            
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                       || ($_SERVER['SERVER_PORT'] ?? 0) == 443
                       || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }

        // Initialize CSRF token if not set
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Get current CSRF token
     */
    public static function getCsrfToken(): string {
        self::initSession();
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Check if request contains valid master sync key (for AI Studio & automation)
     */
    public static function checkMasterKey(): bool {
        if (!defined('MASTER_SYNC_KEY') || empty(MASTER_SYNC_KEY)) {
            return false;
        }
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $headerKey = $_SERVER['HTTP_X_MASTER_KEY'] ?? $headers['X-Master-Key'] ?? $headers['x-master-key'] ?? '';
        $key = $headerKey ?: ($_GET['master_key'] ?? $_POST['master_key'] ?? '');
        return !empty($key) && hash_equals(MASTER_SYNC_KEY, (string)$key);
    }

    /**
     * Verify CSRF token from header or POST data
     */
    public static function verifyCsrfToken(?string $token = null): bool {
        if (self::checkMasterKey()) {
            return true;
        }
        self::initSession();
        if ($token === null) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
        }
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    /**
     * Authenticate user credentials
     */
    public static function login(string $username, string $password): array {
        self::initSession();

        $cleanUser = trim($username);
        $role = null;

        // Verify Admin Credentials
        if (hash_equals(ADMIN_USERNAME, $cleanUser) && hash_equals(ADMIN_PASSWORD, $password)) {
            $role = 'admin';
        } 
        // Verify Client Credentials
        elseif (hash_equals(CLIENT_USERNAME, $cleanUser) && hash_equals(CLIENT_PASSWORD, $password)) {
            $role = 'client';
        }

        if ($role !== null) {
            // Prevent session fixation
            session_regenerate_id(true);
            $_SESSION['auth_logged_in'] = true;
            $_SESSION['auth_user'] = $cleanUser;
            $_SESSION['auth_role'] = $role;
            $_SESSION['auth_time'] = time();

            Database::logAudit($cleanUser, $role, 'LOGIN_SUCCESS', 'User authenticated successfully');

            return [
                'success' => true,
                'role'    => $role,
                'user'    => $cleanUser
            ];
        }

        Database::logAudit($cleanUser, 'unknown', 'LOGIN_FAILED', 'Invalid credentials attempt');

        return [
            'success' => false,
            'message' => 'Invalid username or password'
        ];
    }

    /**
     * Terminate user authentication and regenerate clean session
     */
    public static function logout(): void {
        self::initSession();
        $user = $_SESSION['auth_user'] ?? 'anonymous';
        $role = $_SESSION['auth_role'] ?? 'none';

        Database::logAudit($user, $role, 'LOGOUT', 'User logged out');

        // Clear all auth flags
        unset($_SESSION['auth_logged_in'], $_SESSION['auth_user'], $_SESSION['auth_role']);

        // Safely regenerate session ID and mint a fresh CSRF token
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Check if current session is authenticated
     */
    public static function isLoggedIn(): bool {
        if (self::checkMasterKey()) {
            return true;
        }
        self::initSession();
        return !empty($_SESSION['auth_logged_in']) && $_SESSION['auth_logged_in'] === true;
    }

    /**
     * Get current logged in user details
     */
    public static function user(): ?array {
        if (self::checkMasterKey()) {
            return [
                'username' => ADMIN_USERNAME,
                'role'     => 'admin'
            ];
        }
        self::initSession();
        if (!self::isLoggedIn()) {
            return null;
        }
        return [
            'username' => $_SESSION['auth_user'] ?? '',
            'role'     => $_SESSION['auth_role'] ?? ''
        ];
    }

    /**
     * Check if logged in user is admin
     */
    public static function isAdmin(): bool {
        if (self::checkMasterKey()) {
            return true;
        }
        return self::isLoggedIn() && ($_SESSION['auth_role'] ?? '') === 'admin';
    }

    /**
     * Check if logged in user is client
     */
    public static function isClient(): bool {
        return self::isLoggedIn() && ($_SESSION['auth_role'] ?? '') === 'client';
    }

    /**
     * Validate extraction PIN
     */
    public static function verifyExtractionPin(string $pin): bool {
        return hash_equals(SECURE_EXTRACTION_PIN, trim($pin));
    }
}
