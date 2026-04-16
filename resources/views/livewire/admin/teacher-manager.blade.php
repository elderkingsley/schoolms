<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }

.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.08); border:1px solid rgba(190,18,60,0.2); color:#BE123C; }

/* Tabs */
.tabs { display:flex; gap:2px; border-bottom:1px solid var(--c-border); margin-bottom:20px; }
.tab-btn { padding:9px 16px; font-size:13px; font-weight:500; border:none; background:none;
           cursor:pointer; font-family:var(--f-sans); color:var(--c-text-3);
           border-bottom:2px solid transparent; margin-bottom:-1px; transition:all 0.15s; display:flex; align-items:center; gap:6px; }
.tab-btn:hover { color:var(--c-text-1); }
.tab-btn.active { color:var(--c-accent); border-bottom-color:var(--c-accent); }
.tab-badge { background:var(--c-accent); color:#fff; border-radius:20px; font-size:10px;
             font-weight:700; padding:1px 6px; line-height:1.6; }

.btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px;
       font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); border:none; }
.btn:hover { opacity:0.85; }
.btn-primary { background:var(--c-accent); color:#fff; }

.toolbar { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
.search-wrap { position:relative; flex:1; min-width:200px; max-width:320px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--c-border);
                     border-radius:8px; font-size:13px; font-family:var(--f-sans);
                     background:var(--c-surface); outline:none; color:var(--c-text-1); }
.search-wrap input:focus { border-color:var(--c-accent); }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase;
                 letter-spacing:0.07em; padding:10px 18px; text-align:left;
                 background:var(--c-bg); border-bottom:1px solid var(--c-border); white-space:nowrap; }
.data-table td { padding:12px 18px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }
.mono { font-family:var(--f-mono); font-size:12px; }

.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-active     { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-inactive   { background:rgba(100,100,100,0.08); color:#666; }
.badge-teacher    { background:rgba(99,102,241,0.1); color:#6366F1; }
.badge-ta         { background:rgba(245,158,11,0.1); color:#D97706; }
.badge-pending    { background:rgba(245,158,11,0.1); color:#D97706; }
.badge-approved   { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-rejected   { background:rgba(190,18,60,0.08); color:#BE123C; }

.user-av { width:34px; height:34px; border-radius:50%; background:var(--c-accent-bg);
           display:flex; align-items:center; justify-content:center;
           font-size:12px; font-weight:700; color:var(--c-accent); flex-shrink:0; }
.user-av.ta { background:rgba(245,158,11,0.1); color:#D97706; }

.row-actions { display:flex; gap:5px; flex-wrap:wrap; }
.btn-sm { padding:4px 9px; border-radius:6px; font-size:11px; font-weight:500;
          border:1px solid var(--c-border); background:none; cursor:pointer;
          font-family:var(--f-sans); white-space:nowrap; color:var(--c-text-2); }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }
.btn-sm-green  { color:#15803D; border-color:rgba(21,128,61,0.3); }
.btn-sm-green:hover { background:rgba(21,128,61,0.06); }
.btn-sm-impersonate { background:#1A56FF; color:#fff; border:none; }
.btn-sm-impersonate:hover { background:#1E40AF; opacity:1; }

.empty-state { padding:40px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.pag-wrap { padding:14px 18px; border-top:1px solid var(--c-border); }

/* Modal */
.modal-bg { position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:100;
            display:flex; align-items:center; justify-content:center; padding:20px; }
.modal { background:var(--c-surface); border-radius:var(--r-md); width:100%; max-width:500px;
         padding:24px; box-shadow:0 20px 60px rgba(0,0,0,0.15); max-height:90vh; overflow-y:auto; }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); margin-bottom:18px; letter-spacing:-0.02em; }
.modal-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:20px; }

.form-group { margin-bottom:14px; }
.form-label { display:block; font-size:12px; font-weight:600; color:var(--c-text-2); margin-bottom:5px; }
.form-input { width:100%; padding:9px 12px; border:1px solid var(--c-border); border-radius:8px;
              font-size:13px; font-family:var(--f-sans); background:var(--c-surface);
              outline:none; color:var(--c-text-1); }
.form-input:focus { border-color:var(--c-accent); }
.form-select { appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%23888' d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 10px center; padding-right:28px; }
.form-error { font-size:11px; color:var(--c-danger); margin-top:4px; }
.form-hint  { font-size:11px; color:var(--c-text-3); margin-top:4px; }

.btn-cancel  { padding:8px 14px; border-radius:8px; font-size:13px; font-weight:500;
               border:1px solid var(--c-border); background:none; cursor:pointer;
               font-family:var(--f-sans); color:var(--c-text-2); }
.btn-cancel:hover { background:var(--c-bg); }
.btn-confirm { padding:8px 14px; border-radius:8px; font-size:13px; font-weight:500;
               border:none; background:var(--c-accent); color:#fff; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm:hover { opacity:0.85; }
.btn-danger  { padding:8px 14px; border-radius:8px; font-size:13px; font-weight:500;
               border:none; background:#BE123C; color:#fff; cursor:pointer; font-family:var(--f-sans); }
.btn-danger:hover { opacity:0.85; }

.divider { border:none; border-top:1px solid var(--c-border); margin:16px 0; }
.section-label { font-size:11px; font-weight:600; color:var(--c-text-3);
                 text-transform:uppercase; letter-spacing:0.07em; margin-bottom:10px; }

/* Role toggle */
.role-toggle { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px; }
.role-opt { border:2px solid var(--c-border); border-radius:8px; padding:10px 12px;
            cursor:pointer; transition:all 0.15s; text-align:center; }
.role-opt:hover { border-color:var(--c-accent); }
.role-opt.selected { border-color:var(--c-accent); background:rgba(26,86,255,0.05); }
.role-opt-label { font-size:12px; font-weight:600; color:var(--c-text-1); }
.role-opt-sub   { font-size:11px; color:var(--c-text-3); margin-top:1px; }

/* Registration card */
.reg-card { padding:16px 18px; border-bottom:1px solid var(--c-border); }
.reg-card:last-child { border-bottom:none; }
.reg-card:hover { background:#fafaf8; }
.reg-header { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.reg-name { font-size:14px; font-weight:600; color:var(--c-text-1); }
.reg-meta { font-size:12px; color:var(--c-text-3); margin-top:2px; display:flex; gap:10px; flex-wrap:wrap; }
.reg-notes { font-size:12px; color:var(--c-text-2); margin-top:8px; background:var(--c-bg);
             border-radius:6px; padding:8px 10px; line-height:1.5; }
.reg-actions { display:flex; gap:6px; margin-top:10px; }

@media(max-width:640px) { .hide-sm { display:none; } }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">✗ {{ session('error') }}</div>
@endif

<div class="pg-header">
    <div>
        <div class="pg-title">Staff</div>
        <div class="pg-sub">Teachers and teaching assistants</div>
    </div>
    @if($activeTab === 'staff')
        <button class="btn btn-primary" wire:click="openCreate">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v12M2 8h12"/></svg>
            Add Staff
        </button>
    @endif
</div>

{{-- Tabs --}}
<div class="tabs">
    <button class="tab-btn {{ $activeTab === 'staff' ? 'active' : '' }}"
        wire:click="switchTab('staff')">
        Staff List
    </button>
    <button class="tab-btn {{ $activeTab === 'registrations' ? 'active' : '' }}"
        wire:click="switchTab('registrations')">
        Registrations
        @if($pendingCount > 0)
            <span class="tab-badge">{{ $pendingCount }}</span>
        @endif
    </button>
</div>

{{-- ══ TAB: STAFF LIST ══════════════════════════════════════════════════════ --}}
@if($activeTab === 'staff')

    <div class="toolbar">
        <div class="search-wrap">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
            </svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, email or phone…">
        </div>
    </div>

    <div class="panel">
        @if($staff->isEmpty())
            <div class="empty-state">
                {{ $search ? 'No staff match your search.' : 'No staff yet — click "Add Staff" to get started.' }}
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th class="hide-sm">Email</th>
                        <th class="hide-sm">Form Class</th>
                        <th class="hide-sm">TA Class</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $member)
                        @php
                            $isTA       = $member->user_type === 'teaching_assistant';
                            $formClass  = $member->formClasses->first();
                            $taClass    = $member->assistantClasses->first();
                        @endphp
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div class="user-av {{ $isTA ? 'ta' : '' }}">
                                        {{ strtoupper(substr($member->name,0,1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:var(--c-text-1);">{{ $member->name }}</div>
                                        <div class="mono" style="font-size:11px;color:var(--c-text-3);">{{ $member->phone ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $isTA ? 'badge-ta' : 'badge-teacher' }}">
                                    {{ $isTA ? 'Teaching Asst.' : 'Teacher' }}
                                </span>
                            </td>
                            <td class="hide-sm mono" style="color:var(--c-text-2);font-size:12px;">{{ $member->email }}</td>
                            <td class="hide-sm">
                                @if($formClass)
                                    <span style="font-size:12px;font-weight:500;">{{ $formClass->display_name }}</span>
                                @else
                                    <span style="color:var(--c-text-3);font-size:12px;">—</span>
                                @endif
                            </td>
                            <td class="hide-sm">
                                @if($taClass)
                                    <span style="font-size:12px;font-weight:500;">{{ $taClass->display_name }}</span>
                                @else
                                    <span style="color:var(--c-text-3);font-size:12px;">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $member->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    <span class="badge-dot"></span>
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    {{-- IMPERSONATE BUTTON - Now styled like other buttons --}}
                                    <form method="POST" action="{{ route('admin.impersonate.start', $member) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit"
                                                class="btn-sm"
                                                style="color:#afbfebde; border-color:#afbfebde;"
                                                onclick="return confirm('Login as {{ $member->name }}?\n\nYou will be able to view and edit all their classes and student data. Click OK to continue.')"
                                                title="Login as this teacher">
                                            👤 Login As
                                        </button>
                                    </form>

                                    <button class="btn-sm" wire:click="openEdit({{ $member->id }})">Edit</button>
                                    @if(auth()->user()->isSuperAdmin())
                                        <button class="btn-sm"
                                            wire:click="$set('confirmingResetId', {{ $member->id }})">
                                            Reset PW
                                        </button>
                                        <button class="btn-sm {{ $member->is_active ? 'btn-sm-danger' : 'btn-sm-green' }}"
                                            wire:click="$set('confirmingToggleId', {{ $member->id }})">
                                            {{ $member->is_active ? 'Deactivate' : 'Reactivate' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if($staff->hasPages())
                <div class="pag-wrap">{{ $staff->links() }}</div>
            @endif
        @endif
    </div>

{{-- ══ TAB: REGISTRATIONS ══════════════════════════════════════════════════ --}}
@elseif($activeTab === 'registrations')

    <div class="panel">
        @if($registrations->isEmpty())
            <div class="empty-state">No registration applications yet.</div>
        @else
            @foreach($registrations as $reg)
                <div class="reg-card">
                    <div class="reg-header">
                        <div>
                            <div class="reg-name">{{ $reg->name }}</div>
                            <div class="reg-meta">
                                <span>{{ $reg->email }}</span>
                                @if($reg->phone) <span>{{ $reg->phone }}</span> @endif
                                <span>{{ $reg->role_label }}</span>
                                <span>Applied {{ $reg->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <span class="badge badge-{{ $reg->status }}">
                            <span class="badge-dot"></span>
                            {{ ucfirst($reg->status) }}
                        </span>
                    </div>

                    @if($reg->notes)
                        <div class="reg-notes">{{ $reg->notes }}</div>
                    @endif

                    @if($reg->isRejected() && $reg->rejection_reason)
                        <div class="reg-notes" style="background:rgba(190,18,60,0.05);color:#BE123C;">
                            Rejection reason: {{ $reg->rejection_reason }}
                        </div>
                    @endif

                    @if($reg->isPending())
                        <div class="reg-actions">
                            <button class="btn-sm btn-sm-green"
                                wire:click="approveRegistration({{ $reg->id }})"
                                wire:confirm="Approve {{ $reg->name }} as {{ $reg->role_label }}? This will create their portal account and email credentials.">
                                ✓ Approve
                            </button>
                            <button class="btn-sm btn-sm-danger"
                                wire:click="openRejectForm({{ $reg->id }})">
                                ✗ Reject
                            </button>
                        </div>
                    @elseif($reg->isApproved())
                        <div style="font-size:11px;color:#15803D;margin-top:8px;">
                            Approved by {{ $reg->reviewer?->name ?? 'Admin' }} — {{ $reg->reviewed_at?->format('d M Y') }}
                        </div>
                    @endif
                </div>
            @endforeach
            @if($registrations->hasPages())
                <div class="pag-wrap">{{ $registrations->links() }}</div>
            @endif
        @endif
    </div>

@endif

{{-- ══ MODALS ══════════════════════════════════════════════════════════════ --}}

{{-- Add/Edit Staff --}}
@if($showForm)
    <div class="modal-bg" wire:click.self="$set('showForm', false)">
        <div class="modal">
            <div class="modal-title">{{ $editingId ? 'Edit Staff Member' : 'Add Staff Member' }}</div>

            <div class="section-label">Role</div>
            <div class="role-toggle">
                <div class="role-opt {{ $staffRole === 'teacher' ? 'selected' : '' }}"
                     wire:click="$set('staffRole', 'teacher')">
                    <div class="role-opt-label">Teacher</div>
                    <div class="role-opt-sub">Lead class teacher</div>
                </div>
                <div class="role-opt {{ $staffRole === 'teaching_assistant' ? 'selected' : '' }}"
                     wire:click="$set('staffRole', 'teaching_assistant')">
                    <div class="role-opt-label">Teaching Assistant</div>
                    <div class="role-opt-sub">Support role in class</div>
                </div>
            </div>
            @error('staffRole') <div class="form-error" style="margin-top:-8px;margin-bottom:10px;">{{ $message }}</div> @enderror

            <hr class="divider">
            <div class="section-label">Personal Details</div>

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-input" wire:model="name" placeholder="e.g. Mrs. Adaeze Obi" autofocus>
                @error('name') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-input" wire:model="email" placeholder="e.g. adaeze@school.ng">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
                @if(! $editingId)
                    <div class="form-hint">Login credentials will be sent to this email.</div>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Phone <span style="font-weight:400;color:var(--c-text-3);">(optional)</span></label>
                <input type="text" class="form-input" wire:model="phone" placeholder="e.g. 08012345678">
                @error('phone') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <hr class="divider">
            <div class="section-label">Class Assignments <span style="font-weight:400;text-transform:none;letter-spacing:0;">(optional)</span></div>

            <div class="form-group">
                <label class="form-label">Form Class Teacher of</label>
                <select class="form-input form-select" wire:model="formClassId">
                    <option value="">Not assigned as form teacher</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">
                            {{ $class->display_name }}
                            @if($class->formTeacher && $class->form_teacher_id !== $editingId)
                                (currently: {{ $class->formTeacher->name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('formClassId') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Teaching Assistant in</label>
                <select class="form-input form-select" wire:model="assistantClassId">
                    <option value="">Not assigned as TA</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">
                            {{ $class->display_name }}
                            @if($class->assistantTeacher && $class->assistant_teacher_id !== $editingId)
                                (currently: {{ $class->assistantTeacher->name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('assistantClassId') <div class="form-error">{{ $message }}</div> @enderror
                <div class="form-hint">A staff member can be form teacher of one class and TA in another.</div>
            </div>

            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('showForm', false)">Cancel</button>
                <button class="btn-confirm" wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Add Staff Member' }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- Reset Password --}}
@if($confirmingResetId)
    @php $m = $staff->firstWhere('id', $confirmingResetId); @endphp
    <div class="modal-bg">
        <div class="modal">
            <div class="modal-title">Reset Password?</div>
            <p style="font-size:13px;color:var(--c-text-2);margin-bottom:4px;">
                A new temporary password will be emailed to <strong>{{ $m?->name }}</strong> at {{ $m?->email }}.
            </p>
            <p style="font-size:12px;color:var(--c-text-3);">They will be required to change it on next login.</p>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('confirmingResetId', null)">Cancel</button>
                <button class="btn-confirm" wire:click="resetPassword({{ $confirmingResetId }})">Send New Password</button>
            </div>
        </div>
    </div>
@endif

{{-- Toggle Active --}}
@if($confirmingToggleId)
    @php $m = $staff->firstWhere('id', $confirmingToggleId); @endphp
    <div class="modal-bg">
        <div class="modal">
            <div class="modal-title">{{ $m?->is_active ? 'Deactivate' : 'Reactivate' }} Staff?</div>
            <p style="font-size:13px;color:var(--c-text-2);">
                @if($m?->is_active)
                    <strong>{{ $m?->name }}</strong> will no longer be able to log in.
                @else
                    <strong>{{ $m?->name }}</strong> will be able to log in again.
                @endif
            </p>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('confirmingToggleId', null)">Cancel</button>
                @if($m?->is_active)
                    <button class="btn-danger" wire:click="toggleActive({{ $confirmingToggleId }})">Deactivate</button>
                @else
                    <button class="btn-confirm" wire:click="toggleActive({{ $confirmingToggleId }})">Reactivate</button>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Reject Registration --}}
@if($showRejectForm)
    <div class="modal-bg" wire:click.self="$set('showRejectForm', false)">
        <div class="modal">
            <div class="modal-title">Reject Application</div>
            <p style="font-size:13px;color:var(--c-text-2);margin-bottom:14px;">
                Provide a reason for the rejection (for internal records only — not shown to the applicant).
            </p>
            <div class="form-group">
                <label class="form-label">Rejection Reason</label>
                <textarea class="form-input" wire:model="rejectionReason" rows="3"
                    placeholder="e.g. Position not currently available, or qualifications do not match requirements."></textarea>
                @error('rejectionReason') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('showRejectForm', false)">Cancel</button>
                <button class="btn-danger" wire:click="submitRejection">Reject Application</button>
            </div>
        </div>
    </div>
@endif

</div>
