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

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }

.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 20px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table td { padding:13px 20px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }

.subject-name { font-weight:600; color:var(--c-text-1); }
.subject-code { font-family:var(--f-mono); font-size:11px; color:var(--c-text-3); background:var(--c-bg); border:1px solid var(--c-border); padding:2px 7px; border-radius:4px; }

.order-btns { display:flex; flex-direction:column; gap:2px; }
.btn-order { width:22px; height:22px; border-radius:4px; border:1px solid var(--c-border); background:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--c-text-3); padding:0; transition:background 150ms; }
.btn-order:hover { background:var(--c-bg); color:var(--c-text-1); }
.btn-order:disabled { opacity:0.2; cursor:not-allowed; }

.toggle { width:36px; height:20px; background:var(--c-border); border-radius:10px; position:relative; cursor:pointer; transition:background 200ms; border:none; }
.toggle.on { background:var(--c-accent); }
.toggle::after { content:''; width:14px; height:14px; background:#fff; border-radius:50%; position:absolute; top:3px; left:3px; transition:transform 200ms; box-shadow:0 1px 3px rgba(0,0,0,0.2); }
.toggle.on::after { transform:translateX(16px); }

.row-actions { display:flex; align-items:center; gap:8px; }
.btn-sm { padding:5px 12px; border-radius:6px; font-size:12px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); transition:background 150ms; }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:440px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:20px; }
.form-field { margin-bottom:16px; }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:5px; }
.form-field input, .form-field select { width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px; font-family:var(--f-sans); font-size:14px; color:var(--c-text-1); background:var(--c-bg); outline:none; transition:border-color 150ms; -webkit-appearance:none; }
.form-field input:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 3px rgba(26,86,255,0.08); }
.field-error { font-size:11px; color:var(--c-danger); margin-top:4px; }
.modal-actions { display:flex; gap:10px; margin-top:24px; justify-content:flex-end; }
.btn-cancel  { padding:9px 16px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-weight:500; background:none; cursor:pointer; font-family:var(--f-sans); }
.btn-confirm { padding:9px 20px; background:var(--c-accent); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
.btn-delete-confirm { padding:9px 20px; background:var(--c-danger); color:#fff; border-radius:8px; font-size:13px; font-weight:500; border:none; cursor:pointer; font-family:var(--f-sans); }
.delete-sub { font-size:13px; color:var(--c-text-2); margin-bottom:20px; line-height:1.5; }

.empty-state { padding:48px 20px; text-align:center; }
.empty-title { font-size:14px; font-weight:600; color:var(--c-text-2); }

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
        <h1 class="pg-title">Subjects</h1>
        <p class="pg-sub">Manage the school's subject catalogue. Use ↑↓ to set the order subjects appear on report cards.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.classes.subjects') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--c-text-2);text-decoration:none;background:var(--c-surface);">
            Class Assignments →
        </a>
        <button class="btn-new" wire:click="openCreate">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M8 2v12M2 8h12"/></svg>
            New Subject
        </button>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <span class="panel-title">All Subjects</span>
        <span style="font-size:11px;color:var(--c-text-3)">{{ $subjects->count() }} subjects</span>
    </div>

    @if($subjects->isEmpty())
        <div class="empty-state">
            <div class="empty-title">No subjects yet. Create your first subject.</div>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:48px">Order</th>
                    <th>Subject</th>
                    <th>Code</th>
                    <th class="hide-mobile">Classes Assigned</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                    <tr>
                        <td>
                            <div class="order-btns">
                                <button class="btn-order" wire:click="moveUp({{ $subject->id }})" @if($loop->first) disabled @endif title="Move up">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 7l3-4 3 4"/></svg>
                                </button>
                                <button class="btn-order" wire:click="moveDown({{ $subject->id }})" @if($loop->last) disabled @endif title="Move down">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 3l3 4 3-4"/></svg>
                                </button>
                            </div>
                        </td>
                        <td><span class="subject-name">{{ $subject->name }}</span></td>
                        <td><span class="subject-code">{{ $subject->code }}</span></td>
                        <td class="hide-mobile" style="font-size:12px;color:var(--c-text-3);">
                            {{ $subject->classes()->count() }} {{ Str::plural('class', $subject->classes()->count()) }}
                        </td>
                        <td>
                            <button class="toggle {{ $subject->is_active ? 'on' : '' }}"
                                wire:click="toggleActive({{ $subject->id }})"
                                title="{{ $subject->is_active ? 'Active' : 'Inactive' }}">
                            </button>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-sm" wire:click="openEdit({{ $subject->id }})">Edit</button>
                                <button class="btn-sm btn-sm-danger" wire:click="confirmDelete({{ $subject->id }})">Delete</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Create/Edit modal --}}
@if($showForm)
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">{{ $editingId ? 'Edit Subject' : 'New Subject' }}</div>
        <div class="form-field">
            <label>Subject Name <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="name" placeholder="e.g. English Language">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="form-field">
            <label>Subject Code <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="code" placeholder="e.g. ENG" style="text-transform:uppercase" maxlength="10">
            @error('code') <div class="field-error">{{ $message }}</div> @enderror
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" wire:click="cancelForm">Cancel</button>
            <button class="btn-confirm" wire:click="save">
                <span wire:loading.remove>{{ $editingId ? 'Save Changes' : 'Create' }}</span>
                <span wire:loading>Saving…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Delete confirm --}}
@if($deletingId)
    @php $del = \App\Models\Subject::find($deletingId); @endphp
    @if($del)
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-title">Delete "{{ $del->name }}"?</div>
            <div class="delete-sub">This will permanently remove the subject. If it has results recorded or is assigned to a class, deletion will be blocked — deactivate it instead.</div>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('deletingId', null)">Cancel</button>
                <button class="btn-delete-confirm" wire:click="delete">Delete</button>
            </div>
        </div>
    </div>
    @endif
@endif

</div>
