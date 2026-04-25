<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-info    { background:rgba(26,86,255,0.06); border:1px solid rgba(26,86,255,0.15); color:var(--c-accent); }

/* Stats bar */
.stats-bar { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:18px; }
@media(min-width:640px) { .stats-bar { grid-template-columns:repeat(4,1fr); } }
.stat-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:12px 14px; cursor:pointer; transition:border-color 150ms; }
.stat-card.active { border-color:var(--c-accent); background:var(--c-accent-bg); }
.stat-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; }
.stat-value { font-size:20px; font-weight:700; margin-top:4px; letter-spacing:-0.02em; font-family:var(--f-mono); }

/* Tabs */
.tabs { display:flex; gap:2px; background:var(--c-bg); border:1px solid var(--c-border); border-radius:9px; padding:3px; margin-bottom:16px; width:fit-content; }
.tab-btn { padding:7px 16px; border-radius:6px; border:none; background:none; font-size:13px; font-weight:500; color:var(--c-text-3); cursor:pointer; font-family:var(--f-sans); transition:all 150ms; }
.tab-btn.active { background:var(--c-surface); color:var(--c-text-1); box-shadow:0 1px 3px rgba(0,0,0,0.08); }
.tab-count { display:inline-flex; align-items:center; justify-content:center; min-width:18px; height:18px; padding:0 5px; border-radius:9px; font-size:10px; font-weight:700; margin-left:5px; background:var(--c-border); color:var(--c-text-2); }
.tab-btn.active .tab-count { background:var(--c-accent); color:#fff; }

/* Toolbar */
.toolbar { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; align-items:center; }
.search-wrap { position:relative; flex:1; min-width:180px; max-width:280px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input { width:100%; padding:8px 12px 8px 34px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-family:var(--f-sans); background:var(--c-surface); outline:none; color:var(--c-text-1); }
.search-wrap input:focus { border-color:var(--c-accent); }
.sel { padding:8px 10px; border:1px solid var(--c-border); border-radius:8px; font-size:12px; font-family:var(--f-sans); background:var(--c-surface); outline:none; -webkit-appearance:none; color:var(--c-text-1); background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 8px center; padding-right:24px; }

/* Bulk action bar */
.bulk-bar { background:var(--c-accent-bg); border:1px solid rgba(26,86,255,0.2); border-radius:8px; padding:10px 16px; margin-bottom:12px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.bulk-count { font-size:13px; font-weight:600; color:var(--c-accent); }
.btn-bulk { padding:6px 14px; border-radius:6px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); border:1px solid rgba(26,86,255,0.25); background:var(--c-surface); color:var(--c-accent); transition:background 150ms; }
.btn-bulk:hover { background:rgba(26,86,255,0.08); }
.btn-bulk-green { background:#15803D; color:#fff; border-color:#15803D; }
.btn-bulk-green:hover { opacity:0.9; }
.btn-bulk-red { background:var(--c-danger); color:#fff; border-color:var(--c-danger); }
.btn-bulk-red:hover { opacity:0.9; }

/* Table */
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); flex-wrap:wrap; gap:8px; }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.07em; padding:9px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); white-space:nowrap; }
.data-table td { padding:12px 16px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
/* Clickable rows */
.data-table tbody tr { cursor:pointer; }
.data-table tbody tr td.no-click { cursor:default; }
.student-name { font-weight:600; }
.student-adm  { font-family:var(--f-mono); font-size:10px; color:var(--c-text-3); }
.mono { font-family:var(--f-mono); font-size:12px; }

/* Badges */
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-unpaid  { background:rgba(190,18,60,0.08); color:var(--c-danger); }
.badge-partial { background:rgba(180,83,9,0.08);  color:#B45309; }
.badge-paid    { background:rgba(21,128,61,0.08);  color:#15803D; }
.badge-draft   { background:rgba(100,100,100,0.08); color:#666; }
.badge-sent    { background:rgba(26,86,255,0.08); color:var(--c-accent); }

/* Row actions */
.row-actions { display:flex; align-items:center; gap:5px; }
.btn-sm { padding:4px 9px; border-radius:6px; font-size:11px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); white-space:nowrap; }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-send { background:#15803D; color:#fff; border-color:#15803D; }
.btn-sm-send:hover { opacity:0.9; }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }
.btn-sm-link { color:var(--c-accent); text-decoration:none; padding:4px 9px; border-radius:6px; font-size:11px; font-weight:500; border:1px solid var(--c-border); }
.btn-sm-link:hover { background:var(--c-bg); }

/* Action buttons top */
.btn-generate { padding:9px 16px; background:var(--c-accent); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-generate:hover { opacity:0.9; }
.btn-secondary { padding:9px 16px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); }
.btn-secondary:hover { background:var(--c-bg); }
.btn-send-all  { padding:9px 16px; background:#15803D; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-send-all:hover { opacity:0.9; }
.btn-delete-all { padding:9px 16px; background:none; border:1px solid rgba(190,18,60,0.3); color:var(--c-danger); border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:background 150ms; }
.btn-delete-all:hover { background:rgba(190,18,60,0.06); }

/* Checkbox */
input[type=checkbox].row-check { width:16px; height:16px; accent-color:var(--c-accent); cursor:pointer; }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:440px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); margin-bottom:8px; }
.modal-sub   { font-size:13px; color:var(--c-text-2); margin-bottom:20px; line-height:1.5; }
.form-field { margin-bottom:16px; }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:5px; }
.form-field input { width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px; font-size:14px; font-family:var(--f-sans); background:var(--c-bg); outline:none; color:var(--c-text-1); }
.form-field input:focus { border-color:var(--c-accent); background:#fff; }
.field-hint  { font-size:11px; color:var(--c-text-3); margin-top:4px; }
.field-error { font-size:11px; color:var(--c-danger); margin-top:4px; }
.modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
.btn-cancel  { padding:9px 16px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm { padding:9px 20px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm-green  { padding:9px 20px; background:#15803D; color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm-danger { padding:9px 20px; background:var(--c-danger); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }

.empty-state { padding:48px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.pag-wrap { padding:14px 20px; border-top:1px solid var(--c-border); }
@media(max-width:640px) { .hide-sm { display:none; } }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if($generationMessage)
    <div class="flash flash-info">{{ $generationMessage }}</div>
@endif

{{-- Page header --}}
<div class="pg-header">
    <div>
        <div class="pg-title">Fee Invoices</div>
        <div class="pg-sub">Generate drafts, edit items, then send to parents individually or in bulk.</div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @if($stats['draft'] > 0)
            <button class="btn-secondary" wire:click="openSendModal">
                Send {{ $stats['draft'] }} Draft{{ $stats['draft'] > 1 ? 's' : '' }}…
            </button>
            <button class="btn-send-all" wire:click="sendAllDrafts"
                wire:confirm="Send ALL {{ $stats['draft'] }} draft invoices to parents now?">
                Send All Drafts
            </button>
        @endif
        <button class="btn-delete-all" wire:click="confirmDeleteAll">
            🗑 Delete All Deletable
        </button>
        <button class="btn-generate" wire:click="openCreateModal">
            ✎ Create Invoice
        </button>
        <button class="btn-generate" wire:click="confirmGenerate">
            + Generate All
        </button>
    </div>
</div>

{{-- Term selector --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
    <label style="font-size:12px;font-weight:500;color:var(--c-text-2);">Term:</label>
    <select wire:model.live="selectedTermId" class="sel">
        <option value="">All Terms</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
        @endforeach
    </select>
</div>

{{-- Stats bar --}}
<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-label">Total Invoices</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card" style="{{ $stats['draft'] > 0 ? 'border-color:#B45309' : '' }}">
        <div class="stat-label">Drafts (unsent)</div>
        <div class="stat-value" style="{{ $stats['draft'] > 0 ? 'color:#B45309' : '' }}">{{ $stats['draft'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Collected</div>
        <div class="stat-value" style="color:#15803D;font-size:15px;">₦{{ number_format($stats['revenue'], 0) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Outstanding</div>
        <div class="stat-value" style="color:var(--c-danger);font-size:15px;">₦{{ number_format($stats['outstanding'], 0) }}</div>
    </div>
</div>

{{-- Tabs --}}
<div class="tabs">
    <button class="tab-btn {{ $tab === 'all' ? 'active' : '' }}" wire:click="$set('tab','all')">
        All <span class="tab-count">{{ $stats['total'] }}</span>
    </button>
    <button class="tab-btn {{ $tab === 'draft' ? 'active' : '' }}" wire:click="$set('tab','draft')">
        Drafts <span class="tab-count">{{ $stats['draft'] }}</span>
    </button>
    <button class="tab-btn {{ $tab === 'sent' ? 'active' : '' }}" wire:click="$set('tab','sent')">
        Sent <span class="tab-count">{{ $stats['sent'] }}</span>
    </button>
</div>

{{-- Toolbar --}}
<div class="toolbar">
    <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search student…">
    </div>
    <select wire:model.live="filterStatus" class="sel">
        <option value="">All payment statuses</option>
        <option value="unpaid">Unpaid</option>
        <option value="partial">Part Paid</option>
        <option value="paid">Paid</option>
    </select>
</div>

{{-- Bulk action bar --}}
@if(count($selectedIds) > 0)
    <div class="bulk-bar">
        <span class="bulk-count">{{ count($selectedIds) }} selected</span>
        <button class="btn-bulk btn-bulk-green" wire:click="sendSelected"
            wire:confirm="Send {{ count($selectedIds) }} draft invoice(s) to parents now?">
            ✉ Send Selected
        </button>
        <button class="btn-bulk" wire:click="resendSelected"
            wire:confirm="Resend {{ count($selectedIds) }} invoice(s) to parents? Each parent will receive a fresh copy by email."
            style="border-color:var(--c-accent);color:var(--c-accent);">
            ↺ Resend Selected
        </button>
        <button class="btn-bulk btn-bulk-red" wire:click="confirmDeleteSelected">
            🗑 Delete Selected
        </button>
        <button class="btn-bulk" wire:click="$set('selectedIds', [])">Clear</button>
    </div>
@endif

{{-- Table --}}
<div class="panel">
    <div class="panel-head">
        <span class="panel-title">{{ $invoices->total() }} invoice(s)</span>
        <a href="{{ route('admin.fees.items') }}"
           style="font-size:12px;color:var(--c-text-3);text-decoration:none;">
            Manage Fee Items →
        </a>
    </div>

    @if($invoices->isEmpty())
        <div class="empty-state">
            @if($tab === 'draft')
                No draft invoices.
                <a href="#" wire:click="confirmGenerate" style="color:var(--c-accent)">Generate invoices →</a>
            @elseif($tab === 'sent')
                No sent invoices yet.
            @else
                No invoices found.
                <a href="#" wire:click="confirmGenerate" style="color:var(--c-accent)">Generate invoices →</a>
            @endif
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:36px;">
                        <input type="checkbox" class="row-check"
                            wire:model.live="selectAll"
                            title="Select all on this page">
                    </th>
                    <th>Student</th>
                    <th class="hide-sm">Term</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Sent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr onclick="window.location='{{ route('admin.fees.invoices.show', $invoice) }}'"
                        style="cursor:pointer;">
                        <td class="no-click" onclick="event.stopPropagation()">
                            <input type="checkbox" class="row-check"
                                wire:click="toggleSelect({{ $invoice->id }})"
                                @checked(in_array((string)$invoice->id, $selectedIds))>
                        </td>
                        <td>
                            <div class="student-name">{{ $invoice->student->full_name }}</div>
                            <div class="student-adm">{{ $invoice->student->admission_number }}</div>
                        </td>
                        <td class="hide-sm" style="font-size:12px;color:var(--c-text-3);">
                            @if($invoice->isMiscellaneous())
                                <span style="background:rgba(124,58,237,0.08);color:#7C3AED;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:600;">MISC</span>
                                {{ $invoice->description }}
                            @else
                                {{ $invoice->term->name }}
                            @endif
                        </td>
                        <td class="mono">₦{{ number_format($invoice->total_amount, 0) }}</td>
                        <td class="mono" style="color:#15803D;">₦{{ number_format($invoice->amount_paid, 0) }}</td>
                        <td class="mono" style="color:{{ $invoice->balance > 0 ? 'var(--c-danger)' : '#15803D' }}">
                            ₦{{ number_format($invoice->balance, 0) }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $invoice->status }}">
                                <span class="badge-dot"></span>
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            @if($invoice->isSent())
                                <span class="badge badge-sent" title="{{ $invoice->sent_at->format('d M Y, g:ia') }}">
                                    <span class="badge-dot"></span>
                                    Sent
                                </span>
                            @else
                                <span class="badge badge-draft">
                                    <span class="badge-dot"></span>
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="no-click" onclick="event.stopPropagation()">
                            <div class="row-actions">
                                @if($invoice->isDraft())
                                    <button class="btn-sm btn-sm-send"
                                        wire:click="sendInvoice({{ $invoice->id }})"
                                        wire:confirm="Send this invoice to {{ $invoice->student->full_name }}'s parents?">
                                        ✉ Send
                                    </button>
                                @else
                                    <button class="btn-sm"
                                        wire:click="resendInvoice({{ $invoice->id }})"
                                        wire:confirm="Resend this invoice to {{ $invoice->student->full_name }}'s parents?"
                                        title="Send a fresh copy by email">
                                        ↺ Resend
                                    </button>
                                @endif
                                @if($invoice->status === 'unpaid' && $invoice->payments()->count() === 0)
                                    <button class="btn-sm btn-sm-danger"
                                        wire:click="deleteInvoice({{ $invoice->id }})"
                                        wire:confirm="Delete this invoice for {{ $invoice->student->full_name }}? This cannot be undone.">
                                        Delete
                                    </button>
                                @endif
                            </div>
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

{{-- Generate confirm modal --}}
@if($showConfirmModal)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Generate Invoices for All Students</div>
        <div class="modal-sub">
            This creates draft invoices for <strong>all active students</strong> in the selected term
            based on the current fee structure. Students who already have an invoice for this term are skipped.
            No emails will be sent — you review the drafts first, then send.
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" wire:click="cancelGenerate">Cancel</button>
            <button class="btn-confirm" wire:click="generateInvoices"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Generate Drafts</span>
                <span wire:loading>Generating…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Send batch modal --}}
@if($showSendModal)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Send Draft Invoices</div>
        <div class="modal-sub">
            There are <strong>{{ $stats['draft'] }}</strong> unsent draft invoices for this term.
            Choose how many to send now — they'll be queued and delivered by email.
        </div>
        <div class="form-field">
            <label>Number of invoices to send</label>
            <input type="number" wire:model="sendBatchSize" min="1" max="{{ $stats['draft'] }}">
            <div class="field-hint">
                Max {{ $stats['draft'] }} (all drafts for this term).
                Send in batches if you want to stagger delivery.
            </div>
            @error('sendBatchSize') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showSendModal', false)">Cancel</button>
            <button class="btn-confirm-green" wire:click="sendBatch"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Send {{ $sendBatchSize }} Invoice(s)</span>
                <span wire:loading>Queuing…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Bulk delete confirmation modal --}}
@if($showDeleteModal)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title" style="color:var(--c-danger);">
            @if($deleteScope === 'selected')
                Delete {{ count($selectedIds) }} Selected Invoice(s)?
            @else
                Delete All Deletable Invoices?
            @endif
        </div>

        @if($deletableCount > 0)
            <div style="background:rgba(190,18,60,0.05);border:1px solid rgba(190,18,60,0.2);border-radius:8px;padding:14px;margin-bottom:16px;">
                <div style="font-size:13px;font-weight:600;color:var(--c-danger);margin-bottom:4px;">
                    {{ $deletableCount }} invoice(s) will be permanently deleted.
                </div>
                <div style="font-size:12px;color:var(--c-text-2);line-height:1.5;">
                    Only invoices that are <strong>unpaid</strong> with <strong>no recorded payments</strong> will be deleted.
                    This action cannot be undone.
                </div>
            </div>
        @endif

        @if($skippedCount > 0)
            <div style="background:rgba(180,83,9,0.05);border:1px solid rgba(180,83,9,0.2);border-radius:8px;padding:14px;margin-bottom:16px;">
                <div style="font-size:13px;font-weight:600;color:#B45309;margin-bottom:4px;">
                    {{ $skippedCount }} invoice(s) will be skipped.
                </div>
                <div style="font-size:12px;color:var(--c-text-2);line-height:1.5;">
                    Invoices with recorded payments or a partial/paid status cannot be deleted —
                    they are financial records.
                </div>
            </div>
        @endif

        @if($deletableCount === 0)
            <div style="font-size:13px;color:var(--c-text-2);margin-bottom:20px;line-height:1.5;">
                None of the selected invoices can be deleted. Only unpaid invoices with no
                recorded payments are eligible.
            </div>
        @endif

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showDeleteModal', false)">Cancel</button>
            @if($deletableCount > 0)
                <button class="btn-confirm-danger" wire:click="executeDelete"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50">
                    <span wire:loading.remove>Delete {{ $deletableCount }} Invoice(s)</span>
                    <span wire:loading>Deleting…</span>
                </button>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Create Invoice Modal ── --}}
@if($showCreateModal)
<div class="modal-overlay">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-title">Create Invoice</div>

        {{-- Mode tabs --}}
        <div style="display:flex;gap:2px;background:var(--c-bg);border:1px solid var(--c-border);border-radius:8px;padding:3px;margin-bottom:20px;">
            <button style="flex:1;padding:8px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;font-family:var(--f-sans);transition:all 150ms;background:{{ $createMode === 'single' ? 'var(--c-surface)' : 'none' }};color:{{ $createMode === 'single' ? 'var(--c-text-1)' : 'var(--c-text-3)' }};box-shadow:{{ $createMode === 'single' ? '0 1px 3px rgba(0,0,0,0.08)' : 'none' }};"
                wire:click="$set('createMode','single')">
                Single Student
            </button>
            <button style="flex:1;padding:8px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;font-family:var(--f-sans);transition:all 150ms;background:{{ $createMode === 'class' ? 'var(--c-surface)' : 'none' }};color:{{ $createMode === 'class' ? 'var(--c-text-1)' : 'var(--c-text-3)' }};box-shadow:{{ $createMode === 'class' ? '0 1px 3px rgba(0,0,0,0.08)' : 'none' }};"
                wire:click="$set('createMode','class')">
                Entire Class
            </button>
            <button style="flex:1;padding:8px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;font-family:var(--f-sans);transition:all 150ms;background:{{ $createMode === 'misc' ? 'var(--c-surface)' : 'none' }};color:{{ $createMode === 'misc' ? 'var(--c-text-1)' : 'var(--c-text-3)' }};box-shadow:{{ $createMode === 'misc' ? '0 1px 3px rgba(0,0,0,0.08)' : 'none' }};"
                wire:click="$set('createMode','misc')">
                Miscellaneous
            </button>
        </div>

        {{-- Term selector (shared — hidden for misc invoices) --}}
        @if($createMode !== 'misc')
        <div class="form-field">
            <label>Term <span style="color:var(--c-danger)">*</span></label>
            <select wire:model.live="createTermId" class="filter-sel" style="width:100%;padding:10px 12px;">
                <option value="">Select a term…</option>
                @foreach($terms as $term)
                    <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
                @endforeach
            </select>
            @error('createTermId') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        @endif

        @if($createMode === 'single')
            {{-- Student search --}}
            @if(! $createStudentId)
                <div class="form-field" style="position:relative;">
                    <label>Student <span style="color:var(--c-danger)">*</span></label>
                    <div style="position:relative;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--c-text-3);pointer-events:none;">
                            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
                        </svg>
                        <input type="text" wire:model.live="studentSearch"
                            placeholder="Search by name or admission number…"
                            style="width:100%;padding:10px 12px 10px 34px;border:1px solid var(--c-border);border-radius:8px;font-family:var(--f-sans);font-size:14px;background:var(--c-bg);outline:none;color:var(--c-text-1);"
                            autocomplete="off">
                    </div>
                    @if(count($studentResults) > 0)
                        <div style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--c-surface);border:1px solid var(--c-border);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);overflow:hidden;margin-top:4px;">
                            @foreach($studentResults as $r)
                                <div wire:click="selectStudent({{ $r['id'] }}, '{{ addslashes($r['name']) }}')"
                                     style="padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--c-border);transition:background 100ms;"
                                     onmouseover="this.style.background='var(--c-bg)'"
                                     onmouseout="this.style.background=''">
                                    <div style="font-size:13px;font-weight:600;color:var(--c-text-1);">{{ $r['name'] }}</div>
                                    <div style="font-size:11px;color:var(--c-text-3);">{{ $r['adm'] }} · {{ $r['class'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(strlen(trim($studentSearch)) > 0)
                        <div style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--c-surface);border:1px solid var(--c-border);border-radius:8px;padding:12px 14px;font-size:13px;color:var(--c-text-3);margin-top:4px;">
                            No active students found for "{{ $studentSearch }}"
                        </div>
                    @endif
                    @error('createStudentId') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            @else
                {{-- Selected student chip --}}
                <div class="form-field">
                    <label>Student</label>
                    <div style="display:flex;align-items:center;gap:8px;padding:10px 12px;background:var(--c-accent-bg);border:1px solid rgba(26,86,255,0.2);border-radius:8px;">
                        <span style="font-size:13px;font-weight:600;color:var(--c-text-1);flex:1;">{{ $createStudentName }}</span>
                        <button wire:click="clearStudent"
                            style="background:none;border:none;cursor:pointer;color:var(--c-text-3);font-size:18px;line-height:1;padding:0;"
                            title="Change student">×</button>
                    </div>
                </div>

                {{-- Preview for this student --}}
                @if($createTermId)
                    @if($createPreview === 'already_exists')
                        <div style="background:rgba(26,86,255,0.04);border:1px solid rgba(26,86,255,0.15);border-radius:8px;padding:12px 14px;font-size:13px;color:var(--c-accent);margin-bottom:4px;">
                            ✓ An invoice already exists for this student for the selected term.
                        </div>
                    @elseif($createPreview === 'no_fee_structure')
                        <div style="background:rgba(180,83,9,0.06);border:1px solid rgba(180,83,9,0.2);border-radius:8px;padding:12px 14px;font-size:13px;color:#B45309;margin-bottom:4px;">
                            ⚠ No fee structure configured for this student's class in the selected term.
                            Set it up under <strong>Finance → Fee Structure</strong> first.
                        </div>
                    @elseif(is_array($createPreview))
                        <div style="font-size:11px;font-weight:600;color:var(--c-text-3);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                            Fee Breakdown
                        </div>
                        <div style="border:1px solid var(--c-border);border-radius:8px;overflow:hidden;margin-bottom:4px;">
                            @foreach($createPreview['items'] as $item)
                                <div style="display:flex;justify-content:space-between;padding:9px 14px;border-bottom:1px solid var(--c-border);font-size:13px;">
                                    <span>{{ $item['name'] }}</span>
                                    <span style="font-family:var(--f-mono);font-size:12px;">₦{{ number_format($item['amount'], 0) }}</span>
                                </div>
                            @endforeach
                            <div style="display:flex;justify-content:space-between;padding:10px 14px;background:var(--c-bg);font-size:13px;font-weight:700;border-top:2px solid var(--c-border);">
                                <span>Total</span>
                                <span style="font-family:var(--f-mono);">₦{{ number_format($createPreview['total'], 0) }}</span>
                            </div>
                        </div>
                    @endif
                @endif
            @endif

        @elseif($createMode === 'class')
            {{-- Class mode --}}
            <div class="form-field">
                <label>Class <span style="color:var(--c-danger)">*</span></label>
                <select wire:model.live="createClassId" class="filter-sel" style="width:100%;padding:10px 12px;">
                    <option value="">Select a class…</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                    @endforeach
                </select>
                @error('createClassId') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            @if($createClassId && $createTermId)
                @if($createClassEligible === 0)
                    <div style="background:rgba(26,86,255,0.04);border:1px solid rgba(26,86,255,0.15);border-radius:8px;padding:12px 14px;font-size:13px;color:var(--c-accent);">
                        ✓ All active students in this class already have invoices for the selected term.
                    </div>
                @else
                    <div style="background:rgba(21,128,61,0.06);border:1px solid rgba(21,128,61,0.2);border-radius:8px;padding:12px 14px;font-size:13px;color:#15803D;">
                        <strong>{{ $createClassEligible }}</strong> {{ Str::plural('student', $createClassEligible) }}
                        will receive a new draft invoice. Students who already have an invoice for
                        this term will be skipped.
                    </div>
                @endif
            @endif
        @elseif($createMode === 'misc')

            {{-- Student search --}}
            @if(! $miscStudentId)
                <div class="form-field" style="position:relative;">
                    <label>Student <span style="color:var(--c-danger)">*</span></label>
                    <div style="position:relative;">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--c-text-3);pointer-events:none;">
                            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
                        </svg>
                        <input type="text" wire:model.live="miscStudentSearch"
                            placeholder="Search by name or admission number…"
                            style="width:100%;padding:10px 12px 10px 34px;border:1px solid var(--c-border);border-radius:8px;font-family:var(--f-sans);font-size:14px;background:var(--c-bg);outline:none;color:var(--c-text-1);"
                            autocomplete="off">
                    </div>
                    @if(count($miscStudentResults) > 0)
                        <div style="position:absolute;top:100%;left:0;right:0;z-index:50;background:var(--c-surface);border:1px solid var(--c-border);border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);overflow:hidden;margin-top:4px;">
                            @foreach($miscStudentResults as $r)
                                <div wire:click="selectMiscStudent({{ $r['id'] }}, '{{ addslashes($r['name']) }}')"
                                     style="padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--c-border);"
                                     onmouseover="this.style.background='var(--c-bg)'"
                                     onmouseout="this.style.background=''">
                                    <div style="font-size:13px;font-weight:600;color:var(--c-text-1);">{{ $r['name'] }}</div>
                                    <div style="font-size:11px;color:var(--c-text-3);">{{ $r['adm'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @error('miscStudentId') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            @else
                <div class="form-field">
                    <label>Student</label>
                    <div style="display:flex;align-items:center;gap:8px;padding:10px 12px;background:var(--c-accent-bg);border:1px solid rgba(26,86,255,0.2);border-radius:8px;">
                        <span style="font-size:13px;font-weight:600;color:var(--c-text-1);flex:1;">{{ $miscStudentName }}</span>
                        <button wire:click="clearMiscStudent" style="background:none;border:none;cursor:pointer;color:var(--c-text-3);font-size:18px;line-height:1;padding:0;" title="Change student">×</button>
                    </div>
                </div>
            @endif

            <div class="form-field">
                <label>Invoice Description <span style="color:var(--c-danger)">*</span></label>
                <input type="text" wire:model="miscDescription"
                    placeholder="e.g. School Uniform 2026, Book List, Extra Lesson Fee…"
                    style="width:100%;padding:10px 12px;border:1px solid var(--c-border);border-radius:8px;font-family:var(--f-sans);font-size:14px;background:var(--c-bg);outline:none;color:var(--c-text-1);">
                @error('miscDescription') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            {{-- Line items with fee catalogue + qty --}}
            <div class="form-field">
                <label>Line Items <span style="color:var(--c-danger)">*</span></label>

                @foreach($miscItems as $i => $item)
                    <div style="border:1px solid var(--c-border);border-radius:8px;padding:10px 12px;margin-bottom:8px;background:var(--c-bg);">

                        {{-- Fee item catalogue picker --}}
                        <div style="margin-bottom:8px;">
                            <select wire:change="selectFeeItem({{ $i }}, $event.target.value)"
                                style="width:100%;padding:8px 10px;border:1px solid var(--c-border);border-radius:6px;font-family:var(--f-sans);font-size:12px;background:var(--c-surface);color:var(--c-text-2);outline:none;">
                                <option value="">— Pick from fee catalogue (optional) —</option>
                                @foreach($feeItems as $fi)
                                    <option value="{{ $fi->id }}" {{ ($item['fee_item_id'] ?? '') == $fi->id ? 'selected' : '' }}>
                                        {{ $fi->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Item name (editable, auto-filled when catalogue item chosen) --}}
                        <div style="margin-bottom:8px;">
                            <input type="text" wire:model="miscItems.{{ $i }}.name"
                                placeholder="Item name (edit or type custom name)"
                                style="width:100%;padding:8px 10px;border:1px solid var(--c-border);border-radius:6px;font-family:var(--f-sans);font-size:13px;background:var(--c-surface);color:var(--c-text-1);outline:none;">
                            @error("miscItems.{$i}.name") <div class="field-error" style="font-size:11px;">{{ $message }}</div> @enderror
                        </div>

                        {{-- Qty + Unit price on one row --}}
                        <div style="display:flex;gap:8px;align-items:flex-start;">
                            <div style="width:90px;">
                                <label style="font-size:10px;color:var(--c-text-3);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:3px;">Qty</label>
                                <input type="number" wire:model="miscItems.{{ $i }}.qty"
                                    min="1" step="1"
                                    style="width:100%;padding:8px 10px;border:1px solid var(--c-border);border-radius:6px;font-family:var(--f-mono);font-size:13px;background:var(--c-surface);color:var(--c-text-1);outline:none;">
                                @error("miscItems.{$i}.qty") <div class="field-error" style="font-size:10px;">{{ $message }}</div> @enderror
                            </div>
                            <div style="flex:1;">
                                <label style="font-size:10px;color:var(--c-text-3);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:3px;">Unit Price (₦)</label>
                                <input type="number" wire:model="miscItems.{{ $i }}.unit_price"
                                    placeholder="0"
                                    style="width:100%;padding:8px 10px;border:1px solid var(--c-border);border-radius:6px;font-family:var(--f-mono);font-size:13px;background:var(--c-surface);color:var(--c-text-1);outline:none;">
                                @error("miscItems.{$i}.unit_price") <div class="field-error" style="font-size:10px;">{{ $message }}</div> @enderror
                            </div>
                            @php
                                $lineQty   = max(1, (int)($item['qty'] ?? 1));
                                $linePrice = (float)($item['unit_price'] ?? 0);
                                $lineTotal = $lineQty * $linePrice;
                            @endphp
                            <div style="width:100px;text-align:right;padding-top:18px;">
                                <span style="font-family:var(--f-mono);font-size:13px;font-weight:600;color:{{ $lineTotal > 0 ? 'var(--c-text-1)' : 'var(--c-text-3)' }};">
                                    ₦{{ number_format($lineTotal, 0) }}
                                </span>
                            </div>
                            @if(count($miscItems) > 1)
                                <button wire:click="removeMiscItem({{ $i }})"
                                    style="background:none;border:none;cursor:pointer;color:var(--c-danger);font-size:18px;line-height:1;padding:18px 0 0 4px;flex-shrink:0;">×</button>
                            @endif
                        </div>
                    </div>
                @endforeach

                <button wire:click="addMiscItem"
                    style="background:none;border:1px dashed var(--c-border);border-radius:8px;padding:8px 14px;font-size:12px;color:var(--c-text-3);cursor:pointer;width:100%;font-family:var(--f-sans);">
                    + Add another item
                </button>
            </div>

            {{-- Running total --}}
            @php
                $miscTotal = collect($miscItems)->sum(function($item) {
                    return max(1, (int)($item['qty'] ?? 1)) * (float)($item['unit_price'] ?? 0);
                });
            @endphp
            <div style="display:flex;justify-content:space-between;padding:10px 14px;background:var(--c-bg);border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:700;margin-top:4px;">
                <span>Total</span>
                <span style="font-family:var(--f-mono);color:{{ $miscTotal > 0 ? 'var(--c-text-1)' : 'var(--c-text-3)' }};">₦{{ number_format($miscTotal, 0) }}</span>
            </div>

        @endif

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showCreateModal', false)">Cancel</button>
            @php
                $miscTotal = collect($miscItems)->sum(function($item) {
                    return max(1, (int)($item['qty'] ?? 1)) * (float)($item['unit_price'] ?? 0);
                });
            @endphp
            <button class="btn-confirm" wire:click="createInvoices"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>
                    @if($createMode === 'single')
                        Create Draft Invoice
                    @elseif($createMode === 'class')
                        @if($createClassEligible > 0)
                            Create {{ $createClassEligible }} {{ Str::plural('Invoice', $createClassEligible) }}
                        @else
                            Create Invoices
                        @endif
                    @else
                        Create Invoice
                        @if($miscTotal > 0)
                            — ₦{{ number_format($miscTotal, 0) }}
                        @endif
                    @endif
                </span>
                <span wire:loading>Creating…</span>
            </button>
        </div>
    </div>
</div>
@endif

</div>
