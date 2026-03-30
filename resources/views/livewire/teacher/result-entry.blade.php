<div>
<style>
.pg-title { font-size:18px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.02em; margin-bottom:4px; }
.pg-sub   { font-size:13px; color:var(--c-text-3); margin-bottom:16px; }
.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }

.selector-bar { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); padding:14px 16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
.selector-bar label { font-size:12px; font-weight:500; color:var(--c-text-2); white-space:nowrap; }
.sel { padding:8px 10px; border:1px solid var(--c-border); border-radius:8px; font-size:13px; font-family:var(--f-sans); background:var(--c-bg); color:var(--c-text-1); outline:none; -webkit-appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 8px center; padding-right:26px; min-width:140px; flex:1; }
.sel:focus { border-color:var(--c-accent); }
.sel:disabled { opacity:0.5; }

.submitted-banner { background:rgba(180,83,9,0.08); border:1px solid rgba(180,83,9,0.2); border-radius:var(--r-sm); padding:12px 16px; margin-bottom:14px; font-size:13px; color:#B45309; font-weight:500; }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid var(--c-border); flex-wrap:wrap; gap:6px; }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.panel-hint  { font-size:11px; color:var(--c-text-3); }

.score-table { width:100%; border-collapse:collapse; }
.score-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.07em; padding:9px 14px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); }
.score-table th.center { text-align:center; }
.score-table td { padding:10px 14px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.score-table tr:last-child td { border-bottom:none; }
.score-table tr:hover td { background:#fafaf8; }

.student-name { font-weight:600; font-size:13px; }
.student-adm  { font-family:var(--f-mono); font-size:11px; color:var(--c-text-3); }
.score-input { width:60px; padding:7px 8px; border:1px solid var(--c-border); border-radius:6px; font-size:13px; font-family:var(--f-mono); text-align:center; background:var(--c-bg); outline:none; transition:border-color 150ms; }
.score-input:focus { border-color:var(--c-accent); background:#fff; }
.score-total { font-family:var(--f-mono); font-size:14px; font-weight:700; text-align:center; display:inline-block; min-width:36px; }

.grade-badge { display:inline-block; padding:2px 7px; border-radius:5px; font-size:11px; font-weight:700; }
.grade-A { background:rgba(21,128,61,0.1);  color:#15803D; }
.grade-B { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.grade-C { background:rgba(180,83,9,0.08);  color:#B45309; }
.grade-D,.grade-E,.grade-F { background:rgba(190,18,60,0.08); color:var(--c-danger); }
.grade-  { background:var(--c-bg); color:var(--c-text-3); }

.save-bar { position:sticky; bottom:0; left:0; right:0; background:var(--c-surface); border-top:1px solid var(--c-border); padding:12px 16px; display:flex; align-items:center; justify-content:space-between; gap:10px; box-shadow:0 -4px 16px rgba(0,0,0,0.06); z-index:10; flex-wrap:wrap; }
.save-hint { font-size:11px; color:var(--c-text-3); }
.save-actions { display:flex; gap:8px; }
.btn-draft   { padding:9px 16px; background:none; border:1px solid var(--c-border); color:var(--c-text-2); border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); }
.btn-draft:hover { background:var(--c-bg); }
.btn-submit  { padding:9px 16px; background:#15803D; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-submit:hover { opacity:0.9; }
.no-content { padding:32px 16px; text-align:center; font-size:13px; color:var(--c-text-3); }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif

<div class="pg-title">Enter Results</div>
<div class="pg-sub">Save as draft at any time. Submit for review when scores are final.</div>

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
        @foreach($myClasses as $class)
            <option value="{{ $class->id }}">{{ $class->display_name }}</option>
        @endforeach
    </select>

    <label>Subject</label>
    <select wire:model.live="selectedSubjectId" class="sel" @if(!$selectedClassId) disabled @endif>
        <option value="">Select subject…</option>
        @foreach($subjects as $subject)
            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
        @endforeach
    </select>
</div>

@if($isSubmitted)
    <div class="submitted-banner">
        ⏳ You have already submitted these results for admin review. You can still update scores — submitting again will reset the review timestamp.
    </div>
@endif

@if(! $selectedSubjectId)
    <div class="panel"><div class="no-content">Select a term, class, and subject to begin.</div></div>
@elseif($students->isEmpty())
    <div class="panel"><div class="no-content">No active students enrolled in this class.</div></div>
@else
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">{{ $students->count() }} {{ Str::plural('student', $students->count()) }}</span>
            <span class="panel-hint">CA max: 40 &nbsp;|&nbsp; Exam max: 60</span>
        </div>
        <div style="overflow-x:auto">
            <table class="score-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th class="center">CA (40)</th>
                        <th class="center">Exam (60)</th>
                        <th class="center">Total</th>
                        <th class="center">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                        <tr x-data="{
                            get ca()    { return parseInt($wire.scores['{{ $student->id }}']?.ca   || 0) },
                            get exam()  { return parseInt($wire.scores['{{ $student->id }}']?.exam || 0) },
                            get total() { return Math.min(100, this.ca + this.exam) },
                            get grade() {
                                const t = this.total;
                                if(t>=75)return'A'; if(t>=65)return'B'; if(t>=55)return'C';
                                if(t>=45)return'D'; if(t>=35)return'E'; if(t>0)return'F'; return'—';
                            },
                            get gc() { return 'grade-'+(this.grade==='—'?'':this.grade) }
                        }">
                            <td style="color:var(--c-text-3);width:32px;font-size:12px;">{{ $i + 1 }}</td>
                            <td>
                                <div class="student-name">{{ $student->full_name }}</div>
                                <div class="student-adm">{{ $student->admission_number }}</div>
                            </td>
                            <td style="text-align:center">
                                <input type="number" min="0" max="40" class="score-input"
                                    wire:model.lazy="scores.{{ $student->id }}.ca" placeholder="—">
                            </td>
                            <td style="text-align:center">
                                <input type="number" min="0" max="60" class="score-input"
                                    wire:model.lazy="scores.{{ $student->id }}.exam" placeholder="—">
                            </td>
                            <td style="text-align:center">
                                <span class="score-total"
                                    :style="total>0?'color:var(--c-text-1)':'color:var(--c-text-3)'"
                                    x-text="total>0?total:'—'"></span>
                            </td>
                            <td style="text-align:center">
                                <span class="grade-badge" :class="gc" x-text="grade"></span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="save-bar">
        <span class="save-hint">Drafts are not visible to parents. Submit for admin review when ready.</span>
        <div class="save-actions">
            <button class="btn-draft" wire:click="save"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="save">Save Draft</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
            <button class="btn-submit" wire:click="submitForReview"
                wire:confirm="Submit these results for admin review? The admin will be able to see and publish them."
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="submitForReview">Submit for Review</span>
                <span wire:loading wire:target="submitForReview">Submitting…</span>
            </button>
        </div>
    </div>
@endif
</div>
