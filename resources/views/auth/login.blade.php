{{-- Deploy to: resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>
    <link rel="icon" type="image/png" href="/favicon.png"/>
    <title>Sign In — Nurtureville</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --c-bg:      #F5F4F0;
            --c-surface: #FFFFFF;
            --c-accent:  #1A56FF;
            --c-text-1:  #111111;
            --c-text-2:  #555555;
            --c-text-3:  #999999;
            --c-border:  #E8E6E1;
            --c-danger:  #BE123C;
            --f-sans:    'Outfit', sans-serif;
            --r-md:      12px;
        }

        html, body {
            height: 100%;
            font-family: var(--f-sans);
            background: var(--c-bg);
            color: var(--c-text-1);
            -webkit-font-smoothing: antialiased;
        }

        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        .login-card {
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: 20px;
            padding: 40px 36px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }

        /* School branding */
        .school-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .school-logo-wrap {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            background: var(--c-accent);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .school-logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .school-logo-fallback {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            line-height: 1;
        }

        .school-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--c-text-1);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .school-sub {
            font-size: 12px;
            color: var(--c-text-3);
            margin-top: 2px;
        }

        /* Form heading */
        .login-heading {
            font-size: 22px;
            font-weight: 700;
            color: var(--c-text-1);
            letter-spacing: -0.03em;
            margin-bottom: 6px;
        }

        .login-sub {
            font-size: 13px;
            color: var(--c-text-3);
            margin-bottom: 28px;
        }

        /* Form fields */
        .field { margin-bottom: 16px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--c-text-2);
            margin-bottom: 6px;
        }

        .field input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--c-border);
            border-radius: 10px;
            font-family: var(--f-sans);
            font-size: 14px;
            color: var(--c-text-1);
            background: var(--c-bg);
            outline: none;
            transition: border-color 150ms, box-shadow 150ms;
            -webkit-appearance: none;
        }

        .field input:focus {
            border-color: var(--c-accent);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(26,86,255,0.08);
        }

        .field input.has-error {
            border-color: var(--c-danger);
        }

        .field-error {
            font-size: 11px;
            color: var(--c-danger);
            margin-top: 5px;
        }

        /* Remember me */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--c-text-2);
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--c-accent);
            cursor: pointer;
        }

        .forgot-link {
            font-size: 12px;
            color: var(--c-accent);
            text-decoration: none;
            transition: opacity 150ms;
        }

        .forgot-link:hover { opacity: 0.75; }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--c-accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: var(--f-sans);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 150ms, transform 100ms;
            letter-spacing: -0.01em;
        }

        .btn-login:hover  { opacity: 0.92; }
        .btn-login:active { transform: scale(0.99); }

        /* Session status / flash */
        .status-msg {
            background: rgba(21,128,61,0.08);
            border: 1px solid rgba(21,128,61,0.2);
            color: #15803D;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 11px;
            color: var(--c-text-3);
        }

        .login-footer a {
            color: var(--c-text-3);
            text-decoration: none;
        }

        .login-footer a:hover { color: var(--c-text-2); }

        @media (max-width: 440px) {
            .login-card { padding: 28px 20px; border-radius: 16px; }
        }
    </style>
</head>
<body>

@php
    $schoolName = \App\Models\SchoolSetting::get('school_name', 'Nurtureville School');
    $schoolSub  = \App\Models\SchoolSetting::get('school_tagline', 'Parent & Staff Portal');
    $logoUrl    = \App\Models\SchoolSetting::logoUrl();
@endphp

<div class="login-wrap">
    <div class="login-card">

        {{-- School branding --}}
        <div class="school-brand">
            <div class="school-logo-wrap">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $schoolName }} logo">
                @else
                    <span class="school-logo-fallback">{{ strtoupper(substr($schoolName, 0, 1)) }}</span>
                @endif
            </div>
            <div>
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-sub">{{ $schoolSub }}</div>
            </div>
        </div>

        <div class="login-heading">Welcome back</div>
        <div class="login-sub">Sign in to your account to continue</div>

        {{-- Session status (e.g. password reset confirmation) --}}
        @if(session('status'))
            <div class="status-msg">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">Email address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="{{ $errors->has('email') ? 'has-error' : '' }}"
                    placeholder="you@example.com">
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="{{ $errors->has('password') ? 'has-error' : '' }}"
                    placeholder="••••••••">
                @error('password')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember" id="remember_me">
                    Remember me
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="login-footer">
            <a href="{{ route('enrol') }}">Enrol a new student</a>
            &nbsp;·&nbsp;
            <a href="{{ route('staff.register') }}">Staff registration</a>
        </div>
    </div>
</div>

</body>
</html>
