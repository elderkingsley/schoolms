<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — Nurtureville</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* ─────────────────────────────────────────
           DESIGN TOKENS
        ───────────────────────────────────────── */
        :root {
            --sidebar-w: 256px;
            --topbar-h: 56px;
            --bottom-nav-h: 64px;

            --c-bg:        #F5F4F0;
            --c-surface:   #FFFFFF;
            --c-sidebar:   #0E0E0E;
            --c-sidebar-border: rgba(255,255,255,0.07);
            --c-sidebar-hover:  rgba(255,255,255,0.06);
            --c-accent:    #1A56FF;
            --c-accent-bg: rgba(26,86,255,0.08);
            --c-text-1:    #111111;
            --c-text-2:    #555555;
            --c-text-3:    #999999;
            --c-border:    #E8E6E1;
            --c-success:   #15803D;
            --c-warning:   #B45309;
            --c-danger:    #BE123C;

            --f-sans: 'Outfit', sans-serif;
            --f-mono: 'JetBrains Mono', monospace;

            --r-sm: 8px;
            --r-md: 12px;
            --r-lg: 16px;

            --shadow-card: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-float: 0 8px 32px rgba(0,0,0,0.12);

            --ease: cubic-bezier(0.4, 0, 0.2, 1);
            --dur: 200ms;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { height: 100%; }

        body {
            font-family: var(--f-sans);
            background: var(--c-bg);
            color: var(--c-text-1);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            height: 100%;
            overflow: hidden;
        }

        /* ─────────────────────────────────────────
           SHELL
        ───────────────────────────────────────── */
        .shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ─────────────────────────────────────────
           SIDEBAR — desktop only, hidden on mobile
        ───────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--c-sidebar);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: relative;
            z-index: 30;
            /* hidden below lg */
            transform: translateX(-100%);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            transition: transform var(--dur) var(--ease);
        }

        /* drawer open state (toggled via JS) */
        .sidebar.open {
            transform: translateX(0);
            box-shadow: var(--shadow-float);
        }

        /* desktop: always visible */
        @media (min-width: 1024px) {
            .sidebar {
                position: relative;
                transform: translateX(0) !important;
                box-shadow: none;
            }
        }

        /* overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 20;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.visible { display: block; }

        @media (min-width: 1024px) {
            .sidebar-overlay { display: none !important; }
        }

        /* Logo */
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 20px 18px 18px;
            border-bottom: 1px solid var(--c-sidebar-border);
        }

        .logo-mark {
            width: 34px; height: 34px;
            background: var(--c-accent);
            border-radius: var(--r-sm);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 700; color: #fff;
            flex-shrink: 0;
            letter-spacing: -0.5px;
        }

        .logo-text-wrap { display: flex; flex-direction: column; }

        .logo-name {
            font-size: 14px; font-weight: 600;
            color: #fff; letter-spacing: -0.02em; line-height: 1.2;
        }

        .logo-sub {
            font-size: 9.5px; color: rgba(255,255,255,0.28);
            text-transform: uppercase; letter-spacing: 0.08em; margin-top: 1px;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1; overflow-y: auto; padding: 10px 10px;
            scrollbar-width: none;
        }
        .sidebar-nav::-webkit-scrollbar { display: none; }

        .nav-group { margin-bottom: 2px; }

        .nav-label {
            font-size: 9px; font-weight: 600;
            color: rgba(255,255,255,0.22);
            text-transform: uppercase; letter-spacing: 0.1em;
            padding: 14px 8px 5px;
        }

        .nav-link {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 10px;
            border-radius: var(--r-sm);
            font-size: 13px; font-weight: 400;
            color: rgba(255,255,255,0.55);
            text-decoration: none;
            transition: background var(--dur), color var(--dur);
            margin-bottom: 1px;
        }

        .nav-link:hover {
            background: var(--c-sidebar-hover);
            color: rgba(255,255,255,0.9);
        }

        .nav-link.active {
            background: var(--c-accent);
            color: #fff; font-weight: 500;
        }

        .nav-link svg { flex-shrink: 0; opacity: 0.7; }
        .nav-link:hover svg, .nav-link.active svg { opacity: 1; }

        /* Footer */
        .sidebar-footer {
            padding: 10px;
            border-top: 1px solid var(--c-sidebar-border);
        }

        .sidebar-user {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 10px; border-radius: var(--r-sm);
            cursor: pointer;
            transition: background var(--dur);
        }

        .sidebar-user:hover { background: var(--c-sidebar-hover); }

        .user-av {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--c-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 600; color: #fff;
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }

        .user-name {
            font-size: 12px; font-weight: 500; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .user-role {
            font-size: 10px; color: rgba(255,255,255,0.28);
            text-transform: capitalize;
        }

        .logout-btn {
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,0.2); padding: 4px;
            display: flex; transition: color var(--dur);
        }
        .logout-btn:hover { color: rgba(255,255,255,0.7); }

        /* ─────────────────────────────────────────
           MAIN AREA
        ───────────────────────────────────────── */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            /* on mobile, take full width */
            min-width: 0;
        }

        /* ─────────────────────────────────────────
           TOPBAR
        ───────────────────────────────────────── */
        .topbar {
            height: var(--topbar-h);
            background: var(--c-surface);
            border-bottom: 1px solid var(--c-border);
            display: flex; align-items: center;
            padding: 0 16px; gap: 12px;
            flex-shrink: 0;
            position: sticky; top: 0; z-index: 10;
        }

        @media (min-width: 1024px) {
            .topbar { padding: 0 24px; }
        }

        .topbar-menu-btn {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: var(--r-sm);
            border: 1px solid var(--c-border); background: none;
            cursor: pointer; color: var(--c-text-2);
            transition: background var(--dur), color var(--dur);
            flex-shrink: 0;
        }

        .topbar-menu-btn:hover { background: var(--c-bg); color: var(--c-text-1); }

        /* hide hamburger on desktop */
        @media (min-width: 1024px) {
            .topbar-menu-btn { display: none; }
        }

        .topbar-title {
            font-size: 15px; font-weight: 600;
            color: var(--c-text-1); letter-spacing: -0.02em;
            flex: 1;
        }

        .topbar-right {
            display: flex; align-items: center; gap: 8px;
        }

        .topbar-session-badge {
            display: none;
            align-items: center; gap: 6px;
            background: var(--c-accent-bg);
            color: var(--c-accent);
            font-size: 11px; font-weight: 500;
            padding: 5px 10px; border-radius: 20px;
        }

        @media (min-width: 640px) {
            .topbar-session-badge { display: flex; }
        }

        .badge-dot {
            width: 6px; height: 6px;
            background: var(--c-accent); border-radius: 50%;
        }

        .topbar-date {
            font-size: 11px; color: var(--c-text-3);
            font-family: var(--f-mono);
            display: none;
        }

        @media (min-width: 768px) {
            .topbar-date { display: block; }
        }

        /* ─────────────────────────────────────────
           PAGE CONTENT
        ───────────────────────────────────────── */
        .page-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px 16px;
            /* on mobile add bottom padding for bottom nav */
            padding-bottom: calc(var(--bottom-nav-h) + 20px);
        }

        @media (min-width: 640px) {
            .page-content { padding: 24px 20px; padding-bottom: 24px; }
        }

        @media (min-width: 1024px) {
            .page-content { padding: 28px 28px; padding-bottom: 28px; }
        }

        /* ─────────────────────────────────────────
           BOTTOM NAV — mobile only
        ───────────────────────────────────────── */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0;
            height: var(--bottom-nav-h);
            background: var(--c-sidebar);
            display: flex; align-items: center;
            border-top: 1px solid var(--c-sidebar-border);
            z-index: 15;
            padding: 0 4px;
        }

        @media (min-width: 1024px) {
            .bottom-nav { display: none; }
        }

        .bottom-nav-item {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 3px;
            padding: 8px 4px;
            color: rgba(255,255,255,0.35);
            text-decoration: none; font-size: 9.5px; font-weight: 500;
            border-radius: var(--r-sm);
            transition: color var(--dur), background var(--dur);
            letter-spacing: 0.02em;
        }

        .bottom-nav-item:hover { color: rgba(255,255,255,0.7); }

        .bottom-nav-item.active {
            color: #fff;
            background: rgba(26,86,255,0.25);
        }

        .bottom-nav-item svg { flex-shrink: 0; }

    </style>
</head>
<body>

<div class="shell">

    {{-- ── Sidebar overlay (mobile) ── --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ── Sidebar ── --}}
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <div class="logo-mark">N</div>
            <div class="logo-text-wrap">
                <span class="logo-name">Nurtureville</span>
                <span class="logo-sub">School Management</span>
            </div>
        </div>

        @php $pendingCount = \App\Models\Student::where('status', 'pending')->count(); @endphp

        <nav class="sidebar-nav">

            <div class="nav-group">
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="1" y="1" width="6" height="6" rx="1.5"/>
                        <rect x="9" y="1" width="6" height="6" rx="1.5"/>
                        <rect x="1" y="9" width="6" height="6" rx="1.5"/>
                        <rect x="9" y="9" width="6" height="6" rx="1.5"/>
                    </svg>
                    Dashboard
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-label">Academics</div>

                <a href="{{ route('admin.students') }}"
                   class="nav-link {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="8" cy="5" r="3"/>
                        <path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5"/>
                    </svg>
                    Students
                </a>

                <a href="{{ route('admin.enrolment.queue') }}"
                   class="nav-link {{ request()->routeIs('admin.enrolment*') ? 'active' : '' }}"
                   style="justify-content: space-between">
                    <span style="display:flex;align-items:center;gap:9px">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M14 2H2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                            <path d="M5 7h6M5 10h4"/>
                        </svg>
                        Enrolment
                    </span>
                    @if($pendingCount > 0)
                        <span style="background:rgba(255,255,255,0.15);color:#fff;font-size:10px;font-weight:600;padding:1px 7px;border-radius:20px;min-width:20px;text-align:center">
                            {{ $pendingCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.results.entry') }}" class="nav-link {{ request()->routeIs('admin.results*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M12 2H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                        <path d="M5 6h6M5 9h6M5 12h3"/>
                    </svg>
                    Results
                </a>

                <a href="#" class="nav-link {{ request()->routeIs('admin.notes*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M4 1h8l3 3v11H1V1zM12 1v3h3"/>
                        <path d="M4 7h8M4 10h5"/>
                    </svg>
                    Lesson Notes
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-label">Finance</div>
                <a href="{{ route('admin.fees.structure') }}" class="nav-link {{ request()->routeIs('admin.fees.structure') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="1" y="3" width="14" height="10" rx="1.5"/>
                        <path d="M1 6h14M5 10h2"/>
                    </svg>
                    Fee Structure
                </a>
                <a href="{{ route('admin.fees.items') }}" class="nav-link {{ request()->routeIs('admin.fees.items') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M2 4h12M2 8h8M2 12h10"/>
                    </svg>
                    Fee Items
                </a>
                <a href="{{ route('admin.fees.invoices') }}" class="nav-link {{ request()->routeIs('admin.fees.invoices*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="2" y="1" width="12" height="14" rx="1.5"/>
                        <path d="M5 5h6M5 8h6M5 11h3"/>
                    </svg>
                    Invoices
                </a>
                <a href="#" class="nav-link {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M8 1v14M5 4h4.5a2.5 2.5 0 0 1 0 5H5M5 9h5a2.5 2.5 0 0 1 0 5H5"/>
                    </svg>
                    Payments
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-label">Communication</div>
                <a href="{{ route('admin.messages') }}" class="nav-link {{ request()->routeIs('admin.messages*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M14 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3l3 3 3-3h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                    </svg>
                    Messages
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-label">Settings</div>
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="6" cy="5" r="2.5"/>
                        <path d="M1 14c0-2.761 2.239-4.5 5-4.5s5 1.739 5 4.5"/>
                        <path d="M11 7.5c.828 0 1.5-.672 1.5-1.5S11.828 4.5 11 4.5M15 14c0-2-1.343-3.5-4-3.5"/>
                    </svg>
                    Users
                </a>
                <a href="{{ route('admin.classes') }}" class="nav-link {{ request()->routeIs('admin.classes*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="1" y="4" width="14" height="9" rx="1.5"/>
                        <path d="M5 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1"/>
                    </svg>
                    Classes & Subjects
                </a>
                <a href="#" class="nav-link {{ request()->routeIs('admin.sessions*') ? 'active' : '' }}">
                    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="1" y="2" width="14" height="13" rx="1.5"/>
                        <path d="M1 6h14M5 1v2M11 1v2"/>
                    </svg>
                    Sessions & Terms
                </a>
            </div>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-av">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ str_replace('_', ' ', auth()->user()->user_type) }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Log out">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M6 2H3a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>
                            <path d="M10 11l4-4-4-4M14 8H6"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ── Main ── --}}
    <div class="main">

        {{-- Topbar --}}
        <header class="topbar">
            <button class="topbar-menu-btn" onclick="openSidebar()" aria-label="Open menu">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M2 4h12M2 8h12M2 12h12"/>
                </svg>
            </button>

            <span class="topbar-title">{{ $title ?? 'Dashboard' }}</span>

            <div class="topbar-right">
                <span class="topbar-date" id="topbarDate"></span>
                <div class="topbar-session-badge">
                    <span class="badge-dot"></span>
                    {{ \App\Models\AcademicSession::where('is_active', true)->value('name') ?? 'No Active Session' }}
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="page-content">
            {{ $slot }}
        </main>

    </div>

</div>

{{-- ── Bottom nav (mobile) ── --}}
<nav class="bottom-nav">
    <a href="{{ route('admin.dashboard') }}"
       class="bottom-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
            <rect x="1" y="1" width="6" height="6" rx="1.5"/>
            <rect x="9" y="1" width="6" height="6" rx="1.5"/>
            <rect x="1" y="9" width="6" height="6" rx="1.5"/>
            <rect x="9" y="9" width="6" height="6" rx="1.5"/>
        </svg>
        Home
    </a>
    <a href="#" class="bottom-nav-item {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
            <circle cx="8" cy="5" r="3"/>
            <path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5"/>
        </svg>
        Students
    </a>
    <a href="{{ route('admin.results.entry') }}" class="bottom-nav-item {{ request()->routeIs('admin.results*') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M12 2H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
            <path d="M5 6h6M5 9h6M5 12h3"/>
        </svg>
        Results
    </a>
    <a href="#" class="bottom-nav-item {{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
            <rect x="1" y="3" width="14" height="10" rx="1.5"/>
            <path d="M1 6h14M5 10h2"/>
        </svg>
        Fees
    </a>
    <a href="#" class="bottom-nav-item {{ request()->routeIs('admin.messages*') ? 'active' : '' }}">
        <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
            <path d="M14 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3l3 3 3-3h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
        </svg>
        Messages
    </a>
</nav>

@livewireScripts

<script>
    // Topbar date
    function setDate() {
        const el = document.getElementById('topbarDate');
        if (!el) return;
        el.textContent = new Date().toLocaleDateString('en-GB', {
            weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
        });
    }
    setDate();

    // Sidebar drawer (mobile)
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('visible');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('visible');
        document.body.style.overflow = '';
    }

    // Close sidebar on nav link click (mobile)
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) closeSidebar();
        });
    });
</script>

</body>
</html>
