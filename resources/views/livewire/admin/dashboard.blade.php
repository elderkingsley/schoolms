<div>
<style>
/* ─────────────────────────────────
   PAGE HEADER
───────────────────────────────── */
.pg-header { margin-bottom: 20px; }

.pg-greeting {
    font-size: 20px; font-weight: 700;
    color: var(--c-text-1); letter-spacing: -0.03em; line-height: 1.2;
}

@media (min-width: 640px) { .pg-greeting { font-size: 24px; } }

.pg-sub {
    font-size: 13px; color: var(--c-text-3); margin-top: 3px;
}

/* ─────────────────────────────────
   STATUS BAR
───────────────────────────────── */
.status-bar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 20px;
}

@media (min-width: 640px) {
    .status-bar { grid-template-columns: repeat(3, 1fr); }
}

.status-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md);
    padding: 14px 16px;
    display: flex; align-items: center; gap: 10px;
}

/* third card hidden on smallest screens */
.status-card:nth-child(3) { display: none; }
@media (min-width: 640px) { .status-card:nth-child(3) { display: flex; } }

.status-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.status-dot.on  { background: #15803D; box-shadow: 0 0 0 3px rgba(21,128,61,0.15); }
.status-dot.off { background: var(--c-text-3); }

.status-body {}

.status-lbl {
    font-size: 9.5px; font-weight: 600; color: var(--c-text-3);
    text-transform: uppercase; letter-spacing: 0.08em;
}

.status-val {
    font-size: 13px; font-weight: 600; color: var(--c-text-1);
    letter-spacing: -0.01em; margin-top: 1px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* ─────────────────────────────────
   STAT CARDS
───────────────────────────────── */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .stats-grid { grid-template-columns: repeat(4, 1fr); gap: 14px; }
}

.stat-card {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md);
    padding: 16px;
    position: relative; overflow: hidden;
    transition: box-shadow var(--dur) var(--ease), transform var(--dur) var(--ease);
}

.stat-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}

/* Subtle tinted top border per card */
.stat-card::before {
    content: ''; position: absolute;
    top: 0; left: 0; right: 0; height: 2px;
    border-radius: var(--r-md) var(--r-md) 0 0;
}

.stat-card.c-blue::before  { background: #1A56FF; }
.stat-card.c-green::before { background: #15803D; }
.stat-card.c-amber::before { background: #B45309; }
.stat-card.c-rose::before  { background: #BE123C; }

.stat-icon {
    width: 30px; height: 30px;
    border-radius: var(--r-sm);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
}

.stat-icon.c-blue  { background: rgba(26,86,255,0.08);  color: #1A56FF; }
.stat-icon.c-green { background: rgba(21,128,61,0.08);  color: #15803D; }
.stat-icon.c-amber { background: rgba(180,83,9,0.08);   color: #B45309; }
.stat-icon.c-rose  { background: rgba(190,18,60,0.08);  color: #BE123C; }

.stat-val {
    font-size: 26px; font-weight: 700;
    color: var(--c-text-1); letter-spacing: -0.04em;
    line-height: 1; font-family: var(--f-mono);
    margin-bottom: 4px;
}

@media (min-width: 640px) { .stat-val { font-size: 30px; } }

.stat-lbl {
    font-size: 11px; font-weight: 500;
    color: var(--c-text-2); letter-spacing: -0.01em;
}

.stat-sub {
    font-size: 10.5px; color: var(--c-text-3); margin-top: 4px;
}

/* ─────────────────────────────────
   LOWER GRID
───────────────────────────────── */
.lower-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}

@media (min-width: 768px) {
    .lower-grid { grid-template-columns: 1fr 1fr; }
}

/* ─────────────────────────────────
   PANEL
───────────────────────────────── */
.panel {
    background: var(--c-surface);
    border: 1px solid var(--c-border);
    border-radius: var(--r-md);
    overflow: hidden;
}

.panel-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--c-border);
}

.panel-title {
    font-size: 13px; font-weight: 600;
    color: var(--c-text-1); letter-spacing: -0.01em;
}

.panel-link {
    font-size: 11px; color: var(--c-accent); font-weight: 500;
    text-decoration: none;
}
.panel-link:hover { text-decoration: underline; }

/* ─────────────────────────────────
   QUICK ACTIONS
───────────────────────────────── */
.qa-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    padding: 14px;
}

.qa-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 12px;
    border-radius: var(--r-sm);
    border: 1px solid var(--c-border);
    background: var(--c-bg);
    cursor: pointer; text-decoration: none;
    transition: border-color var(--dur), background var(--dur), box-shadow var(--dur);
}

.qa-btn:hover {
    border-color: var(--c-accent);
    background: rgba(26,86,255,0.04);
    box-shadow: 0 2px 8px rgba(26,86,255,0.1);
}

.qa-icon {
    width: 32px; height: 32px; border-radius: var(--r-sm);
    background: var(--c-surface); border: 1px solid var(--c-border);
    display: flex; align-items: center; justify-content: center;
    color: var(--c-text-2); flex-shrink: 0;
    transition: background var(--dur), color var(--dur), border-color var(--dur);
}

.qa-btn:hover .qa-icon {
    background: var(--c-accent); color: #fff; border-color: var(--c-accent);
}

.qa-text {}

.qa-lbl {
    font-size: 12px; font-weight: 500; color: var(--c-text-1);
    white-space: nowrap;
}

.qa-sub {
    font-size: 10px; color: var(--c-text-3); margin-top: 1px;
    white-space: nowrap;
}

/* ─────────────────────────────────
   ACTIVITY
───────────────────────────────── */
.activity-list { padding: 6px 0; }

.activity-row {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 10px 18px;
    transition: background var(--dur);
}

.activity-row:hover { background: var(--c-bg); }

.act-dot {
    width: 7px; height: 7px; border-radius: 50%;
    margin-top: 5px; flex-shrink: 0;
}

.act-dot.blue  { background: #1A56FF; }
.act-dot.green { background: #15803D; }
.act-dot.amber { background: #B45309; }

.act-body { flex: 1; }

.act-desc {
    font-size: 12.5px; color: var(--c-text-1); line-height: 1.4;
}

.act-time {
    font-size: 10px; color: var(--c-text-3);
    font-family: var(--f-mono); margin-top: 2px;
}

.empty-msg {
    padding: 28px 18px;
    text-align: center; font-size: 12px; color: var(--c-text-3);
}
</style>

{{-- Page header --}}
<div class="pg-header">
    <h1 class="pg-greeting">
        Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
        {{ explode(' ', auth()->user()->name)[0] }} 👋
    </h1>
    <p class="pg-sub">Here's what's happening at Nurtureville today.</p>
</div>

{{-- Status bar --}}
<div class="status-bar">
    <div class="status-card">
        <div class="status-dot {{ $activeSession ? 'on' : 'off' }}"></div>
        <div class="status-body">
            <div class="status-lbl">Session</div>
            <div class="status-val">{{ $activeSession?->name ?? '—' }}</div>
        </div>
    </div>
    <div class="status-card">
        <div class="status-dot {{ $activeTerm ? 'on' : 'off' }}"></div>
        <div class="status-body">
            <div class="status-lbl">Term</div>
            <div class="status-val">{{ $activeTerm ? $activeTerm->name . ' Term' : '—' }}</div>
        </div>
    </div>
    <div class="status-card">
        <div class="status-dot on"></div>
        <div class="status-body">
            <div class="status-lbl">Today</div>
            <div class="status-val">{{ now()->format('d M Y') }}</div>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="stats-grid">

    <div class="stat-card c-blue">
        <div class="stat-icon c-blue">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="8" cy="5" r="3"/>
                <path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5"/>
            </svg>
        </div>
        <div class="stat-val">{{ $totalStudents }}</div>
        <div class="stat-lbl">Students</div>
        <div class="stat-sub">{{ $activeStudents }} enrolled this session</div>
    </div>

    <div class="stat-card c-green">
        <div class="stat-icon c-green">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="6" cy="5" r="2.5"/>
                <path d="M1 14c0-2.761 2.239-4.5 5-4.5s5 1.739 5 4.5"/>
                <path d="M11 7.5c.828 0 1.5-.672 1.5-1.5S11.828 4.5 11 4.5M15 14c0-2-1.343-3.5-4-3.5"/>
            </svg>
        </div>
        <div class="stat-val">{{ $totalParents }}</div>
        <div class="stat-lbl">Parents</div>
        <div class="stat-sub">Registered guardians</div>
    </div>

    <div class="stat-card c-amber">
        <div class="stat-icon c-amber">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="1" y="3" width="14" height="10" rx="1.5"/>
                <path d="M1 6h14M5 10h2"/>
            </svg>
        </div>
        <div class="stat-val">₦{{ number_format($feesCollected / 1000, 0) }}k</div>
        <div class="stat-lbl">Collected</div>
        <div class="stat-sub">₦{{ number_format($feesCollected) }} this term</div>
    </div>

    <div class="stat-card c-rose">
        <div class="stat-icon c-rose">
            <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M8 1v14M5 4h4.5a2.5 2.5 0 0 1 0 5H5M5 9h5a2.5 2.5 0 0 1 0 5H5"/>
            </svg>
        </div>
        <div class="stat-val">₦{{ number_format($feesOutstanding / 1000, 0) }}k</div>
        <div class="stat-lbl">Outstanding</div>
        <div class="stat-sub">₦{{ number_format($feesOutstanding) }} unpaid</div>
    </div>

</div>

{{-- Lower grid --}}
<div class="lower-grid">

    {{-- Quick actions --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Quick Actions</span>
        </div>
        <div class="qa-grid">
            <a href="#" class="qa-btn">
                <div class="qa-icon">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <circle cx="8" cy="5" r="3"/>
                        <path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5"/>
                    </svg>
                </div>
                <div class="qa-text">
                    <div class="qa-lbl">Add Student</div>
                    <div class="qa-sub">Enrol a new pupil</div>
                </div>
            </a>
            <a href="#" class="qa-btn">
                <div class="qa-icon">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <rect x="1" y="3" width="14" height="10" rx="1.5"/>
                        <path d="M1 6h14M5 10h2"/>
                    </svg>
                </div>
                <div class="qa-text">
                    <div class="qa-lbl">Record Payment</div>
                    <div class="qa-sub">Log a fee payment</div>
                </div>
            </a>
            <a href="#" class="qa-btn">
                <div class="qa-icon">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M12 2H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                        <path d="M5 6h6M5 9h6M5 12h3"/>
                    </svg>
                </div>
                <div class="qa-text">
                    <div class="qa-lbl">Enter Results</div>
                    <div class="qa-sub">Upload term scores</div>
                </div>
            </a>
            <a href="#" class="qa-btn">
                <div class="qa-icon">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M14 2H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h3l3 3 3-3h3a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                    </svg>
                </div>
                <div class="qa-text">
                    <div class="qa-lbl">Send Message</div>
                    <div class="qa-sub">Broadcast to parents</div>
                </div>
            </a>
        </div>
    </div>

    {{-- Recent activity --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Recent Activity</span>
            <a href="#" class="panel-link">View all</a>
        </div>
        <div class="activity-list">
            {{-- Seeded data won't have real activity yet, so we show a placeholder --}}
            <div class="empty-msg">
                No recent activity yet.<br>Activity will appear here as you use the system.
            </div>
        </div>
    </div>

</div>

</div>
