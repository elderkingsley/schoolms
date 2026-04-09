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
.field input, .field textarea {
    padding:9px 12px; border:1px solid var(--c-border); border-radius:8px;
    font-size:13px; font-family:var(--f-sans); background:var(--c-surface);
    outline:none; color:var(--c-text-1); width:100%;
}
.field input:focus, .field textarea:focus { border-color:var(--c-accent); }
.field textarea { resize:vertical; min-height:70px; }
.field-hint { font-size:11px; color:var(--c-text-3); }
.err { font-size:11px; color:var(--c-danger); margin-top:2px; }

.logo-preview { display:flex; align-items:center; gap:12px; margin-bottom:8px; }
.logo-img { width:72px; height:72px; border-radius:8px; object-fit:contain; border:1px solid var(--c-border); background:#f5f5f5; padding:4px; }
.logo-placeholder { width:72px; height:72px; border-radius:8px; border:2px dashed var(--c-border); display:flex; align-items:center; justify-content:center; background:var(--c-bg); }
.logo-placeholder span { font-size:22px; font-weight:700; color:var(--c-accent); }

.btn-primary { padding:10px 20px; background:var(--c-accent); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; font-family:var(--f-sans); cursor:pointer; }
.btn-primary:hover { opacity:0.9; }
.btn-danger-sm { padding:5px 10px; background:none; border:1px solid var(--c-border); color:var(--c-danger); border-radius:6px; font-size:11px; font-weight:500; cursor:pointer; font-family:var(--f-sans); }
.btn-danger-sm:hover { background:rgba(190,18,60,0.05); }
.save-bar { display:flex; justify-content:flex-end; padding-top:8px; }
</style>

<div class="pg-header">
    <div>
        <div class="pg-title">School Settings</div>
        <div class="pg-sub">Configure school identity, logo and invoice payment details</div>
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

</div>
