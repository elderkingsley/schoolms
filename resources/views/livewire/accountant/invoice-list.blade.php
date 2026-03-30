<div>
<style>
.pg-title { font-size:18px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:16px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.filters { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
.search-wrap { position:relative; flex:1; min-width:180px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:13px; color:var(--c-text-1); background:var(--c-surface); outline:none; }
.search-wrap input:focus { border-color:var(--c-accent); }
.filter-sel { padding:9px 10px; border:1px solid var(--c-border); border-radius:8px; font-size:12px; font-family:var(--f-sans); background:var(--c-surface); color:var(--c-text-1); outline:none; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 8px center; padding-right:24px; }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.07em; padding:9px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table th.right, .data-table td.right { text-align:right; }
.data-table td { padding:12px 16px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }
.student-name { font-weight:600; }
.student-adm  { font-family:var(--f-mono); font-size:10px; color:var(--c-text-3); }
.mono { font-family:var(--f-mono); font-size:12px; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-paid    { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-partial { background:rgba(180,83,9,0.08);  color:#B45309; }
.badge-unpaid  { background:rgba(190,18,60,0.08); color:var(--c-danger); }
.btn-pay { padding:5px 10px; border-radius:6px; font-size:11px; font-weight:500; background:#15803D; color:#fff; border:none; cursor:pointer; font-family:var(--f-sans); }
.btn-pay:disabled { opacity:0.4; cursor:not-allowed; }
.pag-wrap { padding:14px 16px; border-top:1px solid var(--c-border); }
.empty-state { padding:40px; text-align:center; font-size:13px; color:var(--c-text-3); }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:flex-end; justify-content:center; padding:0; }
@media(min-width:600px) { .modal-overlay { align-items:center; padding:16px; } }
.modal-box { background:var(--c-surface); border-radius:16px 16px 0 0; width:100%; max-width:440px; padding:24px; box-shadow:0 -8px 32px rgba(0,0,0,0.15); }
@media(min-width:600px) { .modal-box { border-radius:16px; } }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); margin-bottom:4px; }
.modal-sub   { font-size:12px; color:var(--c-text-3); margin-bottom:16px; }
.form-field { margin-bottom:12px; }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:4px; }
.form-field input, .form-field select { width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:14px; color:var(--c-text-1); background:var(--c-bg); outline:none; -webkit-appearance:none; }
.form-field input:focus, .form-field select:focus { border-color:#15803D; background:#fff; }
.field-error { font-size:11px; color:var(--c-danger); margin-top:3px; }
.modal-actions { display:flex; gap:10px; margin-top:20px; }
.btn-cancel  { flex:1; padding:11px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm { flex:2; padding:11px; background:#15803D; color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif

<div class="pg-title">Invoices</div>

<div class="filters">
    <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search student…">
    </div>
    <select wire:model.live="filterTerm" class="filter-sel">
        <option value="">All Terms</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
        @endforeach
    </select>
    <select wire:model.live="filterStatus" class="filter-sel">
        <option value="">All Statuses</option>
        <option value="unpaid">Unpaid</option>
        <option value="partial">Part Paid</option>
        <option value="paid">Paid</option>
    </select>
</div>

<div class="panel">
    @if($invoices->isEmpty())
        <div class="empty-state">No invoices found.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Term</th>
                    <th class="right">Total</th>
                    <th class="right">Paid</th>
                    <th class="right">Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>
                            <div class="student-name">{{ $invoice->student->full_name }}</div>
                            <div class="student-adm">{{ $invoice->student->admission_number }}</div>
                        </td>
                        <td style="font-size:12px;color:var(--c-text-3);">
                            {{ $invoice->term->name }} · {{ $invoice->term->session->name }}
                        </td>
                        <td class="right mono">₦{{ number_format($invoice->total_amount, 0) }}</td>
                        <td class="right mono" style="color:#15803D;">₦{{ number_format($invoice->amount_paid, 0) }}</td>
                        <td class="right mono" style="color:{{ $invoice->balance > 0 ? 'var(--c-danger)' : '#15803D' }}">
                            ₦{{ number_format($invoice->balance, 0) }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $invoice->status }}">
                                <span class="badge-dot"></span>
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn-pay"
                                wire:click="openPayment({{ $invoice->id }})"
                                @if($invoice->status === 'paid') disabled @endif>
                                + Payment
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($invoices->hasPages())
            <div class="pag-wrap">{{ $invoices->links() }}</div>
        @endif
    @endif
</div>

{{-- Record payment modal --}}
@if($showPayModal && $payingInvoice)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Record Payment</div>
        <div class="modal-sub">
            {{ $payingInvoice->student->full_name }} ·
            Balance: ₦{{ number_format($payingInvoice->balance, 0) }}
        </div>

        <div class="form-field">
            <label>Amount (₦) <span style="color:var(--c-danger)">*</span></label>
            <input type="number" wire:model="payAmount" placeholder="0" min="1">
            @error('payAmount') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Payment Method <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="payMethod">
                <option value="cash">Cash</option>
                <option value="transfer">Bank Transfer</option>
                <option value="pos">POS</option>
                <option value="cheque">Cheque</option>
            </select>
            @error('payMethod') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Note <span style="color:var(--c-text-3);font-weight:400">(optional)</span></label>
            <input type="text" wire:model="payNote" placeholder="e.g. Receipt No. 12345">
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showPayModal', false)">Cancel</button>
            <button class="btn-confirm" wire:click="recordPayment"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Record Payment</span>
                <span wire:loading>Saving…</span>
            </button>
        </div>
    </div>
</div>
@endif
</div>
