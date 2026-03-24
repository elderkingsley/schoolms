<div>
<style>
/* ── Back link ── */
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; color: var(--c-text-3); text-decoration: none;
    margin-bottom: 20px;
    transition: color var(--dur);
}
.back-link:hover { color: var(--c-text-1); }

/* ── Profile header ── */
.profile-header {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--r-md); padding: 24px;
    display: flex; align-items: flex-start; gap: 20px;
    margin-bottom: 20px; flex-wrap: wrap;
}

.profile-avatar {
    width: 64px; height: 64px; border-radius: 50%;
    background: var(--c-accent-bg);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 700; color: var(--c-accent);
    flex-shrink: 0;
}

.profile-info { flex: 1; min-width: 0; }

.profile-name {
    font-size: 20px; font-weight: 700;
    color: var(--c-text-1); letter-spacing: -0.03em; line-height: 1.2;
}

.profile-meta {
    font-size: 13px; color: var(--c-text-3); margin-top: 4px;
}

.profile-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }

.badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 500;
}

.badge-active   { background: rgba(21,128,61,0.08);  color: #15803D; }
.badge-pending  { background: rgba(180,83,9,0.08);   color: #B45309; }
.badge-withdrawn { background: rgba(100,100,100,0.08); color: #666; }
.badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.badge-blue { background: var(--c-accent-bg); color: var(--c-accent); }

.adm-no {
    font-family: var(--f-mono); font-size: 12px;
    color: var(--c-text-3); margin-top: 6px;
}

/* ── Grid layout ── */
.profile-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

@media (min-width: 768px) {
    .profile-grid { grid-template-columns: 1fr 1fr; }
}

/* ── Info cards ── */
.info-card {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--r-md); overflow: hidden;
}

.info-card-head {
    padding: 14px 18px; border-bottom: 1px solid var(--c-border);
    font-size: 12px; font-weight: 600; color: var(--c-text-1);
    display: flex; align-items: center; gap: 8px;
}

.info-card-head svg { color: var(--c-accent); }

.info-rows { padding: 4px 0; }

.info-row {
    display: flex; align-items: baseline;
    padding: 10px 18px; gap: 12px;
    border-bottom: 1px solid var(--c-border);
}

.info-row:last-child { border-bottom: none; }

.info-key {
    font-size: 11px; font-weight: 500; color: var(--c-text-3);
    min-width: 130px; flex-shrink: 0;
}

.info-val {
    font-size: 13px; color: var(--c-text-1); font-weight: 500;
    word-break: break-word;
}

.info-val.mono { font-family: var(--f-mono); font-size: 12px; }

/* ── Parent cards ── */
.parent-card {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--r-md); margin-bottom: 10px; overflow: hidden;
}

.parent-card:last-child { margin-bottom: 0; }

.parent-head {
    padding: 12px 18px; background: var(--c-bg);
    border-bottom: 1px solid var(--c-border);
    display: flex; align-items: center; justify-content: space-between;
}

.parent-name { font-size: 13px; font-weight: 600; color: var(--c-text-1); }
.parent-rel  { font-size: 11px; color: var(--c-text-3); }

/* ── Full width card ── */
.full-width { grid-column: 1 / -1; }

/* ── Medical alert ── */
.medical-alert {
    background: rgba(180,83,9,0.06); border: 1px solid rgba(180,83,9,0.2);
    border-radius: var(--r-sm); padding: 12px 16px;
    font-size: 13px; color: #B45309; line-height: 1.5;
    display: flex; gap: 10px; align-items: flex-start;
}

/* ── Enrolment history table ── */
.hist-table { width: 100%; border-collapse: collapse; }
.hist-table th {
    font-size: 10px; font-weight: 600; color: var(--c-text-3);
    text-transform: uppercase; letter-spacing: 0.06em;
    padding: 10px 18px; text-align: left;
    background: var(--c-bg); border-bottom: 1px solid var(--c-border);
}
.hist-table td {
    padding: 12px 18px; font-size: 13px;
    border-bottom: 1px solid var(--c-border);
}
.hist-table tr:last-child td { border-bottom: none; }

.empty-note {
    padding: 20px 18px; font-size: 12px;
    color: var(--c-text-3); text-align: center;
}
</style>

{{-- Back link --}}
<a href="{{ url()->previous() }}" class="back-link">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
        <path d="M10 3L5 8l5 5"/>
    </svg>
    Back
</a>

{{-- Profile header --}}
<div class="profile-header">
    <div class="profile-avatar">
        {{ strtoupper(substr($student->first_name, 0, 1)) }}
    </div>
    <div class="profile-info">
        <div class="profile-name">
            {{ $student->first_name }}
            @if($student->other_name) {{ $student->other_name }} @endif
            {{ $student->last_name }}
        </div>
        <div class="profile-meta">
            {{ $student->gender }} ·
            {{ $student->date_of_birth?->format('d M Y') ?? '—' }}
            @if($student->date_of_birth)
                · Age {{ $student->date_of_birth->age }}
            @endif
        </div>
        <div class="profile-badges">
            <span class="badge badge-{{ $student->status }}">
                <span class="badge-dot"></span>
                {{ ucfirst($student->status) }}
            </span>
            @if($student->class_applied_for)
                <span class="badge badge-blue">
                    Applied: {{ $student->class_applied_for }}
                </span>
            @endif
            @if($enrolment = $student->enrolments->first())
                <span class="badge badge-active">
                    {{ $enrolment->schoolClass?->name ?? '—' }}
                </span>
            @endif
        </div>
        @if(!str_starts_with($student->admission_number, 'TEMP-'))
            <div class="adm-no">Admission No: {{ $student->admission_number }}</div>
        @endif
    </div>
</div>

{{-- Info grid --}}
<div class="profile-grid">

    {{-- Student details --}}
    <div class="info-card">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5"/>
            </svg>
            Student Details
        </div>
        <div class="info-rows">
            <div class="info-row">
                <span class="info-key">First Name</span>
                <span class="info-val">{{ $student->first_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Last Name</span>
                <span class="info-val">{{ $student->last_name }}</span>
            </div>
            @if($student->other_name)
            <div class="info-row">
                <span class="info-key">Other Name</span>
                <span class="info-val">{{ $student->other_name }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-key">Gender</span>
                <span class="info-val">{{ $student->gender }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Date of Birth</span>
                <span class="info-val">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Class Applied For</span>
                <span class="info-val">{{ $student->class_applied_for ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Status</span>
                <span class="info-val">
                    <span class="badge badge-{{ $student->status }}">
                        <span class="badge-dot"></span>
                        {{ ucfirst($student->status) }}
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-key">Submitted</span>
                <span class="info-val mono">
                    {{ $student->created_at->format('d M Y, g:ia') }}
                </span>
            </div>
            @if($student->approved_at)
            <div class="info-row">
                <span class="info-key">Approved</span>
                <span class="info-val mono">
                    {{ $student->approved_at->format('d M Y, g:ia') }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Emergency contact --}}
    <div class="info-card">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M14 10.67c0 .2-.05.4-.14.58l-.89 1.79a1.5 1.5 0 0 1-1.37.96C5.4 14 2 10.6 2 4.4a1.5 1.5 0 0 1 .96-1.37l1.79-.89c.18-.09.38-.14.58-.14.5 0 .96.29 1.17.74l1.2 2.67c.14.32.11.69-.09.98L6.58 7.3A8.03 8.03 0 0 0 8.7 9.42l.91-1.03c.29-.2.66-.23.98-.09l2.67 1.2c.45.21.74.67.74 1.17z"/>
            </svg>
            Emergency Contact
        </div>
        <div class="info-rows">
            @php $primaryParent = $student->parents->first(); @endphp
            @if($primaryParent && $primaryParent->emergency_contact_name)
                <div class="info-row">
                    <span class="info-key">Name</span>
                    <span class="info-val">{{ $primaryParent->emergency_contact_name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Phone</span>
                    <span class="info-val mono">{{ $primaryParent->emergency_contact_phone ?? '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Relationship</span>
                    <span class="info-val">{{ $primaryParent->emergency_contact_relationship ?? '—' }}</span>
                </div>
            @else
                <div class="empty-note">No emergency contact on file.</div>
            @endif
        </div>

        @if($student->medical_notes)
            <div style="padding: 14px 18px; border-top: 1px solid var(--c-border)">
                <div style="font-size:11px;font-weight:600;color:var(--c-text-3);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">
                    Medical / Health Notes
                </div>
                <div class="medical-alert">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" style="flex-shrink:0;margin-top:1px">
                        <circle cx="8" cy="8" r="7"/>
                        <path d="M8 5v3M8 11h.01"/>
                    </svg>
                    {{ $student->medical_notes }}
                </div>
            </div>
        @endif
    </div>

    {{-- Parents / Guardians --}}
    <div class="info-card full-width">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="6" cy="5" r="2.5"/>
                <path d="M1 14c0-2.761 2.239-4.5 5-4.5s5 1.739 5 4.5"/>
                <path d="M11 7.5c.828 0 1.5-.672 1.5-1.5S11.828 4.5 11 4.5M15 14c0-2-1.343-3.5-4-3.5"/>
            </svg>
            Parents / Guardians ({{ $student->parents->count() }})
        </div>
        <div style="padding: 16px 18px; display: flex; flex-direction: column; gap: 12px;">
            @forelse($student->parents as $parent)
                <div class="parent-card">
                    <div class="parent-head">
                        <div>
                            <div class="parent-name">
                                {{ $parent->_temp_name ?? $parent->user?->name ?? '—' }}
                            </div>
                            <div class="parent-rel">
                                {{ $parent->pivot->relationship ?? $parent->relationship ?? 'Guardian' }}
                                @if($parent->pivot->is_primary_contact)
                                    · <span style="color:var(--c-accent)">Primary Contact</span>
                                @endif
                            </div>
                        </div>
                        @if($parent->user_id)
                            <span class="badge badge-active" style="font-size:10px">
                                Portal Active
                            </span>
                        @else
                            <span class="badge badge-pending" style="font-size:10px">
                                Pending Approval
                            </span>
                        @endif
                    </div>
                    <div class="info-rows">
                        <div class="info-row">
                            <span class="info-key">Email</span>
                            <span class="info-val mono">
                                {{ $parent->_temp_email ?? $parent->user?->email ?? '—' }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-key">Phone</span>
                            <span class="info-val mono">{{ $parent->phone ?? '—' }}</span>
                        </div>
                        @if($parent->address)
                        <div class="info-row">
                            <span class="info-key">Address</span>
                            <span class="info-val">{{ $parent->address }}</span>
                        </div>
                        @endif
                        @if($parent->occupation)
                        <div class="info-row">
                            <span class="info-key">Occupation</span>
                            <span class="info-val">{{ $parent->occupation }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-note">No parent records found.</div>
            @endforelse
        </div>
    </div>

    {{-- Enrolment history --}}
    <div class="info-card full-width">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="1" y="2" width="14" height="13" rx="1.5"/>
                <path d="M1 6h14M5 1v2M11 1v2"/>
            </svg>
            Enrolment History
        </div>
        @if($student->enrolments->isEmpty())
            <div class="empty-note">
                Not yet enrolled in any class.
                @if($student->status === 'pending') Approve the enrolment above to assign a class. @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="hist-table">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>Class</th>
                            <th>Enrolled On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($student->enrolments as $enrolment)
                            <tr>
                                <td>{{ $enrolment->session?->name ?? '—' }}</td>
                                <td>{{ $enrolment->schoolClass?->name ?? '—' }}</td>
                                <td style="font-family:var(--f-mono);font-size:12px">
                                    {{ $enrolment->enrolled_at?->format('d M Y') ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $enrolment->status }}">
                                        <span class="badge-dot"></span>
                                        {{ ucfirst($enrolment->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
</div>
