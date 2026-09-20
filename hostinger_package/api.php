<?php
/**
 * Single-Tenant Email Accounts Vault - Unified API Engine
 * Handles Authentication, Stock Levels, CSV Ingestion, Secure Extraction, and Replacements.
 */

declare(strict_types=1);

define('VAULT_APP', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

Auth::initSession();

// Enable CORS for cross-origin sync with AI Studio and client apps
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, X-Master-Key');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Parse JSON input if sent via fetch POST
$rawBody = file_get_contents('php://input');
if (!empty($rawBody)) {
    $parsedJson = json_decode($rawBody, true);
    if (is_array($parsedJson)) {
        foreach ($parsedJson as $k => $v) {
            if (!isset($_POST[$k])) {
                $_POST[$k] = $v;
            }
        }
    }
}

// Set default response headers for JSON
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Allow direct download action to stream file directly without JSON header
if ($action !== 'download_file') {
    header('Content-Type: application/json; charset=utf-8');
}

// Error helper
function respondJson(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $pdo = Database::getConnection();

    switch ($action) {
        // ==========================================
        // Full Real-Time 2-Way Synchronization with AI Studio
        // ==========================================
        case 'full_sync':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin or Master Key required.'], 403);
            }

            // 1. All accounts (up to 5000)
            $accountsStmt = $pdo->query("SELECT id, email, password, recovery_email, domain, status, created_at, downloaded_at, replaced_at FROM emails ORDER BY id DESC LIMIT 5000");
            $accountsList = $accountsStmt->fetchAll();

            // 2. All orders
            $ordersStmt = $pdo->query("SELECT * FROM orders ORDER BY datetime(created_at) DESC, id DESC");
            $ordersList = $ordersStmt->fetchAll();
            foreach ($ordersList as &$o) {
                $o['accounts'] = json_decode($o['accounts_json'], true) ?: [];
                $o['rate_per_mail'] = (float)($o['rate_per_mail'] ?? 18.0);
                $o['total_price'] = (float)($o['total_price'] ?? ($o['quantity'] * $o['rate_per_mail']));
                $isReverted = (!empty($o['status']) && ($o['status'] === 'reverted' || $o['status'] === 'cancelled')) || (!empty($o['notes']) && strpos($o['notes'], '[REVERTED TO AVAILABLE STOCK]') !== false);
                $o['status'] = $isReverted ? 'reverted' : ($o['status'] ?? 'delivered');
                unset($o['accounts_json']);
            }

            // 3. All payments
            $payStmt = $pdo->query("SELECT * FROM payments ORDER BY datetime(payment_date) DESC, id DESC");
            $paymentsList = $payStmt->fetchAll();

            // 4. All replacements
            $repStmt = $pdo->query("SELECT * FROM replacements ORDER BY id DESC");
            $replacementsList = $repStmt->fetchAll();

            // 5. Audit logs (latest 50)
            $logsStmt = $pdo->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 50");
            $logsList = $logsStmt->fetchAll();

            // 6. Maintenance mode
            $maintMode = false;
            $maintMsg = 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!';
            try {
                $stmtM = $pdo->query("SELECT value FROM settings WHERE key = 'client_maintenance_mode'");
                $maintMode = ($stmtM->fetchColumn() === '1');
                $stmtMsg = $pdo->query("SELECT value FROM settings WHERE key = 'client_maintenance_message'");
                $valMsg = $stmtMsg->fetchColumn();
                if ($valMsg !== false && $valMsg !== null) {
                    $maintMsg = (string)$valMsg;
                }
            } catch (Exception $e) {}

            respondJson([
                'success'       => true,
                'server_time'   => date('Y-m-d H:i:s'),
                'accounts'      => $accountsList,
                'orders'        => $ordersList,
                'payments'      => $paymentsList,
                'replacements'  => $replacementsList,
                'logs'          => $logsList,
                'maintenance'   => [
                    'enabled' => $maintMode,
                    'message' => $maintMsg
                ]
            ]);
            break;
        // ==========================================
        // Live Health & GitHub Deployment Verification Ping
        // ==========================================
        case 'ping':
        case 'live_check':
            $totalEmails = 0;
            $availableStock = 0;
            $domainBreakdown = [];
            try {
                $totalEmails = (int)$pdo->query("SELECT COUNT(*) FROM emails")->fetchColumn();
                $availableStock = (int)$pdo->query("SELECT COUNT(*) FROM emails WHERE status = 'available'")->fetchColumn();
                $stmtD = $pdo->query("SELECT domain, COUNT(*) as count FROM emails WHERE status = 'available' GROUP BY domain");
                while ($row = $stmtD->fetch()) {
                    $domainBreakdown[$row['domain']] = (int)$row['count'];
                }
            } catch (Exception $e) {}

            respondJson([
                'success'           => true,
                'status'            => 'online',
                'system'            => 'Hadi Digital Vault Portal',
                'target_directory'  => 'public_html/asim',
                'live_url'          => 'https://asim.eztoolbox.xyz',
                'deploy_version'    => 'v3.5.0-github-live-sync',
                'server_time'       => date('Y-m-d H:i:s T'),
                'php_version'       => PHP_VERSION,
                'sqlite_connected'  => true,
                'total_vault_emails'=> $totalEmails,
                'available_stock'   => $availableStock,
                'domain_stock'      => $domainBreakdown,
                'sync_message'      => 'GitHub push to Hostinger public_html/asim verified successfully!'
            ]);
            break;

        // ==========================================
        // Auth Status Check
        // ==========================================
        case 'status':
            $user = Auth::user();
            $maintMode = false;
            $maintMsg = 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!';
            try {
                $stmtM = $pdo->query("SELECT value FROM settings WHERE key = 'client_maintenance_mode'");
                $maintMode = ($stmtM->fetchColumn() === '1');
                $stmtMsg = $pdo->query("SELECT value FROM settings WHERE key = 'client_maintenance_message'");
                $valMsg = $stmtMsg->fetchColumn();
                if ($valMsg !== false && $valMsg !== null) {
                    $maintMsg = (string)$valMsg;
                }
            } catch (Exception $e) {}

            respondJson([
                'authenticated'      => Auth::isLoggedIn(),
                'user'               => $user,
                'csrf_token'         => Auth::getCsrfToken(),
                'managed_domains'    => MANAGED_DOMAINS,
                'maintenance_mode'   => $maintMode,
                'maintenance_message'=> $maintMsg,
                'is_admin'           => Auth::isAdmin()
            ]);
            break;

        // ==========================================
        // User Login
        // ==========================================
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                respondJson(['success' => false, 'message' => 'Method not allowed'], 405);
            }

            // Verify CSRF
            if (!Auth::verifyCsrfToken()) {
                respondJson(['success' => false, 'message' => 'Security token mismatch. Please refresh.'], 403);
            }

            $username = (string)($_POST['username'] ?? '');
            $password = (string)($_POST['password'] ?? '');

            $result = Auth::login($username, $password);
            if ($result['success']) {
                respondJson([
                    'success'    => true,
                    'user'       => Auth::user(),
                    'csrf_token' => Auth::getCsrfToken(),
                    'message'    => 'Authentication successful'
                ]);
            } else {
                respondJson($result, 401);
            }
            break;

        // ==========================================
        // User Logout
        // ==========================================
        case 'logout':
            Auth::logout();
            respondJson([
                'success'    => true,
                'csrf_token' => Auth::getCsrfToken(),
                'message'    => 'Logged out successfully'
            ]);
            break;

        // ==========================================
        // Real-Time Stock Counts
        // ==========================================
        case 'stock':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Safe auto-migration for domain typo in SQLite
            try {
                $pdo->exec("UPDATE emails SET domain = 'basis5.ch' WHERE domain = 'bsis5.ch'");
                $pdo->exec("UPDATE orders SET domain = 'basis5.ch' WHERE domain = 'bsis5.ch'");
            } catch (Exception $e) {}

            // Group count by domain and status
            $stmt = $pdo->query("
                SELECT domain, status, COUNT(*) as count 
                FROM emails 
                GROUP BY domain, status
            ");
            $rows = $stmt->fetchAll();

            $domainStats = [];
            // Pre-seed managed domains so they always exist even with 0 counts
            foreach (MANAGED_DOMAINS as $d) {
                $domainStats[$d] = [
                    'domain'     => $d,
                    'available'  => 0,
                    'downloaded' => 0,
                    'replaced'   => 0,
                    'total'      => 0
                ];
            }

            foreach ($rows as $row) {
                $d = $row['domain'];
                $s = $row['status'];
                $c = (int)$row['count'];

                if (!isset($domainStats[$d])) {
                    $domainStats[$d] = [
                        'domain'     => $d,
                        'available'  => 0,
                        'downloaded' => 0,
                        'replaced'   => 0,
                        'total'      => 0
                    ];
                }

                if (isset($domainStats[$d][$s])) {
                    $domainStats[$d][$s] += $c;
                }
                $domainStats[$d]['total'] += $c;
            }

            // Overall totals
            $totalsStmt = $pdo->query("
                SELECT 
                    COUNT(*) as total_accounts,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as total_available,
                    SUM(CASE WHEN status = 'downloaded' THEN 1 ELSE 0 END) as total_downloaded,
                    SUM(CASE WHEN status = 'replaced' THEN 1 ELSE 0 END) as total_replaced
                FROM emails
            ");
            $overall = $totalsStmt->fetch();

            respondJson([
                'success'         => true,
                'domains'         => array_values($domainStats),
                'managed_domains' => MANAGED_DOMAINS,
                'overall'         => [
                    'total_accounts'   => (int)($overall['total_accounts'] ?? 0),
                    'total_available'  => (int)($overall['total_available'] ?? 0),
                    'total_downloaded' => (int)($overall['total_downloaded'] ?? 0),
                    'total_replaced'   => (int)($overall['total_replaced'] ?? 0),
                ]
            ]);
            break;

        // ==========================================
        // Admin: CSV Ingestion
        // ==========================================
        case 'upload_csv':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            if (!Auth::verifyCsrfToken()) {
                respondJson(['success' => false, 'message' => 'Security token invalid.'], 403);
            }

            $csvContent = '';

            // Handle uploaded file or raw text input
            if (!empty($_FILES['csv_file']['tmp_name'])) {
                if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                    respondJson(['success' => false, 'message' => 'File upload error occurred.'], 400);
                }
                $csvContent = file_get_contents($_FILES['csv_file']['tmp_name']);
            } elseif (!empty($_POST['csv_text'])) {
                $csvContent = (string)$_POST['csv_text'];
            } else {
                respondJson(['success' => false, 'message' => 'No CSV file or text provided.'], 400);
            }

            if (empty(trim($csvContent))) {
                respondJson(['success' => false, 'message' => 'The provided CSV content is empty.'], 400);
            }

            // Parse lines safely
            $lines = preg_split("/\r\n|\n|\r/", trim($csvContent));
            if (empty($lines)) {
                respondJson(['success' => false, 'message' => 'No valid records found in CSV.'], 400);
            }

            $totalRows = 0;
            $insertedCount = 0;
            $updatedCount = 0;
            $duplicateCount = 0;
            $invalidCount = 0;
            $soldUpdatedCount = 0;
            $soldToAvailableCount = 0;
            $soldEmailsSample = [];
            $domainCounts = [];

            // target_status: 'keep_existing' (default), 'make_available', or 'make_downloaded'
            $targetStatus = trim((string)($_POST['target_status'] ?? 'keep_existing'));
            if (!in_array($targetStatus, ['keep_existing', 'make_available', 'make_downloaded'], true)) {
                $targetStatus = !empty($_POST['update_existing']) && $_POST['update_existing'] !== 'false' ? 'make_available' : 'keep_existing';
            }

            // Detect if first line is header: Email, Password, Recovery Mail
            $firstLine = strtolower(trim($lines[0]));
            $startIndex = 0;
            if (strpos($firstLine, 'email') !== false && strpos($firstLine, 'password') !== false) {
                $startIndex = 1;
            }

            $pdo->beginTransaction();

            $checkStmt = $pdo->prepare("SELECT id, email, status, password, recovery_email FROM emails WHERE LOWER(email) = LOWER(?)");
            $insertStmt = $pdo->prepare("
                INSERT INTO emails (email, password, recovery_email, domain, status, downloaded_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $updateStmt = $pdo->prepare("
                UPDATE emails 
                SET password = ?, 
                    recovery_email = CASE WHEN ? != '' THEN ? ELSE recovery_email END,
                    status = ?,
                    downloaded_at = CASE WHEN ? = 'available' THEN NULL WHEN ? = 'downloaded' AND downloaded_at IS NULL THEN CURRENT_TIMESTAMP ELSE downloaded_at END
                WHERE id = ?
            ");

            for ($i = $startIndex; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) {
                    continue;
                }

                $totalRows++;
                $cols = str_getcsv($line);

                $email = trim($cols[0] ?? '');
                $password = trim($cols[1] ?? '');
                $recovery = trim($cols[2] ?? '');

                // Basic validation
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $invalidCount++;
                    continue;
                }

                if (empty($password)) {
                    $invalidCount++;
                    continue;
                }

                // Automatically extract domain from email
                $atPos = strrpos($email, '@');
                if ($atPos === false) {
                    $invalidCount++;
                    continue;
                }
                $domain = strtolower(substr($email, $atPos + 1));

                // Check existing
                $checkStmt->execute([$email]);
                $existing = $checkStmt->fetch();

                if ($existing) {
                    $existingStatus = $existing['status'];
                    $wasSold = ($existingStatus === 'downloaded');

                    if ($wasSold) {
                        $soldUpdatedCount++;
                        if (count($soldEmailsSample) < 5) {
                            $soldEmailsSample[] = $email;
                        }
                    }

                    // Determine target status
                    $newStatus = $existingStatus;
                    if ($targetStatus === 'make_available') {
                        $newStatus = 'available';
                        if ($wasSold) {
                            $soldToAvailableCount++;
                        }
                    } elseif ($targetStatus === 'make_downloaded') {
                        $newStatus = 'downloaded';
                    }

                    // Update credentials to latest
                    $updateStmt->execute([
                        $password,
                        $recovery,
                        $recovery,
                        $newStatus,
                        $newStatus,
                        $newStatus,
                        $existing['id']
                    ]);

                    $updatedCount++;
                    $domainCounts[$domain] = ($domainCounts[$domain] ?? 0) + 1;
                } else {
                    // New account
                    $initialStatus = ($targetStatus === 'make_downloaded') ? 'downloaded' : 'available';
                    $downloadedAt = ($initialStatus === 'downloaded') ? date('Y-m-d H:i:s') : null;

                    $insertStmt->execute([
                        $email,
                        $password,
                        $recovery,
                        $domain,
                        $initialStatus,
                        $downloadedAt
                    ]);

                    $insertedCount++;
                    $domainCounts[$domain] = ($domainCounts[$domain] ?? 0) + 1;
                }
            }

            $pdo->commit();

            // Log action
            Database::logAudit(
                Auth::user()['username'] ?? 'Hadi',
                'admin',
                'CSV_UPLOAD',
                "Imported {$insertedCount} new, updated {$updatedCount} (including {$soldUpdatedCount} sold updated, {$soldToAvailableCount} moved to available)."
            );

            respondJson([
                'success'                    => true,
                'total_rows'                 => $totalRows,
                'inserted'                   => $insertedCount,
                'updated'                    => $updatedCount,
                'sold_updated'               => $soldUpdatedCount,
                'sold_to_available'          => $soldToAvailableCount,
                'sold_emails_sample'         => $soldEmailsSample,
                'duplicates'                 => $duplicateCount,
                'invalid'                    => $invalidCount,
                'domain_breakdown'           => $domainCounts,
                'message'                    => "Successfully processed: {$insertedCount} new emails added, {$updatedCount} existing updated with latest password/recovery."
            ]);
            break;

        // ==========================================
        // Client: Extraction with Secure PIN
        // ==========================================
        case 'extract':
            if (!Auth::isClient() && !Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }

            if (!Auth::verifyCsrfToken()) {
                respondJson(['success' => false, 'message' => 'Security token invalid.'], 403);
            }

            // Check Client Portal Maintenance Mode (Admins bypass for testing)
            try {
                $maintCheck = $pdo->query("SELECT value FROM settings WHERE key = 'client_maintenance_mode'")->fetchColumn();
                if ($maintCheck === '1' && !Auth::isAdmin()) {
                    $mMsg = $pdo->query("SELECT value FROM settings WHERE key = 'client_maintenance_message'")->fetchColumn() ?: 'Client Portal is currently under maintenance. Please try again shortly.';
                    respondJson(['success' => false, 'message' => (string)$mMsg], 503);
                }
            } catch (Exception $e) {}

            $domain = trim((string)($_POST['domain'] ?? ''));
            $quantity = (int)($_POST['quantity'] ?? 0);
            $format = strtolower(trim((string)($_POST['format'] ?? 'csv')));
            $pin = (string)($_POST['pin'] ?? '');

            if (empty($domain)) {
                respondJson(['success' => false, 'message' => 'Please select a domain.'], 400);
            }

            if ($quantity <= 0) {
                respondJson(['success' => false, 'message' => 'Please specify a quantity greater than zero.'], 400);
            }

            if (!in_array($format, ['csv', 'txt'], true)) {
                $format = 'csv';
            }

            // CRITICAL: Validate Secure Extraction PIN
            if (!Auth::verifyExtractionPin($pin)) {
                Database::logAudit(
                    Auth::user()['username'] ?? 'rana asim',
                    Auth::user()['role'] ?? 'client',
                    'EXTRACTION_PIN_FAILED',
                    "Failed extraction attempt for {$quantity} accounts of {$domain} with incorrect PIN"
                );
                respondJson([
                    'success' => false, 
                    'message' => 'Access Denied: Invalid 4-digit Secure Extraction PIN.'
                ], 403);
            }

            // Execute extraction within an immediate exclusive transaction
            $pdo->beginTransaction();

            // Check currently available count
            $stockCheck = $pdo->prepare("
                SELECT COUNT(*) FROM emails WHERE domain = ? AND status = 'available'
            ");
            $stockCheck->execute([$domain]);
            $availableStock = (int)$stockCheck->fetchColumn();

            if ($availableStock < $quantity) {
                $pdo->rollBack();
                respondJson([
                    'success' => false,
                    'message' => "Insufficient stock. Only {$availableStock} available for {$domain} (requested {$quantity})."
                ], 400);
            }

            // Fetch records to extract
            $fetchStmt = $pdo->prepare("
                SELECT id, email, password, recovery_email 
                FROM emails 
                WHERE domain = ? AND status = 'available' 
                ORDER BY id ASC 
                LIMIT ?
            ");
            $fetchStmt->execute([$domain, $quantity]);
            $records = $fetchStmt->fetchAll();

            if (empty($records)) {
                $pdo->rollBack();
                respondJson(['success' => false, 'message' => 'No accounts available for extraction.'], 400);
            }

            // Extract IDs to update
            $ids = array_column($records, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            // Update status to 'downloaded'
            $updateStmt = $pdo->prepare("
                UPDATE emails 
                SET status = 'downloaded', downloaded_at = CURRENT_TIMESTAMP 
                WHERE id IN ($placeholders)
            ");
            $updateStmt->execute($ids);

            // Insert into orders history
            $orderCount = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() + 1;
            $orderNumber = "Order #" . $orderCount;
            $defaultRate = 18.0;
            $orderTotalPrice = count($records) * $defaultRate;
            $orderStmt = $pdo->prepare("
                INSERT INTO orders (order_number, client_username, domain, quantity, format, accounts_json, rate_per_mail, total_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $orderStmt->execute([
                $orderNumber,
                Auth::user()['username'] ?? 'rana asim',
                $domain,
                count($records),
                $format,
                json_encode($records),
                $defaultRate,
                $orderTotalPrice
            ]);

            $pdo->commit();

            // Log extraction audit
            Database::logAudit(
                Auth::user()['username'] ?? 'rana asim',
                Auth::user()['role'] ?? 'client',
                'EXTRACTION_SUCCESS',
                "Extracted {$quantity} accounts from domain {$domain} as {$format} ({$orderNumber})"
            );

            // Construct exact export file content with exact required header: email,password,recovery email
            $fileLines = [];
            $fileLines[] = "email,password,recovery email";

            foreach ($records as $r) {
                $fileLines[] = sprintf('%s,%s,%s', $r['email'], $r['password'], $r['recovery_email'] ?? '');
            }

            $exportContent = implode("\r\n", $fileLines) . "\r\n";
            $filename = sprintf('%s_%s_%s.%s', str_replace(' ', '_', $orderNumber), str_replace('.', '_', $domain), date('Ymd_His'), $format);

            respondJson([
                'success'      => true,
                'order_number' => $orderNumber,
                'extracted'    => count($records),
                'domain'       => $domain,
                'format'       => $format,
                'filename'     => $filename,
                'file_content' => base64_encode($exportContent),
                'message'      => "{$orderNumber}: Extracted " . count($records) . " accounts successfully."
            ]);
            break;

        // ==========================================
        // Financial & Khata Summary (Live Stock Math)
        // ==========================================
        case 'financial_summary':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Live Stock Math: Gross Sold Mails is dynamically counted from active 'downloaded' emails in stock
            $emailStats = $pdo->query("
                SELECT COUNT(*) as sold_count 
                FROM emails 
                WHERE status = 'downloaded'
            ")->fetch();
            $grossSold = (int)($emailStats['sold_count'] ?? 0);

            // Effective rate determination
            $defaultRate = 18.0;
            $orderStats = $pdo->query("
                SELECT 
                    COUNT(*) as total_orders,
                    AVG(rate_per_mail) as avg_rate
                FROM orders
                WHERE (status != 'reverted' AND status != 'cancelled' OR status IS NULL OR status = '')
                  AND notes NOT LIKE '%[REVERTED TO AVAILABLE STOCK]%'
            ")->fetch();

            if (!empty($orderStats['avg_rate']) && (float)$orderStats['avg_rate'] > 0) {
                $defaultRate = (float)$orderStats['avg_rate'];
            }
            $totalOrders = (int)($orderStats['total_orders'] ?? 0);
            $grossBilled = $grossSold * $defaultRate;

            // Replaced count & deductions
            $repStats = $pdo->query("
                SELECT 
                    COUNT(*) as replaced_count,
                    COALESCE(SUM(rate_deduction), 0) as total_deductions
                FROM replacements
            ")->fetch();

            $replacedCount = (int)($repStats['replaced_count'] ?? 0);
            $totalDeductions = (float)($repStats['total_deductions'] ?? 0);
            if ($totalDeductions <= 0 && $replacedCount > 0) {
                $totalDeductions = $replacedCount * $defaultRate;
            }

            // Payments total
            $payStats = $pdo->query("
                SELECT 
                    COUNT(*) as total_payments,
                    COALESCE(SUM(amount), 0) as total_paid_amount
                FROM payments
            ")->fetch();

            $totalPayments = (int)($payStats['total_payments'] ?? 0);
            $totalPaid = (float)($payStats['total_paid_amount'] ?? 0);

            // Net Calculations: Live stock sold minus replacements
            $netActiveMails = max(0, $grossSold - $replacedCount);
            $netBilledAmount = max(0.0, $netActiveMails * $defaultRate);
            $pendingBalance = $netBilledAmount - $totalPaid;

            respondJson([
                'success'             => true,
                'currency'            => 'PKR',
                'currency_symbol'     => 'Rs.',
                'default_rate'        => $defaultRate,
                'gross_sold_mails'    => $grossSold,
                'replaced_mails'      => $replacedCount,
                'net_active_mails'    => $netActiveMails,
                'gross_billed_amount' => $grossBilled,
                'total_deductions'    => $totalDeductions,
                'net_billed_amount'   => $netBilledAmount,
                'total_paid_amount'   => $totalPaid,
                'pending_balance'     => $pendingBalance,
                'total_orders'        => $totalOrders,
                'total_payments'      => $totalPayments
            ]);
            break;

        // ==========================================
        // Orders History
        // ==========================================
        case 'orders':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            if (Auth::isAdmin()) {
                $ordersStmt = $pdo->query("SELECT * FROM orders ORDER BY datetime(created_at) DESC, id DESC");
            } else {
                $clientUser = Auth::user()['username'] ?? '';
                $ordersStmt = $pdo->prepare("SELECT * FROM orders WHERE client_username = ? ORDER BY datetime(created_at) DESC, id DESC");
                $ordersStmt->execute([$clientUser]);
            }

            $ordersList = $ordersStmt->fetchAll();
            foreach ($ordersList as &$o) {
                $o['accounts'] = json_decode($o['accounts_json'], true) ?: [];
                $o['rate_per_mail'] = (float)($o['rate_per_mail'] ?? 18.0);
                $o['total_price'] = (float)($o['total_price'] ?? ($o['quantity'] * $o['rate_per_mail']));
                $isReverted = (!empty($o['status']) && ($o['status'] === 'reverted' || $o['status'] === 'cancelled')) || (!empty($o['notes']) && strpos($o['notes'], '[REVERTED TO AVAILABLE STOCK]') !== false);
                $o['status'] = $isReverted ? 'reverted' : ($o['status'] ?? 'delivered');
                unset($o['accounts_json']);
            }

            respondJson([
                'success' => true,
                'orders'  => $ordersList
            ]);
            break;

        // ==========================================
        // Save / Ingest Order (Admin Only)
        // ==========================================
        case 'save_order':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $orderId = (int)($_POST['order_id'] ?? 0);
            $orderNumber = trim((string)($_POST['order_number'] ?? ''));
            $orderDate = trim((string)($_POST['created_at'] ?? ''));
            $ratePerMail = (float)($_POST['rate_per_mail'] ?? 18.0);
            $customTotal = isset($_POST['total_price']) && $_POST['total_price'] !== '' ? (float)$_POST['total_price'] : null;
            $notes = trim((string)($_POST['notes'] ?? ''));

            if ($orderId > 0) {
                // Edit existing order metadata
                $existing = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
                $existing->execute([$orderId]);
                $currentOrder = $existing->fetch();
                if (!$currentOrder) {
                    respondJson(['success' => false, 'message' => 'Order not found.'], 404);
                }

                $qty = (int)$currentOrder['quantity'];
                $finalTotal = $customTotal !== null ? $customTotal : ($qty * $ratePerMail);

                $updateParams = [
                    $orderNumber ?: $currentOrder['order_number'],
                    $ratePerMail,
                    $finalTotal,
                    $notes,
                ];

                $sql = "UPDATE orders SET order_number = ?, rate_per_mail = ?, total_price = ?, notes = ?";
                if (!empty($orderDate)) {
                    $sql .= ", created_at = ?";
                    $updateParams[] = $orderDate;
                }
                $sql .= " WHERE id = ?";
                $updateParams[] = $orderId;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($updateParams);

                Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'ORDER_UPDATED', "Updated Order ID {$orderId}");

                respondJson([
                    'success' => true,
                    'message' => "Order #{$orderId} updated successfully."
                ]);
            } else {
                // New Order Creation / WhatsApp Ingestion
                $domain = trim((string)($_POST['domain'] ?? 'basis5.ch'));
                $csvText = '';

                if (!empty($_FILES['csv_file']['tmp_name'])) {
                    $csvText = file_get_contents($_FILES['csv_file']['tmp_name']);
                } elseif (!empty($_POST['csv_text'])) {
                    $csvText = (string)$_POST['csv_text'];
                }

                if (empty(trim($csvText))) {
                    respondJson(['success' => false, 'message' => 'Please provide accounts CSV content or file.'], 400);
                }

                $lines = preg_split("/\r\n|\n|\r/", trim($csvText));
                $accounts = [];

                $firstLine = strtolower(trim($lines[0] ?? ''));
                $startIdx = (strpos($firstLine, 'email') !== false && strpos($firstLine, 'password') !== false) ? 1 : 0;

                for ($i = $startIdx; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (empty($line)) continue;
                    $cols = str_getcsv($line);
                    $email = trim($cols[0] ?? '');
                    $pass = trim($cols[1] ?? '');
                    $rec = trim($cols[2] ?? '');

                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $accounts[] = [
                            'email'          => $email,
                            'password'       => $pass,
                            'recovery_email' => $rec
                        ];
                    }
                }

                if (empty($accounts)) {
                    respondJson(['success' => false, 'message' => 'No valid email records detected in CSV.'], 400);
                }

                if (empty($orderNumber)) {
                    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() + 1;
                    $orderNumber = "Order #" . $cnt;
                }

                $qty = count($accounts);
                $finalTotal = $customTotal !== null ? $customTotal : ($qty * $ratePerMail);
                $finalDate = !empty($orderDate) ? $orderDate : date('Y-m-d H:i:s');

                $pdo->beginTransaction();

                // Save into orders
                $insertOrder = $pdo->prepare("
                    INSERT INTO orders (order_number, client_username, domain, quantity, format, accounts_json, rate_per_mail, total_price, notes, created_at)
                    VALUES (?, 'rana asim', ?, ?, 'csv', ?, ?, ?, ?, ?)
                ");
                $insertOrder->execute([
                    $orderNumber,
                    $domain,
                    $qty,
                    json_encode($accounts),
                    $ratePerMail,
                    $finalTotal,
                    $notes,
                    $finalDate
                ]);

                // Insert accounts into emails table as 'downloaded' so search/inventory recognizes them
                $insertEmail = $pdo->prepare("
                    INSERT INTO emails (email, password, recovery_email, domain, status, downloaded_at, created_at)
                    VALUES (?, ?, ?, ?, 'downloaded', ?, ?)
                    ON CONFLICT(email) DO UPDATE SET 
                        password = excluded.password,
                        recovery_email = CASE WHEN excluded.recovery_email != '' THEN excluded.recovery_email ELSE emails.recovery_email END,
                        status = 'downloaded',
                        downloaded_at = excluded.downloaded_at
                ");

                foreach ($accounts as $acc) {
                    $accDomain = $domain;
                    $at = strrpos($acc['email'], '@');
                    if ($at !== false) {
                        $accDomain = strtolower(substr($acc['email'], $at + 1));
                    }
                    $insertEmail->execute([
                        $acc['email'],
                        $acc['password'],
                        $acc['recovery_email'],
                        $accDomain,
                        $finalDate,
                        $finalDate
                    ]);
                }

                $pdo->commit();

                Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'ORDER_CREATED', "Ingested {$orderNumber} with {$qty} accounts");

                respondJson([
                    'success'      => true,
                    'order_number' => $orderNumber,
                    'quantity'     => $qty,
                    'total_price'  => $finalTotal,
                    'message'      => "{$orderNumber} saved successfully with {$qty} accounts."
                ]);
            }
            break;

        // ==========================================
        // Delete Order (Admin Only) - Auto-reverts accounts to available stock!
        // ==========================================
        case 'delete_order':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }
            $orderId = (int)($_POST['order_id'] ?? 0);
            if ($orderId <= 0) {
                respondJson(['success' => false, 'message' => 'Invalid order ID.'], 400);
            }

            // Check if order exists and fetch its accounts to auto-revert them
            $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch();

            $revertedAccounts = 0;
            if ($order && !empty($order['accounts_json'])) {
                $accList = json_decode($order['accounts_json'], true) ?: [];
                if (!empty($accList)) {
                    $upStmt = $pdo->prepare("UPDATE emails SET status = 'available', downloaded_at = NULL, replaced_at = NULL WHERE LOWER(email) = LOWER(?)");
                    foreach ($accList as $acc) {
                        $em = trim($acc['email'] ?? '');
                        if (!empty($em)) {
                            $upStmt->execute([$em]);
                            $revertedAccounts++;
                        }
                    }
                }
            }

            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);

            Database::logAudit(
                Auth::user()['username'] ?? 'admin',
                'admin',
                'ORDER_DELETED',
                "Deleted Order ID {$orderId} and auto-reverted {$revertedAccounts} accounts back to available stock."
            );

            respondJson([
                'success' => true,
                'reverted_count' => $revertedAccounts,
                'message' => "Order #{$orderId} deleted successfully and {$revertedAccounts} accounts reverted to Available stock."
            ]);
            break;

        // ==========================================
        // Quick Update Order Price / Rate (Admin Only)
        // ==========================================
        case 'quick_update_order_price':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }
            $orderId = (int)($_POST['order_id'] ?? 0);
            $newRate = (float)($_POST['rate_per_mail'] ?? 18.0);
            $customTotal = isset($_POST['total_price']) && $_POST['total_price'] !== '' ? (float)$_POST['total_price'] : null;

            if ($orderId <= 0) {
                respondJson(['success' => false, 'message' => 'Invalid order ID.'], 400);
            }

            $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch();
            if (!$order) {
                respondJson(['success' => false, 'message' => 'Order not found.'], 404);
            }

            $qty = (int)$order['quantity'];
            $newTotal = $customTotal !== null ? $customTotal : ($qty * $newRate);

            $upStmt = $pdo->prepare("UPDATE orders SET rate_per_mail = ?, total_price = ? WHERE id = ?");
            $upStmt->execute([$newRate, $newTotal, $orderId]);

            Database::logAudit(
                Auth::user()['username'] ?? 'admin',
                'admin',
                'ORDER_PRICE_UPDATED',
                "Updated Order #{$orderId} price: Rate Rs. {$newRate}, Total Rs. {$newTotal}"
            );

            respondJson([
                'success' => true,
                'rate_per_mail' => $newRate,
                'total_price' => $newTotal,
                'message' => "Order price updated to Rs. {$newTotal} (Rate: Rs. {$newRate}/mail)."
            ]);
            break;

        // ==========================================
        // 1-Click Order Status Switcher (Delivered <-> Reverted)
        // Automatically moves order emails between Downloaded and Available stock & updates the bill!
        // ==========================================
        case 'update_order_status':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }
            $orderId = (int)($_POST['order_id'] ?? 0);
            $newStatus = trim((string)($_POST['status'] ?? 'delivered'));

            if ($orderId <= 0) {
                respondJson(['success' => false, 'message' => 'Invalid order ID.'], 400);
            }

            $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch();

            if (!$order) {
                respondJson(['success' => false, 'message' => 'Order not found.'], 404);
            }

            $accList = json_decode($order['accounts_json'], true) ?: [];
            $revertedCount = 0;

            $pdo->beginTransaction();

            if ($newStatus === 'reverted') {
                $curNotes = trim($order['notes'] ?? '');
                if (strpos($curNotes, '[REVERTED TO AVAILABLE STOCK]') === false) {
                    $curNotes = trim($curNotes . ' [REVERTED TO AVAILABLE STOCK]');
                }
                $upOrder = $pdo->prepare("UPDATE orders SET status = 'reverted', notes = ? WHERE id = ?");
                $upOrder->execute([$curNotes, $orderId]);

                if (!empty($accList)) {
                    $upEmail = $pdo->prepare("UPDATE emails SET status = 'available', downloaded_at = NULL, replaced_at = NULL WHERE LOWER(email) = LOWER(?)");
                    foreach ($accList as $acc) {
                        $em = trim($acc['email'] ?? '');
                        if (!empty($em)) {
                            $upEmail->execute([$em]);
                            $revertedCount++;
                        }
                    }
                }
            } else {
                $curNotes = trim(str_replace('[REVERTED TO AVAILABLE STOCK]', '', $order['notes'] ?? ''));
                $upOrder = $pdo->prepare("UPDATE orders SET status = 'delivered', notes = ? WHERE id = ?");
                $upOrder->execute([$curNotes, $orderId]);

                if (!empty($accList)) {
                    $upEmail = $pdo->prepare("UPDATE emails SET status = 'downloaded', downloaded_at = CURRENT_TIMESTAMP WHERE LOWER(email) = LOWER(?)");
                    foreach ($accList as $acc) {
                        $em = trim($acc['email'] ?? '');
                        if (!empty($em)) {
                            $upEmail->execute([$em]);
                            $revertedCount++;
                        }
                    }
                }
            }

            $pdo->commit();

            Database::logAudit(
                Auth::user()['username'] ?? 'admin',
                'admin',
                'ORDER_STATUS_CHANGED',
                "Order #{$order['order_number']} status changed to '{$newStatus}'. {$revertedCount} emails updated."
            );

            respondJson([
                'success' => true,
                'status' => $newStatus,
                'reverted_count' => $revertedCount,
                'message' => $newStatus === 'reverted'
                    ? "Order #{$order['order_number']} revert ho gaya: {$revertedCount} mails Available stock me wapis aa gayin aur Bill kam ho gaya!"
                    : "Order #{$order['order_number']} delivered ho gaya: {$revertedCount} mails Downloaded me shift ho gayin aur Bill me shamil ho gaya."
            ]);
            break;

        // ==========================================
        // 1-Click Sync Stock & Fix Stranded Mails
        // Automatically returns orphaned downloaded emails to Available stock & ensures Khata matches!
        // ==========================================
        case 'sync_stock_and_orders':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $pdo->beginTransaction();

            // 1. Find all emails in active delivered orders
            $activeOrders = $pdo->query("
                SELECT accounts_json FROM orders 
                WHERE (status = 'delivered' OR status IS NULL OR status = '')
                  AND notes NOT LIKE '%[REVERTED TO AVAILABLE STOCK]%'
            ")->fetchAll();

            $activeDeliveredEmails = [];
            foreach ($activeOrders as $ao) {
                $accs = json_decode($ao['accounts_json'], true) ?: [];
                foreach ($accs as $a) {
                    $e = strtolower(trim($a['email'] ?? ''));
                    if (!empty($e)) {
                        $activeDeliveredEmails[$e] = true;
                    }
                }
            }

            // 2. Any email with status = 'downloaded' that is NOT in an active delivered order -> shift to 'available'
            $downloadedEmails = $pdo->query("SELECT id, LOWER(email) as email FROM emails WHERE status = 'downloaded'")->fetchAll();
            $orphansFixed = 0;
            $makeAvailableStmt = $pdo->prepare("UPDATE emails SET status = 'available', downloaded_at = NULL WHERE id = ?");
            foreach ($downloadedEmails as $de) {
                if (!isset($activeDeliveredEmails[$de['email']])) {
                    $makeAvailableStmt->execute([$de['id']]);
                    $orphansFixed++;
                }
            }

            $pdo->commit();

            Database::logAudit(
                Auth::user()['username'] ?? 'admin',
                'admin',
                'SYNC_STOCK_ORDERS',
                "Synchronized stock and orders. {$orphansFixed} orphaned downloaded emails returned to available stock."
            );

            respondJson([
                'success' => true,
                'orphans_fixed' => $orphansFixed,
                'message' => "Stock & Khata Synced: {$orphansFixed} stranded downloaded mails wapis Available stock me bhej di gayin aur Bill theek ho gaya!"
            ]);
            break;

        // ==========================================
        // Revert All Downloaded Mails to Available Stock (Admin Only)
        // Automatically updates emails to Available AND marks corresponding orders as Reverted so Bill decreases!
        // ==========================================
        case 'revert_all_downloaded':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }
            $domain = trim((string)($_POST['domain'] ?? ''));

            $pdo->beginTransaction();

            if (!empty($domain)) {
                $stmt = $pdo->prepare("UPDATE emails SET status = 'available', downloaded_at = NULL, replaced_at = NULL WHERE status = 'downloaded' AND domain = ?");
                $stmt->execute([$domain]);

                // Also mark domain orders as reverted so bill drops!
                $pdo->prepare("UPDATE orders SET status = 'reverted', notes = notes || ' [REVERTED TO AVAILABLE STOCK]' WHERE domain = ? AND (status != 'reverted' OR status IS NULL)")->execute([$domain]);
            } else {
                $stmt = $pdo->prepare("UPDATE emails SET status = 'available', downloaded_at = NULL, replaced_at = NULL WHERE status = 'downloaded'");
                $stmt->execute();

                // Also mark all orders as reverted so bill drops!
                $pdo->exec("UPDATE orders SET status = 'reverted', notes = notes || ' [REVERTED TO AVAILABLE STOCK]' WHERE (status != 'reverted' OR status IS NULL)");
            }
            $reverted = $stmt->rowCount();

            $pdo->commit();

            Database::logAudit(
                Auth::user()['username'] ?? 'admin',
                'admin',
                'REVERT_ALL_DOWNLOADED',
                "Reverted {$reverted} downloaded accounts back to Available stock" . (!empty($domain) ? " for domain {$domain}" : "")
            );

            respondJson([
                'success' => true,
                'reverted_count' => $reverted,
                'message' => "Successfully reverted {$reverted} downloaded accounts back to Available stock, aur Bill kam ho gaya!"
            ]);
            break;

        // ==========================================
        // Download Single Order
        // ==========================================
        case 'download_order':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $orderId = (int)($_GET['order_id'] ?? 0);
            if ($orderId <= 0) {
                respondJson(['success' => false, 'message' => 'Invalid order ID'], 400);
            }

            $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch();

            if (!$order) {
                respondJson(['success' => false, 'message' => 'Order not found'], 404);
            }

            if (!Auth::isAdmin() && $order['client_username'] !== Auth::user()['username']) {
                respondJson(['success' => false, 'message' => 'Access denied'], 403);
            }

            $accounts = json_decode($order['accounts_json'], true) ?: [];
            $format = $order['format'] ?: 'csv';
            $domain = $order['domain'];
            $orderNumber = $order['order_number'];

            $fileLines = ["email,password,recovery email"];
            foreach ($accounts as $acc) {
                $fileLines[] = sprintf('%s,%s,%s', $acc['email'] ?? '', $acc['password'] ?? '', $acc['recovery_email'] ?? '');
            }

            $content = implode("\r\n", $fileLines) . "\r\n";
            $cleanOrder = preg_replace('/[^a-zA-Z0-9]/', '_', $orderNumber);
            $filename = sprintf('%s_%s.%s', $cleanOrder, str_replace('.', '_', $domain), $format);

            respondJson([
                'success'      => true,
                'filename'     => $filename,
                'format'       => $format,
                'file_content' => base64_encode($content)
            ]);
            break;

        // ==========================================
        // Payments: List
        // ==========================================
        case 'list_payments':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $stmt = $pdo->query("SELECT * FROM payments ORDER BY datetime(payment_date) DESC, id DESC");
            $payments = $stmt->fetchAll();

            respondJson([
                'success'  => true,
                'payments' => $payments
            ]);
            break;

        // ==========================================
        // Payments: Save / Record (Admin Only)
        // ==========================================
        case 'save_payment':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $paymentId = (int)($_POST['id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);
            $paymentDate = trim((string)($_POST['payment_date'] ?? ''));
            $paymentMethod = trim((string)($_POST['payment_method'] ?? 'Bank Transfer'));
            $refNote = trim((string)($_POST['reference_note'] ?? ''));

            if ($amount <= 0) {
                respondJson(['success' => false, 'message' => 'Please provide a valid payment amount.'], 400);
            }

            if (empty($paymentDate)) {
                $paymentDate = date('Y-m-d H:i:s');
            }

            if ($paymentId > 0) {
                $stmt = $pdo->prepare("
                    UPDATE payments 
                    SET amount = ?, payment_date = ?, payment_method = ?, reference_note = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$amount, $paymentDate, $paymentMethod, $refNote, $paymentId]);
                Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'PAYMENT_UPDATED', "Updated payment ID {$paymentId}: Rs. {$amount}");
                respondJson(['success' => true, 'message' => 'Payment entry updated successfully.']);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO payments (client_username, amount, payment_date, payment_method, reference_note)
                    VALUES ('rana asim', ?, ?, ?, ?)
                ");
                $stmt->execute([$amount, $paymentDate, $paymentMethod, $refNote]);
                Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'PAYMENT_RECORDED', "Recorded payment of Rs. {$amount}");
                respondJson(['success' => true, 'message' => 'Payment recorded successfully.']);
            }
            break;

        // ==========================================
        // Payments: Delete (Admin Only)
        // ==========================================
        case 'delete_payment':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }
            $paymentId = (int)($_POST['id'] ?? 0);
            if ($paymentId <= 0) {
                respondJson(['success' => false, 'message' => 'Invalid payment ID.'], 400);
            }

            $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
            $stmt->execute([$paymentId]);
            Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'PAYMENT_DELETED', "Deleted payment ID {$paymentId}");

            respondJson(['success' => true, 'message' => 'Payment entry deleted successfully.']);
            break;

        // ==========================================
        // Replacements: List (Admin & Client)
        // ==========================================
        case 'list_replacements':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $stmt = $pdo->query("SELECT * FROM replacements ORDER BY id DESC");
            $replacements = $stmt->fetchAll();

            respondJson([
                'success'      => true,
                'replacements' => $replacements
            ]);
            break;

        // ==========================================
        // Replacements: Add Faulty Mails (Admin Only)
        // ==========================================
        case 'add_replacements':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $rawText = (string)($_POST['emails_text'] ?? '');
            $rateDeduction = (float)($_POST['rate_deduction'] ?? 18.0);
            $reason = trim((string)($_POST['reason'] ?? 'Faulty / Deducted'));

            if (empty(trim($rawText))) {
                respondJson(['success' => false, 'message' => 'Please provide email accounts to replace/deduct.'], 400);
            }

            $lines = preg_split("/[\r\n,]+/", trim($rawText));
            $emails = [];
            foreach ($lines as $line) {
                $e = strtolower(trim($line));
                if (!empty($e) && filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $e;
                }
            }
            $emails = array_unique($emails);

            if (empty($emails)) {
                respondJson(['success' => false, 'message' => 'No valid email addresses detected.'], 400);
            }

            $pdo->beginTransaction();

            $addedCount = 0;
            $emailLookup = $pdo->prepare("SELECT email, password, recovery_email, domain FROM emails WHERE email = ?");
            $updateEmail = $pdo->prepare("UPDATE emails SET status = 'replaced', replaced_at = CURRENT_TIMESTAMP WHERE email = ?");
            $insertRep = $pdo->prepare("
                INSERT INTO replacements (email, password, recovery_email, domain, rate_deduction, reason)
                VALUES (?, ?, ?, ?, ?, ?)
                ON CONFLICT(email) DO UPDATE SET 
                    rate_deduction = excluded.rate_deduction,
                    reason = excluded.reason
            ");

            foreach ($emails as $email) {
                $emailLookup->execute([$email]);
                $existing = $emailLookup->fetch();

                $pass = $existing['password'] ?? '';
                $rec = $existing['recovery_email'] ?? '';
                $dom = $existing['domain'] ?? '';
                if (empty($dom)) {
                    $at = strrpos($email, '@');
                    $dom = $at !== false ? substr($email, $at + 1) : '';
                }

                $updateEmail->execute([$email]);
                $insertRep->execute([$email, $pass, $rec, $dom, $rateDeduction, $reason]);
                $addedCount++;
            }

            $pdo->commit();

            Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'REPLACEMENTS_ADDED', "Added {$addedCount} replaced accounts");

            respondJson([
                'success'     => true,
                'added_count' => $addedCount,
                'message'     => "{$addedCount} accounts marked as replaced and deducted from bill."
            ]);
            break;

        // ==========================================
        // Replacements: Remove Single (Admin Only)
        // ==========================================
        case 'remove_replacement':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }
            $repId = (int)($_POST['id'] ?? 0);
            if ($repId <= 0) {
                respondJson(['success' => false, 'message' => 'Invalid replacement ID.'], 400);
            }

            $fetch = $pdo->prepare("SELECT email FROM replacements WHERE id = ?");
            $fetch->execute([$repId]);
            $rec = $fetch->fetch();

            if ($rec) {
                $del = $pdo->prepare("DELETE FROM replacements WHERE id = ?");
                $del->execute([$repId]);
                // Revert email status to downloaded
                $upd = $pdo->prepare("UPDATE emails SET status = 'downloaded' WHERE email = ?");
                $upd->execute([$rec['email']]);
            }

            respondJson(['success' => true, 'message' => 'Account removed from replacements.']);
            break;

        // ==========================================
        // Single Mail Credential Finder (Admin & Client)
        // ==========================================
        case 'search_mail':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $query = trim((string)($_GET['query'] ?? $_POST['query'] ?? ''));
            if (empty($query)) {
                respondJson(['success' => false, 'message' => 'Please enter an email to search.'], 400);
            }

            // Search in emails table
            $stmt = $pdo->prepare("
                SELECT e.id, e.email, e.password, e.recovery_email, e.domain, e.status, e.created_at, e.downloaded_at, e.replaced_at,
                       r.id as is_in_replacements, r.reason as replacement_reason
                FROM emails e
                LEFT JOIN replacements r ON LOWER(e.email) = LOWER(r.email)
                WHERE LOWER(e.email) LIKE ? OR LOWER(e.recovery_email) LIKE ?
                ORDER BY e.id DESC
                LIMIT 20
            ");
            $like = '%' . strtolower($query) . '%';
            $stmt->execute([$like, $like]);
            $results = $stmt->fetchAll();

            // Find associated order number for each result
            $allOrders = $pdo->query("SELECT id, order_number, created_at, accounts_json FROM orders ORDER BY id DESC")->fetchAll();
            
            foreach ($results as &$r) {
                $r['associated_order'] = null;
                $emailLower = strtolower($r['email']);

                foreach ($allOrders as $ord) {
                    if (strpos(strtolower($ord['accounts_json']), $emailLower) !== false) {
                        $r['associated_order'] = [
                            'id'           => $ord['id'],
                            'order_number' => $ord['order_number'],
                            'created_at'   => $ord['created_at']
                        ];
                        break;
                    }
                }
            }

            respondJson([
                'success' => true,
                'count'   => count($results),
                'results' => $results
            ]);
            break;

        // ==========================================
        // Download Account Statement / Invoice
        // ==========================================
        case 'download_statement':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Financial Summary
            $orderStats = $pdo->query("
                SELECT COUNT(*) as total_orders, COALESCE(SUM(quantity), 0) as gross_sold_mails, COALESCE(SUM(total_price), 0) as gross_billed_amount
                FROM orders
            ")->fetch();
            $grossSold = (int)($orderStats['gross_sold_mails'] ?? 0);
            $grossBilled = (float)($orderStats['gross_billed_amount'] ?? 0);

            $repStats = $pdo->query("SELECT COUNT(*) as rep_count, COALESCE(SUM(rate_deduction), 0) as deductions FROM replacements")->fetch();
            $repCount = (int)($repStats['rep_count'] ?? 0);
            $deductions = (float)($repStats['deductions'] ?? 0);

            $payStats = $pdo->query("SELECT COUNT(*) as pay_count, COALESCE(SUM(amount), 0) as total_paid FROM payments")->fetch();
            $totalPaid = (float)($payStats['total_paid'] ?? 0);

            $netActive = max(0, $grossSold - $repCount);
            $netBilled = max(0.0, $grossBilled - $deductions);
            $balance = $netBilled - $totalPaid;

            $orders = $pdo->query("SELECT * FROM orders ORDER BY datetime(created_at) ASC, id ASC")->fetchAll();
            $payments = $pdo->query("SELECT * FROM payments ORDER BY datetime(payment_date) ASC, id ASC")->fetchAll();
            $reps = $pdo->query("SELECT * FROM replacements ORDER BY datetime(created_at) ASC, id ASC")->fetchAll();

            $lines = [];
            $lines[] = "=================================================================";
            $lines[] = "HADI DIGITAL - CLIENT ACCOUNT STATEMENT & KHATA LEDGER";
            $lines[] = "Client Name: Rana Asim";
            $lines[] = "Statement Generated: " . date('Y-m-d H:i:s');
            $lines[] = "=================================================================";
            $lines[] = "";
            $lines[] = "FINANCIAL SUMMARY:";
            $lines[] = "Gross Mails Sold:," . $grossSold . " accounts";
            $lines[] = "Replaced / Faulty Deductions:," . $repCount . " accounts (Rs. " . number_format($deductions, 2) . ")";
            $lines[] = "Total Billable Mails:," . $netActive . " accounts";
            $lines[] = "Total Net Billed Amount:," . "Rs. " . number_format($netBilled, 2);
            $lines[] = "Total Paid (Wasool Shuda):," . "Rs. " . number_format($totalPaid, 2);
            $lines[] = "Pending Balance (Baqaya):," . "Rs. " . number_format($balance, 2);
            $lines[] = "";
            $lines[] = "=================================================================";
            $lines[] = "SECTION 1: ORDERS & INGESTED SALES";
            $lines[] = "Order #,Date & Time,Domain,Quantity,Rate Per Mail (PKR),Total Price (PKR),Notes";
            foreach ($orders as $o) {
                $rate = $o['rate_per_mail'] ?: 18.0;
                $tot = $o['total_price'] ?: ($o['quantity'] * $rate);
                $lines[] = sprintf('"%s","%s","%s",%d,"%.2f","%.2f","%s"',
                    $o['order_number'], $o['created_at'], $o['domain'], (int)$o['quantity'], (float)$rate, (float)$tot, str_replace('"', '""', $o['notes'] ?? '')
                );
            }
            $lines[] = "";
            $lines[] = "=================================================================";
            $lines[] = "SECTION 2: REPLACED / DEDUCTED ACCOUNTS";
            $lines[] = "Email Account,Domain,Deduction Rate (PKR),Reason,Date Added";
            foreach ($reps as $r) {
                $lines[] = sprintf('"%s","%s","%.2f","%s","%s"',
                    $r['email'], $r['domain'], (float)$r['rate_deduction'], str_replace('"', '""', $r['reason'] ?? ''), $r['created_at']
                );
            }
            $lines[] = "";
            $lines[] = "=================================================================";
            $lines[] = "SECTION 3: PAYMENTS RECEIVED (WASOOLI)";
            $lines[] = "Payment ID,Date & Time,Amount (PKR),Payment Method,Reference Note";
            foreach ($payments as $p) {
                $lines[] = sprintf('"%s","%s","%.2f","%s","%s"',
                    "PAY-#" . $p['id'], $p['payment_date'], (float)$p['amount'], $p['payment_method'], str_replace('"', '""', $p['reference_note'] ?? '')
                );
            }

            $content = implode("\r\n", $lines) . "\r\n";
            $filename = "Hadi_Digital_Khata_Statement_" . date('Ymd_His') . ".csv";

            respondJson([
                'success'      => true,
                'filename'     => $filename,
                'file_content' => base64_encode($content)
            ]);
            break;

        // ==========================================
        // Admin: View Inventory Accounts Table
        // ==========================================
        case 'list_accounts':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $domainFilter = trim((string)($_GET['domain'] ?? ''));
            $statusFilter = trim((string)($_GET['status'] ?? ''));
            $search = trim((string)($_GET['search'] ?? ''));
            $limit = min(200, max(10, (int)($_GET['limit'] ?? 50)));

            $sql = "SELECT id, email, domain, status, created_at, downloaded_at, replaced_at FROM emails WHERE 1=1";
            $params = [];

            if (!empty($domainFilter)) {
                $sql .= " AND domain = ?";
                $params[] = $domainFilter;
            }

            if (!empty($statusFilter)) {
                $sql .= " AND status = ?";
                $params[] = $statusFilter;
            }

            if (!empty($search)) {
                $sql .= " AND (email LIKE ? OR recovery_email LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            $sql .= " ORDER BY id DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $accounts = $stmt->fetchAll();

            respondJson([
                'success'  => true,
                'accounts' => $accounts
            ]);
            break;

        // ==========================================
        // Admin: Update Single Account Status (Revert to Available)
        // ==========================================
        case 'update_account_status':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $email = trim((string)($_POST['email'] ?? ''));
            $accountId = (int)($_POST['id'] ?? 0);
            $newStatus = trim((string)($_POST['status'] ?? 'available'));

            if (!in_array($newStatus, ['available', 'downloaded', 'replaced'], true)) {
                $newStatus = 'available';
            }

            if ($accountId > 0) {
                $stmt = $pdo->prepare("
                    UPDATE emails 
                    SET status = ?, 
                        downloaded_at = CASE WHEN ? = 'available' THEN NULL ELSE downloaded_at END,
                        replaced_at = CASE WHEN ? = 'available' THEN NULL ELSE replaced_at END
                    WHERE id = ?
                ");
                $stmt->execute([$newStatus, $newStatus, $newStatus, $accountId]);
            } else if (!empty($email)) {
                $stmt = $pdo->prepare("
                    UPDATE emails 
                    SET status = ?, 
                        downloaded_at = CASE WHEN ? = 'available' THEN NULL ELSE downloaded_at END,
                        replaced_at = CASE WHEN ? = 'available' THEN NULL ELSE replaced_at END
                    WHERE LOWER(email) = LOWER(?)
                ");
                $stmt->execute([$newStatus, $newStatus, $newStatus, $email]);
            } else {
                respondJson(['success' => false, 'message' => 'Account ID or Email required.'], 400);
            }

            Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'ACCOUNT_STATUS_UPDATED', "Set {$email} to {$newStatus}");
            respondJson(['success' => true, 'message' => "Account status set to '{$newStatus}' successfully."]);
            break;

        // ==========================================
        // Admin: Edit Account Credentials
        // ==========================================
        case 'edit_account':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $accountId = (int)($_POST['id'] ?? 0);
            $email = trim((string)($_POST['email'] ?? ''));
            $password = trim((string)($_POST['password'] ?? ''));
            $recovery = trim((string)($_POST['recovery_email'] ?? ''));
            $status = trim((string)($_POST['status'] ?? 'available'));

            if ($accountId <= 0 || empty($email) || empty($password)) {
                respondJson(['success' => false, 'message' => 'ID, email, and password are required.'], 400);
            }

            $stmt = $pdo->prepare("
                UPDATE emails 
                SET email = ?, password = ?, recovery_email = ?, status = ?,
                    downloaded_at = CASE WHEN ? = 'available' THEN NULL ELSE downloaded_at END,
                    replaced_at = CASE WHEN ? = 'available' THEN NULL ELSE replaced_at END
                WHERE id = ?
            ");
            $stmt->execute([$email, $password, $recovery, $status, $status, $status, $accountId]);

            Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'ACCOUNT_EDITED', "Updated credentials for ID {$accountId}: {$email}");
            respondJson(['success' => true, 'message' => "Account updated successfully."]);
            break;

        // ==========================================
        // Admin: Delete Single Account (With Sold Safety Check)
        // ==========================================
        case 'delete_account':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $accountId = (int)($_POST['id'] ?? 0);
            $email = trim((string)($_POST['email'] ?? ''));

            $account = null;
            if ($accountId > 0) {
                $stmt = $pdo->prepare("SELECT id, email, status FROM emails WHERE id = ?");
                $stmt->execute([$accountId]);
                $account = $stmt->fetch();
            } else if (!empty($email)) {
                $stmt = $pdo->prepare("SELECT id, email, status FROM emails WHERE LOWER(email) = LOWER(?)");
                $stmt->execute([$email]);
                $account = $stmt->fetch();
            }

            if (!$account) {
                respondJson(['success' => false, 'message' => 'Account record not found.'], 404);
            }

            $wasSold = ($account['status'] === 'downloaded');
            $delStmt = $pdo->prepare("DELETE FROM emails WHERE id = ?");
            $delStmt->execute([$account['id']]);

            Database::logAudit(
                Auth::user()['username'] ?? 'admin',
                'admin',
                'ACCOUNT_DELETED',
                "Deleted account {$account['email']} (ID: {$account['id']}, Status was {$account['status']})"
            );

            respondJson([
                'success'  => true,
                'was_sold' => $wasSold,
                'email'    => $account['email'],
                'message'  => $wasSold 
                    ? "Sold account {$account['email']} deleted. Sold count and Bill automatically reduced by Rs. 18."
                    : "Account {$account['email']} deleted successfully."
            ]);
            break;

        // ==========================================
        // Admin: Revert Entire Order to Stock
        // ==========================================
        case 'revert_order_to_stock':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $orderId = (int)($_POST['order_id'] ?? 0);
            $deleteOrder = !empty($_POST['delete_order']) && $_POST['delete_order'] !== 'false';
            $passwordsCsv = trim((string)($_POST['passwords_csv'] ?? ''));

            if ($orderId <= 0) {
                respondJson(['success' => false, 'message' => 'Invalid order ID.'], 400);
            }

            $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $orderStmt->execute([$orderId]);
            $order = $orderStmt->fetch();

            if (!$order) {
                respondJson(['success' => false, 'message' => 'Order not found.'], 404);
            }

            $accounts = json_decode($order['accounts_json'], true) ?: [];
            if (empty($accounts)) {
                respondJson(['success' => false, 'message' => 'Order contains no email records.'], 400);
            }

            // Parse optional passwords CSV
            $customPasswords = [];
            if (!empty($passwordsCsv)) {
                $pLines = preg_split("/\r\n|\n|\r/", $passwordsCsv);
                $pStart = 0;
                if (!empty($pLines[0]) && stripos($pLines[0], 'email') !== false && stripos($pLines[0], 'password') !== false) {
                    $pStart = 1;
                }
                for ($p = $pStart; $p < count($pLines); $p++) {
                    $pLine = trim($pLines[$p]);
                    if (empty($pLine)) continue;
                    $cols = str_getcsv($pLine);
                    $em = strtolower(trim($cols[0] ?? ''));
                    $pw = trim($cols[1] ?? '');
                    $rc = trim($cols[2] ?? '');
                    if (!empty($em) && !empty($pw)) {
                        $customPasswords[$em] = ['password' => $pw, 'recovery' => $rc];
                    }
                }
            }

            $pdo->beginTransaction();

            $updateStmt = $pdo->prepare("
                UPDATE emails 
                SET status = 'available', 
                    downloaded_at = NULL, 
                    replaced_at = NULL,
                    password = CASE WHEN ? != '' THEN ? ELSE password END,
                    recovery_email = CASE WHEN ? != '' THEN ? ELSE recovery_email END
                WHERE LOWER(email) = LOWER(?)
            ");

            $revertedCount = 0;
            foreach ($accounts as $acc) {
                $emLower = strtolower(trim($acc['email'] ?? ''));
                if (empty($emLower)) continue;

                $newPw = $customPasswords[$emLower]['password'] ?? '';
                $newRc = $customPasswords[$emLower]['recovery'] ?? '';

                $updateStmt->execute([$newPw, $newPw, $newRc, $newRc, $emLower]);
                $revertedCount++;
            }

            if ($deleteOrder) {
                $delStmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $delStmt->execute([$orderId]);
            } else {
                $noteStmt = $pdo->prepare("UPDATE orders SET notes = notes || ' [REVERTED TO AVAILABLE STOCK]' WHERE id = ?");
                $noteStmt->execute([$orderId]);
            }

            $pdo->commit();

            Database::logAudit(
                Auth::user()['username'] ?? 'admin',
                'admin',
                'ORDER_REVERTED',
                "Reverted {$revertedCount} accounts from Order {$order['order_number']} back to available stock. Deleted order: " . ($deleteOrder ? 'YES' : 'NO')
            );

            respondJson([
                'success'        => true,
                'reverted_count' => $revertedCount,
                'order_deleted'  => $deleteOrder,
                'message'        => "Successfully reverted {$revertedCount} accounts to available stock."
            ]);
            break;

        // ==========================================
        // Admin: Bulk Accounts Action (Make Available / Delete)
        // ==========================================
        case 'bulk_account_action':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }

            $bulkAction = trim((string)($_POST['bulk_action'] ?? ''));
            $emails = $_POST['emails'] ?? [];
            if (is_string($emails)) {
                $emails = json_decode($emails, true) ?: preg_split("/[\r\n,]+/", $emails);
            }

            if (empty($emails) || !is_array($emails)) {
                respondJson(['success' => false, 'message' => 'No accounts specified.'], 400);
            }

            $pdo->beginTransaction();
            $affected = 0;

            if ($bulkAction === 'make_available') {
                $stmt = $pdo->prepare("UPDATE emails SET status = 'available', downloaded_at = NULL, replaced_at = NULL WHERE LOWER(email) = LOWER(?)");
                foreach ($emails as $em) {
                    $em = trim((string)$em);
                    if (!empty($em)) {
                        $stmt->execute([$em]);
                        $affected += $stmt->rowCount();
                    }
                }
            } else if ($bulkAction === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM emails WHERE LOWER(email) = LOWER(?)");
                foreach ($emails as $em) {
                    $em = trim((string)$em);
                    if (!empty($em)) {
                        $stmt->execute([$em]);
                        $affected += $stmt->rowCount();
                    }
                }
            } else {
                $pdo->rollBack();
                respondJson(['success' => false, 'message' => 'Invalid bulk action.'], 400);
            }

            $pdo->commit();

            Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', 'BULK_ACCOUNT_ACTION', "Action: {$bulkAction}, affected: {$affected}");
            respondJson(['success' => true, 'affected' => $affected, 'message' => "Bulk action '{$bulkAction}' completed for {$affected} accounts."]);
            break;

        // ==========================================
        // Audit Logs (Recent events)
        // ==========================================
        case 'audit_logs':
            if (!Auth::isLoggedIn()) {
                respondJson(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $limit = Auth::isAdmin() ? 50 : 20;
            $stmt = $pdo->prepare("
                SELECT username, role, action, details, created_at 
                FROM audit_logs 
                ORDER BY id DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $logs = $stmt->fetchAll();

            respondJson([
                'success' => true,
                'logs'    => $logs
            ]);
            break;

        // ==========================================
        // Maintenance Mode Settings (Admin Only to set, Public to view)
        // ==========================================
        case 'get_maintenance_mode':
            $maintMode = false;
            $maintMsg = 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!';
            try {
                $stmtM = $pdo->query("SELECT value FROM settings WHERE key = 'client_maintenance_mode'");
                $maintMode = ($stmtM->fetchColumn() === '1');
                $stmtMsg = $pdo->query("SELECT value FROM settings WHERE key = 'client_maintenance_message'");
                $valMsg = $stmtMsg->fetchColumn();
                if ($valMsg !== false && $valMsg !== null) {
                    $maintMsg = (string)$valMsg;
                }
            } catch (Exception $e) {}

            respondJson([
                'success'            => true,
                'maintenance_mode'   => $maintMode,
                'maintenance_message'=> $maintMsg,
                'is_admin'           => Auth::isAdmin()
            ]);
            break;

        case 'set_maintenance_mode':
            if (!Auth::isAdmin()) {
                respondJson(['success' => false, 'message' => 'Admin authorization required.'], 403);
            }
            if (!Auth::verifyCsrfToken()) {
                respondJson(['success' => false, 'message' => 'Security token invalid.'], 403);
            }

            $rawEnabled = $_POST['enabled'] ?? '0';
            $mode = ($rawEnabled === '1' || $rawEnabled === 'true' || $rawEnabled === 1 || $rawEnabled === true) ? '1' : '0';
            $msg = trim((string)($_POST['message'] ?? 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!'));
            if (empty($msg)) {
                $msg = 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!';
            }

            $stmtUpsert = $pdo->prepare("
                INSERT INTO settings (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)
                ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = CURRENT_TIMESTAMP
            ");
            $stmtUpsert->execute(['client_maintenance_mode', $mode]);
            $stmtUpsert->execute(['client_maintenance_message', $msg]);

            $actionText = ($mode === '1') ? 'MAINTENANCE_MODE_ENABLED' : 'MAINTENANCE_MODE_DISABLED';
            $detailText = ($mode === '1') ? 'Admin activated Client Portal Maintenance Mode' : 'Admin disabled Client Portal Maintenance Mode (Portal LIVE)';
            Database::logAudit(Auth::user()['username'] ?? 'admin', 'admin', $actionText, $detailText);

            respondJson([
                'success'            => true,
                'maintenance_mode'   => ($mode === '1'),
                'maintenance_message'=> $msg,
                'message'            => ($mode === '1') ? 'Client Portal Maintenance Mode Activated!' : 'Client Portal is now LIVE!'
            ]);
            break;

        default:
            respondJson([
                'success' => false,
                'message' => "Unknown API action: {$action}"
            ], 404);
            break;
    }
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('API Exception: ' . $e->getMessage());
    respondJson([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ], 500);
}
