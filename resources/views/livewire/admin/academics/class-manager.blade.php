{{-- Deploy to: resources/views/livewire/admin/academics/class-manager.blade.php --}}
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
.class-name { font-weight:600; color:var(--c-text-1); }
.result-badge-scored      { display:inline-flex; align-items:center; padding:2px 7px; background:rgba(26,86,255,0.08); color:var(--c-accent); border-radius:4px; font-size:10px; font-weight:600; }
.result-badge-remark_only { display:inline-flex; align-items:center; padding:2px 7px; background:rgba(180,83,9,0.08); color:#B45309; border-radius:4px; font-size:10px; font-weight:600; }
.arm-badge { display:inline-flex; align-items:center; padding:2px 8px; background:var(--c-accent-bg); color:var(--c-accent); border-radius:4px; font-size:11px; font-weight:600; margin-left:8px; }
.order-btns { display:flex; flex-direction:column; gap:2px; }
.btn-order { width:22px; height:22px; border-radius:4px; border:1px solid var(--c-border); background:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--c-text-3); padding:0; transition:background 150ms; }
.btn-order:hover { background:var(--c-bg); color:var(--c-text-1); }
.btn-order:disabled { opacity:0.2; cursor:not-allowed; }
.row-actions { display:flex; align-items:center; gap:8px; }
.btn-sm { padding:5px 12px; border-radius:6px; font-size:12px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); transition:background 150ms; }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }
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
.empty-state { padding:48px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
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
        <h1 class="pg-title">Classes</h1>
        <p class="pg-sub">Manage school classes and arms. Use ↑↓ to set display order.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.classes.subjects') }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--c-text-2);text-decoration:none;background:var(--c-surface);">
            Subject Assignments →
        </a>
        <button class="btn-new" wire:click="openCreate">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M8 2v12M2 8h12"/></svg>
            New Class
        </button>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <span class="panel-title">All Classes</span>
        <span style="font-size:11px;color:var(--c-text-3)">{{ $classes->count() }} classes</span>
    </div>

    @if($classes->isEmpty())
        <div class="empty-state">No classes yet. Create your first class.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:48px">Order</th>
                    <th>Class</th>
                    <th class="hide-mobile">Level</th>
                    <th class="hide-mobile">Form Teacher</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classes as $class)
                    <tr>
                        <td>
                            <div class="order-btns">
                                <button class="btn-order" wire:click="moveUp({{ $class->id }})" @if($loop->first) disabled @endif>
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 7l3-4 3 4"/></svg>
                                </button>
                                <button class="btn-order" wire:click="moveDown({{ $class->id }})" @if($loop->last) disabled @endif>
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 3l3 4 3-4"/></svg>
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="class-name">{{ $class->name }}</span>
                            @if($class->arm)
                                <span class="arm-badge">{{ $class->arm }}</span>
                            @endif
                            <span class="result-badge-{{ $class->result_type ?? 'scored' }}" style="margin-left:6px;">
                                {{ $class->result_type === 'remark_only' ? 'Remarks Only' : 'Scored' }}
                            </span>
                        </td>
                        <td class="hide-mobile" style="font-size:12px;color:var(--c-text-3);">{{ $class->level }}</td>
                        <td class="hide-mobile" style="font-size:12px;color:var(--c-text-2);">
                            {{ $class->formTeacher?->name ?? '—' }}
                        </td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-sm" wire:click="openEdit({{ $class->id }})">Edit</button>
                                <button class="btn-sm btn-sm-danger" wire:click="confirmDelete({{ $class->id }})">Delete</button>
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
        <div class="modal-title">{{ $editingId ? 'Edit Class' : 'New Class' }}</div>

        <div class="form-field">
            <label>Base Class Name <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="name" placeholder="e.g. Primary 3">
            <div class="field-hint">The class name without the arm. e.g. "Primary 3", "Nursery 2"</div>
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Level <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="level" placeholder="e.g. Primary 3">
            <div class="field-hint">Used for grouping. Usually same as base name.</div>
            @error('level') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Arm <span style="color:var(--c-text-3);font-weight:400">(optional)</span></label>
            <input type="text" wire:model="arm" placeholder="e.g. Gold, Silver, Red, Blue">
            <div class="field-hint">Leave blank if this class has no arm subdivision.</div>
            @error('arm') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Form Teacher <span style="color:var(--c-text-3);font-weight:400">(optional)</span></label>
            <select wire:model="formTeacherId">
                <option value="">Not assigned</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                @endforeach
            </select>
            @error('formTeacherId') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Results Format <span style="color:var(--c-danger)">*</span></label>
            <select wire:model="resultType">
                <option value="scored">Scored — CA (40%) + Exam (60%)</option>
                <option value="remark_only">Remarks Only — Nursery classes</option>
            </select>
            <div class="field-hint">Nursery classes use remarks only. All other classes use scored format.</div>
            @error('resultType') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="modal-actions">
            <button class="btn-cancel" wire:click="cancelForm">Cancel</button>
            <button class="btn-confirm" wire:click="save">
                <span wire:loading.remove>{{ $editingId ? 'Save Changes' : 'Create Class' }}</span>
                <span wire:loading>Saving…</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Delete confirm --}}
@if($deletingId)
    @php $del = \App\Models\SchoolClass::find($deletingId); @endphp
    @if($del)
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-title">Delete "{{ $del->display_name }}"?</div>
            <div class="delete-sub">This will permanently remove the class. Deletion is blocked if students are enrolled in it.</div>
            <div class="modal-actions">
                <button class="btn-cancel" wire:click="$set('deletingId', null)">Cancel</button>
                <button class="btn-delete-confirm" wire:click="delete">Delete</button>
            </div>
        </div>
    </div>
    @endif
@endif
</div>
