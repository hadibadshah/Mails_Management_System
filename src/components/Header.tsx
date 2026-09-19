import { Shield, LogOut, KeyRound } from 'lucide-react';
import { UserSession } from '../types';

interface HeaderProps {
  session: UserSession | null;
  onLogout: () => void;
}

export default function Header({ session, onLogout }: HeaderProps) {
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

        {/* Right action controls */}
        <div className="flex items-center space-x-3">
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
