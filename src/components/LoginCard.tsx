import React, { useState } from 'react';
import { Lock, User, Key, ArrowRight, ShieldCheck } from 'lucide-react';
import { ADMIN_USERNAME, ADMIN_PASSWORD, CLIENT_USERNAME, CLIENT_PASSWORD } from '../data/constants';
import { UserSession } from '../types';
import { cyberAlertError, cyberAlertSuccess } from '../utils/cyberSwal';

interface LoginCardProps {
  onLoginSuccess: (session: UserSession) => void;
}

export default function LoginCard({ onLoginSuccess }: LoginCardProps) {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const cleanUser = username.trim();

    if (!cleanUser || !password) {
      cyberAlertError('Missing Credentials', 'Please provide both username and password.');
      return;
    }

    setIsLoading(true);

    setTimeout(() => {
      setIsLoading(false);
      // Admin verification
      if (cleanUser.toLowerCase() === ADMIN_USERNAME.toLowerCase() && password === ADMIN_PASSWORD) {
        cyberAlertSuccess('Clearance Verified', `Welcome back Administrator <b>${ADMIN_USERNAME}</b>.`);
        onLoginSuccess({ username: ADMIN_USERNAME, role: 'admin' });
      }
      // Client verification
      else if (cleanUser.toLowerCase() === CLIENT_USERNAME.toLowerCase() && password === CLIENT_PASSWORD) {
        cyberAlertSuccess('Clearance Verified', `Welcome back Client <b>${CLIENT_USERNAME}</b>.`);
        onLoginSuccess({ username: CLIENT_USERNAME, role: 'client' });
      } else {
        cyberAlertError('Access Denied', 'Invalid username or password. Check credentials and retry.');
      }
    }, 400);
  };

  return (
    <div id="login-container" className="max-w-md mx-auto my-8 animate__animated animate__fadeIn">
      <div
        id="card-login-terminal"
        className="bg-slate-900/75 backdrop-blur-2xl border border-cyan-500/30 rounded-2xl p-8 relative overflow-hidden shadow-[0_0_40px_rgba(6,182,212,0.15)]"
      >
        {/* Glow accents */}
        <div className="absolute top-0 right-0 w-36 h-36 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none" />
        <div className="absolute bottom-0 left-0 w-36 h-36 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none" />

        {/* Security Crest */}
        <div className="text-center mb-8">
          <div
            id="login-crest"
            className="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-950/80 border border-cyan-500/40 text-cyan-400 mb-4 shadow-[0_0_20px_rgba(6,182,212,0.25)]"
          >
            <Lock className="w-8 h-8" />
          </div>
          <h2 className="text-2xl font-bold text-white tracking-tight">Hadi Digital</h2>
          <p className="text-xs text-slate-400 mt-1 font-mono">
            Portal Access - Secure Login
          </p>
        </div>

        {/* Form */}
        <form id="form-user-login" onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label
              htmlFor="input-login-username"
              className="block text-xs font-mono font-medium text-slate-300 mb-1.5 uppercase tracking-wider"
            >
              Username
            </label>
            <div className="relative">
              <span className="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                <User className="w-4 h-4" />
              </span>
              <input
                id="input-login-username"
                type="text"
                required
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                placeholder="Username likhein"
                className="w-full pl-10 pr-4 py-2.5 bg-slate-950/90 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 text-sm font-sans transition-all"
              />
            </div>
          </div>

          <div>
            <label
              htmlFor="input-login-password"
              className="block text-xs font-mono font-medium text-slate-300 mb-1.5 uppercase tracking-wider"
            >
              Password
            </label>
            <div className="relative">
              <span className="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                <Key className="w-4 h-4" />
              </span>
              <input
                id="input-login-password"
                type="password"
                required
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                placeholder="Password likhein"
                className="w-full pl-10 pr-4 py-2.5 bg-slate-950/90 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 text-sm font-sans transition-all"
              />
            </div>
          </div>

          <button
            id="btn-submit-clearance"
            type="submit"
            disabled={isLoading}
            className="w-full mt-2 py-3 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-sky-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-white font-bold text-xs font-mono tracking-widest uppercase shadow-[0_0_25px_rgba(6,182,212,0.35)] hover:shadow-cyan-500/50 transition-all flex items-center justify-center space-x-2 disabled:opacity-60 cursor-pointer"
          >
            {isLoading ? (
              <span>LOGIN HO RAHA HAI...</span>
            ) : (
              <>
                <span>LOGIN KAREIN</span>
                <ArrowRight className="w-4 h-4" />
              </>
            )}
          </button>
        </form>
      </div>
    </div>
  );
}
