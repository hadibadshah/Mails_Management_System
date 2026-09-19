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

export const HOSTINGER_BASE_URL = 'https://asim.eztoolbox.xyz';
export const HOSTINGER_API_URL = `${HOSTINGER_BASE_URL}/api.php`;
export const GITHUB_ACTIONS_URL = 'https://github.com/hadibadshah/email_valut/actions';

export async function pingHostinger(): Promise<{
  online: boolean;
  data: HostingerPingResponse | null;
  error?: string;
  latencyMs?: number;
}> {
  const startTime = Date.now();
  try {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 6000);

    const res = await fetch(`${HOSTINGER_API_URL}?action=ping&_t=${Date.now()}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
      signal: controller.signal,
    });

    clearTimeout(timeoutId);
    const latencyMs = Date.now() - startTime;

    if (!res.ok) {
      return {
        online: false,
        data: null,
        error: `Server responded with HTTP ${res.status}`,
        latencyMs,
      };
    }

    const data: HostingerPingResponse = await res.json();
    return {
      online: !!data.success,
      data,
      latencyMs,
    };
  } catch (err: any) {
    const latencyMs = Date.now() - startTime;
    return {
      online: false,
      data: null,
      error: err?.name === 'AbortError' ? 'Connection timed out' : err?.message || 'Failed to reach Hostinger',
      latencyMs,
    };
  }
}
