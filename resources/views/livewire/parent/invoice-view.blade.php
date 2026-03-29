<div>
<style>
.back-link { display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--c-text-3); text-decoration:none; margin-bottom:16px; }
.back-link:hover { color:var(--c-text-1); }

.inv-header { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:18px; margin-bottom:14px; }
.inv-student { font-size:17px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; }
.inv-meta    { font-size:12px; color:var(--c-text-3); margin-top:3px; }

.amounts-row { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
.amount-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:12px 14px; }
.amount-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; }
.amount-value { font-size:18px; font-weight:700; margin-top:4px; font-family:var(--f-mono); }

.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:500; margin-top:8px; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-unpaid  { background:rgba(190,18,60,0.08);  color:var(--c-danger); }
.badge-partial { background:rgba(180,83,9,0.08);   color:var(--c-warning); }
.badge-paid    { background:rgba(21,128,61,0.08);   color:var(--c-success); }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; margin-bottom:12px; }
.panel-head { padding:12px 16px; border-bottom:1px solid var(--c-border); font-size:12px; font-weight:600; color:var(--c-text-1); display:flex; justify-content:space-between; align-items:center; }

.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; padding:9px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table th.right, .data-table td.right { text-align:right; }
.data-table td { padding:12px 16px; font-size:13px; border-bottom:1px solid var(--c-border); }
.data-table tr:last-child td { border-bottom:none; }
.total-row td { font-weight:700; background:var(--c-bg) !important; }
.mono { font-family:var(--f-mono); font-size:12px; }

.btn-pdf {
    display:inline-flex; align-items:center; gap:6px;
    padding:10px 18px; background:var(--c-accent); color:#fff;
    border-radius:8px; font-size:13px; font-weight:500; text-decoration:none;
    transition:opacity 150ms; width:100%; justify-content:center; margin-top:4px;
}
.btn-pdf:hover { opacity:0.9; }

.empty-cell { padding:20px 16px; text-align:center; font-size:13px; color:var(--c-text-3); }
</style>

<a href="{{ route('parent.fees') }}" class="back-link">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M10 3L5 8l5 5"/>
    </svg>
    All Invoices
</a>

{{-- Header --}}
<div class="inv-header">
    <div class="inv-student">{{ $invoice->student->full_name }}</div>
    <div class="inv-meta">{{ $invoice->term->name }} Term — {{ $invoice->term->session->name }}</div>
    <div>
        <span class="badge badge-{{ $invoice->status }}">
            <span class="badge-dot"></span>
            {{ ucfirst($invoice->status) }}
        </span>
    </div>
</div>

{{-- Amounts --}}
<div class="amounts-row">
    <div class="amount-card">
        <div class="amount-label">Total</div>
        <div class="amount-value">₦{{ number_format($invoice->total_amount, 0) }}</div>
    </div>
    <div class="amount-card">
        <div class="amount-label">Paid</div>
        <div class="amount-value" style="color:var(--c-success)">₦{{ number_format($invoice->amount_paid, 0) }}</div>
    </div>
    <div class="amount-card">
        <div class="amount-label">Balance</div>
        <div class="amount-value" style="color:{{ $invoice->balance > 0 ? 'var(--c-danger)' : 'var(--c-success)' }}">
            ₦{{ number_format($invoice->balance, 0) }}
        </div>
    </div>
</div>

{{-- Fee breakdown --}}
<div class="panel">
    <div class="panel-head">Fee Breakdown</div>
    @if($invoice->items->isEmpty())
        <div class="empty-cell">No line items.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td class="right mono">₦{{ number_format($item->amount, 0) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td>Total</td>
                    <td class="right mono">₦{{ number_format($invoice->total_amount, 0) }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>

{{-- Payment history --}}
@if($invoice->payments->isNotEmpty())
<div class="panel">
    <div class="panel-head">Payment History</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->payments->sortByDesc('paid_at') as $payment)
                <tr>
                    <td>{{ $payment->paid_at->format('d M Y') }}</td>
                    <td>{{ $payment->method }}</td>
                    <td class="right mono" style="color:var(--c-success)">₦{{ number_format($payment->amount, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Download PDF --}}
<a href="{{ route('admin.fees.invoices.pdf', $invoice) }}" target="_blank" class="btn-pdf">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M4 1h6l4 4v10H2V1z"/><path d="M10 1v4h4"/>
    </svg>
    Download Invoice PDF
</a>
</div>
