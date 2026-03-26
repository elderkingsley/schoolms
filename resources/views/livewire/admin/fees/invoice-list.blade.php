<div>
<style>
/* ── Page header ─────────────────────────────────────────────────────── */
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }

/* ── Flash / feedback ───────────────────────────────────────────────── */
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-info    { background:rgba(26,86,255,0.06);  border:1px solid rgba(26,86,255,0.15); color:var(--c-accent); }

/* ── Stat cards ─────────────────────────────────────────────────────── */
.stats-row { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:20px; }
@media(min-width:640px){ .stats-row { grid-template-columns:repeat(3,1fr); } }
@media(min-width:1024px){ .stats-row { grid-template-columns:repeat(6,1fr); } }
.stat-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:14px 16px; }
.stat-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; }
.stat-value { font-size:20px; font-weight:700; color:var(--c-text-1); margin-top:4px; letter-spacing:-0.03em; }
.stat-value.accent { color:var(--c-accent); }
.stat-value.success { color:var(--c-success); }
.stat-value.warning { color:var(--c-warning); }
.stat-value.danger  { color:var(--c-danger); }

/* ── Toolbar ─────────────────────────────────────────────────────────── */
.toolbar { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center; }
.toolbar-left { display:flex; gap:10px; flex:1; flex-wrap:wrap; }

.search-wrap { position:relative; flex:1; min-width:180px; max-width:320px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:13px; color:var(--c-text-1); background:var(--c-surface); outline:none; transition:border-color 150ms; }
.search-wrap input:focus { border-color:var(--c-accent); box-shadow:0 0 0 3px rgba(26,86,255,0.08); }

.filter-select { padding:9px 12px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:13px; color:var(--c-text-1); background:var(--c-surface); outline:none; cursor:pointer; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:30px; }
.filter-select:focus { border-color:var(--c-accent); }

/* ── Buttons ─────────────────────────────────────────────────────────── */
.btn-primary { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; white-space:nowrap; }
.btn-primary:hover { opacity:0.9; }
.btn-primary:disabled { opacity:0.5; cursor:not-allowed; }

/* ── Table panel ─────────────────────────────────────────────────────── */
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.panel-count { font-size:12px; color:var(--c-text-3); }

.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 20px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table td { padding:14px 20px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }

.student-name { font-weight:600; color:var(--c-text-1); }
.student-meta { font-size:11px; color:var(--c-text-3); margin-top:2px; }
.amount-cell  { font-family:var(--f-mono); font-size:12px; }

/* ── Status badges ───────────────────────────────────────────────────── */
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-unpaid  { background:rgba(190,18,60,0.08);  color:var(--c-danger); }
.badge-partial { background:rgba(180,83,9,0.08);   color:var(--c-warning); }
.badge-paid    { background:rgba(21,128,61,0.08);   color:var(--c-success); }

/* ── Action link ─────────────────────────────────────────────────────── */
.link-view { font-size:12px; font-weight:500; color:var(--c-accent); text-decoration:none; }
.link-view:hover { text-decoration:underline; }

/* ── Empty state ─────────────────────────────────────────────────────── */
.empty-state { text-align:center; padding:60px 20px; }
.empty-icon  { color:var(--c-border); margin-bottom:12px; }
.empty-title { font-size:15px; font-weight:600; color:var(--c-text-2); }
.empty-desc  { font-size:13px; color:var(--c-text-3); margin-top:4px; }

/* ── Pagination ─────────────────────────────────────────────────────── */
.pag-wrap { padding:16px 20px; border-top:1px solid var(--c-border); }

/* ── Confirm modal ───────────────────────────────────────────────────── */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:440px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:8px; }
.modal-body  { font-size:13px; color:var(--c-text-2); line-height:1.6; margin-bottom:24px; }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; }
.btn-cancel { padding:9px 16px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); color:var(--c-text-2); transition:background 150ms; }
.btn-cancel:hover { background:var(--c-bg); }
.btn-confirm { padding:9px 16px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-confirm:hover { opacity:0.9; }

/* ── Mobile: hide less important columns ────────────────────────────── */
@media(max-width:640px) {
    .col-balance, .col-term { display:none; }
}
</style>

{{-- ── Page header ──────────────────────────────────────────────────────── --}}
<div class="pg-header">
    <div>
        <div class="pg-title">Fee Invoices</div>
        <div class="pg-sub">Generate and manage student fee invoices by term</div>
    </div>
    <button wire:click="confirmGenerate"
            @if(!$selectedTermId) disabled @endif
            class="btn-primary">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 3v10M3 8h10"/>
        </svg>
        Generate Invoices
    </button>
</div>

{{-- ── Generation feedback ──────────────────────────────────────────────── --}}
@if($generationMessage)
    <div class="flash flash-{{ str_starts_with($generationMessage, '✓') ? 'success' : 'info' }}">
        {{ $generationMessage }}
    </div>
@endif

{{-- ── Stat cards ───────────────────────────────────────────────────────── --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-label">Total Invoices</div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Unpaid</div>
        <div class="stat-value danger">{{ number_format($stats['unpaid']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Part-Paid</div>
        <div class="stat-value warning">{{ number_format($stats['partial']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Fully Paid</div>
        <div class="stat-value success">{{ number_format($stats['paid']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Revenue Collected</div>
        <div class="stat-value accent" style="font-size:15px;">₦{{ number_format($stats['revenue'], 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Outstanding</div>
        <div class="stat-value danger" style="font-size:15px;">₦{{ number_format($stats['outstanding'], 0) }}</div>
    </div>
</div>

{{-- ── Toolbar ──────────────────────────────────────────────────────────── --}}
<div class="toolbar">
    <div class="toolbar-left">
        {{-- Term selector --}}
        <select wire:model.live="selectedTermId" class="filter-select">
            <option value="">All Terms</option>
            @foreach($terms as $term)
                <option value="{{ $term->id }}">
                    {{ $term->name }} — {{ $term->session->name }}
                    @if($term->is_active) (Current) @endif
                </option>
            @endforeach
        </select>

        {{-- Status filter --}}
        <select wire:model.live="filterStatus" class="filter-select">
            <option value="">All Statuses</option>
            <option value="unpaid">Unpaid</option>
            <option value="partial">Part-Paid</option>
            <option value="paid">Paid</option>
        </select>

        {{-- Search --}}
        <div class="search-wrap">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
            </svg>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search student name or number…">
        </div>
    </div>
</div>

{{-- ── Invoice table ─────────────────────────────────────────────────────── --}}
<div class="panel">
    <div class="panel-head">
        <span class="panel-title">Invoices</span>
        <span class="panel-count">{{ $invoices->total() }} {{ Str::plural('record', $invoices->total()) }}</span>
    </div>

    @if($invoices->isEmpty())
        <div class="empty-state">
            <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <rect x="4" y="2" width="16" height="20" rx="2"/>
                <path d="M8 7h8M8 11h8M8 15h5"/>
            </svg>
            <div class="empty-title">No invoices found</div>
            <div class="empty-desc">
                @if($selectedTermId)
                    Click "Generate Invoices" to create invoices for the selected term.
                @else
                    Select a term or generate invoices to get started.
                @endif
            </div>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th class="col-term">Term</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th class="col-balance">Balance</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td>
                            <div class="student-name">{{ $invoice->student->full_name }}</div>
                            <div class="student-meta">{{ $invoice->student->admission_number }}</div>
                        </td>
                        <td class="col-term">
                            <div style="font-size:12px; color:var(--c-text-2);">{{ $invoice->term->name }}</div>
                            <div style="font-size:11px; color:var(--c-text-3);">{{ $invoice->term->session->name }}</div>
                        </td>
                        <td class="amount-cell">₦{{ number_format($invoice->total_amount, 0) }}</td>
                        <td class="amount-cell" style="color:var(--c-success);">₦{{ number_format($invoice->amount_paid, 0) }}</td>
                        <td class="amount-cell col-balance" style="color:var(--c-danger);">₦{{ number_format($invoice->balance, 0) }}</td>
                        <td>
                            <span class="badge badge-{{ $invoice->status }}">
                                <span class="badge-dot"></span>
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            {{-- View link — will become route('admin.fees.invoices.show', $invoice) once InvoiceDetail is built --}}
                            <a href="#" class="link-view">View →</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($invoices->hasPages())
            <div class="pag-wrap">
                {{ $invoices->links() }}
            </div>
        @endif
    @endif
</div>

{{-- ── Confirm generation modal ─────────────────────────────────────────── --}}
@if($showConfirmModal)
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-title">Generate Invoices for This Term?</div>
            <div class="modal-body">
                This will create fee invoices for all <strong>active students</strong> enrolled in the selected term,
                based on the configured fee structure. An email will be sent to each parent with a portal account.<br><br>
                Students who already have an invoice for this term will <strong>not</strong> be affected.
            </div>
            <div class="modal-actions">
                <button wire:click="cancelGenerate" class="btn-cancel">Cancel</button>
                <button wire:click="generateInvoices" class="btn-confirm">
                    <span wire:loading.remove wire:target="generateInvoices">Yes, Generate</span>
                    <span wire:loading wire:target="generateInvoices">Generating…</span>
                </button>
            </div>
        </div>
    </div>
@endif

</div>
