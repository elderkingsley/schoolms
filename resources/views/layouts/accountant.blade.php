<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>
    <link rel="icon" type="image/png" href="/favicon.png"/>
    <title>{{ $title ?? 'Finance' }} — Nurtureville</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --topbar-h:56px; --bottom-nav-h:64px;
            --c-bg:#F5F4F0; --c-surface:#FFFFFF; --c-accent:#15803D;
            --c-accent-bg:rgba(21,128,61,0.08); --c-text-1:#111111;
            --c-text-2:#555555; --c-text-3:#999999; --c-border:#E8E6E1;
            --c-success:#15803D; --c-warning:#B45309; --c-danger:#BE123C;
            --f-sans:'Outfit',sans-serif; --f-mono:'JetBrains Mono',monospace;
            --r-sm:8px; --r-md:12px; --r-lg:16px;
            --shadow-card:0 1px 3px rgba(0,0,0,0.05);
        }
        *,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
        html,body { height:100%; }
        body { font-family:var(--f-sans); background:var(--c-bg); color:var(--c-text-1); font-size:14px; line-height:1.5; -webkit-font-smoothing:antialiased; padding-top:var(--topbar-h); padding-bottom:var(--bottom-nav-h); min-height:100%; overflow-y:auto; }
        .a-topbar { position:fixed; top:0; left:0; right:0; z-index:40; height:var(--topbar-h); background:#0E0E0E; display:flex; align-items:center; padding:0 16px; justify-content:space-between; }
        .a-brand { display:flex; align-items:center; gap:10px; }
        .a-logo { width:32px; height:32px; border-radius:8px; background:#15803D; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:#fff; }
        .a-name { font-size:15px; font-weight:600; color:#fff; letter-spacing:-0.02em; }
        .a-sub  { font-size:11px; color:rgba(255,255,255,0.4); }
        .a-user { display:flex; align-items:center; gap:10px; }
        .a-av { width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; color:#fff; }
        .a-username { font-size:13px; color:rgba(255,255,255,0.7); display:none; }
        @media(min-width:480px) { .a-username { display:block; } }
        .a-logout { font-size:12px; color:rgba(255,255,255,0.4); background:none; border:none; cursor:pointer; font-family:var(--f-sans); padding:4px 8px; border-radius:6px; }
        .a-logout:hover { color:rgba(255,255,255,0.8); }
        .a-main { max-width:900px; margin:0 auto; padding:20px 16px; }
        .a-bottom-nav { position:fixed; bottom:0; left:0; right:0; z-index:40; height:var(--bottom-nav-h); background:#fff; border-top:1px solid var(--c-border); display:flex; align-items:stretch; }
        .a-nav-item { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px; color:var(--c-text-3); text-decoration:none; font-size:10px; font-weight:500; transition:color 200ms; }
        .a-nav-item.active { color:#15803D; }
        .a-nav-item.active svg { stroke:#15803D; }
        .a-nav-item svg { stroke:var(--c-text-3); transition:stroke 200ms; }
    </style>
</head>
<body>
<header class="a-topbar">
    <div class="a-brand">
        <div class="a-logo">₦</div>
        <div>
            <div class="a-name">Nurtureville</div>
            <div class="a-sub">Finance Portal</div>
        </div>
    </div>
    <div class="a-user">
        <span class="a-username">{{ auth()->user()->name }}</span>
        <div class="a-av">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="a-logout">Log out</button>
        </form>
    </div>
</header>

<main class="a-main">
    {{ $slot }}
</main>

<nav class="a-bottom-nav">
    <a href="{{ route('accountant.dashboard') }}"
       class="a-nav-item {{ request()->routeIs('accountant.dashboard') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Dashboard
    </a>
    <a href="{{ route('accountant.invoices') }}"
       class="a-nav-item {{ request()->routeIs('accountant.invoices*') ? 'active' : '' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>
        </svg>
        Invoices
    </a>
</nav>

@livewireScripts
</body>
</html>
