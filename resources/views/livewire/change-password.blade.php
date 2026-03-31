<div>
<style>
.pw-wrap { max-width:480px; margin:0 auto; padding:16px; }
.pw-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.pw-head { padding:20px 24px; border-bottom:1px solid var(--c-border); }
.pw-title { font-size:16px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; }
.pw-sub { font-size:13px; color:var(--c-text-3); margin-top:4px; }
.pw-body { padding:24px; }
.form-field { margin-bottom:18px; }
.form-field label { display:block; font-size:12px; font-weight:600; color:var(--c-text-2); margin-bottom:6px; }
.form-field input { width:100%; padding:11px 14px; border:1px solid var(--c-border); border-radius:9px; font-family:var(--f-sans); font-size:14px; color:var(--c-text-1); background:var(--c-bg); outline:none; transition:border-color 150ms,box-shadow 150ms; }
.form-field input:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 3px rgba(26,86,255,0.08); }
.field-error { font-size:11px; color:var(--c-danger); margin-top:5px; }
.pw-rules { font-size:11px; color:var(--c-text-3); margin-top:5px; }
.btn-save { width:100%; padding:12px; background:var(--c-accent); color:#fff; border:none; border-radius:9px; font-size:14px; font-weight:600; font-family:var(--f-sans); cursor:pointer; transition:opacity 150ms; margin-top:4px; }
.btn-save:hover { opacity:0.9; }
.btn-save:disabled { opacity:0.5; cursor:not-allowed; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); border-radius:9px; padding:14px 16px; font-size:13px; font-weight:600; color:#15803D; margin-bottom:18px; }
</style>

<div class="pw-wrap">
    <div class="pw-card">
        <div class="pw-head">
            <div class="pw-title">Change Password</div>
            <div class="pw-sub">Update your login password. You will need your current password to confirm.</div>
        </div>
        <div class="pw-body">

            @if(session('success'))
                <div class="flash-success">✓ {{ session('success') }}</div>
            @endif

            @if(! $done)
            <div class="form-field">
                <label>Current Password</label>
                <input type="password" wire:model="currentPassword" autocomplete="current-password" placeholder="Your current password">
                @error('currentPassword') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-field">
                <label>New Password</label>
                <input type="password" wire:model="newPassword" autocomplete="new-password" placeholder="At least 8 characters">
                @error('newPassword') <div class="field-error">{{ $message }}</div> @enderror
                <div class="pw-rules">Minimum 8 characters, must include letters and numbers.</div>
            </div>

            <div class="form-field">
                <label>Confirm New Password</label>
                <input type="password" wire:model="newPasswordConfirm" autocomplete="new-password" placeholder="Repeat new password">
                @error('newPasswordConfirm') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <button class="btn-save" wire:click="save"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Update Password</span>
                <span wire:loading>Saving…</span>
            </button>
            @endif

        </div>
    </div>
</div>
</div>
