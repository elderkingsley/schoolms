<div>
<style>
.pg-title { font-size:18px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:16px; }

.child-card {
    background:var(--c-surface); border:1px solid var(--c-border);
    border-radius:var(--r-md); overflow:hidden; margin-bottom:12px;
}
.child-header {
    padding:16px; display:flex; align-items:center; gap:14px;
    border-bottom:1px solid var(--c-border);
}
.child-avatar {
    width:48px; height:48px; border-radius:50%;
    background:var(--c-accent-bg); color:var(--c-accent);
    font-size:20px; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.child-name { font-size:16px; font-weight:700; color:var(--c-text-1); }
.child-adm  { font-family:var(--f-mono); font-size:11px; color:var(--c-text-3); margin-top:2px; }

.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:500; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-active    { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-pending   { background:rgba(180,83,9,0.08);  color:#B45309; }
.badge-withdrawn { background:rgba(100,100,100,0.08); color:#666; }

.detail-grid { display:grid; grid-template-columns:1fr 1fr; }
.detail-item { padding:12px 16px; border-bottom:1px solid var(--c-border); border-right:1px solid var(--c-border); }
.detail-item:nth-child(even) { border-right:none; }
.detail-item:nth-last-child(-n+2) { border-bottom:none; }
.detail-label { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; }
.detail-value { font-size:13px; font-weight:500; color:var(--c-text-1); margin-top:3px; }

.enrolment-row { padding:12px 16px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--c-border); font-size:13px; }
.enrolment-row:last-child { border-bottom:none; }
.enrolment-session { color:var(--c-text-3); font-size:12px; }

.empty-state { text-align:center; padding:40px 20px; color:var(--c-text-3); font-size:13px; }
</style>

<div class="pg-title">My Children</div>

@if($children->isEmpty())
    <div class="empty-state">No children linked to your account yet.</div>
@else
    @foreach($children as $child)
        <div class="child-card">
            <div class="child-header">
                <div class="child-avatar"
                     style="{{ $child->photo ? 'background:none;padding:0;overflow:hidden;' : '' }}">
                    @if($child->photo)
                        <img src="{{ Storage::url($child->photo) }}"
                             alt="{{ $child->full_name }}"
                             style="width:100%;height:100%;object-fit:cover;border-radius:50%;"
                             onerror="this.style.display='none'">
                    @else
                        {{ strtoupper(substr($child->first_name, 0, 1)) }}
                    @endif
                </div>
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="child-name">{{ $child->full_name }}</span>
                        <span class="badge badge-{{ $child->status }}">
                            <span class="badge-dot"></span>
                            {{ ucfirst($child->status) }}
                        </span>
                    </div>
                    <div class="child-adm">{{ $child->admission_number }}</div>
                </div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Gender</div>
                    <div class="detail-value">{{ $child->gender }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date of Birth</div>
                    <div class="detail-value">{{ $child->date_of_birth?->format('d M Y') ?? '—' }}</div>
                </div>
                @php $latestEnrolment = $child->enrolments->sortByDesc('id')->first(); @endphp
                <div class="detail-item">
                    <div class="detail-label">Current Class</div>
                    <div class="detail-value">{{ $latestEnrolment?->schoolClass?->display_name ?? '—' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Session</div>
                    <div class="detail-value">{{ $latestEnrolment?->session?->name ?? '—' }}</div>
                </div>
            </div>

            {{-- Enrolment history if more than one --}}
            @if($child->enrolments->count() > 1)
                <div style="padding:10px 16px;border-top:1px solid var(--c-border);font-size:11px;font-weight:600;color:var(--c-text-3);text-transform:uppercase;letter-spacing:0.06em;">
                    Enrolment History
                </div>
                @foreach($child->enrolments->sortByDesc('id') as $enrolment)
                    <div class="enrolment-row">
                        <span>{{ $enrolment->schoolClass?->name }}</span>
                        <span class="enrolment-session">{{ $enrolment->session?->name }}</span>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
@endif
</div>
