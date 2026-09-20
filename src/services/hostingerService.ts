import {
  EmailAccount,
  OrderRecord,
  PaymentRecord,
  ReplacementRecord,
  AuditLog,
  MaintenanceSettings
} from '../types';

export interface HostingerPingResponse {
  success: boolean;
  status: string;
  system: string;
  target_directory: string;
  live_url: string;
  deploy_version: string;
  server_time: string;
  php_version: string;
  sqlite_connected: boolean;
  total_vault_emails: number;
  available_stock: number;
  domain_stock: Record<string, number>;
  sync_message: string;
}

export interface FullSyncResponse {
  success: boolean;
  server_time: string;
  accounts: EmailAccount[];
  orders: OrderRecord[];
  payments: PaymentRecord[];
  replacements: ReplacementRecord[];
  logs: AuditLog[];
  maintenance: MaintenanceSettings;
  error?: string;
}

export const HOSTINGER_BASE_URL = 'https://asim.eztoolbox.xyz';
export const HOSTINGER_API_URL = `${HOSTINGER_BASE_URL}/api.php`;
export const GITHUB_ACTIONS_URL = 'https://github.com/hadibadshah/email_valut/actions';
export const MASTER_SYNC_KEY = 'HADI_DIGITAL_MASTER_SYNC_KEY_2026';

/**
 * Send authenticated request to Hostinger Unified API
 */
async function callHostingerApi(action: string, options: {
  method?: 'GET' | 'POST';
  body?: any;
  timeoutMs?: number;
} = {}): Promise<any> {
  const method = options.method || 'GET';
  const timeoutMs = options.timeoutMs || 10000;
  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

  const url = `${HOSTINGER_API_URL}?action=${encodeURIComponent(action)}&master_key=${encodeURIComponent(MASTER_SYNC_KEY)}&_t=${Date.now()}`;

  const headers: Record<string, string> = {
    'Accept': 'application/json',
    'X-Master-Key': MASTER_SYNC_KEY,
  };

  let body: any = undefined;
  if (method === 'POST') {
    if (options.body instanceof FormData) {
      body = options.body;
      body.append('master_key', MASTER_SYNC_KEY);
    } else if (typeof options.body === 'object') {
      headers['Content-Type'] = 'application/json';
      body = JSON.stringify({
        ...options.body,
        action,
        master_key: MASTER_SYNC_KEY,
      });
    }
  }

  try {
    const res = await fetch(url, {
      method,
      headers,
      body,
      signal: controller.signal,
    });
    clearTimeout(timeoutId);

    if (!res.ok) {
      const errText = await res.text().catch(() => '');
      throw new Error(`Hostinger API returned HTTP ${res.status}: ${errText.substring(0, 150)}`);
    }

    return await res.json();
  } catch (err: any) {
    clearTimeout(timeoutId);
    if (err?.name === 'AbortError') {
      throw new Error('Hostinger request timed out');
    }
    throw err;
  }
}

/**
 * Health check & status probe
 */
export async function pingHostinger(): Promise<{
  online: boolean;
  data: HostingerPingResponse | null;
  error?: string;
  latencyMs?: number;
}> {
  const startTime = Date.now();
  try {
    const data = await callHostingerApi('ping', { method: 'GET', timeoutMs: 6000 });
    const latencyMs = Date.now() - startTime;
    return {
      online: !!data?.success,
      data,
      latencyMs,
    };
  } catch (err: any) {
    const latencyMs = Date.now() - startTime;
    return {
      online: false,
      data: null,
      error: err?.message || 'Connection failed',
      latencyMs,
    };
  }
}

/**
 * Complete 2-Way Data Sync: fetches live accounts, orders, payments, replacements, maintenance
 */
export async function fetchFullSync(): Promise<FullSyncResponse> {
  try {
    const resp = await callHostingerApi('full_sync', { method: 'GET', timeoutMs: 12000 });
    if (!resp.success) {
      throw new Error(resp.message || 'Failed to sync data from Hostinger');
    }

    return {
      success: true,
      server_time: resp.server_time || new Date().toISOString(),
      accounts: Array.isArray(resp.accounts) ? resp.accounts : [],
      orders: Array.isArray(resp.orders) ? resp.orders : [],
      payments: Array.isArray(resp.payments) ? resp.payments : [],
      replacements: Array.isArray(resp.replacements) ? resp.replacements : [],
      logs: Array.isArray(resp.logs) ? resp.logs : [],
      maintenance: resp.maintenance || { enabled: false, message: '' },
    };
  } catch (err: any) {
    return {
      success: false,
      server_time: new Date().toISOString(),
      accounts: [],
      orders: [],
      payments: [],
      replacements: [],
      logs: [],
      maintenance: { enabled: false, message: '' },
      error: err?.message || 'Hostinger sync failed',
    };
  }
}

/**
 * Upload CSV directly into Hostinger SQLite Database
 */
export async function uploadCsvToLive(
  csvText: string,
  targetStatus: 'make_available' | 'keep_existing' | 'make_downloaded' = 'make_available'
): Promise<{
  success: boolean;
  inserted?: number;
  updated?: number;
  duplicates?: number;
  message: string;
}> {
  return await callHostingerApi('upload_csv', {
    method: 'POST',
    body: {
      csv_text: csvText,
      target_status: targetStatus,
    },
    timeoutMs: 30000,
  });
}

/**
 * Save / Create / Update an Order on Hostinger Live Database
 */
export async function saveOrderToLive(orderData: {
  order_id?: number;
  order_number: string;
  created_at?: string;
  rate_per_mail?: number;
  total_price?: number;
  notes?: string;
  domain?: string;
  csv_text?: string;
}): Promise<{ success: boolean; message: string; order_id?: number }> {
  return await callHostingerApi('save_order', {
    method: 'POST',
    body: orderData,
  });
}

/**
 * Delete an Order on Hostinger Live Database
 */
export async function deleteOrderOnLive(orderId: number): Promise<{ success: boolean; message: string }> {
  return await callHostingerApi('delete_order', {
    method: 'POST',
    body: { order_id: orderId },
  });
}

/**
 * Save / Record a Payment on Hostinger Live Database
 */
export async function savePaymentToLive(paymentData: {
  id?: number;
  amount: number;
  payment_date?: string;
  payment_method?: string;
  reference_note?: string;
}): Promise<{ success: boolean; message: string }> {
  return await callHostingerApi('save_payment', {
    method: 'POST',
    body: paymentData,
  });
}

/**
 * Delete a Payment on Hostinger Live Database
 */
export async function deletePaymentOnLive(paymentId: number): Promise<{ success: boolean; message: string }> {
  return await callHostingerApi('delete_payment', {
    method: 'POST',
    body: { id: paymentId },
  });
}

/**
 * Add Faulty Replacements to Hostinger Live Database
 */
export async function addReplacementsToLive(
  emails: string[],
  rateDeduction: number = 18,
  reason: string = 'Faulty Replacement'
): Promise<{ success: boolean; message: string; count?: number }> {
  return await callHostingerApi('add_replacements', {
    method: 'POST',
    body: {
      emails: emails.join('\n'),
      rate_deduction: rateDeduction,
      reason,
    },
  });
}

/**
 * Delete a Replacement on Hostinger Live Database
 */
export async function deleteReplacementOnLive(repId: number): Promise<{ success: boolean; message: string }> {
  return await callHostingerApi('remove_replacement', {
    method: 'POST',
    body: { id: repId },
  });
}

/**
 * Revert Order Mails to Stock on Hostinger Live Database
 */
export async function revertOrderToStockOnLive(
  orderId: number,
  deleteOrder: boolean,
  newPasswordsCsv?: string
): Promise<{ success: boolean; message: string }> {
  return await callHostingerApi('revert_order_to_stock', {
    method: 'POST',
    body: {
      order_id: orderId,
      delete_order: deleteOrder ? '1' : '0',
      passwords_csv: newPasswordsCsv || '',
    },
  });
}

/**
 * Perform Bulk Action on Accounts (delete or make_available) on Hostinger
 */
export async function bulkAccountActionOnLive(
  emails: string[],
  bulkAction: 'make_available' | 'delete'
): Promise<{ success: boolean; affected: number; message: string }> {
  return await callHostingerApi('bulk_account_action', {
    method: 'POST',
    body: {
      bulk_action: bulkAction,
      emails,
    },
  });
}

/**
 * Update Maintenance Mode on Hostinger Live Database
 */
export async function updateMaintenanceOnLive(
  enabled: boolean,
  message: string
): Promise<{ success: boolean; message: string }> {
  return await callHostingerApi('set_maintenance_mode', {
    method: 'POST',
    body: {
      enabled: enabled ? '1' : '0',
      message,
    },
  });
}
