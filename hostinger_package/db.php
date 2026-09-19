<?php
/**
 * Single-Tenant Email Accounts Vault - Database Manager
 * Auto-initializes SQLite database with schema, indices, and WAL mode.
 */

declare(strict_types=1);

if (!defined('VAULT_APP') && php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Direct script access is prohibited.');
}

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $pdo = null;

    /**
     * Get or create PDO connection with auto-initialization
     */
    public static function getConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // Ensure database directory exists with proper permissions
        if (!is_dir(DB_DIR)) {
            if (!mkdir(DB_DIR, 0755, true) && !is_dir(DB_DIR)) {
                throw new RuntimeException('Failed to create database directory: ' . DB_DIR);
            }
        }

        // Ensure .htaccess exists in DB directory for Apache protection
        $dbHtaccess = DB_DIR . '/.htaccess';
        if (!file_exists($dbHtaccess)) {
            file_put_contents($dbHtaccess, "Order deny,allow\nDeny from all\nRequire all denied\n");
        }

        $dsn = 'sqlite:' . DB_FILE;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$pdo = new PDO($dsn, null, null, $options);
            // Optimizations for SQLite concurrency on shared hosting
            self::$pdo->exec('PRAGMA journal_mode = WAL;');
            self::$pdo->exec('PRAGMA synchronous = NORMAL;');
            self::$pdo->exec('PRAGMA foreign_keys = ON;');
            self::$pdo->exec('PRAGMA busy_timeout = 5000;');

            // Initialize schema if not exists
            self::initSchema(self::$pdo);

            return self::$pdo;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new RuntimeException('Database initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Auto-initialize database tables and indices
     */
    private static function initSchema(PDO $pdo): void {
        // Table: emails
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS emails (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                recovery_email TEXT DEFAULT '',
                domain TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'available' CHECK(status IN ('available', 'downloaded', 'replaced')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                downloaded_at DATETIME NULL,
                replaced_at DATETIME NULL
            );
        ");

        // Indexes for ultra-fast stock querying and extraction
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_emails_domain_status ON emails(domain, status);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_emails_status ON emails(status);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_emails_email ON emails(email);");

        // Table: audit_logs
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                role TEXT NOT NULL,
                action TEXT NOT NULL,
                details TEXT NOT NULL,
                ip_address TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_audit_logs_created ON audit_logs(created_at DESC);");

        // Table: orders (Tracks client extraction history with exact accounts list)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number TEXT NOT NULL,
                client_username TEXT NOT NULL,
                domain TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                format TEXT NOT NULL,
                accounts_json TEXT NOT NULL,
                rate_per_mail REAL DEFAULT 18.0,
                total_price REAL DEFAULT 0.0,
                notes TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at DESC);");

        // Safe column additions for existing installations
        try {
            $pdo->exec("ALTER TABLE orders ADD COLUMN rate_per_mail REAL DEFAULT 18.0;");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE orders ADD COLUMN total_price REAL DEFAULT 0.0;");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE orders ADD COLUMN notes TEXT DEFAULT '';");
        } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE orders ADD COLUMN status TEXT DEFAULT 'delivered';");
        } catch (Exception $e) {}

        // Auto-sync existing reverted orders status
        try {
            $pdo->exec("UPDATE orders SET status = 'reverted' WHERE notes LIKE '%[REVERTED TO AVAILABLE STOCK]%';");
        } catch (Exception $e) {}

        // Auto-backfill total_price for legacy orders with 0 price
        try {
            $pdo->exec("UPDATE orders SET rate_per_mail = 18.0, total_price = quantity * 18.0 WHERE total_price IS NULL OR total_price = 0;");
        } catch (Exception $e) {}

        // Table: payments (Tracks payments received from client)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_username TEXT NOT NULL DEFAULT 'rana asim',
                amount REAL NOT NULL,
                payment_date DATETIME NOT NULL,
                payment_method TEXT DEFAULT 'Bank Transfer',
                reference_note TEXT DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_payments_date ON payments(payment_date DESC);");

        // Table: replacements (Tracks faulty accounts deducted from bill)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS replacements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                password TEXT DEFAULT '',
                recovery_email TEXT DEFAULT '',
                domain TEXT DEFAULT '',
                order_number TEXT DEFAULT '',
                rate_deduction REAL DEFAULT 18.0,
                reason TEXT DEFAULT 'Faulty / Replaced',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_replacements_email ON replacements(email);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_replacements_created ON replacements(created_at DESC);");

        // Table: settings (Stores portal settings like maintenance mode)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Insert default maintenance mode settings if not exist
        $pdo->exec("
            INSERT OR IGNORE INTO settings (key, value) VALUES 
            ('client_maintenance_mode', '0'),
            ('client_maintenance_message', 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!');
        ");
    }

    /**
     * Helper to log audit actions
     */
    public static function logAudit(string $username, string $role, string $action, string $details): void {
        try {
            $pdo = self::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmt = $pdo->prepare('INSERT INTO audit_logs (username, role, action, details, ip_address) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$username, $role, $action, $details, $ip]);
        } catch (Exception $e) {
            error_log('Audit log error: ' . $e->getMessage());
        }
    }
}
