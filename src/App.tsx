import { useState, useEffect, useCallback } from 'react';
import Header from './components/Header';
import ParticlesBackground from './components/ParticlesBackground';
import LoginCard from './components/LoginCard';
import ClientDashboard from './components/ClientDashboard';
import AdminDashboard from './components/AdminDashboard';
import ClientMaintenanceScreen from './components/ClientMaintenanceScreen';
import LiveHostingerModal from './components/LiveHostingerModal';
import {
  pingHostinger,
  fetchFullSync,
  updateMaintenanceOnLive,
  HostingerPingResponse
} from './services/hostingerService';
import {
  EmailAccount,
  UserSession,
  AuditLog,
  OrderRecord,
  PaymentRecord,
  ReplacementRecord,
  MaintenanceSettings
} from './types';
import { INITIAL_EMAIL_ACCOUNTS, INITIAL_ORDERS } from './data/constants';
import { cyberAlertSuccess } from './utils/cyberSwal';

const STORAGE_ACCOUNTS_KEY = 'hadi_digital_accounts_v4';
const STORAGE_SESSION_KEY = 'hadi_digital_session_v4';
const STORAGE_ORDERS_KEY = 'hadi_digital_orders_v4';
const STORAGE_PAYMENTS_KEY = 'hadi_digital_payments_v4';
const STORAGE_REPLACEMENTS_KEY = 'hadi_digital_replacements_v4';
const STORAGE_LOGS_KEY = 'hadi_digital_audit_logs_v4';
const STORAGE_MAINTENANCE_KEY = 'hadi_digital_maintenance_v4';

// Clear old legacy and empty cache keys on initial run
try {
  localStorage.removeItem('hadi_digital_accounts_v3');
  localStorage.removeItem('hadi_digital_orders_v3');
  localStorage.removeItem('hadi_digital_accounts_v2');
  localStorage.removeItem('hadi_digital_orders_v2');
  localStorage.removeItem('hadi_digital_accounts_v1');
  localStorage.removeItem('hadi_digital_orders_v1');
  localStorage.removeItem('cybervault_accounts_v1');
  localStorage.removeItem('cybervault_orders_v1');
} catch {
  // ignore
}

export default function App() {
  const [session, setSession] = useState<UserSession | null>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_SESSION_KEY);
      return saved ? JSON.parse(saved) : null;
    } catch {
      return null;
    }
  });

  const [accounts, setAccounts] = useState<EmailAccount[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_ACCOUNTS_KEY);
      if (saved) {
        const parsed = JSON.parse(saved);
        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
      }
      return INITIAL_EMAIL_ACCOUNTS;
    } catch {
      return INITIAL_EMAIL_ACCOUNTS;
    }
  });

  const [orders, setOrders] = useState<OrderRecord[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_ORDERS_KEY);
      if (saved) {
        const parsed = JSON.parse(saved);
        if (Array.isArray(parsed) && parsed.length > 0) return parsed;
      }
      return INITIAL_ORDERS;
    } catch {
      return INITIAL_ORDERS;
    }
  });

  const [payments, setPayments] = useState<PaymentRecord[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_PAYMENTS_KEY);
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  const [replacements, setReplacements] = useState<ReplacementRecord[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_REPLACEMENTS_KEY);
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  const [logs, setLogs] = useState<AuditLog[]>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_LOGS_KEY);
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  const [maintenance, setMaintenance] = useState<MaintenanceSettings>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_MAINTENANCE_KEY);
      return saved
        ? JSON.parse(saved)
        : {
            enabled: false,
            message: 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!'
          };
    } catch {
      return {
        enabled: false,
        message: 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!'
      };
    }
  });

  const [adminClientPreview, setAdminClientPreview] = useState(false);

  // Hostinger & GitHub Live Sync Monitoring
  const [isLiveModalOpen, setIsLiveModalOpen] = useState(false);
  const [liveHostingerData, setLiveHostingerData] = useState<HostingerPingResponse | null>(null);
  const [isHostingerOnline, setIsHostingerOnline] = useState(false);
  const [hostingerLatency, setHostingerLatency] = useState<number | undefined>(undefined);
  const [lastHostingerCheck, setLastHostingerCheck] = useState<Date | null>(null);
  const [isCheckingHostinger, setIsCheckingHostinger] = useState(false);

  const checkHostingerConnection = useCallback(async () => {
    setIsCheckingHostinger(true);
    const res = await pingHostinger();
    setIsHostingerOnline(res.online);
    setLiveHostingerData(res.data);
    setHostingerLatency(res.latencyMs);
    setLastHostingerCheck(new Date());
    setIsCheckingHostinger(false);
  }, []);

  useEffect(() => {
    checkHostingerConnection();
    const interval = setInterval(checkHostingerConnection, 45000);
    return () => clearInterval(interval);
  }, [checkHostingerConnection]);

  // Sync state to localStorage
  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_MAINTENANCE_KEY, JSON.stringify(maintenance));
    } catch (e) {
      console.error('Failed to persist maintenance settings:', e);
    }
  }, [maintenance]);

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_ACCOUNTS_KEY, JSON.stringify(accounts));
    } catch (e) {
      console.error('Failed to persist accounts:', e);
    }
  }, [accounts]);

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_ORDERS_KEY, JSON.stringify(orders));
    } catch (e) {
      console.error('Failed to persist orders:', e);
    }
  }, [orders]);

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_PAYMENTS_KEY, JSON.stringify(payments));
    } catch (e) {
      console.error('Failed to persist payments:', e);
    }
  }, [payments]);

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_REPLACEMENTS_KEY, JSON.stringify(replacements));
    } catch (e) {
      console.error('Failed to persist replacements:', e);
    }
  }, [replacements]);

  useEffect(() => {
    try {
      if (session) {
        localStorage.setItem(STORAGE_SESSION_KEY, JSON.stringify(session));
      } else {
        localStorage.removeItem(STORAGE_SESSION_KEY);
      }
    } catch (e) {
      console.error('Failed to persist session:', e);
    }
  }, [session]);

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_LOGS_KEY, JSON.stringify(logs));
    } catch (e) {
      console.error('Failed to persist logs:', e);
    }
  }, [logs]);

  const handleLoginSuccess = (newSession: UserSession) => {
    setSession(newSession);
    addLog('LOGIN_SUCCESS', `User ${newSession.username} logged in as ${newSession.role}`);
  };

  const handleLogout = () => {
    if (session) {
      addLog('LOGOUT', `User ${session.username} logged out`);
    }
    setSession(null);
    cyberAlertSuccess('Logout Ho Gaya', 'Aap kamyabi se portal se logout ho gaye hain.');
  };

  const addLog = useCallback((action: string, details: string) => {
    const newLog: AuditLog = {
      id: Date.now(),
      username: session?.username || 'system',
      role: session?.role || 'anonymous',
      action,
      details,
      timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19)
    };
    setLogs((prev) => [newLog, ...prev.slice(0, 49)]);
  }, [session]);

  // 2-Way Real-time Sync with Live Hostinger SQLite Database
  const [isSyncingLive, setIsSyncingLive] = useState(false);
  const [lastLiveSyncAt, setLastLiveSyncAt] = useState<Date | null>(null);

  const syncWithLiveDatabase = useCallback(async (isSilent = false) => {
    setIsSyncingLive(true);
    try {
      const res = await fetchFullSync();
      if (res && res.success) {
        if (Array.isArray(res.accounts) && res.accounts.length > 0) {
          setAccounts(res.accounts);
        }
        if (Array.isArray(res.orders) && res.orders.length > 0) {
          setOrders(res.orders);
        }
        if (Array.isArray(res.payments)) {
          setPayments(res.payments);
        }
        if (Array.isArray(res.replacements)) {
          setReplacements(res.replacements);
        }
        if (res.maintenance) {
          setMaintenance(res.maintenance);
        }
        if (Array.isArray(res.logs) && res.logs.length > 0) {
          setLogs(res.logs);
        }
        setLastLiveSyncAt(new Date());
        setIsHostingerOnline(true);
        if (!isSilent) {
          cyberAlertSuccess(
            'Live Database Synchronized',
            `<div class="text-xs text-slate-300 font-mono space-y-1">
              <p class="text-emerald-400 font-bold text-sm">Hostinger SQLite Se Real-Time Data Sync Ho Chuka Hai!</p>
              <p>Mails: <b class="text-white">${res.accounts?.length || 0}</b> | Orders: <b class="text-white">${res.orders?.length || 0}</b></p>
              <p>Payments: <b class="text-white">${res.payments?.length || 0}</b> | Deductions: <b class="text-white">${res.replacements?.length || 0}</b></p>
            </div>`
          );
        }
      }
    } catch (err) {
      console.warn('Live sync error:', err);
    } finally {
      setIsSyncingLive(false);
    }
  }, []);

  // Automatic 2-Way Real-time Polling every 20 seconds
  useEffect(() => {
    syncWithLiveDatabase(true);
    const syncInterval = setInterval(() => {
      syncWithLiveDatabase(true);
    }, 20000);
    return () => clearInterval(syncInterval);
  }, [syncWithLiveDatabase]);

  const handleUpdateMaintenance = useCallback(async (settings: MaintenanceSettings) => {
    setMaintenance(settings);
    try {
      await updateMaintenanceOnLive(settings.enabled, settings.message);
      addLog('MAINTENANCE_SYNC', `Hostinger live maintenance mode: ${settings.enabled ? 'ENABLED' : 'DISABLED'}`);
    } catch (err) {
      console.warn('Failed to update maintenance on Hostinger:', err);
    }
  }, [addLog]);

  return (
    <div className="relative min-h-screen flex flex-col justify-between selection:bg-cyan-500/30 selection:text-cyan-200">
      {/* Background */}
      <ParticlesBackground />

      {/* Navigation Header */}
      <Header
        session={session}
        onLogout={handleLogout}
        onOpenLiveModal={() => setIsLiveModalOpen(true)}
        isHostingerOnline={isHostingerOnline}
        latencyMs={hostingerLatency}
      />

      {/* Main Content Area */}
      <main className="relative z-10 flex-grow max-w-7xl w-full mx-auto px-4 sm:px-8 py-8">
        {!session && (
          <LoginCard onLoginSuccess={handleLoginSuccess} />
        )}

        {session && session.role === 'client' && (
          maintenance.enabled ? (
            <ClientMaintenanceScreen
              message={maintenance.message}
              onLogout={handleLogout}
            />
          ) : (
            <ClientDashboard
              accounts={accounts}
              orders={orders}
              payments={payments}
              replacements={replacements}
              onAccountsUpdate={setAccounts}
              onOrdersUpdate={setOrders}
              onAddLog={addLog}
            />
          )
        )}

        {session && session.role === 'admin' && (
          adminClientPreview ? (
            <ClientDashboard
              accounts={accounts}
              orders={orders}
              payments={payments}
              replacements={replacements}
              onAccountsUpdate={setAccounts}
              onOrdersUpdate={setOrders}
              onAddLog={addLog}
              isAdminPreview={true}
              onExitAdminPreview={() => setAdminClientPreview(false)}
              maintenanceMode={maintenance.enabled}
              onToggleMaintenance={(enabled) =>
                setMaintenance((prev) => ({ ...prev, enabled }))
              }
            />
          ) : (
            <AdminDashboard
              accounts={accounts}
              orders={orders}
              payments={payments}
              replacements={replacements}
              onAccountsUpdate={setAccounts}
              onOrdersUpdate={setOrders}
              onPaymentsUpdate={setPayments}
              onReplacementsUpdate={setReplacements}
              onAddLog={addLog}
              maintenance={maintenance}
              onUpdateMaintenance={handleUpdateMaintenance}
              onOpenClientPreview={() => setAdminClientPreview(true)}
              onSyncWithLive={() => syncWithLiveDatabase(false)}
              isSyncing={isSyncingLive}
              lastSyncedAt={lastLiveSyncAt}
            />
          )
        )}
      </main>

      {/* Footer */}
      <footer className="relative z-10 bg-slate-950/80 backdrop-blur-xl border-t border-slate-800/80 py-4 px-4 text-center text-xs font-mono text-slate-500">
        <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
          <div>
            Hadi Digital &bull; Email Accounts Management &amp; Distribution Portal
          </div>
          <div className="flex items-center space-x-2 text-[11px] text-slate-400">
            <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Secure Online System</span>
          </div>
        </div>
      </footer>
      {/* Live Hostinger & GitHub Sync Inspector Modal */}
      <LiveHostingerModal
        isOpen={isLiveModalOpen}
        onClose={() => setIsLiveModalOpen(false)}
        liveData={liveHostingerData}
        isOnline={isHostingerOnline}
        latencyMs={hostingerLatency}
        lastChecked={lastHostingerCheck}
        onRefresh={checkHostingerConnection}
        isChecking={isCheckingHostinger}
        onTriggerFullSync={() => syncWithLiveDatabase(false)}
        isSyncingLive={isSyncingLive}
      />
    </div>
  );
}
