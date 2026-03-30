<div>
<style>
.welcome-card { background:linear-gradient(135deg,#15803D 0%,#0d5f2e 100%); border-radius:var(--r-lg); padding:20px; color:#fff; margin-bottom:20px; }
.welcome-title { font-size:18px; font-weight:700; letter-spacing:-0.02em; }
.welcome-sub   { font-size:13px; opacity:0.7; margin-top:3px; }
.welcome-term  { font-size:11px; opacity:0.6; margin-top:8px; }

.stats-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px; }
@media(min-width:600px) { .stats-grid { grid-template-columns:repeat(3,1fr); } }

.stat-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:16px; }
.stat-label { font-size:11px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; }
.stat-value { font-size:22px; font-weight:700; letter-spacing:-0.03em; margin-top:6px; font-family:var(--f-mono); }
.stat-value.success { color:#15803D; }
.stat-value.danger  { color:var(--c-danger); }
.stat-value.neutral { color:var(--c-text-1); }

.section-title { font-size:12px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px; }

.status-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:20px; }
.status-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:12px; text-align:center; }
.status-card-val { font-size:24px; font-weight:700; }
.status-card-lbl { font-size:10px; color:var(--c-text-3); margin-top:3px; }
.s-paid    { color:#15803D; }
.s-partial { color:#B45309; }
.s-unpaid  { color:var(--c-danger); }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { padding:12px 16px; border-bottom:1px solid var(--c-border); font-size:13px; font-weight:600; color:var(--c-text-1); }
.data-table { width:100%; border-collapse:collapse; }
.data-table td { padding:11px 16px; font-size:13px; border-bottom:1px solid var(--c-border); }
.data-table tr:last-child td { border-bottom:none; }
.mono { font-family:var(--f-mono); font-size:12px; }
.student-name { font-weight:500; }
.amount-val { font-weight:700; color:#15803D; }

.cta { display:block; text-align:center; padding:10px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; text-decoration:none; margin-top:16px; transition:opacity 150ms; }
.cta:hover { opacity:0.9; }
</style>

<div class="welcome-card">
    <div class="welcome-title">Finance Dashboard</div>
    <div class="welcome-sub">{{ auth()->user()->name }}</div>
    @if($activeTerm)
        <div class="welcome-term">{{ $activeTerm->name }} Term — {{ $activeTerm->session->name }}</div>
    @endif
</div>

{{-- Financial summary --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Billed</div>
        <div class="stat-value neutral">₦{{ number_format($stats['total_invoiced'], 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Collected</div>
        <div class="stat-value success">₦{{ number_format($stats['total_collected'], 0) }}</div>
    </div>
    <div class="stat-card" style="grid-column:span 2;" @media(min-width:600px){grid-column:span 1}>
        <div class="stat-label">Outstanding</div>
        <div class="stat-value danger">₦{{ number_format($stats['total_outstanding'], 0) }}</div>
    </div>
</div>

{{-- Invoice status breakdown --}}
<div class="section-title">Invoice Status — This Term</div>
<div class="status-grid">
    <div class="status-card">
        <div class="status-card-val s-paid">{{ $stats['paid_count'] }}</div>
        <div class="status-card-lbl">Fully Paid</div>
    </div>
    <div class="status-card">
        <div class="status-card-val s-partial">{{ $stats['partial_count'] }}</div>
        <div class="status-card-lbl">Part Paid</div>
    </div>
    <div class="status-card">
        <div class="status-card-val s-unpaid">{{ $stats['unpaid_count'] }}</div>
        <div class="status-card-lbl">Unpaid</div>
    </div>
</div>

{{-- Recent payments --}}
@if($recentPayments->isNotEmpty())
    <div class="section-title">Recent Payments</div>
    <div class="panel">
        <div class="panel-head">Last {{ $recentPayments->count() }} payments</div>
        <table class="data-table">
            @foreach($recentPayments as $payment)
                <tr>
                    <td>
                        <div class="student-name">{{ $payment->invoice->student->full_name }}</div>
                        <div class="mono" style="font-size:10px;color:var(--c-text-3);">{{ $payment->paid_at->format('d M Y') }}</div>
                    </td>
                    <td style="text-align:right">
                        <span class="amount-val">₦{{ number_format($payment->amount, 0) }}</span>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endif

<a href="{{ route('accountant.invoices') }}" class="cta">View All Invoices →</a>
</div>
