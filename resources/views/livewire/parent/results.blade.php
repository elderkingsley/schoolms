<div>
<style>
.pg-title { font-size:18px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:16px; }
.filters { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
.filter-select {
    padding:8px 10px; border:1px solid var(--c-border); border-radius:8px;
    font-family:var(--f-sans); font-size:12px; color:var(--c-text-1);
    background:var(--c-surface); outline:none; -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 8px center; padding-right:26px;
    flex:1; min-width:140px;
}
.filter-select:focus { border-color:var(--c-accent); }

.term-section { margin-bottom:16px; }
.term-label { font-size:12px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px; }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.06em; padding:9px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.data-table th.center, .data-table td.center { text-align:center; }
.data-table td { padding:12px 16px; font-size:13px; border-bottom:1px solid var(--c-border); }
.data-table tr:last-child td { border-bottom:none; }

.grade { display:inline-block; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:700; }
.grade-A { background:rgba(21,128,61,0.1); color:#15803D; }
.grade-B { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.grade-C { background:rgba(180,83,9,0.08); color:#B45309; }
.grade-D, .grade-E, .grade-F { background:rgba(190,18,60,0.08); color:var(--c-danger); }

.empty-state { text-align:center; padding:40px 20px; color:var(--c-text-3); font-size:13px; }
.empty-icon  { color:var(--c-border); margin-bottom:10px; }
.empty-title { font-size:14px; font-weight:600; color:var(--c-text-2); }
.empty-sub   { margin-top:4px; }

.hint-card {
    background:var(--c-accent-bg); border:1px solid rgba(26,86,255,0.15);
    border-radius:var(--r-md); padding:16px; font-size:13px;
    color:var(--c-accent); margin-bottom:16px;
}
</style>

<div class="pg-title">Results</div>

{{-- Child & term filters --}}
@if($children->count() > 1)
<div class="filters">
    <select wire:model.live="filterChild" class="filter-select">
        <option value="">Select a child…</option>
        @foreach($children as $child)
            <option value="{{ $child->id }}">{{ $child->full_name }}</option>
        @endforeach
    </select>
    <select wire:model.live="filterTerm" class="filter-select">
        <option value="">All Terms</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
        @endforeach
    </select>
</div>
@elseif($children->count() === 1)
<div class="filters">
    <select wire:model.live="filterTerm" class="filter-select">
        <option value="">All Terms</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
        @endforeach
    </select>
</div>
@endif

{{-- Content --}}
@if($children->isEmpty())
    <div class="empty-state">
        <div class="empty-title">No active students linked to your account.</div>
    </div>

@elseif($results->isEmpty())
    <div class="empty-state">
        <svg class="empty-icon" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
        </svg>
        <div class="empty-title">No published results yet</div>
        <div class="empty-sub">Results will appear here once the school publishes them.</div>
    </div>

@else
    @foreach($results as $termId => $termResults)
        @php $term = $termResults->first()->term; @endphp
        <div class="term-section">
            <div class="term-label">{{ $term->name }} Term — {{ $term->session->name }}</div>
            <div class="panel">
                @if($isRemarkOnly)
                {{-- Nursery / preschool — remark-only layout --}}
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Assessment</th>
                            <th class="center">Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($termResults->sortBy('subject.name') as $result)
                            <tr>
                                <td style="font-weight:500">{{ $result->subject->name }}</td>
                                <td style="color:var(--c-text-2);font-size:13px">{{ $result->admin_comment ?? '—' }}</td>
                                <td class="center" style="font-size:13px;font-weight:500">{{ $result->remark ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                {{-- Primary — scored layout --}}
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th class="center">CA</th>
                            <th class="center">Exam</th>
                            <th class="center">Total</th>
                            <th class="center">Grade</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($termResults->sortBy('subject.name') as $result)
                            <tr>
                                <td style="font-weight:500">{{ $result->subject->name }}</td>
                                <td class="center">{{ $result->ca_score ?? '—' }}</td>
                                <td class="center">{{ $result->exam_score ?? '—' }}</td>
                                <td class="center" style="font-weight:700">{{ $result->total ?? '—' }}</td>
                                <td class="center">
                                    @if($result->grade)
                                        <span class="grade grade-{{ $result->grade[0] }}">{{ $result->grade }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="color:var(--c-text-3);font-size:12px">{{ $result->remark ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    @endforeach
@endif
</div>