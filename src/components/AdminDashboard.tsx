import React, { useState, useRef } from 'react';
import {
  UploadCloud,
  FileText,
  CheckCircle2,
  Search,
  Filter,
  Layers,
  Copy,
  Check,
  Eye,
  X,
  Download,
  FolderArchive,
  Trash2,
  Server,
  Plus,
  RefreshCw,
  Wallet,
  ArrowDownCircle,
  FileSpreadsheet,
  Clock,
  Edit2,
  AlertCircle,
  RotateCcw,
  Undo2,
  CheckSquare,
  Square,
  Zap,
  Wrench,
  ShieldAlert,
  ShoppingCart,
  Send
} from 'lucide-react';
import {
  EmailAccount,
  OrderRecord,
  ExtractedEmailItem,
  ExportFormat,
  PaymentRecord,
  ReplacementRecord,
  MaintenanceSettings
} from '../types';
import { SAMPLE_CSV_DATA, MANAGED_DOMAINS } from '../data/constants';
import { cyberAlertError, cyberAlertSuccess } from '../utils/cyberSwal';

interface AdminDashboardProps {
  accounts: EmailAccount[];
  orders: OrderRecord[];
  payments: PaymentRecord[];
  replacements: ReplacementRecord[];
  onAccountsUpdate: (updated: EmailAccount[]) => void;
  onOrdersUpdate: (updated: OrderRecord[]) => void;
  onPaymentsUpdate: (updated: PaymentRecord[]) => void;
  onReplacementsUpdate: (updated: ReplacementRecord[]) => void;
  onAddLog: (action: string, details: string) => void;
  maintenance?: MaintenanceSettings;
  onUpdateMaintenance?: (settings: MaintenanceSettings) => void;
  onOpenClientPreview?: () => void;
}

export default function AdminDashboard({
  accounts,
  orders,
  payments,
  replacements,
  onAccountsUpdate,
  onOrdersUpdate,
  onPaymentsUpdate,
  onReplacementsUpdate,
  onAddLog,
  maintenance,
  onUpdateMaintenance,
  onOpenClientPreview
}: AdminDashboardProps) {
  const [activeTab, setActiveTab] = useState<'inventory' | 'orders' | 'payments' | 'replacements' | 'finder'>('inventory');
  const [csvText, setCsvText] = useState('');
  const [selectedFileName, setSelectedFileName] = useState<string | null>(null);
  const [domainFilter, setDomainFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [searchTerm, setSearchTerm] = useState('');
  const [isProcessing, setIsProcessing] = useState(false);
  const [showDeployModal, setShowDeployModal] = useState(false);

  // Modal for inspecting order in Admin
  const [viewingOrder, setViewingOrder] = useState<OrderRecord | null>(null);
  const [copiedOrderId, setCopiedOrderId] = useState<number | null>(null);

  // Ingest/Record Order Modal
  const [showOrderModal, setShowOrderModal] = useState(false);
  const [orderModalNumber, setOrderModalNumber] = useState('');
  const [orderModalDate, setOrderModalDate] = useState('');
  const [orderModalRate, setOrderModalRate] = useState<number>(18);
  const [orderModalNotes, setOrderModalNotes] = useState('');
  const [orderModalDomain, setOrderModalDomain] = useState<string>(MANAGED_DOMAINS[0]);
  const [orderModalCsv, setOrderModalCsv] = useState('');
  const [editingOrderId, setEditingOrderId] = useState<number | null>(null);

  // Stock Allocation Window (Destination Selector: Available vs Sold)
  const [showAllocationModal, setShowAllocationModal] = useState(false);
  const [parsedUploadAccounts, setParsedUploadAccounts] = useState<
    { email: string; password: string; recovery_email: string; domain: string }[]
  >([]);
  const [allocDestination, setAllocDestination] = useState<'available' | 'sold'>('available');
  const [allocOrderNumber, setAllocOrderNumber] = useState('');
  const [allocOrderDate, setAllocOrderDate] = useState('');
  const [allocOrderRate, setAllocOrderRate] = useState<number>(18);
  const [allocOrderNotes, setAllocOrderNotes] = useState('');

  // Record Payment Modal
  const [showPaymentModal, setShowPaymentModal] = useState(false);
  const [paymentModalAmount, setPaymentModalAmount] = useState<number | ''>(5000);
  const [paymentModalDate, setPaymentModalDate] = useState('');
  const [paymentModalMethod, setPaymentModalMethod] = useState('Bank Transfer');
  const [paymentModalNote, setPaymentModalNote] = useState('');
  const [editingPaymentId, setEditingPaymentId] = useState<number | null>(null);

  // Add Replacement Modal
  const [showReplacementModal, setShowReplacementModal] = useState(false);
  const [replacementModalEmails, setReplacementModalEmails] = useState('');
  const [replacementModalRate, setReplacementModalRate] = useState<number>(18);
  const [replacementModalReason, setReplacementModalReason] = useState('Faulty Mail (Password / Disabled)');

  // Single Mail Finder
  const [finderQuery, setFinderQuery] = useState('');
  const [finderResult, setFinderResult] = useState<{
    account: EmailAccount | ExtractedEmailItem;
    domain: string;
    orderNumber?: string;
    date?: string;
  } | null>(null);
  const [finderError, setFinderError] = useState<string | null>(null);
  const [copiedFinderField, setCopiedFinderField] = useState<string | null>(null);

  // Revert & Status Management States
  const [csvTargetStatus, setCsvTargetStatus] = useState<'keep_existing' | 'make_available'>('keep_existing');
  const [revertingOrder, setRevertingOrder] = useState<OrderRecord | null>(null);
  const [revertDeleteOrder, setRevertDeleteOrder] = useState(true);
  const [revertNewPasswordsCsv, setRevertNewPasswordsCsv] = useState('');
  const [editingAccount, setEditingAccount] = useState<EmailAccount | null>(null);
  const [selectedAccountIds, setSelectedAccountIds] = useState<Set<number>>(new Set());

  // Maintenance Mode States
  const [showMaintenanceModal, setShowMaintenanceModal] = useState(false);
  const [maintEnabledInput, setMaintEnabledInput] = useState<boolean>(maintenance?.enabled ?? false);
  const [maintMessageInput, setMaintMessageInput] = useState<string>(
    maintenance?.message ?? 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!'
  );

  const fileInputRef = useRef<HTMLInputElement>(null);

  // Overall metrics
  const totalAccounts = accounts.length;
  const availableCount = accounts.filter((a) => a.status === 'available').length;
  const downloadedCount = accounts.filter((a) => a.status === 'downloaded').length;
  const replacedCount = accounts.filter((a) => a.status === 'replaced').length;

  // Financial Metrics (strictly excluding reverted or cancelled orders)
  const isOrderDelivered = (o: OrderRecord) => {
    return o.status !== 'reverted' && o.status !== 'cancelled' && !o.notes?.includes('[REVERTED TO AVAILABLE STOCK]');
  };

  const activeDeliveredOrders = orders.filter(isOrderDelivered);
  // Live Stock Math: totalDeliveredMails counts downloaded or replaced accounts
  const totalDeliveredMails = accounts.filter((a) => a.status === 'downloaded' || a.status === 'replaced').length;
  const totalReplacedMails = replacements.length > 0 ? replacements.length : replacedCount;
  const netSoldMails = Math.max(0, totalDeliveredMails - totalReplacedMails);
  const netActiveMails = netSoldMails;

  const avgRate = activeDeliveredOrders.length > 0
    ? (activeDeliveredOrders.reduce((sum, o) => sum + (o.rate_per_mail !== undefined ? o.rate_per_mail : 18), 0) / activeDeliveredOrders.length)
    : 18;

  const grossBilledAmount = totalDeliveredMails * avgRate;

  const totalDeductions = replacements.length > 0
    ? replacements.reduce((sum, r) => sum + (r.rate_deduction || 18), 0)
    : (totalReplacedMails * 18);

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

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      setSelectedFileName(file.name);
      const reader = new FileReader();
      reader.onload = (event) => {
        const content = event.target?.result as string;
        setCsvText(content);
      };
      reader.readAsText(file);
    }
  };

  const handleLoadSample = () => {
    setCsvText(SAMPLE_CSV_DATA);
    setSelectedFileName('sample_inventory.csv');
    cyberAlertSuccess('Sample Loaded', '7 sample mails load ho chuki hain.');
  };

  const handleCsvSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!csvText.trim()) {
      cyberAlertError('Khali Data', 'Barah-e-karam CSV file select karein ya text paste karein.');
      return;
    }

    try {
      const lines = csvText.split(/[\r\n]+/).map((l) => l.trim()).filter((l) => l.length > 0);
      if (lines.length === 0) {
        cyberAlertError('No Data', 'CSV me koi valid line nahi mili.');
        return;
      }

      let startIndex = 0;
      const firstLine = lines[0].toLowerCase();
      if (firstLine.includes('email') && firstLine.includes('password')) {
        startIndex = 1;
      }

      const validParsed: { email: string; password: string; recovery_email: string; domain: string }[] = [];
      for (let i = startIndex; i < lines.length; i++) {
        const parts = lines[i].split(',').map((p) => p.trim());
        const email = parts[0] || '';
        const password = parts[1] || '';
        const recovery_email = parts[2] || '';

        if (!email || !email.includes('@') || !password) {
          continue;
        }

        const domain = email.split('@')[1]?.toLowerCase() || 'other';
        validParsed.push({ email, password, recovery_email, domain });
      }

      if (validParsed.length === 0) {
        cyberAlertError('No Valid Accounts', 'CSV me koi valid email,password record nahi mila.');
        return;
      }

      // Open the Allocation Window!
      setParsedUploadAccounts(validParsed);
      setAllocDestination('available');
      setAllocOrderNumber(`Order #${orders.length + 1}`);
      const now = new Date();
      const localDatetime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
      setAllocOrderDate(localDatetime);
      setAllocOrderRate(18);
      setAllocOrderNotes('');
      setShowAllocationModal(true);
    } catch (err) {
      cyberAlertError('Error', 'CSV parse karne me masla pesh aya.');
    }
  };

  const handleConfirmAllocation = () => {
    if (parsedUploadAccounts.length === 0) return;
    setIsProcessing(true);

    try {
      const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);
      const existingMap = new Map<string, EmailAccount>();
      accounts.forEach((acc) => {
        existingMap.set(acc.email.toLowerCase(), acc);
      });

      let workingAccounts = [...accounts];
      const newAccounts: EmailAccount[] = [];
      let updatedCount = 0;
      let nextId = Date.now();

      if (allocDestination === 'available') {
        // INGEST AS AVAILABLE STOCK
        for (const item of parsedUploadAccounts) {
          const emailLower = item.email.toLowerCase();
          const existing = existingMap.get(emailLower);
          if (existing) {
            workingAccounts = workingAccounts.map((acc) => {
              if (acc.email.toLowerCase() === emailLower) {
                return {
                  ...acc,
                  password: item.password,
                  recovery_email: item.recovery_email || acc.recovery_email,
                  status: 'available' as const,
                  downloaded_at: null,
                  replaced_at: null
                };
              }
              return acc;
            });
            updatedCount++;
          } else {
            const newAcc: EmailAccount = {
              id: nextId++,
              email: item.email,
              password: item.password,
              recovery_email: item.recovery_email,
              domain: item.domain,
              status: 'available',
              created_at: nowStr,
              downloaded_at: null,
              replaced_at: null
            };
            newAccounts.push(newAcc);
            existingMap.set(emailLower, newAcc);
          }
        }

        const finalAccounts = [...newAccounts, ...workingAccounts];
        onAccountsUpdate(finalAccounts);
        onAddLog('STOCK_INGESTED', `Admin ne ${parsedUploadAccounts.length} mails 'Available' stock me load ki.`);
        cyberAlertSuccess(
          'Available Stock Me Add Ho Gayi!',
          `<div class="text-xs text-slate-300 space-y-1 font-mono">
            <p class="text-emerald-400 font-bold text-sm">${parsedUploadAccounts.length} Mails Available Stock Me Shamil Hain</p>
            <p class="text-slate-400">Client ab inko portal se extract kar sakta hai.</p>
          </div>`
        );
      } else {
        // INGEST AS SOLD ORDER
        const finalDate = allocOrderDate ? allocOrderDate.replace('T', ' ') + ':00' : nowStr;
        const orderItems: ExtractedEmailItem[] = [];

        for (const item of parsedUploadAccounts) {
          orderItems.push({
            email: item.email,
            password: item.password,
            recovery_email: item.recovery_email
          });

          const emailLower = item.email.toLowerCase();
          const existing = existingMap.get(emailLower);
          if (existing) {
            workingAccounts = workingAccounts.map((acc) => {
              if (acc.email.toLowerCase() === emailLower) {
                return {
                  ...acc,
                  password: item.password,
                  recovery_email: item.recovery_email || acc.recovery_email,
                  status: 'downloaded' as const,
                  downloaded_at: finalDate
                };
              }
              return acc;
            });
            updatedCount++;
          } else {
            const newAcc: EmailAccount = {
              id: nextId++,
              email: item.email,
              password: item.password,
              recovery_email: item.recovery_email,
              domain: item.domain,
              status: 'downloaded',
              created_at: finalDate,
              downloaded_at: finalDate,
              replaced_at: null
            };
            newAccounts.push(newAcc);
            existingMap.set(emailLower, newAcc);
          }
        }

        const primaryDomain = parsedUploadAccounts[0]?.domain || 'basis5.ch';
        const orderRate = Number(allocOrderRate) || 18;
        const orderTotal = parsedUploadAccounts.length * orderRate;
        const orderNum = allocOrderNumber.trim() || `Order #${orders.length + 1}`;

        const newOrder: OrderRecord = {
          id: Date.now(),
          order_number: orderNum,
          client_username: 'rana asim',
          domain: primaryDomain,
          quantity: parsedUploadAccounts.length,
          rate_per_mail: orderRate,
          total_price: orderTotal,
          notes: allocOrderNotes.trim(),
          format: 'csv',
          created_at: finalDate,
          status: 'delivered',
          accounts: orderItems
        };

        const finalAccounts = [...newAccounts, ...workingAccounts];
        onAccountsUpdate(finalAccounts);
        onOrdersUpdate([newOrder, ...orders]);
        onAddLog(
          'ORDER_INGESTED',
          `${orderNum} (${parsedUploadAccounts.length} mails) add hua aur Rs. ${orderTotal.toLocaleString()} Khata me shamil huwe.`
        );
        cyberAlertSuccess(
          'Sold Order Record Ban Gaya!',
          `<div class="text-xs text-slate-300 space-y-1 font-mono">
            <p class="text-cyan-400 font-bold text-sm">${orderNum} (${parsedUploadAccounts.length} Mails) Record Ho Gaya</p>
            <p class="text-emerald-400 font-bold">Total Bill: Rs. ${orderTotal.toLocaleString()} (Rate: Rs. ${orderRate})</p>
            <p class="text-slate-400 text-[11px]">CSV Downloadable hai aur Khata me update ho gaya hai.</p>
          </div>`
        );
      }

      setCsvText('');
      setSelectedFileName(null);
      if (fileInputRef.current) fileInputRef.current.value = '';
      setShowAllocationModal(false);
      setParsedUploadAccounts([]);
    } catch (err) {
      cyberAlertError('Error', 'Ingestion me masla pesh aya.');
    } finally {
      setIsProcessing(false);
    }
  };

  // REVERT / STATUS MANAGEMENT HANDLERS
  const handleSetAccountAvailable = (acc: EmailAccount) => {
    const updated = accounts.map((a) => {
      if (a.id === acc.id || a.email.toLowerCase() === acc.email.toLowerCase()) {
        return {
          ...a,
          status: 'available' as const,
          downloaded_at: null,
          replaced_at: null
        };
      }
      return a;
    });
    onAccountsUpdate(updated);
    onAddLog('ACCOUNT_AVAILABLE', `${acc.email} ko wapis 'available' stock me bhej diya gaya.`);
    cyberAlertSuccess('Status Reverted', `${acc.email} ab 'Available' stock me mojud hai.`);
  };

  const handleDeleteAccount = (acc: EmailAccount) => {
    if (acc.status === 'downloaded') {
      if (!window.confirm(`⚠️ KHABARDAAR!\n\nYeh account "${acc.email}" pehle se SOLD (Downloaded) hai!\n\nIsay delete karne se Sold count me se 1 kam ho jayega aur Total Bill me se Rs. 18 minus ho jayenge.\n\nKya aap waqai isay delete karna chahte hain?`)) {
        return;
      }
    }
    const updated = accounts.filter((a) => a.id !== acc.id && a.email.toLowerCase() !== acc.email.toLowerCase());
    onAccountsUpdate(updated);
    setSelectedAccountIds((prev) => {
      const next = new Set(prev);
      next.delete(acc.id);
      return next;
    });
    onAddLog('ACCOUNT_DELETED', `${acc.email} stock se delete kar diya gaya.`);
    cyberAlertSuccess('Deleted', `${acc.email} stock se remove ho gaya.`);
  };

  const handleSaveEditAccount = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingAccount) return;
    if (!editingAccount.email.trim() || !editingAccount.password.trim()) {
      cyberAlertError('Missing Fields', 'Email aur Password lazmi hain.');
      return;
    }

    const updated = accounts.map((a) => {
      if (a.id === editingAccount.id) {
        return {
          ...editingAccount,
          downloaded_at: editingAccount.status === 'available' ? null : a.downloaded_at,
          replaced_at: editingAccount.status === 'available' ? null : a.replaced_at
        };
      }
      return a;
    });

    onAccountsUpdate(updated);
    onAddLog('ACCOUNT_EDITED', `Account ${editingAccount.email} ke credentials/status update kiye gaye.`);
    cyberAlertSuccess('Account Updated', `${editingAccount.email} update ho gaya.`);
    setEditingAccount(null);
  };

  const handleBulkMakeAvailable = (targetList: EmailAccount[]) => {
    if (targetList.length === 0) return;
    if (window.confirm(`Kya aap waqai in تمام ${targetList.length} accounts ko 'Available' stock me dalna chahte hain?`)) {
      const targetSet = new Set(targetList.map((a) => a.email.toLowerCase()));
      const updated = accounts.map((a) => {
        if (targetSet.has(a.email.toLowerCase())) {
          return {
            ...a,
            status: 'available' as const,
            downloaded_at: null,
            replaced_at: null
          };
        }
        return a;
      });
      onAccountsUpdate(updated);
      setSelectedAccountIds(new Set());
      onAddLog('BULK_AVAILABLE', `Admin ne ${targetList.length} accounts ko 'Available' stock me revert kar diya.`);
      cyberAlertSuccess('Bulk Update Complete', `${targetList.length} accounts ab 'Available' stock me shamil hain.`);
    }
  };

  const handleBulkDelete = (targetList: EmailAccount[]) => {
    if (targetList.length === 0) return;
    if (window.confirm(`Khabardaar! Kya aap waqai in ${targetList.length} accounts ko delete karna chahte hain?`)) {
      const targetSet = new Set(targetList.map((a) => a.email.toLowerCase()));
      const updated = accounts.filter((a) => !targetSet.has(a.email.toLowerCase()));
      onAccountsUpdate(updated);
      setSelectedAccountIds(new Set());
      onAddLog('BULK_DELETE', `Admin ne ${targetList.length} accounts delete kar diye.`);
      cyberAlertSuccess('Bulk Delete Complete', `${targetList.length} accounts delete ho gaye.`);
    }
  };

  const toggleSelectAccount = (id: number) => {
    setSelectedAccountIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  };

  const toggleSelectAllFiltered = () => {
    const visibleIds = filteredAccounts.slice(0, 100).map((a) => a.id);
    const allSelected = visibleIds.length > 0 && visibleIds.every((id) => selectedAccountIds.has(id));
    setSelectedAccountIds((prev) => {
      const next = new Set(prev);
      if (allSelected) {
        visibleIds.forEach((id) => next.delete(id));
      } else {
        visibleIds.forEach((id) => next.add(id));
      }
      return next;
    });
  };

  // REVERT ORDER TO STOCK HANDLERS
  const openRevertOrderModal = (order: OrderRecord) => {
    setRevertingOrder(order);
    setRevertDeleteOrder(true);
    setRevertNewPasswordsCsv('');
  };

  const handleConfirmRevertOrder = (e: React.FormEvent) => {
    e.preventDefault();
    if (!revertingOrder) return;

    const order = revertingOrder;
    const orderEmails = new Set(order.accounts.map((a) => a.email.toLowerCase()));

    // Parse optional new passwords CSV
    const customPasswordMap = new Map<string, { pass: string; rec?: string }>();
    if (revertNewPasswordsCsv.trim()) {
      const pLines = revertNewPasswordsCsv.split(/[\r\n]+/).map((l) => l.trim()).filter((l) => l.length > 0);
      let pStart = 0;
      if (pLines[0].toLowerCase().includes('email') && pLines[0].toLowerCase().includes('password')) {
        pStart = 1;
      }
      for (let i = pStart; i < pLines.length; i++) {
        const parts = pLines[i].split(',').map((p) => p.trim());
        if (parts[0] && parts[1]) {
          customPasswordMap.set(parts[0].toLowerCase(), {
            pass: parts[1],
            rec: parts[2] || undefined
          });
        }
      }
    }

    // Update matching accounts back to 'available'
    const updatedAccounts = accounts.map((acc) => {
      if (orderEmails.has(acc.email.toLowerCase())) {
        const custom = customPasswordMap.get(acc.email.toLowerCase());
        return {
          ...acc,
          password: custom ? custom.pass : acc.password,
          recovery_email: (custom && custom.rec) ? custom.rec : acc.recovery_email,
          status: 'available' as const,
          downloaded_at: null,
          replaced_at: null
        };
      }
      return acc;
    });

    onAccountsUpdate(updatedAccounts);

    // If revertDeleteOrder is checked, remove order from Khata
    if (revertDeleteOrder) {
      onOrdersUpdate(orders.filter((o) => o.id !== order.id));
      onAddLog('ORDER_REVERTED_DELETED', `Order ${order.order_number} (${order.quantity} mails) revert karke Khata se delete kar diya gaya.`);
    } else {
      onOrdersUpdate(
        orders.map((o) => (o.id === order.id ? { ...o, notes: (o.notes ? o.notes + ' ' : '') + '[REVERTED TO AVAILABLE STOCK]' } : o))
      );
      onAddLog('ORDER_REVERTED_STOCK', `Order ${order.order_number} (${order.quantity} mails) wapis Available stock me move kar di gayi.`);
    }

    cyberAlertSuccess(
      'Mails Wapis Stock Me Shamil!',
      `
      <div class="text-xs text-slate-300 space-y-1 text-center font-mono">
        <p class="text-emerald-400 font-bold text-sm">${order.quantity} Mails Available Stock Me Chali Gayi Hain</p>
        <p class="text-slate-400 text-[11px]">${revertDeleteOrder ? 'Order Khata aur Bill se delete kar diya gaya.' : 'Order marked as reverted.'}</p>
        ${customPasswordMap.size > 0 ? `<p class="text-cyan-400 text-[11px]">${customPasswordMap.size} mails ke naye passwords bhi update ho gaye.</p>` : ''}
      </div>
      `
    );

    setRevertingOrder(null);
  };

  const handleClearAllAccounts = () => {
    if (accounts.length === 0) {
      cyberAlertError('Stock Pehle Se Khali Hai', 'Filhal koi mail mojud nahi hai.');
      return;
    }
    if (window.confirm('Kya aap waqai tamam mails ka stock delete karna chahte hain? Tamam loaded mails remove ho jayengi.')) {
      onAccountsUpdate([]);
      onAddLog('STOCK_PURGED', 'Admin ne tamam mails ka stock delete kar diya.');
      cyberAlertSuccess('Stock Khali Kar Diya Gaya', 'Tamam mails delete ho chuki hain aur inventory zero ho gayi hai.');
    }
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
    cyberAlertSuccess('Mails Copy Ho Gayi!', 'Tamam mails clipboard par copy ho chuki hain.');
  };

  // Helper to download order file
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

  // ORDER INGESTION (WhatsApp or manual order)
  const openNewOrderModal = () => {
    setEditingOrderId(null);
    setOrderModalNumber(`Order #${orders.length + 1}`);
    const now = new Date();
    setOrderModalDate(now.toISOString().substring(0, 16));
    setOrderModalRate(18);
    setOrderModalNotes('');
    setOrderModalDomain(MANAGED_DOMAINS[0]);
    setOrderModalCsv('');
    setShowOrderModal(true);
  };

  const handleSaveOrder = (e: React.FormEvent) => {
    e.preventDefault();
    if (!orderModalNumber.trim()) {
      cyberAlertError('Missing', 'Order Number likhein (e.g. Order #1).');
      return;
    }

    if (editingOrderId) {
      // Edit existing order metadata
      const updated = orders.map((o) => {
        if (o.id === editingOrderId) {
          const rate = orderModalRate || 18;
          return {
            ...o,
            order_number: orderModalNumber.trim(),
            created_at: orderModalDate || o.created_at,
            rate_per_mail: rate,
            total_price: o.quantity * rate,
            notes: orderModalNotes
          };
        }
        return o;
      });
      onOrdersUpdate(updated);
      onAddLog('ORDER_UPDATED', `Admin ne order update kiya: ${orderModalNumber}`);
      cyberAlertSuccess('Order Updated', `${orderModalNumber} update ho chuka hai.`);
      setShowOrderModal(false);
      return;
    }

    // New Ingestion
    if (!orderModalCsv.trim()) {
      cyberAlertError('Missing CSV', 'Barah-e-karam mails ka data paste karein.');
      return;
    }

    const lines = orderModalCsv.split(/[\r\n]+/).map((l) => l.trim()).filter((l) => l.length > 0);
    let startIndex = 0;
    if (lines[0].toLowerCase().includes('email') && lines[0].toLowerCase().includes('password')) {
      startIndex = 1;
    }

    const items: ExtractedEmailItem[] = [];
    const accountsToAdd: EmailAccount[] = [];
    let nextId = Date.now();
    const dateStr = orderModalDate ? orderModalDate.replace('T', ' ') + ':00' : new Date().toISOString().replace('T', ' ').substring(0, 19);

    for (let i = startIndex; i < lines.length; i++) {
      const parts = lines[i].split(',').map((p) => p.trim());
      const email = parts[0];
      const password = parts[1];
      const rec = parts[2] || '';

      if (email && email.includes('@') && password) {
        items.push({ email, password, recovery_email: rec });
        const dom = email.split('@')[1]?.toLowerCase() || orderModalDomain;
        accountsToAdd.push({
          id: nextId++,
          email,
          password,
          recovery_email: rec,
          domain: dom,
          status: 'downloaded',
          created_at: dateStr,
          downloaded_at: dateStr
        });
      }
    }

    if (items.length === 0) {
      cyberAlertError('No Valid Mails', 'Koi valid email,password line nahi mili.');
      return;
    }

    const qty = items.length;
    const rate = orderModalRate || 18;
    const newOrd: OrderRecord = {
      id: Date.now(),
      order_number: orderModalNumber.trim(),
      client_username: 'rana asim',
      domain: orderModalDomain,
      quantity: qty,
      rate_per_mail: rate,
      total_price: qty * rate,
      notes: orderModalNotes,
      format: 'csv',
      created_at: dateStr,
      accounts: items
    };

    onOrdersUpdate([newOrd, ...orders]);
    onAccountsUpdate([...accountsToAdd, ...accounts]);
    onAddLog('ORDER_INGESTED', `Order Ingest kiya: ${newOrd.order_number} (${qty} mails)`);
    cyberAlertSuccess('Order Ingested', `${newOrd.order_number} kamyabi se Khata me add ho gaya.`);
    setShowOrderModal(false);
  };

  const handleDeleteOrder = (orderId: number, orderNum: string) => {
    if (window.confirm(`Kya aap waqai "${orderNum}" ko delete karna chahte hain? Is order ki tamam mails automatically wapis Available stock me move ho jayengi aur order ledger se hat jayega.`)) {
      const orderToDelete = orders.find((o) => o.id === orderId);
      if (orderToDelete && orderToDelete.accounts && orderToDelete.accounts.length > 0) {
        const orderEmailSet = new Set(orderToDelete.accounts.map((a) => a.email.toLowerCase().trim()));
        onAccountsUpdate(
          accounts.map((acc) => {
            if (orderEmailSet.has(acc.email.toLowerCase().trim()) && acc.status === 'downloaded') {
              return { ...acc, status: 'available' };
            }
            return acc;
          })
        );
      }
      onOrdersUpdate(orders.filter((o) => o.id !== orderId));
      onAddLog('ORDER_DELETED', `Admin ne ${orderNum} delete kar diya aur mails wapis Available stock me move kar di.`);
      cyberAlertSuccess('Order Deleted', `${orderNum} delete ho gaya hai aur iski mails wapis Available stock me shamil ho gayi hain.`);
    }
  };

  const handleQuickUpdateRate = (orderId: number, orderNum: string, currentRate: number, quantity: number) => {
    const input = prompt(`Order "${orderNum}" (${quantity} mails) ke liye naya rate (PKR per mail) darj karein:`, currentRate.toString());
    if (input === null) return;
    const newRate = parseFloat(input);
    if (isNaN(newRate) || newRate < 0) {
      cyberAlertError('Invalid Rate', 'Barah-e-karam durust rate likhein.');
      return;
    }
    const newTotal = Math.round(newRate * quantity);
    onOrdersUpdate(
      orders.map((o) => (o.id === orderId ? { ...o, rate_per_mail: newRate, total_price: newTotal } : o))
    );
    onAddLog('ORDER_PRICE_UPDATED', `Admin ne ${orderNum} ka rate Rs. ${newRate} (Total: Rs. ${newTotal}) update kiya.`);
    cyberAlertSuccess('Price Updated', `${orderNum} ka rate Rs. ${newRate} aur total Rs. ${newTotal.toLocaleString()} update ho gaya.`);
  };

  const handleToggleOrderStatus = (orderId: number, newStatus: 'delivered' | 'reverted') => {
    const targetOrder = orders.find((o) => o.id === orderId);
    if (!targetOrder) return;
    const isReverting = newStatus === 'reverted';
    const orderNum = targetOrder.order_number || `Order #${orderId}`;
    const msg = isReverting
      ? `Kya aap "${orderNum}" ki ${targetOrder.quantity} mails ko wapis Available stock me shift karna chahte hain aur Khata / Total Bill kam karna chahte hain?`
      : `Kya aap "${orderNum}" ko Delivered (Sold) mark karna chahte hain aur Khata / Bill me shamil karna chahte hain?`;

    if (window.confirm(msg)) {
      // 1. Update orders
      onOrdersUpdate(
        orders.map((o) => {
          if (o.id === orderId) {
            let notes = o.notes || '';
            if (isReverting) {
              if (!notes.includes('[REVERTED TO AVAILABLE STOCK]')) {
                notes = (notes ? notes + ' ' : '') + '[REVERTED TO AVAILABLE STOCK]';
              }
            } else {
              notes = notes.replace('[REVERTED TO AVAILABLE STOCK]', '').trim();
            }
            return {
              ...o,
              status: newStatus,
              notes
            };
          }
          return o;
        })
      );

      // 2. Update accounts
      if (targetOrder.accounts && targetOrder.accounts.length > 0) {
        const emailSet = new Set(targetOrder.accounts.map((a) => a.email.toLowerCase().trim()));
        onAccountsUpdate(
          accounts.map((acc) => {
            if (emailSet.has(acc.email.toLowerCase().trim())) {
              return {
                ...acc,
                status: isReverting ? ('available' as const) : ('downloaded' as const),
                downloaded_at: isReverting ? null : (acc.downloaded_at || new Date().toISOString())
              };
            }
            return acc;
          })
        );
      }

      onAddLog(
        isReverting ? 'ORDER_REVERTED' : 'ORDER_DELIVERED',
        `Admin ne ${orderNum} status "${newStatus}" kiya. ${targetOrder.quantity} accounts update huwe.`
      );
      cyberAlertSuccess(
        isReverting ? 'Order Reverted!' : 'Order Delivered!',
        isReverting
          ? `${orderNum} revert ho gaya: ${targetOrder.quantity} mails Available stock me wapis aa gayin aur Bill kam ho gaya!`
          : `${orderNum} deliver ho gaya: ${targetOrder.quantity} mails Downloaded me shift ho gayin aur Bill me shamil ho gaya.`
      );
    }
  };

  const handleSyncStockAndOrders = () => {
    // 1. Find all emails in active delivered orders
    const activeDelivered = orders.filter(
      (o) => o.status !== 'reverted' && o.status !== 'cancelled' && !o.notes?.includes('[REVERTED TO AVAILABLE STOCK]')
    );
    const deliveredEmails = new Set<string>();
    activeDelivered.forEach((o) => {
      (o.accounts || []).forEach((a) => deliveredEmails.add(a.email.toLowerCase().trim()));
    });

    let fixedCount = 0;
    const updatedAccounts = accounts.map((acc) => {
      if (acc.status === 'downloaded' && !deliveredEmails.has(acc.email.toLowerCase().trim())) {
        fixedCount++;
        return {
          ...acc,
          status: 'available' as const,
          downloaded_at: null
        };
      }
      return acc;
    });

    onAccountsUpdate(updatedAccounts);
    onAddLog('SYNC_STOCK_ORDERS', `Stock aur Khata sync kiya gaya: ${fixedCount} stranded downloaded mails wapis Available stock me shamil ki gayin.`);
    cyberAlertSuccess(
      'Stock & Khata Synced!',
      `${fixedCount} stranded downloaded mails wapis Available stock me bhej di gayin aur Bill theek ho gaya!`
    );
  };

  const handleRevertAllDownloaded = () => {
    const downloadedAccounts = accounts.filter((a) => a.status === 'downloaded');
    if (downloadedAccounts.length === 0) {
      cyberAlertError('Koi Downloaded Mails Nahi Hain', 'Filhal koi mail downloaded status me nahi hai.');
      return;
    }
    if (window.confirm(`Kya aap tamam downloaded mails (${downloadedAccounts.length} accounts) ko wapis Available stock me shift karna chahte hain? Tamam orders bhi revert ho kar Bill kam ho jayega.`)) {
      onAccountsUpdate(
        accounts.map((a) => (a.status === 'downloaded' ? { ...a, status: 'available', downloaded_at: null } : a))
      );
      onOrdersUpdate(
        orders.map((o) => {
          let notes = o.notes || '';
          if (!notes.includes('[REVERTED TO AVAILABLE STOCK]')) {
            notes = (notes ? notes + ' ' : '') + '[REVERTED TO AVAILABLE STOCK]';
          }
          return { ...o, status: 'reverted' as const, notes };
        })
      );
      onAddLog('ACCOUNTS_REVERTED', `Admin ne tamam downloaded mails (${downloadedAccounts.length}) ko wapis Available stock me move kar diya aur orders revert kar diye.`);
      cyberAlertSuccess('Stock & Bill Updated', `Tamam ${downloadedAccounts.length} downloaded accounts wapis Available stock me shamil ho chuke hain aur Bill kam ho gaya hai!`);
    }
  };

  // PAYMENT MANAGEMENT
  const openNewPaymentModal = () => {
    setEditingPaymentId(null);
    setPaymentModalAmount(5000);
    const now = new Date();
    setPaymentModalDate(now.toISOString().substring(0, 10));
    setPaymentModalMethod('Bank Transfer');
    setPaymentModalNote('');
    setShowPaymentModal(true);
  };

  const handleSavePayment = (e: React.FormEvent) => {
    e.preventDefault();
    const amt = typeof paymentModalAmount === 'number' ? paymentModalAmount : parseFloat(paymentModalAmount);
    if (!amt || amt <= 0) {
      cyberAlertError('Invalid Amount', 'Barah-e-karam durust amount likhein.');
      return;
    }

    if (editingPaymentId) {
      const updated = payments.map((p) => {
        if (p.id === editingPaymentId) {
          return {
            ...p,
            amount: amt,
            payment_date: paymentModalDate || p.payment_date,
            payment_method: paymentModalMethod,
            reference_note: paymentModalNote
          };
        }
        return p;
      });
      onPaymentsUpdate(updated);
      onAddLog('PAYMENT_UPDATED', `Payment update hui: Rs. ${amt}`);
      cyberAlertSuccess('Payment Updated', `Payment Rs. ${amt} update ho gayi.`);
      setShowPaymentModal(false);
      return;
    }

    const newPayment: PaymentRecord = {
      id: Date.now(),
      client_username: 'rana asim',
      amount: amt,
      payment_date: paymentModalDate || new Date().toISOString().substring(0, 10),
      payment_method: paymentModalMethod,
      reference_note: paymentModalNote,
      created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
    };

    onPaymentsUpdate([newPayment, ...payments]);
    onAddLog('PAYMENT_RECORDED', `Payment record hui: Rs. ${amt} (${paymentModalMethod})`);
    cyberAlertSuccess('Payment Recorded', `Rs. ${amt} wasooli ledger me add ho gayi.`);
    setShowPaymentModal(false);
  };

  const handleDeletePayment = (payId: number) => {
    if (window.confirm('Kya aap is payment record ko delete karna chahte hain?')) {
      onPaymentsUpdate(payments.filter((p) => p.id !== payId));
      onAddLog('PAYMENT_DELETED', `Payment ID ${payId} delete kar di gayi.`);
      cyberAlertSuccess('Payment Deleted', 'Payment record remove kar diya gaya.');
    }
  };

  // REPLACEMENT MANAGEMENT
  const handleSaveReplacement = (e: React.FormEvent) => {
    e.preventDefault();
    if (!replacementModalEmails.trim()) {
      cyberAlertError('Khali Data', 'Kharab mails yahan paste karein.');
      return;
    }

    const rawList = replacementModalEmails
      .split(/[\r\n,]+/)
      .map((m) => m.trim().toLowerCase())
      .filter((m) => m.length > 0 && m.includes('@'));

    const unique: string[] = Array.from(new Set(rawList));
    if (unique.length === 0) {
      cyberAlertError('Invalid Format', 'Koi valid email nahi mili.');
      return;
    }

    const rate = replacementModalRate || 18;
    const nowStr = new Date().toISOString().replace('T', ' ').substring(0, 19);
    let nextId = Date.now();

    const newReps: ReplacementRecord[] = unique.map((em: string) => ({
      id: nextId++,
      email: em,
      domain: em.split('@')[1] || 'Unknown',
      rate_deduction: rate,
      reason: replacementModalReason,
      created_at: nowStr
    }));

    // Also update accounts status to 'replaced'
    const emailSet = new Set(unique);
    const updatedAccounts = accounts.map((a) => {
      if (emailSet.has(a.email.toLowerCase())) {
        return {
          ...a,
          status: 'replaced' as const,
          replaced_at: nowStr
        };
      }
      return a;
    });

    onAccountsUpdate(updatedAccounts);
    onReplacementsUpdate([...newReps, ...replacements]);
    onAddLog('REPLACEMENTS_ADDED', `Admin ne ${unique.length} faulty mails deduct ki.`);
    cyberAlertSuccess('Replacements Recorded', `${unique.length} mails deduct ho gayi hain aur -Rs. ${unique.length * rate} bill se minus ho gaya.`);
    setShowReplacementModal(false);
    setReplacementModalEmails('');
  };

  const handleDeleteReplacement = (repId: number) => {
    if (window.confirm('Kya aap is replacement deduction ko delete karna chahte hain?')) {
      onReplacementsUpdate(replacements.filter((r) => r.id !== repId));
      onAddLog('REPLACEMENT_DELETED', `Replacement ID ${repId} delete ho gaya.`);
      cyberAlertSuccess('Deleted', 'Replacement deduction remove ho gayi.');
    }
  };

  // SINGLE MAIL FINDER
  const handleFinderSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setFinderError(null);
    setFinderResult(null);

    const q = finderQuery.trim().toLowerCase();
    if (!q) {
      setFinderError('Barah-e-karam email address likhein.');
      return;
    }

    for (const ord of orders) {
      if (ord.accounts) {
        const found = ord.accounts.find((a) => a.email.toLowerCase() === q);
        if (found) {
          setFinderResult({
            account: found,
            domain: ord.domain || found.email.split('@')[1] || 'Unknown',
            orderNumber: ord.order_number,
            date: ord.created_at
          });
          return;
        }
      }
    }

    const acc = accounts.find((a) => a.email.toLowerCase() === q);
    if (acc) {
      setFinderResult({
        account: acc,
        domain: acc.domain,
        date: acc.downloaded_at || acc.created_at
      });
      return;
    }

    setFinderError(`"${q}" kisi bhi order ya stock me nahi mila.`);
  };

  const copyFinderText = (text: string, key: string) => {
    navigator.clipboard.writeText(text);
    setCopiedFinderField(key);
    setTimeout(() => setCopiedFinderField(null), 2000);
  };

  // Download Statement CSV
  const handleDownloadStatement = () => {
    const lines: string[] = [];
    lines.push('HADI DIGITAL - MASTER FINANCIAL & KHATA LEDGER');
    lines.push(`Client: Rana Asim,Date: ${new Date().toLocaleString()}`);
    lines.push('');
    lines.push('--- FINANCIAL SUMMARY ---');
    lines.push(`Total Delivered Mails,${totalDeliveredMails}`);
    lines.push(`Faulty Mails Replaced,-${totalReplacedMails}`);
    lines.push(`Total Billable Mails,${netActiveMails}`);
    lines.push(`Total Bill Amount (PKR),Rs. ${Math.round(netBilledAmount)}`);
    lines.push(`Total Paid Amount (Wasooli PKR),Rs. ${Math.round(totalPaidAmount)}`);
    lines.push(`Pending Balance (Baqaya PKR),Rs. ${Math.round(pendingBalance)}`);
    lines.push('');
    lines.push('--- ORDERS & DELIVERY HISTORY ---');
    lines.push('Order Number,Date,Domain,Quantity,Rate (PKR),Total Amount (PKR)');
    orders.forEach((o) => {
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
    link.setAttribute('download', `Hadi_Digital_Master_Statement_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };

  const filteredAccounts = accounts.filter((acc) => {
    const matchesDomain = domainFilter ? acc.domain.toLowerCase() === domainFilter.toLowerCase() : true;
    const matchesStatus = statusFilter ? acc.status === statusFilter : true;
    const matchesSearch = searchTerm
      ? acc.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
        acc.recovery_email.toLowerCase().includes(searchTerm.toLowerCase())
      : true;
    return matchesDomain && matchesStatus && matchesSearch;
  });

  return (
    <div id="admin-dashboard-container" className="space-y-8 animate__animated animate__fadeIn">
      {/* 1. ADMIN WELCOME BANNER */}
      <div
        id="admin-banner-header"
        className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xl"
      >
        <div>
          <div className="flex items-center space-x-2 mb-1">
            <span className="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse" />
            <span className="text-xs font-mono uppercase tracking-wider text-cyan-400">
              Admin Master Control Hub
            </span>
          </div>
          <h2 className="text-2xl font-bold text-white tracking-tight">Hadi Digital - Admin Panel</h2>
          <p className="text-xs text-slate-400 mt-0.5">
            Manage stock allocation, order deliveries, payments wasooli, and faulty deductions.
          </p>
        </div>

        {/* Quick Actions */}
        <div className="flex flex-wrap items-center gap-2">
          {/* Client Area Maintenance Control Button */}
          {maintenance && (
            <button
              type="button"
              onClick={() => {
                setMaintEnabledInput(maintenance.enabled);
                setMaintMessageInput(maintenance.message);
                setShowMaintenanceModal(true);
              }}
              className={`px-3 py-2 rounded-xl border text-xs font-mono flex items-center space-x-2 transition-all cursor-pointer shadow-sm ${
                maintenance.enabled
                  ? 'bg-amber-950/70 border-amber-500/60 text-amber-300 hover:bg-amber-900/80 animate-pulse'
                  : 'bg-emerald-950/40 border-emerald-500/40 text-emerald-400 hover:bg-emerald-900/60'
              }`}
              title="Click to manage Client Portal Maintenance Mode"
            >
              <Wrench className="w-3.5 h-3.5" />
              <span>{maintenance.enabled ? 'Client Portal: Maintenance' : 'Client Portal: Live'}</span>
            </button>
          )}

          {/* Test Client View Button */}
          {onOpenClientPreview && (
            <button
              type="button"
              onClick={onOpenClientPreview}
              className="px-3 py-2 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 border border-indigo-500/40 text-indigo-300 text-xs font-mono flex items-center space-x-1.5 transition-all cursor-pointer shadow-sm"
              title="Test Client Area directly with Admin privileges"
            >
              <Eye className="w-3.5 h-3.5 text-indigo-400" />
              <span>Test Client View</span>
            </button>
          )}

          <button
            type="button"
            onClick={handleDownloadStatement}
            className="px-3 py-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-mono flex items-center space-x-1.5 transition-all cursor-pointer shadow-sm"
          >
            <FileSpreadsheet className="w-3.5 h-3.5" />
            <span>Download Statement</span>
          </button>

          <button
            type="button"
            onClick={() => setShowDeployModal(true)}
            className="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-cyan-300 text-xs font-mono flex items-center space-x-1.5 transition-all cursor-pointer shadow-sm"
          >
            <Server className="w-3.5 h-3.5 text-cyan-400" />
            <span>Hostinger Deploy Guide</span>
          </button>

          <a
            href="/hadi_digital_hostinger.zip"
            download="hadi_digital_hostinger.zip"
            className="px-3 py-2 rounded-xl bg-cyan-950/60 hover:bg-cyan-900/80 border border-cyan-500/40 text-cyan-300 text-xs font-mono flex items-center space-x-1.5 transition-all cursor-pointer"
          >
            <FolderArchive className="w-3.5 h-3.5 text-cyan-400" />
            <span>Hostinger ZIP</span>
          </a>
        </div>
      </div>

      {/* Maintenance Mode Notice Banner for Admin */}
      {maintenance?.enabled && (
        <div className="p-4 bg-amber-950/70 border border-amber-500/60 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-mono shadow-xl backdrop-blur-xl">
          <div className="flex items-center space-x-2.5 text-amber-300">
            <span className="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping flex-shrink-0" />
            <div>
              <strong className="text-white">CLIENT PORTAL UNDER MAINTENANCE:</strong>{' '}
              <span className="text-amber-200">
                Aam clients ko maintenance screen nazar aa rahi hai. Aap "Test Client View" se portal test kar sakte hain ya wapis LIVE kar sakte hain.
              </span>
            </div>
          </div>
          <div className="flex items-center space-x-2 flex-shrink-0">
            {onOpenClientPreview && (
              <button
                type="button"
                onClick={onOpenClientPreview}
                className="px-3.5 py-1.5 bg-indigo-500 hover:bg-indigo-400 text-white font-bold rounded-xl transition-all shadow-md cursor-pointer flex items-center space-x-1.5"
              >
                <Eye className="w-3.5 h-3.5" />
                <span>Test Client View</span>
              </button>
            )}
            <button
              type="button"
              onClick={() => {
                if (onUpdateMaintenance) {
                  onUpdateMaintenance({ enabled: false, message: maintenance.message });
                  onAddLog('MAINTENANCE_DISABLED', 'Admin ne Client Portal Maintenance Mode band kar diya (Portal Live).');
                  cyberAlertSuccess('Portal Live Ho Gaya', 'Client portal ab live hai aur clients access kar sakte hain.');
                }
              }}
              className="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl transition-all shadow-md cursor-pointer"
            >
              🟢 Live Kar Dein
            </button>
          </div>
        </div>
      )}

      {/* 2. MASTER FINANCIAL & KHATA SUMMARY CARDS (6 Cards Grid) */}
      <div id="admin-khata-ledger" className="space-y-3">
        <div className="flex items-center justify-between">
          <h3 className="text-xs font-mono text-slate-400 uppercase tracking-widest flex items-center space-x-2">
            <Wallet className="w-4 h-4 text-cyan-400" />
            <span>Financial Summary &amp; Ledger Balance (Client: Rana Asim)</span>
          </h3>
          <span className="text-[11px] font-mono text-slate-500">Live Real-Time Khata</span>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
          <div className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-4 flex flex-col justify-between shadow-md">
            <div className="flex items-center justify-between text-slate-400 mb-1">
              <span className="text-[11px] font-mono uppercase tracking-wider">Total Delivered</span>
              <Layers className="w-4 h-4 text-slate-400" />
            </div>
            <div>
              <div className="text-2xl font-bold font-mono text-white tracking-tight">
                {totalDeliveredMails.toLocaleString()}
              </div>
              <div className="text-[10px] text-slate-500 font-mono mt-0.5">All orders total</div>
            </div>
          </div>

          <div className="bg-slate-900/80 backdrop-blur-xl border border-amber-500/30 rounded-2xl p-4 flex flex-col justify-between shadow-md">
            <div className="flex items-center justify-between text-amber-400 mb-1">
              <span className="text-[11px] font-mono uppercase tracking-wider">Faulty Replaced</span>
              <RefreshCw className="w-4 h-4 text-amber-400" />
            </div>
            <div>
              <div className="text-2xl font-bold font-mono text-amber-300 tracking-tight">
                -{totalReplacedMails.toLocaleString()}
              </div>
              <div className="text-[10px] text-amber-400/70 font-mono mt-0.5">Deducted from bill</div>
            </div>
          </div>

          <div className="bg-slate-900/80 backdrop-blur-xl border border-emerald-500/30 rounded-2xl p-4 flex flex-col justify-between shadow-md">
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

          <div className="bg-slate-900/80 backdrop-blur-xl border border-cyan-500/30 rounded-2xl p-4 flex flex-col justify-between shadow-md">
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

          <div className="bg-slate-900/80 backdrop-blur-xl border border-indigo-500/30 rounded-2xl p-4 flex flex-col justify-between shadow-md">
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

          <div
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

      {/* 3. NAVIGATION TABS */}
      <div className="flex items-center space-x-2 bg-slate-900/80 p-2 rounded-2xl border border-slate-800 overflow-x-auto">
        <button
          type="button"
          onClick={() => setActiveTab('inventory')}
          className={`px-4 py-2 rounded-xl text-xs font-mono font-bold transition-all cursor-pointer whitespace-nowrap ${
            activeTab === 'inventory'
              ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20'
              : 'text-slate-400 hover:text-white'
          }`}
        >
          Mails Stock ({totalAccounts})
        </button>

        <button
          type="button"
          onClick={() => setActiveTab('orders')}
          className={`px-4 py-2 rounded-xl text-xs font-mono font-bold transition-all cursor-pointer whitespace-nowrap ${
            activeTab === 'orders'
              ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20'
              : 'text-slate-400 hover:text-white'
          }`}
        >
          Orders Console &amp; Khata ({orders.length})
        </button>

        <button
          type="button"
          onClick={() => setActiveTab('payments')}
          className={`px-4 py-2 rounded-xl text-xs font-mono font-bold transition-all cursor-pointer whitespace-nowrap ${
            activeTab === 'payments'
              ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20'
              : 'text-slate-400 hover:text-white'
          }`}
        >
          Wasooli Tracker ({payments.length})
        </button>

        <button
          type="button"
          onClick={() => setActiveTab('replacements')}
          className={`px-4 py-2 rounded-xl text-xs font-mono font-bold transition-all cursor-pointer whitespace-nowrap ${
            activeTab === 'replacements'
              ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
              : 'text-slate-400 hover:text-white'
          }`}
        >
          Faulty Deductions ({totalReplacedMails})
        </button>

        <button
          type="button"
          onClick={() => setActiveTab('finder')}
          className={`px-4 py-2 rounded-xl text-xs font-mono font-bold transition-all cursor-pointer whitespace-nowrap ${
            activeTab === 'finder'
              ? 'bg-cyan-500 text-slate-950 shadow-md shadow-cyan-500/20'
              : 'text-slate-400 hover:text-white'
          }`}
        >
          Mail Credential Finder
        </button>
      </div>

      {/* TAB 1: INVENTORY & STOCK */}
      {activeTab === 'inventory' && (
        <div className="space-y-6">
          {/* CSV INGESTION / UPLOAD */}
          <div
            id="card-csv-ingestion"
            className="bg-slate-900/80 backdrop-blur-2xl border border-slate-800 rounded-2xl p-6 lg:p-8 shadow-xl"
          >
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-4 border-b border-slate-800">
              <div>
                <h3 className="text-lg font-bold text-white flex items-center space-x-2">
                  <UploadCloud className="w-5 h-5 text-cyan-400" />
                  <span>Fresh Mails Load Karein (Stock Ingestion)</span>
                </h3>
                <p className="text-xs text-slate-400 mt-1">
                  Format me 3 columns hon bina space: <b className="text-cyan-300 font-mono">email,password,recovery email</b>
                </p>
              </div>

              <button
                type="button"
                onClick={handleLoadSample}
                className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono flex items-center space-x-1.5 transition-all self-start sm:self-center cursor-pointer"
              >
                <FileText className="w-3.5 h-3.5 text-cyan-400" />
                <span>Load Sample CSV</span>
              </button>
            </div>

            <form onSubmit={handleCsvSubmit} className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                  onClick={() => fileInputRef.current?.click()}
                  className="p-6 rounded-xl border border-dashed border-slate-700 hover:border-cyan-400 bg-slate-950/60 flex flex-col items-center justify-center text-center cursor-pointer transition-all group"
                >
                  <UploadCloud className="w-8 h-8 text-slate-500 group-hover:text-cyan-400 mb-2 transition-colors" />
                  <span className="text-xs font-mono text-slate-300 font-semibold">CSV / TXT File Select Karein</span>
                  <span className="text-[10px] text-slate-500 mt-1">Click to browse file</span>
                  {selectedFileName && (
                    <div className="mt-2 px-2.5 py-1 rounded bg-cyan-950 text-cyan-300 border border-cyan-800 text-[11px] font-mono break-all">
                      {selectedFileName}
                    </div>
                  )}
                  <input
                    ref={fileInputRef}
                    type="file"
                    accept=".csv,.txt"
                    onChange={handleFileChange}
                    className="hidden"
                  />
                </div>

                <div className="md:col-span-2">
                  <textarea
                    rows={6}
                    value={csvText}
                    onChange={(e) => setCsvText(e.target.value)}
                    placeholder="email,password,recovery email&#10;user1@basis5.ch,pass123,rec1@gmail.com&#10;user2@adlover.site,pass456,rec2@gmail.com"
                    className="w-full p-3.5 bg-slate-950/90 border border-slate-700/80 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-cyan-400 transition-all placeholder:text-slate-600 custom-scroll"
                  />
                </div>
              </div>

              <div className="pt-2">
                <button
                  type="submit"
                  disabled={isProcessing}
                  className="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold font-mono text-xs uppercase tracking-wider transition-all shadow-md shadow-cyan-500/20 cursor-pointer disabled:opacity-50 flex items-center space-x-2"
                >
                  <Send className="w-4 h-4" />
                  <span>{isProcessing ? 'Processing...' : 'Process Mails & Select Destination (Available vs Sold)'}</span>
                </button>
              </div>
            </form>
          </div>

          {/* ACCOUNTS INVENTORY TABLE */}
          <div className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 shadow-xl">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-800">
              <div>
                <h3 className="text-lg font-bold text-white flex items-center space-x-2">
                  <Layers className="w-5 h-5 text-cyan-400" />
                  <span>Loaded Accounts Master Table</span>
                </h3>
                <p className="text-xs text-slate-400 mt-0.5">Filter by domain, status, or search email.</p>
              </div>

              {/* Filters */}
              <div className="flex flex-wrap items-center gap-2">
                <select
                  value={domainFilter}
                  onChange={(e) => setDomainFilter(e.target.value)}
                  className="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-white focus:outline-none focus:border-cyan-400"
                >
                  <option value="">All Domains</option>
                  {MANAGED_DOMAINS.map((d) => (
                    <option key={d} value={d}>{d}</option>
                  ))}
                </select>

                <select
                  value={statusFilter}
                  onChange={(e) => setStatusFilter(e.target.value)}
                  className="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-white focus:outline-none focus:border-cyan-400"
                >
                  <option value="">All Statuses</option>
                  <option value="available">Available</option>
                  <option value="downloaded">Downloaded</option>
                  <option value="replaced">Replaced</option>
                </select>

                <div className="relative">
                  <Search className="w-3.5 h-3.5 text-slate-500 absolute left-2.5 top-2.5" />
                  <input
                    type="text"
                    placeholder="Search email..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="pl-8 pr-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-white focus:outline-none focus:border-cyan-400"
                  />
                </div>
              </div>
            </div>

            {/* Bulk Actions for Selected Checkboxes */}
            {(() => {
              const selectedAccountsList = accounts.filter((a) => selectedAccountIds.has(a.id));

              return (
                <div className="space-y-3 mb-4">
                  {/* Bulk Actions for Selected Checkboxes */}
                  {selectedAccountIds.size > 0 && (
                    <div className="p-3 bg-cyan-950/80 border border-cyan-500/40 rounded-xl flex flex-wrap items-center justify-between gap-3 animate__animated animate__fadeIn">
                      <span className="text-xs font-mono text-cyan-300 font-bold">
                        {selectedAccountIds.size} accounts muntakhib hain (selected)
                      </span>
                      <div className="flex items-center space-x-2">
                        <button
                          type="button"
                          onClick={() => handleBulkMakeAvailable(selectedAccountsList)}
                          className="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold font-mono text-xs rounded-lg flex items-center space-x-1.5 cursor-pointer shadow-sm"
                        >
                          <RotateCcw className="w-3.5 h-3.5" />
                          <span>Make Selected Available ({selectedAccountIds.size})</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => handleBulkDelete(selectedAccountsList)}
                          className="px-3 py-1.5 bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-300 font-bold font-mono text-xs rounded-lg flex items-center space-x-1.5 cursor-pointer"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                          <span>Delete Selected ({selectedAccountIds.size})</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => setSelectedAccountIds(new Set())}
                          className="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono rounded-lg cursor-pointer"
                        >
                          Clear
                        </button>
                      </div>
                    </div>
                  )}
                </div>
              );
            })()}

            {filteredAccounts.length === 0 ? (
              <div className="text-center py-10 text-slate-500 font-mono text-xs">
                Koi account nahi mila.
              </div>
            ) : (
              <div className="overflow-x-auto rounded-xl border border-slate-800">
                <table className="w-full text-left text-xs font-mono">
                  <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                    <tr>
                      <th className="p-3 text-center w-8">
                        <button
                          type="button"
                          onClick={toggleSelectAllFiltered}
                          className="text-slate-400 hover:text-white cursor-pointer"
                          title="Select / Deselect Visible"
                        >
                          {filteredAccounts.slice(0, 100).length > 0 &&
                          filteredAccounts.slice(0, 100).every((a) => selectedAccountIds.has(a.id)) ? (
                            <CheckSquare className="w-4 h-4 text-cyan-400" />
                          ) : (
                            <Square className="w-4 h-4 text-slate-600" />
                          )}
                        </button>
                      </th>
                      <th className="p-3">#</th>
                      <th className="p-3">Email</th>
                      <th className="p-3">Password</th>
                      <th className="p-3">Recovery Email</th>
                      <th className="p-3">Domain</th>
                      <th className="p-3 text-center">Status</th>
                      <th className="p-3 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/60 bg-slate-950/30">
                    {filteredAccounts.slice(0, 100).map((acc, index) => {
                      const isSelected = selectedAccountIds.has(acc.id);
                      return (
                        <tr
                          key={acc.id || index}
                          className={`hover:bg-slate-900/50 transition-colors ${
                            isSelected ? 'bg-cyan-950/20' : ''
                          }`}
                        >
                          <td className="p-3 text-center">
                            <input
                              type="checkbox"
                              checked={isSelected}
                              onChange={() => toggleSelectAccount(acc.id)}
                              className="rounded border-slate-700 text-cyan-500 focus:ring-0 focus:ring-offset-0 bg-slate-900 w-3.5 h-3.5 cursor-pointer"
                            />
                          </td>
                          <td className="p-3 text-slate-500">{index + 1}</td>
                          <td className="p-3 font-semibold text-white break-all">{acc.email}</td>
                          <td className="p-3 text-cyan-300 font-mono">{acc.password}</td>
                          <td className="p-3 text-slate-400">{acc.recovery_email || '—'}</td>
                          <td className="p-3 text-slate-300">{acc.domain}</td>
                          <td className="p-3 text-center">
                            <span
                              className={`px-2 py-0.5 rounded-full text-[10px] font-bold ${
                                acc.status === 'available'
                                  ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20'
                                  : acc.status === 'downloaded'
                                  ? 'bg-cyan-500/10 text-cyan-300 border border-cyan-500/20'
                                  : 'bg-amber-500/10 text-amber-300 border border-amber-500/20'
                              }`}
                            >
                              {acc.status}
                            </span>
                          </td>
                          <td className="p-3 text-center">
                            <div className="flex items-center justify-center space-x-1.5">
                              {acc.status !== 'available' && (
                                <button
                                  type="button"
                                  onClick={() => handleSetAccountAvailable(acc)}
                                  className="p-1.5 rounded bg-emerald-950 hover:bg-emerald-900 text-emerald-300 border border-emerald-800 transition-colors cursor-pointer"
                                  title="Wapis Available Stock Me Daalein"
                                >
                                  <RotateCcw className="w-3.5 h-3.5" />
                                </button>
                              )}
                              <button
                                type="button"
                                onClick={() => setEditingAccount(acc)}
                                className="p-1.5 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 transition-colors cursor-pointer"
                                title="Edit Credentials &amp; Status"
                              >
                                <Edit2 className="w-3.5 h-3.5" />
                              </button>
                              <button
                                type="button"
                                onClick={() => handleDeleteAccount(acc)}
                                className="p-1.5 rounded bg-slate-800 hover:bg-rose-950/60 text-rose-400 transition-colors cursor-pointer"
                                title="Delete Account"
                              >
                                <Trash2 className="w-3.5 h-3.5" />
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
        </div>
      )}

      {/* TAB 2: ORDERS CONSOLE & KHATA */}
      {activeTab === 'orders' && (
        <div className="space-y-6">
          <div className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 shadow-xl">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-800">
              <div>
                <h3 className="text-lg font-bold text-white flex items-center space-x-2">
                  <FileSpreadsheet className="w-5 h-5 text-cyan-400" />
                  <span>Client Orders &amp; Delivery Hub</span>
                </h3>
                <p className="text-xs text-slate-400 mt-1">
                  Yahan aap delivered orders aur automated extractions dono manage kar sakte hain.
                </p>
              </div>

              <div className="flex items-center space-x-2.5">
                <button
                  type="button"
                  onClick={openNewOrderModal}
                  className="px-4 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold font-mono text-xs uppercase tracking-wider rounded-xl transition-all flex items-center space-x-2 cursor-pointer shadow-md"
                >
                  <Plus className="w-4 h-4" />
                  <span>Record / Ingest Order</span>
                </button>
              </div>
            </div>

            {orders.length === 0 ? (
              <div className="text-center py-12 text-slate-500 font-mono text-xs">
                Koi order record nahi mila. "Record / Ingest Order" daba kar order add karein.
              </div>
            ) : (
              <div className="overflow-x-auto rounded-xl border border-slate-800">
                <table className="w-full text-left text-xs font-mono">
                  <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                    <tr>
                      <th className="p-3.5">Order Number</th>
                      <th className="p-3.5">Date</th>
                      <th className="p-3.5">Domain</th>
                      <th className="p-3.5 text-center">Quantity</th>
                      <th className="p-3.5 text-center">Status</th>
                      <th className="p-3.5 text-right">Rate</th>
                      <th className="p-3.5 text-right">Total Price</th>
                      <th className="p-3.5">Notes</th>
                      <th className="p-3.5 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/60 bg-slate-950/30">
                    {orders.map((ord) => {
                      const rate = ord.rate_per_mail !== undefined ? ord.rate_per_mail : 18;
                      const total = ord.total_price !== undefined ? ord.total_price : (ord.quantity * rate);
                      const isReverted = (ord.status && (ord.status === 'reverted' || ord.status === 'cancelled')) || !!(ord.notes && ord.notes.includes('[REVERTED TO AVAILABLE STOCK]'));
                      return (
                        <tr key={ord.id} className="hover:bg-slate-900/50">
                          <td className="p-3.5 font-bold text-white flex items-center space-x-1.5">
                            <span className={`w-2 h-2 rounded-full ${isReverted ? 'bg-amber-400' : 'bg-cyan-400'}`} />
                            <span>{ord.order_number}</span>
                          </td>
                          <td className="p-3.5 text-slate-300">{formatDateTime(ord.created_at)}</td>
                          <td className="p-3.5 font-bold text-slate-300">{ord.domain}</td>
                          <td className="p-3.5 text-center">
                            <span className="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 font-bold">
                              {ord.quantity} Mails
                            </span>
                          </td>
                          <td className="p-3.5 text-center">
                            <select
                              value={isReverted ? 'reverted' : 'delivered'}
                              onChange={(e) => handleToggleOrderStatus(ord.id, e.target.value as 'delivered' | 'reverted')}
                              className={`px-2 py-1 rounded text-[11px] font-bold font-mono border focus:outline-none cursor-pointer transition-all ${
                                isReverted
                                  ? 'bg-amber-950/80 text-amber-300 border-amber-500/70 hover:border-amber-400'
                                  : 'bg-emerald-950/80 text-emerald-300 border-emerald-500/70 hover:border-emerald-400'
                              }`}
                              title="Status change karein: Revert karne par mails Available stock me aa jayengi aur bill foran kam ho jayega"
                            >
                              <option value="delivered">Delivered (Sold)</option>
                              <option value="reverted">Reverted (In Stock)</option>
                            </select>
                          </td>
                          <td className="p-3.5 text-right">
                            <button
                              type="button"
                              onClick={() => handleQuickUpdateRate(ord.id, ord.order_number, rate, ord.quantity)}
                              className="text-amber-300 hover:text-amber-200 underline font-mono inline-flex items-center space-x-1 cursor-pointer"
                              title="Click karein rate ya price change karne ke liye"
                            >
                              <span>Rs. {rate}</span>
                              <Edit2 className="w-2.5 h-2.5 text-slate-400" />
                            </button>
                          </td>
                          <td className={`p-3.5 text-right font-bold ${isReverted ? 'text-slate-500 line-through' : 'text-emerald-400'}`}>
                            Rs. {total.toLocaleString()}
                          </td>
                          <td className="p-3.5 text-slate-400 truncate max-w-[150px]">{ord.notes || '—'}</td>
                          <td className="p-3.5 text-center">
                            <div className="flex items-center justify-center space-x-1.5">
                              <button
                                type="button"
                                onClick={() => setViewingOrder(ord)}
                                className="p-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors cursor-pointer"
                                title="Inspect Mails"
                              >
                                <Eye className="w-3.5 h-3.5 text-cyan-400" />
                              </button>
                              <button
                                type="button"
                                onClick={() => downloadAccountsFile(ord.accounts, ord.domain, ord.format, ord.order_number)}
                                className="p-1.5 rounded bg-cyan-950 hover:bg-cyan-900 text-cyan-300 border border-cyan-800 transition-colors cursor-pointer"
                                title="Download CSV"
                              >
                                <Download className="w-3.5 h-3.5" />
                              </button>
                              <button
                                type="button"
                                onClick={() => {
                                  setEditingOrderId(ord.id);
                                  setOrderModalNumber(ord.order_number);
                                  setOrderModalDate(ord.created_at.substring(0, 16));
                                  setOrderModalRate(rate);
                                  setOrderModalNotes(ord.notes || '');
                                  setOrderModalDomain(ord.domain);
                                  setShowOrderModal(true);
                                }}
                                className="p-1.5 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 transition-colors cursor-pointer"
                                title="Edit Full Order Details"
                              >
                                <Edit2 className="w-3.5 h-3.5" />
                              </button>
                              {isReverted ? (
                                <button
                                  type="button"
                                  onClick={() => handleToggleOrderStatus(ord.id, 'delivered')}
                                  className="p-1.5 rounded bg-emerald-950 hover:bg-emerald-900 text-emerald-300 border border-emerald-800 transition-colors cursor-pointer"
                                  title="Mark Delivered & Add Back to Bill"
                                >
                                  <CheckCircle2 className="w-3.5 h-3.5" />
                                </button>
                              ) : (
                                <button
                                  type="button"
                                  onClick={() => handleToggleOrderStatus(ord.id, 'reverted')}
                                  className="p-1.5 rounded bg-amber-950 hover:bg-amber-900 text-amber-300 border border-amber-800 transition-colors cursor-pointer"
                                  title="Revert Mails to Stock & Deduct Bill"
                                >
                                  <RotateCcw className="w-3.5 h-3.5" />
                                </button>
                              )}
                              <button
                                type="button"
                                onClick={() => handleDeleteOrder(ord.id, ord.order_number)}
                                className="p-1.5 rounded bg-slate-800 hover:bg-rose-900/50 text-rose-400 transition-colors cursor-pointer"
                                title="Delete Order & Return Mails to Stock"
                              >
                                <Trash2 className="w-3.5 h-3.5" />
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
        </div>
      )}

      {/* TAB 3: WASOOLI TRACKER (PAYMENTS) */}
      {activeTab === 'payments' && (
        <div className="space-y-6">
          <div className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 shadow-xl">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-800">
              <div>
                <h3 className="text-lg font-bold text-white flex items-center space-x-2">
                  <ArrowDownCircle className="w-5 h-5 text-emerald-400" />
                  <span>Client Payments (Wasooli Ledger)</span>
                </h3>
                <p className="text-xs text-slate-400 mt-1">
                  Client se received payments ka record with date, method, aur amount.
                </p>
              </div>

              <button
                type="button"
                onClick={openNewPaymentModal}
                className="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold font-mono text-xs uppercase tracking-wider rounded-xl transition-all flex items-center space-x-2 cursor-pointer shadow-md"
              >
                <Plus className="w-4 h-4" />
                <span>Record Payment</span>
              </button>
            </div>

            {payments.length === 0 ? (
              <div className="text-center py-12 text-slate-500 font-mono text-xs">
                Filhal koi payment record nahi mila. "Record Payment" button se wasooli add karein.
              </div>
            ) : (
              <div className="overflow-x-auto rounded-xl border border-slate-800">
                <table className="w-full text-left text-xs font-mono">
                  <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                    <tr>
                      <th className="p-3.5">Payment ID</th>
                      <th className="p-3.5">Date</th>
                      <th className="p-3.5">Client</th>
                      <th className="p-3.5">Method</th>
                      <th className="p-3.5">Reference / Note</th>
                      <th className="p-3.5 text-right">Amount Received</th>
                      <th className="p-3.5 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/60 bg-slate-950/30">
                    {payments.map((p, idx) => (
                      <tr key={p.id || idx} className="hover:bg-slate-900/50">
                        <td className="p-3.5 font-bold text-white">PAY-#{p.id || idx + 1}</td>
                        <td className="p-3.5 text-slate-300">{p.payment_date}</td>
                        <td className="p-3.5 text-slate-300 font-bold">Rana Asim</td>
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
                          <div className="flex items-center justify-center space-x-1.5">
                            <button
                              type="button"
                              onClick={() => {
                                setEditingPaymentId(p.id);
                                setPaymentModalAmount(p.amount);
                                setPaymentModalDate(p.payment_date);
                                setPaymentModalMethod(p.payment_method);
                                setPaymentModalNote(p.reference_note);
                                setShowPaymentModal(true);
                              }}
                              className="p-1.5 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 transition-colors cursor-pointer"
                              title="Edit Payment"
                            >
                              <Edit2 className="w-3.5 h-3.5" />
                            </button>
                            <button
                              type="button"
                              onClick={() => handleDeletePayment(p.id)}
                              className="p-1.5 rounded bg-slate-800 hover:bg-rose-900/50 text-rose-400 transition-colors cursor-pointer"
                              title="Delete Payment"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* TAB 4: REPLACEMENTS (FAULTY DEDUCTIONS) */}
      {activeTab === 'replacements' && (
        <div className="space-y-6">
          <div className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 shadow-xl">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-800">
              <div>
                <h3 className="text-lg font-bold text-white flex items-center space-x-2">
                  <RefreshCw className="w-5 h-5 text-amber-400" />
                  <span>Faulty Replacements (Auto-Deduction Console)</span>
                </h3>
                <p className="text-xs text-slate-400 mt-1">
                  Kharab mails yahan add karne se wo client ke total bill aur sale quantity se direct minus ho jati hain.
                </p>
              </div>

              <button
                type="button"
                onClick={() => setShowReplacementModal(true)}
                className="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold font-mono text-xs uppercase tracking-wider rounded-xl transition-all flex items-center space-x-2 cursor-pointer shadow-md"
              >
                <Plus className="w-4 h-4" />
                <span>Add Faulty Replacements</span>
              </button>
            </div>

            {replacements.length === 0 ? (
              <div className="text-center py-12 text-slate-500 font-mono text-xs">
                Filhal koi faulty replacement record nahi hai.
              </div>
            ) : (
              <div className="overflow-x-auto rounded-xl border border-slate-800">
                <table className="w-full text-left text-xs font-mono">
                  <thead className="bg-slate-950 text-slate-400 uppercase text-[10px] border-b border-slate-800">
                    <tr>
                      <th className="p-3.5">#</th>
                      <th className="p-3.5">Email Address</th>
                      <th className="p-3.5">Domain</th>
                      <th className="p-3.5 text-right">Deduction (PKR)</th>
                      <th className="p-3.5">Reason</th>
                      <th className="p-3.5">Date</th>
                      <th className="p-3.5 text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/60 bg-slate-950/30">
                    {replacements.map((r, idx) => (
                      <tr key={r.id || idx} className="hover:bg-slate-900/50">
                        <td className="p-3.5 text-slate-500">{idx + 1}</td>
                        <td className="p-3.5 font-semibold text-white break-all">{r.email}</td>
                        <td className="p-3.5 text-slate-300">{r.domain}</td>
                        <td className="p-3.5 text-right text-emerald-400 font-bold">
                          -Rs. {r.rate_deduction || 18}
                        </td>
                        <td className="p-3.5 text-amber-300/80">{r.reason || 'Faulty'}</td>
                        <td className="p-3.5 text-slate-400">{formatDateTime(r.created_at)}</td>
                        <td className="p-3.5 text-center">
                          <button
                            type="button"
                            onClick={() => handleDeleteReplacement(r.id)}
                            className="p-1.5 rounded bg-slate-800 hover:bg-rose-900/50 text-rose-400 transition-colors cursor-pointer"
                            title="Delete"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* TAB 5: SINGLE MAIL FINDER */}
      {activeTab === 'finder' && (
        <div className="space-y-6">
          <div className="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 lg:p-8 shadow-xl">
            <div className="flex items-center space-x-2 mb-2">
              <Search className="w-5 h-5 text-cyan-400" />
              <h3 className="text-lg font-bold text-white tracking-tight">Single Mail Credential Finder</h3>
            </div>
            <p className="text-xs text-slate-400 mb-6">
              Enter email address to look up its password, recovery email, order date, and delivery details.
            </p>

            <form onSubmit={handleFinderSearch} className="flex flex-col sm:flex-row items-center gap-3">
              <input
                type="text"
                required
                placeholder="e.g. alpha_01@basis5.ch"
                value={finderQuery}
                onChange={(e) => setFinderQuery(e.target.value)}
                className="w-full flex-1 px-4 py-3 bg-slate-950/90 border border-slate-700/80 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-cyan-400"
              />
              <button
                type="submit"
                className="w-full sm:w-auto px-6 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-mono font-bold text-xs uppercase tracking-wider rounded-xl transition-all flex items-center justify-center space-x-2 cursor-pointer shadow-md"
              >
                <Search className="w-4 h-4" />
                <span>Search Credential</span>
              </button>
            </form>

            {finderError && (
              <div className="mt-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-mono flex items-center space-x-2">
                <AlertCircle className="w-4 h-4 flex-shrink-0" />
                <span>{finderError}</span>
              </div>
            )}

            {finderResult && (
              <div className="mt-6 p-5 rounded-2xl bg-slate-950/90 border border-cyan-500/30">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-4 border-b border-slate-800">
                  <div className="flex items-center space-x-2">
                    <span className="w-2 h-2 rounded-full bg-emerald-400" />
                    <span className="text-xs font-mono text-cyan-400 font-bold uppercase tracking-wider">
                      Account Found
                    </span>
                    {finderResult.orderNumber && (
                      <span className="px-2 py-0.5 rounded text-[10px] font-mono bg-cyan-950 text-cyan-300 border border-cyan-800">
                        {finderResult.orderNumber}
                      </span>
                    )}
                  </div>
                  <button
                    type="button"
                    onClick={() => {
                      const item = finderResult.account;
                      const text = `${item.email},${item.password},${item.recovery_email || ''}`;
                      copyFinderText(text, 'full_combo');
                    }}
                    className="px-3 py-1.5 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 rounded-lg text-xs font-mono flex items-center space-x-1.5 transition-all cursor-pointer"
                  >
                    {copiedFinderField === 'full_combo' ? (
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
                      <div className="text-white font-bold break-all mt-0.5">{finderResult.account.email}</div>
                    </div>
                    <button
                      type="button"
                      onClick={() => copyFinderText(finderResult.account.email, 'email')}
                      className="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded transition-colors ml-2 cursor-pointer"
                    >
                      {copiedFinderField === 'email' ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                    </button>
                  </div>

                  <div className="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                    <div>
                      <div className="text-[10px] text-slate-500 uppercase">Password</div>
                      <div className="text-cyan-300 font-bold break-all mt-0.5">{finderResult.account.password}</div>
                    </div>
                    <button
                      type="button"
                      onClick={() => copyFinderText(finderResult.account.password, 'password')}
                      className="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded transition-colors ml-2 cursor-pointer"
                    >
                      {copiedFinderField === 'password' ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                    </button>
                  </div>

                  <div className="p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between">
                    <div>
                      <div className="text-[10px] text-slate-500 uppercase">Recovery Email</div>
                      <div className="text-slate-300 break-all mt-0.5">{finderResult.account.recovery_email || 'N/A'}</div>
                    </div>
                    {finderResult.account.recovery_email && (
                      <button
                        type="button"
                        onClick={() => copyFinderText(finderResult.account.recovery_email, 'rec')}
                        className="p-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded transition-colors ml-2 cursor-pointer"
                      >
                        {copiedFinderField === 'rec' ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
                      </button>
                    )}
                  </div>
                </div>

                {/* Actions for found account */}
                <div className="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-800">
                  <div className="flex items-center space-x-2 text-xs font-mono">
                    <span className="text-slate-400">Current Status:</span>
                    <span
                      className={`px-2 py-0.5 rounded-full text-[10px] font-bold ${
                        (finderResult.account as EmailAccount).status === 'available'
                          ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20'
                          : (finderResult.account as EmailAccount).status === 'downloaded'
                          ? 'bg-cyan-500/10 text-cyan-300 border border-cyan-500/20'
                          : 'bg-amber-500/10 text-amber-300 border border-amber-500/20'
                      }`}
                    >
                      {(finderResult.account as EmailAccount).status || 'available'}
                    </span>
                  </div>

                  <div className="flex items-center space-x-2">
                    {(finderResult.account as EmailAccount).status !== 'available' && (
                      <button
                        type="button"
                        onClick={() => {
                          const acc = accounts.find((a) => a.email.toLowerCase() === finderResult.account.email.toLowerCase()) || (finderResult.account as EmailAccount);
                          handleSetAccountAvailable(acc);
                          setFinderResult({
                            ...finderResult,
                            account: { ...finderResult.account, status: 'available' }
                          });
                        }}
                        className="px-3 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-300 font-bold text-xs flex items-center space-x-1.5 cursor-pointer"
                      >
                        <RotateCcw className="w-3.5 h-3.5" />
                        <span>Revert to 'Available' Stock</span>
                      </button>
                    )}
                    <button
                      type="button"
                      onClick={() => {
                        const acc = accounts.find((a) => a.email.toLowerCase() === finderResult.account.email.toLowerCase()) || (finderResult.account as EmailAccount);
                        setEditingAccount(acc);
                      }}
                      className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 border border-slate-700 font-bold text-xs flex items-center space-x-1.5 cursor-pointer"
                    >
                      <Edit2 className="w-3.5 h-3.5" />
                      <span>Edit Credentials</span>
                    </button>
                  </div>
                </div>
              </div>
            )}
          </div>
        </div>
      )}

      {/* MODAL: RECORD / INGEST ORDER */}
      {showOrderModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
          <div className="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl flex flex-col font-mono text-xs">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
              <h3 className="text-base font-bold text-white">
                {editingOrderId ? 'Edit Order Details' : 'Record / Ingest Order'}
              </h3>
              <button
                type="button"
                onClick={() => setShowOrderModal(false)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSaveOrder} className="p-6 space-y-4">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-400 mb-1">Order Number</label>
                  <input
                    type="text"
                    required
                    value={orderModalNumber}
                    onChange={(e) => setOrderModalNumber(e.target.value)}
                    placeholder="e.g. Order #1"
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                  />
                </div>
                <div>
                  <label className="block text-slate-400 mb-1">Date &amp; Time</label>
                  <input
                    type="datetime-local"
                    value={orderModalDate}
                    onChange={(e) => setOrderModalDate(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-400 mb-1">Rate Per Mail (PKR)</label>
                  <input
                    type="number"
                    min="1"
                    required
                    value={orderModalRate}
                    onChange={(e) => setOrderModalRate(parseInt(e.target.value, 10) || 18)}
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                  />
                </div>
                <div>
                  <label className="block text-slate-400 mb-1">Domain</label>
                  <select
                    value={orderModalDomain}
                    onChange={(e) => setOrderModalDomain(e.target.value)}
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                  >
                    {MANAGED_DOMAINS.map((d) => (
                      <option key={d} value={d}>{d}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Notes / Description</label>
                <input
                  type="text"
                  value={orderModalNotes}
                  onChange={(e) => setOrderModalNotes(e.target.value)}
                  placeholder="e.g. Batch delivery note"
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                />
              </div>

              {!editingOrderId && (
                <div>
                  <label className="block text-slate-400 mb-1">
                    Mails Data (Format: <span className="text-cyan-300">email,password,recovery</span>)
                  </label>
                  <textarea
                    rows={6}
                    required
                    value={orderModalCsv}
                    onChange={(e) => setOrderModalCsv(e.target.value)}
                    placeholder="mail1@basis5.ch,pass1,rec1@gmail.com&#10;mail2@basis5.ch,pass2,rec2@gmail.com"
                    className="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-white"
                  />
                </div>
              )}

              <div className="pt-2 flex justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setShowOrderModal(false)}
                  className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold rounded-xl"
                >
                  {editingOrderId ? 'Save Changes' : 'Save & Ingest Order'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: RECORD PAYMENT */}
      {showPaymentModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
          <div className="bg-slate-900 border border-emerald-500/40 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl flex flex-col font-mono text-xs">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
              <h3 className="text-base font-bold text-white">
                {editingPaymentId ? 'Edit Payment' : 'Record Client Payment (Wasooli)'}
              </h3>
              <button
                type="button"
                onClick={() => setShowPaymentModal(false)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSavePayment} className="p-6 space-y-4">
              <div>
                <label className="block text-slate-400 mb-1">Amount Received (PKR)</label>
                <input
                  type="number"
                  required
                  min="1"
                  value={paymentModalAmount}
                  onChange={(e) => setPaymentModalAmount(e.target.value === '' ? '' : parseFloat(e.target.value))}
                  placeholder="e.g. 5000"
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white text-base font-bold text-emerald-400"
                />
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Payment Date</label>
                <input
                  type="date"
                  required
                  value={paymentModalDate}
                  onChange={(e) => setPaymentModalDate(e.target.value)}
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                />
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Payment Method</label>
                <select
                  value={paymentModalMethod}
                  onChange={(e) => setPaymentModalMethod(e.target.value)}
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                >
                  <option value="Bank Transfer">Bank Transfer / Raast</option>
                  <option value="EasyPaisa">EasyPaisa</option>
                  <option value="JazzCash">JazzCash</option>
                  <option value="Cash / Hand">Cash</option>
                  <option value="Other">Other</option>
                </select>
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Reference / Note</label>
                <input
                  type="text"
                  value={paymentModalNote}
                  onChange={(e) => setPaymentModalNote(e.target.value)}
                  placeholder="e.g. HBL Raast transfer txn #98214"
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                />
              </div>

              <div className="pt-2 flex justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setShowPaymentModal(false)}
                  className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl"
                >
                  {editingPaymentId ? 'Save Changes' : 'Save Payment'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: ADD REPLACEMENTS */}
      {showReplacementModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
          <div className="bg-slate-900 border border-amber-500/40 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col font-mono text-xs">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
              <h3 className="text-base font-bold text-white">Add Faulty Replacements (Auto-Deduction)</h3>
              <button
                type="button"
                onClick={() => setShowReplacementModal(false)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSaveReplacement} className="p-6 space-y-4">
              <div>
                <label className="block text-slate-400 mb-1">Faulty Emails List (1 per line or comma-separated)</label>
                <textarea
                  rows={6}
                  required
                  value={replacementModalEmails}
                  onChange={(e) => setReplacementModalEmails(e.target.value)}
                  placeholder="bad_mail1@basis5.ch&#10;bad_mail2@adlover.site"
                  className="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-white"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-slate-400 mb-1">Deduction Per Mail (PKR)</label>
                  <input
                    type="number"
                    min="1"
                    required
                    value={replacementModalRate}
                    onChange={(e) => setReplacementModalRate(parseInt(e.target.value, 10) || 18)}
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white font-bold text-amber-300"
                  />
                </div>
                <div>
                  <label className="block text-slate-400 mb-1">Reason</label>
                  <input
                    type="text"
                    value={replacementModalReason}
                    onChange={(e) => setReplacementModalReason(e.target.value)}
                    placeholder="e.g. Password wrong"
                    className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white"
                  />
                </div>
              </div>

              <p className="text-slate-400 text-[11px]">
                * Ye mails direct client ke bill aur quantity se deduct ho jayengi.
              </p>

              <div className="pt-2 flex justify-end space-x-2">
                <button
                  type="button"
                  onClick={() => setShowReplacementModal(false)}
                  className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl"
                >
                  Deduct Faulty Mails
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: READ MORE / INSPECT ORDER */}
      {viewingOrder && (
        <div
          id="admin-modal-view-order"
          className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate__animated animate__fadeIn"
        >
          <div className="bg-slate-900 border border-cyan-500/40 rounded-2xl w-full max-w-3xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden font-mono">
            <div className="flex items-center justify-between p-5 border-b border-slate-800 bg-slate-950/70">
              <div>
                <h3 className="text-base font-bold text-white flex items-center space-x-2">
                  <span className="text-cyan-400">{viewingOrder.order_number}</span>
                  <span className="text-slate-400 text-xs">
                    &bull; {viewingOrder.quantity} Mails ({viewingOrder.domain})
                  </span>
                </h3>
                <p className="text-xs text-slate-400 mt-0.5">Date: {viewingOrder.created_at}</p>
              </div>

              <div className="flex items-center space-x-2">
                <button
                  type="button"
                  onClick={() => {
                    const ord = viewingOrder;
                    setViewingOrder(null);
                    openRevertOrderModal(ord);
                  }}
                  className="px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 font-bold text-xs flex items-center space-x-1.5 cursor-pointer"
                >
                  <RotateCcw className="w-3.5 h-3.5" />
                  <span>Revert to Stock</span>
                </button>
                <button
                  type="button"
                  onClick={() => copyAccountsToClipboard(viewingOrder.accounts, viewingOrder.id)}
                  className="px-3 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center space-x-1.5 cursor-pointer"
                >
                  <Copy className="w-3.5 h-3.5" />
                  <span>Copy Mails</span>
                </button>
                <button
                  type="button"
                  onClick={() => setViewingOrder(null)}
                  className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            <div className="p-6 overflow-y-auto custom-scroll flex-grow">
              <table className="w-full text-left text-xs">
                <thead className="bg-slate-950 text-slate-400 border-b border-slate-800">
                  <tr>
                    <th className="p-2.5">#</th>
                    <th className="p-2.5">Email</th>
                    <th className="p-2.5">Password</th>
                    <th className="p-2.5">Recovery Mail</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-800/60 text-slate-300">
                  {viewingOrder.accounts.map((item, idx) => (
                    <tr key={idx} className="hover:bg-slate-950/40">
                      <td className="p-2.5 text-slate-500">{idx + 1}</td>
                      <td className="p-2.5 text-cyan-300 select-all">{item.email}</td>
                      <td className="p-2.5 text-slate-300 select-all">{item.password}</td>
                      <td className="p-2.5 text-slate-400 select-all">{item.recovery_email || '-'}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="p-4 border-t border-slate-800 bg-slate-950/70 flex justify-end">
              <button
                type="button"
                onClick={() => setViewingOrder(null)}
                className="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs text-slate-300 cursor-pointer"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* MODAL: EDIT ACCOUNT / REVERT STATUS */}
      {editingAccount && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate__animated animate__fadeIn">
          <div className="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl flex flex-col font-mono text-xs">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
              <div className="flex items-center space-x-2">
                <Edit2 className="w-4 h-4 text-cyan-400" />
                <h3 className="text-base font-bold text-white">Edit Account &amp; Status</h3>
              </div>
              <button
                type="button"
                onClick={() => setEditingAccount(null)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSaveEditAccount} className="p-6 space-y-4">
              <div>
                <label className="block text-slate-400 mb-1">Email Address</label>
                <input
                  type="email"
                  required
                  value={editingAccount.email}
                  onChange={(e) => setEditingAccount({ ...editingAccount, email: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white focus:border-cyan-400 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Password</label>
                <input
                  type="text"
                  required
                  value={editingAccount.password}
                  onChange={(e) => setEditingAccount({ ...editingAccount, password: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-cyan-300 font-bold focus:border-cyan-400 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-slate-400 mb-1">Recovery Email (Optional)</label>
                <input
                  type="text"
                  value={editingAccount.recovery_email || ''}
                  onChange={(e) => setEditingAccount({ ...editingAccount, recovery_email: e.target.value })}
                  placeholder="e.g. recovery@gmail.com"
                  className="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-xl text-white focus:border-cyan-400 focus:outline-none"
                />
              </div>

              <div className="p-3 bg-slate-950/60 rounded-xl border border-slate-800 space-y-2">
                <label className="block text-slate-400">Inventory Status</label>
                <div className="flex items-center gap-2">
                  <select
                    value={editingAccount.status}
                    onChange={(e) => setEditingAccount({ ...editingAccount, status: e.target.value as any })}
                    className="flex-1 px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white font-bold"
                  >
                    <option value="available">available (Stock me bikne ke liye tayar)</option>
                    <option value="downloaded">downloaded (Sold / Extracted)</option>
                    <option value="replaced">replaced (Deducted replacement)</option>
                  </select>

                  {editingAccount.status !== 'available' && (
                    <button
                      type="button"
                      onClick={() => setEditingAccount({ ...editingAccount, status: 'available' })}
                      className="px-3 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 border border-emerald-500/40 rounded-xl font-bold whitespace-nowrap cursor-pointer"
                    >
                      Set 'Available'
                    </button>
                  )}
                </div>
                <p className="text-[10px] text-slate-500">
                  Tip: Agar aap is account ka password thik karke wapis stock me bhejna chahte hain to status ko <b>'available'</b> par set karein.
                </p>
              </div>

              <div className="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setEditingAccount(null)}
                  className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold rounded-xl cursor-pointer shadow-md"
                >
                  Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL: REVERT ORDER TO STOCK */}
      {revertingOrder && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate__animated animate__fadeIn">
          <div className="bg-slate-900 border border-emerald-500/40 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl flex flex-col font-mono text-xs">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
              <div className="flex items-center space-x-2">
                <RotateCcw className="w-5 h-5 text-emerald-400" />
                <h3 className="text-base font-bold text-white">
                  Revert Mails to Available Stock: <span className="text-cyan-400">{revertingOrder.order_number}</span>
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setRevertingOrder(null)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleConfirmRevertOrder} className="p-6 space-y-4">
              <div className="p-4 bg-emerald-950/20 border border-emerald-500/30 rounded-xl space-y-2">
                <div className="flex items-center justify-between text-xs">
                  <span className="text-slate-400">Total Accounts to Revert:</span>
                  <span className="text-emerald-400 font-bold text-sm">{revertingOrder.quantity} Mails</span>
                </div>
                <div className="flex items-center justify-between text-xs">
                  <span className="text-slate-400">Domain:</span>
                  <span className="text-white font-bold">{revertingOrder.domain}</span>
                </div>
                <p className="text-[11px] text-slate-300 pt-1 border-t border-emerald-900/40">
                  Yeh تمام {revertingOrder.quantity} accounts <strong>'downloaded'</strong> status se nikal kar dobara <strong>'available'</strong> stock me shift ho jayenge taa ke client unhe extract kar sake.
                </p>
              </div>

              <div>
                <label className="block text-slate-300 font-bold mb-1">
                  Naye Passwords Update Karein (Optional):
                </label>
                <p className="text-[10px] text-slate-400 mb-2">
                  Agar aapne in 500 mails ke passwords change kiye hain, to yahan format me paste karein: <code className="text-cyan-300">email,password,recovery email</code>. (Agar khali chorenge to purane passwords hi rehenge).
                </p>
                <textarea
                  rows={4}
                  value={revertNewPasswordsCsv}
                  onChange={(e) => setRevertNewPasswordsCsv(e.target.value)}
                  placeholder="user1@adlover.site,newpass123,rec1@gmail.com&#10;user2@adlover.site,newpass456,rec2@gmail.com"
                  className="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-cyan-400 custom-scroll placeholder:text-slate-600"
                />
              </div>

              <div className="p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                <label className="flex items-start space-x-2.5 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={revertDeleteOrder}
                    onChange={(e) => setRevertDeleteOrder(e.target.checked)}
                    className="mt-0.5 rounded border-slate-700 text-rose-500 focus:ring-0 bg-slate-900 w-4 h-4 cursor-pointer"
                  />
                  <div>
                    <span className="text-white font-bold block">
                      Khata / Invoices se yeh Order record delete karein
                    </span>
                    <span className="text-[10px] text-slate-400 block mt-0.5">
                      Check rakhne se yeh faulty order Khata aur total bill calculation se mukammal remove ho jayega taa ke client ka hisab sahi rahe.
                    </span>
                  </div>
                </label>
              </div>

              <div className="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                <button
                  type="button"
                  onClick={() => setRevertingOrder(null)}
                  className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl cursor-pointer shadow-md flex items-center space-x-1.5"
                >
                  <RotateCcw className="w-3.5 h-3.5" />
                  <span>Confirm &amp; Revert to Stock</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Hostinger Deployment Guide Modal */}
      {showDeployModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md">
          <div className="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
              <div className="flex items-center space-x-2">
                <Server className="w-5 h-5 text-cyan-400" />
                <h3 className="text-base font-bold text-white">Hostinger Deployment Guide (asim.eztoolbox.xyz)</h3>
              </div>
              <button
                type="button"
                onClick={() => setShowDeployModal(false)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-xs text-slate-300 custom-scroll font-mono">
              <div className="p-3.5 rounded-xl bg-cyan-950/30 border border-cyan-500/30 text-cyan-200">
                <p className="font-bold text-sm text-cyan-300 mb-1">Subdomain Setup Complete</p>
                <p>Subdomain: <b className="text-white">asim.eztoolbox.xyz</b></p>
                <p>Hostinger Folder: <b className="text-white">public_html/asim/</b></p>
              </div>

              <div className="space-y-3">
                <div className="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                  <div className="text-white font-bold mb-1 text-sm flex items-center space-x-1.5">
                    <span className="w-5 h-5 rounded-full bg-cyan-500 text-slate-950 flex items-center justify-center text-xs">1</span>
                    <span>Download ZIP Package</span>
                  </div>
                  <p className="text-slate-400">
                    Hostinger deployment package ko download karein jisme تمام PHP files (.htaccess, db.php, api.php, auth.php, config.php, index.php) tayar hain.
                  </p>
                  <div className="mt-2">
                    <a
                      href="/hadi_digital_hostinger.zip"
                      download="hadi_digital_hostinger.zip"
                      className="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs"
                    >
                      <Download className="w-3.5 h-3.5" />
                      <span>Download hadi_digital_hostinger.zip</span>
                    </a>
                  </div>
                </div>

                <div className="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                  <div className="text-white font-bold mb-1 text-sm flex items-center space-x-1.5">
                    <span className="w-5 h-5 rounded-full bg-cyan-500 text-slate-950 flex items-center justify-center text-xs">2</span>
                    <span>Hostinger File Manager me Upload &amp; Extract</span>
                  </div>
                  <ol className="list-decimal list-inside space-y-1 text-slate-400 mt-1">
                    <li>Hostinger hPanel me <b>File Manager</b> kholein.</li>
                    <li><code className="text-cyan-300">public_html/asim</code> folder me jayein.</li>
                    <li><code className="text-white">hadi_digital_hostinger.zip</code> upload karein aur <b>Extract</b> karein (ya sirf index.php, api.php, db.php replace karein).</li>
                  </ol>
                </div>

                <div className="p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                  <div className="text-white font-bold mb-1 text-sm flex items-center space-x-1.5">
                    <span className="w-5 h-5 rounded-full bg-cyan-500 text-slate-950 flex items-center justify-center text-xs">3</span>
                    <span>Browser me Open Karein</span>
                  </div>
                  <p className="text-slate-400">
                    Visit: <a href="https://asim.eztoolbox.xyz" target="_blank" rel="noreferrer" className="text-cyan-400 underline">https://asim.eztoolbox.xyz</a>. Pehli dafa load hotay hi SQLite database automatically update ho jayegi.
                  </p>
                </div>
              </div>
            </div>

            <div className="p-4 border-t border-slate-800 bg-slate-950/70 flex justify-end">
              <button
                type="button"
                onClick={() => setShowDeployModal(false)}
                className="px-4 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs text-slate-300 cursor-pointer"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Client Portal Maintenance Controller Modal */}
      {showMaintenanceModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md animate-fadeIn">
          <div className="bg-slate-900 border border-amber-500/50 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl space-y-4">
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
              <div className="flex items-center space-x-2.5">
                <div className="w-8 h-8 rounded-lg bg-amber-500/20 border border-amber-500/40 flex items-center justify-center">
                  <Wrench className="w-4 h-4 text-amber-400" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-white">Client Portal Maintenance Controller</h3>
                  <p className="text-[11px] text-slate-400 font-mono">Control Rana Asim's portal view during your updates</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setShowMaintenanceModal(false)}
                className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer transition-colors"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-5 space-y-4 text-xs font-mono">
              {/* Toggle Switch */}
              <div className="p-4 bg-slate-950/80 border border-slate-800 rounded-xl flex items-center justify-between">
                <div>
                  <div className="text-sm font-bold text-white">Client Portal Access State</div>
                  <div className="text-xs text-slate-400 font-mono mt-0.5">
                    {maintEnabledInput ? (
                      <span className="text-amber-400 font-semibold">🔴 Under Maintenance (Client blocked)</span>
                    ) : (
                      <span className="text-emerald-400 font-semibold">🟢 LIVE (Client has full access)</span>
                    )}
                  </div>
                </div>
                <button
                  type="button"
                  onClick={() => setMaintEnabledInput(!maintEnabledInput)}
                  className={`relative inline-flex h-7 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
                    maintEnabledInput ? 'bg-amber-500' : 'bg-slate-700'
                  }`}
                >
                  <span
                    className={`pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out ${
                      maintEnabledInput ? 'translate-x-7' : 'translate-x-0'
                    }`}
                  />
                </button>
              </div>

              <div className="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-200/90 text-[11px] leading-relaxed">
                💡 <b>Admin Testing Benefit:</b> Maintenance mode lagane ke baad bhi <b>aap (Admin) "Test Client View" button se client portal ko bilkul aam client ki tarah test kar sakte hain</b> aur extractions check kar sakte hain, jabke client ko sirf Maintenance screen nazar aayegi!
              </div>

              {/* Maintenance Notice Message */}
              <div className="space-y-1.5">
                <label className="block text-[11px] text-slate-300 font-semibold uppercase">
                  Client Notice Message (Clients ko kya show ho):
                </label>
                <textarea
                  rows={3}
                  value={maintMessageInput}
                  onChange={(e) => setMaintMessageInput(e.target.value)}
                  className="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-amber-400 font-mono"
                  placeholder="Maintenance message yahan likhein..."
                />
                <div className="flex flex-wrap gap-1.5 pt-1">
                  <button
                    type="button"
                    onClick={() => setMaintMessageInput('Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!')}
                    className="text-[10px] px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 cursor-pointer"
                  >
                    Stock Upgrade Notice
                  </button>
                  <button
                    type="button"
                    onClick={() => setMaintMessageInput('Scheduled Maintenance: Portal 15-20 minute ke liye update par hai. Tamam stock mehfooz hai.')}
                    className="text-[10px] px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 cursor-pointer"
                  >
                    15 Min Quick Notice
                  </button>
                  <button
                    type="button"
                    onClick={() => setMaintMessageInput('Server Sync & Database Optimization chal rahi hai. Baraye meharbani kuch dair baad check karein.')}
                    className="text-[10px] px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 cursor-pointer"
                  >
                    Server Sync Notice
                  </button>
                </div>
              </div>
            </div>

            <div className="p-4 border-t border-slate-800 bg-slate-950/70 flex items-center justify-between">
              {onOpenClientPreview && (
                <button
                  type="button"
                  onClick={() => {
                    setShowMaintenanceModal(false);
                    onOpenClientPreview();
                  }}
                  className="px-3.5 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/40 text-indigo-300 font-mono text-xs font-semibold rounded-xl flex items-center space-x-1.5 transition-all cursor-pointer"
                >
                  <Eye className="w-3.5 h-3.5" />
                  <span>Test Client View</span>
                </button>
              )}
              <div className="flex items-center space-x-2 ml-auto">
                <button
                  type="button"
                  onClick={() => setShowMaintenanceModal(false)}
                  className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono rounded-xl cursor-pointer"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  onClick={() => {
                    if (onUpdateMaintenance) {
                      onUpdateMaintenance({
                        enabled: maintEnabledInput,
                        message: maintMessageInput.trim() || 'Portal Under Maintenance'
                      });
                      onAddLog(
                        maintEnabledInput ? 'MAINTENANCE_ENABLED' : 'MAINTENANCE_DISABLED',
                        maintEnabledInput
                          ? 'Admin ne Client Portal Maintenance Mode ON kiya'
                          : 'Admin ne Client Portal Maintenance Mode OFF kiya (Portal Live)'
                      );
                      cyberAlertSuccess(
                        maintEnabledInput ? 'Maintenance Mode Active' : 'Portal Live Ho Gaya',
                        maintEnabledInput
                          ? 'Client Portal ab Maintenance mode me hai. Clients ko notice nazar aayega.'
                          : 'Client Portal ab LIVE ho chuka hai.'
                      );
                    }
                    setShowMaintenanceModal(false);
                  }}
                  className={`px-5 py-2 text-slate-950 font-bold text-xs font-mono rounded-xl cursor-pointer transition-all shadow-md ${
                    maintEnabledInput ? 'bg-amber-400 hover:bg-amber-300' : 'bg-emerald-400 hover:bg-emerald-300'
                  }`}
                >
                  Save Settings
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* MODAL: STOCK ALLOCATION & DESTINATION */}
      {showAllocationModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md">
          <div className="bg-slate-900 border border-cyan-500/50 rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl flex flex-col font-mono text-xs">
            {/* Modal Header */}
            <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/80">
              <div className="flex items-center space-x-2.5">
                <div className="p-2 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400">
                  <Layers className="w-5 h-5" />
                </div>
                <div>
                  <h3 className="text-base font-bold text-white">Stock Allocation &amp; Destination</h3>
                  <p className="text-[11px] text-cyan-400 font-mono">
                    {parsedUploadAccounts.length} Mails Detect Huwi Hain &bull; Primary Domain: {parsedUploadAccounts[0]?.domain || 'basis5.ch'}
                  </p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => {
                  setShowAllocationModal(false);
                  setParsedUploadAccounts([]);
                }}
                className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-6 space-y-5">
              {/* Destination Selector Cards */}
              <div>
                <label className="block text-slate-300 font-bold mb-2">
                  Yeh Mails Kahan Daalni Hain? (Destination Select Karein):
                </label>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {/* Option 1: Available Stock */}
                  <div
                    onClick={() => setAllocDestination('available')}
                    className={`p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between ${
                      allocDestination === 'available'
                        ? 'bg-emerald-950/50 border-emerald-500 shadow-md shadow-emerald-500/10'
                        : 'bg-slate-950/50 border-slate-800 hover:border-slate-700'
                    }`}
                  >
                    <div>
                      <div className="flex items-center justify-between mb-2">
                        <span className="text-emerald-400 font-bold text-sm flex items-center space-x-1.5">
                          <CheckCircle2 className="w-4 h-4" />
                          <span>Available Stock</span>
                        </span>
                        <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                          Fresh Stock
                        </span>
                      </div>
                      <p className="text-[11px] text-slate-400 leading-relaxed">
                        Yeh mails client ke Available Stock me jayengi taake wo portal se naye orders extract kar sake.
                      </p>
                    </div>
                    <div className="mt-3 pt-2 border-t border-slate-800/80 text-[10px] text-slate-500">
                      Koi naya bill generate nahi hoga.
                    </div>
                  </div>

                  {/* Option 2: Sold Order */}
                  <div
                    onClick={() => setAllocDestination('sold')}
                    className={`p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between ${
                      allocDestination === 'sold'
                        ? 'bg-cyan-950/50 border-cyan-500 shadow-md shadow-cyan-500/10'
                        : 'bg-slate-950/50 border-slate-800 hover:border-slate-700'
                    }`}
                  >
                    <div>
                      <div className="flex items-center justify-between mb-2">
                        <span className="text-cyan-400 font-bold text-sm flex items-center space-x-1.5">
                          <ShoppingCart className="w-4 h-4" />
                          <span>Sold Order Record</span>
                        </span>
                        <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">
                          Delivered / Khata
                        </span>
                      </div>
                      <p className="text-[11px] text-slate-400 leading-relaxed">
                        Yeh mails already sale / deliver ho chuki hain. Inka Order banega, CSV downloadable hogi, aur Khata me add hoga.
                      </p>
                    </div>
                    <div className="mt-3 pt-2 border-t border-slate-800/80 text-[10px] text-emerald-400 font-bold">
                      Khata Bill me add ho jayega.
                    </div>
                  </div>
                </div>
              </div>

              {/* If Sold Order is Selected, show Order Details fields */}
              {allocDestination === 'sold' && (
                <div className="p-4 bg-slate-950/80 border border-cyan-500/30 rounded-xl space-y-3.5 animate__animated animate__fadeIn">
                  <div className="text-xs font-bold text-cyan-300 border-b border-slate-800 pb-2 flex items-center justify-between">
                    <span>Sold Order Details (Khata Information)</span>
                    <span className="text-[11px] text-slate-400 font-normal">Default Rate: Rs. 18/mail</span>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                      <label className="block text-slate-400 mb-1">Order Number</label>
                      <input
                        type="text"
                        required
                        value={allocOrderNumber}
                        onChange={(e) => setAllocOrderNumber(e.target.value)}
                        placeholder="e.g. Order #1"
                        className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white font-bold"
                      />
                    </div>

                    <div>
                      <label className="block text-slate-400 mb-1">Date &amp; Time (Delivered At)</label>
                      <input
                        type="datetime-local"
                        required
                        value={allocOrderDate}
                        onChange={(e) => setAllocOrderDate(e.target.value)}
                        className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                      <label className="block text-slate-400 mb-1">Rate Per Mail (PKR)</label>
                      <input
                        type="number"
                        min="1"
                        required
                        value={allocOrderRate}
                        onChange={(e) => setAllocOrderRate(parseInt(e.target.value, 10) || 18)}
                        className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white font-bold text-amber-300"
                      />
                    </div>

                    <div>
                      <label className="block text-slate-400 mb-1">Order Note / Reference (Optional)</label>
                      <input
                        type="text"
                        value={allocOrderNotes}
                        onChange={(e) => setAllocOrderNotes(e.target.value)}
                        placeholder="e.g. Delivery Batch"
                        className="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white"
                      />
                    </div>
                  </div>

                  {/* Live Calculation Banner */}
                  <div className="p-3 bg-emerald-950/50 border border-emerald-500/40 rounded-xl flex items-center justify-between">
                    <div>
                      <div className="text-[11px] text-slate-400">Total Khata Bill Addition:</div>
                      <div className="text-slate-300 text-xs font-mono">
                        {parsedUploadAccounts.length} Mails &times; Rs. {allocOrderRate}
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="text-base font-bold text-emerald-400 font-mono">
                        Rs. {(parsedUploadAccounts.length * allocOrderRate).toLocaleString()}
                      </div>
                      <div className="text-[10px] text-emerald-300/80">Net Khata Ledger</div>
                    </div>
                  </div>
                </div>
              )}

              {/* If Available is selected, simple confirmation info */}
              {allocDestination === 'available' && (
                <div className="p-3.5 bg-emerald-950/30 border border-emerald-500/30 rounded-xl text-xs text-slate-300 space-y-1">
                  <div className="font-bold text-emerald-300 flex items-center space-x-1.5">
                    <CheckCircle2 className="w-4 h-4 text-emerald-400" />
                    <span>Available Inventory Ingestion:</span>
                  </div>
                  <p className="text-slate-400 text-[11px]">
                    Tamam {parsedUploadAccounts.length} mails ko Available stock me shamil kiya jayega. Agar koi mail pehle se mojud hai to uska password update ho jayega aur status Available kar diya jayega.
                  </p>
                </div>
              )}
            </div>

            {/* Modal Footer Actions */}
            <div className="p-5 border-t border-slate-800 flex items-center justify-end space-x-3 bg-slate-950/60">
              <button
                type="button"
                onClick={() => {
                  setShowAllocationModal(false);
                  setParsedUploadAccounts([]);
                }}
                className="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-all cursor-pointer font-mono"
              >
                Cancel
              </button>
              <button
                type="button"
                disabled={isProcessing}
                onClick={handleConfirmAllocation}
                className={`px-6 py-2 rounded-xl font-bold font-mono text-slate-950 transition-all cursor-pointer flex items-center space-x-2 shadow-lg ${
                  allocDestination === 'sold'
                    ? 'bg-cyan-400 hover:bg-cyan-300 shadow-cyan-500/20'
                    : 'bg-emerald-400 hover:bg-emerald-300 shadow-emerald-500/20'
                }`}
              >
                <span>
                  {allocDestination === 'sold'
                    ? `Confirm & Save ${allocOrderNumber || 'Order'} (Rs. ${(parsedUploadAccounts.length * allocOrderRate).toLocaleString()})`
                    : `Confirm & Ingest ${parsedUploadAccounts.length} Mails to Available Stock`}
                </span>
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
