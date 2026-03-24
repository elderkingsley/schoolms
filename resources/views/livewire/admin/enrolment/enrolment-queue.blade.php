<div>
<style>
/* ── Page header ── */
.pg-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}

.pg-title {
    font-size: 20px; font-weight: 700;
    color: var(--c-text-1); letter-spacing: -0.03em;
}

.pg-sub {
    font-size: 13px; color: var(--c-text-3); margin-top: 2px;
}

/* ── Stats row ── */
.queue-stats {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 12px; margin-bottom: 24px;
}

@media (max-width: 640px) { .queue-stats { grid-template-columns: 1fr 1fr; } }

.queue-stat {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--r-md); padding: 16px;
}

.qs-val {
    font-size: 28px; font-weight: 700;
    font-family: var(--f-mono); letter-spacing: -0.04em;
    color: var(--c-text-1); line-height: 1;
}

.qs-lbl {
    font-size: 11px; font-weight: 500; color: var(--c-text-3);
    text-transform: uppercase; letter-spacing: 0.06em; margin-top: 6px;
}

/* ── Flash message ── */
.flash-success {
    background: rgba(21,128,61,0.08); border: 1px solid rgba(21,128,61,0.2);
    color: #15803D; border-radius: var(--r-sm);
    padding: 12px 16px; margin-bottom: 16px;
    font-size: 13px; font-weight: 500;
}

/* ── Table panel ── */
.panel {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--r-md); overflow: hidden;
}

.panel-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-bottom: 1px solid var(--c-border);
}

.panel-title { font-size: 13px; font-weight: 600; color: var(--c-text-1); }

.count-badge {
    background: var(--c-accent-bg); color: var(--c-accent);
    font-size: 11px; font-weight: 600;
    padding: 3px 9px; border-radius: 20px;
}

/* ── Table ── */
.data-table { width: 100%; border-collapse: collapse; }

.data-table th {
    font-size: 10px; font-weight: 600;
    color: var(--c-text-3); text-transform: uppercase;
    letter-spacing: 0.08em; padding: 10px 20px;
    text-align: left; background: var(--c-bg);
    border-bottom: 1px solid var(--c-border);
}

.data-table td {
    padding: 14px 20px; font-size: 13px;
    color: var(--c-text-1); border-bottom: 1px solid var(--c-border);
    vertical-align: middle;
}

.data-table tr:last-child td { border-bottom: none; }

.data-table tr:hover td { background: var(--c-bg); }

/* Mobile: hide less important columns */
@media (max-width: 768px) {
    .hide-mobile { display: none; }
}

/* ── Student name cell ── */
.student-name { font-weight: 600; color: var(--c-text-1); }
.student-meta { font-size: 11px; color: var(--c-text-3); margin-top: 2px; }

/* ── Status badge ── */
.badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 20px;
    font-size: 11px; font-weight: 500;
}

.badge-pending { background: rgba(180,83,9,0.08); color: #B45309; }
.badge-active  { background: rgba(21,128,61,0.08); color: #15803D; }
.badge-withdrawn { background: rgba(100,100,100,0.08); color: #666; }

.badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

/* ── Action buttons ── */
.actions { display: flex; align-items: center; gap: 8px; }

.btn-approve {
    padding: 6px 14px; border-radius: 6px;
    background: var(--c-accent); color: #fff;
    font-size: 12px; font-weight: 500;
    border: none; cursor: pointer;
    transition: opacity 150ms;
    font-family: var(--f-sans);
}

.btn-approve:hover { opacity: 0.85; }

.btn-reject {
    padding: 6px 14px; border-radius: 6px;
    background: none; color: var(--c-danger);
    font-size: 12px; font-weight: 500;
    border: 1px solid rgba(190,18,60,0.2);
    cursor: pointer; transition: background 150ms;
    font-family: var(--f-sans);
}

.btn-reject:hover { background: rgba(190,18,60,0.06); }

/* ── Modal overlay ── */
.modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(3px);
    z-index: 50;
    display: flex; align-items: center; justify-content: center;
    padding: 16px;
}

.modal-box {
    background: var(--c-surface);
    border-radius: 16px;
    width: 100%; max-width: 480px;
    padding: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-title {
    font-size: 16px; font-weight: 700;
    color: var(--c-text-1); letter-spacing: -0.02em;
    margin-bottom: 4px;
}

.modal-sub {
    font-size: 12px; color: var(--c-text-3); margin-bottom: 20px;
}

.form-field { margin-bottom: 16px; }

.form-field label {
    display: block; font-size: 12px; font-weight: 500;
    color: var(--c-text-2); margin-bottom: 5px;
}

.form-field input,
.form-field select {
    width: 100%; padding: 10px 12px;
    border: 1px solid var(--c-border);
    border-radius: 8px; font-size: 14px;
    font-family: var(--f-sans);
    color: var(--c-text-1); background: var(--c-bg);
    outline: none; transition: border-color 150ms;
    -webkit-appearance: none;
}

.form-field input:focus,
.form-field select:focus {
    border-color: var(--c-accent);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(26,86,255,0.08);
}

.field-error { font-size: 11px; color: var(--c-danger); margin-top: 4px; }

.modal-actions {
    display: flex; gap: 10px; margin-top: 20px;
}

.btn-primary {
    flex: 1; padding: 11px;
    background: var(--c-accent); color: #fff;
    border: none; border-radius: 8px;
    font-size: 14px; font-weight: 500;
    cursor: pointer; font-family: var(--f-sans);
    transition: opacity 150ms;
}

.btn-primary:hover { opacity: 0.9; }

.btn-ghost {
    padding: 11px 20px;
    background: none; border: 1px solid var(--c-border);
    color: var(--c-text-2); border-radius: 8px;
    font-size: 14px; cursor: pointer;
    font-family: var(--f-sans);
}

.btn-ghost:hover { background: var(--c-bg); }

/* ── Student detail in modal ── */
.student-detail-card {
    background: var(--c-bg); border: 1px solid var(--c-border);
    border-radius: 8px; padding: 14px 16px; margin-bottom: 20px;
}

.detail-row {
    display: flex; justify-content: space-between;
    font-size: 12px; padding: 4px 0;
}

.detail-key { color: var(--c-text-3); }
.detail-val { font-weight: 500; color: var(--c-text-1); }

/* ── Empty state ── */
.empty-state {
    padding: 48px 20px; text-align: center;
}

.empty-icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: var(--c-bg); border: 1px solid var(--c-border);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px; color: var(--c-text-3);
}

.empty-title {
    font-size: 14px; font-weight: 600; color: var(--c-text-1); margin-bottom: 4px;
}

.empty-sub { font-size: 12px; color: var(--c-text-3); }

/* ── Pagination ── */
.pagination-wrap { padding: 14px 20px; border-top: 1px solid var(--c-border); }
</style>

{{-- Flash message --}}
@if(session('success'))
    <div class="flash-success">✓ {{ session('success') }}</div>
@endif

{{-- Page header --}}
<div class="pg-header">
    <div>
        <h1 class="pg-title">Enrolment Queue</h1>
        <p class="pg-sub">Review and approve pending student enrolment submissions.</p>
    </div>
</div>

{{-- Stats --}}
<div class="queue-stats">
    <div class="queue-stat">
        <div class="qs-val">{{ $pending->total() }}</div>
        <div class="qs-lbl">Pending Review</div>
    </div>
    <div class="queue-stat">
        <div class="qs-val">{{ \App\Models\Student::where('status', 'active')->count() }}</div>
        <div class="qs-lbl">Active Students</div>
    </div>
    <div class="queue-stat" style="display:none" id="stat3">
        <div class="qs-val">{{ \App\Models\Student::where('status', 'withdrawn')->count() }}</div>
        <div class="qs-lbl">Rejected</div>
    </div>
</div>

{{-- Queue table --}}
<div class="panel">
    <div class="panel-head">
        <span class="panel-title">Pending Submissions</span>
        <span class="count-badge">{{ $pending->total() }}</span>
    </div>

    @if($pending->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <svg width="20" height="20" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M8 2a6 6 0 1 0 0 12A6 6 0 0 0 8 2z"/>
                    <path d="M8 7v2M8 11h.01"/>
                </svg>
            </div>
            <div class="empty-title">No pending enrolments</div>
            <div class="empty-sub">New submissions from the enrolment form will appear here.</div>
        </div>
    @else
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th class="hide-mobile">Class Applied</th>
                        <th class="hide-mobile">Parent / Guardian</th>
                        <th class="hide-mobile">Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pending as $student)
                        <tr>
                            <td>
                                <div class="student-name">
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </div>
                                <div class="student-meta">
                                    {{ $student->gender }} ·
                                    {{ $student->date_of_birth ? $student->date_of_birth->format('d M Y') : '—' }}
                                </div>
                                @if($student->medical_notes)
                                    <div class="student-meta" style="color:#B45309">
                                        ⚕ Medical notes on file
                                    </div>
                                @endif
                            </td>
                            <td class="hide-mobile">
                                <span class="badge badge-pending">
                                    <span class="badge-dot"></span>
                                    {{ $student->class_applied_for ?? '—' }}
                                </span>
                            </td>
                            <td class="hide-mobile">
                                @foreach($student->parents->take(1) as $parent)
                                    <div style="font-size:13px;font-weight:500">
                                        {{ $parent->_temp_name ?? '—' }}
                                    </div>
                                    <div style="font-size:11px;color:var(--c-text-3)">
                                        {{ $parent->_temp_email ?? '' }}
                                    </div>
                                @endforeach
                            </td>
                            <td class="hide-mobile">
                                <span style="font-size:12px;color:var(--c-text-3);font-family:var(--f-mono)">
                                    {{ $student->created_at->format('d M Y') }}
                                </span>
                                <div style="font-size:11px;color:var(--c-text-3)">
                                    {{ $student->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="btn-approve"
                                        wire:click="approve({{ $student->id }})"
                                        wire:loading.attr="disabled">
                                        Approve
                                    </button>
                                    <button class="btn-reject"
                                        wire:click="reject({{ $student->id }})"
                                        wire:confirm="Reject this enrolment? This cannot be undone."
                                        wire:loading.attr="disabled">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($pending->hasPages())
            <div class="pagination-wrap">
                {{ $pending->links() }}
            </div>
        @endif
    @endif
</div>

{{-- ── Approval modal ── --}}
@if($reviewingId)
    @php $reviewStudent = \App\Models\Student::with('parents')->find($reviewingId); @endphp
    @if($reviewStudent)
    <div class="modal-overlay">
        <div class="modal-box">
            <h2 class="modal-title">Approve Enrolment</h2>
            <p class="modal-sub">Confirm class assignment and admission number for this student.</p>

            {{-- Student summary --}}
            <div class="student-detail-card">
                <div class="detail-row">
                    <span class="detail-key">Student</span>
                    <span class="detail-val">{{ $reviewStudent->first_name }} {{ $reviewStudent->last_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Gender</span>
                    <span class="detail-val">{{ $reviewStudent->gender }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Date of Birth</span>
                    <span class="detail-val">{{ $reviewStudent->date_of_birth?->format('d M Y') ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Applied For</span>
                    <span class="detail-val">{{ $reviewStudent->class_applied_for ?? '—' }}</span>
                </div>
                @foreach($reviewStudent->parents->take(1) as $p)
                <div class="detail-row">
                    <span class="detail-key">Parent</span>
                    <span class="detail-val">{{ $p->_temp_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Parent Email</span>
                    <span class="detail-val">{{ $p->_temp_email }}</span>
                </div>
                @endforeach
                @if($reviewStudent->medical_notes)
                <div class="detail-row">
                    <span class="detail-key" style="color:#B45309">Medical Notes</span>
                    <span class="detail-val" style="color:#B45309;font-size:11px">{{ $reviewStudent->medical_notes }}</span>
                </div>
                @endif
            </div>

            {{-- Assign class --}}
            <div class="form-field">
                <label>Assign Class <span style="color:var(--c-danger)">*</span></label>
                <select wire:model="assignedClass">
                    <option value="">Select class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class }}">{{ $class }}</option>
                    @endforeach
                </select>
                @error('assignedClass')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            {{-- Admission number --}}
            <div class="form-field">
                <label>Admission Number <span style="color:var(--c-danger)">*</span></label>
                <input type="text" wire:model="admissionNumber" placeholder="e.g. NV/2025/0001">
                @error('admissionNumber')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="modal-actions">
                <button class="btn-ghost" wire:click="$set('reviewingId', null)">Cancel</button>
                <button class="btn-primary"
                    wire:click="confirmApproval"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50">
                    <span wire:loading.remove wire:target="confirmApproval">Confirm & Approve</span>
                    <span wire:loading wire:target="confirmApproval">Approving...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
@endif

</div>
