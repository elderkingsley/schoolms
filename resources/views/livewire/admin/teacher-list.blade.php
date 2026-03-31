<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }

.toolbar { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
.search-wrap { position:relative; flex:1; min-width:200px; max-width:320px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-family:var(--f-sans); background:var(--c-surface); outline:none; color:var(--c-text-1); }
.search-wrap input:focus { border-color:var(--c-accent); }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.07em; padding:10px 18px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); white-space:nowrap; }
.data-table td { padding:13px 18px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }
.mono { font-family:var(--f-mono); font-size:12px; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-active   { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-inactive { background:rgba(100,100,100,0.08); color:#666; }
.user-av { width:34px; height:34px; border-radius:50%; background:var(--c-accent-bg); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:var(--c-accent); flex-shrink:0; }
.row-actions { display:flex; gap:5px; flex-wrap:wrap; }
.btn-sm { padding:4px 9px; border-radius:6px; font-size:11px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); white-space:nowrap; color:var(--c-text-2); }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }
.empty-state { padding:40px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.pag-wrap { padding:14px 18px; border-top:1px solid var(--c-border); }
@media(max-width:640px) { .hide-sm { display:none; } }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif

<div class="pg-header">
    <div>
        <div class="pg-title">Teachers</div>
        <div class="pg-sub">All teacher accounts — {{ $teachers->total() }} total</div>
    </div>
</div>

<div class="toolbar">
    <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, email or phone…">
    </div>
</div>

<div class="panel">
    @if($teachers->isEmpty())
        <div class="empty-state">No teachers found.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th class="hide-sm">Email</th>
                    <th class="hide-sm">Phone</th>
                    <th class="hide-sm">Form Class</th>
                    <th>Status</th>
                    @if(auth()->user()->isSuperAdmin()) <th>Actions</th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($teachers as $teacher)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="user-av">{{ strtoupper(substr($teacher->name,0,1)) }}</div>
                                <div>
                                    <div style="font-weight:600;color:var(--c-text-1);">{{ $teacher->name }}</div>
                                    <div style="font-size:11px;color:var(--c-text-3);">Teacher</div>
                                </div>
                            </div>
                        </td>
                        <td class="hide-sm mono" style="color:var(--c-text-2);">{{ $teacher->email }}</td>
                        <td class="hide-sm mono" style="color:var(--c-text-3);">{{ $teacher->phone ?? '—' }}</td>
                        <td class="hide-sm">
                            @php
                                $formClass = \App\Models\SchoolClass::where('form_teacher_id', $teacher->id)->first();
                            @endphp
                            @if($formClass)
                                <span style="font-size:12px;font-weight:500;">{{ $formClass->display_name }}</span>
                            @else
                                <span style="color:var(--c-text-3);font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $teacher->is_active ? 'badge-active' : 'badge-inactive' }}">
                                <span class="badge-dot"></span>
                                {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                        <td>
                            <div class="row-actions">
                                <button class="btn-sm"
                                    wire:click="resetPassword({{ $teacher->id }})"
                                    wire:confirm="Send a password reset email to {{ $teacher->name }}?">
                                    Reset PW
                                </button>
                                <button class="btn-sm {{ $teacher->is_active ? 'btn-sm-danger' : '' }}"
                                    wire:click="toggleActive({{ $teacher->id }})"
                                    wire:confirm="{{ $teacher->is_active ? 'Deactivate' : 'Reactivate' }} {{ $teacher->name }}?">
                                    {{ $teacher->is_active ? 'Deactivate' : 'Reactivate' }}
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($teachers->hasPages())
            <div class="pag-wrap">{{ $teachers->links() }}</div>
        @endif
    @endif
</div>
</div>
