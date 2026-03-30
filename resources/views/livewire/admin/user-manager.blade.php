<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.08);  border:1px solid rgba(190,18,60,0.2);  color:#BE123C; }
.btn-new { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-new:hover { opacity:0.9; }

/* Toolbar */
.toolbar { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.search-wrap { position:relative; flex:1; min-width:180px; max-width:320px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:13px; color:var(--c-text-1); background:var(--c-surface); outline:none; }
.search-wrap input:focus { border-color:var(--c-accent); box-shadow:0 0 0 3px rgba(26,86,255,0.08); }
.filter-select { padding:9px 12px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:13px; color:var(--c-text-1); background:var(--c-surface); outline:none; cursor:pointer; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:30px; }

/* Table */
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 20px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table td { padding:13px 20px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }
.user-name  { font-weight:600; color:var(--c-text-1); }
.user-email { font-size:11px; color:var(--c-text-3); margin-top:1px; }
.user-phone { font-size:11px; color:var(--c-text-3); }

/* Type badge */
.type-badge { display:inline-flex; align-items:center; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; }
.type-super_admin  { background:#0E0E0E; color:#fff; }
.type-admin        { background:rgba(26,86,255,0.1); color:var(--c-accent); }
.type-accountant   { background:rgba(21,128,61,0.08); color:#15803D; }
.type-teacher      { background:rgba(180,83,9,0.08); color:#B45309; }
.type-parent       { background:rgba(100,100,100,0.08); color:#666; }

/* Status dot */
.status-dot { width:7px; height:7px; border-radius:50%; display:inline-block; }
.status-active   { background:#15803D; }
.status-inactive { background:#BE123C; }

.row-actions { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.btn-sm { padding:5px 10px; border-radius:6px; font-size:11px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); white-space:nowrap; transition:background 150ms; }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }
.btn-sm-warn { color:#B45309; border-color:rgba(180,83,9,0.2); }
.btn-sm-warn:hover { background:rgba(180,83,9,0.06); }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:480px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:20px; }
.form-field { margin-bottom:14px; }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:5px; }
.form-field input, .form-field select { width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:14px; color:var(--c-text-1); background:var(--c-bg); outline:none; transition:border-color 150ms; -webkit-appearance:none; }
.form-field input:focus, .form-field select:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 3px rgba(26,86,255,0.08); }
.field-error { font-size:11px; color:var(--c-danger); margin-top:4px; }
.field-hint  { font-size:11px; color:var(--c-text-3); margin-top:4px; }
.modal-actions { display:flex; gap:10px; margin-top:24px; justify-content:flex-end; }
.btn-cancel  { padding:9px 16px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm { padding:9px 20px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
.btn-delete-confirm { padding:9px 20px; background:var(--c-danger); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
.delete-sub { font-size:13px; color:var(--c-text-2); margin-bottom:20px; line-height:1.5; }

/* Force-change tag */
.pw-tag { display:inline-flex; align-items:center; padding:2px 6px; background:rgba(180,83,9,0.08); color:#B45309; border-radius:4px; font-size:10px; font-weight:500; margin-left:6px; }

.empty-state { padding:48px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.pag-wrap { padding:14px 20px; border-top:1px solid var(--c-border); }
@media(max-width:640px) { .hide-mobile { display:none; } }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">⚠ {{ session('error') }}</div>
@endif

<div class="pg-header">
    <div>
        <h1 class="pg-title">Users</h1>
        <p class="pg-sub">Manage all staff and parent accounts. Super Admin access only.</p>
    </div>
    <button class="btn-new" wire:click="openCreate">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M8 2v12M2 8h12"/></svg>
        New User
    </button>
</div>

<div class="toolbar">
    <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name or email…">
    </div>
    <select wire:model.live="filterType" class="filter-select">
        <option value="">All Types</option>
        <option value="super_admin">Super Admin</option>
        <option value="admin">Admin</option>
        <option value="accountant">Accountant</option>
        <option value="teacher">Teacher</option>
        <option value="parent">Parent</option>
    </select>
</div>

<div class="panel">
    <div class="panel-head">
        <span class="panel-title">All Users</span>
        <span style="font-size:11px;color:var(--c-text-3)">{{ $users->total() }} {{ Str::plural('user', $users->total()) }}</span>
    </div>

    @if($users->isEmpty())
        <div class="empty-state">No users found.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Type</th>
                    <th class="hide-mobile">Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;border-radius:50%;background:var(--c-accent-bg);color:var(--c-accent);font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="user-name">
                                        {{ $user->name }}
                                        @if($user->force_password_change)
                                            <span class="pw-tag">Temp PW</span>
                                        @endif
                                    </div>
                                    <div class="user-email">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="type-badge type-{{ $user->user_type }}">
                                {{ $user->type_label }}
                            </span>
                        </td>
                        <td class="hide-mobile" style="font-size:12px;color:var(--c-text-3);">
                            {{ $user->phone ?? '—' }}
                        </td>
                        <td>
                            <span class="status-dot {{ $user->is_active ? 'status-active' : 'status-inactive' }}"></span>
                            <span style="font-size:12px;color:var(--c-text-3);margin-left:5px;">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if($user->isSuperAdmin())
                                <span style="font-size:12px;color:var(--c-text-3);">Protected</span>
                            @else
                                <div class="row-actions">
                                    <button class="btn-sm" wire:click="openEdit({{ $user->id }})">Edit</button>

                                    {{--
                                        Email button — links to compose page with parent pre-selected.
                                        For parent accounts: passes parentProfile ID as query param.
                                        For staff accounts: passes email as query param so compose
                                        can pre-fill the search field.
                                    --}}
                                    @if($user->isParent() && $user->parentProfile)
                                        <a href="{{ route('admin.messages.compose') }}?parent={{ $user->parentProfile->id }}"
                                           class="btn-sm"
                                           title="Message this parent">✉ Email</a>
                                    @endif

                                    <button class="btn-sm btn-sm-warn"
                                        wire:click="resetPassword({{ $user->id }})"
                                        wire:confirm="Reset {{ $user->name }}'s password? A new temporary password will be emailed to them.">
                                        Reset PW
                                    </button>
                                    <button class="btn-sm {{ $user->is_active ? 'btn-sm-warn' : '' }}"
                                        wire:click="toggleActive({{ $user->id }})"
                                        wire:confirm="{{ $user->is_active ? 'Deactivate' : 'Reactivate' }} {{ $user->name }}?">
                                        {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                                    </button>
                                    <button class="btn-sm btn-sm-danger" wire:click="confirmDelete({{ $user->id }})">Delete</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="pag-wrap">{{ $users->links() }}</div>
        @endif
    @endif
</div>

{{-- Create/Edit modal --}}
@if($showForm)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">{{ $editingId ? 'Edit User' : 'New User' }}</div>

        <div class="form-field">
            <label>Full Name <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="name" placeholder="e.g. Chioma Okafor">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Email Address <span style="color:var(--c-danger)">*</span></label>
            <input type="email" wire:model="email" placeholder="e.g. chioma@nurtureville.org">
            @error('email') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Phone <span style="color:var(--c-text-3);font-weight:400">(optional)</span></label>
            <input type="text" wire:model="phone" placeholder="e.g. 08031234567">
            @error('phone') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>User Type <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="userType">
                <option value="admin">Admin</option>
                <option value="accountant">Accountant</option>
                <option value="teacher">Teacher</option>
            </select>
            <div class="field-hint">
                Parents are created automatically during enrolment approval.
                Super Admin accounts are created via the server seeder.
            </div>
            @error('userType') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        @if(! $editingId)
            <div style="background:rgba(26,86,255,0.05);border:1px solid rgba(26,86,255,0.15);border-radius:8px;padding:12px;font-size:12px;color:var(--c-text-2);">
                A temporary password will be generated and emailed to this user.
                They will be required to set a new password on their first login.
            </div>
        @endif

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="$set('showForm', false)">Cancel</button>
            <button class="btn-confirm" wire:click="save">
                <span wire:loading.remove>{{ $editingId ? 'Save Changes' : 'Create & Send Login' }}</span>
                <span wire:loading>{{ $editingId ? 'Saving…' : 'Creating…' }}</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Delete confirm --}}
@if($deletingId)
    @php $del = \App\Models\User::find($deletingId); @endphp
    @if($del)
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-title">Delete "{{ $del->name }}"?</div>
            <div class="delete-sub">
                This permanently removes their account. For parent accounts with linked students,
                deactivation is recommended instead. This action cannot be undone.
            </div>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('deletingId', null)">Cancel</button>
                <button class="btn-delete-confirm" wire:click="delete">Permanently Delete</button>
            </div>
        </div>
    </div>
    @endif
@endif
</div>
