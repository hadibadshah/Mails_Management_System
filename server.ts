import express from 'express';
import path from 'path';
import https from 'https';
import { createServer as createViteServer } from 'vite';

// Hostinger Credentials & Constants
const HOSTINGER_DOMAIN = 'asim.eztoolbox.xyz';
const ADMIN_USER = 'Hadi';
const ADMIN_PASS = '91199119';
const MASTER_SYNC_KEY = 'HADI_DIGITAL_MASTER_SYNC_KEY_2026';

class HostingerBridge {
  private cookie: string = '';
  private csrfToken: string = '';
  private lastLogin: number = 0;

  private request(path: string, options: { method?: string; headers?: Record<string, string> } = {}, postData: string | null = null): Promise<{ statusCode: number; headers: Record<string, any>; data: string }> {
    return new Promise((resolve, reject) => {
      const req = https.request({
        hostname: HOSTINGER_DOMAIN,
        port: 443,
        path,
        method: options.method || 'GET',
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) HadiDigitalSync/2.0',
          'X-Master-Key': MASTER_SYNC_KEY,
          ...(options.headers || {})
        }
      }, (res) => {
        let data = '';
        res.on('data', chunk => data += chunk);
        res.on('end', () => resolve({ statusCode: res.statusCode || 200, headers: res.headers, data }));
      });
      req.on('error', reject);
      if (postData) req.write(postData);
      req.end();
    });
  }

  async ensureAuthenticated(): Promise<string> {
    // Reuse session for 20 minutes if valid
    if (this.cookie && (Date.now() - this.lastLogin < 1000 * 60 * 20)) {
      return this.cookie;
    }

    try {
      const statusRes = await this.request('/api.php?action=status');
      let cookie = statusRes.headers['set-cookie'] ? statusRes.headers['set-cookie'][0].split(';')[0] : '';
      let csrf = '';
      try {
        const sJson = JSON.parse(statusRes.data);
        csrf = sJson.csrf_token || '';
      } catch {
        // ignore
      }
      this.csrfToken = csrf;

      const body = 'username=' + encodeURIComponent(ADMIN_USER) + 
                   '&password=' + encodeURIComponent(ADMIN_PASS) + 
                   '&csrf_token=' + encodeURIComponent(csrf);

      const loginRes = await this.request('/api.php?action=login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Cookie': cookie
        }
      }, body);

      if (loginRes.headers['set-cookie']) {
        cookie = loginRes.headers['set-cookie'][0].split(';')[0];
      }

      this.cookie = cookie;
      this.lastLogin = Date.now();
      return this.cookie;
    } catch (err) {
      console.warn('Authentication to Hostinger failed:', err);
      return this.cookie;
    }
  }

  async getLiveSync(): Promise<any> {
    const startTime = Date.now();
    const cookie = await this.ensureAuthenticated();

    try {
      // 1. First try modern full_sync endpoint
      const fullSyncRes = await this.request(`/api.php?action=full_sync&master_key=${encodeURIComponent(MASTER_SYNC_KEY)}`, {
        headers: { 'Cookie': cookie }
      });

      try {
        const fullJson = JSON.parse(fullSyncRes.data);
        if (fullJson && fullJson.success) {
          return {
            ...fullJson,
            online: true,
            latencyMs: Date.now() - startTime
          };
        }
      } catch {
        // Fall back to legacy multi-endpoint aggregation
      }

      // 2. Legacy fallback: Stock + Orders
      const [stockRes, ordersRes, statusRes] = await Promise.all([
        this.request('/api.php?action=stock', { headers: { 'Cookie': cookie } }).catch(() => null),
        this.request('/api.php?action=orders', { headers: { 'Cookie': cookie } }).catch(() => null),
        this.request('/api.php?action=status', { headers: { 'Cookie': cookie } }).catch(() => null)
      ]);

      let stockData: any = null;
      let ordersData: any[] = [];
      let maintenanceData = { enabled: false, message: '' };

      if (stockRes && stockRes.data) {
        try {
          const s = JSON.parse(stockRes.data);
          stockData = s.overall || null;
        } catch {
          // ignore
        }
      }

      if (ordersRes && ordersRes.data) {
        try {
          const o = JSON.parse(ordersRes.data);
          ordersData = o.orders || [];
        } catch {
          // ignore
        }
      }

      if (statusRes && statusRes.data) {
        try {
          const st = JSON.parse(statusRes.data);
          maintenanceData = {
            enabled: !!st.maintenance_mode,
            message: st.maintenance_message || ''
          };
        } catch {
          // ignore
        }
      }

      // Flatten accounts from orders
      const extractedAccounts: any[] = [];
      for (const order of ordersData) {
        if (Array.isArray(order.accounts)) {
          for (const acc of order.accounts) {
            extractedAccounts.push({
              id: acc.id || extractedAccounts.length + 1,
              email: acc.email,
              password: acc.password || 'VaultP@ss101',
              recovery_email: acc.recovery_email || '',
              domain: order.domain || 'basis5.ch',
              status: 'downloaded',
              created_at: order.created_at,
              downloaded_at: order.created_at
            });
          }
        }
      }

      return {
        success: true,
        online: true,
        latencyMs: Date.now() - startTime,
        server_time: new Date().toISOString(),
        stock: stockData,
        orders: ordersData,
        accounts: extractedAccounts,
        maintenance: maintenanceData
      };
    } catch (err: any) {
      return {
        success: false,
        online: false,
        latencyMs: Date.now() - startTime,
        error: err?.message || 'Sync failed'
      };
    }
  }

  async uploadCsv(csvData: string, domain: string): Promise<any> {
    const cookie = await this.ensureAuthenticated();
    const boundary = '----WebKitFormBoundary' + Math.random().toString(36).substring(2);
    
    let body = '';
    body += `--${boundary}\r\n`;
    body += `Content-Disposition: form-data; name="csrf_token"\r\n\r\n${this.csrfToken}\r\n`;
    body += `--${boundary}\r\n`;
    body += `Content-Disposition: form-data; name="domain"\r\n\r\n${domain}\r\n`;
    body += `--${boundary}\r\n`;
    body += `Content-Disposition: form-data; name="csv_file"; filename="upload.csv"\r\n`;
    body += `Content-Type: text/csv\r\n\r\n`;
    body += `${csvData}\r\n`;
    body += `--${boundary}--\r\n`;

    const res = await this.request('/api.php?action=upload_csv', {
      method: 'POST',
      headers: {
        'Content-Type': `multipart/form-data; boundary=${boundary}`,
        'Cookie': cookie
      }
    }, body);

    try {
      return JSON.parse(res.data);
    } catch {
      return { success: res.statusCode === 200, raw: res.data };
    }
  }

  async setMaintenance(enabled: boolean, message: string): Promise<any> {
    const cookie = await this.ensureAuthenticated();
    const body = 'csrf_token=' + encodeURIComponent(this.csrfToken) +
                 '&enabled=' + (enabled ? '1' : '0') +
                 '&message=' + encodeURIComponent(message);

    const res = await this.request('/api.php?action=toggle_maintenance', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Cookie': cookie
      }
    }, body);

    try {
      return JSON.parse(res.data);
    } catch {
      return { success: res.statusCode === 200 };
    }
  }

  async callAction(action: string, data: any = {}): Promise<any> {
    const cookie = await this.ensureAuthenticated();
    const params = new URLSearchParams();
    params.append('csrf_token', this.csrfToken);
    if (data && typeof data === 'object') {
      for (const [k, v] of Object.entries(data)) {
        if (v !== undefined && v !== null && k !== 'csrf_token') {
          if (Array.isArray(v)) {
            params.append(k, JSON.stringify(v));
          } else {
            params.append(k, String(v));
          }
        }
      }
    }
    const body = params.toString();
    const res = await this.request(`/api.php?action=${encodeURIComponent(action)}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Cookie': cookie
      }
    }, body);

    try {
      return JSON.parse(res.data);
    } catch {
      return { success: res.statusCode === 200, raw: res.data };
    }
  }
}

const bridge = new HostingerBridge();

async function startServer() {
  const app = express();
  const PORT = 3000;

  app.use(express.json({ limit: '50mb' }));
  app.use(express.urlencoded({ extended: true, limit: '50mb' }));

  // ==========================================
  // API Routes (Server-Side Proxy with Hostinger)
  // ==========================================
  app.get('/api/health', (req, res) => {
    res.json({ status: 'ok', timestamp: new Date().toISOString() });
  });

  // Execute Authenticated Action on Hostinger Live Database (save_order, delete_order, save_payment, etc.)
  app.post('/api/hostinger/action', async (req, res) => {
    try {
      const { action, payload } = req.body;
      if (!action) {
        res.status(400).json({ success: false, message: 'Action is required' });
        return;
      }
      const result = await bridge.callAction(action, payload || {});
      res.json(result);
    } catch (err: any) {
      res.status(500).json({ success: false, message: err?.message || 'Action execution failed' });
    }
  });

  // Live Sync Endpoint: Returns live orders, accounts, stock from Hostinger
  app.get('/api/hostinger/sync', async (req, res) => {
    try {
      const data = await bridge.getLiveSync();
      res.json(data);
    } catch (err: any) {
      res.status(500).json({ success: false, error: err?.message || 'Sync failed' });
    }
  });

  // Upload CSV to Hostinger live SQLite database
  app.post('/api/hostinger/upload-csv', async (req, res) => {
    try {
      const { csvData, domain } = req.body;
      if (!csvData) {
        res.status(400).json({ success: false, message: 'CSV data is required' });
        return;
      }
      const result = await bridge.uploadCsv(csvData, domain || 'basis5.ch');
      res.json(result);
    } catch (err: any) {
      res.status(500).json({ success: false, error: err?.message || 'Upload failed' });
    }
  });

  // Toggle Maintenance on Hostinger
  app.post('/api/hostinger/maintenance', async (req, res) => {
    try {
      const { enabled, message } = req.body;
      const result = await bridge.setMaintenance(!!enabled, message || '');
      res.json(result);
    } catch (err: any) {
      res.status(500).json({ success: false, error: err?.message || 'Maintenance update failed' });
    }
  });

  // Ping Hostinger status
  app.get('/api/hostinger/ping', async (req, res) => {
    try {
      const data = await bridge.getLiveSync();
      res.json({
        online: data.online,
        latencyMs: data.latencyMs,
        stock: data.stock,
        totalOrders: data.orders ? data.orders.length : 0
      });
    } catch (err: any) {
      res.json({ online: false, error: err?.message });
    }
  });

  // ==========================================
  // Vite Integration (SPA Middleware / Static)
  // ==========================================
  if (process.env.NODE_ENV !== 'production') {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa',
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), 'dist');
    app.use(express.static(distPath));
    app.get('*', (req, res) => {
      res.sendFile(path.join(distPath, 'index.html'));
    });
  }

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Hadi Digital Full-Stack Server running on port ${PORT}`);
  });
}

startServer().catch((err) => {
  console.error('Fatal server startup error:', err);
});
