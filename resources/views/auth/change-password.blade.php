<x-guest-layout>
    <style>
        .change-pw-wrap { text-align:center; margin-bottom:24px; }
        .change-pw-icon { width:48px; height:48px; border-radius:50%; background:#FEF3C7; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; }
        .change-pw-title { font-size:18px; font-weight:700; color:#111; }
        .change-pw-sub   { font-size:13px; color:#777; margin-top:4px; line-height:1.5; }
        .info-banner { background:#EFF6FF; border:1px solid #BFDBFE; border-radius:8px; padding:10px 14px; margin-bottom:20px; font-size:13px; color:#1D4ED8; }
    </style>

    <div class="change-pw-wrap">
        <div class="change-pw-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#B45309" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <div class="change-pw-title">Set Your Password</div>
        <div class="change-pw-sub">
            Your account was created with a temporary password.<br>
            Please set a new password to continue.
        </div>
    </div>

    @if(session('info'))
        <div class="info-banner">{{ session('info') }}</div>
    @endif

    <form method="POST" action="{{ route('password.change.update') }}">
        @csrf

        <div>
            <x-input-label for="password" value="New Password" />
            <x-text-input id="password" class="block mt-1 w-full"
                type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <p style="font-size:11px;color:#999;margin-top:4px;">
                Minimum 8 characters, must include letters and numbers.
            </p>
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirm New Password" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                Set Password & Continue
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
