import { ShieldAlert, RefreshCw, LogOut, Wrench, Clock, Lock } from 'lucide-react';

interface ClientMaintenanceScreenProps {
  message?: string;
  onRefresh?: () => void;
  onLogout?: () => void;
}

export default function ClientMaintenanceScreen({
  message = 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!',
  onRefresh,
  onLogout
}: ClientMaintenanceScreenProps) {
  return (
    <div className="max-w-2xl mx-auto my-12 animate-fadeIn font-sans">
      <div className="relative overflow-hidden rounded-2xl bg-slate-900/90 border border-amber-500/40 p-8 sm:p-12 shadow-2xl backdrop-blur-xl">
        {/* Ambient Glows */}
        <div className="absolute top-0 right-0 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none" />
        <div className="absolute bottom-0 left-0 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />

        {/* Center Animated Icon */}
        <div className="flex flex-col items-center text-center">
          <div className="relative mb-6">
            <div className="w-20 h-20 rounded-2xl bg-slate-950 border border-amber-500/50 flex items-center justify-center shadow-lg shadow-amber-500/20">
              <Wrench className="w-10 h-10 text-amber-400 animate-bounce" />
            </div>
            <div className="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-amber-500/20 border border-amber-400/80 flex items-center justify-center">
              <Clock className="w-4 h-4 text-amber-300 animate-spin" />
            </div>
          </div>

          {/* Status Badge */}
          <div className="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/40 text-amber-400 font-mono text-xs font-semibold mb-4 tracking-wider">
            <span className="w-2 h-2 rounded-full bg-amber-400 animate-ping" />
            <span>PORTAL UNDER SCHEDULED MAINTENANCE</span>
          </div>

          <h2 className="text-2xl sm:text-3xl font-bold text-white tracking-tight mb-3">
            Client Portal Is Currently Under Maintenance
          </h2>

          <div className="p-4 bg-slate-950/80 border border-slate-800 rounded-xl max-w-lg w-full text-slate-300 text-sm font-mono leading-relaxed mb-6">
            {message}
          </div>

          {/* Reassurance Grid */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-lg mb-8 text-left text-xs font-mono">
            <div className="p-3 bg-slate-950/60 border border-emerald-500/30 rounded-lg flex items-start space-x-2.5">
              <Lock className="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" />
              <div>
                <strong className="text-emerald-300 block">100% Data Protection:</strong>
                <span className="text-slate-400">Aapka tamam stock, khata aur orders bilkul mehfooz hain.</span>
              </div>
            </div>
            <div className="p-3 bg-slate-950/60 border border-cyan-500/30 rounded-lg flex items-start space-x-2.5">
              <ShieldAlert className="w-4 h-4 text-cyan-400 flex-shrink-0 mt-0.5" />
              <div>
                <strong className="text-cyan-300 block">System Upgrades:</strong>
                <span className="text-slate-400">Upgrades mukammal hote hi portal khud-ba-khud active ho jayega.</span>
              </div>
            </div>
          </div>

          {/* Action Buttons */}
          <div className="flex flex-wrap items-center justify-center gap-3">
            <button
              onClick={() => onRefresh ? onRefresh() : window.location.reload()}
              className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs font-mono flex items-center space-x-2 shadow-lg transition-all"
            >
              <RefreshCw className="w-4 h-4" />
              <span>Check Status / Refresh</span>
            </button>
            {onLogout && (
              <button
                onClick={onLogout}
                className="px-5 py-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-mono flex items-center space-x-2 transition-all"
              >
                <LogOut className="w-4 h-4 text-rose-400" />
                <span>Logout</span>
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
