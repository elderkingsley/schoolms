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
.badge-partial { background:rgba(180,83,9,0.08);   color:#B45309; }
.badge-paid    { background:rgba(21,128,61,0.08);  color:#15803D; }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; margin-bottom:12px; }
.panel-head { padding:12px 16px; border-bottom:1px solid var(--c-border); font-size:12px; font-weight:600; color:var(--c-text-1); display:flex; justify-content:space-between; align-items:center; }

.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; padding:9px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table th.right, .data-table td.right { text-align:right; }
.data-table td { padding:12px 16px; font-size:13px; border-bottom:1px solid var(--c-border); }
.data-table tr:last-child td { border-bottom:none; }
.total-row td { font-weight:700; background:var(--c-bg) !important; }
.mono { font-family:var(--f-mono); font-size:12px; }

/* Virtual account payment panel */
.nuban-box { padding:16px; }
.nuban-card { background:var(--c-bg); border:1px solid var(--c-border); border-radius:10px; padding:16px; margin:10px 0; }
.nuban-row  { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--c-border); font-size:13px; }
.nuban-row:last-child { border-bottom:none; padding-bottom:0; }
.nuban-label { color:var(--c-text-3); font-size:12px; }
.nuban-value { font-weight:600; color:var(--c-text-1); font-family:var(--f-mono); }
.copy-wrap   { display:flex; align-items:center; gap:6px; }
.copy-btn    { padding:3px 8px; border:1px solid var(--c-border); border-radius:5px; background:var(--c-surface); font-size:11px; font-weight:500; cursor:pointer; font-family:var(--f-sans); color:var(--c-text-2); }
.nuban-note  { font-size:11px; color:var(--c-text-3); line-height:1.5; margin-top:10px; }
.nuban-pending { background:rgba(180,83,9,0.05); border:1px solid rgba(180,83,9,0.2); border-radius:8px; padding:12px; font-size:12px; color:#B45309; line-height:1.5; margin-top:8px; }

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
    <span class="badge badge-{{ $invoice->status }}">
        <span class="badge-dot"></span>{{ ucfirst($invoice->status) }}
    </span>
</div>

{{-- Amounts --}}
<div class="amounts-row">
    <div class="amount-card">
        <div class="amount-label">Total</div>
        <div class="amount-value">₦{{ number_format($invoice->total_amount, 0) }}</div>
    </div>
    <div class="amount-card">
        <div class="amount-label">Paid</div>
        <div class="amount-value" style="color:#15803D">₦{{ number_format($invoice->amount_paid, 0) }}</div>
    </div>
    <div class="amount-card">
        <div class="amount-label">Balance</div>
        <div class="amount-value" style="color:{{ $invoice->balance > 0 ? 'var(--c-danger)' : '#15803D' }}">
            ₦{{ number_format($invoice->balance, 0) }}
        </div>
    </div>
</div>

{{-- Payment instructions — shown when there is an outstanding balance --}}
@if($invoice->balance > 0 && $invoice->status !== 'paid')
<div class="panel">
    <div class="panel-head">
        How to Pay
        @if($parentProfile?->active_account_number)
            <span style="font-size:10px;background:rgba(21,128,61,0.08);color:#15803D;padding:2px 8px;border-radius:10px;font-weight:600;">
                Bank Transfer Ready
            </span>
        @endif
    </div>
    <div class="nuban-box">

        @if($parentProfile && $parentProfile->hasVirtualAccount())
            {{-- Virtual account is provisioned — show full payment details --}}
            <p style="font-size:13px;color:var(--c-text-2);margin-bottom:2px;line-height:1.5;">
                Transfer <strong>₦{{ number_format($invoice->balance, 0) }}</strong>
                to your dedicated school fees account. Your payment will be confirmed
                automatically — no need to send a receipt.
            </p>

            <div class="nuban-card" x-data>
                <div class="nuban-row">
                    <span class="nuban-label">Bank</span>
                    <span class="nuban-value" style="font-family:var(--f-sans);">
                        {{ $parentProfile->active_bank_name }}
                    </span>
                </div>
                <div class="nuban-row">
                    <span class="nuban-label">Account Number</span>
                    <div class="copy-wrap">
                        <span class="nuban-value">{{ $parentProfile->active_account_number }}</span>
                        <button class="copy-btn"
                            x-on:click="navigator.clipboard.writeText('{{ $parentProfile->active_account_number }}');
                                        $el.textContent='Copied!';
                                        setTimeout(()=>$el.textContent='Copy',2000)">
                            Copy
                        </button>
                    </div>
                </div>
                <div class="nuban-row">
                    <span class="nuban-label">Account Name</span>
                    <span class="nuban-value" style="font-family:var(--f-sans);">
                        {{ $parentProfile->user?->name ?? auth()->user()->name }}
                    </span>
                </div>
            </div>

            <p class="nuban-note">
                This is your permanent school fees account. You can reuse it for all future
                payments — no new account number each term.
            </p>

        @elseif($parentProfile && $parentProfile->isWalletProvisioning())
            {{-- Wallet job is still running --}}
            <div class="nuban-pending">
                ⏳ Your dedicated payment account is being set up. This usually takes under
                a minute. Please refresh this page shortly or contact the bursary if this
                message persists.
            </div>
            <p style="font-size:12px;color:var(--c-text-3);margin-top:10px;line-height:1.5;">
                In the meantime, you can pay at the school bursary and quote reference:
                <strong style="font-family:var(--f-mono);">
                    {{ $invoice->payment_link_reference ?? 'INV-'.$invoice->id.'-T'.$invoice->term_id }}
                </strong>
            </p>

        @elseif($parentProfile && $parentProfile->isWalletFailed())
            {{-- Provisioning failed — fallback to bursary --}}
            <p style="font-size:13px;color:var(--c-text-2);line-height:1.5;">
                Please pay at the school bursary or by bank transfer and quote:
            </p>
            <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:8px;padding:12px 14px;margin:10px 0;">
                <div style="font-size:11px;color:var(--c-text-3);">Payment Reference</div>
                <div style="font-size:16px;font-weight:700;font-family:var(--f-mono);margin-top:3px;">
                    {{ $invoice->payment_link_reference ?? 'INV-'.$invoice->id.'-T'.$invoice->term_id }}
                </div>
            </div>
            <p style="font-size:11px;color:var(--c-text-3);">
                Contact the bursary to confirm your account details.
            </p>

        @else
            {{-- No parent profile or account not yet started --}}
            <p style="font-size:13px;color:var(--c-text-2);line-height:1.5;">
                Please pay at the school bursary and quote your payment reference:
            </p>
            <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:8px;padding:12px 14px;margin:10px 0;">
                <div style="font-size:11px;color:var(--c-text-3);">Payment Reference</div>
                <div style="font-size:16px;font-weight:700;font-family:var(--f-mono);margin-top:3px;">
                    {{ $invoice->payment_link_reference ?? 'INV-'.$invoice->id.'-T'.$invoice->term_id }}
                </div>
            </div>
        @endif
    </div>
</div>
@endif

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
                    <td class="right mono" style="color:#15803D">
                        ₦{{ number_format($payment->amount, 0) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Download PDF --}}
<a href="{{ route('parent.fees.pdf', $invoice) }}" target="_blank" class="btn-pdf">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M4 1h6l4 4v10H2V1z"/><path d="M10 1v4h4"/>
    </svg>
    Download Invoice PDF
</a>
</div>
