{{-- Deploy to: resources/views/livewire/admin/academics/session-term-manager.blade.php --}}
{{-- REPLACES existing file — adds school_days_count and next_term_begins to the term form. --}}
<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.08); border:1px solid rgba(190,18,60,0.2); color:#BE123C; }
.btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); border:none; transition:opacity 0.15s; }
.btn:hover { opacity:0.85; }
.btn-primary { background:var(--c-accent); color:#fff; }
.btn-sm { padding:4px 10px; border-radius:6px; font-size:11px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); color:var(--c-text-2); }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-green  { color:#15803D; border-color:rgba(21,128,61,0.3); }
.btn-sm-green:hover  { background:rgba(21,128,61,0.06); }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }
.session-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); margin-bottom:16px; overflow:hidden; }
.session-card.active-session { border-color:var(--c-accent); }
.session-header { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; gap:12px; flex-wrap:wrap; }
.session-name { font-size:16px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; }
.session-actions { display:flex; gap:6px; flex-wrap:wrap; }
.terms-table { width:100%; border-collapse:collapse; border-top:1px solid var(--c-border); }
.terms-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.07em; padding:8px 18px; text-align:left; background:var(--c-bg); }
.terms-table td { padding:11px 18px; font-size:13px; border-top:1px solid var(--c-border); vertical-align:middle; }
.terms-table tr:hover td { background:#fafaf8; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-active   { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-inactive { background:rgba(100,100,100,0.08); color:#888; }
.badge-session  { background:rgba(99,102,241,0.1); color:#6366F1; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
.empty-terms { padding:20px 18px; font-size:13px; color:var(--c-text-3); text-align:center; }
.modal-bg { position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:100; display:flex; align-items:center; justify-content:center; padding:20px; }
.modal { background:var(--c-surface); border-radius:var(--r-md); width:100%; max-width:480px; padding:24px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto; }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); margin-bottom:18px; letter-spacing:-0.02em; }
.modal-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:20px; }
.form-group { margin-bottom:14px; }
.form-row   { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.form-label { display:block; font-size:12px; font-weight:600; color:var(--c-text-2); margin-bottom:5px; }
.form-input { width:100%; padding:9px 12px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-family:var(--f-sans); background:var(--c-surface); outline:none; color:var(--c-text-1); }
.form-input:focus { border-color:var(--c-accent); }
.form-select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%23888' d='M4 6l4 4 4-4'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:28px; }
.form-error { font-size:11px; color:var(--c-danger); margin-top:4px; }
.form-hint  { font-size:11px; color:var(--c-text-3); margin-top:4px; }
.form-section-label { font-size:10px; font-weight:700; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px; margin-top:4px; padding-top:12px; border-top:1px solid var(--c-border); }
.btn-cancel  { padding:8px 14px; border-radius:8px; font-size:13px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); color:var(--c-text-2); }
.btn-cancel:hover { background:var(--c-bg); }
.btn-confirm { padding:8px 14px; border-radius:8px; font-size:13px; font-weight:500; border:none; background:var(--c-accent); color:#fff; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm:hover { opacity:0.85; }
.btn-danger  { padding:8px 14px; border-radius:8px; font-size:13px; font-weight:500; border:none; background:#BE123C; color:#fff; cursor:pointer; font-family:var(--f-sans); }
.btn-danger:hover { opacity:0.85; }
.no-sessions { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:40px; text-align:center; font-size:13px; color:var(--c-text-3); }
.meta-pill { display:inline-block; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:500; background:var(--c-bg); border:1px solid var(--c-border); color:var(--c-text-3); margin-left:6px; }
</style>

@if(session('success'))
    <div class="flash flash-success">&#10003; {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">&#10007; {{ session('error') }}</div>
@endif

<div class="pg-header">
    <div>
        <div class="pg-title">Sessions &amp; Terms</div>
        <div class="pg-sub">Manage academic sessions, activate terms, and set report card dates</div>
    </div>
    <button class="btn btn-primary" wire:click="openCreateSession">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v12M2 8h12"/></svg>
        New Session
    </button>
</div>

@if($sessions->isEmpty())
    <div class="no-sessions">No academic sessions yet. Create one to get started.</div>
@else
    @foreach($sessions as $session)
        <div class="session-card {{ $session->is_active ? 'active-session' : '' }}">
            <div class="session-header">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <div class="session-name">{{ $session->name }}</div>
                    @if($session->is_active)
                        <span class="badge-session">Active Session</span>
                    @endif
                </div>
                <div class="session-actions">
                    @if(! $session->is_active)
                        <button class="btn-sm btn-sm-green"
                            wire:click="activateSession({{ $session->id }})"
                            wire:confirm="Activate {{ $session->name }}? This will deactivate the current session and all active terms.">
                            Activate
                        </button>
                    @endif
                    <button class="btn-sm" wire:click="openCreateTerm({{ $session->id }})">+ Term</button>
                    <button class="btn-sm" wire:click="openEditSession({{ $session->id }})">Edit</button>
                    @if(! $session->is_active)
                        <button class="btn-sm btn-sm-danger" wire:click="confirmDeleteSession({{ $session->id }})">Delete</button>
                    @endif
                </div>
            </div>

            @if($session->terms->isEmpty())
                <div class="empty-terms">No terms — click "+ Term" to add one.</div>
            @else
                <table class="terms-table">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>School Days</th>
                            <th>Next Term Begins</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($session->terms as $term)
                            <tr>
                                <td style="font-weight:600;">{{ $term->name }} Term</td>
                                <td style="color:var(--c-text-3);font-size:12px;">{{ $term->start_date?->format('d M Y') ?? '—' }}</td>
                                <td style="color:var(--c-text-3);font-size:12px;">{{ $term->end_date?->format('d M Y') ?? '—' }}</td>
                                <td style="color:var(--c-text-3);font-size:12px;">
                                    {{ $term->school_days_count ? $term->school_days_count . ' days' : '—' }}
                                </td>
                                <td style="color:var(--c-text-3);font-size:12px;">
                                    {{ $term->next_term_begins?->format('d M Y') ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $term->is_active ? 'badge-active' : 'badge-inactive' }}">
                                        <span class="badge-dot"></span>
                                        {{ $term->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                        @if(! $term->is_active && $session->is_active)
                                            <button class="btn-sm btn-sm-green"
                                                wire:click="activateTerm({{ $term->id }})"
                                                wire:confirm="Set {{ $term->name }} Term as active? This will deactivate the current term.">
                                                Activate
                                            </button>
                                        @endif
                                        <button class="btn-sm" wire:click="openEditTerm({{ $term->id }})">Edit</button>
                                        @if(! $term->is_active)
                                            <button class="btn-sm btn-sm-danger" wire:click="confirmDeleteTerm({{ $term->id }})">Delete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach
@endif

{{-- ── Session Form Modal ──────────────────────────────────────────────── --}}
@if($showSessionForm)
    <div class="modal-bg" wire:click.self="$set('showSessionForm', false)">
        <div class="modal">
            <div class="modal-title">{{ $editingSessionId ? 'Edit Session' : 'New Academic Session' }}</div>
            <div class="form-group">
                <label class="form-label">Session Name</label>
                <input type="text" class="form-input" wire:model="sessionName" placeholder="e.g. 2025/2026" autofocus>
                @error('sessionName') <div class="form-error">{{ $message }}</div> @enderror
                @if(! $editingSessionId)
                    <div class="form-hint">Three terms (First, Second, Third) will be created automatically.</div>
                @endif
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('showSessionForm', false)">Cancel</button>
                <button class="btn-confirm" wire:click="saveSession">
                    {{ $editingSessionId ? 'Save Changes' : 'Create Session' }}
                </button>
            </div>
        </div>
    </div>
@endif

{{-- ── Term Form Modal ─────────────────────────────────────────────────── --}}
@if($showTermForm)
    <div class="modal-bg" wire:click.self="$set('showTermForm', false)">
        <div class="modal">
            <div class="modal-title">{{ $editingTermId ? 'Edit Term' : 'Add Term' }}</div>

            <div class="form-group">
                <label class="form-label">Term</label>
                <select class="form-input form-select" wire:model="termName" {{ $editingTermId ? 'disabled' : '' }}>
                    <option value="">Select term…</option>
                    <option value="First">First Term</option>
                    <option value="Second">Second Term</option>
                    <option value="Third">Third Term</option>
                </select>
                @error('termName') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-input" wire:model="termStartDate">
                    @error('termStartDate') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-input" wire:model="termEndDate">
                    @error('termEndDate') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Report Card Fields --}}
            <div class="form-section-label">Report Card Settings</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">School Days This Term</label>
                    <input type="number" min="1" max="366" class="form-input" wire:model="termSchoolDays"
                        placeholder="e.g. 124">
                    <div class="form-hint">Printed as "No. of Times School Opened"</div>
                    @error('termSchoolDays') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Next Term Begins</label>
                    <input type="date" class="form-input" wire:model="termNextTermBegins">
                    <div class="form-hint">Printed on all report cards</div>
                    @error('termNextTermBegins') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('showTermForm', false)">Cancel</button>
                <button class="btn-confirm" wire:click="saveTerm">
                    {{ $editingTermId ? 'Save Changes' : 'Add Term' }}
                </button>
            </div>
        </div>
    </div>
@endif

{{-- ── Delete Session Confirmation ─────────────────────────────────────── --}}
@if($confirmingDeleteSessionId)
    <div class="modal-bg">
        <div class="modal">
            <div class="modal-title">Delete Session?</div>
            <p style="font-size:13px;color:var(--c-text-2);margin-bottom:4px;">This will permanently delete the session and all its terms.</p>
            <p style="font-size:12px;color:var(--c-text-3);">Sessions with invoices cannot be deleted.</p>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('confirmingDeleteSessionId', null)">Cancel</button>
                <button class="btn-danger" wire:click="deleteSession">Delete Session</button>
            </div>
        </div>
    </div>
@endif

{{-- ── Delete Term Confirmation ────────────────────────────────────────── --}}
@if($confirmingDeleteTermId)
    <div class="modal-bg">
        <div class="modal">
            <div class="modal-title">Delete Term?</div>
            <p style="font-size:13px;color:var(--c-text-2);margin-bottom:4px;">This will permanently delete this term.</p>
            <p style="font-size:12px;color:var(--c-text-3);">Terms with invoices cannot be deleted.</p>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('confirmingDeleteTermId', null)">Cancel</button>
                <button class="btn-danger" wire:click="deleteTerm">Delete Term</button>
            </div>
        </div>
    </div>
@endif

</div>
