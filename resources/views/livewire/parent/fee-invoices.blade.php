<div>
<style>
.pg-title { font-size:18px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:16px; }

/* Summary strip */
.summary-strip { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
.summary-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:12px 14px; }
.summary-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; }
.summary-value { font-size:16px; font-weight:700; margin-top:4px; letter-spacing:-0.02em; }
.summary-value.danger  { color:var(--c-danger); }
.summary-value.success { color:var(--c-success); }

/* Filters */
.filters { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
.filter-select {
    padding:8px 10px; border:1px solid var(--c-border); border-radius:8px;
    font-family:var(--f-sans); font-size:12px; color:var(--c-text-1);
    background:var(--c-surface); outline:none; -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 8px center; padding-right:26px;
    flex:1; min-width:120px;
}
.filter-select:focus { border-color:var(--c-accent); }

/* Invoice cards */
.invoice-card {
    background:var(--c-surface); border:1px solid var(--c-border);
    border-radius:var(--r-md); margin-bottom:10px;
    overflow:hidden;
}
.invoice-card-head {
    padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;
    border-bottom:1px solid var(--c-border);
}
.invoice-student { font-size:15px; font-weight:700; color:var(--c-text-1); }
.invoice-term    { font-size:12px; color:var(--c-text-3); margin-top:2px; }

.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-unpaid  { background:rgba(190,18,60,0.08);  color:var(--c-danger); }
.badge-partial { background:rgba(180,83,9,0.08);   color:var(--c-warning); }
.badge-paid    { background:rgba(21,128,61,0.08);   color:var(--c-success); }

.invoice-amounts { display:grid; grid-template-columns:repeat(3,1fr); }
.amount-cell { padding:12px 16px; border-right:1px solid var(--c-border); }
.amount-cell:last-child { border-right:none; }
.amount-label { font-size:10px; color:var(--c-text-3); font-weight:600; text-transform:uppercase; letter-spacing:0.05em; }
.amount-value { font-size:14px; font-weight:700; margin-top:3px; font-family:var(--f-mono); }

.invoice-actions { padding:12px 16px; border-top:1px solid var(--c-border); display:flex; gap:8px; }
.btn-link {
    padding:7px 14px; border:1px solid var(--c-border); border-radius:8px;
    font-size:12px; font-weight:500; font-family:var(--f-sans);
    background:none; color:var(--c-text-2); text-decoration:none;
    display:inline-flex; align-items:center; gap:5px;
    transition:background 150ms;
}
.btn-link:hover { background:var(--c-bg); }
.btn-primary-sm {
    padding:7px 14px; background:var(--c-accent); color:#fff; border:none;
    border-radius:8px; font-size:12px; font-weight:500; font-family:var(--f-sans);
    text-decoration:none; display:inline-flex; align-items:center; gap:5px;
    transition:opacity 150ms;
}
.btn-primary-sm:hover { opacity:0.9; }

.empty-state { text-align:center; padding:40px 20px; color:var(--c-text-3); font-size:13px; }
</style>

<div class="pg-title">Fee Invoices</div>

{{-- Summary strip --}}
<div class="summary-strip">
    <div class="summary-card">
        <div class="summary-label">Total Billed</div>
        <div class="summary-value">₦{{ number_format($totals['total'], 0) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Paid</div>
        <div class="summary-value success">₦{{ number_format($totals['paid'], 0) }}</div>
    </div>
    <div class="summary-card">
        <div class="summary-label">Outstanding</div>
        <div class="summary-value danger">₦{{ number_format($totals['outstanding'], 0) }}</div>
    </div>
</div>

{{-- Filters --}}
<div class="filters">
    <select wire:model.live="filterChild" class="filter-select">
        <option value="">All Children</option>
        @foreach($children as $child)
            <option value="{{ $child->id }}">{{ $child->full_name }}</option>
        @endforeach
    </select>
    <select wire:model.live="filterTerm" class="filter-select">
        <option value="">All Terms</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
        @endforeach
    </select>
    <select wire:model.live="filterStatus" class="filter-select">
        <option value="">All Statuses</option>
        <option value="unpaid">Unpaid</option>
        <option value="partial">Part Paid</option>
        <option value="paid">Paid</option>
    </select>
</div>

{{-- Invoice cards --}}
@if($invoices->isEmpty())
    <div class="empty-state">No invoices found for the selected filters.</div>
@else
    @foreach($invoices as $invoice)
        <div class="invoice-card">
            <div class="invoice-card-head">
                <div>
                    <div class="invoice-student">{{ $invoice->student->full_name }}</div>
                    <div class="invoice-term">{{ $invoice->isMiscellaneous() ? $invoice->description : $invoice->term->name.' Term — '.$invoice->term->session->name }}</div>
                </div>
                <span class="badge badge-{{ $invoice->status }}">
                    <span class="badge-dot"></span>
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
            <div class="invoice-amounts">
                <div class="amount-cell">
                    <div class="amount-label">Total</div>
                    <div class="amount-value">₦{{ number_format($invoice->total_amount, 0) }}</div>
                </div>
                <div class="amount-cell">
                    <div class="amount-label">Paid</div>
                    <div class="amount-value" style="color:var(--c-success)">₦{{ number_format($invoice->amount_paid, 0) }}</div>
                </div>
                <div class="amount-cell">
                    <div class="amount-label">Balance</div>
                    <div class="amount-value" style="color:{{ $invoice->balance > 0 ? 'var(--c-danger)' : 'var(--c-success)' }}">
                        ₦{{ number_format($invoice->balance, 0) }}
                    </div>
                </div>
            </div>
            <div class="invoice-actions">
                <a href="{{ route('parent.fees.show', $invoice) }}" class="btn-link">
                    View Details
                </a>
                <a href="{{ route('parent.fees.pdf', $invoice) }}" target="_blank" class="btn-link">
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 1h6l4 4v10H2V1z"/><path d="M10 1v4h4"/>
                    </svg>
                    PDF
                </a>
            </div>
        </div>
    @endforeach
@endif
</div>
