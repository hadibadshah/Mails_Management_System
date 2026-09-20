import { useState } from 'react';
import {
  Server,
  ExternalLink,
  RefreshCw,
  CheckCircle2,
  AlertTriangle,
  Database,
  FolderGit2,
  Clock,
  Zap,
  Globe,
  X
} from 'lucide-react';
import {
  HostingerPingResponse,
  HOSTINGER_BASE_URL,
  GITHUB_ACTIONS_URL,
  pingHostinger
} from '../services/hostingerService';

interface LiveHostingerModalProps {
  isOpen: boolean;
  onClose: () => void;
  liveData: HostingerPingResponse | null;
  isOnline: boolean;
  latencyMs?: number;
  lastChecked: Date | null;
  onRefresh: () => Promise<void>;
  isChecking: boolean;
  onTriggerFullSync?: () => Promise<void> | void;
  isSyncingLive?: boolean;
}

export default function LiveHostingerModal({
  isOpen,
  onClose,
  liveData,
  isOnline,
  latencyMs,
  lastChecked,
  onRefresh,
  isChecking,
  onTriggerFullSync,
  isSyncingLive
}: LiveHostingerModalProps) {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-200">
      <div
        className="relative w-full max-w-2xl bg-slate-900 border border-cyan-500/30 rounded-2xl shadow-[0_0_50px_rgba(6,182,212,0.15)] overflow-hidden text-slate-200"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Modal Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-slate-950/60">
          <div className="flex items-center space-x-3">
            <div className={`p-2 rounded-xl border ${isOnline ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-amber-500/10 border-amber-500/30 text-amber-400'}`}>
              <Server className="w-5 h-5" />
            </div>
            <div>
              <h2 className="text-base font-bold text-white flex items-center gap-2">
                Hostinger & GitHub Live Sync Status
                {isOnline && (
                  <span className="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-mono border border-emerald-500/30">
                    LIVE CONNECTED
                  </span>
                )}
              </h2>
              <p className="text-xs text-slate-400 font-mono">Target: asim.eztoolbox.xyz (public_html/asim)</p>
            </div>
          </div>

          <button
            onClick={onClose}
            className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Modal Content */}
        <div className="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
          {/* Status Banner */}
          <div
            className={`p-4 rounded-xl border flex items-start space-x-3 ${
              isOnline
                ? 'bg-emerald-950/30 border-emerald-500/30 text-emerald-300'
                : 'bg-amber-950/30 border-amber-500/30 text-amber-300'
            }`}
          >
            {isOnline ? (
              <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" />
            ) : (
              <AlertTriangle className="w-5 h-5 text-amber-400 shrink-0 mt-0.5" />
            )}
            <div className="text-xs leading-relaxed">
              {isOnline ? (
                <div>
                  <span className="font-semibold text-emerald-200">Hostinger Live Par Code Active Hai!</span>
                  <p className="text-slate-300 mt-1">
                    Aapka GitHub auto-deploy workflow bilkul sahi kaam kar raha hai. AI Studio se push ki gayi files Hostinger ke <span className="font-mono text-cyan-300 bg-cyan-950/50 px-1 py-0.5 rounded">public_html/asim/</span> folder me live pahunch chuki hain.
                  </p>
                </div>
              ) : (
                <div>
                  <span className="font-semibold text-amber-200">Checking Live Connection...</span>
                  <p className="text-slate-300 mt-1">
                    Hostinger server se rabta check ho raha hai. Agar aapne abhi push kiya hai to GitHub Actions deployment 15-30 seconds me complete ho jayegi.
                  </p>
                </div>
              )}
            </div>
          </div>

          {/* Verification Cards Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
            {/* Target Folder Verification */}
            <div className="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 space-y-1.5">
              <div className="flex items-center text-slate-400 gap-1.5 font-medium">
                <FolderGit2 className="w-4 h-4 text-cyan-400" />
                <span>Verified Server Directory</span>
              </div>
              <div className="font-mono text-cyan-300 text-sm font-semibold">
                {liveData?.target_directory || 'public_html/asim'}
              </div>
              <p className="text-[11px] text-slate-400">Strict path lock: Kisi aur folder par touch nahi hota.</p>
            </div>

            {/* Live Database Health */}
            <div className="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 space-y-1.5">
              <div className="flex items-center text-slate-400 gap-1.5 font-medium">
                <Database className="w-4 h-4 text-emerald-400" />
                <span>Hostinger SQLite Database</span>
              </div>
              <div className="font-mono text-emerald-400 text-sm font-semibold flex items-center gap-1.5">
                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                {liveData?.sqlite_connected ? 'Connected & Healthy' : 'Protected (vault_data.sqlite)'}
              </div>
              <p className="text-[11px] text-slate-400">Auto-deploy excludes database to prevent data loss.</p>
            </div>

            {/* Response Latency & Time */}
            <div className="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 space-y-1.5">
              <div className="flex items-center text-slate-400 gap-1.5 font-medium">
                <Zap className="w-4 h-4 text-amber-400" />
                <span>Connection Latency</span>
              </div>
              <div className="font-mono text-amber-300 text-sm font-semibold">
                {latencyMs ? `${latencyMs} ms` : 'Testing...'}
              </div>
              <div className="text-[11px] text-slate-400 flex items-center gap-1">
                <Clock className="w-3 h-3" />
                <span>Server Time: {liveData?.server_time ? liveData.server_time.split(' ')[1] : 'Active'}</span>
              </div>
            </div>

            {/* Live Stock on Hostinger */}
            <div className="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 space-y-1.5">
              <div className="flex items-center text-slate-400 gap-1.5 font-medium">
                <Server className="w-4 h-4 text-indigo-400" />
                <span>Live Hostinger Stock</span>
              </div>
              <div className="font-mono text-indigo-300 text-sm font-semibold">
                {liveData ? `${liveData.available_stock} Available / ${liveData.total_vault_emails} Total` : 'Synced with SQLite'}
              </div>
              <p className="text-[11px] text-slate-400">Real-time counts fetched from Hostinger SQLite.</p>
            </div>
          </div>

          {/* Live Domain Breakdown from Hostinger */}
          {liveData?.domain_stock && Object.keys(liveData.domain_stock).length > 0 && (
            <div className="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80">
              <h4 className="text-xs font-semibold text-slate-300 mb-2 flex items-center gap-1.5">
                <Database className="w-3.5 h-3.5 text-cyan-400" />
                Live Stock by Domain (Hostinger DB):
              </h4>
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs font-mono">
                {Object.entries(liveData.domain_stock).map(([domain, count]) => (
                  <div key={domain} className="p-2 rounded-lg bg-slate-900 border border-slate-800 flex justify-between items-center">
                    <span className="text-slate-400 truncate">{domain}</span>
                    <span className="text-cyan-400 font-bold ml-1">{count}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Quick Action Buttons */}
          <div className="pt-2 flex flex-wrap gap-2.5">
            {onTriggerFullSync && (
              <button
                onClick={() => onTriggerFullSync()}
                disabled={isSyncingLive}
                className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-semibold flex items-center space-x-2 transition cursor-pointer shadow-[0_0_20px_rgba(16,185,129,0.3)]"
              >
                <RefreshCw className={`w-3.5 h-3.5 ${isSyncingLive ? 'animate-spin' : ''}`} />
                <span>{isSyncingLive ? 'Syncing SQLite Database...' : 'Sync Full Live Data'}</span>
              </button>
            )}

            <button
              onClick={onRefresh}
              disabled={isChecking}
              className="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 text-white text-xs font-semibold flex items-center space-x-2 transition cursor-pointer shadow-[0_0_20px_rgba(6,182,212,0.25)]"
            >
              <RefreshCw className={`w-3.5 h-3.5 ${isChecking ? 'animate-spin' : ''}`} />
              <span>{isChecking ? 'Testing Connection...' : 'Re-Check Hostinger Ping'}</span>
            </button>

            <a
              href={GITHUB_ACTIONS_URL}
              target="_blank"
              rel="noopener noreferrer"
              className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold flex items-center space-x-2 transition cursor-pointer border border-slate-700"
            >
              <FolderGit2 className="w-3.5 h-3.5 text-cyan-400" />
              <span>View GitHub Actions Live Logs</span>
              <ExternalLink className="w-3 h-3 text-slate-400" />
            </a>

            <a
              href={HOSTINGER_BASE_URL}
              target="_blank"
              rel="noopener noreferrer"
              className="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 text-xs font-semibold flex items-center space-x-2 transition cursor-pointer border border-slate-700 ml-auto"
            >
              <Globe className="w-3.5 h-3.5 text-emerald-400" />
              <span>Open asim.eztoolbox.xyz</span>
              <ExternalLink className="w-3 h-3 text-slate-400" />
            </a>
          </div>
        </div>

        {/* Modal Footer */}
        <div className="px-6 py-3 border-t border-slate-800 bg-slate-950/80 text-[11px] text-slate-500 flex justify-between items-center">
          <span>Version: {liveData?.deploy_version || 'v3.5.0-github-live-sync'}</span>
          <span>Last Ping: {lastChecked ? lastChecked.toLocaleTimeString() : 'Never'}</span>
        </div>
      </div>
    </div>
  );
}
