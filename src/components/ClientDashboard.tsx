import React, { useState } from 'react';
import {
  Download,
  CheckCircle2,
  Copy,
  Check,
  Eye,
  X,
  History,
  ShieldCheck,
  Search,
  Wallet,
  ArrowDownCircle,
  FileSpreadsheet,
  AlertCircle,
  Clock,
  Layers,
  RefreshCw,
  FileText
} from 'lucide-react';
import { EmailAccount, ExportFormat, OrderRecord, ExtractedEmailItem, PaymentRecord, ReplacementRecord, sortOrdersNaturally } from '../types';
import { SECURE_EXTRACTION_PIN, MANAGED_DOMAINS } from '../data/constants';
import { cyberAlertError, cyberAlertSuccess, cyberPromptPin } from '../utils/cyberSwal';

interface ClientDashboardProps {
  accounts: EmailAccount[];
  orders: OrderRecord[];
  payments: PaymentRecord[];
  replacements: ReplacementRecord[];
  onAccountsUpdate: (updated: EmailAccount[]) => void;
  onOrdersUpdate: (updated: OrderRecord[]) => void;
  onAddLog: (action: string, details: string) => void;
  isAdminPreview?: boolean;
  onExitAdminPreview?: () => void;
  maintenanceMode?: boolean;
  onToggleMaintenance?: (enabled: boolean) => void;
}

export default function ClientDashboard({
  accounts,
  orders,
  payments,
  replacements,
  onAccountsUpdate,
  onOrdersUpdate,
  onAddLog,
  isAdminPreview,
  onExitAdminPreview,
  maintenanceMode,
  onToggleMaintenance
}: ClientDashboardProps) {
  const [selectedDomain, setSelectedDomain] = useState<string>(MANAGED_DOMAINS[0]);
  const [quantity, setQuantity] = useState<number | ''>(5);
  const [format, setFormat] = useState<ExportFormat>('csv');
  const [isProcessing, setIsProcessing] = useState(false);

  // Modals state
  const [viewingOrder, setViewingOrder] = useState<OrderRecord | null>(null);
  const [copiedOrderId, setCopiedOrderId] = useState<number | null>(null);
  const [showReplacedModal, setShowReplacedModal] = useState(false);
  const [replacedSearchTerm, setReplacedSearchTerm] = useState('');

  // Single Mail Search State
  const [searchMailQuery, setSearchMailQuery] = useState('');
  const [searchMailResult, setSearchMailResult] = useState<{
    account: EmailAccount | ExtractedEmailItem;
    domain: string;
    orderNumber?: string;
    date?: string;
  } | null>(null);
  const [searchMailError, setSearchMailError] = useState<string | null>(null);
  const [copiedSearchField, setCopiedSearchField] = useState<string | null>(null);

  // Compute Domain Stocks
  const getDomainStats = (domain: string) => {
    const forDomain = accounts.filter((a) => a.domain.toLowerCase() === domain.toLowerCase());
    const available = forDomain.filter((a) => a.status === 'available').length;
    const downloaded = forDomain.filter((a) => a.status === 'downloaded').length;
    const replaced = forDomain.filter((a) => a.status === 'replaced').length;
    return { available, downloaded, replaced, total: forDomain.length };
  };

  const domainStats = MANAGED_DOMAINS.map((domain) => ({
    domain,
    ...getDomainStats(domain)
  }));

  const currentDomainAvailable = getDomainStats(selectedDomain).available;

  // Compute Financial Ledger Metrics (strictly excluding reverted or cancelled orders)
  const isOrderDelivered = (o: OrderRecord) => {
    return o.status !== 'reverted' && o.status !== 'cancelled' && !o.notes?.includes('[REVERTED TO AVAILABLE STOCK]');
  };

  const activeDeliveredOrders = sortOrdersNaturally(orders.filter(isOrderDelivered));
  // Live Stock Math: totalDeliveredMails counts downloaded or replaced accounts
  const totalDeliveredMails = accounts.filter((a) => a.status === 'downloaded' || a.status === 'replaced').length;
  const replacedMailsCount = replacements.length > 0
    ? replacements.length
    : accounts.filter((a) => a.status === 'replaced').length;

  const netActiveMails = Math.max(0, totalDeliveredMails - replacedMailsCount);

  const avgRate = activeDeliveredOrders.length > 0
    ? (activeDeliveredOrders.reduce((sum, o) => sum + (o.rate_per_mail !== undefined ? o.rate_per_mail : 18), 0) / activeDeliveredOrders.length)
    : 18;

  const grossBilledAmount = totalDeliveredMails * avgRate;

  const totalDeductions = replacements.length > 0
    ? replacements.reduce((sum, r) => sum + (r.rate_deduction || 18), 0)
    : (replacedMailsCount * 18);

  const netBilledAmount = Math.max(0, grossBilledAmount - totalDeductions);
  const totalPaidAmount = payments.reduce((sum, p) => sum + (Number(p.amount) || 0), 0);
  const pendingBalance = netBilledAmount - totalPaidAmount;

  // Format Date-Time
  const formatDateTime = (dateStr: string) => {
    try {
      const d = new Date(dateStr.replace(' ', 'T'));
      if (isNaN(d.getTime())) return dateStr;
      return d.toLocaleString('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
      });
    } catch {
      return dateStr;
    }
  };

  // Helper to force download accounts file
  const downloadAccountsFile = (
    items: ExtractedEmailItem[],
    domain: string,
    fileFormat: ExportFormat,
    orderNum: string
  ) => {
    const lines: string[] = ['email,password,recovery email'];
    items.forEach((acc) => {
      lines.push(`${acc.email},${acc.password},${acc.recovery_email || ''}`);
    });

    const fileContent = lines.join('\r\n') + '\r\n';
    const cleanOrder = orderNum.replace(/[^a-zA-Z0-9]/g, '_');
    const filename = `${cleanOrder}_${domain.replace('.', '_')}_${Date.now()}.${fileFormat}`;
    const mimeType = fileFormat === 'csv' ? 'text/csv;charset=utf-8;' : 'text/plain;charset=utf-8;';

    const blob = new Blob([fileContent], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };

  // Helper to copy accounts to clipboard
  const copyAccountsToClipboard = (items: ExtractedEmailItem[], orderId: number) => {
    const lines: string[] = ['email,password,recovery email'];
    items.forEach((acc) => {
      lines.push(`${acc.email},${acc.password},${acc.recovery_email || ''}`);
    });
    const textToCopy = lines.join('\n');
    navigator.clipboard.writeText(textToCopy);
    setCopiedOrderId(orderId);
    setTimeout(() => setCopiedOrderId(null), 2000);
    cyberAlertSuccess('Mails Copy Ho Gayi!', 'Tamam mails clipboard par copy kar li gayi hain.');
  };

  // Handle Extraction Flow with PIN
  const handleExtract = async (e: React.FormEvent) => {
    e.preventDefault();

    const qty = typeof quantity === 'number' ? quantity : parseInt(quantity, 10);
    if (!qty || qty <= 0) {
      cyberAlertError('Ghalt Quantity', 'Barah-e-karam quantity likhein ke kitni mails chahiye.');
      return;
    }

    if (qty > currentDomainAvailable) {
      cyberAlertError(
        'Stock Kam Hai',
        `${selectedDomain} ke paas sirf ${currentDomainAvailable} mails available hain. Aap ne ${qty} likhi hain.`
      );
      return;
    }

    // SweetAlert2 PIN dialog (Does NOT expose PIN in UI)
    const enteredPin = await cyberPromptPin(selectedDomain, qty, format);
    if (!enteredPin) {
      return;
    }

    if (enteredPin.trim() !== SECURE_EXTRACTION_PIN) {
      cyberAlertError('Ghalt PIN', 'Security PIN durust nahi hai. Extraction cancel ho gayi.');
      onAddLog('PIN_FAILED', `Extraction fail hui invalid PIN ke sath (${selectedDomain})`);
      return;
    }

    setIsProcessing(true);

    try {
      const availableMatching = accounts.filter(
        (a) => a.domain.toLowerCase() === selectedDomain.toLowerCase() && a.status === 'available'
      );

      const accountsToExtract = availableMatching.slice(0, qty);
      const extractedIds = new Set(accountsToExtract.map((a) => a.id));

      const now = new Date();
      const nowStr = now.toISOString().replace('T', ' ').substring(0, 19);

      const updatedAccounts = accounts.map((a) => {
        if (extractedIds.has(a.id)) {
          return {
            ...a,
            status: 'downloaded' as const,
            downloaded_at: nowStr
          };
        }
        return a;
      });

      const orderItems: ExtractedEmailItem[] = accountsToExtract.map((a) => ({
        email: a.email,
        password: a.password,
        recovery_email: a.recovery_email || ''
      }));

      const orderCount = orders.length + 1;
      const rate = 18;
      const newOrder: OrderRecord = {
        id: Date.now(),
        order_number: `Order #${orderCount}`,
        client_username: 'rana asim',
        domain: selectedDomain,
        quantity: qty,
        rate_per_mail: rate,
        total_price: qty * rate,
        notes: 'Automated Extraction Portal',
        format,
        created_at: nowStr,
        accounts: orderItems
      };

      onAccountsUpdate(updatedAccounts);
      onOrdersUpdate([newOrder, ...orders]);
      onAddLog('EXTRACTION_SUCCESS', `Rana Asim ne ${qty} mails nikali (${selectedDomain}) - ${newOrder.order_number}`);

      downloadAccountsFile(orderItems, selectedDomain, format, newOrder.order_number);

      cyberAlertSuccess(
        'Mails Nikal Gayi Hain!',
        `
        <div class="text-xs text-slate-300 space-y-1 text-center font-mono">
          <p class="text-emerald-400 font-bold text-sm">${newOrder.order_number} Generate Ho Gaya</p>
          <p><b class="text-white">${qty}</b> Mails (${selectedDomain}) file download ho chuki hai.</p>
          <p class="text-slate-400 text-[11px] pt-1">Ledger me Rs. ${qty * rate} add ho gaya hai.</p>
        </div>
        `
      );

      setQuantity('');
    } catch (err) {
      cyberAlertError('Error', 'Mails nikalne ke doraan koi masla aya.');
    } finally {
      setIsProcessing(false);
    }
  };

  // Handle Single Mail Search
  const handleMailSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearchMailError(null);
    setSearchMailResult(null);

    const query = searchMailQuery.trim().toLowerCase();
    if (!query) {
      setSearchMailError('Barah-e-karam email address likhein.');
      return;
    }

    // Search in orders first
    for (const ord of orders) {
      if (ord.accounts && ord.accounts.length > 0) {
        const found = ord.accounts.find((a) => a.email.toLowerCase() === query);
        if (found) {
          setSearchMailResult({
            account: found,
            domain: ord.domain || found.email.split('@')[1] || 'Unknown',
            orderNumber: ord.order_number,
            date: ord.created_at
          });
          return;
        }
      }
    }

    // Search in general accounts list
    const accFound = accounts.find((a) => a.email.toLowerCase() === query);
    if (accFound) {
      setSearchMailResult({
        account: accFound,
        domain: accFound.domain,
        date: accFound.downloaded_at || accFound.created_at
      });
      return;
    }

    setSearchMailError(`"${query}" database ya order history me nahi mila.`);
  };

  // Helper to copy text to clipboard
  const copyText = (text: string, fieldKey: string) => {
    navigator.clipboard.writeText(text);
    setCopiedSearchField(fieldKey);
    setTimeout(() => setCopiedSearchField(null), 2000);
  };

  // Download Complete Statement CSV
  const handleDownloadStatement = () => {
    const lines: string[] = [];
    lines.push('HADI DIGITAL - CLIENT BILLING STATEMENT & KHATA LEDGER');
    lines.push(`Client: Rana Asim,Date: ${new Date().toLocaleString()}`);
    lines.push('');
    lines.push('--- FINANCIAL SUMMARY ---');
    lines.push(`Total Delivered Mails,${totalDeliveredMails}`);
    lines.push(`Faulty Mails Replaced,-${replacedMailsCount}`);
    lines.push(`Total Billable Mails,${netActiveMails}`);
    lines.push(`Total Bill Amount (PKR),Rs. ${Math.round(netBilledAmount)}`);
    lines.push(`Total Paid Amount (Wasooli PKR),Rs. ${Math.round(totalPaidAmount)}`);
    lines.push(`Pending Balance (Baqaya PKR),Rs. ${Math.round(pendingBalance)}`);
    lines.push('');
    lines.push('--- ORDERS & DELIVERY HISTORY ---');
    lines.push('Order Number,Date,Domain,Quantity,Rate (PKR),Total Amount (PKR)');
    activeDeliveredOrders.forEach((o) => {
      const rate = o.rate_per_mail !== undefined ? o.rate_per_mail : 18;
      const total = o.total_price !== undefined ? o.total_price : (o.quantity * rate);
      lines.push(`"${o.order_number}","${o.created_at}","${o.domain}",${o.quantity},${rate},${total}`);
    });
    lines.push('');
    lines.push('--- REPLACEMENTS (BILL DEDUCTIONS) ---');
    lines.push('Email,Domain,Rate Deduction (PKR),Reason,Date');
    const repList = replacements.length > 0
      ? replacements
      : accounts.filter((a) => a.status === 'replaced').map((a, i) => ({
          id: i,
          email: a.email,
          domain: a.domain,
          rate_deduction: 18,
          reason: 'Faulty Mail Replacement',
          created_at: a.replaced_at || a.created_at
        }));
    repList.forEach((r) => {
      lines.push(`"${r.email}","${r.domain}",-${r.rate_deduction || 18},"${r.reason || 'Faulty'}","${r.created_at}"`);
    });
    lines.push('');
    lines.push('--- PAYMENTS (WASOOLI LEDGER) ---');
    lines.push('Payment ID,Date,Payment Method,Reference Note,Amount (PKR)');
    payments.forEach((p, idx) => {
      lines.push(`"PAY-#${p.id || idx + 1}","${p.payment_date}","${p.payment_method}","${p.reference_note || ''}",${p.amount}`);
    });

    const csvContent = lines.join('\r\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Rana_Asim_Billing_Statement_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };

  // Replaced accounts list
  const displayReplacedList = replacements.length > 0
    ? replacements
    : accounts.filter((a) => a.status === 'replaced').map((a, i) => ({
        id: i + 1,
        email: a.email,
        domain: a.domain,
        rate_deduction: 18,
        reason: 'Faulty Account Deducted',
        created_at: a.replaced_at || a.created_at
      }));

  const filteredReplacedList = displayReplacedList.filter((r) =>
    r.email.toLowerCase().includes(replacedSearchTerm.toLowerCase()) ||
    r.domain.toLowerCase().includes(replacedSearchTerm.toLowerCase())
  );

  return (
    <div id="client-dashboard-terminal" className="space-y-8 animate__animated animate__fadeIn">
      {/* Admin Preview Top Bar */}
      {isAdminPreview && (
        <div className="p-4 bg-amber-950/80 border border-amber-500/60 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-mono shadow-xl backdrop-blur-xl">
          <div className="flex items-center space-x-2.5 text-amber-300">
            <span className="w-3 h-3 rounded-full bg-amber-400 animate-ping flex-shrink-0" />
            <div>
              <strong className="text-white">ADMIN CLIENT PREVIEW &amp; TEST MODE:</strong>{' '}
              {maintenanceMode ? (
                <span className="text-amber-300">
                  Maintenance Mode <b>ACTIVE</b> hai (Aam clients ko maintenance screen nazar aa rahi hai). Aap live testing kar sakte hain.
                </span>
              ) : (
                <span className="text-emerald-400">Portal LIVE hai. Aap client perspective test kar rahe hain.</span>
              )}
            </div>
          </div>
          <div className="flex items-center space-x-2 flex-shrink-0">
            {maintenanceMode && onToggleMaintenance && (
              <button
                type="button"
                onClick={() => onToggleMaintenance(false)}
                className="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl transition-all shadow-md cursor-pointer"
              >
                🟢 Live Kar Dein
              </button>
            )}
            {onExitAdminPreview && (
              <button
                type="button"
                onClick={onExitAdminPreview}
                className="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-cyan-300 font-bold rounded-xl transition-all cursor-pointer"
              >
                Wapis Admin Panel ➔
              </button>
            )}
          </div>
        </div>
      )}

      {/* 1. TOP WELCOME BANNER (Clean, no awkward badge) */}
      <div
        id="client-welcome-banner"
        className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xl"
      >
        <div>
          <div className="flex items-center space-x-2 mb-1">
            <span className="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse" />
            <span className="text-xs font-mono uppercase tracking-widest text-emerald-400">
              Client Distribution &amp; Khata Ledger
            </span>
          </div>
          <h2 className="text-2xl font-bold text-white tracking-tight">Rana Asim Portal</h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Mojooda stock, automated extraction, financial Khata ledger, aur order history console.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <button
            id="btn-download-client-statement"
            type="button"
            onClick={handleDownloadStatement}
            className="px-4 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-xl text-xs font-mono font-semibold flex items-center space-x-2 transition-all cursor-pointer shadow-sm hover:shadow-emerald-500/10"
          >
            <FileSpreadsheet className="w-4 h-4" />
            <span>Download Statement (CSV)</span>
          </button>
        </div>
      </div>

      {/* 2. MASTER FINANCIAL SUMMARY & KHATA LEDGER (Top Cards) */}
      <div id="section-khata-ledger" className="space-y-3">
        <div className="flex items-center justify-between">
          <h3 className="text-xs font-mono text-slate-400 uppercase tracking-widest flex items-center space-x-2">
            <Wallet className="w-4 h-4 text-cyan-400" />
            <span>Client Khata &amp; Billing Statement</span>
          </h3>
          <span className="text-[11px] font-mono text-slate-500">Live Auto-Calculated Ledger</span>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
          {/* Card 1: Total Delivered Mails */}
          <div
            id="card-client-gross-mails"
            className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-md"
          >
            <div className="flex items-center justify-between text-slate-400 mb-1">
              <span className="text-[11px] font-mono uppercase tracking-wider">Total Delivered</span>
              <Layers className="w-4 h-4 text-slate-400" />
            </div>
            <div>
              <div className="text-2xl font-bold font-mono text-white tracking-tight">
                {totalDeliveredMails.toLocaleString()}
              </div>
              <div className="text-[10px] text-slate-500 font-mono mt-0.5">All delivered orders</div>
            </div>
          </div>

          {/* Card 2: Faulty Mails (Replaced) */}
          <div
            id="card-client-replaced-mails"
            className="bg-slate-900/80 backdrop-blur-xl border border-amber-500/30 rounded-2xl p-4 flex flex-col justify-between shadow-md"
          >
            <div className="flex items-center justify-between text-amber-400 mb-1">
              <span className="text-[11px] font-mono uppercase tracking-wider">Faulty Replaced</span>
              <RefreshCw className="w-4 h-4 text-amber-400" />
            </div>
            <div>
              <div className="text-2xl font-bold font-mono text-amber-300 tracking-tight">
                -{replacedMailsCount.toLocaleString()}
              </div>
              <div className="text-[10px] text-amber-400/70 font-mono mt-0.5">Deducted from bill</div>
            </div>
          </div>

          {/* Card 3: Total Billable Mails */}
          <div
            id="card-client-net-mails"
            className="bg-slate-900/80 backdrop-blur-xl border border-emerald-500/30 rounded-2xl p-4 flex flex-col justify-between shadow-md"
          >
            <div className="flex items-center justify-between text-emerald-400 mb-1">
              <span className="text-[11px] font-mono uppercase tracking-wider">Total Billable Mails</span>
              <CheckCircle2 className="w-4 h-4 text-emerald-400" />
            </div>
            <div>
              <div className="text-2xl font-bold font-mono text-emerald-300 tracking-tight">
                {netActiveMails.toLocaleString()}
              </div>
              <div className="text-[10px] text-emerald-400/70 font-mono mt-0.5">Delivered minus faulty</div>
            </div>
          </div>

          {/* Card 4: Total Bill */}
          <div
            id="card-client-total-bill"
            className="bg-slate-900/80 backdrop-blur-xl border border-cyan-500/30 rounded-2xl p-4 flex flex-col justify-between shadow-md"
          >
            <div className="flex items-center justify-between text-cyan-300 mb-1">
              <span className="text-[11px] font-mono uppercase tracking-wider">Total Bill (PKR)</span>
              <FileSpreadsheet className="w-4 h-4 text-cyan-400" />
            </div>
            <div>
              <div className="text-xl font-bold font-mono text-cyan-200 tracking-tight truncate">
                Rs. {Math.round(netBilledAmount).toLocaleString()}
              </div>
              <div className="text-[10px] text-cyan-300/70 font-mono mt-0.5">Total net amount</div>
            </div>
          </div>

          {/* Card 5: Total Wasooli Received */}
          <div
            id="card-client-total-paid"
            className="bg-slate-900/80 backdrop-blur-xl border border-indigo-500/30 rounded-2xl p-4 flex flex-col justify-between shadow-md"
          >
            <div className="flex items-center justify-between text-indigo-400 mb-1">
              <span className="text-[11px] font-mono uppercase tracking-wider">Total Wasooli</span>
              <ArrowDownCircle className="w-4 h-4 text-indigo-400" />
            </div>
            <div>
              <div className="text-xl font-bold font-mono text-indigo-300 tracking-tight truncate">
                Rs. {Math.round(totalPaidAmount).toLocaleString()}
              </div>
              <div className="text-[10px] text-indigo-400/70 font-mono mt-0.5">Received from client</div>
            </div>
          </div>

          {/* Card 6: Pending Balance (Baqaya) */}
          <div
            id="card-client-pending-balance"
            className={`bg-slate-900/80 backdrop-blur-xl rounded-2xl p-4 flex flex-col justify-between shadow-md border ${
              pendingBalance > 0
                ? 'border-rose-500/40 bg-rose-950/10'
                : 'border-emerald-500/40 bg-emerald-950/10'
            }`}
          >
            <div className="flex items-center justify-between text-slate-300 mb-1">
              <span className="text-[11px] font-mono uppercase tracking-wider">Baqaya (Balance)</span>
              <Clock className={`w-4 h-4 ${pendingBalance > 0 ? 'text-rose-400' : 'text-emerald-400'}`} />
            </div>
            <div>
              <div
                className={`text-xl font-bold font-mono tracking-tight truncate ${
                  pendingBalance > 0 ? 'text-rose-400' : 'text-emerald-400'
                }`}
              >
                Rs. {Math.round(pendingBalance).toLocaleString()}
              </div>
              <div className="mt-1">
                <span
                  className={`inline-block px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase tracking-wider ${
                    pendingBalance > 0
                      ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30'
                      : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                  }`}
                >
                  {pendingBalance > 0 ? 'Payable (Baqaya)' : 'All Dues Cleared'}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* 3. REAL-TIME STOCK LEVELS BY DOMAIN */}
      <div id="section-stock-levels">
        <h3 className="text-xs font-mono text-slate-400 uppercase tracking-widest mb-3 flex items-center space-x-2">
          <span>Mojooda Stock (Real-Time Inventory By Domain)</span>
          <span className="h-px flex-grow bg-slate-800" />
        </h3>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {domainStats.map((item) => (
            <div
              key={item.domain}
              id={`stock-card-${item.domain.replace('.', '-')}`}
              className={`bg-slate-900/80 backdrop-blur-xl rounded-2xl p-6 relative overflow-hidden transition-all border ${
                item.available > 0 ? 'border-cyan-500/40 shadow-[0_0_25px_rgba(6,182,212,0.12)]' : 'border-slate-800'
              }`}
            >
              <div className="flex items-center justify-between mb-2">
                <div className="flex items-center space-x-2">
                  <div
                    className={`w-2.5 h-2.5 rounded-full ${
                      item.available > 0 ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500'
                    }`}
                  />
                  <span className="text-xs font-mono text-slate-400">Domain</span>
                </div>
                <span
                  className={`px-2.5 py-0.5 rounded-full text-[11px] font-mono ${
                    item.available > 0
                      ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20'
                      : 'bg-rose-500/10 text-rose-300 border border-rose-500/20'
                  }`}
                >
                  {item.available > 0 ? 'Stock Available' : 'Stock Khatam'}
                </span>
              </div>

              <div className="text-2xl font-bold font-mono text-white mb-4 tracking-tight">
                {item.domain}
              </div>

              <div className="grid grid-cols-3 gap-2 pt-3 border-t border-slate-800/80 text-center font-mono">
                <div className="bg-slate-950/50 p-2.5 rounded-xl border border-slate-800">
                  <div className="text-[10px] text-slate-400 uppercase">Available (Stock)</div>
                  <div className="text-xl font-bold text-emerald-400 mt-0.5">{item.available}</div>
                </div>
                <div className="bg-slate-950/50 p-2.5 rounded-xl border border-slate-800">
                  <div className="text-[10px] text-slate-400 uppercase">Nikali Gayi</div>
                  <div className="text-xl font-bold text-cyan-400 mt-0.5">{item.downloaded}</div>
                </div>
                <div className="bg-slate-950/50 p-2.5 rounded-xl border border-slate-800">
                  <div className="text-[10px] text-slate-400 uppercase">Replaced</div>
                  <div className="text-xl font-bold text-amber-400 mt-0.5">{item.replaced}</div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* 4. DUAL PANELS: EXTRACTION ENGINE & REPLACED MAILS DEDUCTIONS CARD */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {/* EXTRACTION ENGINE (7 Cols) */}
        <div
          id="terminal-extraction"
          className="lg:col-span-7 bg-slate-900/80 backdrop-blur-2xl border border-cyan-500/40 rounded-2xl p-6 lg:p-8 relative shadow-[0_0_30px_rgba(6,182,212,0.1)] flex flex-col justify-between"
        >
          <div>
            <div className="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
              <div>
                <h3 className="text-lg font-bold text-white flex items-center space-x-2">
                  <Download className="w-5 h-5 text-cyan-400" />
                  <span>Mails Nikalein (Extract Accounts)</span>
                </h3>
                <p className="text-xs text-slate-400 mt-1">
                  Domain select karein, quantity likhein, PIN enter karein aur file download karein.
                </p>
              </div>
            </div>

            <form id="form-account-extraction" onSubmit={handleExtract} className="space-y-5">
              {/* Domain Selection */}
              <div>
                <label className="block text-xs font-mono text-slate-300 mb-2 uppercase tracking-wider">
                  Domain Select Karein
                </label>
                <div className="grid grid-cols-2 gap-3" id="extract-domain-chips">
                  {MANAGED_DOMAINS.map((domain) => {
                    const stock = getDomainStats(domain).available;
                    const isSelected = selectedDomain === domain;
                    return (
                      <button
                        type="button"
                        key={domain}
                        id={`btn-select-domain-${domain.replace('.', '-')}`}
                        onClick={() => setSelectedDomain(domain)}
                        className={`p-3.5 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer ${
                          isSelected
                            ? 'border-cyan-400 bg-cyan-950/30 shadow-[0_0_15px_rgba(6,182,212,0.2)]'
                            : 'border-slate-800 bg-slate-950/40 hover:border-slate-700'
                        }`}
                      >
                        <div>
                          <div className="text-sm font-semibold font-mono text-white">{domain}</div>
                          <div className="text-[11px] text-slate-400 font-mono mt-0.5">
                            <span className={stock > 0 ? 'text-emerald-400 font-bold' : 'text-rose-400 font-bold'}>
                              {stock}
                            </span>{' '}
                            available
                          </div>
                        </div>
                        <span
                          className={`w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all ${
                            isSelected ? 'border-cyan-400 bg-cyan-400' : 'border-slate-600'
                          }`}
                        >
                          {isSelected && <span className="w-1.5 h-1.5 rounded-full bg-slate-950" />}
                        </span>
                      </button>
                    );
                  })}
                </div>
              </div>

              {/* Quantity Input (Manual Typing) */}
              <div>
                <div className="flex items-center justify-between mb-2">
                  <label
                    htmlFor="input-extract-qty"
                    className="text-xs font-mono text-slate-300 uppercase tracking-wider"
                  >
                    Quantity Likhein (Kitni Mails Chahiye?)
                  </label>
                  <span id="label-domain-available" className="text-xs font-mono text-cyan-400">
                    Stock: <b className="text-emerald-400">{currentDomainAvailable}</b>
                  </span>
                </div>
                <input
                  id="input-extract-qty"
                  type="number"
                  min="1"
                  max={currentDomainAvailable || 1}
                  required
                  value={quantity}
                  onChange={(e) => setQuantity(e.target.value === '' ? '' : parseInt(e.target.value, 10))}
                  className="w-full px-4 py-3 bg-slate-950/90 border border-slate-700/80 rounded-xl text-white font-mono text-base focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all placeholder:text-slate-600"
                  placeholder="Number likhein (e.g. 5, 10, 20...)"
                />
                <p className="text-[11px] text-slate-500 font-mono mt-1.5">
                  * Rate: Rs. 18.00 / mail. Order generate hote hi file download hogi aur ledger update hoga.
                </p>
              </div>

              {/* Format Selection */}
              <div>
                <label className="block text-xs font-mono text-slate-300 mb-2 uppercase tracking-wider">
                  File Format
                </label>
                <div className="grid grid-cols-2 gap-3">
                  <button
                    type="button"
                    onClick={() => setFormat('csv')}
                    className={`p-3 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer ${
                      format === 'csv'
                        ? 'border-cyan-400 bg-cyan-950/30'
                        : 'border-slate-800 bg-slate-950/40 hover:border-slate-700'
                    }`}
                  >
                    <div>
                      <div className="text-xs font-semibold text-white font-mono">CSV File (.csv)</div>
                      <div className="text-[10px] text-slate-400">Excel / Spreadsheet</div>
                    </div>
                    <span className="text-xs font-mono font-bold text-cyan-400">CSV</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => setFormat('txt')}
                    className={`p-3 rounded-xl border text-left transition-all flex items-center justify-between cursor-pointer ${
                      format === 'txt'
                        ? 'border-cyan-400 bg-cyan-950/30'
                        : 'border-slate-800 bg-slate-950/40 hover:border-slate-700'
                    }`}
                  >
                    <div>
                      <div className="text-xs font-semibold text-white font-mono">Text File (.txt)</div>
                      <div className="text-[10px] text-slate-400">Plain Text Notepad</div>
                    </div>
                    <span className="text-xs font-mono font-bold text-cyan-400">TXT</span>
                  </button>
                </div>
              </div>

              {/* Header info */}
              <div className="p-3 rounded-xl bg-slate-950/70 border border-slate-800 text-[11px] font-mono text-slate-400 flex items-center space-x-2">
                <CheckCircle2 className="w-4 h-4 text-cyan-400 flex-shrink-0" />
                <span>
                  File header: <b className="text-cyan-300">email,password,recovery email</b> (Column A, B, C bina space)
                </span>
              </div>

              {/* Extraction Action Button */}
              <button
                id="btn-execute-extract"
                type="submit"
                disabled={isProcessing || currentDomainAvailable === 0}
                className="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-sky-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-white font-bold text-xs font-mono tracking-widest uppercase shadow-[0_0_25px_rgba(6,182,212,0.35)] hover:shadow-cyan-500/50 transition-all flex items-center justify-center space-x-2 cursor-pointer disabled:opacity-50"
              >
                <Download className="w-4 h-4" />
                <span>{isProcessing ? 'PROCESSING...' : 'MAILS NIKALEIN (ENTER PIN)'}</span>
              </button>
            </form>
          </div>
        </div>

        {/* REPLACED MAILS DEDUCTIONS CARD (5 Cols - NO TEXTAREA, READ ONLY WITH VIEW BUTTON) */}
        <div
          id="terminal-replacement-card"
          className="lg:col-span-5 bg-slate-900/80 backdrop-blur-xl border border-amber-500/30 rounded-2xl p-6 lg:p-8 relative flex flex-col justify-between shadow-xl"
        >
          <div>
            <div className="flex items-center justify-between mb-4 pb-4 border-b border-slate-800">
              <div>
                <h3 className="text-lg font-bold text-white flex items-center space-x-2">
                  <RefreshCw className="w-5 h-5 text-amber-400" />
                  <span>Faulty Mails Deductions</span>
                </h3>
                <p className="text-xs text-slate-400 mt-1">
                  Kharab mails ka bill ledger me se automatically minus ho chuka hai.
                </p>
              </div>
              <span className="px-2.5 py-1 rounded-full text-[10px] font-mono bg-amber-500/10 text-amber-300 border border-amber-500/30">
                Direct Credit
              </span>
            </div>

            {/* Prominent Deductions Stats */}
            <div className="space-y-3.5 my-6">
              <div className="p-4 rounded-xl bg-slate-950/80 border border-slate-800/80 flex items-center justify-between">
                <div>
                  <div className="text-xs text-slate-400 font-mono uppercase tracking-wider">
                    Replaced Accounts (Deducted)
                  </div>
                  <div className="text-3xl font-extrabold font-mono text-amber-300 mt-1">
                    {replacedMailsCount} <span className="text-sm font-normal text-slate-400">Mails</span>
                  </div>
                </div>
                <div className="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                  <RefreshCw className="w-6 h-6" />
                </div>
              </div>

              <div className="p-4 rounded-xl bg-slate-950/80 border border-slate-800/80 flex items-center justify-between">
                <div>
                  <div className="text-xs text-slate-400 font-mono uppercase tracking-wider">
                    Total Amount Deducted From Bill
                  </div>
                  <div className="text-2xl font-bold font-mono text-emerald-400 mt-1">
                    -Rs. {totalDeductions.toLocaleString()}
                  </div>
                </div>
                <div className="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                  <Wallet className="w-6 h-6" />
                </div>
              </div>
            </div>

            <div className="p-3.5 rounded-xl bg-amber-500/5 border border-amber-500/20 text-xs font-mono text-slate-300 space-y-1 mb-6">
              <div className="flex items-center space-x-2 text-amber-400 font-bold">
                <CheckCircle2 className="w-4 h-4" />
                <span>Automatic Bill Settlement</span>
              </div>
              <p className="text-[11px] text-slate-400 leading-relaxed">
                Tamam faulty mails ka amount client billing ledger se foran deduct ho chuka hai. Kisi nayi faulty mail ke liye Admin (Hadi) se rabta karein.
              </p>
            </div>
          </div>

          <button
            id="btn-view-all-replacements"
            type="button"
            onClick={() => setShowReplacedModal(true)}
            className="w-full py-3.5 px-4 rounded-xl bg-slate-950 hover:bg-slate-900 border border-amber-500/40 text-amber-300 hover:text-amber-200 font-mono text-xs font-bold tracking-wider uppercase transition-all flex items-center justify-center space-x-2 cursor-pointer shadow-md"
          >
            <Eye className="w-4 h-4" />
            <span>VIEW ALL REPLACED MAILS ({replacedMailsCount})</span>
          </button>
        </div>
      </div>

      {/* 5. SINGLE MAIL CREDENTIAL FINDER */}
      <div
        id="section-mail-finder"
        className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 lg:p-8 shadow-xl"
      >
        <div className="flex items-center space-x-2 mb-2">
          <Search className="w-5 h-5 text-cyan-400" />
          <h3 className="text-lg font-bold text-white tracking-tight">Mail Credential Finder</h3>
        </div>
        <p className="text-xs text-slate-400 mb-6">
          Kisi bhi purchased email ko search karein aur uska Password aur Recovery mail foran hasil karein.
        </p>

        <form onSubmit={handleMailSearch} className="flex flex-col sm:flex-row items-center gap-3">
          <div className="relative flex-1 w-full">
            <input
              id="input-client-search-mail"
              type="text"
              required
              placeholder="e.g. alpha_01@basis5.ch"
              value={searchMailQuery}
              onChange={(e) => setSearchMailQuery(e.target.value)}
              className="w-full pl-4 pr-10 py-3 bg-slate-950/90 border border-slate-700/80 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all placeholder:text-slate-600"
            />
          </div>
          <button
            id="btn-execute-mail-search"
            type="submit"
            className="w-full sm:w-auto px-6 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-mono font-bold text-xs uppercase tracking-wider rounded-xl transition-all flex items-center justify-center space-x-2 cursor-pointer shadow-md"
          >
            <Search className="w-4 h-4" />
            <span>Search Credential</span>
          </button>
        </form>

        {searchMailError && (
          <div className="mt-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-mono flex items-center space-x-2">
            <AlertCircle className="w-4 h-4 flex-shrink-0" />
            <span>{searchMailError}</span>
          </div>
        )}

        {searchMailResult && (
          <div className="mt-6 p-5 rounded-2xl bg-slate-950/90 border border-cyan-500/30 animate__animated animate__fadeIn">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-slate-800">
              <div className="flex items-center space-x-2">
                <span className="w-2 h-2 rounded-full bg-emerald-400" />
                <span className="text-xs font-mono text-cyan-400 font-bold uppercase tracking-wider">
                  Credential Found
                </span>
                {searchMailResult.orderNumber && (
                  <span className="px-2 py-0.5 rounded text-[10px] font-mono bg-cyan-950 text-cyan-300 border border-cyan-800">
                    {searchMailResult.orderNumber}
                  </span>
                )}
              </div>
              <button
                type="button"
                onClick={() => {
                  const item = searchMailResult.account;
                  const text = `${item.email},${item.password},${item.recovery_email || ''}`;
                  copyText(text, 'full_combo');
                }}
                className="px-3 py-1.5 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 rounded-lg text-xs font-mono flex items-center space-x-1.5 transition-all cursor-pointer"
              >
                {copiedSearchField === 'full_combo' ? (
                  <>
                    <Check className="w-3.5 h-3.5 text-emerald-400" />
                    <span>Copied Combo!</span>
                  </>
                ) : (
                  <>
                    <Copy className="w-3.5 h-3.5" />
                    <span>Copy email,pass,rec</span>
                  </>
                )}
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-3 font-mono text-xs">
              <div className="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                <div>
                  <div className="text-[10px] text-slate-500 uppercase">Email Address</div>
                  <div className="text-white font-bold break-all mt-0.5">{searchMailResult.account.email}</div>
                </div>
                <button
                  type="button"
                  onClick={() => copyText(searchMailResult.account.email, 'email')}
                  className="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded transition-colors ml-2 cursor-pointer"
                  title="Copy email"
                >
                  {copiedSearchField === 'email' ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                </button>
              </div>

              <div className="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                <div>
                  <div className="text-[10px] text-slate-500 uppercase">Password</div>
                  <div className="text-cyan-300 font-bold break-all mt-0.5">{searchMailResult.account.password}</div>
                </div>
                <button
                  type="button"
                  onClick={() => copyText(searchMailResult.account.password, 'password')}
                  className="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded transition-colors ml-2 cursor-pointer"
                  title="Copy password"
                >
                  {copiedSearchField === 'password' ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                </button>
              </div>

              <div className="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                <div>
                  <div className="text-[10px] text-slate-500 uppercase">Recovery Email</div>
                  <div className="text-slate-300 break-all mt-0.5">{searchMailResult.account.recovery_email || 'N/A'}</div>
                </div>
                {searchMailResult.account.recovery_email && (
                  <button
                    type="button"
                    onClick={() => copyText(searchMailResult.account.recovery_email, 'rec')}
                    className="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded transition-colors ml-2 cursor-pointer"
                    title="Copy recovery"
                  >
                    {copiedSearchField === 'rec' ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                  </button>
                )}
              </div>
            </div>
          </div>
        )}
      </div>

      {/* 6. ORDER HISTORY (DELIVERY CONSOLE) */}
      <div
        id="section-order-history"
        className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 lg:p-8 shadow-xl"
      >
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-800">
          <div>
            <h3 className="text-lg font-bold text-white flex items-center space-x-2">
              <History className="w-5 h-5 text-cyan-400" />
              <span>Order History (Nikali Gayi Mails Ka Record)</span>
            </h3>
            <p className="text-xs text-slate-400 mt-1">
              Har extraction ka order number, real date/time, rate aur dobara download ya copy karne ki sahulat.
            </p>
          </div>
          <div className="text-xs font-mono text-slate-400 bg-slate-950/60 px-3 py-1.5 rounded-xl border border-slate-800">
            Total Orders: <b className="text-cyan-400">{activeDeliveredOrders.length}</b>
          </div>
        </div>

        {activeDeliveredOrders.length === 0 ? (
          <div className="text-center py-12 border border-dashed border-slate-800 rounded-2xl bg-slate-950/40">
            <History className="w-10 h-10 text-slate-600 mx-auto mb-3 opacity-60" />
            <p className="text-sm font-mono text-slate-300 font-semibold">Koi Order Record Nahi Mila</p>
            <p className="text-xs text-slate-500 font-mono mt-1">
              Upar diye gaye form se mails nikalein, record yahan automatically save ho jayega.
            </p>
          </div>
        ) : (
          <div className="overflow-x-auto rounded-xl border border-slate-800/80">
            <table className="w-full text-left text-xs font-mono">
              <thead className="bg-slate-950/90 text-slate-400 uppercase text-[11px] border-b border-slate-800">
                <tr>
                  <th className="p-3.5">Order Number</th>
                  <th className="p-3.5">Date &amp; Time</th>
                  <th className="p-3.5">Domain</th>
                  <th className="p-3.5 text-center">Quantity</th>
                  <th className="p-3.5 text-right">Rate</th>
                  <th className="p-3.5 text-right">Total Price</th>
                  <th className="p-3.5 text-center">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60 bg-slate-950/30">
                {activeDeliveredOrders.map((ord) => {
                  const rate = ord.rate_per_mail !== undefined ? ord.rate_per_mail : 18;
                  const total = ord.total_price !== undefined ? ord.total_price : (ord.quantity * rate);
                  return (
                    <tr key={ord.id} className="hover:bg-slate-900/50 transition-colors">
                      <td className="p-3.5 font-bold text-white flex items-center space-x-2">
                        <span className="w-2 h-2 rounded-full bg-cyan-400" />
                        <span className="text-cyan-300 font-bold">{ord.order_number}</span>
                      </td>
                      <td className="p-3.5 text-slate-300">{formatDateTime(ord.created_at)}</td>
                      <td className="p-3.5 text-slate-300 font-bold">{ord.domain}</td>
                      <td className="p-3.5 text-center">
                        <span className="px-2.5 py-1 rounded-full bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 font-bold">
                          {ord.quantity} Mails
                        </span>
                      </td>
                      <td className="p-3.5 text-right text-slate-400">Rs. {rate}</td>
                      <td className="p-3.5 text-right text-emerald-400 font-bold">
                        Rs. {total.toLocaleString()}
                      </td>
                      <td className="p-3.5 text-center">
                        <div className="flex items-center justify-center space-x-2">
                          <button
                            type="button"
                            onClick={() => downloadAccountsFile(ord.accounts, ord.domain, ord.format, ord.order_number)}
                            className="px-3 py-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold flex items-center space-x-1.5 transition-all cursor-pointer shadow-sm shadow-cyan-500/20"
                            title="Download CSV File"
                          >
                            <Download className="w-3.5 h-3.5" />
                            <span>Download CSV</span>
                          </button>

                          <button
                            type="button"
                            onClick={() => setViewingOrder(ord)}
                            className="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center space-x-1 transition-all cursor-pointer"
                            title="Mails Dekhein"
                          >
                            <Eye className="w-3.5 h-3.5 text-cyan-400" />
                            <span>Mails Dekhein</span>
                          </button>

                          <button
                            type="button"
                            onClick={() => copyAccountsToClipboard(ord.accounts, ord.id)}
                            className="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center space-x-1 transition-all cursor-pointer"
                            title="Copy Mails"
                          >
                            {copiedOrderId === ord.id ? (
                              <>
                                <Check className="w-3.5 h-3.5 text-emerald-400" />
                                <span className="text-emerald-400">Copied</span>
                              </>
                            ) : (
                              <>
                                <Copy className="w-3.5 h-3.5 text-slate-400" />
                                <span>Copy</span>
                              </>
                            )}
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* 7. PAYMENTS HISTORY (WASOOLI LEDGER) */}
      <div
        id="section-payment-history"
        className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 lg:p-8 shadow-xl"
      >
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-800">
          <div>
            <h3 className="text-lg font-bold text-white flex items-center space-x-2">
              <ArrowDownCircle className="w-5 h-5 text-emerald-400" />
              <span>Payments History (Wasooli Ledger)</span>
            </h3>
            <p className="text-xs text-slate-400 mt-1">
              Client ki taraf se received tamam payments ka record with date, payment method, aur status.
            </p>
          </div>
          <div className="text-xs font-mono text-slate-300 bg-slate-950/60 px-3 py-1.5 rounded-xl border border-slate-800">
            Total Received: <b className="text-emerald-400">Rs. {Math.round(totalPaidAmount).toLocaleString()}</b>
          </div>
        </div>

        {payments.length === 0 ? (
          <div className="text-center py-10 border border-dashed border-slate-800 rounded-2xl bg-slate-950/40">
            <Wallet className="w-10 h-10 text-slate-600 mx-auto mb-2 opacity-60" />
            <p className="text-sm font-mono text-slate-400">Filhal koi payment record nahi mila.</p>
            <p className="text-xs text-slate-500 font-mono mt-0.5">Admin payment record add karega to yahan show hogi.</p>
          </div>
        ) : (
          <div className="overflow-x-auto rounded-xl border border-slate-800/80">
            <table className="w-full text-left text-xs font-mono">
              <thead className="bg-slate-950/90 text-slate-400 uppercase text-[11px] border-b border-slate-800">
                <tr>
                  <th className="p-3.5">Payment ID</th>
                  <th className="p-3.5">Date</th>
                  <th className="p-3.5">Method</th>
                  <th className="p-3.5">Reference / Note</th>
                  <th className="p-3.5 text-right">Amount Received</th>
                  <th className="p-3.5 text-center">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-800/60 bg-slate-950/30">
                {payments.map((p, idx) => (
                  <tr key={p.id || idx} className="hover:bg-slate-900/50 transition-colors">
                    <td className="p-3.5 font-bold text-white">PAY-#{p.id || idx + 1}</td>
                    <td className="p-3.5 text-slate-300">{p.payment_date}</td>
                    <td className="p-3.5 text-slate-300">
                      <span className="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300">
                        {p.payment_method}
                      </span>
                    </td>
                    <td className="p-3.5 text-slate-400">{p.reference_note || '—'}</td>
                    <td className="p-3.5 text-right font-bold text-emerald-400">
                      Rs. {Number(p.amount).toLocaleString()}
                    </td>
                    <td className="p-3.5 text-center">
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-300 border border-emerald-500/30">
                        VERIFIED
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* MODAL 1: INSPECT ORDER ACCOUNTS */}
      {viewingOrder && (
        <div
          id="modal-inspect-order"
          className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate__animated animate__fadeIn animate__faster"
        >
          <div className="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
              <div className="flex items-center space-x-3">
                <div className="w-3 h-3 rounded-full bg-cyan-400 animate-pulse" />
                <div>
                  <h3 className="text-base font-bold text-white font-mono flex items-center space-x-2">
                    <span>{viewingOrder.order_number}</span>
                    <span className="text-xs text-slate-400 font-normal">({viewingOrder.domain})</span>
                  </h3>
                  <p className="text-[11px] text-slate-400 font-mono mt-0.5">
                    Date: {formatDateTime(viewingOrder.created_at)} &bull; Quantity: {viewingOrder.quantity} Mails
                  </p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setViewingOrder(null)}
                className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-4 bg-slate-950/40 border-b border-slate-800/80 flex items-center justify-between gap-3 text-xs font-mono">
              <div className="text-slate-400">
                Format: <b className="text-cyan-400 uppercase">{viewingOrder.format}</b> (email,password,recovery email)
              </div>
              <div className="flex items-center space-x-2">
                <button
                  type="button"
                  onClick={() =>
                    downloadAccountsFile(
                      viewingOrder.accounts,
                      viewingOrder.domain,
                      viewingOrder.format,
                      viewingOrder.order_number
                    )
                  }
                  className="px-3 py-1.5 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 rounded-lg flex items-center space-x-1.5 transition-all cursor-pointer"
                >
                  <Download className="w-3.5 h-3.5" />
                  <span>Download File</span>
                </button>
                <button
                  type="button"
                  onClick={() => copyAccountsToClipboard(viewingOrder.accounts, viewingOrder.id)}
                  className="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-lg flex items-center space-x-1.5 transition-all cursor-pointer"
                >
                  <Copy className="w-3.5 h-3.5" />
                  <span>Copy All</span>
                </button>
              </div>
            </div>

            <div className="p-5 overflow-y-auto flex-grow">
              <div className="overflow-x-auto rounded-xl border border-slate-800">
                <table className="w-full text-left text-xs font-mono">
                  <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                    <tr>
                      <th className="p-3">#</th>
                      <th className="p-3">Email Address</th>
                      <th className="p-3">Password</th>
                      <th className="p-3">Recovery Email</th>
                      <th className="p-3 text-center">Copy</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/60 bg-slate-950/30">
                    {viewingOrder.accounts.map((acc, index) => (
                      <tr key={index} className="hover:bg-slate-900/50">
                        <td className="p-3 text-slate-500">{index + 1}</td>
                        <td className="p-3 font-semibold text-white break-all">{acc.email}</td>
                        <td className="p-3 text-cyan-300">{acc.password}</td>
                        <td className="p-3 text-slate-400">{acc.recovery_email || '—'}</td>
                        <td className="p-3 text-center">
                          <button
                            type="button"
                            onClick={() => {
                              const str = `${acc.email},${acc.password},${acc.recovery_email || ''}`;
                              navigator.clipboard.writeText(str);
                              cyberAlertSuccess('Copied', `${acc.email} copy ho gayi.`);
                            }}
                            className="p-1 hover:bg-slate-800 text-slate-400 hover:text-white rounded transition-colors cursor-pointer"
                            title="Copy single line"
                          >
                            <Copy className="w-3.5 h-3.5" />
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            <div className="p-4 border-t border-slate-800 bg-slate-950/80 flex justify-end">
              <button
                type="button"
                onClick={() => setViewingOrder(null)}
                className="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-mono text-xs font-bold rounded-xl transition-all cursor-pointer"
              >
                Close Window
              </button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL 2: VIEW ALL REPLACED MAILS */}
      {showReplacedModal && (
        <div
          id="modal-view-replaced-mails"
          className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate__animated animate__fadeIn animate__faster"
        >
          <div className="bg-slate-900 border border-amber-500/40 rounded-2xl w-full max-w-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
              <div className="flex items-center space-x-3">
                <RefreshCw className="w-5 h-5 text-amber-400" />
                <div>
                  <h3 className="text-base font-bold text-white font-mono flex items-center space-x-2">
                    <span>Replaced Mails (Faulty Deductions)</span>
                  </h3>
                  <p className="text-[11px] text-slate-400 font-mono mt-0.5">
                    Total: {displayReplacedList.length} Mails &bull; Total Deductions: -Rs. {totalDeductions.toLocaleString()}
                  </p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setShowReplacedModal(false)}
                className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-4 bg-slate-950/40 border-b border-slate-800/80 flex items-center justify-between gap-3 text-xs font-mono">
              <div className="relative flex-1">
                <Search className="w-3.5 h-3.5 text-slate-500 absolute left-3 top-2.5" />
                <input
                  type="text"
                  placeholder="Replaced email search karein..."
                  value={replacedSearchTerm}
                  onChange={(e) => setReplacedSearchTerm(e.target.value)}
                  className="w-full pl-8 pr-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:outline-none focus:border-amber-400"
                />
              </div>
              <div className="text-amber-300 font-bold">
                Rate: -Rs. 18 / mail
              </div>
            </div>

            <div className="p-5 overflow-y-auto flex-grow">
              {filteredReplacedList.length === 0 ? (
                <div className="text-center py-10 text-slate-500 font-mono text-xs">
                  Koi replaced mail nahi mili.
                </div>
              ) : (
                <div className="overflow-x-auto rounded-xl border border-slate-800">
                  <table className="w-full text-left text-xs font-mono">
                    <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                      <tr>
                        <th className="p-3">#</th>
                        <th className="p-3">Email Address</th>
                        <th className="p-3">Domain</th>
                        <th className="p-3 text-right">Deduction</th>
                        <th className="p-3">Reason</th>
                        <th className="p-3">Date</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800/60 bg-slate-950/30">
                      {filteredReplacedList.map((r, index) => (
                        <tr key={r.id || index} className="hover:bg-slate-900/50">
                          <td className="p-3 text-slate-500">{index + 1}</td>
                          <td className="p-3 font-semibold text-white break-all">{r.email}</td>
                          <td className="p-3 text-slate-300">{r.domain}</td>
                          <td className="p-3 text-right text-emerald-400 font-bold">
                            -Rs. {r.rate_deduction || 18}
                          </td>
                          <td className="p-3 text-amber-300/80">{r.reason || 'Faulty Mail'}</td>
                          <td className="p-3 text-slate-400">{formatDateTime(r.created_at)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>

            <div className="p-4 border-t border-slate-800 bg-slate-950/80 flex justify-end">
              <button
                type="button"
                onClick={() => setShowReplacedModal(false)}
                className="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-mono text-xs font-bold rounded-xl transition-all cursor-pointer"
              >
                Close Window
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
