export type AccountStatus = 'available' | 'downloaded' | 'replaced';

export interface EmailAccount {
  id: number;
  email: string;
  password: string;
  recovery_email: string;
  domain: string;
  status: AccountStatus;
  created_at: string;
  downloaded_at?: string | null;
  replaced_at?: string | null;
}

export type UserRole = 'admin' | 'client';

export interface UserSession {
  username: string;
  role: UserRole;
}

export interface DomainStock {
  domain: string;
  available: number;
  downloaded: number;
  replaced: number;
  total: number;
}

export interface AuditLog {
  id: number;
  username: string;
  role: string;
  action: string;
  details: string;
  timestamp: string;
}

export type ExportFormat = 'csv' | 'txt';

export interface ExtractedEmailItem {
  id?: number;
  email: string;
  password: string;
  recovery_email: string;
}

export interface OrderRecord {
  id: number;
  order_number: string; // e.g. "Order #1", "Order #2"
  client_username: string;
  domain: string;
  quantity: number;
  rate_per_mail?: number;
  total_price?: number;
  notes?: string;
  status?: 'delivered' | 'reverted' | 'cancelled';
  format: ExportFormat;
  created_at: string;
  accounts: ExtractedEmailItem[];
}

export interface PaymentRecord {
  id: number;
  client_username: string;
  amount: number;
  payment_date: string;
  payment_method: string;
  reference_note: string;
  created_at?: string;
}

export interface ReplacementRecord {
  id: number;
  email: string;
  domain: string;
  rate_deduction: number;
  reason: string;
  created_at: string;
  password?: string;
  recovery_email?: string;
}

export interface FinancialSummary {
  grossSoldMails: number;
  replacedMails: number;
  netActiveMails: number;
  grossBilledAmount: number;
  totalDeductions: number;
  netBilledAmount: number;
  totalPaidAmount: number;
  pendingBalance: number;
}

export interface MaintenanceSettings {
  enabled: boolean;
  message: string;
}

export function sortOrdersNaturally<T extends { order_number?: string; id?: number | string }>(ordersList: T[]): T[] {
  return [...ordersList].sort((a, b) => {
    const parseNum = (item: T) => {
      if (item.order_number) {
        const match = item.order_number.match(/\d+/);
        if (match) return parseInt(match[0], 10);
      }
      const idNum = Number(item.id);
      return isNaN(idNum) ? 999999 : idNum;
    };
    return parseNum(a) - parseNum(b);
  });
}

