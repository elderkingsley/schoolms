<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }

.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.08);  border:1px solid rgba(190,18,60,0.2);  color:#BE123C; }

.session-bar {
    background:var(--c-surface); border:1px solid var(--c-border);
    border-radius:var(--r-md); padding:14px 18px;
    display:flex; align-items:center; gap:14px;
    flex-wrap:wrap; margin-bottom:20px;
}
.session-bar label { font-size:12px; font-weight:500; color:var(--c-text-2); }
.session-select {
    padding:8px 12px; border:1px solid var(--c-border); border-radius:8px;
    font-size:13px; font-family:var(--f-sans); background:var(--c-bg);
    color:var(--c-text-1); outline:none; -webkit-appearance:none; min-width:200px;
}
.session-select:focus { border-color:var(--c-accent); }
.btn-copy { padding:8px 14px; border:1px solid var(--c-border); border-radius:8px; font-size:12px; font-weight:500; background:none; color:var(--c-text-2); cursor:pointer; font-family:var(--f-sans); transition:background 150ms; }
.btn-copy:hover { background:var(--c-bg); }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; margin-bottom:20px; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.panel-hint  { font-size:11px; color:var(--c-text-3); }

/* Grid table */
.grid-table { width:100%; border-collapse:collapse; }
.grid-table th {
    font-size:10px; font-weight:600; color:var(--c-text-3);
    text-transform:uppercase; letter-spacing:0.06em;
    padding:10px 12px; text-align:center;
    background:var(--c-bg); border-bottom:1px solid var(--c-border);
    white-space:nowrap;
}
.grid-table th:first-child { text-align:left; padding-left:20px; min-width:160px; position:sticky; left:0; background:var(--c-bg); z-index:1; }
.grid-table td { padding:10px 12px; border-bottom:1px solid var(--c-border); text-align:center; vertical-align:middle; }
.grid-table td:first-child { text-align:left; padding-left:20px; font-weight:500; font-size:13px; position:sticky; left:0; background:var(--c-surface); z-index:1; }
.grid-table tr:last-child td { border-bottom:none; }
.grid-table tr:hover td { background:rgba(26,86,255,0.02); }
.grid-table tr:hover td:first-child { background:rgba(26,86,255,0.02); }

/* Checkbox cells */
.cs-checkbox {
    width:20px; height:20px; border-radius:4px;
    border:2px solid var(--c-border); background:var(--c-bg);
    cursor:pointer; appearance:none; -webkit-appearance:none;
    transition:background 150ms, border-color 150ms;
    position:relative; display:block; margin:0 auto;
}
.cs-checkbox:checked {
    background:var(--c-accent); border-color:var(--c-accent);
}
.cs-checkbox:checked::after {
    content:''; position:absolute; left:5px; top:2px;
    width:6px; height:10px;
    border:2px solid #fff; border-top:none; border-left:none;
    transform:rotate(45deg);
}
.cs-checkbox:hover:not(:checked) { border-color:var(--c-accent); }

.no-session { padding:40px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">⚠ {{ session('error') }}</div>
@endif

<div class="pg-header">
    <div>
        <h1 class="pg-title">Class Subject Assignments</h1>
        <p class="pg-sub">Tick which subjects each class takes. Changes save instantly.</p>
    </div>
    <a href="{{ route('admin.subjects') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--c-text-2);text-decoration:none;background:var(--c-surface);">
        ← Manage Subjects
    </a>
</div>

{{-- Session selector --}}
<div class="session-bar">
    <label>Academic Session:</label>
    <select wire:model.live="selectedSessionId" class="session-select">
        <option value="">Select a session</option>
        @foreach($sessions as $session)
            <option value="{{ $session->id }}">{{ $session->name }}</option>
        @endforeach
    </select>

    @if($previousSession && $selectedSessionId)
        <button class="btn-copy"
            wire:click="copyFromSession({{ $previousSession->id }})"
            wire:confirm="Copy all assignments from {{ $previousSession->name }}? This will add (not replace) assignments to the current session.">
            Copy from {{ $previousSession->name }}
        </button>
    @endif
</div>

@if(! $selectedSessionId)
    <div class="panel"><div class="no-session">Select a session above to view and edit class-subject assignments.</div></div>
@elseif($subjects->isEmpty())
    <div class="panel"><div class="no-session">No active subjects found. <a href="{{ route('admin.subjects') }}" style="color:var(--c-accent)">Create some →</a></div></div>
@elseif($classes->isEmpty())
    <div class="panel"><div class="no-session">No classes found.</div></div>
@else
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Subject × Class Grid</span>
            <span class="panel-hint">✓ = assigned &nbsp; □ = not assigned</span>
        </div>
        <div style="overflow-x:auto">
            <table class="grid-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        @foreach($classes as $class)
                            <th>{{ $class->display_name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjects as $subject)
                        <tr>
                            <td>{{ $subject->name }}</td>
                            @foreach($classes as $class)
                                <td>
                                    <input
                                        type="checkbox"
                                        class="cs-checkbox"
                                        @checked(isset($assigned["{$class->id}-{$subject->id}"]))
                                        wire:click="toggle({{ $class->id }}, {{ $subject->id }})"
                                        title="{{ $class->name }} — {{ $subject->name }}">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
</div>
