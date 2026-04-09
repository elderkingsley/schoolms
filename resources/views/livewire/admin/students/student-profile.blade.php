<div>
<style>
.back-link { display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--c-text-3); text-decoration:none; margin-bottom:20px; transition:color var(--dur); }
.back-link:hover { color:var(--c-text-1); }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }

/* Profile header */
.profile-header { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:24px; display:flex; align-items:flex-start; gap:20px; margin-bottom:20px; flex-wrap:wrap; }
.profile-avatar {
    width:72px; height:72px; border-radius:50%;
    background:var(--c-accent-bg);
    display:flex; align-items:center; justify-content:center;
    font-size:26px; font-weight:700; color:var(--c-accent);
    flex-shrink:0; overflow:hidden; border:2px solid var(--c-border);
}
.profile-avatar img { width:100%; height:100%; object-fit:cover; display:block; }
.profile-info { flex:1; min-width:0; }
.profile-name { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; line-height:1.2; }
.profile-meta { font-size:13px; color:var(--c-text-3); margin-top:4px; }
.profile-badges { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-active    { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-pending   { background:rgba(180,83,9,0.08); color:#B45309; }
.badge-withdrawn { background:rgba(100,100,100,0.08); color:#666; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-blue { background:var(--c-accent-bg); color:var(--c-accent); }
.adm-no { font-family:var(--f-mono); font-size:12px; color:var(--c-text-3); margin-top:6px; }

/* Edit button */
.btn-edit { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); color:var(--c-text-2); transition:background 150ms; }
.btn-edit:hover { background:var(--c-bg); }
.btn-save { padding:9px 20px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-save:hover { opacity:0.9; }
.btn-cancel-edit { padding:9px 16px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); }
.btn-cancel { padding:10px 20px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; color:var(--c-text-2); cursor:pointer; font-family:var(--f-sans); transition:background 150ms; }
.btn-cancel:hover { background:var(--c-bg); color:var(--c-text-1); }
.btn-confirm { padding:10px 20px; background:var(--c-accent); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-confirm:hover { opacity:0.88; }

/* Edit form */
.edit-card { background:var(--c-surface); border:2px solid var(--c-accent); border-radius:var(--r-md); padding:24px; margin-bottom:20px; }
.edit-title { font-size:15px; font-weight:700; color:var(--c-text-1); margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; }
.edit-grid { display:grid; grid-template-columns:1fr; gap:14px; }
@media(min-width:640px) { .edit-grid { grid-template-columns:1fr 1fr; } }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:5px; }
.form-field input, .form-field select, .form-field textarea { width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:14px; color:var(--c-text-1); background:var(--c-bg); outline:none; transition:border-color 150ms; -webkit-appearance:none; }
.form-field input:focus, .form-field select:focus, .form-field textarea:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 3px rgba(26,86,255,0.08); }
.form-field textarea { resize:vertical; min-height:80px; }
.field-error { font-size:11px; color:var(--c-danger); margin-top:4px; }
.edit-actions { display:flex; gap:10px; margin-top:20px; justify-content:flex-end; }
.span-2 { grid-column:span 2; }
@media(max-width:640px) { .span-2 { grid-column:span 1; } }

/* Info grid */
.profile-grid { display:grid; grid-template-columns:1fr; gap:16px; }
@media(min-width:768px) { .profile-grid { grid-template-columns:1fr 1fr; } }
.info-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.info-card-head { padding:14px 18px; border-bottom:1px solid var(--c-border); font-size:12px; font-weight:600; color:var(--c-text-1); display:flex; align-items:center; gap:8px; }
.info-card-head svg { color:var(--c-accent); }
.info-rows { padding:4px 0; }
.info-row { display:flex; align-items:baseline; padding:10px 18px; gap:12px; border-bottom:1px solid var(--c-border); }
.info-row:last-child { border-bottom:none; }
.info-key { font-size:11px; font-weight:500; color:var(--c-text-3); min-width:130px; flex-shrink:0; }
.info-val { font-size:13px; color:var(--c-text-1); font-weight:500; word-break:break-word; }
.info-val.mono { font-family:var(--f-mono); font-size:12px; }
.parent-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); margin-bottom:10px; overflow:hidden; }
.parent-card:last-child { margin-bottom:0; }
.parent-head { padding:12px 18px; background:var(--c-bg); border-bottom:1px solid var(--c-border); display:flex; align-items:center; justify-content:space-between; }
.parent-name { font-size:13px; font-weight:600; color:var(--c-text-1); }
.parent-rel  { font-size:11px; color:var(--c-text-3); }
.full-width { grid-column:1 / -1; }
.medical-alert { background:rgba(180,83,9,0.06); border:1px solid rgba(180,83,9,0.2); border-radius:var(--r-sm); padding:12px 16px; font-size:13px; color:#B45309; line-height:1.5; display:flex; gap:10px; align-items:flex-start; }
.hist-table { width:100%; border-collapse:collapse; }
.hist-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; padding:10px 18px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.hist-table td { padding:12px 18px; font-size:13px; border-bottom:1px solid var(--c-border); }
.hist-table tr:last-child td { border-bottom:none; }
.empty-note { padding:20px 18px; font-size:12px; color:var(--c-text-3); text-align:center; }

/* Invoice creation modal */
.inv-preview-items { margin:12px 0; border:1px solid var(--c-border); border-radius:8px; overflow:hidden; }
.inv-preview-row { display:flex; justify-content:space-between; padding:9px 14px; border-bottom:1px solid var(--c-border); font-size:13px; }
.inv-preview-row:last-child { border-bottom:none; }
.inv-preview-total { display:flex; justify-content:space-between; padding:10px 14px; background:var(--c-bg); font-size:13px; font-weight:700; border-top:2px solid var(--c-border); }
.inv-warning { background:rgba(180,83,9,0.06); border:1px solid rgba(180,83,9,0.2); border-radius:8px; padding:12px 14px; font-size:13px; color:#B45309; margin-top:10px; line-height:1.5; }
.inv-info    { background:rgba(26,86,255,0.04); border:1px solid rgba(26,86,255,0.15); border-radius:8px; padding:12px 14px; font-size:13px; color:var(--c-accent); margin-top:10px; line-height:1.5; }
.fee-snapshot { display:grid; grid-template-columns:repeat(3,1fr); border-top:1px solid var(--c-border); }
.fee-snap-item { padding:14px 18px; border-right:1px solid var(--c-border); }
.fee-snap-item:last-child { border-right:none; }
.fee-snap-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px; }
.fee-snap-value { font-size:18px; font-weight:700; font-family:var(--f-mono); letter-spacing:-0.02em; }
.fee-snap-value.danger  { color:var(--c-danger); }
.fee-snap-value.success { color:#15803D; }
.fee-snap-value.neutral { color:var(--c-text-1); }

/* ── Invoice history rows ── */
.inv-row { display:flex; align-items:center; gap:12px; border-bottom:1px solid var(--c-border); padding:11px 18px; font-size:13px; text-decoration:none; color:inherit; transition:background 150ms; }
.inv-row:last-child { border-bottom:none; }
.inv-row:hover { background:#fafaf8; }
.inv-row-term   { flex:1; }
.inv-row-name   { font-weight:500; color:var(--c-text-1); }
.inv-row-meta   { font-size:11px; color:var(--c-text-3); margin-top:1px; }
.inv-row-amount { font-family:var(--f-mono); font-size:12px; text-align:right; min-width:90px; }

/* ── Results snapshot ── */
.grade { display:inline-block; padding:2px 7px; border-radius:5px; font-size:11px; font-weight:700; }
.grade-A { background:rgba(21,128,61,0.1);  color:#15803D; }
.grade-B { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.grade-C { background:rgba(180,83,9,0.08);  color:#B45309; }
.grade-D,.grade-E,.grade-F { background:rgba(190,18,60,0.08); color:var(--c-danger); }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif

<a href="{{ url()->previous() }}" class="back-link">
    <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 3L5 8l5 5"/></svg>
    Back
</a>

{{-- ── Profile header ── --}}
<div class="profile-header">
    <div class="profile-avatar">
        @if($student->photo)
            <img src="{{ Storage::url($student->photo) }}"
                 alt="{{ $student->full_name }}"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
            <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:var(--c-accent);">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}
            </span>
        @else
            {{ strtoupper(substr($student->first_name, 0, 1)) }}
        @endif
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
            @if($student->date_of_birth) · Age {{ $student->date_of_birth->age }} @endif
        </div>
        <div class="profile-badges">
            <span class="badge badge-{{ $student->status }}">
                <span class="badge-dot"></span>{{ ucfirst($student->status) }}
            </span>
            @if($student->class_applied_for)
                <span class="badge badge-blue">Applied: {{ $student->class_applied_for }}</span>
            @endif
            @if($enrolment = $student->enrolments->first())
                <span class="badge badge-active">{{ $enrolment->schoolClass?->display_name ?? '—' }}</span>
            @endif
        </div>
        @if(!str_starts_with($student->admission_number, 'TEMP-'))
            <div class="adm-no">Admission No: {{ $student->admission_number }}</div>
        @endif
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @if($student->status === 'pending')
            {{-- Approve and reject buttons shown only for pending enrolments --}}
            <button style="padding:8px 18px;background:var(--c-accent);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--f-sans);display:inline-flex;align-items:center;gap:6px;"
                wire:click="openApproveModal">
                <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 8l4 4 8-8"/></svg>
                Approve
            </button>
            <button style="padding:8px 18px;background:none;border:1px solid var(--c-danger);color:var(--c-danger);border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--f-sans);display:inline-flex;align-items:center;gap:6px;"
                wire:click="openRejectModal">
                <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
                Reject
            </button>
        @endif
        @if($student->status === 'active')
            <button class="btn-edit" wire:click="openInvoiceModal"
                style="background:var(--c-accent);color:#fff;border-color:var(--c-accent);">
                <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3" width="14" height="10" rx="1.5"/>
                    <path d="M1 6h14M5 10h2"/>
                </svg>
                Generate Invoice
            </button>
        @endif
        @if(! $editing)
            <button class="btn-edit" wire:click="startEdit">
                <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M11 2l3 3-9 9H2v-3z"/>
                </svg>
                Edit Student
            </button>
        @endif
    </div>
</div>

{{-- ── Inline edit form ── --}}
@if($editing)
<div class="edit-card">
    <div class="edit-title">
        <span>Edit Student Details</span>
        <button class="btn-cancel-edit" wire:click="cancelEdit">Cancel</button>
    </div>

    <div class="edit-grid">

        {{-- Photo upload -- spans full width on all screen sizes --}}
        <div class="form-field" style="grid-column:1/-1;">
            <label>Student Photo</label>
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">

                {{-- Current / preview photo --}}
                <div style="width:72px;height:72px;border-radius:50%;overflow:hidden;border:2px solid var(--c-border);background:var(--c-accent-bg);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:var(--c-accent);">
                    @if($newPhoto)
                        <img src="{{ $newPhoto->temporaryUrl() }}"
                             style="width:100%;height:100%;object-fit:cover;" alt="Preview">
                    @elseif($student->photo)
                        <img src="{{ Storage::url($student->photo) }}"
                             style="width:100%;height:100%;object-fit:cover;" alt="{{ $student->full_name }}"
                             onerror="this.style.display='none'">
                    @else
                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                    @endif
                </div>

                {{-- Upload controls --}}
                <div style="flex:1;min-width:160px;">
                    <label style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid var(--c-border);border-radius:8px;font-size:12px;font-weight:500;color:var(--c-text-2);cursor:pointer;background:var(--c-surface);transition:background 150ms;"
                           onmouseover="this.style.background='var(--c-bg)'"
                           onmouseout="this.style.background='var(--c-surface)'">
                        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M8 2v9M4 6l4-4 4 4"/><path d="M2 13h12"/>
                        </svg>
                        {{ $newPhoto ? 'Change Photo' : ($student->photo ? 'Replace Photo' : 'Upload Photo') }}
                        <input type="file" wire:model="newPhoto" accept="image/jpeg,image/png,image/webp"
                               style="display:none;">
                    </label>
                    <div style="font-size:11px;color:var(--c-text-3);margin-top:5px;">
                        JPG, PNG or WebP · max 2MB
                    </div>
                    @error('newPhoto')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                    @if($student->photo && !$newPhoto)
                        <button type="button" wire:click="removePhoto"
                            wire:confirm="Remove this student's photo?"
                            style="margin-top:6px;background:none;border:none;font-size:11px;color:var(--c-danger);cursor:pointer;font-family:var(--f-sans);padding:0;">
                            Remove photo
                        </button>
                    @endif
                </div>
            </div>
        </div>
            <label>First Name <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="firstName" placeholder="First name">
            @error('firstName') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Last Name <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="lastName" placeholder="Last name">
            @error('lastName') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Other Name</label>
            <input type="text" wire:model="otherName" placeholder="Middle name (optional)">
            @error('otherName') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Gender <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="gender">
                <option value="">Select gender…</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
            @error('gender') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Date of Birth</label>
            <input type="date" wire:model="dateOfBirth">
            @error('dateOfBirth') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Status <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="status">
                <option value="pending">Pending</option>
                <option value="active">Active</option>
                <option value="withdrawn">Withdrawn</option>
            </select>
            @error('status') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Class Applied For</label>
            <input type="text" wire:model="classAppliedFor" placeholder="e.g. Primary 3">
            @error('classAppliedFor') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field span-2">
            <label>General Notes</label>
            <textarea wire:model="notes" placeholder="Any general notes about this student…"></textarea>
            @error('notes') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field span-2">
            <label>Medical / Health Notes</label>
            <textarea wire:model="medicalNotes" placeholder="Allergies, conditions, or health information…"></textarea>
            @error('medicalNotes') <div class="field-error">{{ $message }}</div> @enderror
        </div>

    </div>

    <div class="edit-actions">
        <button class="btn-cancel-edit" wire:click="cancelEdit">Cancel</button>
        <button class="btn-save" wire:click="saveEdit"
            wire:loading.attr="disabled" wire:loading.class="opacity-50">
            <span wire:loading.remove>Save Changes</span>
            <span wire:loading>Saving…</span>
        </button>
    </div>
</div>
@endif

{{-- ── Info grid (read-only) ── --}}
<div class="profile-grid">

    {{-- Student Details --}}
    <div class="info-card">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <circle cx="8" cy="5" r="3"/><path d="M2 14c0-3.314 2.686-5 6-5s6 1.686 6 5"/>
            </svg>
            Student Details
        </div>
        <div class="info-rows">
            <div class="info-row"><span class="info-key">First Name</span><span class="info-val">{{ $student->first_name }}</span></div>
            <div class="info-row"><span class="info-key">Last Name</span><span class="info-val">{{ $student->last_name }}</span></div>
            @if($student->other_name)
            <div class="info-row"><span class="info-key">Other Name</span><span class="info-val">{{ $student->other_name }}</span></div>
            @endif
            <div class="info-row"><span class="info-key">Gender</span><span class="info-val">{{ $student->gender }}</span></div>
            <div class="info-row"><span class="info-key">Date of Birth</span><span class="info-val">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</span></div>
            <div class="info-row"><span class="info-key">Class Applied For</span><span class="info-val">{{ $student->class_applied_for ?? '—' }}</span></div>
            <div class="info-row">
                <span class="info-key">Status</span>
                <span class="info-val">
                    <span class="badge badge-{{ $student->status }}">
                        <span class="badge-dot"></span>{{ ucfirst($student->status) }}
                    </span>
                </span>
            </div>
            <div class="info-row"><span class="info-key">Submitted</span><span class="info-val mono">{{ $student->created_at->format('d M Y, g:ia') }}</span></div>
            @if($student->approved_at)
            <div class="info-row"><span class="info-key">Approved</span><span class="info-val mono">{{ $student->approved_at->format('d M Y, g:ia') }}</span></div>
            @endif
            @if($student->notes)
            <div class="info-row"><span class="info-key">Notes</span><span class="info-val" style="font-size:12px;color:var(--c-text-2)">{{ $student->notes }}</span></div>
            @endif
        </div>
    </div>

    {{-- Emergency Contact --}}
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
                <div class="info-row"><span class="info-key">Name</span><span class="info-val">{{ $primaryParent->emergency_contact_name }}</span></div>
                <div class="info-row"><span class="info-key">Phone</span><span class="info-val mono">{{ $primaryParent->emergency_contact_phone ?? '—' }}</span></div>
                <div class="info-row"><span class="info-key">Relationship</span><span class="info-val">{{ $primaryParent->emergency_contact_relationship ?? '—' }}</span></div>
            @else
                <div class="empty-note">No emergency contact on file.</div>
            @endif
        </div>

        @if($student->medical_notes)
            <div style="padding:14px 18px;border-top:1px solid var(--c-border)">
                <div style="font-size:11px;font-weight:600;color:var(--c-text-3);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px">Medical / Health Notes</div>
                <div class="medical-alert">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" style="flex-shrink:0;margin-top:1px">
                        <circle cx="8" cy="8" r="7"/><path d="M8 5v3M8 11h.01"/>
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
                <circle cx="6" cy="5" r="2.5"/><path d="M1 14c0-2.761 2.239-4.5 5-4.5s5 1.739 5 4.5"/>
                <path d="M11 7.5c.828 0 1.5-.672 1.5-1.5S11.828 4.5 11 4.5M15 14c0-2-1.343-3.5-4-3.5"/>
            </svg>
            Parents / Guardians ({{ $student->parents->count() }})
        </div>
        <div style="padding:16px 18px;display:flex;flex-direction:column;gap:12px;">
            @forelse($student->parents as $parent)
                <div class="parent-card">
                    <div class="parent-head">
                        <div>
                            <div class="parent-name">{{ $parent->_temp_name ?? $parent->user?->name ?? '—' }}</div>
                            <div class="parent-rel">
                                {{ $parent->pivot->relationship ?? $parent->relationship ?? 'Guardian' }}
                                @if($parent->pivot->is_primary_contact)
                                    · <span style="color:var(--c-accent)">Primary Contact</span>
                                @endif
                            </div>
                        </div>
                        @if($parent->user_id)
                            <span class="badge badge-active" style="font-size:10px">Portal Active</span>
                        @else
                            <span class="badge badge-pending" style="font-size:10px">Pending Approval</span>
                        @endif
                    </div>
                    <div class="info-rows">
                        <div class="info-row"><span class="info-key">Email</span><span class="info-val mono">{{ $parent->_temp_email ?? $parent->user?->email ?? '—' }}</span></div>
                        <div class="info-row"><span class="info-key">Phone</span><span class="info-val mono">{{ $parent->phone ?? '—' }}</span></div>
                        @if($parent->address)
                        <div class="info-row"><span class="info-key">Address</span><span class="info-val">{{ $parent->address }}</span></div>
                        @endif
                        @if($parent->occupation)
                        <div class="info-row"><span class="info-key">Occupation</span><span class="info-val">{{ $parent->occupation }}</span></div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-note">No parent records found.</div>
            @endforelse
        </div>
    </div>

    {{-- Payment Account --}}
    <div class="info-card full-width">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="1" y="3" width="14" height="10" rx="1.5"/><path d="M1 6h14M5 10h2"/>
            </svg>
            School Fees Payment Account
            @php
                $paymentParent = $student->parents->filter(fn($p) => $p->user !== null)->first();
            @endphp
            @if($paymentParent && $paymentParent->hasVirtualAccount())
                <span style="margin-left:auto;background:rgba(21,128,61,0.08);color:#15803D;font-size:10px;font-weight:600;padding:2px 9px;border-radius:10px;">Active</span>
            @elseif($paymentParent && $paymentParent->isWalletProvisioning())
                <span style="margin-left:auto;background:rgba(180,83,9,0.08);color:#B45309;font-size:10px;font-weight:600;padding:2px 9px;border-radius:10px;">Provisioning…</span>
            @elseif($paymentParent && $paymentParent->isWalletFailed())
                <span style="margin-left:auto;background:rgba(190,18,60,0.08);color:var(--c-danger);font-size:10px;font-weight:600;padding:2px 9px;border-radius:10px;">Failed</span>
            @elseif($paymentParent && ! $paymentParent->hasVirtualAccount())
                <button wire:click="provisionWallet"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                    style="margin-left:auto;padding:5px 12px;border:1px solid var(--c-accent);border-radius:6px;background:none;font-family:var(--f-sans);font-size:11px;font-weight:600;cursor:pointer;color:var(--c-accent);">
                    <span wire:loading.remove wire:target="provisionWallet">⚡ Provision Account</span>
                    <span wire:loading wire:target="provisionWallet">Queuing…</span>
                </button>
            @endif
        </div>

        @if($paymentParent && $paymentParent->hasVirtualAccount())
            <div style="padding:16px 18px;">
                <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:8px;overflow:hidden;" x-data>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid var(--c-border);font-size:13px;">
                        <span style="color:var(--c-text-3);font-size:12px;">Bank</span>
                        <span style="font-weight:600;">{{ $paymentParent->active_bank_name }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid var(--c-border);font-size:13px;">
                        <span style="color:var(--c-text-3);font-size:12px;">Account Number</span>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-weight:700;font-family:var(--f-mono);font-size:14px;">{{ $paymentParent->active_account_number }}</span>
                            <button style="padding:3px 9px;border:1px solid var(--c-border);border-radius:5px;background:var(--c-surface);font-size:11px;font-weight:500;cursor:pointer;font-family:var(--f-sans);color:var(--c-text-2);"
                                x-on:click="navigator.clipboard.writeText('{{ $paymentParent->active_account_number }}');
                                            $el.textContent='Copied!';
                                            setTimeout(()=>$el.textContent='Copy',2000)">
                                Copy
                            </button>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid var(--c-border);font-size:13px;">
                        <span style="color:var(--c-text-3);font-size:12px;">Account Name</span>
                        <span style="font-weight:600;">{{ $student->full_name }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;font-size:13px;">
                        <span style="color:var(--c-text-3);font-size:12px;">Parent</span>
                        <span style="color:var(--c-text-2);">{{ $paymentParent->user?->name }}</span>
                    </div>
                </div>
                <p style="font-size:11px;color:var(--c-text-3);margin-top:8px;line-height:1.4;">
                    Permanent account — reusable for all future fee payments every term.
                </p>
            </div>

        @elseif($paymentParent && $paymentParent->isWalletProvisioning())
            <div style="padding:16px 18px;">
                <div style="background:rgba(180,83,9,0.05);border:1px solid rgba(180,83,9,0.2);border-radius:8px;padding:12px 14px;font-size:13px;color:#B45309;line-height:1.5;">
                    ⏳ Provisioning in progress — usually takes under 2 minutes. Refresh to check.
                </div>
            </div>

        @elseif($paymentParent && $paymentParent->isWalletFailed())
            <div style="padding:16px 18px;">
                <div style="background:rgba(190,18,60,0.05);border:1px solid rgba(190,18,60,0.2);border-radius:8px;padding:12px 14px;font-size:13px;color:var(--c-danger);line-height:1.5;margin-bottom:10px;">
                    Provisioning failed. Check the queue logs, then retry below.
                </div>
                <button wire:click="provisionWallet"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                    style="padding:8px 16px;border:1px solid var(--c-border);border-radius:7px;background:none;font-family:var(--f-sans);font-size:12px;font-weight:500;cursor:pointer;color:var(--c-text-1);">
                    <span wire:loading.remove wire:target="provisionWallet">↺ Retry Provisioning</span>
                    <span wire:loading wire:target="provisionWallet">Queuing…</span>
                </button>
            </div>

        @elseif(! $paymentParent)
            <div style="padding:16px 18px;">
                <div class="empty-note">No parent portal account. Approve the enrolment first — provisioning runs automatically on approval.</div>
            </div>

        @else
            {{-- Has parent account but no NUBAN yet — show provision button in body too --}}
            <div style="padding:16px 18px;">
                <p style="font-size:13px;color:var(--c-text-2);margin-bottom:12px;line-height:1.5;">
                    No virtual account yet. Click the button above to provision one now, or it will be created automatically the next time an invoice is sent.
                </p>
            </div>
        @endif
    </div>

    {{-- Enrolment History --}}
    <div class="info-card full-width">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="1" y="2" width="14" height="13" rx="1.5"/><path d="M1 6h14M5 1v2M11 1v2"/>
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
                            <th>Session</th><th>Class</th><th>Enrolled On</th><th>Status</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($student->enrolments as $enrolment)
                            <tr>
                                <td>{{ $enrolment->session?->name ?? '—' }}</td>
                                <td>{{ $enrolment->schoolClass?->display_name ?? '—' }}</td>
                                <td style="font-family:var(--f-mono);font-size:12px">{{ $enrolment->enrolled_at?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $enrolment->status }}">
                                        <span class="badge-dot"></span>{{ ucfirst($enrolment->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($enrolment->status === 'active')
                                        <button
                                            wire:click="openClassModal({{ $enrolment->id }})"
                                            style="padding:4px 9px;border-radius:6px;font-size:11px;font-weight:500;border:1px solid var(--c-border);background:none;cursor:pointer;font-family:var(--f-sans);color:var(--c-accent);white-space:nowrap;">
                                            Change Class
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ── Fee Summary ── --}}
    <div class="info-card full-width">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="1" y="3" width="14" height="10" rx="1.5"/><path d="M1 6h14M5 10h2"/>
            </svg>
            Fee Summary
            @if($invoices->isNotEmpty())
                <a href="{{ route('admin.fees.invoices') }}?search={{ $student->admission_number }}"
                   style="margin-left:auto;font-size:11px;color:var(--c-accent);text-decoration:none;font-weight:400;">
                    All Invoices →
                </a>
            @endif
        </div>

        @if($invoices->isEmpty())
            <div class="empty-note">No fee invoices generated for this student yet.</div>
        @else
            {{-- Lifetime totals strip --}}
            <div class="fee-snapshot">
                <div class="fee-snap-item">
                    <div class="fee-snap-label">Total Billed</div>
                    <div class="fee-snap-value neutral">₦{{ number_format($feeSummary['total_billed'], 0) }}</div>
                </div>
                <div class="fee-snap-item">
                    <div class="fee-snap-label">Total Paid</div>
                    <div class="fee-snap-value success">₦{{ number_format($feeSummary['total_paid'], 0) }}</div>
                </div>
                <div class="fee-snap-item">
                    <div class="fee-snap-label">Outstanding</div>
                    <div class="fee-snap-value {{ $feeSummary['total_outstanding'] > 0 ? 'danger' : 'success' }}">
                        ₦{{ number_format($feeSummary['total_outstanding'], 0) }}
                    </div>
                </div>
            </div>

            {{-- Current term callout --}}
            @if($currentInvoice)
                @php $ci = $currentInvoice; @endphp
                <div style="padding:12px 18px;border-top:1px solid var(--c-border);border-bottom:1px solid var(--c-border);background:{{ $ci->balance > 0 ? 'rgba(190,18,60,0.03)' : 'rgba(21,128,61,0.03)' }}">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                        <div>
                            <div style="font-size:12px;font-weight:600;color:var(--c-text-2);">
                                Current Term: {{ $ci->term->name }} — {{ $ci->term->session->name }}
                            </div>
                            <div style="font-size:11px;color:var(--c-text-3);margin-top:2px;">
                                {{ $ci->items->count() }} {{ Str::plural('item', $ci->items->count()) }}
                                @if($ci->isSent()) · Sent {{ $ci->sent_at->format('d M Y') }}
                                @else · <span style="color:#B45309">Draft — not sent yet</span>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:16px;">
                            <div style="text-align:right;">
                                <div style="font-size:10px;font-weight:600;color:var(--c-text-3);text-transform:uppercase;letter-spacing:0.06em;">Balance</div>
                                <div style="font-size:18px;font-weight:700;font-family:var(--f-mono);color:{{ $ci->balance > 0 ? 'var(--c-danger)' : '#15803D' }}">
                                    ₦{{ number_format($ci->balance, 0) }}
                                </div>
                            </div>
                            <span class="badge badge-{{ $ci->status }}">
                                <span class="badge-dot"></span>{{ ucfirst($ci->status) }}
                            </span>
                            <a href="{{ route('admin.fees.invoices.show', $ci) }}"
                               style="padding:6px 12px;border:1px solid var(--c-border);border-radius:7px;font-size:11px;font-weight:500;color:var(--c-accent);text-decoration:none;background:var(--c-surface);">
                                View Invoice →
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Invoice history by term --}}
            @foreach($invoices as $invoice)
                @if($currentInvoice && $invoice->id === $currentInvoice->id) @continue @endif
                <a href="{{ route('admin.fees.invoices.show', $invoice) }}" class="inv-row">
                    <div class="inv-row-term">
                        <div class="inv-row-name">{{ $invoice->term->name }} — {{ $invoice->term->session->name }}</div>
                        <div class="inv-row-meta">
                            {{ $invoice->items->count() }} items
                            @if($invoice->isSent()) · Sent @else · Draft @endif
                        </div>
                    </div>
                    <div class="inv-row-amount">
                        <div style="color:var(--c-text-3);font-size:10px;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">Total</div>
                        ₦{{ number_format($invoice->total_amount, 0) }}
                    </div>
                    <div class="inv-row-amount">
                        <div style="color:var(--c-text-3);font-size:10px;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">Balance</div>
                        <span style="color:{{ $invoice->balance > 0 ? 'var(--c-danger)' : '#15803D' }}">
                            ₦{{ number_format($invoice->balance, 0) }}
                        </span>
                    </div>
                    <span class="badge badge-{{ $invoice->status }}" style="flex-shrink:0;">
                        <span class="badge-dot"></span>{{ ucfirst($invoice->status) }}
                    </span>
                </a>
            @endforeach
        @endif
    </div>

    {{-- ── Published Results Snapshot ── --}}
    @php
        $publishedResults = $student->results
            ->where('is_published', true)
            ->sortByDesc('term_id')
            ->groupBy('term_id');
    @endphp

    @if($publishedResults->isNotEmpty())
    <div class="info-card full-width">
        <div class="info-card-head">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
                <path d="M12 2H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                <path d="M5 6h6M5 9h6M5 12h3"/>
            </svg>
            Published Results
        </div>
        @foreach($publishedResults as $termId => $termResults)
            @php
                $term    = $termResults->first()->term;
                $average = $termResults->count() > 0
                    ? round($termResults->sum('total') / $termResults->count(), 1)
                    : 0;
            @endphp
            <div style="padding:10px 18px;border-bottom:1px solid var(--c-border);background:var(--c-bg);">
                <span style="font-size:11px;font-weight:600;color:var(--c-text-2);">
                    {{ $term->name }} Term — {{ $term->session->name }}
                </span>
                <span style="font-size:11px;color:var(--c-text-3);margin-left:10px;">
                    Average: <strong style="color:var(--c-text-1)">{{ $average }}%</strong>
                    · {{ $termResults->count() }} subjects
                </span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0;border-bottom:1px solid var(--c-border);">
                @foreach($termResults->sortBy('subject.name') as $result)
                    <div style="padding:10px 18px;border-right:1px solid var(--c-border);border-bottom:1px solid var(--c-border);">
                        <div style="font-size:11px;color:var(--c-text-3);">{{ $result->subject->name }}</div>
                        <div style="display:flex;align-items:center;gap:6px;margin-top:3px;">
                            <span style="font-size:14px;font-weight:700;font-family:var(--f-mono);">{{ $result->total }}</span>
                            @if($result->grade)
                                <span class="grade grade-{{ $result->grade[0] }}">{{ $result->grade }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    @endif

</div>

{{-- Approve enrolment modal --}}
@if($showApproveModal)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Approve Enrolment</div>
        <div style="font-size:13px;color:var(--c-text-2);margin-bottom:16px;">
            Approving <strong>{{ $student->full_name }}</strong>.
            Assign a class and confirm the admission number.
        </div>

        <div class="form-field">
            <label>Assign Class <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="assignedClass" style="width:100%;padding:10px 12px;border:1px solid var(--c-border);border-radius:8px;font-family:var(--f-sans);font-size:14px;color:var(--c-text-1);background:var(--c-bg);outline:none;-webkit-appearance:none;">
                <option value="">Select class...</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                @endforeach
            </select>
            @error('assignedClass') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Admission Number <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="admissionNumber"
                style="width:100%;padding:10px 12px;border:1px solid var(--c-border);border-radius:8px;font-family:var(--f-mono);font-size:14px;color:var(--c-text-1);background:var(--c-bg);outline:none;box-sizing:border-box;"
                placeholder="e.g. NV/2026/0080">
            @error('admissionNumber') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showApproveModal', false)">Cancel</button>
            <button class="btn-confirm" wire:click="confirmApproval"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="confirmApproval">Confirm & Approve</span>
                <span wire:loading wire:target="confirmApproval">Approving...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Reject enrolment modal --}}
@if($showRejectModal)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Reject Enrolment</div>
        <div style="font-size:13px;color:var(--c-text-2);margin-bottom:16px;">
            Rejecting <strong>{{ $student->full_name }}</strong>.
            Please provide a reason — it will be included in the email sent to the parent.
        </div>

        <div class="form-field">
            <label>Reason for Rejection <span style="color:var(--c-danger)">*</span></label>
            <textarea wire:model="rejectionReason" rows="4"
                style="width:100%;padding:10px 12px;border:1px solid var(--c-border);border-radius:8px;font-family:var(--f-sans);font-size:14px;color:var(--c-text-1);background:var(--c-bg);outline:none;box-sizing:border-box;resize:vertical;"
                placeholder="e.g. No available space in the requested class for this term..."></textarea>
            @error('rejectionReason') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showRejectModal', false)">Cancel</button>
            <button style="padding:10px 20px;background:var(--c-danger);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--f-sans);transition:opacity 150ms;"
                wire:click="confirmRejection"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="confirmRejection">Confirm Rejection</span>
                <span wire:loading wire:target="confirmRejection">Sending...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Class change modal --}}
@if($showClassModal)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Change Class</div>
        <div style="font-size:13px;color:var(--c-text-2);margin-bottom:16px;">
            Moving <strong>{{ $student->full_name }}</strong> to a different class.
            This updates their active enrolment record immediately.
        </div>

        <div class="form-field">
            <label>New Class <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="newClassId" style="width:100%;padding:10px 12px;border:1px solid var(--c-border);border-radius:8px;font-family:var(--f-sans);font-size:14px;color:var(--c-text-1);background:var(--c-bg);outline:none;-webkit-appearance:none;">
                <option value="">Select a class…</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                @endforeach
            </select>
            @error('newClassId') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showClassModal', false)">Cancel</button>
            <button class="btn-confirm" wire:click="saveClassChange"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>Move Student</span>
                <span wire:loading>Saving…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Invoice creation modal --}}
@if($showInvoiceModal)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">Generate Invoice</div>
        <div style="font-size:13px;color:var(--c-text-2);margin-bottom:16px;">
            Creating a draft invoice for <strong>{{ $student->full_name }}</strong>.
            No email is sent until you choose to send it from the invoices page.
        </div>

        <div class="form-field">
            <label>Term <span style="color:var(--c-danger)">*</span></label>
            <select wire:model.live="invoiceTermId" style="width:100%;padding:10px 12px;border:1px solid var(--c-border);border-radius:8px;font-family:var(--f-sans);font-size:14px;color:var(--c-text-1);background:var(--c-bg);outline:none;-webkit-appearance:none;">
                <option value="">Select a term…</option>
                @foreach($terms as $term)
                    <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
                @endforeach
            </select>
            @error('invoiceTermId') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        @if($invoiceTermId)
            @if($invoicePreview === 'already_exists')
                <div class="inv-info">
                    ✓ An invoice already exists for this student for the selected term.
                    You can view and edit it from the invoices page.
                </div>
            @elseif($invoicePreview === 'no_fee_structure')
                <div class="inv-warning">
                    ⚠ No fee structure is configured for this student's class in the selected term.
                    Set up the fee structure first under <strong>Finance → Fee Structure</strong>.
                </div>
            @elseif(is_array($invoicePreview))
                <div style="font-size:12px;font-weight:600;color:var(--c-text-3);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">
                    Fee Breakdown Preview
                </div>
                <div class="inv-preview-items">
                    @foreach($invoicePreview['items'] as $item)
                        <div class="inv-preview-row">
                            <span>{{ $item['name'] }}</span>
                            <span style="font-family:var(--f-mono);font-size:12px;">
                                ₦{{ number_format($item['amount'], 0) }}
                            </span>
                        </div>
                    @endforeach
                    <div class="inv-preview-total">
                        <span>Total</span>
                        <span style="font-family:var(--f-mono);">
                            ₦{{ number_format($invoicePreview['total'], 0) }}
                        </span>
                    </div>
                </div>
            @endif
        @endif

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showInvoiceModal', false)">Cancel</button>
            @if(is_array($invoicePreview))
                <button class="btn-confirm" wire:click="createInvoice"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50">
                    <span wire:loading.remove>Create Draft Invoice</span>
                    <span wire:loading>Creating…</span>
                </button>
            @endif
        </div>
    </div>
</div>
@endif

</div>
