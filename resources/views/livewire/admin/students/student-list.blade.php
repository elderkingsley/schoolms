<div>
<style>
.pg-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
}

.pg-title { font-size: 20px; font-weight: 700; color: var(--c-text-1); letter-spacing: -0.03em; }
.pg-sub   { font-size: 13px; color: var(--c-text-3); margin-top: 2px; }

/* ── Filters bar ── */
.filters-bar {
    display: flex; gap: 10px; margin-bottom: 20px;
    flex-wrap: wrap; align-items: center;
}

.search-wrap {
    flex: 1; min-width: 200px; position: relative;
}

.search-icon {
    position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
    color: var(--c-text-3); pointer-events: none;
}

.search-input {
    width: 100%; padding: 9px 12px 9px 34px;
    border: 1px solid var(--c-border); border-radius: 8px;
    font-size: 13px; font-family: var(--f-sans);
    color: var(--c-text-1); background: var(--c-surface);
    outline: none; transition: border-color 150ms;
}

.search-input:focus {
    border-color: var(--c-accent);
    box-shadow: 0 0 0 3px rgba(26,86,255,0.08);
}

.filter-select {
    padding: 9px 12px; border: 1px solid var(--c-border);
    border-radius: 8px; font-size: 13px;
    font-family: var(--f-sans); color: var(--c-text-1);
    background: var(--c-surface); outline: none;
    cursor: pointer; -webkit-appearance: none;
    min-width: 140px;
}

.filter-select:focus { border-color: var(--c-accent); }

/* ── Table panel ── */
.panel {
    background: var(--c-surface); border: 1px solid var(--c-border);
    border-radius: var(--r-md); overflow: hidden;
}

.panel-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-bottom: 1px solid var(--c-border);
}

.panel-title { font-size: 13px; font-weight: 600; color: var(--c-text-1); }

.count-badge {
    background: var(--c-accent-bg); color: var(--c-accent);
    font-size: 11px; font-weight: 600;
    padding: 3px 9px; border-radius: 20px;
}

.data-table { width: 100%; border-collapse: collapse; }

.data-table th {
    font-size: 10px; font-weight: 600;
    color: var(--c-text-3); text-transform: uppercase;
    letter-spacing: 0.08em; padding: 10px 20px;
    text-align: left; background: var(--c-bg);
    border-bottom: 1px solid var(--c-border);
}

.data-table td {
    padding: 13px 20px; font-size: 13px;
    border-bottom: 1px solid var(--c-border);
    vertical-align: middle;
}

.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: var(--c-bg); }

@media (max-width: 768px) { .hide-mobile { display: none; } }

.student-name { font-weight: 600; color: var(--c-text-1); }
.student-meta { font-size: 11px; color: var(--c-text-3); margin-top: 1px; }

.badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px; border-radius: 20px;
    font-size: 11px; font-weight: 500;
}

.badge-active   { background: rgba(21,128,61,0.08);  color: #15803D; }
.badge-pending  { background: rgba(180,83,9,0.08);    color: #B45309; }
.badge-withdrawn { background: rgba(100,100,100,0.08); color: #666; }
.badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

.adm-no {
    font-family: var(--f-mono); font-size: 11px;
    color: var(--c-text-3);
}

.empty-state {
    padding: 48px 20px; text-align: center;
}

.empty-title { font-size: 14px; font-weight: 600; color: var(--c-text-1); margin-bottom: 4px; }
.empty-sub   { font-size: 12px; color: var(--c-text-3); }

.pagination-wrap { padding: 14px 20px; border-top: 1px solid var(--c-border); }
</style>

{{-- Page header --}}
<div class="pg-header">
    <div>
        <h1 class="pg-title">Students</h1>
        <p class="pg-sub">All students registered in the system.</p>
    </div>
    <a href="{{ route('enrol') }}" target="_blank"
       style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;background:var(--c-accent);color:#fff;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 2v12M2 8h12"/>
        </svg>
        Enrolment Form
    </a>
</div>

{{-- Filters --}}
<div class="filters-bar">
    <div class="search-wrap">
        <div class="search-icon">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="6.5" cy="6.5" r="4.5"/>
                <path d="M14 14l-3-3"/>
            </svg>
        </div>
        <input type="text" class="search-input"
            wire:model.live.debounce.300ms="search"
            placeholder="Search by name or admission number...">
    </div>

    <select class="filter-select" wire:model.live="filterStatus">
        <option value="">All Statuses</option>
        <option value="active">Active</option>
        <option value="pending">Pending</option>
        <option value="graduated">Graduated</option>
        <option value="withdrawn">Withdrawn</option>
    </select>

    <select class="filter-select" wire:model.live="filterClass">
        <option value="">All Classes</option>
        @foreach($classes as $class)
            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
        @endforeach
    </select>
</div>

{{-- Table --}}
<div class="panel">
    <div class="panel-head">
        <span class="panel-title">
            {{ $filterStatus ? ucfirst($filterStatus) . ' Students' : 'All Students' }}
        </span>
        <span class="count-badge">{{ $students->total() }}</span>
    </div>

    @if($students->isEmpty())
        <div class="empty-state">
            <div class="empty-title">No students found</div>
            <div class="empty-sub">
                @if($search)
                    No results for "{{ $search }}". Try a different search.
                @else
                    No students match the selected filters.
                @endif
            </div>
        </div>
    @else
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th class="hide-mobile">Admission No.</th>
                        <th class="hide-mobile">Class</th>
                        <th class="hide-mobile">Parents</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php
                            $enrolment = $student->enrolments->first();
                        @endphp
                        <tr onclick="window.location='{{ route('admin.students.profile', $student) }}'"
                            title="View full profile">
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:36px;height:36px;border-radius:50%;background:var(--c-accent-bg);flex-shrink:0;overflow:hidden;border:1px solid var(--c-border);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--c-accent);">
                                        @if($student->photo)
                                            <img src="{{ Storage::url($student->photo) }}"
                                                 alt="{{ $student->first_name }}"
                                                 style="width:100%;height:100%;object-fit:cover;"
                                                 onerror="this.style.display='none';this.parentElement.innerHTML='{{ strtoupper(substr($student->first_name, 0, 1)) }}'">
                                        @else
                                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="student-name">
                                            {{ $student->first_name }} {{ $student->last_name }}
                                            @if($student->other_name)
                                                <span style="font-weight:400;color:var(--c-text-3)">
                                                    {{ $student->other_name }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="student-meta">
                                            {{ $student->gender }} ·
                                            {{ $student->date_of_birth?->format('d M Y') ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="hide-mobile">
                                <span class="adm-no">{{ $student->admission_number }}</span>
                            </td>
                            <td class="hide-mobile">
                                @if($enrolment && $enrolment->schoolClass)
                                    {{ $enrolment->schoolClass->display_name }}
                                @elseif($student->class_applied_for)
                                    <span style="color:var(--c-text-3);font-size:12px">
                                        Applied: {{ $student->class_applied_for }}
                                    </span>
                                @else
                                    <span style="color:var(--c-text-3)">—</span>
                                @endif
                            </td>
                            <td class="hide-mobile">
                                @foreach($student->parents->take(2) as $parent)
                                    <div style="font-size:12px">
                                        {{ $parent->_temp_name ?? $parent->user?->name ?? '—' }}
                                    </div>
                                @endforeach
                                @if($student->parents->count() > 2)
                                    <div style="font-size:11px;color:var(--c-text-3)">
                                        +{{ $student->parents->count() - 2 }} more
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $student->status }}">
                                    <span class="badge-dot"></span>
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="pagination-wrap">
                {{ $students->links() }}
            </div>
        @endif
    @endif
</div>

</div>
