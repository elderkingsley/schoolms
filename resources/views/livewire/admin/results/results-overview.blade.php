<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.selector-bar { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:14px 18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
.selector-bar label { font-size:12px; font-weight:500; color:var(--c-text-2); white-space:nowrap; }
.sel { padding:8px 12px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-family:var(--f-sans); background:var(--c-bg); color:var(--c-text-1); outline:none; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 8px center; padding-right:28px; min-width:150px; }
.sel:focus { border-color:var(--c-accent); }
.sel:disabled { opacity:0.5; }
.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); flex-wrap:wrap; gap:8px; }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.data-table { width:100%; border-collapse:collapse; }
.data-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); white-space:nowrap; }
.data-table th.center, .data-table td.center { text-align:center; }
.data-table td { padding:12px 16px; font-size:13px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:#fafaf8; }
.student-name { font-weight:600; color:var(--c-text-1); }
.student-adm  { font-family:var(--f-mono); font-size:11px; color:var(--c-text-3); }
.mono { font-family:var(--f-mono); font-size:12px; }
.badge { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:500; white-space:nowrap; }
.badge-dot { width:5px; height:5px; border-radius:50%; background:currentColor; }
.badge-published   { background:rgba(21,128,61,0.08); color:#15803D; }
.badge-submitted   { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.badge-unpublished { background:rgba(180,83,9,0.08);  color:#B45309; }
.badge-none        { background:rgba(100,100,100,0.08); color:#777; }
.btn-sm { padding:5px 9px; border-radius:6px; font-size:11px; font-weight:500; border:1px solid var(--c-border); background:none; cursor:pointer; font-family:var(--f-sans); white-space:nowrap; transition:background 150ms; }
.btn-sm:hover { background:var(--c-bg); }
.btn-pdf { display:inline-flex; align-items:center; gap:4px; padding:5px 9px; border-radius:6px; font-size:11px; font-weight:500; border:1px solid var(--c-border); background:none; color:var(--c-text-2); text-decoration:none; transition:background 150ms; }
.btn-pdf:hover { background:var(--c-bg); }
.btn-send { padding:5px 9px; border-radius:6px; font-size:11px; font-weight:500; background:#15803D; color:#fff; border:none; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-send:hover { opacity:0.9; }
.btn-publish-all   { padding:8px 14px; background:#15803D; color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-publish-all:hover { opacity:0.9; }
.btn-send-all { padding:8px 14px; background:var(--c-accent); color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-send-all:hover { opacity:0.9; }
.btn-unpublish-all { padding:8px 14px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:12px; font-weight:500; cursor:pointer; font-family:var(--f-sans); }
.btn-unpublish-all:hover { background:var(--c-bg); }
.row-actions { display:flex; align-items:center; gap:5px; flex-wrap:wrap; }
.no-content { padding:40px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
.avg-bar { display:inline-block; height:4px; border-radius:2px; background:var(--c-accent); min-width:4px; vertical-align:middle; margin-left:6px; }
.submitted-info { font-size:10px; color:var(--c-text-3); margin-top:3px; }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif

<div class="pg-header">
    <div>
        <div class="pg-title">Results Overview</div>
        <div class="pg-sub">Review, publish, and send report cards to parents.</div>
    </div>
    <a href="{{ route('admin.results.entry') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--c-text-2);text-decoration:none;background:var(--c-surface);">
        ← Enter Results
    </a>
</div>

<div class="selector-bar">
    <label>Term</label>
    <select wire:model.live="selectedTermId" class="sel">
        <option value="">Select term…</option>
        @foreach($terms as $term)
            <option value="{{ $term->id }}">{{ $term->name }} — {{ $term->session->name }}</option>
        @endforeach
    </select>
    <label>Class</label>
    <select wire:model.live="selectedClassId" class="sel" @if(!$selectedTermId) disabled @endif>
        <option value="">Select class…</option>
        @foreach($classes as $class)
            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
        @endforeach
    </select>
</div>

@if(! $selectedClassId)
    <div class="panel"><div class="no-content">Select a term and class to view results.</div></div>
@elseif($rows->isEmpty())
    <div class="panel"><div class="no-content">No students enrolled in this class for the selected term.</div></div>
@else
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">{{ $rows->count() }} {{ Str::plural('student', $rows->count()) }}</span>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="btn-unpublish-all" wire:click="unpublishAll"
                    wire:confirm="Unpublish all results?">Unpublish All</button>
                <button class="btn-publish-all" wire:click="publishAll"
                    wire:confirm="Publish all results? Parents will see them.">Publish All</button>
                <button class="btn-send-all" wire:click="sendAllReportCards"
                    wire:confirm="Send report card PDFs to all parents for this class?"
                    wire:loading.attr="disabled" wire:loading.class="opacity-50">
                    <span wire:loading.remove wire:target="sendAllReportCards">✉ Send All PDFs</span>
                    <span wire:loading wire:target="sendAllReportCards">Queuing…</span>
                </button>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th class="center">Subjects</th>
                    <th class="center">Average</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>
                            <div class="student-name">{{ $row['student']->full_name }}</div>
                            <div class="student-adm">{{ $row['student']->admission_number }}</div>
                            @if($row['submitted'] && $row['submitted_by'])
                                <div class="submitted-info">
                                    Submitted by {{ $row['submitted_by'] }}
                                    @if($row['submitted_at'])· {{ $row['submitted_at']->format('d M, g:ia') }}@endif
                                </div>
                            @endif
                        </td>
                        <td class="center mono">{{ $row['subject_count'] }}</td>
                        <td class="center">
                            @if($row['average'] !== null)
                                <span class="mono" style="font-weight:700">{{ $row['average'] }}%</span>
                                <span class="avg-bar" style="width:{{ min(60, $row['average'] * 0.6) }}px"></span>
                            @else
                                <span style="color:var(--c-text-3)">—</span>
                            @endif
                        </td>
                        <td>
                            @if(! $row['has_results'])
                                <span class="badge badge-none"><span class="badge-dot"></span>No results</span>
                            @elseif($row['published'])
                                <span class="badge badge-published"><span class="badge-dot"></span>Published</span>
                            @elseif($row['submitted'])
                                <span class="badge badge-submitted"><span class="badge-dot"></span>Submitted</span>
                            @else
                                <span class="badge badge-unpublished"><span class="badge-dot"></span>Draft</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                @if($row['has_results'])
                                    <button class="btn-sm" wire:click="toggleStudentPublish({{ $row['student']->id }})">
                                        {{ $row['published'] ? 'Unpublish' : 'Publish' }}
                                    </button>
                                    <button class="btn-send"
                                        wire:click="sendReportCard({{ $row['student']->id }})"
                                        wire:loading.attr="disabled"
                                        title="Email PDF to parents">✉</button>
                                @endif
                                <a href="{{ route('admin.results.report-card', $row['student']) }}?term={{ $selectedTermId }}"
                                   target="_blank" class="btn-pdf">
                                    <svg width="11" height="11" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 1h6l4 4v10H2V1z"/><path d="M10 1v4h4"/></svg>
                                    View PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
</div>
