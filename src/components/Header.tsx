import { Shield, LogOut, KeyRound, Server, Activity } from 'lucide-react';
import { UserSession } from '../types';

interface HeaderProps {
  session: UserSession | null;
  onLogout: () => void;
  onOpenLiveModal?: () => void;
  isHostingerOnline?: boolean;
  latencyMs?: number;
}

export default function Header({
  session,
  onLogout,
  onOpenLiveModal,
  isHostingerOnline,
  latencyMs
}: HeaderProps) {
  return (
    <header
      id="main-app-header"
      className="sticky top-0 z-40 bg-slate-950/80 backdrop-blur-xl border-b border-slate-800/80 px-4 sm:px-8 py-3.5 transition-all shadow-2xl"
    >
      <div className="max-w-7xl mx-auto flex items-center justify-between gap-4">
        {/* Logo & Brand Identity: Hadi Digital */}
        <div className="flex items-center space-x-3">
          <div
            id="brand-logo-badge"
            className="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 via-sky-500 to-indigo-600 flex items-center justify-center shadow-[0_0_20px_rgba(6,182,212,0.35)] border border-cyan-400/30"
          >
            <Shield className="w-5 h-5 text-white" />
          </div>
          <div>
            <div className="flex items-center space-x-2">
              <h1 className="text-lg sm:text-xl font-bold tracking-wide text-white">
                HADI <span className="text-cyan-400">DIGITAL</span>
              </h1>
            </div>
          </div>
        </div>

        {/* Center/Right Live Hostinger Sync Pill */}
        <div className="flex items-center space-x-3">
          {onOpenLiveModal && (
            <button
              onClick={onOpenLiveModal}
              title="Click to inspect Hostinger & GitHub Live Sync"
              className={`px-3 py-1.5 rounded-xl border text-xs font-mono flex items-center space-x-2 transition cursor-pointer shadow-sm ${
                isHostingerOnline
                  ? 'bg-emerald-950/40 hover:bg-emerald-900/50 border-emerald-500/40 text-emerald-300'
                  : 'bg-slate-900 hover:bg-slate-800 border-slate-700 text-slate-300'
              }`}
            >
              <span className="relative flex h-2 w-2">
                <span className={`animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${isHostingerOnline ? 'bg-emerald-400' : 'bg-amber-400'}`}></span>
                <span className={`relative inline-flex rounded-full h-2 w-2 ${isHostingerOnline ? 'bg-emerald-500' : 'bg-amber-500'}`}></span>
              </span>
              <span className="hidden sm:inline">
                {isHostingerOnline ? 'Hostinger Live: asim' : 'Hostinger Sync'}
              </span>
              {latencyMs !== undefined && latencyMs > 0 && (
                <span className="text-[10px] text-cyan-400 font-bold hidden md:inline">
                  {latencyMs}ms
                </span>
              )}
            </button>
          )}

          {/* Right action controls */}
          {session ? (
            <div className="flex items-center space-x-3">
              <div className="text-right hidden sm:block">
                <div className="text-xs font-semibold text-slate-200 flex items-center justify-end space-x-1">
                  <span>{session.username}</span>
                  <KeyRound className="w-3.5 h-3.5 text-cyan-400" />
                </div>
                <div className="text-[10px] uppercase font-mono tracking-wider text-cyan-400">
                  {session.role === 'admin' ? 'Admin (Hadi)' : 'Client (Rana Asim)'}
                </div>
              </div>

              <button
                id="btn-session-logout"
                type="button"
                onClick={onLogout}
                className="px-3.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-300 text-xs font-semibold flex items-center space-x-1.5 transition-all cursor-pointer"
              >
                <LogOut className="w-3.5 h-3.5" />
                <span>Logout</span>
              </button>
            </div>
          ) : (
            <div className="flex items-center space-x-2 text-xs font-mono text-slate-400">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Online Portal</span>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
