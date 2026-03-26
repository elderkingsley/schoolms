<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }

.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.08);  border:1px solid rgba(190,18,60,0.2);  color:#BE123C; }

.btn-new {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 16px; background:var(--c-accent); color:#fff;
    border-radius:8px; font-size:13px; font-weight:500;
    border:none; cursor:pointer; font-family:var(--f-sans);
    transition:opacity 150ms;
}
.btn-new:hover { opacity:0.9; }

/* Table */
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }

.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 20px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table td { padding:14px 20px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:var(--c-bg); }

.item-name { font-weight:600; color:var(--c-text-1); }
.item-desc { font-size:11px; color:var(--c-text-3); margin-top:2px; }

.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-compulsory { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.badge-optional   { background:rgba(180,83,9,0.08);   color:#B45309; }
.badge-active     { background:rgba(21,128,61,0.08);   color:#15803D; }
.badge-inactive   { background:rgba(100,100,100,0.08); color:#666; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }

.row-actions { display:flex; align-items:center; gap:8px; }
.btn-sm { padding:5px 12px; border-radius:6px; font-size:12px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); transition:background 150ms, color 150ms; }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }

/* Toggle */
.toggle { width:36px; height:20px; background:var(--c-border); border-radius:10px; position:relative; cursor:pointer; transition:background 200ms; border:none; }
.toggle.on { background:var(--c-accent); }
.toggle::after { content:''; width:14px; height:14px; background:#fff; border-radius:50%; position:absolute; top:3px; left:3px; transition:transform 200ms; box-shadow:0 1px 3px rgba(0,0,0,0.2); }
.toggle.on::after { transform:translateX(16px); }

/* Form modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); backdrop-filter:blur(3px); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:480px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.2); }
.modal-title { font-size:16px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:20px; }

.form-field { margin-bottom:16px; }
.form-field label { display:block; font-size:12px; font-weight:500; color:var(--c-text-2); margin-bottom:5px; }
.form-field input, .form-field select, .form-field textarea {
    width:100%; padding:10px 12px; border:1px solid var(--c-border); border-radius:8px;
    font-family:var(--f-sans); font-size:14px; color:var(--c-text-1);
    background:var(--c-bg); outline:none; transition:border-color 150ms; -webkit-appearance:none;
}
.form-field input:focus, .form-field select:focus, .form-field textarea:focus {
    border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 3px rgba(26,86,255,0.08);
}
.field-error { font-size:11px; color:var(--c-danger); margin-top:4px; }

/* Type selector */
.type-toggle { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.type-opt {
    padding:10px; border:2px solid var(--c-border); border-radius:8px;
    text-align:center; cursor:pointer; transition:border-color 150ms, background 150ms;
}
.type-opt.selected-compulsory { border-color:var(--c-accent); background:var(--c-accent-bg); }
.type-opt.selected-optional   { border-color:#B45309; background:rgba(180,83,9,0.06); }
.type-opt-name { font-size:13px; font-weight:600; color:var(--c-text-1); }
.type-opt-desc { font-size:11px; color:var(--c-text-3); margin-top:2px; }

.modal-actions { display:flex; gap:10px; margin-top:24px; }
.btn-primary { flex:1; padding:11px; background:var(--c-accent); color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-primary:hover { opacity:0.9; }
.btn-ghost { padding:11px 20px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:14px; cursor:pointer; font-family:var(--f-sans); }
.btn-ghost:hover { background:var(--c-bg); }

/* Delete confirm */
.delete-modal-box { background:var(--c-surface); border-radius:16px; width:100%; max-width:400px; padding:24px; box-shadow:0 20px 60px rgba(0,0,0,0.2); text-align:center; }
.delete-icon { width:48px; height:48px; border-radius:50%; background:rgba(190,18,60,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:#BE123C; }
.delete-title { font-size:16px; font-weight:700; color:var(--c-text-1); margin-bottom:6px; }
.delete-sub   { font-size:13px; color:var(--c-text-3); line-height:1.5; margin-bottom:20px; }
.delete-actions { display:flex; gap:10px; }
.btn-delete { flex:1; padding:11px; background:#BE123C; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; font-family:var(--f-sans); }

.empty-state { padding:48px 20px; text-align:center; }
.empty-title { font-size:14px; font-weight:600; color:var(--c-text-1); margin-bottom:4px; }
.empty-sub   { font-size:12px; color:var(--c-text-3); }

@media(max-width:640px) { .hide-mobile { display:none; } }
</style>

{{-- Flash messages --}}
@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">⚠ {{ session('error') }}</div>
@endif

{{-- Page header --}}
<div class="pg-header">
    <div>
        <h1 class="pg-title">Fee Items</h1>
        <p class="pg-sub">Manage the catalogue of fee items used to build fee structures.</p>
    </div>
    <button class="btn-new" wire:click="openCreate">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M8 2v12M2 8h12"/>
        </svg>
        New Fee Item
    </button>
</div>

{{-- Items table --}}
<div class="panel">
    <div class="panel-head">
        <span class="panel-title">All Fee Items</span>
        <span style="font-size:11px;color:var(--c-text-3)">
            {{ $items->total() }} item{{ $items->total() !== 1 ? 's' : '' }}
        </span>
    </div>

    @if($items->isEmpty())
        <div class="empty-state">
            <div class="empty-title">No fee items yet</div>
            <div class="empty-sub">Create your first fee item to start building fee structures.</div>
        </div>
    @else
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th class="hide-mobile">Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>
                                <div class="item-name">{{ $item->name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $item->type }}">
                                    <span class="badge-dot"></span>
                                    {{ ucfirst($item->type) }}
                                </span>
                            </td>
                            <td class="hide-mobile">
                                <span style="font-size:12px;color:var(--c-text-3)">
                                    {{ $item->description ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <button
                                    class="toggle {{ $item->is_active ? 'on' : '' }}"
                                    wire:click="toggleActive({{ $item->id }})"
                                    title="{{ $item->is_active ? 'Active — click to deactivate' : 'Inactive — click to activate' }}">
                                </button>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button class="btn-sm" wire:click="openEdit({{ $item->id }})">Edit</button>
                                    <button class="btn-sm btn-sm-danger" wire:click="confirmDelete({{ $item->id }})">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div style="padding:14px 20px;border-top:1px solid var(--c-border)">
                {{ $items->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Create / Edit modal --}}
@if($showForm)
<div class="modal-overlay">
    <div class="modal-box">
        <h2 class="modal-title">{{ $editingId ? 'Edit Fee Item' : 'New Fee Item' }}</h2>

        <div class="form-field">
            <label>Item Name <span style="color:var(--c-danger)">*</span></label>
            <input type="text" wire:model="name" placeholder="e.g. Tuition Fees">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Description <span style="font-weight:400;color:var(--c-text-3)">(optional)</span></label>
            <input type="text" wire:model="description" placeholder="Brief description of this fee">
            @error('description') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-field">
            <label>Fee Type <span style="color:var(--c-danger)">*</span></label>
            <div class="type-toggle">
                <div class="type-opt {{ $type === 'compulsory' ? 'selected-compulsory' : '' }}"
                     wire:click="$set('type', 'compulsory')">
                    <div class="type-opt-name">Compulsory</div>
                    <div class="type-opt-desc">All students pay this</div>
                </div>
                <div class="type-opt {{ $type === 'optional' ? 'selected-optional' : '' }}"
                     wire:click="$set('type', 'optional')">
                    <div class="type-opt-name">Optional</div>
                    <div class="type-opt-desc">Added per student</div>
                </div>
            </div>
            @error('type') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="modal-actions">
            <button class="btn-ghost" wire:click="cancelForm">Cancel</button>
            <button class="btn-primary" wire:click="save"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove>{{ $editingId ? 'Save Changes' : 'Create Item' }}</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Delete confirmation modal --}}
@if($deletingId)
    @php $deletingItem = \App\Models\FeeItem::find($deletingId); @endphp
    @if($deletingItem)
    <div class="modal-overlay">
        <div class="delete-modal-box">
            <div class="delete-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6M14 11v6"/>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
            </div>
            <div class="delete-title">Delete "{{ $deletingItem->name }}"?</div>
            <div class="delete-sub">
                This will permanently remove this fee item. If it has been used in any
                fee structure, you will be asked to deactivate it instead.
            </div>
            <div class="delete-actions">
                <button class="btn-ghost" wire:click="$set('deletingId', null)">Cancel</button>
                <button class="btn-delete" wire:click="delete"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Delete</span>
                    <span wire:loading>Deleting...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
@endif

</div>
