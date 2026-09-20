<?php
/**
 * Email Accounts Vault - High-Tech Portal
 * Clean ASCII / Standard UTF-8 - Hostinger Production Ready
 */

declare(strict_types=1);

define('VAULT_APP', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

Auth::initSession();
$currentUser = Auth::user();
$csrfToken = Auth::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hadi Digital | Email Account Distribution Portal</title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace']
                    },
                    colors: {
                        vault: {
                            bg: '#020617',
                            card: 'rgba(15, 23, 42, 0.75)',
                            border: 'rgba(51, 65, 85, 0.6)',
                            cyan: '#06b6d4',
                            glow: '#0ea5e9',
                            emerald: '#10b981',
                            amber: '#f59e0b',
                            rose: '#f43f5e'
                        }
                    },
                    boxShadow: {
                        'cyber-cyan': '0 0 25px -5px rgba(6, 182, 212, 0.3)',
                        'cyber-emerald': '0 0 25px -5px rgba(16, 185, 129, 0.3)',
                        'cyber-glow': '0 0 35px -5px rgba(14, 165, 233, 0.25)'
                    }
                }
            }
        };
    </script>
    
    <!-- Animate.css for smooth animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- SweetAlert2 (Dark Cyber Theme) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- particles.js -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

    <style>
        body {
            background-color: #020617;
            font-family: 'Poppins', sans-serif;
            color: #e2e8f0;
            overflow-x: hidden;
        }

        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.78);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(51, 65, 85, 0.65);
            box-shadow: 0 10px 35px -10px rgba(0, 0, 0, 0.5);
        }

        .glass-panel-glow {
            background: rgba(15, 23, 42, 0.82);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(6, 182, 212, 0.35);
            box-shadow: 0 0 30px -5px rgba(6, 182, 212, 0.15);
        }

        .custom-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #06b6d4;
        }

        /* SweetAlert2 Cyber Vault Custom Skin */
        .swal2-popup.cyber-swal {
            background: #0b1329 !important;
            border: 1px solid rgba(6, 182, 212, 0.4) !important;
            border-radius: 1rem !important;
            color: #f1f5f9 !important;
            box-shadow: 0 0 40px rgba(6, 182, 212, 0.25) !important;
            font-family: 'Poppins', sans-serif !important;
        }
        .cyber-swal .swal2-title {
            color: #38bdf8 !important;
            font-weight: 700 !important;
        }
        .cyber-swal .swal2-html-container {
            color: #cbd5e1 !important;
        }
        .cyber-swal .swal2-input {
            background: #020617 !important;
            border: 1px solid #334155 !important;
            color: #38bdf8 !important;
            font-family: 'JetBrains Mono', monospace !important;
            letter-spacing: 0.25em !important;
            font-size: 1.5rem !important;
            text-align: center !important;
            border-radius: 0.5rem !important;
        }
        .cyber-swal .swal2-input:focus {
            border-color: #06b6d4 !important;
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.4) !important;
        }
    </style>
</head>
<body class="relative min-h-screen selection:bg-cyan-500/30 selection:text-cyan-200">

    <!-- Interactive Particle Background -->
    <div id="particles-js"></div>

    <!-- Application Shell -->
    <div class="relative z-10 min-h-screen flex flex-col justify-between">
        
        <!-- Navigation Header -->
        <header class="sticky top-0 z-50 glass-panel border-b border-slate-800/80 px-4 lg:px-8 py-3.5 transition-all">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 via-sky-500 to-indigo-600 flex items-center justify-center shadow-cyber-cyan">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h1 class="text-lg font-bold tracking-wider text-white">HADI <span class="text-cyan-400">DIGITAL</span></h1>
                        </div>
                        <p class="text-xs text-slate-400 font-mono tracking-tight">Email Account Distribution Portal</p>
                    </div>
                </div>

                <!-- Session User Controls -->
                <div class="flex items-center space-x-3">
                    <div id="header-auth-badge" class="hidden items-center space-x-3">
                        <div class="text-right hidden sm:block">
                            <div id="header-user-name" class="text-sm font-semibold text-slate-200">User</div>
                            <div id="header-user-role" class="text-[10px] uppercase font-mono text-cyan-400 tracking-wider">Role</div>
                        </div>
                        <button onclick="VaultApp.logout()" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-rose-300 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 transition-all flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </div>

                    <div id="header-login-badge" class="flex items-center">
                        <span class="text-xs font-mono text-slate-400 flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>System Online</span>
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Workspace View Container -->
        <main class="flex-grow max-w-7xl w-full mx-auto px-4 lg:px-8 py-8">
            
            <!-- VIEW 1: AUTHENTICATION KEYCARD LOGIN -->
            <section id="view-login" class="max-w-md mx-auto my-12 animate__animated animate__fadeIn">
                <div class="glass-panel-glow rounded-2xl p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-800/80 border border-cyan-500/40 text-cyan-400 mb-4 shadow-cyber-cyan">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Security Clearance</h2>
                        <p class="text-xs text-slate-400 mt-1">Authenticate to access cryptographic account vault</p>
                    </div>

                    <form id="form-login" onsubmit="VaultApp.handleLogin(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-300 mb-1 uppercase tracking-wider">Access Identifier (Username)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input type="text" id="login-username" required class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 text-sm font-sans transition-all" placeholder="Enter username">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-mono font-medium text-slate-300 mb-1 uppercase tracking-wider">Passcode Key</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input type="password" id="login-password" required class="w-full pl-10 pr-4 py-2.5 bg-slate-950/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 text-sm font-sans transition-all" placeholder="Enter password">
                            </div>
                        </div>

                        <button type="submit" id="btn-login-submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-sky-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-white font-semibold text-sm tracking-wide shadow-cyber-cyan hover:shadow-cyan-500/50 transition-all flex items-center justify-center space-x-2">
                            <span>VERIFY & ENTER VAULT</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>
            </section>


            <!-- VIEW: CLIENT MAINTENANCE SCREEN -->
            <section id="view-maintenance" class="hidden space-y-6 animate__animated animate__fadeIn">
                <div class="glass-panel rounded-3xl p-8 sm:p-12 border border-amber-500/40 text-center max-w-2xl mx-auto shadow-2xl relative overflow-hidden">
                    <div class="absolute -right-16 -top-16 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="w-16 h-16 rounded-2xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center mx-auto mb-5 text-amber-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-mono bg-amber-500/20 text-amber-300 border border-amber-500/30 inline-block mb-3">
                        SYSTEM UPGRADE IN PROGRESS
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight mb-3">
                        Portal Under Maintenance
                    </h2>
                    <p id="maintenance-client-message" class="text-sm font-mono text-slate-300 bg-slate-950/70 p-4 rounded-xl border border-slate-800 leading-relaxed mb-6">
                        Hum naya stock aur system upgrades perform kar rahe hain. Jald hi portal live ho jayega.
                    </p>
                    <div class="text-xs font-mono text-slate-400 space-y-1 mb-6">
                        <p>Aapka pehla tamaam stock, orders, aur billing history bilkul mehfooz hain.</p>
                        <p class="text-amber-400/80">Baraye meharbani kuch dair baad dubara check karein.</p>
                    </div>
                    <div class="flex items-center justify-center space-x-3">
                        <button onclick="VaultApp.checkAuthStatus()" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs font-mono transition-all shadow-md cursor-pointer">
                            Check Status Again
                        </button>
                        <button onclick="VaultApp.logout()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono transition-all cursor-pointer">
                            Sign Out
                        </button>
                    </div>
                </div>
            </section>


            <!-- VIEW 2: CLIENT PORTAL (EXTRACTION, KHATA & CREDENTIAL SEARCH) -->
            <section id="view-client" class="hidden space-y-8 animate__animated animate__fadeIn">
                <!-- ADMIN LIVE PREVIEW BANNER (Visible only when Admin is testing client area) -->
                <div id="client-admin-test-banner" class="hidden p-4 rounded-2xl bg-indigo-950/80 border border-indigo-500/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-mono shadow-xl backdrop-blur-xl">
                    <div class="flex items-center space-x-2.5 text-indigo-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-ping"></span>
                        <div>
                            <strong class="text-white">ADMIN LIVE PREVIEW MODE:</strong>
                            <span class="text-indigo-200">Aap is waqt Client Portal (Rana Asim) ko test kar rahe hain. Maintenance status: <span id="preview-maint-status" class="font-bold text-amber-300">Active</span>.</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        <button onclick="VaultApp.toggleMaintenanceFromPreview()" id="preview-toggle-maint-btn" class="px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/40 text-amber-300 font-bold transition-all cursor-pointer">
                            Toggle Maintenance
                        </button>
                        <button onclick="VaultApp.exitClientPreview()" class="px-3.5 py-1.5 rounded-xl bg-indigo-500 hover:bg-indigo-400 text-slate-950 font-bold transition-all shadow-md cursor-pointer">
                            Back to Admin Panel &rarr;
                        </button>
                    </div>
                </div>

                <!-- Welcome Banner & Quick Statement -->
                <div class="glass-panel rounded-2xl p-6 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-xs font-mono uppercase tracking-widest text-emerald-400">Client Distribution & Khata Ledger</span>
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Active Stock & Financial Khata Hub</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Live inventory extraction, single mail credential lookup, and complete billing ledger.</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button onclick="VaultApp.downloadStatement()" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono flex items-center space-x-2 transition-all shadow-cyber-cyan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Download Khata Statement</span>
                        </button>
                        <button onclick="VaultApp.refreshAllData()" class="px-4 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-xs font-mono text-cyan-300 flex items-center space-x-2 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Live Sync</span>
                        </button>
                    </div>
                </div>

                <!-- CLIENT KHATA FINANCIAL LEDGER BAR -->
                <div>
                    <h3 class="text-xs font-mono text-slate-400 uppercase tracking-widest mb-3 flex items-center space-x-2">
                        <span>Client Khata & Billing Statement</span>
                        <span class="h-px flex-grow bg-slate-800"></span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5" id="client-khata-cards">
                        <div class="glass-panel rounded-xl p-4 border border-slate-800">
                            <div class="text-[10px] font-mono text-slate-400 uppercase">Total Delivered Mails</div>
                            <div id="ck-gross-mails" class="text-xl font-bold font-mono text-white mt-1">0</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">All delivered orders</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-amber-500/30 cursor-pointer hover:border-amber-400 transition-all" onclick="VaultApp.openInspectReplacementsModal()" title="Click to view replaced mails">
                            <div class="text-[10px] font-mono text-amber-400 uppercase flex items-center justify-between">
                                <span>Faulty Mails (Replaced)</span>
                                <span class="text-[10px] underline">View</span>
                            </div>
                            <div id="ck-replaced-mails" class="text-xl font-bold font-mono text-amber-300 mt-1">-0</div>
                            <div class="text-[10px] text-amber-400/80 font-mono mt-0.5">Deducted from bill</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-emerald-500/30">
                            <div class="text-[10px] font-mono text-emerald-400 uppercase">Total Billable Mails</div>
                            <div id="ck-net-mails" class="text-xl font-bold font-mono text-emerald-300 mt-1">0</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">Delivered minus faulty</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-cyan-500/30">
                            <div class="text-[10px] font-mono text-cyan-400 uppercase">Total Bill (PKR)</div>
                            <div id="ck-total-bill" class="text-xl font-bold font-mono text-cyan-300 mt-1">Rs. 0</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">Total net amount</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-indigo-500/30">
                            <div class="text-[10px] font-mono text-indigo-400 uppercase">Total Wasooli Received</div>
                            <div id="ck-total-paid" class="text-xl font-bold font-mono text-indigo-300 mt-1">Rs. 0</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">Received from client</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-rose-500/40 bg-rose-950/10" id="ck-balance-card">
                            <div class="text-[10px] font-mono text-rose-400 uppercase font-bold">Baqaya (Pending Balance)</div>
                            <div id="ck-pending-balance" class="text-xl font-bold font-mono text-rose-300 mt-1">Rs. 0</div>
                            <div id="ck-balance-status" class="text-[10px] text-rose-400 font-mono mt-0.5">Payable balance</div>
                        </div>
                    </div>
                </div>

                <!-- REAL-TIME STOCK METRICS (Specified for basis5.ch and adlover.site) -->
                <div>
                    <h3 class="text-xs font-mono text-slate-400 uppercase tracking-widest mb-3 flex items-center space-x-2">
                        <span>Real-Time Inventory Status</span>
                        <span class="h-px flex-grow bg-slate-800"></span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="client-stock-grid">
                        <!-- Stock cards injected dynamically -->
                    </div>
                </div>

                <!-- DUAL OPERATIONAL TERMINALS: EXTRACTION + (REPLACED CARD & CREDENTIAL FINDER) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- TERMINAL A: EXTRACTION FORM (7 cols) -->
                    <div class="lg:col-span-7 glass-panel-glow rounded-2xl p-6 lg:p-8 relative">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Account Extraction Engine</span>
                                </h3>
                                <p class="text-xs text-slate-400 mt-1">Select domain, specify quantity, and unlock with PIN.</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-mono uppercase bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">PIN Protected</span>
                        </div>

                        <form id="form-extract" onsubmit="VaultApp.handleExtractSubmit(event)" class="space-y-5">
                            <!-- Domain Selection -->
                            <div>
                                <label class="block text-xs font-mono text-slate-300 mb-2 uppercase tracking-wider">Select Managed Domain</label>
                                <div class="grid grid-cols-2 gap-3" id="extract-domain-options">
                                    <!-- Populated dynamically -->
                                </div>
                            </div>

                            <!-- Quantity Input + Quick Selector Chips -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-mono text-slate-300 uppercase tracking-wider">Extraction Quantity</label>
                                    <span id="extract-stock-hint" class="text-xs font-mono text-cyan-400">Available: --</span>
                                </div>
                                <div class="relative">
                                    <input type="number" id="extract-quantity" min="1" required class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-700 rounded-xl text-white font-mono text-lg focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all" placeholder="e.g. 10">
                                </div>
                                <div class="flex items-center space-x-2 mt-2.5">
                                    <button type="button" onclick="VaultApp.setExtractQty(5)" class="px-2.5 py-1 rounded-lg bg-slate-800/80 hover:bg-cyan-950/50 border border-slate-700/80 text-xs font-mono text-slate-300 hover:text-cyan-300 transition-all">+5</button>
                                    <button type="button" onclick="VaultApp.setExtractQty(10)" class="px-2.5 py-1 rounded-lg bg-slate-800/80 hover:bg-cyan-950/50 border border-slate-700/80 text-xs font-mono text-slate-300 hover:text-cyan-300 transition-all">+10</button>
                                    <button type="button" onclick="VaultApp.setExtractQty(25)" class="px-2.5 py-1 rounded-lg bg-slate-800/80 hover:bg-cyan-950/50 border border-slate-700/80 text-xs font-mono text-slate-300 hover:text-cyan-300 transition-all">+25</button>
                                </div>
                            </div>

                            <!-- Format Selection -->
                            <div>
                                <label class="block text-xs font-mono text-slate-300 mb-2 uppercase tracking-wider">Export Format</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="export-format" value="csv" checked class="peer sr-only">
                                        <div class="p-3.5 rounded-xl border border-slate-700/80 bg-slate-950/40 peer-checked:border-cyan-400 peer-checked:bg-cyan-950/20 transition-all flex items-center justify-between">
                                            <div>
                                                <div class="text-sm font-semibold text-white">CSV File</div>
                                                <div class="text-[11px] text-slate-400 font-mono">Comma Separated</div>
                                            </div>
                                            <span class="text-xs font-mono text-cyan-400 font-bold">.csv</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="export-format" value="txt" class="peer sr-only">
                                        <div class="p-3.5 rounded-xl border border-slate-700/80 bg-slate-950/40 peer-checked:border-cyan-400 peer-checked:bg-cyan-950/20 transition-all flex items-center justify-between">
                                            <div>
                                                <div class="text-sm font-semibold text-white">Text File</div>
                                                <div class="text-[11px] text-slate-400 font-mono">Plain Text (Exact Header)</div>
                                            </div>
                                            <span class="text-xs font-mono text-cyan-400 font-bold">.txt</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Notice on Header Spec -->
                            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-[11px] font-mono text-slate-400 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Exported files will strictly feature header: <span class="text-cyan-300 font-bold">Email, Password, Recovery Mail</span></span>
                            </div>

                            <!-- Extraction Action Button -->
                            <button type="submit" id="btn-extract" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-600 hover:from-cyan-400 hover:to-sky-500 text-white font-bold text-sm tracking-wide shadow-cyber-cyan hover:shadow-cyan-500/50 transition-all flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>UNLOCK WITH PIN & DOWNLOAD</span>
                            </button>
                        </form>
                    </div>

                    <!-- TERMINAL B: REPLACED CARD & CREDENTIAL FINDER (5 cols) -->
                    <div class="lg:col-span-5 space-y-6">
                        
                        <!-- CARD 1: REPLACED / DEDUCTED ACCOUNTS CARD -->
                        <div class="glass-panel rounded-2xl p-6 relative border border-amber-500/30">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                                <div class="flex items-center space-x-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    <h3 class="text-base font-bold text-white font-mono">Replaced / Faulty Accounts</h3>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-amber-500/10 text-amber-300 border border-amber-500/20">Auto Deducted</span>
                            </div>

                            <div class="flex items-center justify-between bg-slate-950/60 p-4 rounded-xl border border-slate-800/80 mb-4">
                                <div>
                                    <div class="text-[11px] font-mono text-slate-400 uppercase">Total Deducted Mails</div>
                                    <div id="client-rep-card-count" class="text-3xl font-extrabold font-mono text-amber-300 mt-0.5">0</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] font-mono text-slate-400 uppercase">Bill Credit</div>
                                    <div id="client-rep-card-amount" class="text-lg font-bold font-mono text-emerald-400 mt-0.5">-Rs. 0</div>
                                </div>
                            </div>

                            <p class="text-xs text-slate-400 font-mono mb-4 leading-relaxed">
                                Jo faulty mails record mein add ki gayi hain, unki quantity total sale se aur unki payment aapke total bill se automatically minus (deduct) ho chuki hai.
                            </p>

                            <button type="button" onclick="VaultApp.openInspectReplacementsModal()" class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 border border-amber-500/30 hover:border-amber-400 text-amber-300 font-mono text-xs font-bold transition-all flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>VIEW ALL REPLACED MAILS</span>
                            </button>
                        </div>

                        <!-- CARD 2: SINGLE MAIL CREDENTIAL FINDER -->
                        <div class="glass-panel-glow rounded-2xl p-6 relative">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                                <div>
                                    <h3 class="text-base font-bold text-white flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <span>Mail Credential Finder</span>
                                    </h3>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Apni kisi bhi email ka Password & Recovery yahan talash karein.</p>
                                </div>
                            </div>

                            <form onsubmit="VaultApp.handleMailSearch(event, 'client')" class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <input type="email" id="client-search-mail-input" required placeholder="Enter purchased email address..." class="flex-1 px-3.5 py-2 bg-slate-950/90 border border-slate-700 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
                                    <button type="submit" class="px-4 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono rounded-xl transition-all shadow-sm">
                                        Search
                                    </button>
                                </div>
                            </form>

                            <!-- Result Display Box -->
                            <div id="client-search-result" class="mt-4 hidden">
                                <!-- Injected dynamically -->
                            </div>
                        </div>

                    </div>

                </div>

                <!-- CLIENT EXTRACTION ORDERS HISTORY -->
                <div class="glass-panel rounded-2xl p-6 lg:p-8 relative">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                <span>Orders History (Purchased Batches)</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-1">Har delivery ka order-wise record. Aap kisi bhi order ki mails inspect aur dobara download kar sakte hain.</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="button" onclick="VaultApp.loadOrders()" class="px-3.5 py-1.5 rounded-lg bg-slate-800/90 hover:bg-slate-700 border border-slate-700 text-xs font-mono text-cyan-300 flex items-center space-x-1.5 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Refresh Orders</span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scroll">
                        <table class="w-full text-left text-xs font-mono text-slate-300">
                            <thead class="bg-slate-950/70 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="p-3">Order #</th>
                                    <th class="p-3">Date & Time</th>
                                    <th class="p-3">Domain</th>
                                    <th class="p-3">Quantity</th>
                                    <th class="p-3">Rate (PKR)</th>
                                    <th class="p-3">Total Amount</th>
                                    <th class="p-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="client-orders-tbody" class="divide-y divide-slate-800/60">
                                <tr>
                                    <td colspan="7" class="p-6 text-center text-slate-500">Orders history load ho rahi hai...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- CLIENT PAYMENTS HISTORY (WASOOLI LEDGER) -->
                <div class="glass-panel rounded-2xl p-6 lg:p-8 relative">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Payments Received (Wasooli Record)</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-1">Aap ki taraf se ki gayi tamam payments ka authenticated record.</p>
                        </div>
                        <button type="button" onclick="VaultApp.loadPayments()" class="px-3.5 py-1.5 rounded-lg bg-slate-800/90 hover:bg-slate-700 border border-slate-700 text-xs font-mono text-emerald-300 flex items-center space-x-1.5 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Refresh Payments</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto custom-scroll">
                        <table class="w-full text-left text-xs font-mono text-slate-300">
                            <thead class="bg-slate-950/70 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="p-3">Receipt #</th>
                                    <th class="p-3">Payment Date</th>
                                    <th class="p-3">Payment Method</th>
                                    <th class="p-3">Amount Received</th>
                                    <th class="p-3">Reference / Note</th>
                                    <th class="p-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody id="client-payments-tbody" class="divide-y divide-slate-800/60">
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-500">Payments record load ho raha hai...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>


            <!-- VIEW 3: ADMIN CONSOLE (INGESTION, ORDERS, PAYMENTS, REPLACEMENTS & SEARCH) -->
            <section id="view-admin" class="hidden space-y-8 animate__animated animate__fadeIn">
                <!-- Welcome Banner & Quick Action Bar -->
                <div class="glass-panel rounded-2xl p-6 relative overflow-hidden flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                            <span class="text-xs font-mono uppercase tracking-widest text-cyan-400">Master Administrator Console</span>
                        </div>
                        <h2 class="text-2xl font-bold text-white tracking-tight">Billing Ledger, Stock & Client Management</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Manage stock allocation, order deliveries, payments (wasooli), and deduction ledgers.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <button type="button" onclick="VaultApp.openMaintenanceModal()" id="btn-admin-maint-status" class="px-3.5 py-2 rounded-xl border text-xs font-mono flex items-center space-x-2 transition-all shadow-sm bg-emerald-950/50 border-emerald-500/40 text-emerald-400 hover:bg-emerald-900/60 cursor-pointer" title="Manage Client Area Maintenance Mode">
                            <span id="dot-admin-maint-status" class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span id="label-admin-maint-status">Client: LIVE</span>
                        </button>
                        <button type="button" onclick="VaultApp.enterClientPreview()" class="px-3.5 py-2 rounded-xl bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/40 text-indigo-300 font-bold text-xs font-mono flex items-center space-x-1.5 transition-all shadow-sm cursor-pointer" title="Test Client Portal with Admin Clearance">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Test Client View</span>
                        </button>
                        <button type="button" onclick="VaultApp.openIngestOrderModal()" class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono flex items-center space-x-1.5 transition-all shadow-cyber-cyan cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Ingest Order</span>
                        </button>
                        <button type="button" onclick="VaultApp.openRecordPaymentModal()" class="px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs font-mono flex items-center space-x-1.5 transition-all shadow-sm cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>+ Record Payment</span>
                        </button>
                        <button type="button" onclick="VaultApp.openAddReplacementsModal()" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs font-mono flex items-center space-x-1.5 transition-all shadow-sm cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>+ Add Replacements</span>
                        </button>
                        <button type="button" onclick="VaultApp.downloadStatement()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-mono flex items-center space-x-1.5 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Statement</span>
                        </button>
                    </div>
                </div>

                <!-- ADMIN NOTICE: MAINTENANCE MODE ACTIVE -->
                <div id="admin-maint-active-alert" class="hidden p-4 rounded-2xl bg-amber-950/70 border border-amber-500/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-mono shadow-xl backdrop-blur-xl">
                    <div class="flex items-center space-x-2.5 text-amber-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping flex-shrink-0"></span>
                        <div>
                            <strong class="text-white">CLIENT PORTAL UNDER MAINTENANCE:</strong>
                            <span class="text-amber-200">Aam clients ko maintenance screen nazar aa rahi hai. Aap "Test Client View" se portal live test kar sakte hain.</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 flex-shrink-0">
                        <button type="button" onclick="VaultApp.enterClientPreview()" class="px-3 py-1.5 rounded-xl bg-indigo-500 hover:bg-indigo-400 text-white font-bold transition-all shadow-md cursor-pointer">
                            Test Client Area
                        </button>
                        <button type="button" onclick="VaultApp.quickDisableMaintenance()" class="px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold transition-all shadow-md cursor-pointer">
                            Live Kar Dein
                        </button>
                    </div>
                </div>

                <!-- ADMIN KHATA FINANCIAL LEDGER BAR -->
                <div>
                    <h3 class="text-xs font-mono text-slate-400 uppercase tracking-widest mb-3 flex items-center space-x-2">
                        <span>Financial Summary & Ledger Balance (Client: Rana Asim)</span>
                        <span class="h-px flex-grow bg-slate-800"></span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5" id="admin-khata-cards">
                        <div class="glass-panel rounded-xl p-4 border border-slate-800">
                            <div class="text-[10px] font-mono text-slate-400 uppercase">Total Delivered Mails</div>
                            <div id="ak-gross-mails" class="text-xl font-bold font-mono text-white mt-1">0</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">All orders total</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-amber-500/30 cursor-pointer hover:border-amber-400 transition-all" onclick="VaultApp.setAdminTab('replacements')" title="Click to view & manage replacements">
                            <div class="text-[10px] font-mono text-amber-400 uppercase flex items-center justify-between">
                                <span>Faulty Mails (Replaced)</span>
                                <span class="text-[10px] underline">Manage</span>
                            </div>
                            <div id="ak-replaced-mails" class="text-xl font-bold font-mono text-amber-300 mt-1">-0</div>
                            <div class="text-[10px] text-amber-400/80 font-mono mt-0.5">Deducted from bill</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-emerald-500/30">
                            <div class="text-[10px] font-mono text-emerald-400 uppercase">Total Billable Mails</div>
                            <div id="ak-net-mails" class="text-xl font-bold font-mono text-emerald-300 mt-1">0</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">Delivered minus faulty</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-cyan-500/30">
                            <div class="text-[10px] font-mono text-cyan-400 uppercase">Total Bill (PKR)</div>
                            <div id="ak-total-bill" class="text-xl font-bold font-mono text-cyan-300 mt-1">Rs. 0</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">Total bill payable</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-indigo-500/30 cursor-pointer hover:border-indigo-400 transition-all" onclick="VaultApp.setAdminTab('payments')" title="Click to view & manage payments">
                            <div class="text-[10px] font-mono text-indigo-400 uppercase flex items-center justify-between">
                                <span>Total Wasooli Received</span>
                                <span class="text-[10px] underline">Manage</span>
                            </div>
                            <div id="ak-total-paid" class="text-xl font-bold font-mono text-indigo-300 mt-1">Rs. 0</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">Received payments</div>
                        </div>
                        <div class="glass-panel rounded-xl p-4 border border-rose-500/40 bg-rose-950/10" id="ak-balance-card">
                            <div class="text-[10px] font-mono text-rose-400 uppercase font-bold">Baqaya (Pending Balance)</div>
                            <div id="ak-pending-balance" class="text-xl font-bold font-mono text-rose-300 mt-1">Rs. 0</div>
                            <div id="ak-balance-status" class="text-[10px] text-rose-400 font-mono mt-0.5">Payable balance</div>
                        </div>
                    </div>
                </div>

                <!-- ADMIN NAVIGATION TABS (5 TABS) -->
                <div class="flex flex-wrap items-center gap-2 bg-slate-950/80 p-1.5 rounded-xl border border-slate-800 w-fit">
                    <button type="button" id="tab-btn-inventory" onclick="VaultApp.setAdminTab('inventory')" class="px-3.5 py-2 rounded-lg text-xs font-mono font-bold transition-all bg-cyan-500 text-slate-950 flex items-center space-x-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Stock & CSV Ingestion</span>
                    </button>
                    <button type="button" id="tab-btn-orders" onclick="VaultApp.setAdminTab('orders')" class="px-3.5 py-2 rounded-lg text-xs font-mono font-bold transition-all text-slate-400 hover:text-white flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Orders & Ledger Hub</span>
                        <span id="admin-orders-badge" class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-800 text-cyan-400 border border-slate-700">0</span>
                    </button>
                    <button type="button" id="tab-btn-payments" onclick="VaultApp.setAdminTab('payments')" class="px-3.5 py-2 rounded-lg text-xs font-mono font-bold transition-all text-slate-400 hover:text-white flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Payments (Wasooli) Tracker</span>
                        <span id="admin-payments-badge" class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-800 text-emerald-400 border border-slate-700">0</span>
                    </button>
                    <button type="button" id="tab-btn-replacements" onclick="VaultApp.setAdminTab('replacements')" class="px-3.5 py-2 rounded-lg text-xs font-mono font-bold transition-all text-slate-400 hover:text-white flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span>Faulty Deductions</span>
                        <span id="admin-replacements-badge" class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-800 text-amber-400 border border-slate-700">0</span>
                    </button>
                    <button type="button" id="tab-btn-search" onclick="VaultApp.setAdminTab('search')" class="px-3.5 py-2 rounded-lg text-xs font-mono font-bold transition-all text-slate-400 hover:text-white flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Credential Finder</span>
                    </button>
                </div>

                <!-- ADMIN TAB 1: INVENTORY & INGESTION -->
                <div id="admin-panel-inventory" class="space-y-8">
                    <!-- OVERALL SYSTEM COUNTERS -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" id="admin-overall-stats">
                        <div class="glass-panel rounded-xl p-5">
                            <div class="text-[11px] font-mono text-slate-400 uppercase">Total Stored</div>
                            <div id="stat-total" class="text-2xl font-bold font-mono text-white mt-1">--</div>
                        </div>
                        <div class="glass-panel rounded-xl p-5 border-emerald-500/30">
                            <div class="text-[11px] font-mono text-emerald-400 uppercase">Available Stock</div>
                            <div id="stat-available" class="text-2xl font-bold font-mono text-emerald-300 mt-1">--</div>
                        </div>
                        <div class="glass-panel rounded-xl p-5 border-cyan-500/30">
                            <div class="text-[11px] font-mono text-cyan-400 uppercase">Extracted / Downloaded</div>
                            <div id="stat-downloaded" class="text-2xl font-bold font-mono text-cyan-300 mt-1">--</div>
                        </div>
                        <div class="glass-panel rounded-xl p-5 border-amber-500/30">
                            <div class="text-[11px] font-mono text-amber-400 uppercase">Replaced (Faulty)</div>
                            <div id="stat-replaced" class="text-2xl font-bold font-mono text-amber-300 mt-1">--</div>
                        </div>
                    </div>

                    <!-- CSV INGESTION HUB -->
                    <div class="glass-panel-glow rounded-2xl p-6 lg:p-8 relative">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <span>CSV Inventory Ingestion Terminal</span>
                                </h3>
                                <p class="text-xs text-slate-400 mt-1">Format required: 3 Columns (<span class="text-cyan-300 font-mono">Email, Password, Recovery Mail</span>). Domains auto-extracted, duplicates skipped.</p>
                            </div>
                            <button type="button" onclick="VaultApp.loadSampleCsv()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono border border-slate-700 transition-all flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                <span>Insert Sample Data</span>
                            </button>
                        </div>

                        <form id="form-csv-upload" onsubmit="VaultApp.handleCsvUpload(event)" class="space-y-5">
                            <!-- Dropzone / File Picker -->
                            <div id="dropzone" onclick="document.getElementById('csv-file-input').click()" class="border-2 border-dashed border-slate-700 hover:border-cyan-400/80 rounded-2xl p-6 text-center cursor-pointer bg-slate-950/40 hover:bg-cyan-950/10 transition-all">
                                <input type="file" id="csv-file-input" accept=".csv,.txt" onchange="VaultApp.handleFileSelect(event)" class="hidden">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-800/80 text-cyan-400 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div id="file-chosen-label" class="text-sm font-semibold text-white">Click or drag a CSV file here</div>
                                <div class="text-xs text-slate-500 font-mono mt-1">Supports standard CSV (.csv) or plain text (.txt)</div>
                            </div>

                            <!-- Raw Textarea Option -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-xs font-mono text-slate-300 uppercase tracking-wider">Or Paste Raw CSV Data Directly</label>
                                    <span class="text-[11px] font-mono text-slate-500">Email, Password, Recovery Mail</span>
                                </div>
                                <textarea id="csv-text-input" rows="5" class="w-full p-3.5 bg-slate-950/80 border border-slate-700 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 custom-scroll transition-all" placeholder="Email, Password, Recovery Mail&#10;user1@basis5.ch,Secret123!,rec1@gmail.com&#10;user2@adlover.site,Pass456!,rec2@gmail.com"></textarea>
                            </div>

                            <!-- Stock Destination Allocation Notice -->
                            <div class="p-3.5 bg-slate-900/80 border border-cyan-800/50 rounded-xl flex items-center justify-between text-xs font-mono">
                                <div class="flex items-center space-x-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                                    <span class="text-slate-300"><b>Smart Destination Allocation:</b> Submit karne par window khulegi jahan aap decide kareinge ke stock <b>Available</b> me daalna hai ya <b>Sold (Delivered)</b> order me.</span>
                                </div>
                            </div>

                            <button type="submit" id="btn-upload-csv" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-white font-bold text-sm tracking-wide shadow-cyber-cyan hover:shadow-cyan-500/50 transition-all flex items-center justify-center space-x-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>PROCESS MAILS &amp; CHOOSE DESTINATION (AVAILABLE VS SOLD)</span>
                            </button>
                        </form>
                    </div>

                    <!-- INVENTORY ACCOUNTS TABLE -->
                    <div class="glass-panel rounded-2xl p-6 lg:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-white">Live Inventory Explorer</h3>
                                <p class="text-xs text-slate-400">Real-time status of stored email credentials</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <select id="filter-domain" onchange="VaultApp.loadAdminAccounts()" class="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-slate-300 focus:outline-none focus:border-cyan-400">
                                    <option value="">All Domains</option>
                                    <option value="basis5.ch">basis5.ch</option>
                                    <option value="adlover.site">adlover.site</option>
                                </select>
                                <select id="filter-status" onchange="VaultApp.loadAdminAccounts()" class="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-slate-300 focus:outline-none focus:border-cyan-400">
                                    <option value="">All Statuses</option>
                                    <option value="available">Available</option>
                                    <option value="downloaded">Downloaded</option>
                                    <option value="replaced">Replaced</option>
                                </select>
                            </div>
                        </div>

                        <div class="overflow-x-auto custom-scroll">
                            <table class="w-full text-left text-xs text-slate-300">
                                <thead class="bg-slate-950/70 text-slate-400 font-mono uppercase tracking-wider border-b border-slate-800">
                                    <tr>
                                        <th class="p-3">#ID</th>
                                        <th class="p-3">Email Account</th>
                                        <th class="p-3">Domain</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3">Ingested At</th>
                                        <th class="p-3">Action History</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="admin-accounts-tbody" class="divide-y divide-slate-800/60 font-mono">
                                    <tr>
                                        <td colspan="7" class="p-6 text-center text-slate-500">Loading accounts inventory...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ADMIN TAB 2: CLIENT ORDERS CONSOLE & KHATA -->
                <div id="admin-panel-orders" class="hidden space-y-6">
                    <!-- Client Orders Table -->
                    <div class="glass-panel rounded-2xl p-6 lg:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    <span>Client Orders & Delivery Hub</span>
                                </h3>
                                <p class="text-xs text-slate-400 mt-1">Yahan aap delivered order batches aur automated orders dono manage kar sakte hain.</p>
                            </div>
                            <div class="flex items-center space-x-2.5">
                                <input type="text" id="admin-orders-search" oninput="VaultApp.renderAdminOrders()" placeholder="Search order # or domain..." class="px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-slate-300 focus:outline-none focus:border-cyan-400 w-44 sm:w-56">
                                <button type="button" onclick="VaultApp.cleanupDuplicateOrders()" class="px-3 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 text-xs font-mono border border-amber-500/30 flex items-center space-x-1 transition-all" title="Scan and Clean Duplicate Orders">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Clean Duplicates</span>
                                </button>
                                <button type="button" onclick="VaultApp.openIngestOrderModal()" class="px-3 py-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono flex items-center space-x-1 transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Add Order</span>
                                </button>
                                <button type="button" onclick="VaultApp.loadOrders()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-300 text-xs font-mono border border-slate-700 flex items-center space-x-1 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto custom-scroll">
                            <table class="w-full text-left text-xs font-mono text-slate-300">
                                <thead class="bg-slate-950/70 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                    <tr>
                                        <th class="p-3">Order #</th>
                                        <th class="p-3">Date & Time</th>
                                        <th class="p-3">Domain</th>
                                        <th class="p-3">Quantity</th>
                                        <th class="p-3">Rate (PKR)</th>
                                        <th class="p-3">Total Price</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3">Description / Note</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="admin-orders-tbody" class="divide-y divide-slate-800/60">
                                    <tr>
                                        <td colspan="8" class="p-6 text-center text-slate-500">Orders load ho rahe hain...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ADMIN TAB 3: PAYMENTS (WASOOLI) TRACKER -->
                <div id="admin-panel-payments" class="hidden space-y-6">
                    <div class="glass-panel rounded-2xl p-6 lg:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Payments Tracker (Wasooli Ledger)</span>
                                </h3>
                                <p class="text-xs text-slate-400 mt-1">Client ki taraf se received tamam payments ka record with date, payment method, aur reference note.</p>
                            </div>
                            <div class="flex items-center space-x-2.5">
                                <button type="button" onclick="VaultApp.openRecordPaymentModal()" class="px-3.5 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs font-mono flex items-center space-x-1.5 transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Record Payment</span>
                                </button>
                                <button type="button" onclick="VaultApp.loadPayments()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-300 text-xs font-mono border border-slate-700 flex items-center space-x-1 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto custom-scroll">
                            <table class="w-full text-left text-xs font-mono text-slate-300">
                                <thead class="bg-slate-950/70 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                    <tr>
                                        <th class="p-3">Receipt #</th>
                                        <th class="p-3">Payment Date</th>
                                        <th class="p-3">Client</th>
                                        <th class="p-3">Method</th>
                                        <th class="p-3">Amount Received</th>
                                        <th class="p-3">Reference / Note</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="admin-payments-tbody" class="divide-y divide-slate-800/60">
                                    <tr>
                                        <td colspan="7" class="p-6 text-center text-slate-500">Payments record load ho raha hai...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ADMIN TAB 4: REPLACED ACCOUNTS & DEDUCTIONS -->
                <div id="admin-panel-replacements" class="hidden space-y-6">
                    <div class="glass-panel rounded-2xl p-6 lg:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    <span>Faulty Accounts (Direct Bill Deductions)</span>
                                </h3>
                                <p class="text-xs text-slate-400 mt-1">Yahan add ki gayi faulty mails client ke total bill aur sale quantity se direct minus ho jati hain.</p>
                            </div>
                            <div class="flex items-center space-x-2.5">
                                <button type="button" onclick="VaultApp.openAddReplacementsModal()" class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs font-mono flex items-center space-x-1.5 transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>+ Add Faulty Mails</span>
                                </button>
                                <button type="button" onclick="VaultApp.loadReplacements()" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 text-xs font-mono border border-slate-700 flex items-center space-x-1 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto custom-scroll">
                            <table class="w-full text-left text-xs font-mono text-slate-300">
                                <thead class="bg-slate-950/70 text-slate-400 uppercase tracking-wider border-b border-slate-800">
                                    <tr>
                                        <th class="p-3">#ID</th>
                                        <th class="p-3">Faulty Email</th>
                                        <th class="p-3">Domain</th>
                                        <th class="p-3">Deduction Rate</th>
                                        <th class="p-3">Reason</th>
                                        <th class="p-3">Date Added</th>
                                        <th class="p-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="admin-replacements-tbody" class="divide-y divide-slate-800/60">
                                    <tr>
                                        <td colspan="7" class="p-6 text-center text-slate-500">Replacements record load ho raha hai...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ADMIN TAB 5: CREDENTIAL FINDER -->
                <div id="admin-panel-search" class="hidden space-y-6">
                    <div class="glass-panel-glow rounded-2xl p-6 lg:p-8">
                        <div class="mb-6 pb-4 border-b border-slate-800">
                            <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <span>Single Mail Credential & History Search</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-1">Kisi bhi email address ka complete record, password, recovery mail aur order status yahan check karein.</p>
                        </div>

                        <form onsubmit="VaultApp.handleMailSearch(event, 'admin')" class="max-w-2xl space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="email" id="admin-search-mail-input" required placeholder="Enter any email address (e.g. user@basis5.ch)..." class="flex-1 px-4 py-2.5 bg-slate-950/90 border border-slate-700 rounded-xl text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
                                <button type="submit" class="px-5 py-2.5 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono rounded-xl transition-all shadow-cyber-cyan">
                                    Search Credential
                                </button>
                            </div>
                        </form>

                        <!-- Search Result Box -->
                        <div id="admin-search-result" class="mt-6 max-w-2xl hidden">
                            <!-- Injected dynamically -->
                        </div>
                    </div>
                </div>

            </section>

        </main>

        <!-- Footer Bar -->
        <footer class="glass-panel border-t border-slate-800/80 py-4 px-4 text-center text-xs font-mono text-slate-500">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
                <div>CYBERVAULT v2.4 &bull; Apache Hostinger Production Architecture</div>
                <div>SQLite Strict Engine &bull; Zero-Spurious Characters &bull; SHA-256 CSRF</div>
            </div>
        </footer>

        <!-- ORDER INSPECTION & RE-DOWNLOAD MODAL -->
        <div id="order-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
            <div class="glass-panel border border-cyan-500/30 rounded-2xl w-full max-w-3xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <!-- Modal Header -->
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                            <h3 class="text-lg font-bold text-white font-mono" id="modal-order-title">Order Details</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-mono mt-0.5" id="modal-order-subtitle">Record of extracted credentials</p>
                    </div>
                    <button type="button" onclick="VaultApp.closeOrderModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Meta Badges -->
                <div class="p-4 bg-slate-900/40 border-b border-slate-800/80 grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs font-mono">
                    <div class="bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase">Domain</div>
                        <div class="font-bold text-cyan-300" id="modal-order-domain">--</div>
                    </div>
                    <div class="bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase">Total Quantity</div>
                        <div class="font-bold text-emerald-400" id="modal-order-qty">--</div>
                    </div>
                    <div class="bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase">Rate (PKR)</div>
                        <div class="font-bold text-cyan-400" id="modal-order-rate">Rs. 18</div>
                    </div>
                    <div class="bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase">Total Amount</div>
                        <div class="font-bold text-emerald-300" id="modal-order-total">Rs. 0</div>
                    </div>
                    <div class="bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">
                        <div class="text-[10px] text-slate-500 uppercase">Extracted Date</div>
                        <div class="font-bold text-slate-300 text-[11px]" id="modal-order-date">--</div>
                    </div>
                </div>

                <!-- Modal Filter & Accounts List -->
                <div class="p-4 flex-1 overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between mb-3 gap-2">
                        <input type="text" id="modal-account-search" oninput="VaultApp.filterModalAccounts()" placeholder="Filter emails in this order..." class="w-full px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-slate-300 focus:outline-none focus:border-cyan-400">
                        <span id="modal-accounts-count" class="text-xs font-mono text-slate-400 whitespace-nowrap">0 emails</span>
                    </div>

                    <div class="overflow-y-auto custom-scroll border border-slate-800 rounded-xl bg-slate-950/60 flex-1 max-h-[360px]">
                        <table class="w-full text-left text-xs font-mono text-slate-300">
                            <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider sticky top-0 border-b border-slate-800">
                                <tr>
                                    <th class="p-2.5">#</th>
                                    <th class="p-2.5">Email</th>
                                    <th class="p-2.5">Password</th>
                                    <th class="p-2.5">Recovery Email</th>
                                </tr>
                            </thead>
                            <tbody id="modal-accounts-tbody" class="divide-y divide-slate-800/60">
                                <tr>
                                    <td colspan="4" class="p-4 text-center text-slate-500">Loading accounts...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-slate-800 bg-slate-950/80 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" onclick="VaultApp.copyModalOrderMails()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-xs font-mono text-cyan-300 flex items-center space-x-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copy All Mails</span>
                    </button>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="VaultApp.downloadModalOrderFile()" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono flex items-center space-x-2 transition-all shadow-cyber-cyan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download File</span>
                        </button>
                        <button type="button" onclick="VaultApp.closeOrderModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono transition-all">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 2: INGEST / ADD ORDER (ADMIN ONLY) -->
        <div id="ingest-order-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
            <div class="glass-panel border border-cyan-500/40 rounded-2xl w-full max-w-2xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                            <h3 class="text-lg font-bold text-white font-mono">Ingest / Add Order Record</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">Record customer orders or manual delivery batches. Format: email,password,recovery email</p>
                    </div>
                    <button type="button" onclick="VaultApp.closeIngestOrderModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form id="form-ingest-order" onsubmit="VaultApp.handleIngestOrder(event)" class="p-6 overflow-y-auto custom-scroll space-y-4 text-xs font-mono">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Order # / Name (Optional)</label>
                            <input type="text" id="ingest-order-number" placeholder="e.g. Order #1, WA-Batch-1 (or blank for auto)" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                            <p class="text-[10px] text-slate-500 mt-1">Leave empty to auto-generate next sequential Order #.</p>
                        </div>
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Date & Time</label>
                            <input type="text" id="ingest-order-date" placeholder="YYYY-MM-DD HH:MM:SS (e.g. 2026-09-10 14:30:00)" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                            <p class="text-[10px] text-slate-500 mt-1">Leave empty to use current timestamp.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Domain</label>
                            <select id="ingest-order-domain" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                                <option value="basis5.ch">basis5.ch</option>
                                <option value="adlover.site">adlover.site</option>
                                <option value="custom">Auto-detect from emails</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Rate Per Mail (PKR)</label>
                            <input type="number" id="ingest-order-rate" step="0.5" value="18" min="1" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                        </div>
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Total Price (Optional)</label>
                            <input type="number" id="ingest-order-total" step="1" placeholder="Auto (Qty × Rate)" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 uppercase mb-1">Description / Note (Optional)</label>
                        <input type="text" id="ingest-order-notes" placeholder="e.g. Direct Delivery Batch" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                    </div>

                    <div>
                        <label class="block text-slate-300 uppercase mb-1">Attach CSV File or Paste Accounts</label>
                        <input type="file" id="ingest-order-file" accept=".csv,.txt" class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-mono file:bg-slate-800 file:text-cyan-300 hover:file:bg-slate-700 cursor-pointer bg-slate-950/60 p-2 rounded-xl border border-slate-700">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-slate-300 uppercase">Paste Raw Mails (CSV Text)</label>
                            <span class="text-[10px] text-slate-500">email,password,recovery email</span>
                        </div>
                        <textarea id="ingest-order-text" rows="5" placeholder="alpha_01@basis5.ch,Pass123!,rec1@gmail.com&#10;alpha_02@basis5.ch,Pass456!,rec2@gmail.com" class="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400 custom-scroll"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex items-center justify-end space-x-3">
                        <button type="button" onclick="VaultApp.closeIngestOrderModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-all">
                            Cancel
                        </button>
                        <button type="submit" id="btn-submit-ingest-order" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold tracking-wider transition-all shadow-cyber-cyan">
                            SAVE ORDER TO DATABASE
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: EDIT ORDER (ADMIN ONLY) -->
        <div id="edit-order-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
            <div class="glass-panel border border-cyan-500/40 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
                    <h3 class="text-lg font-bold text-white font-mono" id="edit-order-title">Edit Order</h3>
                    <button type="button" onclick="VaultApp.closeEditOrderModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form id="form-edit-order" onsubmit="VaultApp.handleEditOrder(event)" class="p-6 space-y-4 text-xs font-mono">
                    <input type="hidden" id="edit-order-id">
                    <div>
                        <label class="block text-slate-300 uppercase mb-1">Order # / Name</label>
                        <input type="text" id="edit-order-number" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                    </div>
                    <div>
                        <label class="block text-slate-300 uppercase mb-1">Date & Time</label>
                        <input type="text" id="edit-order-date" placeholder="YYYY-MM-DD HH:MM:SS" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Rate Per Mail (PKR)</label>
                            <input type="number" id="edit-order-rate" step="0.5" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                        </div>
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Total Price (PKR)</label>
                            <input type="number" id="edit-order-total" step="1" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-slate-300 uppercase mb-1">Description / Note</label>
                        <input type="text" id="edit-order-notes" placeholder="Note / Reference" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-cyan-400">
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex items-center justify-end space-x-3">
                        <button type="button" onclick="VaultApp.closeEditOrderModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold transition-all shadow-cyber-cyan">
                            SAVE CHANGES
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 4: RECORD / EDIT PAYMENT (ADMIN ONLY) -->
        <div id="payment-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
            <div class="glass-panel border border-emerald-500/40 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <h3 class="text-lg font-bold text-white font-mono" id="payment-modal-title">Record Payment (Wasooli)</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">Client: Rana Asim &bull; Update ledger wasooli balance</p>
                    </div>
                    <button type="button" onclick="VaultApp.closePaymentModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form id="form-payment" onsubmit="VaultApp.handleSavePayment(event)" class="p-6 space-y-4 text-xs font-mono">
                    <input type="hidden" id="payment-id" value="0">
                    <div>
                        <label class="block text-slate-300 uppercase mb-1">Amount Received (PKR) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-emerald-400 font-bold">Rs.</span>
                            <input type="number" id="payment-amount" required step="1" min="1" placeholder="e.g. 5000" class="w-full pl-10 pr-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white text-base font-bold focus:outline-none focus:border-emerald-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Payment Date & Time</label>
                            <input type="text" id="payment-date" placeholder="YYYY-MM-DD HH:MM:SS" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-emerald-400">
                            <p class="text-[10px] text-slate-500 mt-1">Leave empty for current time</p>
                        </div>
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Payment Method</label>
                            <select id="payment-method" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-emerald-400">
                                <option value="JazzCash">JazzCash</option>
                                <option value="EasyPaisa">EasyPaisa</option>
                                <option value="Bank Transfer">Bank Transfer (Meezan / HBL / etc.)</option>
                                <option value="Cash">Cash</option>
                                <option value="Crypto / USDT">Crypto / USDT</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 uppercase mb-1">Transaction ID / Reference Note (Optional)</label>
                        <input type="text" id="payment-ref-note" placeholder="e.g. TID: 2049284209 - Sent to JazzCash" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-emerald-400">
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex items-center justify-end space-x-3">
                        <button type="button" onclick="VaultApp.closePaymentModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold transition-all shadow-sm">
                            SAVE PAYMENT ENTRY
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 5: ADD FAULTY REPLACEMENTS (ADMIN ONLY) -->
        <div id="replacements-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
            <div class="glass-panel border border-amber-500/40 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <h3 class="text-lg font-bold text-white font-mono">Add Faulty Mails (Direct Deduction)</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">Yeh mails sale count aur total bill se direct minus ho jayengi.</p>
                    </div>
                    <button type="button" onclick="VaultApp.closeReplacementsModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form id="form-replacements" onsubmit="VaultApp.handleAddReplacements(event)" class="p-6 space-y-4 text-xs font-mono">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-slate-300 uppercase">Faulty Emails List *</label>
                            <span class="text-[10px] text-slate-500">One per line or comma-separated</span>
                        </div>
                        <textarea id="rep-emails-text" rows="5" required placeholder="user1@basis5.ch&#10;user2@basis5.ch" class="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-amber-400 custom-scroll"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Deduction Rate Per Mail (PKR)</label>
                            <input type="number" id="rep-rate-deduction" value="18" step="0.5" min="1" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-amber-400">
                        </div>
                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Reason / Note</label>
                            <input type="text" id="rep-reason" value="Faulty / Disabled" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-amber-400">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex items-center justify-end space-x-3">
                        <button type="button" onclick="VaultApp.closeReplacementsModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold transition-all shadow-sm">
                            DEDUCT FAULTY MAILS FROM BILL
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 6: INSPECT REPLACEMENTS (CLIENT & ADMIN) -->
        <div id="inspect-replacements-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
            <div class="glass-panel border border-amber-500/40 rounded-2xl w-full max-w-3xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <h3 class="text-lg font-bold text-white font-mono">Faulty Deducted Accounts List</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-mono mt-0.5" id="inspect-rep-subtitle">Total replaced accounts deducted from bill</p>
                    </div>
                    <button type="button" onclick="VaultApp.closeInspectReplacementsModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-4 flex-1 overflow-hidden flex flex-col">
                    <div class="overflow-y-auto custom-scroll border border-slate-800 rounded-xl bg-slate-950/60 flex-1 max-h-[400px]">
                        <table class="w-full text-left text-xs font-mono text-slate-300">
                            <thead class="bg-slate-900/90 text-slate-400 uppercase tracking-wider sticky top-0 border-b border-slate-800">
                                <tr>
                                    <th class="p-2.5">#</th>
                                    <th class="p-2.5">Email Account</th>
                                    <th class="p-2.5">Domain</th>
                                    <th class="p-2.5">Rate Deducted</th>
                                    <th class="p-2.5">Reason</th>
                                    <th class="p-2.5">Date</th>
                                </tr>
                            </thead>
                            <tbody id="inspect-rep-tbody" class="divide-y divide-slate-800/60">
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-slate-500">Loading replacements...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-800 bg-slate-950/80 flex items-center justify-end">
                    <button type="button" onclick="VaultApp.closeInspectReplacementsModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono transition-all">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 7: CLIENT PORTAL MAINTENANCE CONTROLLER (ADMIN ONLY) -->
        <div id="modal-maintenance" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
            <div class="glass-panel border border-amber-500/50 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white font-mono">Client Portal Maintenance Controller</h3>
                            <p class="text-[11px] text-slate-400 font-mono">Control Rana Asim's portal view during your updates</p>
                        </div>
                    </div>
                    <button type="button" onclick="VaultApp.closeMaintenanceModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs font-mono">
                    <!-- Status Toggle -->
                    <div class="p-4 bg-slate-950/80 border border-slate-800 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="text-sm font-bold text-white">Client Portal Access State</div>
                            <div class="text-xs mt-0.5" id="modal-maint-status-desc">
                                <span class="text-emerald-400 font-semibold">🟢 LIVE (Client has full access)</span>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="modal-maint-checkbox" class="sr-only peer" onchange="VaultApp.onMaintenanceCheckboxChange()">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        </label>
                    </div>

                    <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-200/90 text-[11px] leading-relaxed">
                        💡 <b>Admin Testing Benefit:</b> Maintenance mode lagane ke baad bhi <b>aap (Admin) "Test Client View" se client portal ko test kar sakte hain</b> aur extractions check kar sakte hain, jabke client ko sirf Maintenance screen nazar aayegi!
                    </div>

                    <!-- Custom Message -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] text-slate-300 font-semibold uppercase">
                            Client Notice Message (Clients ko kya show ho):
                        </label>
                        <textarea id="modal-maint-message" rows="3" class="w-full p-3 bg-slate-950 border border-slate-700 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-amber-400 font-mono" placeholder="Maintenance message yahan likhein..."></textarea>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <button type="button" onclick="document.getElementById('modal-maint-message').value = 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!'" class="text-[10px] px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 cursor-pointer">
                                Stock Upgrade Notice
                            </button>
                            <button type="button" onclick="document.getElementById('modal-maint-message').value = 'Scheduled Maintenance: Portal 15-20 minute ke liye update par hai. Tamam stock mehfooz hai.'" class="text-[10px] px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg border border-slate-700 cursor-pointer">
                                15 Min Quick Notice
                            </button>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-800 bg-slate-950/70 flex items-center justify-between">
                    <button type="button" onclick="VaultApp.enterClientPreview(); VaultApp.closeMaintenanceModal();" class="px-3.5 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 border border-indigo-500/40 text-indigo-300 font-mono text-xs font-semibold rounded-xl flex items-center space-x-1.5 transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Test Client View</span>
                    </button>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="VaultApp.closeMaintenanceModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-mono rounded-xl cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" onclick="VaultApp.saveMaintenanceSettings()" class="px-5 py-2 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs font-mono rounded-xl shadow-md cursor-pointer">
                            Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL 8: STOCK ALLOCATION DESTINATION (AVAILABLE VS SOLD) -->
        <div id="modal-stock-allocation" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
            <div class="glass-panel border border-cyan-500/50 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center text-cyan-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white font-mono">Select Stock Destination</h3>
                            <p class="text-[11px] text-slate-400 font-mono" id="alloc-preview-count">Parsed 0 accounts ready for allocation</p>
                        </div>
                    </div>
                    <button type="button" onclick="VaultApp.closeStockAllocationModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs font-mono">
                    <!-- Destination Selection Cards -->
                    <div class="grid grid-cols-2 gap-3">
                        <div onclick="VaultApp.setAllocationDestination('available')" id="alloc-card-available" class="p-3.5 rounded-xl border-2 cursor-pointer transition-all border-cyan-500 bg-cyan-950/30 text-white">
                            <div class="flex items-center space-x-2 font-bold text-cyan-300">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                                <span>Available Stock</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">Fresh stock for client to extract. Bill me koi izafa nahi hoga.</p>
                        </div>

                        <div onclick="VaultApp.setAllocationDestination('sold')" id="alloc-card-sold" class="p-3.5 rounded-xl border-2 cursor-pointer transition-all border-slate-700 bg-slate-900/40 text-slate-400 hover:border-slate-600">
                            <div class="flex items-center space-x-2 font-bold text-emerald-400">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                <span>Sold Order (Delivered)</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">Pehle se sold delivery. Bill me add hogi aur client download kar sakega.</p>
                        </div>
                    </div>

                    <!-- Sold Fields (Visible only when destination === 'sold') -->
                    <div id="alloc-sold-fields" class="space-y-3 p-3.5 bg-slate-950/80 border border-emerald-500/30 rounded-xl hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-300 uppercase mb-1">Order # / Batch Identifier</label>
                                <input type="text" id="alloc-order-number" placeholder="e.g. Order #1" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white focus:outline-none focus:border-emerald-400">
                            </div>
                            <div>
                                <label class="block text-slate-300 uppercase mb-1">Date &amp; Time</label>
                                <input type="datetime-local" id="alloc-order-date" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white focus:outline-none focus:border-emerald-400">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-300 uppercase mb-1">Rate Per Mail (PKR)</label>
                                <input type="number" id="alloc-rate-per-mail" value="18" step="0.5" min="1" oninput="VaultApp.recalcAllocTotal()" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white focus:outline-none focus:border-emerald-400">
                            </div>
                            <div>
                                <label class="block text-slate-300 uppercase mb-1">Total Bill (PKR)</label>
                                <input type="text" id="alloc-total-display" readonly class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-emerald-400 font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block text-slate-300 uppercase mb-1">Delivery Reference / Note (Optional)</label>
                            <input type="text" id="alloc-notes" placeholder="e.g. Direct Delivery Batch" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white focus:outline-none focus:border-emerald-400">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800 flex items-center justify-end space-x-3">
                        <button type="button" onclick="VaultApp.closeStockAllocationModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-all">
                            Cancel
                        </button>
                        <button type="button" id="btn-confirm-allocation" onclick="VaultApp.confirmStockAllocation()" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold transition-all shadow-sm flex items-center space-x-2 cursor-pointer">
                            <span>CONFIRM TO AVAILABLE STOCK</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Frontend Core Application Logic -->
    <script>
        const VaultApp = {
            csrfToken: '<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>',
            user: null,
            managedDomains: <?= json_encode(MANAGED_DOMAINS) ?>,
            selectedExtractDomain: 'basis5.ch',
            stocks: {},
            allOrders: [],
            allPayments: [],
            allReplacements: [],
            financialData: null,
            adminActiveTab: 'inventory',
            activeModalOrder: null,
            maintenanceMode: '0',
            maintenanceMessage: 'Portal Under Maintenance: Hum stock aur system upgrades kar rahe hain. Jald wapis aayenge!',
            isAdminClientPreview: false,

            init: async function() {
                // Prevent browser "Confirm Form Resubmission" dialog on refresh
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
                this.initParticles();
                await this.checkAuthStatus();
            },

            initParticles: function() {
                if (typeof particlesJS !== 'undefined') {
                    particlesJS('particles-js', {
                        particles: {
                            number: { value: 45, density: { enable: true, value_area: 800 } },
                            color: { value: ['#06b6d4', '#0ea5e9', '#3b82f6'] },
                            shape: { type: 'circle' },
                            opacity: { value: 0.25, random: true },
                            size: { value: 2.5, random: true },
                            line_linked: {
                                enable: true,
                                distance: 150,
                                color: '#06b6d4',
                                opacity: 0.15,
                                width: 1
                            },
                            move: {
                                enable: true,
                                speed: 1.2,
                                direction: 'none',
                                random: false,
                                straight: false,
                                out_mode: 'out',
                                bounce: false
                            }
                        },
                        interactivity: {
                            detect_on: 'canvas',
                            events: {
                                onhover: { enable: true, mode: 'grab' },
                                onclick: { enable: true, mode: 'push' },
                                resize: true
                            },
                            modes: {
                                grab: { distance: 140, line_linked: { opacity: 0.4 } },
                                push: { particles_nb: 3 }
                            }
                        },
                        retina_detect: true
                    });
                }
            },

            checkAuthStatus: async function() {
                try {
                    const res = await fetch('api.php?action=status');
                    const data = await res.json();
                    if (data && data.maintenance_mode !== undefined) {
                        this.maintenanceMode = String(data.maintenance_mode);
                        this.maintenanceMessage = data.maintenance_message || this.maintenanceMessage;
                    }
                    if (data.authenticated && data.user) {
                        this.user = data.user;
                        this.csrfToken = data.csrf_token || this.csrfToken;
                        this.showAuthenticatedView();
                    } else {
                        if (data && data.csrf_token) {
                            this.csrfToken = data.csrf_token;
                        }
                        this.showLoginView();
                    }
                } catch (e) {
                    console.error('Status check failed:', e);
                    this.showLoginView();
                }
            },

            handleLogin: async function(e) {
                e.preventDefault();
                const u = document.getElementById('login-username').value.trim();
                const p = document.getElementById('login-password').value;

                if (!u || !p) return;

                const btn = document.getElementById('btn-login-submit');
                const origText = btn.innerHTML;
                btn.innerHTML = '<span>AUTHENTICATING...</span>';
                btn.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('username', u);
                    formData.append('password', p);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=login', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        this.user = data.user;
                        this.csrfToken = data.csrf_token || this.csrfToken;
                        
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Access Granted',
                            text: `Welcome back, ${this.user.username} (${this.user.role.toUpperCase()})`,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        await this.checkAuthStatus();
                    } else {
                        // In case of CSRF mismatch, refresh token seamlessly
                        if (data.csrf_token) {
                            this.csrfToken = data.csrf_token;
                        }
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'error',
                            title: 'Clearance Rejected',
                            text: data.message || 'Invalid credentials'
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'error',
                        title: 'Connection Error',
                        text: 'Unable to communicate with the authentication server.'
                    });
                } finally {
                    btn.innerHTML = origText;
                    btn.disabled = false;
                }
            },

            logout: async function() {
                try {
                    const res = await fetch('api.php?action=logout');
                    const data = await res.json();
                    if (data && data.csrf_token) {
                        this.csrfToken = data.csrf_token;
                    }
                } catch (e) {}
                this.user = null;
                this.isAdminClientPreview = false;
                const uInput = document.getElementById('login-username');
                const pInput = document.getElementById('login-password');
                if (uInput) uInput.value = '';
                if (pInput) pInput.value = '';
                this.showLoginView();
                // Ensure fresh CSRF token so next login immediately works without reload
                try {
                    const res = await fetch('api.php?action=status');
                    const data = await res.json();
                    if (data && data.csrf_token) {
                        this.csrfToken = data.csrf_token;
                    }
                } catch (e) {}
                Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    icon: 'info',
                    title: 'Session Terminated',
                    text: 'Aap successfully logout ho chuke hain.',
                    timer: 1200,
                    showConfirmButton: false
                });
            },

            showLoginView: function() {
                document.getElementById('view-login').classList.remove('hidden');
                document.getElementById('view-client').classList.add('hidden');
                document.getElementById('view-admin').classList.add('hidden');
                document.getElementById('view-maintenance').classList.add('hidden');
                document.getElementById('client-admin-test-banner').classList.add('hidden');
                document.getElementById('header-auth-badge').classList.add('hidden');
                document.getElementById('header-auth-badge').classList.remove('flex');
            },

            showAuthenticatedView: function() {
                document.getElementById('view-login').classList.add('hidden');
                document.getElementById('header-auth-badge').classList.remove('hidden');
                document.getElementById('header-auth-badge').classList.add('flex');
                document.getElementById('header-user-name').innerText = this.user.username;
                document.getElementById('header-user-role').innerText = this.user.role.toUpperCase();

                if (this.user.role === 'admin') {
                    if (this.isAdminClientPreview) {
                        // Admin testing the client view
                        document.getElementById('view-admin').classList.add('hidden');
                        document.getElementById('client-admin-test-banner').classList.remove('hidden');
                        this.updatePreviewBannerStatus();

                        if (this.maintenanceMode === '1') {
                            document.getElementById('view-client').classList.add('hidden');
                            document.getElementById('view-maintenance').classList.remove('hidden');
                            const msgEl = document.getElementById('maintenance-client-message');
                            if (msgEl) msgEl.innerText = this.maintenanceMessage;
                        } else {
                            document.getElementById('view-maintenance').classList.add('hidden');
                            document.getElementById('view-client').classList.remove('hidden');
                            this.refreshStock();
                            this.loadOrders();
                            this.loadKhataData();
                            this.loadPayments();
                            this.loadReplacements();
                        }
                        return;
                    }

                    document.getElementById('client-admin-test-banner').classList.add('hidden');
                    document.getElementById('view-maintenance').classList.add('hidden');
                    document.getElementById('view-client').classList.add('hidden');
                    document.getElementById('view-admin').classList.remove('hidden');
                    this.updateAdminMaintenanceUI();
                    this.loadAdminData();
                    this.loadOrders();
                    this.loadKhataData();
                    this.loadPayments();
                    this.loadReplacements();
                } else {
                    document.getElementById('view-admin').classList.add('hidden');
                    document.getElementById('client-admin-test-banner').classList.add('hidden');

                    if (this.maintenanceMode === '1') {
                        // Client is blocked by maintenance mode!
                        document.getElementById('view-client').classList.add('hidden');
                        document.getElementById('view-maintenance').classList.remove('hidden');
                        const msgEl = document.getElementById('maintenance-client-message');
                        if (msgEl) msgEl.innerText = this.maintenanceMessage;
                    } else {
                        document.getElementById('view-maintenance').classList.add('hidden');
                        document.getElementById('view-client').classList.remove('hidden');
                        this.refreshStock();
                        this.loadOrders();
                        this.loadKhataData();
                        this.loadPayments();
                        this.loadReplacements();
                    }
                }
            },

            // ==========================================
            // Maintenance Mode & Admin Preview Controller
            // ==========================================
            updateAdminMaintenanceUI: function() {
                const isMaint = this.maintenanceMode === '1';
                const label = document.getElementById('label-admin-maint-status');
                const dot = document.getElementById('dot-admin-maint-status');
                const btn = document.getElementById('btn-admin-maint-status');
                const alert = document.getElementById('admin-maint-active-alert');

                if (isMaint) {
                    if (label) label.innerText = 'Client: MAINTENANCE';
                    if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse';
                    if (btn) {
                        btn.className = 'px-3.5 py-2 rounded-xl border text-xs font-mono flex items-center space-x-2 transition-all shadow-sm bg-amber-950/70 border-amber-500/60 text-amber-300 hover:bg-amber-900/80 cursor-pointer animate-pulse';
                    }
                    if (alert) alert.classList.remove('hidden');
                } else {
                    if (label) label.innerText = 'Client: LIVE';
                    if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-400';
                    if (btn) {
                        btn.className = 'px-3.5 py-2 rounded-xl border text-xs font-mono flex items-center space-x-2 transition-all shadow-sm bg-emerald-950/50 border-emerald-500/40 text-emerald-400 hover:bg-emerald-900/60 cursor-pointer';
                    }
                    if (alert) alert.classList.add('hidden');
                }
            },

            updatePreviewBannerStatus: function() {
                const isMaint = this.maintenanceMode === '1';
                const statusEl = document.getElementById('preview-maint-status');
                const btn = document.getElementById('preview-toggle-maint-btn');
                if (statusEl) {
                    statusEl.innerText = isMaint ? 'Active (Clients Blocked)' : 'Disabled (Portal Live)';
                    statusEl.className = isMaint ? 'font-bold text-amber-300' : 'font-bold text-emerald-400';
                }
                if (btn) {
                    btn.innerText = isMaint ? 'Switch to LIVE' : 'Enable Maintenance';
                    btn.className = isMaint
                        ? 'px-3 py-1.5 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 border border-emerald-500/40 text-emerald-300 font-bold transition-all cursor-pointer'
                        : 'px-3 py-1.5 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/40 text-amber-300 font-bold transition-all cursor-pointer';
                }
            },

            enterClientPreview: function() {
                this.isAdminClientPreview = true;
                this.showAuthenticatedView();
                Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    icon: 'info',
                    title: 'Admin Preview Mode Active',
                    text: 'Aap ab Client Portal ko aam user ki tarah dekh aur test kar sakte hain. Jab chaho wapis Admin Panel ja sakte hain.',
                    timer: 2000,
                    showConfirmButton: false
                });
            },

            exitClientPreview: function() {
                this.isAdminClientPreview = false;
                this.showAuthenticatedView();
            },

            toggleMaintenanceFromPreview: async function() {
                const newStatus = this.maintenanceMode === '1' ? '0' : '1';
                await this.sendMaintenanceUpdate(newStatus, this.maintenanceMessage);
                this.updatePreviewBannerStatus();
            },

            quickDisableMaintenance: async function() {
                await this.sendMaintenanceUpdate('0', this.maintenanceMessage);
                this.updateAdminMaintenanceUI();
            },

            openMaintenanceModal: function() {
                const isMaint = this.maintenanceMode === '1';
                const cb = document.getElementById('modal-maint-checkbox');
                const desc = document.getElementById('modal-maint-status-desc');
                const msgInput = document.getElementById('modal-maint-message');

                if (cb) cb.checked = isMaint;
                if (desc) {
                    desc.innerHTML = isMaint
                        ? '<span class="text-amber-400 font-semibold">🔴 Under Maintenance (Client blocked)</span>'
                        : '<span class="text-emerald-400 font-semibold">🟢 LIVE (Client has full access)</span>';
                }
                if (msgInput) msgInput.value = this.maintenanceMessage;

                document.getElementById('modal-maintenance').classList.remove('hidden');
            },

            closeMaintenanceModal: function() {
                document.getElementById('modal-maintenance').classList.add('hidden');
            },

            onMaintenanceCheckboxChange: function() {
                const cb = document.getElementById('modal-maint-checkbox');
                const desc = document.getElementById('modal-maint-status-desc');
                if (desc && cb) {
                    desc.innerHTML = cb.checked
                        ? '<span class="text-amber-400 font-semibold">🔴 Under Maintenance (Client blocked)</span>'
                        : '<span class="text-emerald-400 font-semibold">🟢 LIVE (Client has full access)</span>';
                }
            },

            saveMaintenanceSettings: async function() {
                const cb = document.getElementById('modal-maint-checkbox');
                const msgInput = document.getElementById('modal-maint-message');
                const enabled = cb && cb.checked ? '1' : '0';
                const msg = msgInput ? msgInput.value.trim() : this.maintenanceMessage;

                await this.sendMaintenanceUpdate(enabled, msg);
                this.closeMaintenanceModal();
            },

            sendMaintenanceUpdate: async function(enabled, message) {
                try {
                    const formData = new FormData();
                    formData.append('enabled', enabled);
                    formData.append('message', message || 'Portal Under Maintenance');
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=set_maintenance_mode', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        this.maintenanceMode = String(enabled);
                        this.maintenanceMessage = message || 'Portal Under Maintenance';
                        this.updateAdminMaintenanceUI();
                        if (this.isAdminClientPreview) {
                            this.updatePreviewBannerStatus();
                        }
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: enabled === '1' ? 'Maintenance Mode Active' : 'Portal Live Ho Gaya',
                            text: data.message || 'Settings successfully update ho chuki hain.'
                        });
                    } else {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'error',
                            title: 'Update Failed',
                            text: data.message || 'Maintenance settings save nahi ho sakeen.'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'error',
                        title: 'Error',
                        text: 'Server communication failed.'
                    });
                }
            },

            // ==========================================
            // Stock & Client Hub
            // ==========================================
            refreshStock: async function() {
                try {
                    const res = await fetch('api.php?action=stock');
                    const data = await res.json();
                    if (!data.success) return;

                    const grid = document.getElementById('client-stock-grid');
                    grid.innerHTML = '';
                    const domainOptions = document.getElementById('extract-domain-options');
                    domainOptions.innerHTML = '';

                    // Store stock index
                    data.domains.forEach((d) => {
                        this.stocks[d.domain] = d.available;

                        // Create Stock Card
                        const isPrimary = d.domain === 'basis5.ch' || d.domain === 'adlover.site';
                        const card = document.createElement('div');
                        card.className = `glass-panel rounded-2xl p-6 relative overflow-hidden transition-all hover:border-cyan-500/50 ${isPrimary ? 'border-cyan-500/30' : ''}`;
                        card.innerHTML = `
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full ${d.available > 0 ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500'}"></div>
                                    <span class="text-xs font-mono uppercase tracking-wider text-slate-400">Managed Domain</span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono ${d.available > 0 ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-300 border border-rose-500/20'}">
                                    ${d.available > 0 ? 'Ready for Extraction' : 'Out of Stock'}
                                </span>
                            </div>
                            <div class="text-xl font-bold font-mono text-white mb-3 tracking-tight">${d.domain}</div>
                            <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-800 text-center font-mono">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Available</div>
                                    <div class="text-xl font-bold text-emerald-400">${d.available}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Extracted</div>
                                    <div class="text-xl font-bold text-cyan-400">${d.downloaded}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase">Replaced</div>
                                    <div class="text-xl font-bold text-amber-400">${d.replaced}</div>
                                </div>
                            </div>
                        `;
                        grid.appendChild(card);

                        // Populate extraction domain radio buttons
                        const opt = document.createElement('label');
                        opt.className = 'cursor-pointer';
                        opt.innerHTML = `
                            <input type="radio" name="extract-domain" value="${d.domain}" ${d.domain === this.selectedExtractDomain ? 'checked' : ''} onchange="VaultApp.onDomainChanged('${d.domain}')" class="peer sr-only">
                            <div class="p-3.5 rounded-xl border border-slate-700/80 bg-slate-950/40 peer-checked:border-cyan-400 peer-checked:bg-cyan-950/20 transition-all flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-white font-mono">${d.domain}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">${d.available} in stock</div>
                                </div>
                                <span class="w-3.5 h-3.5 rounded-full border-2 border-slate-600 peer-checked:border-cyan-400 peer-checked:bg-cyan-400"></span>
                            </div>
                        `;
                        domainOptions.appendChild(opt);
                    });

                    this.updateAvailableHint();

                } catch (e) {
                    console.error('Failed to refresh stock:', e);
                }
            },

            onDomainChanged: function(domain) {
                this.selectedExtractDomain = domain;
                this.updateAvailableHint();
            },

            updateAvailableHint: function() {
                const avail = this.stocks[this.selectedExtractDomain] || 0;
                document.getElementById('extract-stock-hint').innerText = `Available: ${avail}`;
            },

            setExtractQty: function(val) {
                const avail = this.stocks[this.selectedExtractDomain] || 0;
                const input = document.getElementById('extract-quantity');
                if (val === 'max') {
                    input.value = Math.max(1, avail);
                } else {
                    input.value = val;
                }
            },

            // ==========================================
            // Extraction & SweetAlert2 PIN Workflow
            // ==========================================
            handleExtractSubmit: function(e) {
                e.preventDefault();
                const domain = this.selectedExtractDomain;
                const qty = parseInt(document.getElementById('extract-quantity').value, 10);
                const format = document.querySelector('input[name="export-format"]:checked')?.value || 'csv';

                if (!domain) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'warning', title: 'Domain Required', text: 'Please select a domain to extract.' });
                    return;
                }

                if (!qty || qty <= 0) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'warning', title: 'Invalid Quantity', text: 'Please enter a valid extraction quantity.' });
                    return;
                }

                const avail = this.stocks[domain] || 0;
                if (qty > avail) {
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'error',
                        title: 'Stock Exceeded',
                        text: `Only ${avail} accounts currently available for ${domain}. Requested: ${qty}`
                    });
                    return;
                }

                // Show SweetAlert2 PIN dialog
                Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: 'SECURE EXTRACTION PIN',
                    html: `
                        <p class="text-xs text-slate-300 mb-3">Authorize extraction of <b class="text-cyan-300 font-mono">${qty}</b> accounts from <b class="text-cyan-300 font-mono">${domain}</b> (${format.toUpperCase()})</p>
                        <p class="text-[11px] text-slate-400 font-mono">Enter your 4-digit security PIN:</p>
                    `,
                    input: 'password',
                    inputPlaceholder: '••••',
                    inputAttributes: {
                        maxlength: 4,
                        autocapitalize: 'off',
                        autocorrect: 'off'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'AUTHENTICATE & EXTRACT',
                    confirmButtonColor: '#06b6d4',
                    cancelButtonText: 'Abort',
                    cancelButtonColor: '#334155',
                    showLoaderOnConfirm: true,
                    preConfirm: async (pin) => {
                        if (!pin || pin.length !== 4) {
                            Swal.showValidationMessage('A valid 4-digit PIN is required');
                            return false;
                        }

                        const formData = new FormData();
                        formData.append('domain', domain);
                        formData.append('quantity', qty);
                        formData.append('format', format);
                        formData.append('pin', pin);
                        formData.append('csrf_token', this.csrfToken);

                        try {
                            const res = await fetch('api.php?action=extract', {
                                method: 'POST',
                                body: formData
                            });
                            const data = await res.json();
                            if (!data.success) {
                                Swal.showValidationMessage(data.message || 'Extraction failed');
                                return false;
                            }
                            return data;
                        } catch (err) {
                            Swal.showValidationMessage('Server communication error');
                            return false;
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const data = result.value;
                        // Trigger force download of the base64 content
                        this.downloadBase64File(data.file_content, data.filename, data.format === 'csv' ? 'text/csv' : 'text/plain');

                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Extraction Successful',
                            html: `
                                <div class="text-xs text-slate-300 space-y-1">
                                    <p>Downloaded: <b class="text-cyan-300 font-mono">${data.extracted}</b> accounts</p>
                                    <p>Domain: <b class="text-white font-mono">${data.domain}</b></p>
                                    <p>Header: <code class="text-emerald-300">email,password,recovery email</code> (bina space)</p>
                                    <p class="text-[11px] text-slate-400 mt-2">File <b>${data.filename}</b> downloaded to your system.</p>
                                </div>
                            `
                        });

                        // Refresh stock numbers and orders history
                        this.refreshStock();
                        this.loadOrders();
                        document.getElementById('extract-quantity').value = '';
                    }
                });
            },

            downloadBase64File: function(base64Content, filename, mimeType) {
                const byteCharacters = atob(base64Content);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: mimeType });
                
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);
            },

            // ==========================================
            // Replacement Feature
            // ==========================================
            handleReplacementSubmit: async function(e) {
                e.preventDefault();
                const raw = document.getElementById('replace-emails').value.trim();
                if (!raw) return;

                const btn = document.getElementById('btn-replace');
                const origText = btn.innerHTML;
                btn.innerHTML = '<span>PROCESSING REPLACEMENTS...</span>';
                btn.disabled = true;

                try {
                    const formData = new FormData();
                    formData.append('faulty_emails', raw);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=replace', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        let detailsHtml = `<p class="text-sm font-semibold text-white mb-2">${data.message}</p>`;
                        if (data.not_found && data.not_found.length > 0) {
                            detailsHtml += `<p class="text-xs text-rose-300 mt-1">Not Found in DB: ${data.not_found.join(', ')}</p>`;
                        }
                        if (data.not_downloaded && data.not_downloaded.length > 0) {
                            detailsHtml += `<p class="text-xs text-amber-300 mt-1">Not in 'downloaded' state: ${data.not_downloaded.map(x => x.email + ' (' + x.status + ')').join(', ')}</p>`;
                        }

                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Replacements Recorded',
                            html: detailsHtml
                        });

                        document.getElementById('replace-emails').value = '';
                        this.refreshStock();
                    } else {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'error',
                            title: 'Replacement Issue',
                            text: data.message || 'Failed to update replacement status'
                        });
                    }
                } catch (err) {
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'error',
                        title: 'Error',
                        text: 'Server communication error during replacement.'
                    });
                } finally {
                    btn.innerHTML = origText;
                    btn.disabled = false;
                }
            },

            // ==========================================
            // Admin: Load Data, CSV Upload, Inventory
            // ==========================================
            loadAdminData: async function() {
                await Promise.all([
                    this.loadAdminStats(),
                    this.loadAdminAccounts()
                ]);
            },

            loadAdminStats: async function() {
                try {
                    const res = await fetch('api.php?action=stock');
                    const data = await res.json();
                    if (!data.success) return;

                    const o = data.overall;
                    document.getElementById('stat-total').innerText = o.total_accounts;
                    document.getElementById('stat-available').innerText = o.total_available;
                    document.getElementById('stat-downloaded').innerText = o.total_downloaded;
                    document.getElementById('stat-replaced').innerText = o.total_replaced;
                } catch (e) {
                    console.error('Failed to load admin stats:', e);
                }
            },

            loadAdminAccounts: async function() {
                const domain = document.getElementById('filter-domain').value;
                const status = document.getElementById('filter-status').value;

                try {
                    const res = await fetch(`api.php?action=list_accounts&domain=${encodeURIComponent(domain)}&status=${encodeURIComponent(status)}&limit=100`);
                    const data = await res.json();
                    if (!data.success) return;

                    const tbody = document.getElementById('admin-accounts-tbody');
                    tbody.innerHTML = '';

                    if (!data.accounts || data.accounts.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="p-6 text-center text-slate-500 font-mono">No accounts match current filters</td></tr>';
                        return;
                    }

                    data.accounts.forEach((acc) => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-900/50 transition-colors font-mono';

                        let statusBadge = '';
                        if (acc.status === 'available') {
                            statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 font-bold">available</span>';
                        } else if (acc.status === 'downloaded') {
                            statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 font-bold">downloaded</span>';
                        } else if (acc.status === 'replaced') {
                            statusBadge = '<span class="px-2 py-0.5 rounded text-[10px] bg-amber-500/10 text-amber-300 border border-amber-500/20 font-bold">replaced</span>';
                        }

                        let actionsHtml = '';
                        if (acc.status !== 'available') {
                            actionsHtml += `<button type="button" onclick="VaultApp.revertAccountAvailable(${acc.id}, '${acc.email}')" title="Revert to Available" class="px-2 py-1 rounded bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 text-xs border border-emerald-500/40 transition-all font-semibold mr-1">Revert to Stock</button>`;
                        }
                        actionsHtml += `<button type="button" onclick="VaultApp.deleteAccount(${acc.id}, '${acc.email}', '${acc.status}')" title="Delete Account" class="px-2 py-1 rounded bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 text-xs border border-rose-500/40 transition-all">Delete</button>`;

                        tr.innerHTML = `
                            <td class="p-3 text-slate-500">${acc.id}</td>
                            <td class="p-3 text-white font-semibold select-all">${acc.email}</td>
                            <td class="p-3 text-cyan-300">${acc.domain}</td>
                            <td class="p-3">${statusBadge}</td>
                            <td class="p-3 text-slate-400 text-[11px]">${acc.created_at}</td>
                            <td class="p-3 text-slate-400 text-[11px]">${acc.downloaded_at ? 'DL: ' + acc.downloaded_at : (acc.replaced_at ? 'Repl: ' + acc.replaced_at : 'None')}</td>
                            <td class="p-3 text-right whitespace-nowrap">${actionsHtml}</td>
                        `;
                        tbody.appendChild(tr);
                    });

                } catch (e) {
                    console.error('Failed to load accounts list:', e);
                }
            },

            revertAccountAvailable: async function(accId, email) {
                const conf = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: 'Revert to Available?',
                    html: `
                        <div class="text-left text-xs font-mono space-y-2">
                            <p class="text-slate-200">Email: <span class="text-emerald-300 font-bold">${email}</span></p>
                            <div class="p-2.5 bg-emerald-950/40 border border-emerald-800/60 rounded-lg text-emerald-200 text-xs">
                                Is mail ko wapis <b>Available</b> stock me shift kar diya jayega aur Live Stock Math ke mutabiq Bill me se <b>Rs. 18 minus</b> ho jayenge.
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Make Available',
                    confirmButtonColor: '#10b981',
                    cancelButtonText: 'Cancel'
                });
                if (!conf.isConfirmed) return;

                try {
                    const formData = new FormData();
                    formData.append('id', accId);
                    formData.append('email', email);
                    formData.append('status', 'available');
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=update_account_status', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Restocked',
                            text: `${email} is now Available in stock! Bill adjusted.`,
                            timer: 1400,
                            showConfirmButton: false
                        });
                        await this.loadAdminAccounts();
                        await this.refreshStock();
                        await this.loadAdminStats();
                        await this.loadKhataData();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Failed to update status.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Server communication error.' });
                }
            },

            deleteAccount: async function(accId, email, status = '') {
                // If mail is SOLD (Downloaded): show confirmation popup warning that bill will reduce
                if (status === 'downloaded') {
                    const conf = await Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        title: '⚠️ Sold Mail Deletion Warning',
                        html: `
                            <div class="text-left text-xs font-mono space-y-2">
                                <p class="text-rose-300 font-bold">Yeh mail SOLD (Downloaded) status me hai!</p>
                                <p class="text-slate-200">Email: <span class="text-cyan-300 font-bold">${email}</span></p>
                                <div class="p-2.5 bg-rose-950/40 border border-rose-800/60 rounded-lg text-rose-200 text-xs">
                                    <b>Asar (Impact):</b> Is mail ko delete karne se Sold count me se 1 kam ho jayega aur Total Bill me se <b>Rs. 18 minus</b> ho jayenge.
                                </div>
                                <p class="text-slate-400">Kya aap waqai isay delete karna chahte hain?</p>
                            </div>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Delete & Minus Bill',
                        confirmButtonColor: '#ef4444',
                        cancelButtonText: 'Cancel'
                    });
                    if (!conf.isConfirmed) return;
                }
                // If status is 'available', proceeds directly without blocking popup!

                try {
                    const formData = new FormData();
                    formData.append('id', accId);
                    formData.append('email', email);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=delete_account', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Deleted',
                            text: data.message || `${email} deleted.`,
                            timer: 1300,
                            showConfirmButton: false
                        });
                        await this.loadAdminAccounts();
                        await this.refreshStock();
                        await this.loadAdminStats();
                        await this.loadKhataData();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Failed to delete account.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Server communication error.' });
                }
            },

            openRevertOrderModal: async function(orderId) {
                const order = this.allOrders.find(o => o.id == orderId);
                if (!order) return;

                const { value: formValues } = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: `Revert ${order.order_number || ('Order #' + order.id)} to Stock`,
                    html: `
                        <div class="text-left text-xs font-mono space-y-3">
                            <p class="text-slate-300">Is order ke tamam <b>${order.quantity} accounts</b> ko wapis <b>Available</b> stock me shift kar diya jayega.</p>
                            <label class="flex items-center space-x-2 text-rose-300 cursor-pointer">
                                <input type="checkbox" id="swal-del-order" class="rounded border-slate-700">
                                <span>Delete order record from Khata/Billing as well?</span>
                            </label>
                            <div>
                                <label class="block text-[11px] text-slate-400 mb-1">Optional: Paste New Passwords CSV (Email, Password):</label>
                                <textarea id="swal-new-passwords" rows="3" class="w-full p-2 bg-slate-900 border border-slate-700 rounded text-white text-[11px]" placeholder="email,newpassword"></textarea>
                            </div>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Revert to Stock',
                    confirmButtonColor: '#10b981',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        return {
                            deleteOrder: document.getElementById('swal-del-order').checked,
                            passwordsCsv: document.getElementById('swal-new-passwords').value.trim()
                        };
                    }
                });

                if (!formValues) return;

                try {
                    const formData = new FormData();
                    formData.append('order_id', orderId);
                    formData.append('delete_order', formValues.deleteOrder ? '1' : '0');
                    formData.append('passwords_csv', formValues.passwordsCsv);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=revert_order_to_stock', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Order Reverted to Stock',
                            text: data.message || `Reverted ${data.reverted_count} accounts to Available.`,
                            timer: 1600,
                            showConfirmButton: false
                        });
                        await this.loadOrders();
                        await this.loadKhataData();
                        await this.refreshStock();
                        await this.loadAdminAccounts();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Failed to revert order.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Server communication error.' });
                }
            },

            handleFileSelect: function(e) {
                const file = e.target.files[0];
                if (file) {
                    document.getElementById('file-chosen-label').innerHTML = `<span class="text-cyan-400 font-mono">${file.name}</span> (${(file.size / 1024).toFixed(1)} KB)`;
                }
            },

            loadSampleCsv: function() {
                const sample = [
                    "email,password,recovery email",
                    "alpha_01@basis5.ch,VaultP@ss101,recovery1@gmail.com",
                    "alpha_02@basis5.ch,VaultP@ss102,recovery2@gmail.com",
                    "alpha_03@basis5.ch,VaultP@ss103,recovery3@gmail.com",
                    "beta_01@adlover.site,CyberSec!201,sec_rec1@yahoo.com",
                    "beta_02@adlover.site,CyberSec!202,sec_rec2@yahoo.com",
                    "beta_03@adlover.site,CyberSec!203,sec_rec3@yahoo.com"
                ].join("\n");

                document.getElementById('csv-text-input').value = sample;
                Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    icon: 'info',
                    title: 'Sample CSV Populated',
                    text: 'Loaded 6 sample records covering basis5.ch and adlover.site',
                    timer: 1200,
                    showConfirmButton: false
                });
            },

            // ==========================================
            // Stock Ingestion & Destination Allocation
            // ==========================================
            pendingStockUpload: null,
            allocDestination: 'available',

            openStockAllocationModal: function() {
                const modal = document.getElementById('modal-stock-allocation');
                if (modal) modal.classList.remove('hidden');
            },

            closeStockAllocationModal: function() {
                const modal = document.getElementById('modal-stock-allocation');
                if (modal) modal.classList.add('hidden');
            },

            setAllocationDestination: function(dest) {
                this.allocDestination = dest;
                const cardAvail = document.getElementById('alloc-card-available');
                const cardSold = document.getElementById('alloc-card-sold');
                const soldFields = document.getElementById('alloc-sold-fields');
                const confirmBtn = document.getElementById('btn-confirm-allocation');

                if (dest === 'available') {
                    if (cardAvail) cardAvail.className = 'p-3.5 rounded-xl border-2 cursor-pointer transition-all border-cyan-500 bg-cyan-950/30 text-white';
                    if (cardSold) cardSold.className = 'p-3.5 rounded-xl border-2 cursor-pointer transition-all border-slate-700 bg-slate-900/40 text-slate-400 hover:border-slate-600';
                    if (soldFields) soldFields.classList.add('hidden');
                    if (confirmBtn) {
                        confirmBtn.className = 'px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold transition-all shadow-sm flex items-center space-x-2 cursor-pointer';
                        confirmBtn.innerHTML = '<span>CONFIRM TO AVAILABLE STOCK</span>';
                    }
                } else {
                    if (cardSold) cardSold.className = 'p-3.5 rounded-xl border-2 cursor-pointer transition-all border-emerald-500 bg-emerald-950/30 text-white';
                    if (cardAvail) cardAvail.className = 'p-3.5 rounded-xl border-2 cursor-pointer transition-all border-slate-700 bg-slate-900/40 text-slate-400 hover:border-slate-600';
                    if (soldFields) soldFields.classList.remove('hidden');
                    if (confirmBtn) {
                        confirmBtn.className = 'px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold transition-all shadow-sm flex items-center space-x-2 cursor-pointer';
                        confirmBtn.innerHTML = '<span>CONFIRM TO SOLD ORDERS</span>';
                    }
                    this.recalcAllocTotal();
                }
            },

            recalcAllocTotal: function() {
                const count = this.pendingStockUpload?.count || 0;
                const rate = parseFloat(document.getElementById('alloc-rate-per-mail')?.value || '18') || 18;
                const total = Math.round(count * rate);
                const disp = document.getElementById('alloc-total-display');
                if (disp) disp.value = 'Rs. ' + total.toLocaleString();
            },

            handleCsvUpload: async function(e) {
                e.preventDefault();
                const fileInput = document.getElementById('csv-file-input');
                const rawText = document.getElementById('csv-text-input').value.trim();

                if (!fileInput.files[0] && !rawText) {
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'warning',
                        title: 'No Data Provided',
                        text: 'Please select a CSV file or paste CSV text to ingest.'
                    });
                    return;
                }

                // Parse content to get count preview and domain
                let textToParse = rawText;
                let fileObj = fileInput.files[0] || null;

                if (fileObj && !textToParse) {
                    try {
                        textToParse = await fileObj.text();
                    } catch (err) {
                        console.error(err);
                    }
                }

                let lines = (textToParse || '').split(/\r\n|\n|\r/);
                let validMails = [];
                let detectedDomain = 'basis5.ch';

                lines.forEach(l => {
                    const row = l.trim();
                    if (!row) return;
                    const parts = row.split(',');
                    const email = (parts[0] || '').trim();
                    if (email && email.includes('@') && email.includes('.')) {
                        validMails.push(email);
                        const at = email.lastIndexOf('@');
                        if (at !== -1) {
                            detectedDomain = email.substring(at + 1).toLowerCase();
                        }
                    }
                });

                if (validMails.length === 0) {
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'warning',
                        title: 'No Valid Accounts',
                        text: 'CSV me koi valid email addresses detect nahi hue. Format: email,password,recovery email'
                    });
                    return;
                }

                this.pendingStockUpload = {
                    file: fileObj,
                    rawText: textToParse || rawText,
                    count: validMails.length,
                    domain: detectedDomain
                };

                // Populate modal
                const countElem = document.getElementById('alloc-preview-count');
                if (countElem) countElem.innerText = `Parsed ${validMails.length} valid accounts (${detectedDomain})`;
                
                const nextNum = (this.allOrders?.length || 0) + 1;
                const orderNumElem = document.getElementById('alloc-order-number');
                if (orderNumElem) orderNumElem.value = 'Order #' + nextNum;
                
                const now = new Date();
                const localIso = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                const orderDateElem = document.getElementById('alloc-order-date');
                if (orderDateElem) orderDateElem.value = localIso;

                const rateElem = document.getElementById('alloc-rate-per-mail');
                if (rateElem) rateElem.value = '18';
                const notesElem = document.getElementById('alloc-notes');
                if (notesElem) notesElem.value = '';

                this.setAllocationDestination('available');
                this.openStockAllocationModal();
            },

            confirmStockAllocation: async function() {
                if (!this.pendingStockUpload) return;
                const btn = document.getElementById('btn-confirm-allocation');
                const origText = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.innerHTML = '<span>INGESTING...</span>';
                    btn.disabled = true;
                }

                try {
                    const dest = this.allocDestination;
                    if (dest === 'available') {
                        const formData = new FormData();
                        if (this.pendingStockUpload.file) {
                            formData.append('csv_file', this.pendingStockUpload.file);
                        }
                        if (this.pendingStockUpload.rawText) {
                            formData.append('csv_text', this.pendingStockUpload.rawText);
                        }
                        formData.append('target_status', 'make_available');
                        formData.append('csrf_token', this.csrfToken);

                        const res = await fetch('api.php?action=upload_csv', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await res.json();
                        if (data.csrf_token) {
                            this.csrfToken = data.csrf_token;
                        }

                        if (data.success) {
                            this.closeStockAllocationModal();
                            const fileIn = document.getElementById('csv-file-input');
                            if (fileIn) fileIn.value = '';
                            const txtIn = document.getElementById('csv-text-input');
                            if (txtIn) txtIn.value = '';
                            const label = document.getElementById('file-chosen-label');
                            if (label) label.innerText = 'Click or drag a CSV file here';
                            this.pendingStockUpload = null;

                            Swal.fire({
                                customClass: { popup: 'cyber-swal' },
                                icon: 'success',
                                title: 'Stock Ingested to Available',
                                html: `
                                    <div class="text-xs text-slate-300 space-y-1.5 text-left font-mono">
                                        <p class="text-emerald-400 font-bold">+${data.inserted} New Accounts Added</p>
                                        ${data.updated > 0 ? `<p class="text-cyan-300 font-semibold">🔄 ${data.updated} Existing Accounts Updated</p>` : ''}
                                        <p class="text-slate-400 text-[11px] mt-2">Yeh stock Rana Asim ke Client Portal par live extraction ke liye available ho gaya hai. Bill me koi izafa nahi hua.</p>
                                    </div>
                                `
                            });

                            try { await this.loadAdminData(); } catch(e) { console.warn('Refresh admin data error:', e); }
                            try { await this.loadKhataData(); } catch(e) { console.warn('Refresh khata error:', e); }
                        } else {
                            Swal.fire({
                                customClass: { popup: 'cyber-swal' },
                                icon: 'error',
                                title: 'Ingestion Error',
                                text: data.message || 'Could not insert accounts'
                            });
                        }
                    } else {
                        // Sold order allocation
                        const orderNum = document.getElementById('alloc-order-number')?.value.trim() || ('Order #' + ((this.allOrders?.length || 0) + 1));
                        const orderDateInput = document.getElementById('alloc-order-date')?.value;
                        const orderDate = orderDateInput ? orderDateInput.replace('T', ' ') + ':00' : '';
                        const ratePerMail = parseFloat(document.getElementById('alloc-rate-per-mail')?.value || '18') || 18;
                        const notes = document.getElementById('alloc-notes')?.value.trim() || 'Direct Delivery Batch';
                        const totalPrice = Math.round(this.pendingStockUpload.count * ratePerMail);

                        const formData = new FormData();
                        if (this.pendingStockUpload.file) {
                            formData.append('csv_file', this.pendingStockUpload.file);
                        }
                        if (this.pendingStockUpload.rawText) {
                            formData.append('csv_text', this.pendingStockUpload.rawText);
                        }
                        formData.append('order_number', orderNum);
                        formData.append('domain', this.pendingStockUpload.domain);
                        formData.append('created_at', orderDate);
                        formData.append('rate_per_mail', ratePerMail);
                        formData.append('total_price', totalPrice);
                        formData.append('notes', notes);
                        formData.append('csrf_token', this.csrfToken);

                        const res = await fetch('api.php?action=save_order', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await res.json();
                        if (data.csrf_token) {
                            this.csrfToken = data.csrf_token;
                        }

                        if (data.success) {
                            this.closeStockAllocationModal();
                            const fileIn = document.getElementById('csv-file-input');
                            if (fileIn) fileIn.value = '';
                            const txtIn = document.getElementById('csv-text-input');
                            if (txtIn) txtIn.value = '';
                            const label = document.getElementById('file-chosen-label');
                            if (label) label.innerText = 'Click or drag a CSV file here';
                            this.pendingStockUpload = null;

                            Swal.fire({
                                customClass: { popup: 'cyber-swal' },
                                icon: 'success',
                                title: 'Delivered Order Recorded',
                                html: `
                                    <div class="text-xs text-slate-300 space-y-1.5 text-left font-mono">
                                        <p class="text-emerald-400 font-bold">✅ ${orderNum} created with ${data.quantity || 'all'} accounts</p>
                                        <p class="text-cyan-300">Rate: Rs. ${ratePerMail}/mail | Total Bill: Rs. ${(data.total_price !== undefined ? data.total_price : totalPrice).toLocaleString()}</p>
                                        ${data.billable_qty !== undefined && data.billable_qty < data.quantity ? `<p class="text-amber-300 text-xs font-semibold">⚠️ ${data.billable_qty} new accounts billed (${data.quantity - data.billable_qty} duplicates skipped from bill)</p>` : ''}
                                        <p class="text-slate-400 text-[11px] mt-2">Accounts mark ho gaye hain Delivered (Sold), Khata ledger update ho gaya hai, aur Client portal par CSV download button ke sath show ho raha hai.</p>
                                    </div>
                                `
                            });

                            try { await this.loadAdminData(); } catch(e) { console.warn('Refresh admin data error:', e); }
                            try { await this.loadOrders(); } catch(e) { console.warn('Refresh orders error:', e); }
                            try { await this.loadKhataData(); } catch(e) { console.warn('Refresh khata error:', e); }
                        } else if (data.duplicate) {
                            Swal.fire({
                                customClass: { popup: 'cyber-swal' },
                                icon: 'warning',
                                title: 'Double-Billing Protection',
                                html: `
                                    <div class="text-xs text-slate-300 space-y-2 text-left font-mono">
                                        <p class="text-amber-400 font-bold">⚠️ ${data.message}</p>
                                        <p class="text-slate-400 text-[11px]">System ne client ko double-bill hone se bacha liya hai. Koi duplicate order create nahi hua aur na hi bill increase hua.</p>
                                    </div>
                                `
                            });
                        } else {
                            Swal.fire({
                                customClass: { popup: 'cyber-swal' },
                                icon: 'error',
                                title: 'Order Creation Error',
                                text: data.message || 'Could not save order record'
                            });
                        }
                    }
                } catch (err) {
                    console.error('Allocation error:', err);
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'error',
                        title: 'Error',
                        text: err?.message ? `Allocation error: ${err.message}` : 'Server communication error during allocation.'
                    });
                } finally {
                    if (btn) {
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    }
                }
            },

            // ==========================================
            // Orders & Extraction History Hub
            // ==========================================
            loadOrders: async function() {
                try {
                    const res = await fetch('api.php?action=orders');
                    const data = await res.json();
                    if (!data.success) return;

                    this.allOrders = (data.orders || []).sort((a, b) => {
                        const numA = (a.order_number && a.order_number.match(/\d+/)) ? parseInt(a.order_number.match(/\d+/)[0], 10) : Number(a.id || 999999);
                        const numB = (b.order_number && b.order_number.match(/\d+/)) ? parseInt(b.order_number.match(/\d+/)[0], 10) : Number(b.id || 999999);
                        return numA - numB;
                    });

                    if (this.user && this.user.role === 'admin') {
                        this.renderAdminOrders();
                    } else {
                        this.renderClientOrders();
                    }
                } catch (e) {
                    console.error('Failed to load orders:', e);
                }
            },

            renderClientOrders: function() {
                const tbody = document.getElementById('client-orders-tbody');
                if (!tbody) return;
                tbody.innerHTML = '';

                if (this.allOrders.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="p-6 text-center text-slate-500 font-mono">Abhi tak koi extraction order record nahi hua</td></tr>';
                    return;
                }

                this.allOrders.forEach((o) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-900/50 transition-colors font-mono';
                    const orderName = o.order_number || ('#' + o.id);
                    const rateVal = o.rate_per_mail ? Number(o.rate_per_mail).toFixed(2) : '18.00';
                    const totalVal = o.total_price ? Number(o.total_price).toLocaleString() : (o.quantity * 18).toLocaleString();

                    tr.innerHTML = `
                        <td class="p-3 text-cyan-300 font-bold">${orderName}</td>
                        <td class="p-3 text-slate-300 text-[11px]">${o.created_at}</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-[11px] bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">${o.domain}</span></td>
                        <td class="p-3 text-emerald-400 font-bold">${o.quantity} accounts</td>
                        <td class="p-3 text-slate-300">Rs. ${rateVal}</td>
                        <td class="p-3 text-emerald-300 font-bold">Rs. ${totalVal}</td>
                        <td class="p-3 text-right space-x-1.5 whitespace-nowrap">
                            <button type="button" onclick="VaultApp.openOrderModal(${o.id})" title="Inspect Mails" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-300 text-xs border border-slate-700 transition-all">
                                <span>Inspect</span>
                            </button>
                            <button type="button" onclick="VaultApp.downloadOrderById(${o.id})" title="Re-download File" class="px-2.5 py-1 rounded-lg bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 text-xs border border-cyan-500/40 transition-all font-semibold">
                                <span>Download</span>
                            </button>
                            <button type="button" onclick="VaultApp.copyOrderMails(${o.id})" title="Copy Mails" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs border border-slate-700 transition-all">
                                <span>Copy</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            },

            setAdminTab: function(tab) {
                this.adminActiveTab = tab;
                const tabs = ['inventory', 'orders', 'payments', 'replacements', 'search'];
                tabs.forEach(t => {
                    const btn = document.getElementById(`tab-btn-${t}`);
                    const pnl = document.getElementById(`admin-panel-${t}`);
                    if (t === tab) {
                        if (btn) btn.className = 'px-3.5 py-2 rounded-lg text-xs font-mono font-bold transition-all bg-cyan-500 text-slate-950 flex items-center space-x-1.5 shadow-sm';
                        if (pnl) pnl.classList.remove('hidden');
                    } else {
                        if (btn) btn.className = 'px-3.5 py-2 rounded-lg text-xs font-mono font-bold transition-all text-slate-400 hover:text-white flex items-center space-x-1.5';
                        if (pnl) pnl.classList.add('hidden');
                    }
                });

                if (tab === 'orders') this.renderAdminOrders();
                if (tab === 'payments') this.renderAdminPayments();
                if (tab === 'replacements') this.renderAdminReplacements();
            },

            renderAdminOrders: function() {
                const badge = document.getElementById('admin-orders-badge');
                if (badge) badge.innerText = this.allOrders.length;

                const tbody = document.getElementById('admin-orders-tbody');
                if (!tbody) return;
                tbody.innerHTML = '';

                const searchInput = document.getElementById('admin-orders-search');
                const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

                const filtered = this.allOrders.filter(o => {
                    if (!query) return true;
                    return String(o.id).includes(query) ||
                           (o.order_number && o.order_number.toLowerCase().includes(query)) ||
                           (o.domain && o.domain.toLowerCase().includes(query)) ||
                           (o.notes && o.notes.toLowerCase().includes(query)) ||
                           (o.created_at && o.created_at.toLowerCase().includes(query));
                });

                if (filtered.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="p-6 text-center text-slate-500 font-mono">No orders matched criteria</td></tr>';
                    return;
                }

                filtered.forEach(o => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-900/50 transition-colors font-mono';
                    const orderName = o.order_number || ('Order #' + o.id);
                    const rateVal = o.rate_per_mail ? Number(o.rate_per_mail).toFixed(2) : '18.00';
                    const totalVal = o.total_price ? Number(o.total_price).toLocaleString() : (o.quantity * 18).toLocaleString();
                    const noteText = o.notes ? o.notes : '-';
                    const isReverted = (o.status && o.status === 'reverted') || (o.notes && o.notes.includes('[REVERTED TO AVAILABLE STOCK]'));
                    const safeOrderName = (o.order_number || ('Order #' + o.id)).replace(/'/g, "\\'");
                    const statusHtml = `
                        <select onchange="VaultApp.changeOrderStatus(${o.id}, this.value, '${safeOrderName}', ${o.quantity})" class="px-2 py-1 rounded text-[11px] font-bold font-mono border focus:outline-none cursor-pointer transition-all ${isReverted ? 'bg-amber-950/90 text-amber-300 border-amber-500/70 hover:border-amber-400' : 'bg-emerald-950/90 text-emerald-300 border-emerald-500/70 hover:border-emerald-400'}" title="Status change karein: Revert karne par mails Available ho jayengi aur bill kam ho jayega">
                            <option value="delivered" ${!isReverted ? 'selected' : ''}>✅ Delivered (Sold)</option>
                            <option value="reverted" ${isReverted ? 'selected' : ''}>🔄 Available (Reverted)</option>
                        </select>
                    `;

                    tr.innerHTML = `
                        <td class="p-3 text-cyan-300 font-bold">${orderName}</td>
                        <td class="p-3 text-slate-300 text-[11px]">${o.created_at}</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-[11px] bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">${o.domain}</span></td>
                        <td class="p-3 text-emerald-400 font-bold">${o.quantity} accounts</td>
                        <td class="p-3">
                            <button type="button" onclick="VaultApp.quickChangeOrderPrice(${o.id}, ${o.rate_per_mail || 18}, ${o.quantity})" class="text-amber-300 hover:text-amber-200 underline font-mono flex items-center space-x-1 cursor-pointer" title="Click to quickly change rate or price">
                                <span>Rs. ${rateVal}</span>
                                <svg class="w-3 h-3 text-slate-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                        </td>
                        <td class="p-3 text-emerald-300 font-bold">Rs. ${totalVal}</td>
                        <td class="p-3">${statusHtml}</td>
                        <td class="p-3 text-slate-400 text-[11px] max-w-xs truncate" title="${noteText}">${noteText}</td>
                        <td class="p-3 text-right space-x-1 whitespace-nowrap">
                            <button type="button" onclick="VaultApp.openOrderModal(${o.id})" title="Inspect Accounts" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-300 text-xs border border-slate-700 transition-all">
                                <span>Inspect</span>
                            </button>
                            <button type="button" onclick="VaultApp.downloadOrderById(${o.id})" title="Download CSV" class="px-2 py-1 rounded bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-300 text-xs border border-cyan-500/40 transition-all font-semibold">
                                <span>Download</span>
                            </button>
                            <button type="button" onclick="VaultApp.copyOrderMails(${o.id})" title="Copy Mails" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs border border-slate-700 transition-all">
                                <span>Copy</span>
                            </button>
                            ${isReverted ? `
                                <button type="button" onclick="VaultApp.changeOrderStatus(${o.id}, 'delivered', '${safeOrderName}', ${o.quantity})" title="Mark Delivered & Re-bill" class="px-2 py-1 rounded bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 text-xs border border-emerald-500/40 transition-all font-semibold">
                                    <span>Mark Sold</span>
                                </button>
                            ` : `
                                <button type="button" onclick="VaultApp.changeOrderStatus(${o.id}, 'reverted', '${safeOrderName}', ${o.quantity})" title="Revert to Available Stock (Wapis Stock Me Bhein & Bill Kam Karein)" class="px-2 py-1 rounded bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs border border-amber-500/40 transition-all font-semibold">
                                    <span>Revert</span>
                                </button>
                            `}
                            <button type="button" onclick="VaultApp.openEditOrderModal(${o.id})" title="Edit Order Metadata" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 text-xs border border-slate-700 transition-all">
                                <span>Edit</span>
                            </button>
                            <button type="button" onclick="VaultApp.deleteOrder(${o.id})" title="Delete Order (Mails automatically revert to Stock & Bill kam hoga)" class="px-2 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs border border-rose-500/30 transition-all">
                                <span>Delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            },

            // ==========================================
            // Khata & Financial Ledger Sync
            // ==========================================
            loadFinancialSummary: async function() {
                return await this.loadKhataData();
            },

            loadKhataData: async function() {
                try {
                    const res = await fetch('api.php?action=financial_summary');
                    const data = await res.json();
                    if (!data.success) return;

                    this.financialData = data;
                    const grossMails = parseInt(data.gross_sold_mails, 10) || 0;
                    const repMails = parseInt(data.replaced_mails, 10) || 0;
                    const netMails = parseInt(data.net_active_mails, 10) || 0;
                    const netBilled = parseFloat(data.net_billed_amount) || 0;
                    const totalPaid = parseFloat(data.total_paid_amount) || 0;
                    const pending = parseFloat(data.pending_balance) || 0;
                    const totalDeductions = parseFloat(data.total_deductions) || 0;

                    // Update Admin Cards
                    const akGross = document.getElementById('ak-gross-mails');
                    if (akGross) akGross.innerText = grossMails.toLocaleString();

                    const akRep = document.getElementById('ak-replaced-mails');
                    if (akRep) akRep.innerText = `-${repMails.toLocaleString()}`;

                    const akNet = document.getElementById('ak-net-mails');
                    if (akNet) akNet.innerText = netMails.toLocaleString();

                    const akBill = document.getElementById('ak-total-bill');
                    if (akBill) akBill.innerText = `Rs. ${Math.round(netBilled).toLocaleString()}`;

                    const akPaid = document.getElementById('ak-total-paid');
                    if (akPaid) akPaid.innerText = `Rs. ${Math.round(totalPaid).toLocaleString()}`;

                    const akPending = document.getElementById('ak-pending-balance');
                    if (akPending) akPending.innerText = `Rs. ${Math.round(pending).toLocaleString()}`;

                    const akStatus = document.getElementById('ak-balance-status');
                    if (akStatus) {
                        if (pending > 0) {
                            akStatus.innerText = 'Payable balance';
                            akStatus.className = 'text-[10px] text-rose-400 font-mono mt-0.5 font-bold';
                        } else if (pending < 0) {
                            akStatus.innerText = 'Advance received';
                            akStatus.className = 'text-[10px] text-emerald-400 font-mono mt-0.5 font-bold';
                        } else {
                            akStatus.innerText = 'All dues cleared';
                            akStatus.className = 'text-[10px] text-cyan-400 font-mono mt-0.5 font-bold';
                        }
                    }

                    // Update Client Cards
                    const ckGross = document.getElementById('ck-gross-mails');
                    if (ckGross) ckGross.innerText = grossMails.toLocaleString();

                    const ckRep = document.getElementById('ck-replaced-mails');
                    if (ckRep) ckRep.innerText = `-${repMails.toLocaleString()}`;

                    const ckNet = document.getElementById('ck-net-mails');
                    if (ckNet) ckNet.innerText = netMails.toLocaleString();

                    const ckBill = document.getElementById('ck-total-bill');
                    if (ckBill) ckBill.innerText = `Rs. ${Math.round(netBilled).toLocaleString()}`;

                    const ckPaid = document.getElementById('ck-total-paid');
                    if (ckPaid) ckPaid.innerText = `Rs. ${Math.round(totalPaid).toLocaleString()}`;

                    const ckPending = document.getElementById('ck-pending-balance');
                    if (ckPending) ckPending.innerText = `Rs. ${Math.round(pending).toLocaleString()}`;

                    const ckStatus = document.getElementById('ck-balance-status');
                    if (ckStatus) {
                        if (pending > 0) {
                            ckStatus.innerText = 'Payable balance';
                            ckStatus.className = 'text-[10px] text-rose-400 font-mono mt-0.5 font-bold';
                        } else if (pending < 0) {
                            ckStatus.innerText = 'Advance paid';
                            ckStatus.className = 'text-[10px] text-emerald-400 font-mono mt-0.5 font-bold';
                        } else {
                            ckStatus.innerText = 'Zero balance / Cleared';
                            ckStatus.className = 'text-[10px] text-cyan-400 font-mono mt-0.5 font-bold';
                        }
                    }

                    // Client Replacement Card
                    const clientRepCount = document.getElementById('client-rep-card-count');
                    if (clientRepCount) clientRepCount.innerText = repMails.toLocaleString();

                    const clientRepAmount = document.getElementById('client-rep-card-amount');
                    if (clientRepAmount) clientRepAmount.innerText = `-Rs. ${Math.round(totalDeductions).toLocaleString()}`;

                } catch (e) {
                    console.error('Failed to load Khata data:', e);
                }
            },

            // ==========================================
            // Payments (Wasooli) Tracker
            // ==========================================
            loadPayments: async function() {
                try {
                    const res = await fetch('api.php?action=list_payments');
                    const data = await res.json();
                    if (!data.success) return;

                    this.allPayments = data.payments || [];
                    const badge = document.getElementById('admin-payments-badge');
                    if (badge) badge.innerText = this.allPayments.length;

                    this.renderAdminPayments();
                    this.renderClientPayments();
                } catch (e) {
                    console.error('Failed to load payments:', e);
                }
            },

            renderAdminPayments: function() {
                const tbody = document.getElementById('admin-payments-tbody');
                if (!tbody) return;
                tbody.innerHTML = '';

                if (this.allPayments.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="p-6 text-center text-slate-500 font-mono">No payment records found yet. Click "+ Record Payment" to add.</td></tr>';
                    return;
                }

                this.allPayments.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-900/50 transition-colors font-mono';
                    const amountVal = Number(p.amount).toLocaleString();
                    const noteText = p.reference_note ? p.reference_note : '-';

                    tr.innerHTML = `
                        <td class="p-3 text-emerald-400 font-bold">PAY-#${p.id}</td>
                        <td class="p-3 text-slate-300 text-[11px]">${p.payment_date}</td>
                        <td class="p-3 text-white font-semibold">${p.client_username || 'Rana Asim'}</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-[11px] bg-slate-800 text-slate-300 border border-slate-700">${p.payment_method || 'Bank Transfer'}</span></td>
                        <td class="p-3 text-emerald-300 font-bold">Rs. ${amountVal}</td>
                        <td class="p-3 text-slate-400 text-[11px] max-w-xs truncate" title="${noteText}">${noteText}</td>
                        <td class="p-3 text-right space-x-1.5 whitespace-nowrap">
                            <button type="button" onclick="VaultApp.openRecordPaymentModal(${p.id})" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-300 text-xs border border-slate-700 transition-all">
                                <span>Edit</span>
                            </button>
                            <button type="button" onclick="VaultApp.deletePayment(${p.id})" class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs border border-rose-500/30 transition-all">
                                <span>Delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            },

            renderClientPayments: function() {
                const tbody = document.getElementById('client-payments-tbody');
                if (!tbody) return;
                tbody.innerHTML = '';

                if (this.allPayments.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-500 font-mono">Abhi tak koi payment record darj nahi hua</td></tr>';
                    return;
                }

                this.allPayments.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-900/50 transition-colors font-mono';
                    const amountVal = Number(p.amount).toLocaleString();
                    const noteText = p.reference_note ? p.reference_note : '-';

                    tr.innerHTML = `
                        <td class="p-3 text-emerald-400 font-bold">PAY-#${p.id}</td>
                        <td class="p-3 text-slate-300 text-[11px]">${p.payment_date}</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-[11px] bg-slate-800 text-slate-300 border border-slate-700">${p.payment_method || 'Bank Transfer'}</span></td>
                        <td class="p-3 text-emerald-300 font-bold">Rs. ${amountVal}</td>
                        <td class="p-3 text-slate-400 text-[11px] max-w-xs truncate" title="${noteText}">${noteText}</td>
                        <td class="p-3 text-right"><span class="px-2 py-0.5 rounded text-[10px] uppercase bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 font-bold">VERIFIED</span></td>
                    `;
                    tbody.appendChild(tr);
                });
            },

            openRecordPaymentModal: function(paymentId = 0) {
                const form = document.getElementById('form-payment');
                if (form) form.reset();

                const idInput = document.getElementById('payment-id');
                const titleEl = document.getElementById('payment-modal-title');

                if (paymentId > 0) {
                    const p = this.allPayments.find(x => x.id == paymentId);
                    if (p) {
                        if (idInput) idInput.value = p.id;
                        document.getElementById('payment-amount').value = p.amount;
                        document.getElementById('payment-date').value = p.payment_date;
                        document.getElementById('payment-method').value = p.payment_method;
                        document.getElementById('payment-ref-note').value = p.reference_note || '';
                        if (titleEl) titleEl.innerText = `Edit Payment PAY-#${p.id}`;
                    }
                } else {
                    if (idInput) idInput.value = '0';
                    if (titleEl) titleEl.innerText = 'Record Payment (Wasooli)';
                    const now = new Date();
                    const pad = n => String(n).padStart(2, '0');
                    const ts = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                    document.getElementById('payment-date').value = ts;
                }

                document.getElementById('payment-modal').classList.remove('hidden');
            },

            closePaymentModal: function() {
                document.getElementById('payment-modal').classList.add('hidden');
            },

            handleSavePayment: async function(e) {
                if (e && e.preventDefault) e.preventDefault();
                const id = document.getElementById('payment-id').value;
                const amount = document.getElementById('payment-amount').value;
                const paymentDate = document.getElementById('payment-date').value.trim();
                const method = document.getElementById('payment-method').value;
                const refNote = document.getElementById('payment-ref-note').value.trim();

                if (!amount || parseFloat(amount) <= 0) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'warning', title: 'Invalid Amount', text: 'Please enter a valid payment amount.' });
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('amount', amount);
                    formData.append('payment_date', paymentDate);
                    formData.append('payment_method', method);
                    formData.append('reference_note', refNote);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=save_payment', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Payment Saved',
                            text: data.message || 'Payment recorded successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        this.closePaymentModal();
                        await this.loadPayments();
                        await this.loadKhataData();
                    } else {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to save payment.'
                        });
                    }
                } catch (err) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Connection Error', text: 'Failed to communicate with the server.' });
                }
            },

            deletePayment: async function(paymentId) {
                const conf = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: 'Delete Payment Entry?',
                    text: `Are you sure you want to delete payment record PAY-#${paymentId}? Baqaya balance recalculate ho jayega.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    confirmButtonColor: '#e11d48',
                    cancelButtonText: 'Cancel'
                });

                if (!conf.isConfirmed) return;

                try {
                    const formData = new FormData();
                    formData.append('id', paymentId);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=delete_payment', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Deleted',
                            text: `Payment entry PAY-#${paymentId} deleted.`,
                            timer: 1200,
                            showConfirmButton: false
                        });
                        await this.loadPayments();
                        await this.loadKhataData();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Could not delete payment.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Connection Error', text: 'Error connecting to server.' });
                }
            },

            // ==========================================
            // Replacements & Faulty Deductions
            // ==========================================
            loadReplacements: async function() {
                try {
                    const res = await fetch('api.php?action=list_replacements');
                    const data = await res.json();
                    if (!data.success) return;

                    this.allReplacements = data.replacements || [];
                    const badge = document.getElementById('admin-replacements-badge');
                    if (badge) badge.innerText = this.allReplacements.length;

                    this.renderAdminReplacements();
                    this.renderInspectReplacements();
                } catch (e) {
                    console.error('Failed to load replacements:', e);
                }
            },

            renderAdminReplacements: function() {
                const tbody = document.getElementById('admin-replacements-tbody');
                if (!tbody) return;
                tbody.innerHTML = '';

                if (this.allReplacements.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="p-6 text-center text-slate-500 font-mono">No faulty replacements recorded yet.</td></tr>';
                    return;
                }

                this.allReplacements.forEach(r => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-900/50 transition-colors font-mono';
                    tr.innerHTML = `
                        <td class="p-3 text-amber-400 font-bold">#${r.id}</td>
                        <td class="p-3 text-cyan-300 font-semibold select-all">${r.email}</td>
                        <td class="p-3"><span class="px-2 py-0.5 rounded text-[11px] bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">${r.domain || '-'}</span></td>
                        <td class="p-3 text-amber-300 font-bold">-Rs. ${r.rate_deduction || 18}</td>
                        <td class="p-3 text-slate-300 text-[11px]">${r.reason || 'Faulty'}</td>
                        <td class="p-3 text-slate-400 text-[11px]">${r.created_at || '-'}</td>
                        <td class="p-3 text-right">
                            <button type="button" onclick="VaultApp.removeReplacement(${r.id}, '${r.email}')" class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs border border-rose-500/30 transition-all">
                                <span>Remove</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            },

            renderInspectReplacements: function() {
                const tbody = document.getElementById('inspect-rep-tbody');
                const sub = document.getElementById('inspect-rep-subtitle');
                if (sub) sub.innerText = `Total ${this.allReplacements.length} replaced accounts automatically deducted from bill.`;
                if (!tbody) return;
                tbody.innerHTML = '';

                if (this.allReplacements.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-500 font-mono">No replaced accounts in record.</td></tr>';
                    return;
                }

                this.allReplacements.forEach((r, i) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-900/60 transition-colors font-mono';
                    tr.innerHTML = `
                        <td class="p-2.5 text-slate-500">${i + 1}</td>
                        <td class="p-2.5 text-cyan-300 font-semibold select-all">${r.email}</td>
                        <td class="p-2.5"><span class="px-2 py-0.5 rounded text-[10px] bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">${r.domain || '-'}</span></td>
                        <td class="p-2.5 text-amber-300 font-bold">-Rs. ${r.rate_deduction || 18}</td>
                        <td class="p-2.5 text-slate-300 text-[11px]">${r.reason || 'Faulty'}</td>
                        <td class="p-2.5 text-slate-400 text-[11px]">${r.created_at || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            },

            openAddReplacementsModal: function() {
                const form = document.getElementById('form-replacements');
                if (form) form.reset();
                document.getElementById('rep-rate-deduction').value = '18';
                document.getElementById('rep-reason').value = 'Faulty / Disabled';
                document.getElementById('replacements-modal').classList.remove('hidden');
            },

            closeReplacementsModal: function() {
                document.getElementById('replacements-modal').classList.add('hidden');
            },

            openInspectReplacementsModal: function() {
                this.renderInspectReplacements();
                document.getElementById('inspect-replacements-modal').classList.remove('hidden');
            },

            closeInspectReplacementsModal: function() {
                document.getElementById('inspect-replacements-modal').classList.add('hidden');
            },

            handleAddReplacements: async function(e) {
                if (e && e.preventDefault) e.preventDefault();
                const rawText = document.getElementById('rep-emails-text').value.trim();
                const rateDeduction = document.getElementById('rep-rate-deduction').value;
                const reason = document.getElementById('rep-reason').value.trim();

                if (!rawText) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'warning', title: 'Emails Required', text: 'Please enter faulty email accounts to deduct.' });
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('emails_text', rawText);
                    formData.append('rate_deduction', rateDeduction);
                    formData.append('reason', reason);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=add_replacements', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Replacements Deducted',
                            text: data.message || `${data.added_count} accounts deducted from bill.`,
                            timer: 1600,
                            showConfirmButton: false
                        });
                        this.closeReplacementsModal();
                        await this.loadReplacements();
                        await this.loadKhataData();
                        await this.loadAdminData();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Failed to add replacements.' });
                    }
                } catch (err) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Connection Error', text: 'Error connecting to server.' });
                }
            },

            removeReplacement: async function(repId, email) {
                const conf = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: 'Remove From Replacements?',
                    text: `Remove ${email} from replacements? Bill deduction will be reversed and account restored to sold count.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    confirmButtonColor: '#06b6d4',
                    cancelButtonText: 'Cancel'
                });

                if (!conf.isConfirmed) return;

                try {
                    const formData = new FormData();
                    formData.append('id', repId);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=remove_replacement', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Removed',
                            text: 'Account removed from replacements.',
                            timer: 1200,
                            showConfirmButton: false
                        });
                        await this.loadReplacements();
                        await this.loadKhataData();
                        await this.loadAdminData();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Could not remove replacement.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Connection Error', text: 'Error connecting to server.' });
                }
            },

            // ==========================================
            // Ingest & Edit Orders (Admin)
            // ==========================================
            openIngestOrderModal: function() {
                const form = document.getElementById('form-ingest-order');
                if (form) form.reset();
                document.getElementById('ingest-order-rate').value = '18';
                const now = new Date();
                const pad = n => String(n).padStart(2, '0');
                const ts = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                document.getElementById('ingest-order-date').value = ts;
                document.getElementById('ingest-order-modal').classList.remove('hidden');
            },

            closeIngestOrderModal: function() {
                document.getElementById('ingest-order-modal').classList.add('hidden');
            },

            handleIngestOrder: async function(e) {
                if (e && e.preventDefault) e.preventDefault();
                const orderNum = document.getElementById('ingest-order-number').value.trim();
                const orderDate = document.getElementById('ingest-order-date').value.trim();
                const domain = document.getElementById('ingest-order-domain').value;
                const rate = document.getElementById('ingest-order-rate').value;
                const customTotal = document.getElementById('ingest-order-total').value;
                const notes = document.getElementById('ingest-order-notes').value.trim();
                const fileInput = document.getElementById('ingest-order-file');
                const rawText = document.getElementById('ingest-order-text').value.trim();

                if (!fileInput.files[0] && !rawText) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'warning', title: 'Data Missing', text: 'Please attach a CSV file or paste raw email records.' });
                    return;
                }

                const btn = document.getElementById('btn-submit-ingest-order');
                const origText = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.innerHTML = '<span>SAVING ORDER...</span>';
                    btn.disabled = true;
                }

                try {
                    const formData = new FormData();
                    formData.append('order_number', orderNum);
                    formData.append('created_at', orderDate);
                    formData.append('domain', domain);
                    formData.append('rate_per_mail', rate);
                    if (customTotal) formData.append('total_price', customTotal);
                    formData.append('notes', notes);
                    if (fileInput.files[0]) formData.append('csv_file', fileInput.files[0]);
                    if (rawText) formData.append('csv_text', rawText);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=save_order', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Order Ingested',
                            html: `
                                <div class="text-xs text-slate-300 space-y-1 font-mono">
                                    <p class="text-emerald-400 font-bold">${data.order_number} Saved Successfully</p>
                                    <p>Quantity: <b class="text-white">${data.quantity}</b> accounts</p>
                                    <p>Total Billed: <b class="text-cyan-300">Rs. ${Number(data.total_price).toLocaleString()}</b></p>
                                </div>
                            `
                        });
                        this.closeIngestOrderModal();
                        await this.loadOrders();
                        await this.loadKhataData();
                        await this.loadAdminData();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Failed to ingest order.' });
                    }
                } catch (err) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Connection Error', text: 'Failed to communicate with server.' });
                } finally {
                    if (btn) {
                        btn.innerHTML = origText;
                        btn.disabled = false;
                    }
                }
            },

            openEditOrderModal: function(orderId) {
                const order = this.allOrders.find(o => o.id == orderId);
                if (!order) return;

                document.getElementById('edit-order-id').value = order.id;
                document.getElementById('edit-order-number').value = order.order_number || `Order #${order.id}`;
                document.getElementById('edit-order-date').value = order.created_at || '';
                document.getElementById('edit-order-rate').value = order.rate_per_mail || 18;
                document.getElementById('edit-order-total').value = order.total_price || (order.quantity * (order.rate_per_mail || 18));
                document.getElementById('edit-order-notes').value = order.notes || '';
                document.getElementById('edit-order-title').innerText = `Edit ${order.order_number || 'Order #' + order.id}`;

                document.getElementById('edit-order-modal').classList.remove('hidden');
            },

            closeEditOrderModal: function() {
                document.getElementById('edit-order-modal').classList.add('hidden');
            },

            handleEditOrder: async function(e) {
                if (e && e.preventDefault) e.preventDefault();
                const orderId = document.getElementById('edit-order-id').value;
                const orderNumber = document.getElementById('edit-order-number').value.trim();
                const orderDate = document.getElementById('edit-order-date').value.trim();
                const rate = document.getElementById('edit-order-rate').value;
                const totalPrice = document.getElementById('edit-order-total').value;
                const notes = document.getElementById('edit-order-notes').value.trim();

                try {
                    const formData = new FormData();
                    formData.append('order_id', orderId);
                    formData.append('order_number', orderNumber);
                    formData.append('created_at', orderDate);
                    formData.append('rate_per_mail', rate);
                    formData.append('total_price', totalPrice);
                    formData.append('notes', notes);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=save_order', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Order Updated',
                            text: data.message || 'Order updated successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        this.closeEditOrderModal();
                        await this.loadOrders();
                        await this.loadKhataData();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Could not update order.' });
                    }
                } catch (err) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Connection Error', text: 'Error connecting to server.' });
                }
            },

            deleteOrder: async function(orderId) {
                const conf = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: 'Delete Order Record?',
                    text: `Are you sure you want to delete Order #${orderId}? Is order ki tamam mails wapis Available stock me move ho jayengi aur order ledger se hat jayega.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    confirmButtonColor: '#e11d48',
                    cancelButtonText: 'Cancel'
                });

                if (!conf.isConfirmed) return;

                try {
                    const formData = new FormData();
                    formData.append('order_id', orderId);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=delete_order', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Order Deleted',
                            text: data.message || `Order #${orderId} deleted and mails reverted to Stock.`,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        await this.loadOrders();
                        await this.loadKhataData();
                        await this.refreshStock();
                        await this.loadAdminAccounts();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Could not delete order.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Connection Error', text: 'Error connecting to server.' });
                }
            },

            cleanupDuplicateOrders: async function() {
                const conf = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: 'Clean Duplicate Orders?',
                    html: `
                        <div class="text-left text-xs font-mono space-y-2 text-slate-300">
                            <p>System database me check karega agar koi duplicate order mojood hai to unhe safely remove kar dega.</p>
                            <p class="text-cyan-400">Emails table aur active stock bilkul mehfooz rahenge aur bill normalize ho jayega.</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Clean Duplicates',
                    cancelButtonText: 'Cancel'
                });

                if (!conf.isConfirmed) return;

                try {
                    const fd = new FormData();
                    fd.append('csrf_token', this.csrfToken);
                    const res = await fetch('api.php?action=cleanup_duplicate_orders', {
                        method: 'POST',
                        body: fd
                    });
                    const data = await res.json();
                    if (data.csrf_token) this.csrfToken = data.csrf_token;

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Cleanup Complete',
                            text: data.message
                        });
                        await this.loadOrders();
                        await this.loadKhataData();
                    } else {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'error',
                            title: 'Cleanup Error',
                            text: data.message || 'Could not clean duplicates'
                        });
                    }
                } catch(e) {
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'error',
                        title: 'Error',
                        text: e.message || 'Server communication error'
                    });
                }
            },

            revertAllDownloadedToStock: async function() {
                const conf = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: '⚡ Revert All Downloaded Mails?',
                    html: `
                        <div class="text-left text-xs font-mono space-y-2 text-slate-300">
                            <p>Kya aap tamam downloaded mails ko wapis <b>Available</b> stock me shift karna chahte hain?</p>
                            <p class="text-amber-400">Jo orders delete ho chuke hain unki mails bhi automatically Available stock me shamil ho jayengi.</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Revert to Stock',
                    confirmButtonColor: '#10b981',
                    cancelButtonText: 'Cancel'
                });

                if (!conf.isConfirmed) return;

                try {
                    const formData = new FormData();
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=revert_all_downloaded', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Stock Updated',
                            text: data.message || `Tamam downloaded accounts wapis Available stock me shamil ho chuke hain!`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        await this.refreshStock();
                        await this.loadAdminAccounts();
                        await this.loadKhataData();
                        await this.loadOrders();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Operation failed.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Server communication error.' });
                }
            },

            changeOrderStatus: async function(orderId, newStatus, orderName, quantity) {
                const isReverting = newStatus === 'reverted';
                const confirmTitle = isReverting 
                    ? `Order #${orderId} ko Revert Karein?` 
                    : `Order #${orderId} ko Delivered Karein?`;
                const confirmHtml = isReverting
                    ? `<div class="text-left text-xs font-mono space-y-2 text-slate-300">
                         <p>Kya aap <b>${orderName}</b> ki ${quantity} mails ko wapis <b>Available</b> stock me shift karna chahte hain?</p>
                         <p class="text-emerald-400 font-semibold">✅ Mails Downloaded se foran hat kar Available stock me shamil ho jayengi.</p>
                         <p class="text-amber-400 font-semibold">✅ Is order ka bill Total Bill aur Khata se foran minus ho jayega.</p>
                       </div>`
                    : `<div class="text-left text-xs font-mono space-y-2 text-slate-300">
                         <p>Kya aap <b>${orderName}</b> ko wapis <b>Delivered (Sold)</b> mark karna chahte hain?</p>
                         <p class="text-cyan-400">Mails Downloaded status me chali jayengi aur Bill me shamil ho jayengi.</p>
                       </div>`;

                const conf = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: confirmTitle,
                    html: confirmHtml,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: isReverting ? 'Yes, Revert to Stock' : 'Yes, Mark Delivered',
                    confirmButtonColor: isReverting ? '#f59e0b' : '#10b981',
                    cancelButtonText: 'Cancel'
                });

                if (!conf.isConfirmed) {
                    this.renderAdminOrders();
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('csrf_token', this.csrfToken);
                    formData.append('order_id', orderId);
                    formData.append('status', newStatus);

                    const res = await fetch('api.php?action=update_order_status', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: isReverting ? 'Order Reverted!' : 'Order Delivered!',
                            text: data.message,
                            timer: 2200,
                            showConfirmButton: false
                        });
                        await this.refreshStock();
                        await this.loadAdminAccounts();
                        await this.loadKhataData();
                        await this.loadOrders();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Operation failed.' });
                        this.renderAdminOrders();
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Server communication error.' });
                    this.renderAdminOrders();
                }
            },

            syncStockAndOrders: async function() {
                const conf = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: '⚡ Sync Stock & Fix Downloaded Mails?',
                    html: `
                        <div class="text-left text-xs font-mono space-y-2 text-slate-300">
                            <p>Kya aap Stock aur Khata ko sync karna chahte hain?</p>
                            <p class="text-emerald-400">Jo mails kisi active delivered order me shamil nahi hain wo automatically <b>Available stock</b> me shift ho jayengi aur Downloaded count se nikal jayengi.</p>
                            <p class="text-cyan-400">Khata aur Total Bill sirf active delivered orders ke mutabiq theek ho jayega.</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Sync & Fix Now',
                    confirmButtonColor: '#06b6d4',
                    cancelButtonText: 'Cancel'
                });

                if (!conf.isConfirmed) return;

                try {
                    const formData = new FormData();
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=sync_stock_and_orders', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Stock & Khata Synced!',
                            text: data.message,
                            timer: 2500,
                            showConfirmButton: false
                        });
                        await this.refreshStock();
                        await this.loadAdminAccounts();
                        await this.loadKhataData();
                        await this.loadOrders();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Operation failed.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Server communication error.' });
                }
            },

            quickChangeOrderPrice: async function(orderId, currentRate, quantity) {
                const { value: formValues } = await Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: `Update Price for Order #${orderId}`,
                    html: `
                        <div class="text-left text-xs font-mono space-y-3">
                            <p class="text-slate-300">Order Quantity: <b class="text-cyan-300">${quantity} accounts</b></p>
                            <div>
                                <label class="block text-slate-400 mb-1">Rate Per Mail (PKR):</label>
                                <input id="swal-order-rate" type="number" step="0.5" value="${currentRate}" class="w-full p-2 bg-slate-900 border border-slate-700 rounded text-white font-mono text-sm" oninput="
                                    const r = parseFloat(this.value) || 0;
                                    document.getElementById('swal-calc-total').innerText = 'Rs. ' + Math.round(r * ${quantity}).toLocaleString();
                                ">
                            </div>
                            <div class="p-2.5 bg-slate-900/80 border border-slate-800 rounded flex items-center justify-between text-xs">
                                <span class="text-slate-400">Calculated Total Price:</span>
                                <span id="swal-calc-total" class="font-bold text-emerald-400 text-sm">Rs. ${Math.round(currentRate * quantity).toLocaleString()}</span>
                            </div>
                        </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Save Price',
                    confirmButtonColor: '#06b6d4',
                    cancelButtonText: 'Cancel',
                    preConfirm: () => {
                        const r = parseFloat(document.getElementById('swal-order-rate').value);
                        if (isNaN(r) || r < 0) {
                            Swal.showValidationMessage('Please enter a valid rate');
                            return false;
                        }
                        return { rate: r };
                    }
                });

                if (!formValues) return;

                try {
                    const formData = new FormData();
                    formData.append('order_id', orderId);
                    formData.append('rate_per_mail', formValues.rate);
                    formData.append('csrf_token', this.csrfToken);

                    const res = await fetch('api.php?action=quick_update_order_price', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await res.json();

                    if (data.success) {
                        Swal.fire({
                            customClass: { popup: 'cyber-swal' },
                            icon: 'success',
                            title: 'Price Updated',
                            text: data.message || 'Price updated successfully',
                            timer: 1400,
                            showConfirmButton: false
                        });
                        await this.loadOrders();
                        await this.loadKhataData();
                    } else {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Could not update price.' });
                    }
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Failed to update order price.' });
                }
            },

            // ==========================================
            // Single Mail Credential Finder
            // ==========================================
            handleMailSearch: async function(e, viewType) {
                if (e && e.preventDefault) e.preventDefault();
                const inputId = viewType === 'admin' ? 'admin-search-mail-input' : 'client-search-mail-input';
                const resultBoxId = viewType === 'admin' ? 'admin-search-result' : 'client-search-result';

                const input = document.getElementById(inputId);
                const resultBox = document.getElementById(resultBoxId);
                const query = input ? input.value.trim() : '';

                if (!query) return;

                resultBox.classList.remove('hidden');
                resultBox.innerHTML = '<div class="p-4 text-center text-slate-500 font-mono text-xs">Searching database...</div>';

                try {
                    const res = await fetch(`api.php?action=search_mail&query=${encodeURIComponent(query)}`);
                    const data = await res.json();

                    if (!data.success || !data.results || data.results.length === 0) {
                        resultBox.innerHTML = `
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-center font-mono text-xs text-rose-400">
                                No email credentials found matching "${query}".
                            </div>
                        `;
                        return;
                    }

                    let html = '<div class="space-y-3 font-mono text-xs">';
                    data.results.forEach(acc => {
                        let statusColor = 'emerald';
                        if (acc.status === 'downloaded') statusColor = 'cyan';
                        if (acc.status === 'replaced') statusColor = 'amber';

                        const orderInfo = acc.associated_order ? 
                            `<span class="text-cyan-300 font-bold">${acc.associated_order.order_number}</span> (${acc.associated_order.created_at})` : 
                            `<span class="text-slate-500">Not attached to specific order</span>`;

                        html += `
                            <div class="glass-panel p-4 rounded-xl border border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-white text-sm select-all">${acc.email}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase bg-${statusColor}-500/10 text-${statusColor}-300 border border-${statusColor}-500/20">${acc.status}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-slate-300 text-[11px] bg-slate-950/60 p-2.5 rounded-lg border border-slate-900">
                                    <div>Password: <span class="text-white font-semibold select-all">${acc.password}</span></div>
                                    <div>Recovery: <span class="text-slate-400 select-all">${acc.recovery_email || 'N/A'}</span></div>
                                    <div>Domain: <span class="text-cyan-300">${acc.domain}</span></div>
                                    <div>Order: ${orderInfo}</div>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <span class="text-[10px] text-slate-500">Ingested: ${acc.created_at}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('${acc.email},${acc.password},${acc.recovery_email || ''}')" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-cyan-300 text-[10px] border border-slate-700 transition-all">
                                        Copy Single Credential
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    resultBox.innerHTML = html;

                } catch (err) {
                    resultBox.innerHTML = '<div class="p-4 text-center text-rose-400 font-mono text-xs">Error performing search.</div>';
                }
            },

            // ==========================================
            // Account Statement Download
            // ==========================================
            downloadStatement: async function() {
                try {
                    const res = await fetch('api.php?action=download_statement');
                    const data = await res.json();
                    if (!data.success) {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: data.message || 'Failed to generate statement' });
                        return;
                    }

                    this.downloadBase64File(data.file_content, data.filename, 'text/csv');
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'success',
                        title: 'Statement Downloaded',
                        text: `Ledger statement ${data.filename} has been generated and downloaded.`,
                        timer: 1600,
                        showConfirmButton: false
                    });
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Server communication error downloading statement.' });
                }
            },

            // ==========================================
            // Sync / Refresh All Data
            // ==========================================
            refreshAllData: async function() {
                Swal.fire({
                    customClass: { popup: 'cyber-swal' },
                    title: 'Syncing Live Data...',
                    text: 'Updating financial ledger, orders, payments, and stock.',
                    timer: 800,
                    showConfirmButton: false
                });
                await Promise.all([
                    this.refreshStock(),
                    this.loadOrders(),
                    this.loadKhataData(),
                    this.loadPayments(),
                    this.loadReplacements(),
                    (this.user && this.user.role === 'admin') ? this.loadAdminData() : Promise.resolve()
                ]);
            },

            // ==========================================
            // Order Modal & Actions
            // ==========================================
            openOrderModal: function(orderId) {
                const order = this.allOrders.find(o => o.id == orderId);
                if (!order) return;

                this.activeModalOrder = order;

                const title = document.getElementById('modal-order-title');
                if (title) title.innerText = `${order.order_number || ('Order #' + order.id)} Credentials`;

                const sub = document.getElementById('modal-order-subtitle');
                if (sub) sub.innerText = `Client: ${order.client_username || 'Rana Asim'} • Extracted: ${order.created_at}`;

                const dom = document.getElementById('modal-order-domain');
                if (dom) dom.innerText = order.domain;

                const qty = document.getElementById('modal-order-qty');
                if (qty) qty.innerText = `${order.quantity} accounts`;

                const rate = document.getElementById('modal-order-rate');
                if (rate) rate.innerText = `Rs. ${order.rate_per_mail || 18}`;

                const total = document.getElementById('modal-order-total');
                if (total) total.innerText = `Rs. ${Number(order.total_price || (order.quantity * (order.rate_per_mail || 18))).toLocaleString()}`;

                const date = document.getElementById('modal-order-date');
                if (date) date.innerText = order.created_at;

                const searchInput = document.getElementById('modal-account-search');
                if (searchInput) searchInput.value = '';

                this.renderModalAccounts(order.accounts || []);
                document.getElementById('order-modal').classList.remove('hidden');
            },

            closeOrderModal: function() {
                this.activeModalOrder = null;
                document.getElementById('order-modal').classList.add('hidden');
            },

            filterModalAccounts: function() {
                if (!this.activeModalOrder) return;
                const q = document.getElementById('modal-account-search').value.trim().toLowerCase();
                const accs = (this.activeModalOrder.accounts || []).filter(a => {
                    if (!q) return true;
                    return (a.email && a.email.toLowerCase().includes(q)) ||
                           (a.recovery_email && a.recovery_email.toLowerCase().includes(q));
                });
                this.renderModalAccounts(accs);
            },

            renderModalAccounts: function(accs) {
                const tbody = document.getElementById('modal-accounts-tbody');
                const countBadge = document.getElementById('modal-accounts-count');
                if (countBadge) countBadge.innerText = `${accs.length} emails`;
                if (!tbody) return;
                tbody.innerHTML = '';

                if (accs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-slate-500 font-mono">No accounts to display</td></tr>';
                    return;
                }

                accs.forEach((a, i) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-900/60 transition-colors font-mono';
                    tr.innerHTML = `
                        <td class="p-2.5 text-slate-500">${i + 1}</td>
                        <td class="p-2.5 text-cyan-300 font-semibold select-all">${a.email}</td>
                        <td class="p-2.5 text-slate-300 select-all">${a.password}</td>
                        <td class="p-2.5 text-slate-400 select-all">${a.recovery_email || 'N/A'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            },

            copyModalOrderMails: function() {
                if (!this.activeModalOrder || !this.activeModalOrder.accounts) return;
                this.copyAccountsToClipboard(this.activeModalOrder.accounts);
            },

            downloadModalOrderFile: function() {
                if (!this.activeModalOrder) return;
                this.downloadOrderById(this.activeModalOrder.id);
            },

            downloadOrderById: async function(orderId) {
                try {
                    const res = await fetch(`api.php?action=download_order&order_id=${orderId}`);
                    const data = await res.json();
                    if (!data.success) {
                        Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Download Error', text: data.message || 'Failed to generate file' });
                        return;
                    }
                    this.downloadBase64File(data.file_content, data.filename, data.format === 'csv' ? 'text/csv' : 'text/plain');
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'success',
                        title: 'Downloaded!',
                        text: `Order #${orderId} (${data.filename}) re-downloaded successfully.`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } catch (e) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Error', text: 'Server communication error while downloading order' });
                }
            },

            copyOrderMails: function(orderId) {
                const order = this.allOrders.find(o => o.id == orderId);
                if (!order || !order.accounts) return;
                this.copyAccountsToClipboard(order.accounts);
            },

            copyAccountsToClipboard: function(accounts) {
                if (!accounts || accounts.length === 0) {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'info', title: 'Empty', text: 'No accounts in this order to copy.' });
                    return;
                }
                const lines = ["email,password,recovery email"];
                accounts.forEach(a => {
                    lines.push(`${a.email},${a.password},${a.recovery_email || ''}`);
                });
                const text = lines.join("\n");
                navigator.clipboard.writeText(text).then(() => {
                    Swal.fire({
                        customClass: { popup: 'cyber-swal' },
                        icon: 'success',
                        title: 'Copied to Clipboard!',
                        text: `${accounts.length} accounts copied in "email,password,recovery email" format.`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }).catch(() => {
                    Swal.fire({ customClass: { popup: 'cyber-swal' }, icon: 'error', title: 'Clipboard Error', text: 'Could not copy to clipboard.' });
                });
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            VaultApp.init();
        });
    </script>
</body>
</html>
