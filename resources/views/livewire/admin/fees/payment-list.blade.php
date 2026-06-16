<div>
<style>
.pay-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:18px; flex-wrap:wrap; }
.pay-title { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; }
.pay-sub { font-size:13px; color:var(--c-text-3); margin-top:3px; }
.summary-grid { display:grid; grid-template-columns:1fr; gap:12px; margin-bottom:16px; }
@media(min-width:640px) { .summary-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
.summary-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:15px 16px; }
.summary-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; }
.summary-value { font-size:22px; font-weight:700; margin-top:5px; font-family:var(--f-mono); letter-spacing:-0.02em; }
.summary-value.success { color:#15803D; }
.filters { display:grid; grid-template-columns:1fr; gap:8px; margin-bottom:14px; }
@media(min-width:760px) { .filters { grid-template-columns:minmax(220px, 1.5fr) repeat(4, minmax(120px, 1fr)) auto; align-items:center; } }
.search-wrap { position:relative; min-width:0; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.filter-input, .filter-sel { width:100%; min-height:38px; padding:9px 10px; border:1px solid var(--c-border); border-radius:8px; font-size:12px; font-family:var(--f-sans); background:var(--c-surface); color:var(--c-text-1); outline:none; }
.search-wrap .filter-input { padding-left:34px; font-size:13px; }
.filter-input:focus, .filter-sel:focus { border-color:var(--c-accent); box-shadow:0 0 0 3px var(--c-accent-bg); }
.filter-sel { -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 8px center; padding-right:24px; }
.btn-clear { min-height:38px; padding:8px 13px; border:1px solid var(--c-border); border-radius:8px; background:var(--c-surface); color:var(--c-text-2); font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); white-space:nowrap; }
.btn-clear:hover { background:var(--c-bg); }
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.table-wrap { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; min-width:850px; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.07em; padding:10px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); white-space:nowrap; }
.data-table th.right, .data-table td.right { text-align:right; }
.data-table td { padding:13px 16px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tbody tr { cursor:pointer; transition:background 150ms; }
.data-table tbody tr:hover td { background:#fafaf8; }
.student-name { font-weight:600; color:var(--c-text-1); }
.student-adm, .muted { color:var(--c-text-3); }
.student-adm, .mono { font-family:var(--f-mono); font-size:11px; }
.amount { font-family:var(--f-mono); font-size:13px; font-weight:700; color:#15803D; }
.badge { display:inline-flex; align-items:center; padding:4px 8px; border-radius:20px; background:rgba(21,128,61,0.08); color:#15803D; font-size:11px; font-weight:500; white-space:nowrap; }
.invoice-link { display:inline-flex; align-items:center; gap:5px; color:var(--c-accent); text-decoration:none; font-size:12px; font-weight:600; white-space:nowrap; }
.invoice-link:hover { text-decoration:underline; }
.empty-state { padding:42px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.pag-wrap { padding:14px 16px; border-top:1px solid var(--c-border); }
</style>

<div class="pay-head">
    <div>
        <div class="pay-title">Payments</div>
        <div class="pay-sub">Newest received payments appear first.</div>
    </div>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-label">Filtered Received</div>
        <div class="summary-value success">₦{{ number_format((float) $summary->total_amount, 0) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Payments</div>
        <div class="summary-value">{{ number_format((int) $summary->payments_count) }}</div>
    </div>
</div>

<div class="filters">
    <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
        </svg>
        <input class="filter-input" type="text" wire:model.live.debounce.300ms="search" placeholder="Search student, receipt, reference">
    </div>

    <select wire:model.live="filterTerm" class="filter-sel" aria-label="Filter by term">
        <option value="">All Terms</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
        @endforeach
    </select>

    <select wire:model.live="filterMethod" class="filter-sel" aria-label="Filter by method">
        <option value="">All Methods</option>
        @foreach($methods as $method)
            <option value="{{ $method }}">{{ $method }}</option>
        @endforeach
    </select>

    <input class="filter-input" type="date" wire:model.live="dateFrom" aria-label="Payments from date">
    <input class="filter-input" type="date" wire:model.live="dateTo" aria-label="Payments to date">

    @if($search || $filterTerm || $filterMethod || $dateFrom || $dateTo)
        <button type="button" class="btn-clear" wire:click="clearFilters">Clear</button>
    @endif
</div>

<div class="panel">
    @if($payments->isEmpty())
        <div class="empty-state">No payments found.</div>
    @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Invoice</th>
                        <th>Method</th>
                        <th>Receipt</th>
                        <th>Reference</th>
                        <th>Recorded By</th>
                        <th class="right">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        @php
                            $invoice = $payment->invoice;
                            $invoiceUrl = $invoice ? route('admin.fees.invoices.show', $invoice) : null;
                        @endphp
                        <tr @if($invoiceUrl) onclick="window.location='{{ $invoiceUrl }}'" @endif>
                            <td>
                                <div class="mono">{{ $payment->paid_at?->format('d M Y') }}</div>
                                <div class="muted" style="font-size:11px;">{{ $payment->paid_at?->format('g:ia') }}</div>
                            </td>
                            <td>
                                <div class="student-name">{{ $invoice?->student?->full_name ?? 'Unknown student' }}</div>
                                <div class="student-adm">{{ $invoice?->student?->admission_number ?? '—' }}</div>
                            </td>
                            <td>
                                @if($invoice)
                                    <div class="mono">#{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    <div class="muted" style="font-size:11px;">
                                        @if($invoice->isMiscellaneous())
                                            {{ $invoice->description ?? 'Miscellaneous Invoice' }}
                                        @else
                                            {{ $invoice->term?->name }} · {{ $invoice->term?->session?->name }}
                                        @endif
                                    </div>
                                @else
                                    <span class="muted">Missing invoice</span>
                                @endif
                            </td>
                            <td><span class="badge">{{ $payment->method }}</span></td>
                            <td class="mono">{{ $payment->receipt_number ?: '—' }}</td>
                            <td class="mono muted">{{ $payment->reference ?: '—' }}</td>
                            <td>{{ $payment->recordedBy?->name ?? 'System' }}</td>
                            <td class="right amount">₦{{ number_format($payment->amount, 0) }}</td>
                            <td onclick="event.stopPropagation()">
                                @if($invoice)
                                    <a class="invoice-link" href="{{ $invoiceUrl }}">
                                        Invoice
                                        <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M6 3l5 5-5 5"/>
                                        </svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="pag-wrap">{{ $payments->links() }}</div>
        @endif
    @endif
</div>
</div>
