<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Teacher' }} — Nurtureville</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --topbar-h: 56px; --bottom-nav-h: 64px;
            --c-bg:#F5F4F0; --c-surface:#FFFFFF; --c-accent:#1A56FF;
            --c-accent-bg:rgba(26,86,255,0.08); --c-text-1:#111111;
            --c-text-2:#555555; --c-text-3:#999999; --c-border:#E8E6E1;
            --c-success:#15803D; --c-warning:#B45309; --c-danger:#BE123C;
            --f-sans:'Outfit',sans-serif; --f-mono:'JetBrains Mono',monospace;
            --r-sm:8px; --r-md:12px; --r-lg:16px;
            --shadow-card:0 1px 3px rgba(0,0,0,0.05);
            --dur:200ms; --ease:cubic-bezier(0.4,0,0.2,1);
        }
        *,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
        html,body { height:100%; }
        body {
            font-family:var(--f-sans); background:var(--c-bg); color:var(--c-text-1);
            font-size:14px; line-height:1.5; -webkit-font-smoothing:antialiased;
            padding-top:var(--topbar-h); padding-bottom:var(--bottom-nav-h);
            min-height:100%; overflow-y:auto;
        }
        /* Top bar */
        .t-topbar {
            position:fixed; top:0; left:0; right:0; z-index:40;
            height:var(--topbar-h); background:#0E0E0E;
            display:flex; align-items:center; padding:0 16px; justify-content:space-between;
        }
        .t-brand { display:flex; align-items:center; gap:10px; }
        .t-logo { width:32px; height:32px; border-radius:8px; background:#15803D; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:#fff; }
        .t-name { font-size:15px; font-weight:600; color:#fff; letter-spacing:-0.02em; }
        .t-sub  { font-size:11px; color:rgba(255,255,255,0.4); }
        .t-user { display:flex; align-items:center; gap:10px; }
        .t-av { width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; color:#fff; }
        .t-username { font-size:13px; color:rgba(255,255,255,0.7); display:none; }
        @media(min-width:480px) { .t-username { display:block; } }
        .t-logout { font-size:12px; color:rgba(255,255,255,0.4); background:none; border:none; cursor:pointer; font-family:var(--f-sans); padding:4px 8px; border-radius:6px; transition:color 150ms; }
        .t-logout:hover { color:rgba(255,255,255,0.8); }
        /* Main */
        .t-main { max-width:900px; margin:0 auto; padding:20px 16px; }
        /* Bottom nav */
        .t-bottom-nav {
            position:fixed; bottom:0; left:0; right:0; z-index:40;
            height:var(--bottom-nav-h); background:#fff;
            border-top:1px solid var(--c-border);
            display:flex; align-items:stretch;
        }
        .t-nav-item {
            flex:1; display:flex; flex-direction:column;
            align-items:center; justify-content:center; gap:3px;
            color:var(--c-text-3); text-decoration:none;
            font-size:10px; font-weight:500;
            transition:color var(--dur);
        }
        .t-nav-item.active { color:#15803D; }
        .t-nav-item.active svg { stroke:#15803D; }
        .t-nav-item svg { stroke:var(--c-text-3); transition:stroke var(--dur); }
        .t-nav-item:hover { color:var(--c-text-1); }
    </style>
</head>
<body>
<header class="t-topbar">
    <div class="t-brand">
        <div class="t-logo">T</div>
        <div>
            <div class="t-name">Nurtureville</div>
            <div class="t-sub">Teacher Portal</div>
        </div>
    </div>
    <div class="t-user">
        <span class="t-username">{{ auth()->user()->name }}</span>
        <div class="t-av">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="t-logout">Log out</button>
        </form>
    </div>
</header>

<main class="t-main">
    {{ $slot }}
</main>

<nav class="t-bottom-nav">
    <a href="{{ route('teacher.dashboard') }}"
       class="t-nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Home
    </a>
    <a href="{{ route('teacher.results') }}"
       class="t-nav-item {{ request()->routeIs('teacher.results') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        Results
    </a>
</nav>

@livewireScripts
</body>
</html>
