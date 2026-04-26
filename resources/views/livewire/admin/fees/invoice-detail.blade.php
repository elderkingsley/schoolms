<div>
<style>
.back-link { display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--c-text-3); text-decoration:none; margin-bottom:20px; transition:color 150ms; }
.back-link:hover { color:var(--c-text-1); }

/* Header card */
.inv-header { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:24px; display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px; margin-bottom:20px; }
.inv-student-name { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.inv-meta { font-size:13px; color:var(--c-text-3); margin-top:4px; }
.inv-adm  { font-family:var(--f-mono); font-size:12px; color:var(--c-text-3); margin-top:2px; }
.inv-amounts { display:flex; gap:24px; flex-wrap:wrap; }
.inv-amount-block { text-align:right; }
.inv-amount-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; }
.inv-amount-value { font-size:22px; font-weight:700; letter-spacing:-0.03em; margin-top:2px; font-family:var(--f-mono); }
.inv-amount-value.total   { color:var(--c-text-1); }
.inv-amount-value.paid    { color:#15803D; }
.inv-amount-value.balance { color:var(--c-danger); }

/* Badges */
.badge { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-unpaid  { background:rgba(190,18,60,0.08);  color:var(--c-danger); }
.badge-partial { background:rgba(180,83,9,0.08);   color:#B45309; }
.badge-paid    { background:rgba(21,128,61,0.08);   color:#15803D; }
.badge-draft   { background:rgba(100,100,100,0.08); color:#666; }
.badge-sent    { background:rgba(26,86,255,0.08);   color:var(--c-accent); }
.badge-admin  { background:rgba(180,83,9,0.08);  color:#B45309; font-size:10px; padding:2px 6px; border-radius:4px; }
.badge-system { background:rgba(26,86,255,0.06); color:var(--c-accent); font-size:10px; padding:2px 6px; border-radius:4px; }
.badge-custom { background:rgba(100,100,100,0.08); color:#666; font-size:10px; padding:2px 6px; border-radius:4px; }

/* Flash */
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.08); border:1px solid rgba(190,18,60,0.2); color:var(--c-danger); }

/* Lock banner */
.lock-banner { background:rgba(180,83,9,0.06); border:1px solid rgba(180,83,9,0.2); border-radius:var(--r-sm); padding:10px 16px; margin-bottom:16px; font-size:12px; color:#B45309; display:flex; align-items:center; gap:8px; }

/* Grid */
.detail-grid { display:grid; grid-template-columns:1fr; gap:16px; margin-bottom:20px; }
@media(min-width:900px) { .detail-grid { grid-template-columns:1.6fr 1fr; } }

/* Panels */
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); flex-wrap:wrap; gap:8px; }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }

/* Tables */
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 20px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table th.right, .data-table td.right { text-align:right; }
.data-table td { padding:12px 20px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }
.mono { font-family:var(--f-mono); font-size:12px; }
.total-row td { font-weight:700; background:var(--c-bg) !important; border-top:2px solid var(--c-border); }

/* Item action buttons in table */
.item-actions { display:flex; align-items:center; gap:5px; justify-content:flex-end; }
.btn-icon { width:28px; height:28px; border-radius:6px; border:1px solid var(--c-border); background:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--c-text-3); transition:all 150ms; padding:0; flex-shrink:0; }
.btn-icon:hover { background:var(--c-bg); color:var(--c-text-1); }
.btn-icon-danger:hover { border-color:rgba(190,18,60,0.3); color:var(--c-danger); background:rgba(190,18,60,0.04); }

/* Buttons */
.btn-primary { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-primary:hover { opacity:0.9; }
.btn-ghost { display:inline-flex; align-items:center; gap:6px; padding:7px 13px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:background 150ms; }
.btn-ghost:hover { background:var(--c-bg); }
.btn-danger { display:inline-flex; align-items:center; gap:6px; padding:7px 13px; background:none; border:1px solid rgba(190,18,60,0.3); color:var(--c-danger); border-radius:8px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:background 150ms; }
.btn-danger:hover { background:rgba(190,18,60,0.06); }
.btn-send { display:inline-flex; align-items:center; gap:6px; padding:7px 13px; background:#15803D; color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-send:hover { opacity:0.9; }

/* Info rows */
.info-row { display:flex; justify-content:space-between; padding:11px 20px; border-bottom:1px solid var(--c-border); font-size:13px; }
.info-row:last-child { border-bottom:none; }
.info-label { color:var(--c-text-3); font-size:12px; }
.info-value { color:var(--c-text-1); font-weight:500; text-align:right; }
.empty-cell { padding:28px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.btn-pdf { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; transition:background 150ms; }
.btn-pdf:hover { background:var(--c-bg); }

/* Payment link panel */
.pay-link-box { padding:16px 20px; }
.pay-link-url { display:flex; align-items:center; gap:8px; background:var(--c-bg); border:1px solid var(--c-border); border-radius:8px; padding:10px 12px; margin-top:10px; }
.pay-link-url a { font-size:12px; color:var(--c-accent); text-decoration:none; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; font-family:var(--f-mono); }
.pay-link-url a:hover { text-decoration:underline; }
.copy-btn { flex-shrink:0; padding:4px 8px; border:1px solid var(--c-border); border-radius:5px; background:var(--c-surface); font-size:11px; font-weight:500; cursor:pointer; font-family:var(--f-sans); color:var(--c-text-2); transition:background 150ms; }
.copy-btn:hover { background:var(--c-bg); }
.pay-link-error { background:rgba(190,18,60,0.05); border:1px solid rgba(190,18,60,0.2); border-radius:8px; padding:12px; margin-top:10px; font-size:12px; color:var(--c-danger); line-height:1.5; }
.pay-link-pending { background:rgba(180,83,9,0.05); border:1px solid rgba(180,83,9,0.2); border-radius:8px; padding:12px; margin-top:10px; font-size:12px; color:#B45309; }
.mode-tabs { display:flex; gap:2px; background:var(--c-bg); border:1px solid var(--c-border); border-radius:7px; padding:3px; margin-bottom:16px; }
.mode-tab { flex:1; padding:7px; border-radius:5px; border:none; background:none; font-size:12px; font-weight:500; color:var(--c-text-3); cursor:pointer; font-family:var(--f-sans); transition:all 150ms; }
.mode-tab.active { background:var(--c-surface); color:var(--c-text-1); box-shadow:0 1px 3px rgba(0,0,0,0.08); }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:460px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:6px; }
.modal-sub   { font-size:13px; color:var(--c-text-3); margin-bottom:18px; }
.form-field { margin-bottom:14px; }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:5px; }
.form-field input, .form-field select { width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:14px; color:var(--c-text-1); background:var(--c-bg); outline:none; transition:border-color 150ms; -webkit-appearance:none; }
.form-field input:focus, .form-field select:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 3px rgba(26,86,255,0.08); }
.field-error { font-size:11px; color:var(--c-danger); margin-top:4px; }
.field-hint  { font-size:11px; color:var(--c-text-3); margin-top:4px; }
.modal-actions { display:flex; gap:10px; margin-top:20px; justify-content:flex-end; }
.btn-cancel  { padding:9px 16px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm { padding:9px 20px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm-danger { padding:9px 20px; background:var(--c-danger); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">⚠ {{ session('error') }}</div>
@endif

<a href="{{ route('admin.fees.invoices') }}" class="back-link">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 3L5 8l5 5"/></svg>
    All Invoices
</a>

{{-- Edit lock warning --}}
@if(! $this->canEdit())
    <div class="lock-banner">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
            <rect x="3" y="7" width="10" height="8" rx="1.5"/>
            <path d="M5 7V5a3 3 0 0 1 6 0v2"/>
        </svg>
        Fee items are locked because payments have been recorded against this invoice.
        Remove all payments first to re-enable editing.
    </div>
@endif

{{-- Invoice header --}}
<div class="inv-header">
    <div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <span class="inv-student-name">{{ $invoice->student->full_name }}</span>
            <span class="badge badge-{{ $invoice->status }}">
                <span class="badge-dot"></span>{{ ucfirst($invoice->status) }}
            </span>
            @if($invoice->isSent())
                <span class="badge badge-sent">
                    <span class="badge-dot"></span>Sent {{ $invoice->sent_at->format('d M Y') }}
                </span>
            @else
                <span class="badge badge-draft">
                    <span class="badge-dot"></span>Draft
                </span>
            @endif
        </div>
        @php
            // Resolve enrolment here so $enrolment is available throughout the page
            // (used in the Student panel below). Null for misc invoices.
            $enrolment = $invoice->isMiscellaneous()
                ? null
                : $invoice->student->enrolments
                    ->where('academic_session_id', $invoice->term->academic_session_id)
                    ->first();
        @endphp
        <div class="inv-meta">
            @if($invoice->isMiscellaneous())
                <span style="background:rgba(124,58,237,0.08);color:#7C3AED;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:600;margin-right:4px;">MISC</span>{{ $invoice->description }}
            @else
                {{ $invoice->term->name }} Term — {{ $invoice->term->session->name }}
                @if($enrolment) · {{ $enrolment->schoolClass->display_name }} @endif
            @endif
        </div>
        <div class="inv-adm">{{ $invoice->student->admission_number }}</div>
    </div>

    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:12px;">
        <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
            <a href="{{ route('admin.fees.invoices.pdf', $invoice) }}" target="_blank" class="btn-pdf">
                <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 1h6l4 4v10H2V1z"/><path d="M10 1v4h4"/>
                </svg>
                PDF
            </a>

            @if($invoice->isDraft())
                <button class="btn-send" wire:click="sendInvoice"
                    wire:confirm="Send this invoice to {{ $invoice->student->full_name }}'s parents?"
                    wire:loading.attr="disabled">
                    ✉ Send to Parent
                </button>
            @else
                <button class="btn-ghost" wire:click="resendInvoice"
                    wire:confirm="Resend this invoice to {{ $invoice->student->full_name }}'s parents? They will receive a fresh copy by email."
                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                    style="font-size:12px;">
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M1 4l7-3 7 3-7 3-7-3z"/><path d="M1 4v8l7 3 7-3V4"/><path d="M8 7v8"/>
                    </svg>
                    Resend
                </button>
            @endif

            @if($invoice->status !== 'paid')
                <button class="btn-primary" wire:click="openPayForm">
                    + Record Payment
                </button>
            @endif

            @if($this->canDelete())
                <button class="btn-danger" wire:click="confirmDelete">
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M2 4h12M5 4V2h6v2M6 7v6M10 7v6M3 4l1 10h8l1-10"/>
                    </svg>
                    Delete Invoice
                </button>
            @endif
        </div>

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

    {{-- LEFT: Line items + payment history --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Fee line items --}}
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">Fee Items</span>
                @if($this->canEdit())
                    <button class="btn-ghost" wire:click="openAddItem">
                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M8 2v12M2 8h12"/></svg>
                        Add Item
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
                            @if($this->canEdit())<th class="right">Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            <tr>
                                <td style="font-weight:500;">{{ $item->item_name }}</td>
                                <td>
                                    @if(is_null($item->fee_item_id))
                                        <span class="badge-custom">Custom</span>
                                    @elseif($item->added_by === 'admin')
                                        <span class="badge-admin">Admin</span>
                                    @else
                                        <span class="badge-system">System</span>
                                    @endif
                                </td>
                                <td class="right mono">₦{{ number_format($item->amount, 0) }}</td>
                                @if($this->canEdit())
                                <td class="right">
                                    <div class="item-actions">
                                        {{-- Edit amount --}}
                                        <button class="btn-icon" wire:click="openEditItem({{ $item->id }})" title="Edit amount">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M11 2l3 3-9 9H2v-3z"/>
                                            </svg>
                                        </button>
                                        {{-- Remove item --}}
                                        <button class="btn-icon btn-icon-danger"
                                            wire:click="removeItem({{ $item->id }})"
                                            wire:confirm="Remove '{{ $item->item_name }}' from this invoice?"
                                            title="Remove item">
                                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path d="M2 4h12M5 4V2h6v2M6 7v6M10 7v6M3 4l1 10h8l1-10"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="{{ $this->canEdit() ? 3 : 2 }}">Total</td>
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
                                <td class="right mono" style="color:#15803D">₦{{ number_format($payment->amount, 0) }}</td>
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

    {{-- RIGHT: Student info + invoice metadata --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">Student</span>
                <a href="{{ route('admin.students.profile', $invoice->student) }}"
                   style="font-size:12px;color:var(--c-accent);text-decoration:none;">View Profile →</a>
            </div>
            <div class="info-row"><span class="info-label">Full Name</span><span class="info-value">{{ $invoice->student->full_name }}</span></div>
            <div class="info-row"><span class="info-label">Admission No.</span><span class="info-value mono">{{ $invoice->student->admission_number }}</span></div>
            @if($enrolment)
            <div class="info-row"><span class="info-label">Class</span><span class="info-value">{{ $enrolment->schoolClass->display_name }}</span></div>
            @endif
            <div class="info-row"><span class="info-label">Gender</span><span class="info-value">{{ $invoice->student->gender }}</span></div>
        </div>

        @if($invoice->student->parents->isNotEmpty())
        <div class="panel">
            <div class="panel-head"><span class="panel-title">Parent / Guardian</span></div>
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

        <div class="panel">
            <div class="panel-head"><span class="panel-title">Invoice Details</span></div>
            <div class="info-row"><span class="info-label">Invoice ID</span><span class="info-value mono">#{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</span></div>
            @if($invoice->isMiscellaneous())
                <div class="info-row"><span class="info-label">Type</span><span class="info-value">Miscellaneous</span></div>
                <div class="info-row"><span class="info-label">Description</span><span class="info-value">{{ $invoice->description }}</span></div>
            @else
                <div class="info-row"><span class="info-label">Term</span><span class="info-value">{{ $invoice->term->name }}</span></div>
                <div class="info-row"><span class="info-label">Session</span><span class="info-value">{{ $invoice->term->session->name }}</span></div>
            @endif
            <div class="info-row"><span class="info-label">Generated</span><span class="info-value">{{ $invoice->created_at->format('d M Y') }}</span></div>
            <div class="info-row">
                <span class="info-label">Sent to Parent</span>
                <span class="info-value">
                    @if($invoice->sent_at)
                        {{ $invoice->sent_at->format('d M Y, g:ia') }}
                    @else
                        <span style="color:var(--c-text-3)">Not sent yet</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="badge badge-{{ $invoice->status }}">
                        <span class="badge-dot"></span>{{ ucfirst($invoice->status) }}
                    </span>
                </span>
            </div>
        </div>

        {{-- ── Virtual Account Status ── --}}
        @php
            $primaryParent = $invoice->student->parents
                ->filter(fn($p) => $p->user !== null)
                ->first();
            $hasAnyAccount = $primaryParent && !empty($primaryParent->active_account_number);
        @endphp
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title">Virtual Account</span>
                @if($hasAnyAccount)
                    <span class="badge badge-sent" style="font-size:10px;">
                        <span class="badge-dot"></span>Active
                    </span>
                @elseif($primaryParent?->isWalletProvisioning())
                    <span style="font-size:11px;color:#B45309;">Provisioning…</span>
                @elseif($primaryParent?->isWalletFailed() && !$hasAnyAccount)
                    <span class="badge badge-unpaid" style="font-size:10px;">
                        <span class="badge-dot"></span>Failed
                    </span>
                @elseif($invoice->isSent())
                    <span style="font-size:11px;color:#B45309;">Pending…</span>
                @else
                    <span style="font-size:11px;color:var(--c-text-3);">Not sent yet</span>
                @endif
            </div>

            <div class="pay-link-box">
                @if($hasAnyAccount)
                    <p style="font-size:12px;color:var(--c-text-2);line-height:1.5;">
                        Parent has a dedicated virtual bank account. Payments sent to
                        this account are automatically matched to their invoices.
                    </p>
                    <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:8px;padding:12px 14px;margin-top:10px;" x-data>
                        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--c-border);font-size:13px;">
                            <span style="color:var(--c-text-3);font-size:12px;">Bank</span>
                            <span style="font-weight:600;">{{ $primaryParent->active_bank_name }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--c-border);font-size:13px;">
                            <span style="color:var(--c-text-3);font-size:12px;">Account Number</span>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-weight:700;font-family:var(--f-mono);">{{ $primaryParent->active_account_number }}</span>
                                <button class="copy-btn"
                                    x-on:click="navigator.clipboard.writeText('{{ $primaryParent->active_account_number }}');
                                                $el.textContent='Copied!';
                                                setTimeout(()=>$el.textContent='Copy',2000)">Copy</button>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:7px 0;font-size:13px;">
                            <span style="color:var(--c-text-3);font-size:12px;">Account Name</span>
                            <span style="font-weight:600;">{{ $invoice->student->full_name }}</span>
                        </div>
                    </div>
                    @if($primaryParent?->isWalletFailed())
                        <p style="font-size:11px;color:#B45309;margin-top:8px;line-height:1.4;">
                            Note: The preferred provider ({{ ucfirst(App\Models\ParentGuardian::getActiveWalletProvider()) }}) provisioning failed. Using fallback account.
                        </p>
                    @endif
                    <p style="font-size:11px;color:var(--c-text-3);margin-top:8px;line-height:1.4;">
                        This is a permanent account — the parent can reuse it for all future payments.
                    </p>

                @elseif($primaryParent?->isWalletFailed())
                    <div class="pay-link-error">
                        <strong>Virtual account provisioning failed.</strong><br>
                        No fallback account available. Check the queue logs.
                    </div>
                    <button class="btn-ghost" style="margin-top:10px;width:100%;justify-content:center;"
                        wire:click="retryProvisionWallet"
                        wire:loading.attr="disabled" wire:loading.class="opacity-50">
                        <span wire:loading.remove>↺ Retry Provisioning</span>
                        <span wire:loading>Queuing…</span>
                    </button>
                @elseif($primaryParent?->isWalletProvisioning())
                    <div class="pay-link-pending">
                        Virtual account is being provisioned in the background.
                        This typically takes 30–60 seconds. Refresh to check.
                    </div>
                @elseif($invoice->isSent())
                    <div class="pay-link-pending">
                        Invoice sent. Virtual account provisioning has been queued
                        and will complete shortly.
                    </div>
                @elseif(! $primaryParent)
                    <p style="font-size:12px;color:var(--c-text-3);line-height:1.5;">
                        No parent portal account found for this student. Approve the
                        enrolment first so the parent receives their portal login.
                    </p>
                @else
                    <p style="font-size:12px;color:var(--c-text-3);line-height:1.5;">
                        A virtual bank account will be provisioned automatically when this
                        invoice is sent to the parent.
                    </p>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════════════════════════════ --}}

{{-- Record payment modal --}}
@if($showPayForm)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Record Payment</div>
        <div class="modal-sub">{{ $invoice->student->full_name }} · Balance: ₦{{ number_format($invoice->balance, 0) }}</div>

        <div class="form-field">
            <label>Amount (₦) <span style="color:var(--c-danger)">*</span></label>
            <input type="text" inputmode="numeric" wire:model="payAmount" placeholder="0"
                x-data
                x-on:focus="$el.value=$el.value.replace(/,/g,'')"
                x-on:blur="let r=$el.value.replace(/,/g,'').replace(/[^0-9]/g,'');$el.value=r?parseInt(r).toLocaleString('en-NG'):''">
            @error('payAmount') <div class="field-error">{{ $message }}</div> @enderror
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
            <button class="btn-confirm" wire:click="recordPayment"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Record Payment</span>
                <span wire:loading>Saving…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Add item modal --}}
@if($showAddItem)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Add Fee Item</div>
        <div class="modal-sub">Choose from your fee catalogue or enter a custom item.</div>

        {{-- Mode toggle --}}
        <div class="mode-tabs">
            <button class="mode-tab {{ $addMode === 'catalogue' ? 'active' : '' }}"
                wire:click="$set('addMode', 'catalogue')">From Catalogue</button>
            <button class="mode-tab {{ $addMode === 'custom' ? 'active' : '' }}"
                wire:click="$set('addMode', 'custom')">Custom Item</button>
        </div>

        @if($addMode === 'catalogue')
            <div class="form-field">
                <label>Fee Item <span style="color:var(--c-danger)">*</span></label>
                <select wire:model.live="addItemId">
                    <option value="">Select a fee item…</option>
                    @foreach($availableItems as $item)
                        <option value="{{ $item->id }}">
                            {{ $item->name }} ({{ ucfirst($item->type) }})
                        </option>
                    @endforeach
                </select>
                @error('addItemId') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        @else
            <div class="form-field">
                <label>Item Name <span style="color:var(--c-danger)">*</span></label>
                <input type="text" wire:model="addCustomName" placeholder="e.g. Late Registration Fee">
                <div class="field-hint">This item will not be added to your fee catalogue.</div>
                @error('addCustomName') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        @endif

        <div class="form-field">
            <label>Amount (₦) <span style="color:var(--c-danger)">*</span></label>
            <input type="number" wire:model="addItemAmount" placeholder="0" min="1">
            @if($addMode === 'catalogue' && $addItemId)
                <div class="field-hint">
                    Auto-filled from fee structure. Edit if needed.
                </div>
            @endif
            @error('addItemAmount') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showAddItem', false)">Cancel</button>
            <button class="btn-confirm" wire:click="addItem"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Add to Invoice</span>
                <span wire:loading>Adding…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Edit item amount modal --}}
@if($showEditItem)
    @php $editingItem = $invoice->items->firstWhere('id', $editingItemId); @endphp
    @if($editingItem)
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-title">Edit Amount</div>
            <div class="modal-sub">{{ $editingItem->item_name }}</div>

            <div class="form-field">
                <label>Amount (₦) <span style="color:var(--c-danger)">*</span></label>
                <input type="number" wire:model="editingItemAmount" min="1" autofocus>
                @error('editingItemAmount') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('showEditItem', false)">Cancel</button>
                <button class="btn-confirm" wire:click="saveItemAmount"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50">
                    <span wire:loading.remove>Save Amount</span>
                    <span wire:loading>Saving…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
@endif

{{-- Delete invoice confirm --}}
@if($showDeleteConfirm)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Delete Invoice?</div>
        <div class="modal-sub">
            This will permanently delete the invoice for
            <strong>{{ $invoice->student->full_name }}</strong>
            ({{ $invoice->isMiscellaneous() ? $invoice->description : $invoice->term->name.' Term — '.$invoice->term->session->name }}).
            <br><br>
            This action cannot be undone. The student's fee structure assignment remains intact
            and a new invoice can be regenerated from the invoices list if needed.
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showDeleteConfirm', false)">Cancel</button>
            <button class="btn-confirm-danger" wire:click="deleteInvoice"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Delete Invoice</span>
                <span wire:loading>Deleting…</span>
            </button>
        </div>
    </div>
</div>
@endif

</div>
