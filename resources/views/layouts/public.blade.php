<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>
    <link rel="icon" type="image/png" href="/favicon.png"/>
    <title>{{ $title ?? 'Student Enrolment' }} — Nurtureville</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --c-accent: #1A56FF;
            --c-bg: #F5F4F0;
            --c-surface: #FFFFFF;
            --c-text-1: #111111;
            --c-text-2: #555555;
            --c-text-3: #999999;
            --c-border: #E8E6E1;
            --c-error: #BE123C;
            --c-success: #15803D;
            --f-sans: 'Outfit', sans-serif;
            --r-md: 12px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--f-sans);
            background: var(--c-bg);
            color: var(--c-text-1);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
        }

        .pub-header {
            background: var(--c-surface);
            border-bottom: 1px solid var(--c-border);
            padding: 16px 20px;
            display: flex; align-items: center; gap: 12px;
        }

        .pub-logo-mark {
            width: 36px; height: 36px;
            background: var(--c-accent); border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700; color: #fff;
        }

        .pub-logo-name {
            font-size: 16px; font-weight: 700;
            color: var(--c-text-1); letter-spacing: -0.02em;
        }

        .pub-logo-sub {
            font-size: 11px; color: var(--c-text-3);
        }

        .pub-main {
            max-width: 680px;
            margin: 0 auto;
            padding: 24px 16px 60px;
        }

        @media (min-width: 640px) {
            .pub-main { padding: 40px 24px 80px; }
        }
    </style>
</head>
<body>

<header class="pub-header">
    <div class="pub-logo-mark">N</div>
    <div>
        <div class="pub-logo-name">Nurtureville</div>
        <div class="pub-logo-sub">School Management Portal</div>
    </div>
</header>

<main class="pub-main">
    {{ $slot }}
</main>

@livewireScripts
</body>
</html>
