<div>
<style>
/* ── Back link ── */
.back-link {
    display:inline-flex; align-items:center; gap:6px;
    font-size:13px; color:var(--c-text-3); text-decoration:none;
    margin-bottom:20px; transition:color 150ms;
}
.back-link:hover { color:var(--c-text-1); }

/* ── Invoice header card ── */
.inv-header {
    background:var(--c-surface); border:1px solid var(--c-border);
    border-radius:var(--r-md); padding:24px;
    display:flex; justify-content:space-between; align-items:flex-start;
    flex-wrap:wrap; gap:20px; margin-bottom:20px;
}
.inv-student-name { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.inv-meta { font-size:13px; color:var(--c-text-3); margin-top:4px; }
.inv-adm  { font-family:var(--f-mono); font-size:12px; color:var(--c-text-3); margin-top:2px; }

.inv-amounts { display:flex; gap:24px; flex-wrap:wrap; }
.inv-amount-block { text-align:right; }
.inv-amount-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; }
.inv-amount-value { font-size:22px; font-weight:700; letter-spacing:-0.03em; margin-top:2px; font-family:var(--f-mono); }
.inv-amount-value.total    { color:var(--c-text-1); }
.inv-amount-value.paid     { color:var(--c-success); }
.inv-amount-value.balance  { color:var(--c-danger); }

/* ── Status badge ── */
.badge { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-unpaid  { background:rgba(190,18,60,0.08);  color:var(--c-danger); }
.badge-partial { background:rgba(180,83,9,0.08);   color:var(--c-warning); }
.badge-paid    { background:rgba(21,128,61,0.08);   color:var(--c-success); }

/* ── Flash ── */
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.08);  border:1px solid rgba(190,18,60,0.2);  color:#BE123C; }

/* ── Grid ── */
.detail-grid { display:grid; grid-template-columns:1fr; gap:16px; margin-bottom:20px; }
@media(min-width:900px) { .detail-grid { grid-template-columns:1.6fr 1fr; } }

/* ── Panel ── */
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }

/* ── Tables ── */
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 20px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table th.right, .data-table td.right { text-align:right; }
.data-table td { padding:13px 20px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }
.mono { font-family:var(--f-mono); font-size:12px; }

/* ── Total row ── */
.total-row td { font-weight:700; background:var(--c-bg) !important; border-top:2px solid var(--c-border); }

/* ── Admin added badge ── */
.badge-admin { background:rgba(180,83,9,0.08); color:#B45309; font-size:10px; padding:2px 6px; border-radius:4px; margin-left:6px; }
.badge-system { background:rgba(26,86,255,0.06); color:var(--c-accent); font-size:10px; padding:2px 6px; border-radius:4px; margin-left:6px; }

/* ── Buttons ── */
.btn-primary { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-primary:hover { opacity:0.9; }
.btn-primary:disabled { opacity:0.4; cursor:not-allowed; }
.btn-ghost { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:background 150ms; }
.btn-ghost:hover { background:var(--c-bg); }

/* ── Empty state ── */
.empty-cell { padding:28px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }

/* ── Info rows (student details) ── */
.info-row { display:flex; justify-content:space-between; padding:11px 20px; border-bottom:1px solid var(--c-border); font-size:13px; }
.info-row:last-child { border-bottom:none; }
.info-label { color:var(--c-text-3); font-size:12px; }
.info-value { color:var(--c-text-1); font-weight:500; text-align:right; }

/* ── Modal ── */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:440px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:20px; }
.form-field { margin-bottom:16px; }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:5px; }
.form-field input, .form-field select {
    width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px;
    font-family:var(--f-sans); font-size:14px; color:var(--c-text-1);
    background:var(--c-bg); outline:none; transition:border-color 150ms; -webkit-appearance:none;
}
.form-field input:focus, .form-field select:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 3px rgba(26,86,255,0.08); }
.field-error { font-size:11px; color:var(--c-danger); margin-top:4px; }
.modal-actions { display:flex; gap:10px; margin-top:24px; justify-content:flex-end; }
.btn-cancel { padding:9px 16px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); color:var(--c-text-2); }
.btn-cancel:hover { background:var(--c-bg); }
.btn-confirm { padding:9px 20px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm:hover { opacity:0.9; }

/* ── PDF link ── */
.btn-pdf { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; transition:background 150ms; }
.btn-pdf:hover { background:var(--c-bg); color:var(--c-text-1); }
</style>

{{-- Flash --}}
@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">⚠ {{ session('error') }}</div>
@endif

{{-- Back link --}}
<a href="{{ route('admin.fees.invoices') }}" class="back-link">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M10 3L5 8l5 5"/>
    </svg>
    All Invoices
</a>

{{-- Invoice header --}}
<div class="inv-header">
    <div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="inv-student-name">{{ $invoice->student->full_name }}</span>
            <span class="badge badge-{{ $invoice->status }}">
                <span class="badge-dot"></span>
                {{ ucfirst($invoice->status) }}
            </span>
        </div>
        <div class="inv-meta">
            {{ $invoice->term->name }} Term — {{ $invoice->term->session->name }}
            @php
                $enrolment = $invoice->student->enrolments
                    ->where('academic_session_id', $invoice->term->academic_session_id)
                    ->first();
            @endphp
            @if($enrolment)
                · {{ $enrolment->schoolClass->display_name }}
            @endif
        </div>
        <div class="inv-adm">{{ $invoice->student->admission_number }}</div>
    </div>

    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:12px;">
        {{-- Action buttons --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
            <a href="{{ route('admin.fees.invoices.pdf', $invoice) }}" target="_blank" class="btn-pdf">
                <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 1h6l4 4v10H2V1z"/><path d="M10 1v4h4"/>
                    <path d="M5 9h6M5 11h4"/>
                </svg>
                Download PDF
            </a>
            @if($invoice->status !== 'paid')
                <button class="btn-primary" wire:click="openPayForm">
                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M8 3v10M3 8h10"/>
                    </svg>
                    Record Payment
                </button>
            @endif
        </div>

        {{-- Amount summary --}}
        <div class="inv-amounts">
            <div class="inv-amount-block">
                <div class="inv-amount-label">Total</div>
                <div class="inv-amount-value total">₦{{ number_format($invoice->total_amount, 0) }}</div>
            </div>
            <div class="inv-amount-block">
                <div class="inv-amount-label">Paid</div>
                <div class="inv-amount-value paid">₦{{ number_format($invoice->amount_paid, 0) }}</div>
            </div>
            <div class="inv-amount-block">
                <div class="inv-amount-label">Balance</div>
                <div class="inv-amount-value balance">₦{{ number_format($invoice->balance, 0) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Two-column grid --}}
<div class="detail-grid">

    {{-- LEFT: Line items + Payment history --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Fee line items --}}
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">Fee Breakdown</span>
                @if($invoice->status !== 'paid')
                    <button class="btn-ghost" style="font-size:12px;padding:6px 12px;" wire:click="openAddItem">
                        + Add Optional Item
                    </button>
                @endif
            </div>
            @if($invoice->items->isEmpty())
                <div class="empty-cell">No line items on this invoice.</div>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Source</th>
                            <th class="right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td>
                                    @if($item->added_by === 'admin')
                                        <span class="badge-admin">Admin</span>
                                    @else
                                        <span class="badge-system">System</span>
                                    @endif
                                </td>
                                <td class="right mono">₦{{ number_format($item->amount, 0) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2">Total</td>
                            <td class="right mono">₦{{ number_format($invoice->total_amount, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Payment history --}}
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">Payment History</span>
                <span style="font-size:11px;color:var(--c-text-3)">
                    {{ $invoice->payments->count() }} {{ Str::plural('payment', $invoice->payments->count()) }}
                </span>
            </div>
            @if($invoice->payments->isEmpty())
                <div class="empty-cell">No payments recorded yet.</div>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Receipt</th>
                            <th class="right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments->sortByDesc('paid_at') as $payment)
                            <tr>
                                <td>{{ $payment->paid_at->format('d M Y') }}</td>
                                <td>{{ $payment->method }}</td>
                                <td class="mono" style="color:var(--c-text-3)">{{ $payment->receipt_number }}</td>
                                <td class="right mono" style="color:var(--c-success)">₦{{ number_format($payment->amount, 0) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="3">Total Paid</td>
                            <td class="right mono">₦{{ number_format($invoice->amount_paid, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- RIGHT: Student & invoice details --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Student info --}}
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">Student</span>
                <a href="{{ route('admin.students.profile', $invoice->student) }}"
                   style="font-size:12px;color:var(--c-accent);text-decoration:none;">View Profile →</a>
            </div>
            <div class="info-row">
                <span class="info-label">Full Name</span>
                <span class="info-value">{{ $invoice->student->full_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Admission No.</span>
                <span class="info-value mono">{{ $invoice->student->admission_number }}</span>
            </div>
            @if($enrolment)
            <div class="info-row">
                <span class="info-label">Class</span>
                <span class="info-value">{{ $enrolment->schoolClass->display_name }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Gender</span>
                <span class="info-value">{{ $invoice->student->gender }}</span>
            </div>
        </div>

        {{-- Parent contacts --}}
        @if($invoice->student->parents->isNotEmpty())
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">Parent / Guardian</span>
            </div>
            @foreach($invoice->student->parents as $parent)
                <div class="info-row" style="flex-direction:column;gap:2px;">
                    <span class="info-value" style="text-align:left;">
                        {{ $parent->user?->name ?? $parent->_temp_name ?? '—' }}
                    </span>
                    <span class="info-label">
                        {{ $parent->user?->email ?? $parent->_temp_email ?? '' }}
                        @if($parent->phone) · {{ $parent->phone }} @endif
                    </span>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Invoice metadata --}}
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">Invoice Details</span>
            </div>
            <div class="info-row">
                <span class="info-label">Invoice ID</span>
                <span class="info-value mono">#{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Term</span>
                <span class="info-value">{{ $invoice->term->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Session</span>
                <span class="info-value">{{ $invoice->term->session->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Generated</span>
                <span class="info-value">{{ $invoice->created_at->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="badge badge-{{ $invoice->status }}">
                        <span class="badge-dot"></span>
                        {{ ucfirst($invoice->status) }}
                    </span>
                </span>
            </div>
        </div>

    </div>
</div>

{{-- Record payment modal --}}
@if($showPayForm)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Record Payment</div>

        <div class="form-field">
            <label>Amount (₦) <span style="color:var(--c-danger)">*</span></label>
            <input type="text" inputmode="numeric" wire:model="payAmount" placeholder="0"
                x-data
                x-on:focus="$el.value = $el.value.replace(/,/g, '')"
                x-on:blur="let r=$el.value.replace(/,/g,'').replace(/[^0-9]/g,''); $el.value=r?parseInt(r).toLocaleString('en-NG'):''">
            @error('payAmount') <div class="field-error">{{ $message }}</div> @enderror
            <div style="font-size:11px;color:var(--c-text-3);margin-top:4px;">
                Balance due: ₦{{ number_format($invoice->balance, 0) }}
            </div>
        </div>

        <div class="form-field">
            <label>Payment Method <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="payMethod">
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="POS">POS</option>
            </select>
            @error('payMethod') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Reference / Teller No. <span style="color:var(--c-text-3);font-weight:400">(optional)</span></label>
            <input type="text" wire:model="payReference" placeholder="Bank teller or transfer reference">
            @error('payReference') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showPayForm', false)">Cancel</button>
            <button class="btn-confirm" wire:click="recordPayment">
                <span wire:loading.remove wire:target="recordPayment">Record Payment</span>
                <span wire:loading wire:target="recordPayment">Saving…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Add optional item modal --}}
@if($showAddItem)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Add Optional Item</div>

        <div class="form-field">
            <label>Fee Item <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="addItemId">
                <option value="">Select a fee item…</option>
                @foreach($optionalItems as $opt)
                    <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                @endforeach
            </select>
            @error('addItemId') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Amount (₦) <span style="color:var(--c-danger)">*</span></label>
            <input type="text" inputmode="numeric" wire:model="addItemAmount" placeholder="0"
                x-data
                x-on:focus="$el.value = $el.value.replace(/,/g, '')"
                x-on:blur="let r=$el.value.replace(/,/g,'').replace(/[^0-9]/g,''); $el.value=r?parseInt(r).toLocaleString('en-NG'):''">
            @error('addItemAmount') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showAddItem', false)">Cancel</button>
            <button class="btn-confirm" wire:click="addOptionalItem">
                <span wire:loading.remove wire:target="addOptionalItem">Add to Invoice</span>
                <span wire:loading wire:target="addOptionalItem">Adding…</span>
            </button>
        </div>
    </div>
</div>
@endif

</div>
