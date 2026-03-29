<div>
<style>
.welcome-card {
    background: linear-gradient(135deg, #1A56FF 0%, #0E3ACC 100%);
    border-radius: var(--r-lg); padding: 20px;
    color: #fff; margin-bottom: 20px;
}
.welcome-title { font-size: 18px; font-weight: 700; letter-spacing: -0.02em; }
.welcome-sub   { font-size: 13px; opacity: 0.7; margin-top: 3px; }
.welcome-term  { font-size: 11px; opacity: 0.6; margin-top: 8px; }

.section-title { font-size: 12px; font-weight: 600; color: var(--c-text-3); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px; }

/* Child cards */
.children-grid { display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 20px; }
@media(min-width: 480px) { .children-grid { grid-template-columns: 1fr 1fr; } }

.child-card {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--r-md); padding: 16px;
    text-decoration: none; color: inherit;
    display: block; transition: box-shadow 150ms;
}
.child-card:hover { box-shadow: var(--shadow-float); }
.child-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--c-accent-bg); color: var(--c-accent);
    font-size: 18px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 10px;
}
.child-name  { font-size: 15px; font-weight: 700; color: var(--c-text-1); }
.child-class { font-size: 12px; color: var(--c-text-3); margin-top: 2px; }
.child-adm   { font-family: var(--f-mono); font-size: 11px; color: var(--c-text-3); margin-top: 2px; }

/* Fee status pill */
.fee-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 500;
    margin-top: 10px;
}
.fee-pill-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.fee-paid    { background: rgba(21,128,61,0.08); color: #15803D; }
.fee-partial { background: rgba(180,83,9,0.08);  color: #B45309; }
.fee-unpaid  { background: rgba(190,18,60,0.08); color: var(--c-danger); }
.fee-none    { background: rgba(100,100,100,0.08); color: #666; }

/* Quick links */
.quick-links { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
.quick-link {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--r-md); padding: 14px 16px;
    text-decoration: none; color: var(--c-text-1);
    display: flex; align-items: center; gap: 10px;
    transition: background 150ms;
}
.quick-link:hover { background: var(--c-accent-bg); }
.quick-link-icon { color: var(--c-accent); flex-shrink: 0; }
.quick-link-label { font-size: 13px; font-weight: 500; }
.quick-link-sub   { font-size: 11px; color: var(--c-text-3); margin-top: 1px; }

/* Unread badge */
.unread-badge {
    background: var(--c-danger); color: #fff;
    font-size: 10px; font-weight: 700;
    padding: 1px 6px; border-radius: 10px; margin-left: auto;
}

.empty-state { text-align: center; padding: 40px 20px; color: var(--c-text-3); font-size: 13px; }
</style>

{{-- Welcome card --}}
<div class="welcome-card">
    <div class="welcome-title">Welcome back, {{ auth()->user()->name }} 👋</div>
    <div class="welcome-sub">Nurtureville Parent Portal</div>
    @if($activeTerm)
        <div class="welcome-term">
            Current term: {{ $activeTerm->name }} Term — {{ $activeTerm->session->name }}
        </div>
    @endif
</div>

{{-- Children --}}
<div class="section-title">Your Children</div>

@if($children->isEmpty())
    <div class="empty-state">No active students linked to your account yet.</div>
@else
    <div class="children-grid">
        @foreach($children as $child)
            @php
                $enrolment  = $child->enrolments->first();
                $invoice    = $child->feeInvoices->first();
                $feeStatus  = $invoice?->status ?? null;
            @endphp
            <a href="{{ route('parent.fees') }}?child={{ $child->id }}" class="child-card">
                <div class="child-avatar">{{ strtoupper(substr($child->first_name, 0, 1)) }}</div>
                <div class="child-name">{{ $child->full_name }}</div>
                <div class="child-class">{{ $enrolment?->schoolClass?->name ?? 'Class not assigned' }}</div>
                <div class="child-adm">{{ $child->admission_number }}</div>

                @if($feeStatus)
                    <div class="fee-pill fee-{{ $feeStatus }}">
                        <span class="fee-pill-dot"></span>
                        @if($feeStatus === 'paid') Fees Paid
                        @elseif($feeStatus === 'partial') Part Paid — ₦{{ number_format($invoice->balance, 0) }} left
                        @else ₦{{ number_format($invoice->total_amount, 0) }} outstanding
                        @endif
                    </div>
                @else
                    <div class="fee-pill fee-none">No invoice this term</div>
                @endif
            </a>
        @endforeach
    </div>
@endif

{{-- Quick links --}}
<div class="section-title">Quick Access</div>
<div class="quick-links">
    <a href="{{ route('parent.fees') }}" class="quick-link">
        <div class="quick-link-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>
            </svg>
        </div>
        <div>
            <div class="quick-link-label">Fee Invoices</div>
            <div class="quick-link-sub">View & download</div>
        </div>
    </a>

    <a href="{{ route('parent.results') }}" class="quick-link">
        <div class="quick-link-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
        </div>
        <div>
            <div class="quick-link-label">Results</div>
            <div class="quick-link-sub">Published terms</div>
        </div>
    </a>

    <a href="{{ route('parent.messages') }}" class="quick-link">
        <div class="quick-link-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <div>
            <div class="quick-link-label">Messages</div>
            <div class="quick-link-sub">From the school</div>
        </div>
        @if($unread > 0)
            <span class="unread-badge">{{ $unread }}</span>
        @endif
    </a>

    <a href="{{ route('parent.children') }}" class="quick-link">
        <div class="quick-link-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="9" cy="7" r="4"/>
                <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
            </svg>
        </div>
        <div>
            <div class="quick-link-label">My Children</div>
            <div class="quick-link-sub">Profiles & details</div>
        </div>
    </a>
</div>
</div>
