<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Portal' }} — Nurtureville</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --topbar-h: 56px;
            --bottom-nav-h: 64px;

            --c-bg:        #F5F4F0;
            --c-surface:   #FFFFFF;
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
            --r-sm: 8px; --r-md: 12px; --r-lg: 16px;
            --shadow-card: 0 1px 3px rgba(0,0,0,0.05);
            --dur: 200ms;
            --ease: cubic-bezier(0.4,0,0.2,1);
        }

        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        html, body { height:100%; }
        body {
            font-family: var(--f-sans);
            background: var(--c-bg);
            color: var(--c-text-1);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            /* Reserve space for fixed top bar and bottom nav */
            padding-top: var(--topbar-h);
            padding-bottom: var(--bottom-nav-h);
            min-height: 100%;
            overflow-y: auto;
        }

        /* ── Top bar ── */
        .p-topbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 40;
            height: var(--topbar-h);
            background: #0E0E0E;
            display: flex; align-items: center;
            padding: 0 16px;
            justify-content: space-between;
        }
        .p-topbar-brand { display: flex; align-items: center; gap: 10px; }
        .p-topbar-logo {
            width: 32px; height: 32px; border-radius: 8px;
            background: var(--c-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff;
        }
        .p-topbar-name { font-size: 15px; font-weight: 600; color: #fff; letter-spacing: -0.02em; }
        .p-topbar-sub  { font-size: 11px; color: rgba(255,255,255,0.4); }

        .p-topbar-user { display: flex; align-items: center; gap: 10px; }
        .p-topbar-av {
            width: 32px; height: 32px; border-radius: 50%;
            background: rgba(255,255,255,0.12);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 600; color: #fff;
        }
        .p-topbar-username { font-size: 13px; color: rgba(255,255,255,0.7); display: none; }
        @media(min-width: 480px) { .p-topbar-username { display: block; } }

        .p-topbar-logout {
            font-size: 12px; color: rgba(255,255,255,0.4);
            background: none; border: none; cursor: pointer;
            font-family: var(--f-sans); padding: 4px 8px;
            border-radius: 6px; transition: color 150ms;
        }
        .p-topbar-logout:hover { color: rgba(255,255,255,0.8); }

        /* ── Main content area ── */
        .p-main {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 16px;
        }

        /* ── Bottom navigation ── */
        .p-bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 40;
            height: var(--bottom-nav-h);
            background: #fff;
            border-top: 1px solid var(--c-border);
            display: flex; align-items: stretch;
            will-change: transform;
        }
        .p-nav-item {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 3px;
            color: var(--c-text-3); text-decoration: none;
            font-size: 10px; font-weight: 500;
            transition: color var(--dur);
            position: relative;
        }
        .p-nav-item.active { color: var(--c-accent); }
        .p-nav-item.active svg { stroke: var(--c-accent); }
        .p-nav-item svg { stroke: var(--c-text-3); transition: stroke var(--dur); }
        .p-nav-item:hover { color: var(--c-text-1); }

        /* Unread badge on messages nav */
        .p-nav-badge {
            position: absolute; top: 8px; right: calc(50% - 14px);
            background: var(--c-danger); color: #fff;
            font-size: 9px; font-weight: 700;
            min-width: 16px; height: 16px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 4px;
        }
    </style>
</head>
<body>

{{-- ── Top bar ── --}}
<header class="p-topbar">
    <div class="p-topbar-brand">
        <div class="p-topbar-logo">N</div>
        <div>
            <div class="p-topbar-name">Nurtureville</div>
            <div class="p-topbar-sub">Parent Portal</div>
        </div>
    </div>
    <div class="p-topbar-user">
        <span class="p-topbar-username">{{ auth()->user()->name }}</span>
        <div class="p-topbar-av">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <a href="{{ route('account.password') }}"
       style="font-size:12px;color:var(--c-text-3);text-decoration:none;padding:6px 10px;border-radius:6px;transition:background 150ms;"
       onmouseover="this.style.background='var(--c-bg)'" onmouseout="this.style.background=''">
        🔒 Password
    </a>
    <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="p-topbar-logout">Log out</button>
        </form>
    </div>
</header>

{{-- ── Page content ── --}}
<main class="p-main">
    {{ $slot }}
</main>

{{-- ── Bottom navigation ── --}}
@php
    $parentProfile = auth()->user()->parentProfile;
    $unreadCount = $parentProfile
        ? \App\Models\MessageRecipient::where('parent_id', $parentProfile->id)
            ->whereNull('read_at')->count()
        : 0;
@endphp

<nav class="p-bottom-nav">
    <a href="{{ route('parent.dashboard') }}"
       class="p-nav-item {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Home
    </a>

    <a href="{{ route('parent.children') }}"
       class="p-nav-item {{ request()->routeIs('parent.children') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.87"/>
        </svg>
        Children
    </a>

    <a href="{{ route('parent.fees') }}"
       class="p-nav-item {{ request()->routeIs('parent.fees*') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="2" y="5" width="20" height="14" rx="2"/>
            <path d="M2 10h20"/>
        </svg>
        Fees
    </a>

    <a href="{{ route('parent.results') }}"
       class="p-nav-item {{ request()->routeIs('parent.results') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
        </svg>
        Results
    </a>

    <a href="{{ route('parent.messages') }}"
       class="p-nav-item {{ request()->routeIs('parent.messages*') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        @if($unreadCount > 0)
            <span class="p-nav-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
        Messages
    </a>

    @if(in_array(auth()->user()->user_type, ['teacher', 'teaching_assistant']))
    <a href="{{ route('teacher.dashboard') }}"
       class="p-nav-item">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Teacher
    </a>
    @endif
</nav>


<script>
(function () {
    // Auto-hide bottom nav on activity, show after 5 seconds of inactivity.
    // Only applies on mobile (screens narrower than 768px) — on desktop the
    // save bar and nav don't overlap so hiding isn't needed.
    var nav     = document.querySelector('.p-bottom-nav');
    var timer   = null;
    var DELAY   = 5000; // ms before nav reappears

    if (! nav) return;

    function hide() {
        nav.style.transform  = 'translateY(100%)';
        nav.style.transition = 'transform 250ms cubic-bezier(0.4,0,0.2,1)';
    }

    function show() {
        nav.style.transform  = 'translateY(0)';
        nav.style.transition = 'transform 250ms cubic-bezier(0.4,0,0.2,1)';
    }

    function onActivity() {
        if (window.innerWidth >= 768) return; // desktop — leave nav visible
        hide();
        clearTimeout(timer);
        timer = setTimeout(show, DELAY);
    }

    // Listen for all meaningful interaction events
    ['touchstart', 'touchmove', 'mousedown', 'scroll', 'keydown', 'focus'].forEach(function (evt) {
        document.addEventListener(evt, onActivity, { passive: true });
    });

    // Also hide when a Livewire request starts (e.g. typing in a score input)
    document.addEventListener('livewire:request', onActivity);
})();
</script>

@livewireScripts
</body>
</html>
