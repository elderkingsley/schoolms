<div>
<style>
.pg-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.pg-title  { font-size:20px; font-weight:700; color:var(--c-text-1); letter-spacing:-0.03em; }
.pg-sub    { font-size:13px; color:var(--c-text-3); margin-top:2px; }

.flash { padding:12px 16px; border-radius:var(--r-sm); margin-bottom:16px; font-size:13px; font-weight:500; }
.flash-success { background:rgba(21,128,61,0.08); border:1px solid rgba(21,128,61,0.2); color:#15803D; }
.flash-error   { background:rgba(190,18,60,0.08);  border:1px solid rgba(190,18,60,0.2);  color:#BE123C; }

.selector-bar {
    background:var(--c-surface); border:1px solid var(--c-border);
    border-radius:var(--r-md); padding:16px 18px;
    display:flex; align-items:center; gap:12px;
    flex-wrap:wrap; margin-bottom:20px;
}
.selector-bar label { font-size:12px; font-weight:500; color:var(--c-text-2); white-space:nowrap; }
.sel {
    padding:8px 12px; border:1px solid var(--c-border); border-radius:8px;
    font-size:13px; font-family:var(--f-sans); background:var(--c-bg);
    color:var(--c-text-1); outline:none; -webkit-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 8px center; padding-right:28px;
    min-width:150px;
}
.sel:focus { border-color:var(--c-accent); }
.sel:disabled { opacity:0.5; cursor:not-allowed; }

.panel { background:var(--c-surface); border:1px solid var(--c-border); border-radius:var(--r-md); overflow:hidden; margin-bottom:0; }
.panel-head { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--c-border); flex-wrap:wrap; gap:8px; }
.panel-title { font-size:13px; font-weight:600; color:var(--c-text-1); }
.panel-hint  { font-size:11px; color:var(--c-text-3); }

/* Score table */
.score-table { width:100%; border-collapse:collapse; }
.score-table th { font-size:10px; font-weight:600; color:var(--c-text-3); text-transform:uppercase; letter-spacing:0.08em; padding:10px 16px; text-align:left; background:var(--c-bg); border-bottom:1px solid var(--c-border); white-space:nowrap; }
.score-table th.center { text-align:center; }
.score-table td { padding:10px 16px; border-bottom:1px solid var(--c-border); vertical-align:middle; }
.score-table tr:last-child td { border-bottom:none; }
.score-table tr:hover td { background:#fafaf8; }

.student-name { font-weight:600; font-size:13px; color:var(--c-text-1); }
.student-adm  { font-family:var(--f-mono); font-size:11px; color:var(--c-text-3); margin-top:1px; }

/* Score inputs */
.score-input {
    width:64px; padding:7px 8px; border:1px solid var(--c-border); border-radius:6px;
    font-size:13px; font-family:var(--f-mono); text-align:center;
    background:var(--c-bg); color:var(--c-text-1); outline:none;
    transition:border-color 150ms;
}
.score-input:focus { border-color:var(--c-accent); background:#fff; box-shadow:0 0 0 2px rgba(26,86,255,0.08); }

/* Auto-computed total */
.score-total { font-family:var(--f-mono); font-size:14px; font-weight:700; text-align:center; min-width:40px; display:inline-block; }

/* Grade badge */
.grade-badge { display:inline-block; padding:2px 8px; border-radius:6px; font-size:12px; font-weight:700; text-align:center; min-width:28px; }
.grade-A { background:rgba(21,128,61,0.1);  color:#15803D; }
.grade-B { background:rgba(26,86,255,0.08); color:var(--c-accent); }
.grade-C { background:rgba(180,83,9,0.08);  color:#B45309; }
.grade-D { background:rgba(180,83,9,0.06);  color:#B45309; }
.grade-E { background:rgba(190,18,60,0.06); color:var(--c-danger); }
.grade-F { background:rgba(190,18,60,0.1);  color:var(--c-danger); }
.grade-  { background:var(--c-bg);           color:var(--c-text-3); }

/* Save bar */
.save-bar {
    position:sticky; bottom:0; left:0; right:0;
    background:var(--c-surface); border-top:1px solid var(--c-border);
    padding:14px 24px;
    display:flex; align-items:center; justify-content:space-between; gap:10px;
    box-shadow:0 -4px 20px rgba(0,0,0,0.06); z-index:10; flex-wrap:wrap;
}
.save-hint { font-size:12px; color:var(--c-text-3); }
.save-actions { display:flex; gap:8px; }
.btn-save { padding:10px 20px; background:var(--c-accent); color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-save:hover { opacity:0.9; }
.btn-publish { padding:10px 20px; background:#15803D; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; font-family:var(--f-sans); transition:opacity 150ms; }
.btn-publish:hover { opacity:0.9; }

.no-content { padding:40px 20px; text-align:center; font-size:13px; color:var(--c-text-3); }
</style>

@if(session('success'))
    <div class="flash flash-success">✓ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">⚠ {{ session('error') }}</div>
@endif

<div class="pg-header">
    <div>
        <h1 class="pg-title">Results Entry</h1>
        <p class="pg-sub">Enter CA and exam scores. Total, grade and remark are calculated automatically.</p>
    </div>
    <a href="{{ route('admin.results.overview') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border:1px solid var(--c-border);border-radius:8px;font-size:13px;font-weight:500;color:var(--c-text-2);text-decoration:none;background:var(--c-surface);">
        Results Overview →
    </a>
</div>

{{-- Selector bar --}}
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
            <option value="{{ $class->id }}">{{ $class->name }}</option>
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

@if(! $selectedSubjectId)
    <div class="panel">
        <div class="no-content">Select a term, class, and subject above to begin entering results.</div>
    </div>
@elseif($students->isEmpty())
    <div class="panel">
        <div class="no-content">No active students enrolled in this class for the selected term.</div>
    </div>
@else
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">
                {{ $students->count() }} {{ Str::plural('student', $students->count()) }}
            </span>
            <span class="panel-hint">CA max: 40 &nbsp;|&nbsp; Exam max: 60 &nbsp;|&nbsp; Total: 100</span>
        </div>

        <div style="overflow-x:auto">
            <table class="score-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th class="center">CA <span style="font-weight:400;text-transform:none;">(/ 40)</span></th>
                        <th class="center">Exam <span style="font-weight:400;text-transform:none;">(/ 60)</span></th>
                        <th class="center">Total</th>
                        <th class="center">Grade</th>
                        <th class="center">Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $i => $student)
                        {{--
                            Alpine.js x-data component per row.
                            Reads the Livewire-bound CA and Exam values reactively,
                            computes total locally (no server round trip on each keystroke),
                            and derives grade/remark from the percentage scale.
                        --}}
                        <tr x-data="{
                            get ca()   { return parseInt($wire.scores['{{ $student->id }}']?.ca   || 0) },
                            get exam() { return parseInt($wire.scores['{{ $student->id }}']?.exam || 0) },
                            get total() { return Math.min(100, this.ca + this.exam) },
                            get grade() {
                                const t = this.total;
                                if (t >= 75) return 'A';
                                if (t >= 65) return 'B';
                                if (t >= 55) return 'C';
                                if (t >= 45) return 'D';
                                if (t >= 35) return 'E';
                                if (t > 0)   return 'F';
                                return '—';
                            },
                            get remark() {
                                const t = this.total;
                                if (t >= 75) return 'Excellent';
                                if (t >= 65) return 'Very Good';
                                if (t >= 55) return 'Good';
                                if (t >= 45) return 'Fair';
                                if (t >= 35) return 'Pass';
                                if (t > 0)   return 'Fail';
                                return '—';
                            },
                            get gradeClass() { return 'grade-' + (this.grade === '—' ? '' : this.grade) }
                        }">
                            <td style="color:var(--c-text-3);font-size:12px;width:36px;">{{ $i + 1 }}</td>
                            <td>
                                <div class="student-name">{{ $student->full_name }}</div>
                                <div class="student-adm">{{ $student->admission_number }}</div>
                            </td>
                            <td style="text-align:center">
                                <input
                                    type="number"
                                    min="0" max="40"
                                    class="score-input"
                                    wire:model.lazy="scores.{{ $student->id }}.ca"
                                    placeholder="—">
                                @error("scores.{$student->id}.ca")
                                    <div style="font-size:10px;color:var(--c-danger);margin-top:2px;">{{ $message }}</div>
                                @enderror
                            </td>
                            <td style="text-align:center">
                                <input
                                    type="number"
                                    min="0" max="60"
                                    class="score-input"
                                    wire:model.lazy="scores.{{ $student->id }}.exam"
                                    placeholder="—">
                                @error("scores.{$student->id}.exam")
                                    <div style="font-size:10px;color:var(--c-danger);margin-top:2px;">{{ $message }}</div>
                                @enderror
                            </td>
                            <td style="text-align:center">
                                <span class="score-total"
                                    :style="total > 0 ? 'color:var(--c-text-1)' : 'color:var(--c-text-3)'"
                                    x-text="total > 0 ? total : '—'">
                                </span>
                            </td>
                            <td style="text-align:center">
                                <span class="grade-badge" :class="gradeClass" x-text="grade"></span>
                            </td>
                            <td style="text-align:center;font-size:12px;color:var(--c-text-3);">
                                <span x-text="remark"></span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sticky save bar --}}
    <div class="save-bar">
        <span class="save-hint">
            Scores are not saved until you click Save. Publishing makes results visible to parents.
        </span>
        <div class="save-actions">
            <button class="btn-save" wire:click="save"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="save">Save Draft</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
            <button class="btn-publish" wire:click="saveAndPublish"
                wire:confirm="Publish results? Parents will be able to see these scores in their portal."
                wire:loading.attr="disabled" wire:loading.class="opacity-50">
                <span wire:loading.remove wire:target="saveAndPublish">Save & Publish</span>
                <span wire:loading wire:target="saveAndPublish">Publishing…</span>
            </button>
        </div>
    </div>
@endif

</div>
