{{-- Deploy to: /var/www/schoolms/resources/views/livewire/admin/parent-list.blade.php --}}
<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }

.stats-bar { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:18px; }
@media(min-width:640px){ .stats-bar { grid-template-columns:repeat(4,1fr); } }
.stat-card { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:12px 14px; }
.stat-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; }
.stat-value { font-size:20px; font-weight:700; margin-top:4px; font-family:var(--f-mono); }

.toolbar { display:flex; gap:8px; margin-bottom:14px; flex-wrap:wrap; }
.search-wrap { position:relative; flex:1; min-width:200px; max-width:320px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--c-text-3); pointer-events:none; }
.search-wrap input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-family:var(--f-sans); background:var(--c-surface); outline:none; color:var(--c-text-1); }
.search-wrap input:focus { border-color:var(--c-accent); }
.sel { padding:9px 10px; border:1px solid var(--c-border); border-radius:8px; font-size:12px; font-family:var(--f-sans); background:var(--c-surface); outline:none; -webkit-appearance:none; color:var(--c-text-1); background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 8px center; padding-right:24px; }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.07em; padding:10px 18px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); white-space:nowrap; }
.data-table td { padding:13px 18px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:top; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }
.mono { font-family:var(--f-mono); font-size:12px; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-active   { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-inactive { background:rgba(100,100,100,0.08); color:#666; }
.badge-wallet-active  { background:rgba(21,128,61,0.08); color:#15803D; font-size:10px; padding:2px 7px; }
.badge-wallet-pending { background:rgba(180,83,9,0.08);  color:#B45309; font-size:10px; padding:2px 7px; }
.badge-wallet-failed  { background:rgba(190,18,60,0.08); color:var(--c-danger); font-size:10px; padding:2px 7px; }
.badge-wallet-none    { background:rgba(100,100,100,0.08); color:#999; font-size:10px; padding:2px 7px; }
.user-av { width:34px; height:34px; border-radius:50%; background:var(--c-accent-bg); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:var(--c-accent); flex-shrink:0; }
.child-pill { display:inline-flex; align-items:center; gap:4px; font-size:11px; color:var(--c-text-2); background:var(--c-bg); border:1px solid var(--c-border); border-radius:5px; padding:2px 7px; margin:1px; }
.row-actions { display:flex; gap:5px; flex-wrap:wrap; }
.btn-sm { padding:4px 9px; border-radius:6px; font-size:11px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); white-space:nowrap; color:var(--c-text-2); }
.btn-sm:hover { background:var(--c-bg); }
.btn-sm-danger { color:var(--c-danger); border-color:rgba(190,18,60,0.2); }
.btn-sm-danger:hover { background:rgba(190,18,60,0.06); }
.empty-state { padding:40px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.pag-wrap { padding:14px 18px; border-top:1px solid var(--c-border); }
@media(max-width:640px){ .hide-sm { display:none; } }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif

<div class="pg-header">
    <div>
        <div class="pg-title">Parents</div>
        <div class="pg-sub">All parent portal accounts and their children</div>
    </div>
</div>

<div class="stats-bar">
    <div class="stat-card">
        <div class="stat-label">Total Parents</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Virtual Acct Active</div>
        <div class="stat-value" style="color:#15803D">{{ $stats['wallet_active'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Provisioning</div>
        <div class="stat-value" style="color:#B45309">{{ $stats['wallet_pending'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Failed</div>
        <div class="stat-value" style="color:var(--c-danger)">{{ $stats['wallet_failed'] }}</div>
    </div>
</div>

<div class="toolbar">
    <div class="search-wrap">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="6.5" cy="6.5" r="4.5"/><path d="M10 10l3 3"/>
        </svg>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, email or phone…">
    </div>
    <select wire:model.live="filterWallet" class="sel">
        <option value="">All virtual account statuses</option>
        <option value="active">Active</option>
        <option value="pending">Provisioning</option>
        <option value="failed">Failed</option>
        <option value="none">None</option>
    </select>
</div>

<div class="panel">
    @if($parents->isEmpty())
        <div class="empty-state">No parents found.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Parent</th>
                    <th class="hide-sm">Contact</th>
                    <th>Children</th>
                    <th class="hide-sm">Virtual Account</th>
                    <th>Status</th>
                    @if(auth()->user()->isSuperAdmin()) <th>Actions</th> @endif
                </tr>
            </thead>
            <tbody>
                @foreach($parents as $parent)
                    @php $user = $parent->user; @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="user-av">{{ strtoupper(substr($user?->name ?? '?', 0, 1)) }}</div>
                                <div>
                                    <div style="font-weight:600;color:var(--c-text-1);">{{ $user?->name ?? '—' }}</div>
                                    <div class="mono" style="color:var(--c-text-3);font-size:11px;">{{ $user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="hide-sm">
                            <div class="mono" style="color:var(--c-text-2);font-size:12px;">{{ $parent->phone ?? $user?->phone ?? '—' }}</div>
                            @if($parent->address)
                                <div style="font-size:11px;color:var(--c-text-3);margin-top:2px;">{{ Str::limit($parent->address, 40) }}</div>
                            @endif
                            @if($parent->occupation)
                                <div style="font-size:11px;color:var(--c-text-3);">{{ $parent->occupation }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:3px;">
                                @forelse($parent->students as $student)
                                    <a href="{{ route('admin.students.profile', $student) }}" class="child-pill">
                                        {{ $student->full_name }}
                                    </a>
                                @empty
                                    <span style="color:var(--c-text-3);font-size:12px;">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="hide-sm">
                            @if($parent->hasVirtualAccount())
                                <span class="badge badge-wallet-active">Active</span>
                                <div class="mono" style="margin-top:4px;font-size:11px;color:var(--c-text-2);">
                                    {{ $parent->active_bank_name }}<br>
                                    {{ $parent->active_account_number }}
                                </div>
                            @elseif($parent->wallet_status === 'pending')
                                <span class="badge badge-wallet-pending">Provisioning</span>
                            @elseif($parent->wallet_status === 'failed')
                                <span class="badge badge-wallet-failed">Failed</span>
                            @else
                                <span class="badge badge-wallet-none">Not set up</span>
                            @endif
                        </td>
                        <td>
                            @if($user)
                                <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    <span class="badge-dot"></span>
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            @endif
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                        <td>
                            @if($user)
                            <div class="row-actions">
                                <button class="btn-sm"
                                    wire:click="resetPassword({{ $user->id }})"
                                    wire:confirm="Send a password reset email to {{ $user->name }}?">
                                    Reset PW
                                </button>
                                <button class="btn-sm {{ $user->is_active ? 'btn-sm-danger' : '' }}"
                                    wire:click="toggleActive({{ $user->id }})"
                                    wire:confirm="{{ $user->is_active ? 'Deactivate' : 'Reactivate' }} {{ $user->name }}?">
                                    {{ $user->is_active ? 'Deactivate' : 'Reactivate' }}
                                </button>
                                @if(! $parent->hasVirtualAccount() || $parent->wallet_status !== 'active')
                                <button class="btn-sm"
                                    wire:click="retryProvisioning({{ $parent->id }})"
                                    wire:confirm="Retry NUBAN provisioning for {{ $user->name }}?">
                                    Retry NUBAN
                                </button>
                                @endif
                            </div>
                            @endif
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($parents->hasPages())
            <div class="pag-wrap">{{ $parents->links() }}</div>
        @endif
    @endif
</div>
</div>
