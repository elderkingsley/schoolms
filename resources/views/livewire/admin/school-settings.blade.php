<div>
<style>
.pg-header  { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.pg-title   { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub     { font-size:13px; color:var(--c-text-3); margin-top:2px; }
.flash      { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }

.settings-grid { display:grid; grid-template-columns:1fr; gap:16px; }
@media(min-width:768px){ .settings-grid { grid-template-columns:1fr 1fr; } }

.card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.card-header { padding:14px 18px; border-bottom:1px solid var(--c-border); background:var(--c-bg); }
.card-title  { font-size:13px; font-weight:700; color:var(--c-text-1); }
.card-sub    { font-size:11px; color:var(--c-text-3); margin-top:1px; }
.card-body   { padding:18px; display:flex; flex-direction:column; gap:14px; }

.field       { display:flex; flex-direction:column; gap:5px; }
.field label { font-size:11px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; }
.field input, .field textarea, .field select {
    padding:9px 12px; border:1px solid var(--c-border); border-radius:8px;
    font-size:13px; font-family:var(--f-sans); background:var(--c-surface);
    outline:none; color:var(--c-text-1); width:100%;
}
.field input:focus, .field textarea:focus, .field select:focus { border-color:var(--c-accent); }
.field textarea { resize:vertical; min-height:70px; }
.field select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%23888' d='M4 6l4 4 4-4'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; padding-right:36px; }
.field-hint { font-size:11px; color:var(--c-text-3); }
.field-hint-warning { font-size:11px; color:#B45309; background:rgba(180,83,9,0.08); padding:8px 10px; border-radius:6px; margin-top:6px; }
.err { font-size:11px; color:var(--c-danger); margin-top:2px; }

.logo-preview { display:flex; align-items:center; gap:12px; margin-bottom:8px; }
.logo-img { width:72px; height:72px; border-radius:8px; object-fit:contain; border:1px solid var(--c-border); background:#f5f5f5; padding:4px; }
.logo-placeholder { width:72px; height:72px; border-radius:8px; border:2px dashed var(--c-border); display:flex; align-items:center; justify-content:center; background:var(--c-bg); }
.logo-placeholder span { font-size:22px; font-weight:700; color:var(--c-accent); }

.btn-primary { padding:10px 20px; background:var(--c-accent); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; font-family:var(--f-sans); cursor:pointer; }
.btn-primary:hover { opacity:0.9; }
.btn-danger-sm { padding:5px 10px; background:none; border:1px solid var(--c-border); color:var(--c-danger); border-radius:6px; font-size:11px; font-weight:500; cursor:pointer; font-family:var(--f-sans); }
.btn-danger-sm:hover { background:rgba(190,18,60,0.05); }
.btn-outline { padding:8px 16px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:6px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); }
.btn-outline:hover { background:var(--c-bg); }
.save-bar { display:flex; justify-content:flex-end; padding-top:8px; }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:440px; padding:24px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size:15px; font-weight:700; color:var(--c-text-1); margin-bottom:12px; display:flex; align-items:center; gap:8px; }
.modal-body { font-size:13px; color:var(--c-text-2); line-height:1.6; margin-bottom:20px; }
.modal-highlight { background:var(--c-accent-bg); padding:12px 14px; border-radius:8px; margin:14px 0; font-weight:600; color:var(--c-accent); text-align:center; }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; }
</style>

<div class="pg-header">
    <div>
        <div class="pg-title">School Settings</div>
        <div class="pg-sub">Configure school identity, logo, wallet provider, and invoice payment details</div>
    </div>
</div>

@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif

<form wire:submit.prevent="save">
<div class="settings-grid">

    {{-- ── School Identity ── --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">School Identity</div>
            <div class="card-sub">Appears on all invoices and documents</div>
        </div>
        <div class="card-body">
            <div class="field">
                <label>School Name</label>
                <input type="text" wire:model="school_name" placeholder="e.g. Nurtureville School">
                @error('school_name') <span class="err">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label>Tagline</label>
                <input type="text" wire:model="school_tagline" placeholder="e.g. Nurturing Minds, Building Futures">
            </div>
            <div class="field">
                <label>Address</label>
                <textarea wire:model="school_address" placeholder="Full school address"></textarea>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" wire:model="school_email" placeholder="admin@school.org">
            </div>
            <div class="field">
                <label>Phone</label>
                <input type="text" wire:model="school_phone" placeholder="+234 ...">
            </div>
            <div class="field">
                <label>Website</label>
                <input type="text" wire:model="school_website" placeholder="connect.nurturevilleschool.org">
            </div>
        </div>
    </div>

    {{-- ── School Logo ── --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">School Logo</div>
            <div class="card-sub">Displayed on invoice PDFs and documents. PNG or JPG, max 2MB.</div>
        </div>
        <div class="card-body">
            <div class="logo-preview">
                @if($current_logo)
                    <img src="{{ Storage::disk('public')->url($current_logo) }}" class="logo-img" alt="School Logo">
                    <div>
                        <div style="font-size:12px;color:var(--c-text-2);margin-bottom:6px;">Current logo</div>
                        <button type="button" wire:click="removeLogo" class="btn-danger-sm">Remove</button>
                    </div>
                @else
                    <div class="logo-placeholder">
                        <span>{{ strtoupper(substr($school_name ?: 'N', 0, 1)) }}</span>
                    </div>
                    <div style="font-size:12px;color:var(--c-text-3);">No logo uploaded yet</div>
                @endif
            </div>

            @if($logo)
                <div style="margin-bottom:8px;">
                    <img src="{{ $logo->temporaryUrl() }}" class="logo-img" alt="Preview">
                    <div style="font-size:11px;color:var(--c-text-3);margin-top:4px;">Preview — save to apply</div>
                </div>
            @endif

            <div class="field">
                <label>Upload New Logo</label>
                <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/jpg,image/gif">
                @error('logo') <span class="err">{{ $message }}</span> @enderror
                <span class="field-hint">PNG or JPG recommended. Will replace the current logo.</span>
            </div>
        </div>
    </div>

    {{-- ── Wallet Provider ── --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Wallet Provider</div>
            <div class="card-sub">Virtual account provider for parent payments</div>
        </div>
        <div class="card-body">
            <div class="field">
                <label>Active Provider</label>
                <select wire:model.live="wallet_provider">
                    <option value="budpay">BudPay</option>
                    <option value="juicyway">JuicyWay</option>
                </select>
                @error('wallet_provider') <span class="err">{{ $message }}</span> @enderror
            </div>
            <div class="field-hint">
                <strong>Current:</strong>
                @if($wallet_provider === 'juicyway')
                    JuicyWay
                @else
                    BudPay
                @endif
            </div>
            <div class="field-hint-warning">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                </svg>
                Changing provider will queue account creation for all parents without an account with the new provider.
            </div>
        </div>
    </div>

    {{-- ── Invoice Payment Instructions ── --}}
    <div class="card" style="grid-column: 1 / -1;">
        <div class="card-header">
            <div class="card-title">Invoice Payment Instructions</div>
            <div class="card-sub">These details appear on every unpaid invoice PDF. Leave blank to hide the payment instructions section.</div>
        </div>
        <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
            <div class="field">
                <label>Bank Name</label>
                <input type="text" wire:model="invoice_bank_name" placeholder="e.g. Wema Bank">
            </div>
            <div class="field">
                <label>Account Name</label>
                <input type="text" wire:model="invoice_account_name" placeholder="e.g. Nurtureville School">
            </div>
            <div class="field">
                <label>Account Number</label>
                <input type="text" wire:model="invoice_account_number" placeholder="10-digit NUBAN">
            </div>
            <div class="field" style="grid-column:1/-1;">
                <label>Payment Note</label>
                <input type="text" wire:model="invoice_payment_note" placeholder="e.g. Use child's admission number as reference">
            </div>
        </div>
    </div>

</div>

<div class="save-bar" style="margin-top:20px;">
    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
        <span wire:loading.remove>Save Settings</span>
        <span wire:loading>Saving…</span>
    </button>
</div>
</form>

{{-- ── Provider Change Confirmation Modal ── --}}
@if($showProviderConfirmModal)
<div class="modal-overlay" wire:click.self="cancelProviderChange">
    <div class="modal-box">
        <div class="modal-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1A56FF" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
            </svg>
            Confirm Provider Change
        </div>
        <div class="modal-body">
            <p>You are switching the wallet provider to <strong>{{ ucfirst($pendingProvider) }}</strong>.</p>
            <div class="modal-highlight">
                {{ $parentsNeedingProvisioning }} parent(s) will have new {{ ucfirst($pendingProvider) }} accounts created.
            </div>
            <p style="font-size:12px;color:var(--c-text-3);">Parents who already have {{ ucfirst($pendingProvider) }} accounts will be skipped.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-outline" wire:click="cancelProviderChange">Cancel</button>
            <button type="button" class="btn-primary" wire:click="confirmProviderChange" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="confirmProviderChange">Yes, Switch</span>
                <span wire:loading wire:target="confirmProviderChange">Processing…</span>
            </button>
        </div>
    </div>
</div>
@endif

</div>
