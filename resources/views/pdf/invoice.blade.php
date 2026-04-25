<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 11px;
        color: #111111;
        background: #fff;
        padding: 36px;
    }

    /* ── School header ── */
    .school-header {
        display: table;
        width: 100%;
        margin-bottom: 28px;
        border-bottom: 3px solid #1A56FF;
        padding-bottom: 18px;
    }
    .school-logo-cell { display: table-cell; vertical-align: middle; width: 70px; }
    .school-logo-img  { width: 60px; height: 60px; border-radius: 8px; object-fit: contain; }
    .school-logo-initial {
        width: 60px; height: 60px; border-radius: 50%;
        background: #1A56FF; color: #fff;
        font-size: 24px; font-weight: 700;
        text-align: center; line-height: 60px;
        display: inline-block;
    }
    .school-info-cell { display: table-cell; vertical-align: middle; padding-left: 14px; }
    .school-name    { font-size: 18px; font-weight: 700; color: #111; letter-spacing: -0.02em; }
    .school-tagline { font-size: 11px; color: #777; margin-top: 2px; }
    .invoice-label-cell { display: table-cell; vertical-align: middle; text-align: right; }
    .invoice-label  { font-size: 22px; font-weight: 700; color: #1A56FF; letter-spacing: -0.02em; }
    .invoice-number { font-size: 11px; color: #777; margin-top: 3px; font-family: 'Courier New', monospace; }

    /* ── Status pill ── */
    .status-pill {
        display: inline-block;
        padding: 3px 10px; border-radius: 20px;
        font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
        margin-top: 6px;
    }
    .status-unpaid  { background: #fde8ee; color: #BE123C; }
    .status-partial { background: #fef3e2; color: #B45309; }
    .status-paid    { background: #dcfce7; color: #15803D; }

    /* ── Meta row ── */
    .meta-row  { display: table; width: 100%; margin-bottom: 24px; }
    .meta-cell { display: table-cell; width: 50%; vertical-align: top; }
    .meta-cell.right { text-align: right; }
    .meta-block { background: #F5F4F0; border-radius: 6px; padding: 14px 16px; display: inline-block; width: 100%; }
    .meta-label { font-size: 9px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px; }
    .meta-value { font-size: 13px; font-weight: 600; color: #111; }
    .meta-sub   { font-size: 11px; color: #555; margin-top: 2px; }
    .meta-mono  { font-family: 'Courier New', monospace; font-size: 11px; color: #777; margin-top: 2px; }

    /* ── Line items table — no Type column ── */
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
    .items-table th {
        font-size: 9px; font-weight: 700; color: #999;
        text-transform: uppercase; letter-spacing: 0.08em;
        padding: 8px 12px; text-align: left;
        background: #F5F4F0; border-bottom: 1px solid #E8E6E1;
    }
    .items-table th.right { text-align: right; }
    .items-table td {
        padding: 7px 12px; font-size: 11px;
        border-bottom: 1px solid #F0EEE9;
        vertical-align: middle;
    }
    .items-table td.right { text-align: right; font-family: 'Courier New', monospace; }
    .items-table .total-row td {
        font-weight: 700; font-size: 12px;
        background: #F5F4F0;
        border-top: 2px solid #E8E6E1;
        border-bottom: none;
    }

    /* ── Summary box ── */
    .summary-box {
        width: 240px; float: right; margin-top: 16px;
        border: 1px solid #E8E6E1; border-radius: 6px; overflow: hidden;
    }
    .summary-row   { display: table; width: 100%; }
    .summary-label { display: table-cell; padding: 9px 14px; font-size: 11px; color: #555; }
    .summary-value { display: table-cell; padding: 9px 14px; font-size: 11px; font-family: 'Courier New', monospace; text-align: right; font-weight: 600; }
    .summary-row.balance { background: #F5F4F0; }
    .summary-row.balance .summary-label { font-weight: 700; color: #111; font-size: 12px; }
    .summary-row.balance .summary-value { font-weight: 700; color: #BE123C; font-size: 13px; }
    .summary-row.balance.cleared .summary-value { color: #15803D; }
    .summary-divider { border: none; border-top: 1px solid #E8E6E1; margin: 0; }

    /* ── Payment history ── */
    .section-title {
        font-size: 10px; font-weight: 700; color: #999;
        text-transform: uppercase; letter-spacing: 0.08em;
        margin-bottom: 8px; margin-top: 24px;
        clear: both; padding-top: 4px;
    }
    .payments-table { width: 100%; border-collapse: collapse; }
    .payments-table th {
        font-size: 9px; font-weight: 700; color: #999;
        text-transform: uppercase; letter-spacing: 0.08em;
        padding: 7px 12px; text-align: left;
        background: #F5F4F0; border-bottom: 1px solid #E8E6E1;
    }
    .payments-table th.right { text-align: right; }
    .payments-table td { padding: 9px 12px; font-size: 11px; border-bottom: 1px solid #F0EEE9; }
    .payments-table td.right { text-align: right; font-family: 'Courier New', monospace; color: #15803D; font-weight: 600; }
    .payments-table .mono { font-family: 'Courier New', monospace; color: #999; font-size: 10px; }

    /* ── Payment instructions ── */
    .instructions {
        margin-top: 28px; border: 1px solid #E8E6E1;
        border-radius: 6px; padding: 16px; background: #FAFAF8; clear: both;
    }
    .instructions-title { font-size: 10px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
    .bank-row   { display: table; width: 100%; margin-bottom: 6px; }
    .bank-label { display: table-cell; width: 130px; font-size: 11px; color: #777; }
    .bank-value { display: table-cell; font-size: 11px; font-weight: 600; color: #111; font-family: 'Courier New', monospace; }

    /* ── Footer ── */
    .footer {
        margin-top: 32px; padding-top: 14px;
        border-top: 1px solid #E8E6E1;
        font-size: 10px; color: #999; text-align: center;
    }
</style>
</head>
<body>

@php
    use App\Models\SchoolSetting;
    $schoolName    = SchoolSetting::get('school_name',    'Nurtureville School');
    $schoolTagline = SchoolSetting::get('school_tagline', 'Nurturing Minds, Building Futures');
    $schoolAddress = SchoolSetting::get('school_address', '');
    $schoolEmail   = SchoolSetting::get('school_email',   '');
    $schoolPhone   = SchoolSetting::get('school_phone',   '');
    $schoolWebsite = SchoolSetting::get('school_website', 'connect.nurturevilleschool.org');
    $logoBase64    = SchoolSetting::logoBase64();

    $bankName      = SchoolSetting::get('invoice_bank_name',      '');
    $accountName   = SchoolSetting::get('invoice_account_name',   '');
    $accountNumber = SchoolSetting::get('invoice_account_number', '');
    $paymentNote   = SchoolSetting::get('invoice_payment_note',   '');

    // Per-student NUBAN from BudPay/Korapay/JuicyWay takes priority over school-wide bank
    $student       = $invoice->student;
    $parentWithAccount = $student->parents->first(fn($p) => ! empty($p->active_account_number));
    if ($parentWithAccount) {
        $bankName      = $parentWithAccount->active_bank_name ?? $bankName;
        $accountName   = $schoolName . ' / ' . $student->full_name;
        $accountNumber = $parentWithAccount->active_account_number;
    }

    $hasPaymentDetails = ! empty($bankName) || ! empty($accountNumber);
@endphp

{{-- ── School header ── --}}
<div class="school-header">
    <div class="school-logo-cell">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" class="school-logo-img" alt="{{ $schoolName }}">
        @else
            <span class="school-logo-initial">{{ strtoupper(substr($schoolName, 0, 1)) }}</span>
        @endif
    </div>
    <div class="school-info-cell">
        <div class="school-name">{{ $schoolName }}</div>
        @if($schoolTagline)
            <div class="school-tagline">{{ $schoolTagline }}</div>
        @endif
    </div>
    <div class="invoice-label-cell">
        <div class="invoice-label">INVOICE</div>
        <div class="invoice-number">#{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
        <div style="margin-top:6px;">
            <span class="status-pill status-{{ $invoice->status }}">
                {{ strtoupper($invoice->status) }}
            </span>
        </div>
    </div>
</div>

{{-- ── Meta row ── --}}
<div class="meta-row">
    <div class="meta-cell">
        <div class="meta-block">
            <div class="meta-label">Billed To</div>
            <div class="meta-value">{{ $invoice->student->full_name }}</div>
            <div class="meta-mono">{{ $invoice->student->admission_number }}</div>
            @php
                $enrolment = $invoice->student->enrolments
                    ->where('academic_session_id', $invoice->term->academic_session_id)
                    ->first();
            @endphp
            @if($enrolment)
                <div class="meta-sub">{{ $enrolment->schoolClass->name }}</div>
            @endif
            @foreach($invoice->student->parents as $parent)
                @if($parent->user || $parent->_temp_name)
                <div class="meta-sub" style="margin-top:6px;">
                    Parent: {{ $parent->user?->name ?? $parent->_temp_name }}
                </div>
                @endif
            @endforeach
        </div>
    </div>
    <div class="meta-cell right">
        <div class="meta-block" style="text-align:left;">
            <div class="meta-label">Invoice Period</div>
            <div class="meta-value">{{ $invoice->isMiscellaneous() ? $invoice->description : $invoice->term->name . ' Term' }}</div>
            <div class="meta-sub">{{ $invoice->term->session->name }}</div>
            <div class="meta-sub" style="margin-top:6px;">
                Date: {{ $invoice->created_at->format('d M Y') }}
            </div>
        </div>
    </div>
</div>

{{-- ── Fee line items — no Type column ── --}}
<table class="items-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Description</th>
            <th class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $i => $item)
        <tr>
            <td style="color:#999;width:30px;">{{ $i + 1 }}</td>
            <td>{{ $item->item_name }}</td>
            <td class="right">NGN {{ number_format($item->amount, 2) }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="2">Total</td>
            <td class="right">NGN {{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
    </tbody>
</table>

{{-- ── Summary box ── --}}
<div class="summary-box">
    <div class="summary-row">
        <div class="summary-label">Subtotal</div>
        <div class="summary-value">NGN {{ number_format($invoice->total_amount, 2) }}</div>
    </div>
    <hr class="summary-divider">
    <div class="summary-row">
        <div class="summary-label">Amount Paid</div>
        <div class="summary-value" style="color:#15803D">NGN {{ number_format($invoice->amount_paid, 2) }}</div>
    </div>
    <hr class="summary-divider">
    <div class="summary-row balance {{ $invoice->balance <= 0 ? 'cleared' : '' }}">
        <div class="summary-label">Balance Due</div>
        <div class="summary-value">NGN {{ number_format($invoice->balance, 2) }}</div>
    </div>
</div>

{{-- ── Payment history ── --}}
@if($invoice->payments->isNotEmpty())
<div class="section-title">Payment History</div>
<table class="payments-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Method</th>
            <th>Receipt No.</th>
            <th>Reference</th>
            <th class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->payments->sortByDesc('paid_at') as $payment)
        <tr>
            <td>{{ $payment->paid_at->format('d M Y') }}</td>
            <td>{{ $payment->method }}</td>
            <td class="mono">{{ $payment->receipt_number }}</td>
            <td class="mono">{{ $payment->reference ?: '—' }}</td>
            <td class="right">NGN {{ number_format($payment->amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ── Payment instructions ── --}}
@if($invoice->balance > 0 && $hasPaymentDetails)
<div class="instructions">
    <div class="instructions-title">Payment Instructions</div>
    @if($bankName)
    <div class="bank-row">
        <div class="bank-label">Bank Name</div>
        <div class="bank-value">{{ $bankName }}</div>
    </div>
    @endif
    @if($accountName)
    <div class="bank-row">
        <div class="bank-label">Account Name</div>
        <div class="bank-value">{{ $accountName }}</div>
    </div>
    @endif
    @if($accountNumber)
    <div class="bank-row">
        <div class="bank-label">Account Number</div>
        <div class="bank-value">{{ $accountNumber }}</div>
    </div>
    @endif
    <div class="bank-row">
        <div class="bank-label">Payment Reference</div>
        <div class="bank-value">{{ $invoice->student->admission_number }}</div>
    </div>
    @if($paymentNote)
    <div style="font-size:10px;color:#999;margin-top:10px;">{{ $paymentNote }}</div>
    @endif
</div>
@endif

{{-- ── Footer — school name, address, email only ── --}}
<div class="footer">
    <div style="font-weight:600;color:#555;">{{ $schoolName }}</div>
    @if($schoolAddress)
        <div style="margin-top:2px;">{{ $schoolAddress }}</div>
    @endif
    <div style="margin-top:2px;">
        @if($schoolEmail){{ $schoolEmail }}@endif
        @if($schoolEmail && $schoolPhone) &nbsp;·&nbsp; @endif
        @if($schoolPhone){{ $schoolPhone }}@endif
        @if(($schoolEmail || $schoolPhone) && $schoolWebsite) &nbsp;·&nbsp; @endif
        @if($schoolWebsite){{ $schoolWebsite }}@endif
    </div>
</div>

</body>
</html>
